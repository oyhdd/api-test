<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\UnitTest;
use App\Models\{ApiModel, UnitTestModel, ProjectModel, BaseModel};
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Admin;

class UnitTestController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(UnitTest::with(['project', 'api']), function (Grid $grid) {
            if (!Admin::user()->isAdministrator()) {
                $project_ids = BaseModel::getProjectIds(Admin::user()->id);
                $grid->model()->whereIn('project_id', $project_ids);
            }
            $grid->model()->where(['status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id')->sortable();
            $grid->column('project.name', '项目')->label('info');
            $grid->column('api.name', '接口名称')->label('info');
            $grid->column('name')->sortable();
            $grid->column('api.method', '请求方法')->label();
            $grid->column('api.url', '接口地址');
            $grid->column('updated_at')->sortable();

            $grid->actions(function ($actions) {
                $actions->prepend("<a href='/admin/api/run/{$this->api_id}'><i title='运行' class='fa fa-paper-plane grid-action-icon'></i>&nbsp; </a>");
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->padding(0, 0, '20px')->panel();

                if (!Admin::user()->isAdministrator()) {
                    $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id');
                    $apiList = ApiModel::getApiList(Admin::user()->id);
                } else {
                    $projectList = ProjectModel::getAll()->pluck('name', 'id');
                    $apiList = ApiModel::getAll();
                }
                $apiList = $apiList->flatMap(function ($item) {
                    $item->name .= ": " . $item->url;
                    return [$item];
                })->toArray();
                $apiList = array_column($apiList, 'name', 'id');
                $filter->equal('project_id')->select($projectList)->width(6);
                $filter->equal('api_id')->select($apiList)->width(6);
                $filter->like('name')->width(6);
                $filter->equal('api.alarm_enable', '是否告警')->select(BaseModel::$label_yes_or_no)->width(6);
            });
        });
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description()['show'] ?? trans('admin.show'))
            ->row(function (Row $row) use ($id) {
                $row->column(6, function (Column $column) use ($id)  {
                    $model = UnitTestModel::where(['status' => UnitTestModel::STATUS_NORMAL])->findOrFail($id);
                    $column->append($this->apiDetail($model->api_id));
                });
                $row->column(6, function (Column $column) use ($id)  {
                    $column->append($this->detail($id));
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
        return Show::make($id, UnitTest::with(['project', 'api']), function (Show $show) {
            $show->field('id');
            $show->field('project.name', '项目')->label('info');
            $show->field('name');
            $show->header()->as(function ($header) {
                return BaseModel::getParamTable($header);
            })->unescape();
            $show->body()->as(function ($body) {
                return BaseModel::getParamTable($body);
            })->unescape();
            $show->field('created_at');
            $show->field('updated_at');

            $show->panel()->title('测试用例详情');
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
            ->row(function (Row $row){
                $row->column(5, function (Column $column){
                    $column->append($this->apiDetail());
                });
                $row->column(7, function (Column $column) {
                    $column->append($this->form());
                });
            });
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description()['edit'] ?? trans('admin.edit'))
            ->row(function (Row $row) use ($id) {
                $row->column(5, function (Column $column) {
                    $column->append($this->apiDetail());
                });
                $row->column(7, function (Column $column) use ($id) {
                    $column->append($this->form()->edit($id));
                });
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = 0)
    {
        return Form::make(new UnitTest(), function (Form $form) use ($id) {
            if (!Admin::user()->isAdministrator()) {
                $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id');
            } else {
                $projectList = ProjectModel::getAll()->pluck('name', 'id');
            }
            $form->display('id');

            if ($id <= 0) {
                $form->select('project_id', '项目')
                    ->options($projectList)
                    ->required()
                    ->load('api_id', "/project/api-list");
                $form->select('api_id', '接口')->options([])->required();
            }

            $form->text('name')->required();
            $form->fieldset('参数设置', function ($form) {
                $form->table('header', function ($table) {
                    $table->text('key', '参数名')->required();
                    $table->text('value', '参数值')->required();
                })->saving(function ($v) {
                    return json_encode($v);
                });
                $form->table('body', function ($table) {
                    $table->text('key', '参数名')->required();
                    $table->text('value', '参数值')->required();
                })->saving(function ($v) {
                    return json_encode($v);
                });
            });
            $form->width(10, 2);
            $form->footer(function ($footer) {
                $footer->disableViewCheck()->disableEditingCheck()->disableCreatingCheck();
            });

            Admin::script("$(\"select[name='api_id']\").change(function () {
                var api_id = $(\"select[name='api_id']\").val();
                if (api_id != null) {
                    console.log(api_id)
                    $.pjax.reload({
                        url: '/admin/unit-test/api-detail/'+ api_id,
                        container: '#api_refresh'
                    });
                }
            });");
        });
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function apiDetail($api_id = 0)
    {
        if (empty($api_id)) {
            $model = new Show();
        } else {
            $model = ApiModel::find($api_id);
        }

        $show = Show::make($model, function (Show $show) {
            $show->field('name', '接口名称')->label()->width(10);
            $show->field('url')->label()->width(10);
            $show->field('alarm_enable')->as(function ($alarm_enable) {
                return BaseModel::$label_yes_or_no[$alarm_enable] ?? '';
            })->width(10);
            $show->field('method')->label('success')->width(10);
            $show->field('desc')->textarea()->width(10);
            $show->header('请求头')->as(function ($header) {
                return BaseModel::getParamTable($header);
            })->unescape()->width(10);
            $show->body('请求体')->as(function ($body) {
                return BaseModel::getParamTable($body);
            })->unescape()->width(10);
            $show->field('request_example')->textarea()->width(10);
            $show->field('response_example')->textarea()->width(10);
            $show->field('response_desc')->textarea()->width(10);
        });

        $show->panel()->title('接口信息')->tools(function ($tools) {
            $tools->disableEdit()->disableList()->disableDelete();
        });

        return "<div id='api_refresh'>" . $show->render() . "</div>";
    }

}
