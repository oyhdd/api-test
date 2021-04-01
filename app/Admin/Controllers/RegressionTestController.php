<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\RegressionTest;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use App\Models\ProjectModel;
use App\Models\RegressionTestModel;
use App\Models\UnitTestModel;

class RegressionTestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new RegressionTest(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('project_id');
            $grid->column('api_id');
            $grid->column('unit_test_id');
            $grid->column('response_md5');
            $grid->column('type');
            $grid->column('status');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
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

    /**
     * 保存回归测试用例
     */
    public function saveRegTest()
    {
        $unit_test_id = $this->request->input('unit_test_id', 0);
        $type = $this->request->input('type', 0);
        $status = $this->request->input('status', 0);
        $response_md5 = $this->request->input('response_md5', '');

        $unit_test = UnitTestModel::where(['status' => UnitTestModel::STATUS_NORMAL])->findOrFail($unit_test_id)->toArray();
        $unit_test['type'] = $type;
        $unit_test['status'] = $status;
        $unit_test['response_md5'] = $response_md5;
        $unit_test['unit_test_id'] = $unit_test_id;
        $ret = RegressionTestModel::saveRegTest($unit_test);
        return $ret;
    }
}
