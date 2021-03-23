<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\UnitTest;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UnitTestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UnitTest(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('project_id');
            $grid->column('api_id');
            $grid->column('name');
            $grid->column('header');
            $grid->column('body');
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
        return Show::make($id, new UnitTest(), function (Show $show) {
            $show->field('id');
            $show->field('project_id');
            $show->field('api_id');
            $show->field('name');
            $show->field('header');
            $show->field('body');
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
        return Form::make(new UnitTest(), function (Form $form) {
            $form->display('id');
            $form->text('project_id');
            $form->text('api_id');
            $form->text('name');
            $form->text('header');
            $form->text('body');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
