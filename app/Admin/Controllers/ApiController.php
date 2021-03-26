<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Api;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use App\Models\ProjectModel;
use App\Models\BaseModel;

class ApiController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Api::with(['project']), function (Grid $grid) {
            if (!Admin::user()->isAdministrator()) {
                $project_ids = BaseModel::getProjectIds(Admin::user()->id);
                $grid->model()->whereIn('project_id', $project_ids);
            }
            $grid->model()->where(['status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id')->sortable();
            $grid->column('project.name', '项目')->label('info');
            $grid->column('name')->sortable();
            $grid->column('url');
            $grid->column('method')->sortable();
            $grid->column('desc');
            $grid->column('alarm_enable')->switch()->sortable();
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->padding(0, 0, '20px')->panel();

                if (!Admin::user()->isAdministrator()) {
                    $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id');
                } else {
                    $projectList = ProjectModel::getAll()->pluck('name', 'id');
                }
                $filter->in('project_id')->multipleSelect($projectList)->width(6);
                $filter->equal('alarm_enable', '是否告警')->select(BaseModel::$label_yes_or_no)->width(6);
                $filter->like('name')->width(6);
                $filter->like('url')->width(6);
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
        return Show::make($id, Api::with(['project']), function (Show $show) {
            $show->field('id');
            $show->field('project.name', '项目');
            $show->field('name');
            $show->field('url')->label('info');
            $show->field('alarm_enable')->as(function ($alarm_enable) {
                return BaseModel::$label_yes_or_no[$alarm_enable] ?? '';
            });
            $show->field('method')->label('success');
            $show->field('desc')->textarea();
            $show->field('request_example')->textarea();
            $show->field('response_example')->textarea();
            $show->field('response_desc')->textarea();
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
        return Form::make(new Api(), function (Form $form) {

            if (!Admin::user()->isAdministrator()) {
                $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id');
            } else {
                $projectList = ProjectModel::getAll()->pluck('name', 'id');
            }

            $form->display('id');
            $form->select('project_id')->options($projectList)->required();
            $form->text('name')->required();
            $form->text('url');
            $form->switch('alarm_enable');
            $form->select('method')->options(BaseModel::$label_request_methods)->default('GET')->required();
            $form->textarea('desc');
            $form->textarea('request_example');
            $form->textarea('response_example');
            $form->textarea('response_desc');

            $form->saving(function (Form $form) {
                $form->url = "/" . ltrim($form->url ?? '', "/");
            });
            $form->footer(function ($footer) {
                $footer->disableViewCheck()->disableEditingCheck()->disableCreatingCheck();
            });
        });
    }
}
