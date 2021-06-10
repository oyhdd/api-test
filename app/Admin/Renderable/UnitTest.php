<?php

namespace App\Admin\Renderable;

use App\Admin\Controllers\AdminController;
use App\Models\ApiModel;
use App\Models\BaseModel;
use App\Admin\Repositories\UnitTest as UnitTestModel;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;

class UnitTest extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(UnitTestModel::with(['api']), function (Grid $grid) {
            $grid->model()->where(['project_id' => AdminController::getProjectId(), 'status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id', 'ID')->sortable();
            $grid->column('api.name', '接口名称');
            $grid->column('api.url', '接口地址');
            $grid->column('name', '测试用例');

            $grid->paginate(7);
            $grid->disableActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->padding(0, 0, '20px')->panel();

                $apiList = ApiModel::getAll(['project_id' => AdminController::getProjectId()]);
                $apiList = $apiList->flatMap(function ($item) {
                    $item->name .= ": " . $item->url;
                    return [$item];
                })->toArray();
                $apiList = array_column($apiList, 'name', 'id');

                $filter->equal('api_id', '接口名称')->select($apiList)->width(6);
                $filter->like('name', '测试用例')->width(6);
            });
            $grid->footer(function ($collection) {
                return '<span class="text-orange-2">备注：需要在 <a href="/admin/run" target="_blank">"接口调试"</a> 界面将测试用例运行结果加入到回归测试后，才能在本页面显示出来</span>'; 
            });
        });
    }
}