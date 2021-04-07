<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Admin;
use App\Models\ApiModel;
use Dcat\Admin\Layout\Content;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class RunController extends AdminController
{

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
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
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
                $url .= '?' . http_build_query($body);
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

}
