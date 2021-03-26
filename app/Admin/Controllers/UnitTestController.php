<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\{UnitTest, Api};
use App\Models\ApiModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Layout\Column;
use App\Models\BaseModel;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

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
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description()['create'] ?? trans('admin.create'))
            ->row(function (Row $row) {
                $row->column(5, function (Column $column) {
                    $column->append($this->apiDetail(1));
                });
                $row->column(7, function (Column $column) {
                    $column->append($this->form());
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
        return Form::make(new UnitTest(), function (Form $form) {
            $form->display('id');
            $form->text('project_id');
            $form->text('api_id');
            $form->text('name');
            $form->text('header');
            $form->text('body');
            $form->display('created_at');
            $form->display('updated_at');
        });
    }

    /**
     * Make a Api show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function apiDetail($api_id = 0)
    {
        return Show::make($api_id, Api::with(['project']), function (Show $show) {
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

            $show->panel()->title('接口信息')->tools(function ($tools) {
                $tools->disableEdit()->disableList()->disableDelete();
            });

            $show->setWidth(9);

            \Dcat\Admin\Admin::script("$(\"select[name='api_id']\").change(function () {
                $.pjax.reload({
                    url: '/admin/unit_test/create?api_id='+$(\"select[name='api_id']\").val(),
                    container: '#api_refresh'
                });
            });");
            // return "<div id='api_refresh'>" . $show->render() . "</div>";
        });
    }
}
