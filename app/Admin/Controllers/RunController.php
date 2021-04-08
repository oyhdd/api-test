<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Admin;
use App\Models\ApiModel;
use App\Models\ProjectModel;
use Dcat\Admin\Layout\Content;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Exception\RequestException;

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
        if (!Admin::user()->isAdministrator()) {
            $apiList = ApiModel::getApiList(Admin::user()->id)->pluck('id')->toArray();
        } else {
            $project_ids = ProjectModel::getAll()->pluck('id');
            $apiList = ApiModel::getAll()->whereIn('project_id', $project_ids)->pluck('id')->toArray();
        }
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
        Admin::js([
            '/js/jquery.md5.js',
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
        $domain = $this->request->input('domain');
        $url = $this->request->input('url');
        $method = $this->request->input('method');
        $header = $this->request->input('header', []);
        $body = $this->request->input('body', []);
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
        $auth = $this->request->header('Authorization');
        $ret = [
            'code' => -1,
            'message' => '回归测试失败，请重试！',
            'data' => [],
        ];
dd($this->request->all());
        $list = ApiModel::getRegressList();
        dd($list);
        $url = $this->request->get('url');
        $apiDocModels = ApiDoc::getAll($url);
        $apiDocModels = array_column($apiDocModels, null, 'id');
        $apiDocIds = array_keys($apiDocModels);
        $apiParamsModels = ApiDocParams::getByApiIds($apiDocIds);

        foreach ($apiDocModels as $apiId => $apiDoc) {
            if ($apiDoc['regression_test'] != ApiDoc::STATUS_REG_TEST_YES) {
                unset($apiDocModels[$apiId]);
            } else {
                $apiDocModels[$apiId]['api_params'] = empty($apiParamsModels[$apiId]) ? [] : $apiParamsModels[$apiId];
                $unitTestList[$apiId] = $apiDoc;
            }
        }
        $ret['data'] = $this->sendRequest($apiDocModels, $auth);
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
     * @param  array        $list               list
     * @param  string       $auth               网站auth认证
     * @param  integer      $timeOut            超时限制60s
     * @return false|array
     */
    public static function sendRequest($list = [], $auth = null, $timeOut = 120)
    {
        $requestData = [];
        $total_api = $total_unit = $success_count = $fail_count = 0;
        $client = new Client(['timeout' => $timeOut]);
        foreach ($list as $apiId => $api) {
            if (in_array($api['method'], ["GET", "get"])) {
                foreach ($api['api_params'] as $key => $apiParams) {
                    $body = json_decode($apiParams['body'], true);
                    $header = json_decode($apiParams['header'], true);
                    if (empty($body)) {
                        $body = [];
                    }
                    if (empty($header)) {
                        $header = [];
                    }
                    foreach ($body as $key1 => $value) {
                        if (is_array($value) && substr($key1, -2) == '[]') {
                            unset($body[$key1]);
                            $body[substr($key1, 0, -2)] = $value;
                        }
                    }
                    if (!empty($auth)) {
                        $header['Authorization'] = $auth;
                    }
                    $url = $api['url'];
                    if (preg_match_all('/(\/{.*})/', $url, $matches) && !empty($matches[1])) {
                        foreach ($body as $p_key => $p_value) {
                            $url = str_replace('{' . $p_key . '}', $p_value, $url);
                        }
                    }
                    $requestData[] = [
                        'url' => $url . "?" . http_build_query($body),
                        'headers' => $header,
                        'key' => $key,
                        'api_id' => $apiId,
                        'method' => "GET",
                        'regression_model' => $api["regression_model"],
                    ];
                    $total_unit++;
                }
            } elseif (in_array($api['method'], ["POST", "post"])) {
                foreach ($api['api_params'] as $key => $apiParams) {
                    $body = json_decode($apiParams['body'], true);
                    $header = json_decode($apiParams['header'], true);
                    if (empty($body)) {
                        $body = [];
                    }
                    if (empty($header)) {
                        $header = [];
                    }
                    foreach ($body as $key1 => $value) {
                        if (is_array($value) && substr($key1, -2) == '[]') {
                            unset($body[$key1]);
                            $body[substr($key1, 0, -2)] = $value;
                        }
                    }

                    if (!empty($auth)) {
                        $header['Authorization'] = $auth;
                    }

                    $url = $api['url'];
                    if (preg_match_all('/(\/{.*})/', $url, $matches) && !empty($matches[1])) {
                        foreach ($body as $p_key => $p_value) {
                            $url = str_replace('{' . $p_key . '}', $p_value, $url);
                        }
                    }
                    $requestData[] = [
                        'url' => $url,
                        'form_params' => $body,
                        'headers' => $header,
                        'key' => $key,
                        'api_id' => $apiId,
                        'method' => "POST",
                        'regression_model' => $api["regression_model"],
                    ];
                    $total_unit++;
                }
            } else {
                return false;
            }
            $total_api++;
        }

        $requests = function ($params) use ($client) {
            if (!empty($params)) {
                foreach ($params as $key => $param) {
                    if ($param['method'] == "GET") {
                        yield new GuzzleRequest('GET', $param['url'], $param['headers']);
                    } elseif ($param['method'] == "POST") {
                        yield function () use ($client, $param) {
                            return $client->requestAsync('post', $param['url'], [
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
            'concurrency' => 20,
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
            $api_id = $requestData[$key]['api_id'];
            $index = $requestData[$key]['key'];
            $success = false;
            if ($response['success']) {
                //完全匹配
                if ($requestData[$key]['regression_model'] == ApiDoc::MODEL_REG_STRCIT) {
                    $success = (md5(stripslashes(trim($response['response']))) == $list[$api_id]['api_params'][$index]['response_md5']);
                } elseif ($requestData[$key]['regression_model'] == ApiDoc::MODEL_REG_REQUEST) {
                    $success = true;
                }
            }
            $data = [
                'id' => $list[$api_id]['api_params'][$index]['id'],
                'success' => $success,
                'test_title' => $list[$api_id]['api_params'][$index]['test_title'],
                'response' => json_decode($response['response'], true)
            ];

            if (!isset($ret['list'][$api_id]['fail_count'])) {
                $ret['list'][$api_id]['fail_count'] = 0;
            }
            if ($success) {
                $success_count++;
            } else {
                $ret['list'][$api_id]['fail_count']++;
                $fail_count++;
            }

            $ret['list'][$api_id]['method'] = $requestData[$key]['method'];
            $ret['list'][$api_id]['title'] = $list[$api_id]['title'];
            $ret['list'][$api_id]['url'] = $list[$api_id]['url'];
            $ret['list'][$api_id]['list'][] = $data;
        }

        $ret['total_api'] = $total_api;
        $ret['total_unit'] = $total_unit;
        $ret['success_count'] = $success_count;
        $ret['fail_count'] = $fail_count;

        return $ret;
    }
}
