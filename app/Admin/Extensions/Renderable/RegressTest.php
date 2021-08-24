<?php

namespace App\Admin\Extensions\Renderable;

use App\Admin\Controllers\AdminController;
use App\Admin\Repositories\RegressTest as RegressTestRep;
use App\Models\ApiModel;
use App\Models\BaseModel;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;

class RegressTest extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(RegressTestRep::with(['api', 'unitTest']), function (Grid $grid) {
            $domain = $this->payload['domain'] ?? '';
            $grid->model()->where(['project_id' => AdminController::getProjectId(), 'domain' => $domain, 'status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id', 'ID')->sortable();
            $grid->column('api.name', '接口名称');
            $grid->column('api.url', '接口地址')->display(function($url) {
                $class = 'bg-success';
                if (strtoupper($this->api->method) == 'POST') {
                    $class = 'bg-custom';
                }
                return "<span class='label {$class}'>{$this->api->method}</span> &nbsp;" . $url;
            });
            $grid->column('unitTest.name', '测试用例');
            $grid->column('unitTest.type', '回归模式')->display(function () {
                return BaseModel::$label_reg_type[$this->type] ?? '';
            });

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
                return '<span >备注：需要在 <a href="/admin/run" target="_blank"> 接口调试 </a> 界面添加回归测试用例后，才能在本页面显示出来</span>'; 
            });
        });
    }
}