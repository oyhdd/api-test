<?php

namespace App\Console\Commands;

use App\Models\Alarm;
use App\Models\BaseModel;
use App\Models\CrontabModel;
use App\Models\LogCrontabModel;
use App\Models\ProjectModel;
use App\Models\UnitTestModel;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

class CrontabCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apitest:crontab {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '计划任务';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        $crontab = CrontabModel::getOne(['id' => $id]);
        if (empty($crontab)) {
            return;
        }

        $domain = ProjectModel::getDomainByKey($crontab->project_id, $crontab->domain);
        $task_value = json_decode($crontab['task_value'], true);
        // 测试用例
        if ($crontab['task_type'] == CrontabModel::TASK_TYPE_UNIT_TEST) {
            $testModel = UnitTestModel::with(['api', 'regTest'])->whereIn('id', $task_value)->where(['status' => UnitTestModel::STATUS_NORMAL])->get();
        } else {
            // 集成测试
        }
        if (empty($testModel)) {
            return;
        }

        $total_unit = 0;
        $requestData = [];
        foreach ($testModel as $unitTest) {
            if (empty($unitTest->regTest->toArray())) {
                continue;
            }
            foreach ($unitTest->regTest as $regTest) {
                if ($regTest->domain == $crontab->domain) {
                    break;
                }
            }
            $body = json_decode($unitTest->body, true);
            $headers = json_decode($unitTest->header, true);
            if (empty($body)) {
                $body = [];
            }
            if (empty($headers)) {
                $headers = [];
            }

            $body = unset_null($body);
            $headers = unset_null($headers);

            if ($unitTest->api->method == "GET") {
                $url = $domain . $unitTest->api->url . "?" . http_build_query($body);
                $form_params = [];
            } else {
                $url = $domain . $unitTest->api->url;
                $form_params = $body;
            }
            $requestData[] = [
                'url' => $url,
                'headers' => $headers,
                'form_params' => $form_params,
                'api_id' => $unitTest->api->id,
                'api_name' => $unitTest->api->name,
                'api_url' => $unitTest->api->url,
                'project_id' => $unitTest->project_id,
                'unit_test_id' => $unitTest->id,
                'method' => $unitTest->api->method,
                'type' => $regTest->type,
                'response_md5' => $regTest->response_md5,
                'unit_test_name' => $unitTest->name,
                'ignore_fields' => explode(',', $regTest->ignore_fields),
            ];
            $total_unit ++;
        }

        $ret = $this->sendRequest($requestData);

        $crontab->last_time = date("Y-m-d H:i:s");
        $crontab->save();
        if (!isset($ret[$crontab->project_id])) {
            $this->alarm($crontab);
            return $this->saveLog($crontab->project_id, $id);
        }

        $ret = $ret[$crontab->project_id];
        $success = ($ret['success_count'] == $ret['total_count']);
        $ret['domain_env'] = $crontab->domain;
        $ret['domain'] = $domain;
        $ret['project_name'] = $crontab->project->name;

        if (empty($success)) {
            $this->alarm($crontab);
        }
        return $this->saveLog($crontab->project_id, $id, $success, json_encode($ret, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 发送并发请求
     * @param  array        $requestData        [['url','headers','form_params','api_id','api_name','api_url','project_id','unit_test_id','method','type','response_md5','unit_test_name','ignore_fields'], ...]
     * @param  int          $concurrency        并发数
     * @param  array        $header             header,例如登录认证令牌等
     * @param  int          $timeOut            超时限制60s
     * @return false|array
     */
    public function sendRequest($requestData = [], $concurrency = 20, $header = [], $timeOut = 120)
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
                    $response_md5 = md5((trim($response['response'])));
                    if (!empty($requestItem['ignore_fields'])) {
                        $temp_response = json_decode($response['response'], true);
                        foreach ($requestItem['ignore_fields'] as $ignore_field) {
                            if (!empty($ignore_field) && isset($temp_response[$ignore_field])) {
                                unset($temp_response[$ignore_field]);
                            }
                        }
                        $temp_response = json_encode($temp_response);
                        $response_md5 = md5((trim($temp_response)));
                    }
                    $success = ($response_md5 == $requestItem['response_md5']);
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

    public function saveLog($project_id, $crontab_id, $success = 0, $log = '[]')
    {
        $day = date("Y-m-d");
        $model = new LogCrontabModel(compact('day', 'project_id', 'crontab_id', 'success', 'log'));
        return $model->save();
    }

    public function alarm($crontab)
    {
        if (!$crontab->project->alarm_enable) {
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
