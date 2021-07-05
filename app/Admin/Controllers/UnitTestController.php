<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\UnitTest;
use App\Models\{ApiModel, UnitTestModel, ProjectModel, BaseModel, RegressionTestModel};
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Admin;
use Illuminate\Support\Facades\DB;

/**
 * 接口管理-测试用例
 */
class UnitTestController extends AdminController
{
    protected $description = [
        'index' => '可以在 "接口调试" 界面进行管理',
        'edit' => '可以在 "接口调试" 界面进行管理',
    ];
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(UnitTest::with(['project', 'api']), function (Grid $grid) {
            $grid->model()->where(['project_id' => self::getProjectId(), 'status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id')->sortable();
            $grid->column('api.name', '接口名称')->link(function () {
                return admin_url('api/' . $this->api_id);
            });
            $grid->column('name')->sortable();
            $grid->column('api.url', '接口地址')->display(function($url) {
                $class = 'bg-success';
                if (strtoupper($this->api->method) == 'POST') {
                    $class = 'bg-custom';
                }
                return "<span class='label {$class}'>{$this->api->method}</span> &nbsp;" . $url;
            });
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
                $apiList = array_column($apiList, 'name', 'id');
                $filter->equal('api_id')->select($apiList)->width(6);
                $filter->like('name')->width(6);
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
        $this->hasPermission($id);
        return $content
            ->title($this->title())
            ->description($this->description()['show'] ?? trans('admin.show'))
            ->row(function (Row $row) use ($id) {
                $row->column(6, function (Column $column) use ($id) {
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
            $show->field('id')->width(10);
            $show->field('project.name', '项目')->label('info')->width(10);
            $show->field('name')->width(10);
            $show->header()->as(function ($header) {
                return BaseModel::getParamTable($this->formatTableData($header));
            })->unescape()->width(10);
            $show->body()->as(function ($body) {
                return BaseModel::getParamTable($this->formatTableData($body));
            })->unescape()->width(10);
            $show->field('created_at')->width(10);
            $show->field('updated_at')->width(10);

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
        $this->hasPermission($id);
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
            $form->display('id');

            if ($id <= 0) {
                $form->select('project_id', '项目')
                    ->options(ProjectModel::getAll()->pluck('name', 'id'))
                    ->default(self::getProjectId())
                    ->disable()
                    ->load('api_id', "/project/api-list");
                $form->select('api_id', '接口')->options([])->required();
            }

            $form->text('name')->required();
            $form->keyValue('header')->saveAsJson();
            $form->keyValue('body')->saveAsJson();

            $form->width(10, 2);
            $form->footer(function ($footer) {
                $footer->disableViewCheck()->disableEditingCheck()->disableCreatingCheck();
            });

            Admin::script("$(\"select[name='api_id']\").change(function () {
                var api_id = $(\"select[name='api_id']\").val();
                if (api_id != null) {
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

    public function save()
    {
        $ret = [
            'status'  => false,
            'data' => [
                'message' => "保存测试用例失败",
            ]
        ];

        $params = $this->request->all();
        $params['ignore_fields'] = $params['ignore_fields'] ?? '';
        $ignoreFields = explode(',', $params['ignore_fields']);
        $apiResponse = json_decode($params['api_response'], true);
        foreach ($ignoreFields as $ignore_field) {
            if (!empty($ignore_field) && isset($apiResponse[$ignore_field])) {
                unset($apiResponse[$ignore_field]);
            }
        }
        $apiResponse = json_encode($apiResponse);
        $params['response'] = trim($apiResponse);

        $model = UnitTestModel::saveUnitTest($params);
        if (empty($model)) {
            return $ret;
        }

        $params['unit_test_id'] = $model->id;
        $params['status'] = $params['regression_status'];
        $ret = RegressionTestModel::saveRegTest($params);

        return [
            'status'  => $ret,
            'data' => [
                'message' => empty($ret) ? "保存回归测试失败" : "保存成功",
            ]
        ];
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($ids)
    {
        $data = [
            'status'  => true,
            'data' => [
                'alert' => true,
                'message' => trans('admin.delete_succeeded'),
            ],
        ];

        try {
            DB::beginTransaction();

            $ids = explode(",", $ids);
            foreach ($ids as $id) {
                $model = $this->form()->repository()->model()->findOrFail($id);
                if (isset($model->status)) {
                    $model->status = $model::STATUS_DELETED;
                    $ret = $model->save();
                } else {
                    $ret = $this->form()->destroy($id);
                }
                if (!$ret) {
                    throw new \Exception(trans('admin.delete_failed'), 1);
                }
                if (!empty($model->regTest)) {
                    foreach ($model->regTest as $regTest) {
                        $regTest->status = $model::STATUS_DELETED;
                        $regTest->save();
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            $data['status'] = false;
            $data['data']['message'] = $th->getMessage();
            DB::rollBack();
        }

        return response()->json($data);
    }

}
