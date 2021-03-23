<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Project;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ProjectController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Project(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('intro');
            $grid->column('alarm_enable');
            $grid->column('alarm_param');
            $grid->column('domain_text');
            $grid->column('domain_prod');
            $grid->column('owner_uid');
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
        return Show::make($id, new Project(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('intro');
            $show->field('alarm_enable');
            $show->field('alarm_param');
            $show->field('domain_text');
            $show->field('domain_prod');
            $show->field('owner_uid');
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
        return Form::make(new Project(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('intro');
            $form->text('alarm_enable');
            $form->text('alarm_param');
            $form->text('domain_text');
            $form->text('domain_prod');
            $form->text('owner_uid');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
