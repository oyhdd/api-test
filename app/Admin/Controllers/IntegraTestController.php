<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\IntegraTest;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;

/**
 * 集成测试
 */
class IntegraTestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new IntegraTest(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('project_id');
            $grid->column('parent_id');
            $grid->column('reg_test_id');
            $grid->column('name');
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
        return Show::make($id, new IntegraTest(), function (Show $show) {
            $show->field('id');
            $show->field('project_id');
            $show->field('parent_id');
            $show->field('reg_test_id');
            $show->field('name');
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
        return Form::make(new IntegraTest(), function (Form $form) {
            $form->display('id');
            $form->text('project_id');
            $form->text('parent_id');
            $form->text('reg_test_id');
            $form->text('name');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
