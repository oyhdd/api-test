<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Api;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

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
            $grid->column('id')->sortable();
            $grid->column('project_id');
            $grid->column('name');
            $grid->column('url');
            $grid->column('method');
            $grid->column('desc');
            $grid->column('request_example');
            $grid->column('response_example');
            $grid->column('response_desc');
            $grid->column('alarm_enable');
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
        return Show::make($id, new Api(), function (Show $show) {
            $show->field('id');
            $show->field('project_id');
            $show->field('name');
            $show->field('url');
            $show->field('method');
            $show->field('desc');
            $show->field('request_example');
            $show->field('response_example');
            $show->field('response_desc');
            $show->field('alarm_enable');
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
        return Form::make(new Api(), function (Form $form) {
            $form->display('id');
            $form->text('project_id');
            $form->text('name');
            $form->text('url');
            $form->text('method');
            $form->text('desc');
            $form->text('request_example');
            $form->text('response_example');
            $form->text('response_desc');
            $form->text('alarm_enable');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
