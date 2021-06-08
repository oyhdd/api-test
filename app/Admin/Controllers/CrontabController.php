<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\UnitTest;
use App\Admin\Repositories\Crontab;
use App\Admin\Renderable\IntegrationTest;
use App\Models\BaseModel;
use App\Models\IntegrationTestModel;
use App\Models\ProjectModel;
use App\Models\UnitTestModel;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Widgets\Table;

class CrontabController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Crontab(), function (Grid $grid) {
            $grid->model()->where(['project_id' => self::getProjectId(), 'status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id')->sortable();
            $grid->column('title')->sortable();
            $grid->column('desc')->limit(40);
            $grid->column('task_type')->display(function($task_type) {
                return BaseModel::$label_task_type[$task_type] ?? '';
            });
            $grid->column('crontab');
            $grid->column('last_time')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->padding(0, 0, '20px')->panel();

                $filter->like('title')->width(6);
                $filter->equal('task_type')->select(BaseModel::$label_task_type)->width(6);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Crontab(), function (Form $form) {

            $form->display('id');
            $form->select('project_id')->options(ProjectModel::getAll()->pluck('name', 'id'))->default(self::getProjectId())->disable();
            $form->text('title');
            $form->textarea('desc');

            $form->select('task_type')
            ->when(BaseModel::TASK_TYPE_UNIT_TEST, function (Form $form) {
                // 异步加载测试用例
                $form->multipleSelectTable('task_value')
                ->title('选择测试用例')
                ->from(UnitTest::make(['id' => $form->getKey()]))
                ->model(UnitTestModel::class, 'id', 'name');
            })
            ->when(BaseModel::TASK_TYPE_INTEGRATION_TEST, function (Form $form) {
                // 异步加载集成测试
                $form->multipleSelectTable('task_value_integration_test', '任务Id')
                ->title('选择集成测试')
                ->from(IntegrationTest::make())
                ->model(IntegrationTestModel::class, 'id', 'name');
            })
            ->options(BaseModel::$label_task_type)->default(BaseModel::TASK_TYPE_UNIT_TEST)->required();

            $form->text('crontab')->default('* * * * *')->help('格式：* * * * * （minute hour day month week）');

            $form->submitted(function (Form $form) {
                if ($form->task_type == BaseModel::TASK_TYPE_INTEGRATION_TEST) {
                    $task_value = $form->task_value_integration_test;
                    $form->input('task_value', $task_value);
                }
                if ($form->isCreating()) {
                    $form->project_id = self::getProjectId();
                }
                $form->deleteInput('task_value_integration_test');
            });

            $form->fieldset('crontab 参数说明', function ($form) {
                $form->html($this->getCrontabDesc());
            })->collapsed();

            $form->display('created_at');
            $form->display('updated_at');

            $form->disableViewButton()->disableCreatingCheck()->disableViewCheck()->disableEditingCheck();
        });
    }

    protected function getCrontabDesc()
    {
        $headers = ['key' => '参数', 'desc' => '说明'];

        $body = [
            ['key' => '*', 'desc' => '所有可能的值'],
            ['key' => ',', 'desc' => '满足条件的多个值'],
            ['key' => '-', 'desc' => '区间范围'],
            ['key' => '/', 'desc' => '间隔时间'],
            ['key' => 'minute', 'desc' => '分钟：0-59'],
            ['key' => 'hour', 'desc' => '小时：0-23'],
            ['key' => 'day', 'desc' => '日期：1-23'],
            ['key' => 'month', 'desc' => '月份：1-12'],
            ['key' => 'week', 'desc' => '星期：0-6'],
        ];

        $table = new Table($headers, $body);
        return $table->render();
    }
}
