<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Admin;
use App\Models\ApiModel;
use Dcat\Admin\Layout\Content;

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

        return [
            'status'  => true,
            'data' => [
                'message' => '请求成功',
            ],
            'result' => $this->request->all(),
            'detail' => [
                'status_code' => 200,
                'request_time' => 500,
                'curl_example' => "curl -X POST http://121.199.40.77/test1 -H 'token: 1' -F str=1 -F number=1 -F arr[]=1 -F arr[]=1",
            ]
        ];
    }

}
