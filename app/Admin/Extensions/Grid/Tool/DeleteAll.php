<?php

namespace App\Admin\Extensions\Grid\Tool;

use App\Models\LogCrontabModel;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;

class DeleteAll extends AbstractTool
{
    /**
     * 按钮样式定义，默认 btn btn-white waves-effect
     * 
     * @var string 
     */
    protected $style = 'btn btn-danger waves-effect';


    public function title()
    {
        return '全部删除';
    }

    public function confirm()
    {
        return '确认删除当前任务的所有日志？';
    }

    /**
     * 处理请求
     * 如果你的类中包含了此方法，则点击按钮后会自动向后端发起ajax请求，并且会通过此方法处理请求逻辑
     * 
     * @param Request $request
     */
    public function handle(Request $request)
    {
        $crontab_id = $request->get('crontab_id', 0);
        if (empty($crontab_id)) {
            return $this->response()->error('删除失败，请指定计划任务')->refresh();
        }

        if (LogCrontabModel::where(['crontab_id' => $crontab_id])->delete()) {
            return $this->response()->success('删除成功')->refresh();
        }
        return $this->response()->error('删除失败')->refresh();
    }

    /**
     * 设置请求参数
     * 
     * @return array|void
     */
    public function parameters()
    {
        return [
            'crontab_id' => request()->get('crontab_id', 0)
        ];
    }
}