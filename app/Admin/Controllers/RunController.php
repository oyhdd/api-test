<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Api;
use Dcat\Admin\Admin;
use App\Models\ApiModel;
use App\Models\CrontabModel;
use App\Models\ProjectModel;
use App\Models\RegressionTestModel;
use Dcat\Admin\Form;
use Dcat\Admin\Layout\Content;
use GuzzleHttp\Client;
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
            ->row(view('run.index', ['model' => $model]));
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
        $project_id = AdminController::getProjectId();
        $domain_key = $this->request->input('domain');
        if (empty($domain_key)) {
            return response()->json(['message' => '请选择运行环境'], 400);
        }
        $domain = ProjectModel::getDomainByKey($project_id, $domain_key);
        $api_ids = $this->request->input('api_ids', '');
        if (empty($api_ids)) {
            return response()->json(['message' => '请选择回归用例'], 400);
        }
        $api_ids = explode(',', $api_ids);

        $testModel = RegressionTestModel::with(['api', 'unitTest'])->whereIn('api_id', $api_ids)->where(['domain' => $domain_key, 'status' => RegressionTestModel::STATUS_NORMAL])->get();

        $requestData = [];
        foreach ($testModel as $regTest) {
            if (empty($regTest->toArray())) {
                continue;
            }
            if ($regTest->domain != $domain_key) {
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
        }
        $ret = CrontabModel::sendRequest($requestData);
        if (!empty($ret)) {
            $ret = $ret[$project_id];
            $ret['domain_env'] = $domain_key;
            $ret['domain'] = $domain;
            $ret['project_name'] = (AdminController::getProject())->name;
            return view('log_crontab.unit_test_modal', ['id' => $project_id, 'data' => $ret, 'expand' => true]);
        }

        return response()->json(['message' => '运行失败！请联系管理员'], 400);
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

    /**
     * 一键回归测试
     * @author   wangmeng
     * @dateTime 2021-08-10
     * @param Content $content
     */
    public function regressTest(Content $content)
    {
        $domain = $this->request->input('domain', '');
        $api_ids = $this->request->input('api_ids', '');
        $api_ids = explode(',', $api_ids);

        return $content
            ->title('回归测试')
            ->description('请先将 <a href="/admin/run" target="_blank">调试结果</a> 保存至回归测试中，方可选择回归用例')
            ->breadcrumb(
                ['text' => '接口调试', 'url' => '/run'],
                ['text' => '回归测试']
            )
            ->body(view('run.regression-test', [
                'project_id' => self::getProjectId(),
                'domain' => $domain,
                'api_ids' => $api_ids,
            ]));
    }
}