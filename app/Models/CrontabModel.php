<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Support\Facades\Cache;
use App\Models\Alarm;
use App\Models\BaseModel;
use App\Models\LogCrontabModel;
use App\Models\ProjectModel;
use App\Models\UnitTestModel;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

class CrontabModel extends BaseModel
{
	use HasDateTimeFormatter;
    protected $table = 'crontab';

    const CACHE_KEY = 'crontab_task';
    const CACHE_TIME = 86400;

    /**
     * 数据状态
     */
    const STATUS_DELETED = 0;
    const STATUS_NORMAL  = 1;
    public static $label_status = [
        self::STATUS_DELETED => '禁用',
        self::STATUS_NORMAL  => '启用',
    ];

    protected $fillable = [
        'project_id',
        'domain',
        'title',
        'desc',
        'task_type',
        'task_value',
        'retain_day',
        'crontab',
        'alarm_enable',
        'status',
        'last_time',
    ];

    public function project()
    {
        return $this->belongsTo(ProjectModel::class, 'project_id', 'id');
    }

    public function setTaskValueAttribute($value)
    {
        $this->attributes['task_value'] = json_encode(array_values($value));
    }

    public static function getCrontabFromCache()
    {
        $task = Cache::get(self::CACHE_KEY);
        if (empty($task)) {
            $task = CrontabModel::getAll([], ['id', 'crontab', 'retain_day'])->toArray();
            Cache::put(self::CACHE_KEY, $task, self::CACHE_TIME);
        }
        return $task;
    }

    public static function boot()
    {
        parent::boot();

        CrontabModel::created(function () {
            Cache::forget(self::CACHE_KEY);
        });

        CrontabModel::updated(function () {
            Cache::forget(self::CACHE_KEY);
        });

        CrontabModel::saved(function () {
            Cache::forget(self::CACHE_KEY);
        });

        CrontabModel::deleted(function () {
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * 运行crontab
     */
    public static function runCrontab($id)
    {
        $crontab = CrontabModel::where(['id' => $id])->first();
        if (empty($crontab)) {
            return;
        }

        $domain = ProjectModel::getDomainByKey($crontab->project_id, $crontab->domain);
        $task_value = json_decode($crontab['task_value'], true);
        // 回归测试用例
        if ($crontab['task_type'] == CrontabModel::TASK_TYPE_REGRESSION_TEST) {
            $testModel = RegressionTestModel::with(['api', 'unitTest'])->whereIn('id', $task_value)->where(['status' => UnitTestModel::STATUS_NORMAL])->get();
        } else {
            // 集成测试用例
        }
        if (empty($testModel)) {
            return;
        }

        $total_unit = 0;
        $requestData = [];
        foreach ($testModel as $regTest) {
            if (empty($regTest->toArray())) {
                continue;
            }
            if ($regTest->domain != $crontab->domain) {
                continue;
            }
            $body = json_decode($regTest->unitTest->body, true);
            $headers = json_decode($regTest->unitTest->header, true);
            if (empty($body)) {
                $body = [];
            }
            if (empty($headers)) {
                $headers = [];
            }

            $body = unset_null($body);
            $headers = unset_null($headers);

            if ($regTest->api->method == "GET") {
                $url = $domain . $regTest->api->url . "?" . http_build_query($body);
                $form_params = [];
            } else {
                $url = $domain . $regTest->api->url;
                $form_params = $body;
            }
            $requestData[] = [
                'url' => $url,
                'headers' => $headers,
                'form_params' => $form_params,
                'api_id' => $regTest->api->id,
                'api_name' => $regTest->api->name,
                'api_url' => $regTest->api->url,
                'project_id' => $regTest->project_id,
                'unit_test_id' => $regTest->unitTest->id,
                'method' => $regTest->api->method,
                'type' => $regTest->type,
                'response' => $regTest->response,
                'unit_test_name' => $regTest->unitTest->name,
                'ignore_fields' => explode(',', $regTest->ignore_fields),
            ];
            $total_unit ++;
        }

        $ret = self::sendRequest($requestData);

        $crontab->last_time = date("Y-m-d H:i:s");
        $crontab->save();
        if (!isset($ret[$crontab->project_id])) {
            self::alarm($crontab);
            LogCrontabModel::saveLog($crontab->project_id, $id);
            return false;
        }

        $ret = $ret[$crontab->project_id];
        $success = ($ret['success_count'] == $ret['total_count']);
        $ret['domain_env'] = $crontab->domain;
        $ret['domain'] = $domain;
        $ret['project_name'] = $crontab->project->name;

        if (empty($success)) {
            self::alarm($crontab);
        }
        LogCrontabModel::saveLog($crontab->project_id, $id, $success, json_encode($ret, JSON_UNESCAPED_UNICODE));
        return $success;
    }

    /**
     * 发送并发请求
     * @param  array        $requestData        [['url','headers','form_params','api_id','api_name','api_url','project_id','unit_test_id','method','type','response','unit_test_name','ignore_fields'], ...]
     * @param  int          $concurrency        并发数
     * @param  array        $header             header,例如登录认证令牌等
     * @param  int          $timeOut            超时限制120s
     * @return false|array
     */
    public static function sendRequest($requestData = [], $concurrency = 20, $timeOut = 120)
    {
        if (empty($requestData)) {
            return false;
        }
        $client = new Client(['timeout' => $timeOut]);
        $requests = function ($params) use ($client) {
            if (!empty($params)) {
                foreach ($params as $param) {
                    if ($param['method'] == "GET") {
                        yield new GuzzleRequest($param['method'], $param['url'], $param['headers']);
                    } elseif ($param['method'] == "POST") {
                        yield function () use ($client, $param) {
                            return $client->requestAsync($param['method'], $param['url'], [
                                'headers' => $param['headers'],
                                'json' => $param['form_params'],
                            ]);
                        };
                    }
                }
            }
        };

        $temp = [];
        $pool = new Pool($client, $requests($requestData), [
            'concurrency' => $concurrency,
            'fulfilled' => function ($response, $index) use (&$temp) { //成功
                $temp[$index] = [
                    'key' => $index,
                    'success' => true,
                    'response' => $response->getBody()->getContents(),
                ];
            },
            'rejected' => function ($reason, $index) use (&$temp) { //失败
                $str = $reason->getMessage();
                $str = str_replace("\\", '\\\\', $str);
                $str = str_replace("\r\n", '\n', $str);
                $temp[$index] = [
                    'key' => $index,
                    'success' => false,
                    'response' => json_encode([$str]),
                ];
            }
        ]);

        $promise = $pool->promise();
        $promise->wait();

        $ret = [];
        foreach ($temp as $key => $response) {
            $requestItem = $requestData[$key];
            $project_id = $requestItem['project_id'];
            $api_id = $requestItem['api_id'];
            $success = false;
            if ($response['success']) {
                // 完全匹配
                if ($requestItem['type'] == BaseModel::REG_TYPE_ALL) {
                    if (!empty($requestItem['ignore_fields'])) {
                        $response['response'] = json_decode($response['response'], true);
                        foreach ($requestItem['ignore_fields'] as $ignore_field) {
                            if (!empty($ignore_field) && isset($response['response'][$ignore_field])) {
                                unset($response['response'][$ignore_field]);
                            }
                        }
                        $response['response'] = json_encode($response['response']);
                        $response_md5 = md5((trim($response['response'])));
                    } else {
                        $response_md5 = md5((trim($response['response'])));
                    }
                    $success = ($response_md5 == md5(trim($requestItem['response'])));
                } else {
                    // 请求成功
                    $success = true;
                }
            }

            if (!isset($ret[$project_id])) {
                $ret[$project_id] = [
                    "id"            => $project_id,
                    "success_count" => 0,
                    "total_count"   => 0,
                ];
            }
            if (!isset($ret[$project_id]['apiList'][$api_id])) {
                $ret[$project_id]['apiList'][$api_id] = [
                    "id"            => $api_id,
                    "success_count" => 0,
                    "total_count"   => 0,
                    "method"        => $requestItem['method'],
                    "name"          => $requestItem['api_name'],
                    "url"           => $requestItem['api_url'],
                ];
            }
            if (!isset($ret[$project_id]['apiList'][$api_id]['unitTestList'][$requestItem['unit_test_id']])) {
                $ret[$project_id]['apiList'][$api_id]['unitTestList'][$requestItem['unit_test_id']] = [
                    'id'             => $requestItem['unit_test_id'],
                    "request_result" => $response['success'],
                    'name'           => $requestItem['unit_test_name'],
                    'response'       => $response['response'],
                    'response_reg'   => $requestItem['response'],
                    'ignore_fields'  => implode(',', $requestItem['ignore_fields']),
                ];
                if ($requestItem['type'] == BaseModel::REG_TYPE_ALL) {
                    $ret[$project_id]['apiList'][$api_id]['unitTestList'][$requestItem['unit_test_id']]['result'] = $success;
                }
            }

            if ($success) {
                $ret[$project_id]['success_count'] ++;
                $ret[$project_id]['apiList'][$api_id]['success_count'] ++;
            }
            $ret[$project_id]['total_count'] ++;
            $ret[$project_id]['apiList'][$api_id]['total_count'] ++;
        }

        return $ret;
    }


    public static function alarm($crontab)
    {
        if (!$crontab->project->alarm_enable || !$crontab->alarm_enable) {
            return false;
        }
        $emails = [];
        $robot_keys = [];
        foreach ($crontab->project->alarm_param as $alarm_param) {
            if (!empty($alarm_param['alarm_qy_wechat'])) {
                $robot_keys[] = $alarm_param['alarm_qy_wechat'];
            }
            if (!empty($alarm_param['alarm_email'])) {
                $emails[] = $alarm_param['alarm_email'];
            }
        }

        $alarm_msg = sprintf(">项目: %s\n>任务id: %s\n>任务名称: %s\n>任务描述: %s\n>执行结果: 失败\n执行时间: %s",
            $crontab->project->name,
            $crontab->id,
            $crontab->title,
            $crontab->desc,
            date("Y-m-d H:i:s")
        );
        Alarm::alarmQyWeachat($alarm_msg, '计划任务执行失败', $robot_keys);
    }
}
