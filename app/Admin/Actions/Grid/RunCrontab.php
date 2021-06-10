<?php

namespace App\Admin\Actions\Grid;

use Dcat\Admin\Grid\RowAction;
use App\Models\CrontabModel;
use Illuminate\Http\Request;

class RunCrontab extends RowAction
{
    /**
     * @return string
     */
    protected $title = "运行";


    /**
     * 处理请求
     *
     * @param Request $request
     *
     * @return \Dcat\Admin\Actions\Response
     */
    public function handle(Request $request)
    {
        $crontab_id = $this->getKey();

        if (CrontabModel::runCrontab($crontab_id)) {
            return $this->response()->success("执行成功")->refresh();
        }

        return $this->response()->error("执行失败")->refresh();
    }

    public function parameters()
    {
        return [
            'id' => $this->row->id,
        ];
    }
}