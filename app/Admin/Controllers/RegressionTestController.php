<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\RegressionTest;
use App\Models\BaseModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use App\Models\ProjectModel;
use App\Models\RegressionTestModel;
use App\Models\UnitTestModel;
use App\Models\ApiModel;

class RegressionTestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(RegressionTest::with(['project', 'api', 'unitTest']), function (Grid $grid) {
            if (!Admin::user()->isAdministrator()) {
                $project_ids = BaseModel::getProjectIds(Admin::user()->id);
                $grid->model()->whereIn('project_id', $project_ids);
            }
            $grid->model()->where(['status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');
            $grid->column('id')->sortable();
            $grid->column('project.name', '项目')->label('info');
            $grid->column('api.name', '接口名称')->label('info');
            $grid->column('unitTest.name', '测试用例')->label('info');
            $grid->column('type')->select(BaseModel::$label_reg_type, true);
            $grid->column('updated_at')->sortable();

            $grid->actions(function ($actions) {
                $actions->prepend("<a href='/admin/run/{$this->api_id}'><i title='运行' class='fa fa-paper-plane grid-action-icon'></i>&nbsp; </a>");
            });

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
                $filter->equal('project_id')->select($projectList)->width(6);
                $filter->equal('type')->select(BaseModel::$label_reg_type)->width(6);
                $filter->equal('api_id')->select($apiList)->width(6);
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new RegressionTest(), function (Show $show) {
            $show->field('id');
            $show->field('project_id');
            $show->field('api_id');
            $show->field('unit_test_id');
            $show->field('response_md5');
            $show->field('type');
            $show->field('status');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new RegressionTest(), function (Form $form) {
            if (!Admin::user()->isAdministrator()) {
                $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id');
            } else {
                $projectList = ProjectModel::getAll()->pluck('name', 'id');
            }

            $form->display('id');
            $form->select('project_id')
                ->options($projectList)
                ->required()
                ->load('api_id', "/project/api-list");
            $form->select('api_id')
                ->options([])
                ->required()
                ->load('unit_test_id', "/api/unit-test-list");
            $form->select('unit_test_id')
                ->options([])
                ->required();
            $form->text('response_md5');
            $form->text('type');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
