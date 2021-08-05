<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Api;
use Dcat\Admin\Admin;
use App\Models\ApiModel;
use App\Models\BaseModel;
use App\Models\ProjectModel;
use Dcat\Admin\Form;
use Dcat\Admin\Layout\Content;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Exception\RequestException;

/**
 * 接口调试
 */
class RunController extends AdminController
{
    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        $apiList = ApiModel::getAll(['project_id' => AdminController::getProjectId()])->pluck('id')->toArray();
        if ($api_id = current($apiList)) {
            return redirect(admin_url('run/'.$api_id));
        }

        return $content
            ->title('接口调试')
            ->body("请先 <a href='/admin/api'>添加接口</a>");
    }

    /**
     * 运行接口界面
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        $this->hasPermission($id);
        Admin::js([
            '/js/jsbeautify.js',
            '/js/checkutil.js',
            '/js/treeMenu.js',
        ]);
        Admin::css('/css/treeMenu.css');
        $model = ApiModel::where(['status' => ApiModel::STATUS_NORMAL])->findOrFail($id);

        return $content
            ->title($model->project->name ?? "")
            ->description($model->name)
            ->row(view('unit_test.index', ['model' => $model]));
    }

    /**
     * 运行接口
     */
    public function update($id)
    {
        $project_id = $this->request->input('project_id');
        $domain_key = $this->request->input('domain');
        if (empty($domain_key)) {
            return admin_toastr('请选择运行环境');
        }
        $domain = ProjectModel::getDomainByKey($project_id, $domain_key);
        $url = $this->request->input('url');
        $method = $this->request->input('method');
        $header = unset_null($this->request->input('header', []));
        $body = unset_null($this->request->input('body', []));
        $url = $domain . $url;

        $status = false;
        $start_time = microtime(true);
        $curl_example = 'curl';
        try {
            $client = new Client();
            $options = empty($header) ? [] : ['headers' => $header];
            if ($method == 'GET') {
                if (!empty($body)) {
                    $url .= '?' . http_build_query($body);
                }
                $curl_example .= " -g \"{$url}\" ";
                foreach ($header as $key => $value) {
                    $curl_example .= " -H \"{$key}:$value\"";
                }
            } else {
                $options['headers']['Accept'] = 'application/json';
                $options['headers']['Content-type'] = 'application/json';
                $options['json'] = $body;
                $curl_example .= " -X {$method} \"{$url}\"";
                foreach ($options['headers'] as $key => $header) {
                    $curl_example .= " -H \"{$key}:$header\"";
                }
                $curl_example .= " --data '" . json_encode($body) . "'";
            }
            $response = $client->request($method, $url, $options);

            $status = true;
            $status_code = $response->getStatusCode();
            $message = '请求成功';
            $result = $response->getBody()->getContents();
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $status_code = $response->getStatusCode();
            $message = $e->getMessage();
            $result = $response->getBody()->getContents();
        }

        return [
            'status'  => $status,
            'data' => [
                'message' => $message,
            ],
            'result' => $result,
            'detail' => [
                'status_code' => $status_code,
                'request_time' => round((microtime(true) - $start_time) * 1000),
                'curl_example' => $curl_example,
            ]
        ];
    }

    /**
     * 回归测试
     */
    public function regress()
    {
        $ret = [
            'code' => -1,
            'message' => '回归测试失败，请重试！',
            'data' => [],
        ];
        $list = $this->request->input('api');
        
        if (!Admin::user()->isAdministrator()) {
            $projectIds = ProjectModel::getProjectIds(Admin::user()->id);
        } else {
            $projectIds = ProjectModel::getAll()->pluck('id')->toArray();
        }

        $projectIds = array_intersect($projectIds, array_keys($list));
        $projectList = ProjectModel::getAll()->whereIn('id', $projectIds)->toArray();
        $projectList = array_column($projectList, null, 'id');

        foreach ($list as $project_id => $value) {
            $list[$project_id]['project_id'] = $project_id;
            $list[$project_id]['project_name'] = $projectList[$project_id]['name'];
            $list[$project_id]['domain_list'] = array_column($projectList[$project_id]['domain'], 'value', 'key');
            $list[$project_id]['api_ids'] = empty($value['api_ids']) ? [] : explode(',', $value['api_ids']);
        }

        $ret['data'] = $this->sendRequest($list);
        if (!empty($ret['data'])) {
            $ret['code'] = 0;
            $ret['message'] = '成功';
        }

        return $ret;
    }

    /**
     * 发送并发请求
     * @author wangmeng
     * @date   2019-05-15
     * @param  array        $list               list[[project_id, project_name, domain, domain_list, api_ids], ...]
     * @param  int          $concurrency        并发数
     * @param  array        $header             header,例如登录认证令牌等
     * @param  int          $timeOut            超时限制60s
     * @return false|array
     */
    public static function sendRequest($list = [], $concurrency = 20, $header = [], $timeOut = 120)
    {
        $requestData = $projectData = [];
        $total_project = $total_api = $total_unit = $success_count = 0;

        foreach ($list as $item) {
            $projectData[$item['project_id']] = [
                'id' => $item['project_id'],
                'domain' => $item['domain'],
                'name' => $item['project_name'],
            ];
            if (empty($item['api_ids']) || empty($item['domain'])) {
                continue;
            }
            $total_project ++;

            // 接口列表
            $apiList = ApiModel::getAll()->whereIn('id', $item['api_ids']);
            foreach ($apiList as $api) {
                $total_api ++;
                // 回归测试列表
                foreach ($api->regTest as $key => $regTest) {
                    if (in_array($api->method, BaseModel::$label_request_methods) && $item['domain_list'][$regTest->domain] == $item['domain']) {
                        $body = json_decode($regTest->unitTest->body, true);
                        $headers = json_decode($regTest->unitTest->header, true);
                        if (empty($body)) {
                            $body = [];
                        }
                        if (empty($headers)) {
                            $headers = [];
                        }
                        if (!empty($header)) {
                            $headers += $header;
                        }
                        $body = unset_null($body);
                        $headers = unset_null($headers);

                        if ($api->method == "GET") {
                            $url = $item['domain'] . $api->url . "?" . http_build_query($body);
                            $form_params = [];
                        } else {
                            $url = $item['domain'] . $api->url;
                            $form_params = $body;
                        }

                        $requestData[] = [
                            'url' => $url,
                            'headers' => $headers,
                            'form_params' => $form_params,
                            // 'key' => $key,
                            'api_id' => $api->id,
                            'api_name' => $api->name,
                            'api_url' => $api->url,
                            'project_id' => $item['project_id'],
                            'unit_test_id' => $regTest->unit_test_id,
                            'method' => $api->method,
                            'type' => $regTest->type,
                            'response' => $regTest->response,
                            'unit_test_name' => $regTest->unitTest->name,
                            'ignore_fields' => explode(',', $regTest->ignore_fields),
                        ];
                        $total_unit ++;
                    } else {
                        continue;
                    }
                }
            }
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

            if (!isset($ret['list'][$project_id])) {
                $ret['list'][$project_id] = [
                    "id"            => $project_id,
                    "success_count" => 0,
                    "total_count"   => 0,
                    "name"          => $projectData[$project_id]['name'],
                    "domain"        => $projectData[$project_id]['domain'],
                ];
            }
            if (!isset($ret['list'][$project_id]['apiList'][$api_id])) {
                $ret['list'][$project_id]['apiList'][$api_id] = [
                    "id"            => $api_id,
                    "success_count" => 0,
                    "total_count"   => 0,
                    "method"        => $requestItem['method'],
                    "name"          => $requestItem['api_name'],
                    "url"           => $requestItem['api_url'],
                ];
            }
            if (!isset($ret['list'][$project_id]['apiList'][$api_id]['unitTestList'][$requestItem['unit_test_id']])) {
                $ret['list'][$project_id]['apiList'][$api_id]['unitTestList'][$requestItem['unit_test_id']] = [
                    'id'             => $requestItem['unit_test_id'],
                    "request_result" => $response['success'],
                    'name'           => $requestItem['unit_test_name'],
                    'response'       => $response['response'],
                    'response_reg'   => $requestItem['response'],
                ];
                if ($requestItem['type'] == BaseModel::REG_TYPE_ALL) {
                    $ret['list'][$project_id]['apiList'][$api_id]['unitTestList'][$requestItem['unit_test_id']]['result'] = $success;
                }
            }

            if ($success) {
                $success_count ++;
                $ret['list'][$project_id]['success_count'] ++;
                $ret['list'][$project_id]['apiList'][$api_id]['success_count'] ++;
            }
            $ret['list'][$project_id]['total_count'] ++;
            $ret['list'][$project_id]['apiList'][$api_id]['total_count'] ++;
        }

        $ret['total_api']     = $total_api;
        $ret['total_unit']    = $total_unit;
        $ret['total_project'] = $total_project;
        $ret['success_count'] = $success_count;

        return $ret;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Api(), function (Form $form) {
        });
    }
}
