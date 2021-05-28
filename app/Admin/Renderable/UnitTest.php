<?php

namespace App\Admin\Renderable;

use App\Models\ApiModel;
use App\Models\BaseModel;
use App\Models\ProjectModel;
use App\Admin\Repositories\UnitTest as UnitTestModel;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;

class UnitTest extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(UnitTestModel::with(['project', 'api']), function (Grid $grid) {
            if (!Admin::user()->isAdministrator()) {
                $project_ids = BaseModel::getProjectIds(Admin::user()->id);
            } else {
                $project_ids = ProjectModel::getAll()->pluck('id');
            }
            $grid->model()->whereIn('project_id', $project_ids)->where(['status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id', 'ID')->sortable();
            $grid->column('project.name', '项目');
            $grid->column('api.name', '接口名称');
            $grid->column('api.url', '接口地址');
            $grid->column('name', '测试用例');

            $grid->paginate(8);
            $grid->disableActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->padding(0, 0, '20px')->panel();

                if (!Admin::user()->isAdministrator()) {
                    $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id');
                    $apiList = ApiModel::getApiList(Admin::user()->id);
                } else {
                    $projectList = ProjectModel::getAll()->pluck('name', 'id');
                    $apiList = ApiModel::getAll();
                }
                $apiList = $apiList->flatMap(function ($item) {
                    $item->name .= ": " . $item->url;
                    return [$item];
                })->toArray();
                $apiList = array_column($apiList, 'name', 'id');
                $filter->equal('project_id', '项目')->select($projectList)->width(6);
                $filter->equal('api_id', '接口名称')->select($apiList)->width(6);
                $filter->like('name', '测试用例')->width(6);
            });
        });
    }
}