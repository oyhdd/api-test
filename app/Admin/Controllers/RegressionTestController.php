<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\RegressionTest;
use App\Models\BaseModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use App\Models\ProjectModel;
use App\Models\ApiModel;

/**
 * 回归测试
 */
class RegressionTestController extends AdminController
{
    protected $description = [
        'create' => '建议从接口调试界面添加',
    ];
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(RegressionTest::with(['api', 'unitTest']), function (Grid $grid) {
            $grid->model()->where(['project_id' => self::getProjectId(), 'status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id')->sortable();
            $grid->column('api.name', '接口名称')->link(function () {
                return admin_url('api/' . $this->api_id);
            })->label('info');
            $grid->column('unitTest.name', '测试用例')->link(function () {
                return admin_url('unit-test/' . $this->unit_test_id . "/edit");
            });
            $grid->column('type')->display(function () {
                return BaseModel::$label_reg_type[$this->type] ?? '';
            });
            $grid->column('ignore_fields');
            $grid->column('domain')->limit(60);
            $grid->column('updated_at')->sortable();

            $grid->actions(function ($actions) {
                $actions->prepend("<a href='/admin/run/{$this->api_id}'><i title='运行' class='fa fa-paper-plane grid-action-icon'></i>&nbsp; </a>");
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->padding(0, 0, '20px')->panel();

                $apiList = ApiModel::getAll(['project_id' => self::getProjectId()]);
                $apiList = $apiList->flatMap(function ($item) {
                    $item->name .= ": " . $item->url;
                    return [$item];
                })->toArray();
                // $domainList = ProjectModel::getAll(['id' => self::getProjectId()], ['domain_prod', 'domain_text'])->toArray();

                $apiList = array_column($apiList, 'name', 'id');
                $filter->equal('api_id')->select($apiList)->width(4);
                // $filter->equal('domain')->select($domainList)->width(4);
                $filter->equal('type')->select(BaseModel::$label_reg_type)->width(4);
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
        return Show::make($id, RegressionTest::with(['project', 'api', 'unitTest']), function (Show $show) {
            $show->field('id');
            $show->field('project.name', '项目');
            $show->field('api.name', '接口名称');
            $show->field('api.url', '接口地址');
            $show->field('api.desc', '接口描述');
            $show->field('unit_test.name', '测试用例')->link('/admin/unit-test/'. $show->model()->unit_test_id)->label('info');
            $show->field('response_md5');
            $show->field('type')->as(function ($type) {
                return BaseModel::$label_reg_type[$type] ?? '';
            });
            $show->field('ignore_fields');
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
            $form->display('id');
            $form->select('project_id')
                ->options(ProjectModel::getAll()->pluck('name', 'id'))
                ->default(self::getProjectId())
                ->disable()
                ->load('api_id', "/project/api-list");
            $form->select('api_id')
                ->options([])
                ->required()
                ->load('unit_test_id', "/api/unit-test-list");
            $form->select('unit_test_id')
                ->options([])
                ->required();
            $form->text('response_md5');
            $form->select('type')->options(BaseModel::$label_reg_type);
            $form->text('ignore_fields')->help('匹配时忽略字段：多个字段使用英文逗号分隔');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
