<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Api;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use App\Models\{UnitTestModel, ProjectModel, BaseModel};
use App\Admin\Actions\Grid\CopyApi;

class ApiController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Api(), function (Grid $grid) {
            $grid->model()->where(['project_id' => self::getProjectId(), 'status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id')->sortable();
            $grid->column('name')->sortable();
            $grid->column('url');
            $grid->column('method')->sortable();
            $grid->column('desc')->limit(20);
            $grid->column('alarm_enable')->switch()->sortable();
            $grid->column('updated_at')->sortable();

            $grid->actions(function ($actions) {
                $actions->prepend("&nbsp; <a href='/admin/run/{$this->getKey()}'><i title='运行' class='fa fa-paper-plane grid-action-icon'></i>&nbsp; </a>");
                $actions->prepend(new CopyApi());
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->padding(0, 0, '20px')->panel();
                $filter->like('name')->width(6);
                $filter->equal('alarm_enable', '是否告警')->select(BaseModel::$label_yes_or_no)->width(6);
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
            $show->header()->as(function ($header) {
                return $this->getParamTable($header);
            })->unescape();
            $show->body()->as(function ($body) {
                return $this->getParamTable($body);
            })->unescape();
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

            $form->display('id');
            $form->select('project_id')->options(ProjectModel::getAll()->pluck('name', 'id'))->default(self::getProjectId())->disable();
            $form->text('name')->required();
            $form->text('url');
            $form->switch('alarm_enable');
            $form->select('method')->options(BaseModel::$label_request_methods)->default('GET')->required();
            $form->textarea('desc');
            $form->fieldset('参数设置', function ($form) {
                $form->table('header', function ($table) {
                    $table->text('key', '参数名')->required();
                    $table->text('type', '参数类型')->default('string');
                    $table->radio('is_necessary', '是否必填')->options(BaseModel::$label_yes_or_no)->default(BaseModel::NO);
                    $table->text('desc', '参数说明');
                })->saving(function ($v) {
                    return json_encode($v);
                });
                $form->table('body', function ($table) {
                    $table->text('key', '参数名')->required();
                    $table->text('type', '参数类型')->default('string');
                    $table->radio('is_necessary', '是否必填')->options(BaseModel::$label_yes_or_no)->default(BaseModel::NO);
                    $table->text('desc', '参数说明');
                })->saving(function ($v) {
                    return json_encode($v);
                });
            });
            $form->textarea('request_example');
            $form->textarea('response_example');
            $form->textarea('response_desc');

            $form->width(9, 2);

            $form->saving(function (Form $form) {
                $form->url = "/" . ltrim($form->url ?? '', "/");
            });
            $form->footer(function ($footer) {
                $footer->disableViewCheck()->disableEditingCheck()->disableCreatingCheck();
            });
        });
    }

    public function unitTestList()
    {
        $api_id = intval($this->request->input('q', 0));
        if ($api_id <= 0) {
            return [];
        }

        $unitTest = UnitTestModel::getAll(['api_id' => $api_id])->toArray();
        $ret = [];
        foreach ($unitTest as $key => $unit) {
            $ret[$key] = [
                'id' => $unit['id'],
                'text' => $unit['name'],
            ];
        }
        return $ret;
    }

}
