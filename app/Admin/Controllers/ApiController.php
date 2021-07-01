<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grid\Action\CopyApi;
use App\Admin\Repositories\Api;
use Dcat\Admin\Form;
use Dcat\Admin\Show;
use App\Models\{ApiModel, UnitTestModel, ProjectModel, BaseModel};
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Tree;
use Dcat\Admin\Widgets\Form as WidgetForm;

/**
 * 接口管理
 */
class ApiController extends AdminController
{

    public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description()['index'] ?? trans('admin.list'))
            ->body(function (Row $row) {
                $row->column(5, $this->treeView()->render());

                $row->column(7, function (Column $column) {
                    $form = new WidgetForm();

                    $form->select('parent_id')
                    ->options(ApiModel::selectOptions(function($query) {
                        return $query->where(['project_id' => self::getProjectId(), 'status' => BaseModel::STATUS_NORMAL]);
                    }));
                    $form->hidden('project_id')->default(self::getProjectId());
                    $form->text('name')->required();
                    $form->text('url');
                    $form->select('method')->options(BaseModel::$label_request_methods)->default('GET')->required();
                    $form->textarea('desc');
                    $form->fieldset('参数设置', function ($form) {
                        $form->table('header', function ($table) {
                            $table->text('key', '参数名')->required();
                            $table->text('type', '参数类型')->default('string');
                            $table->radio('is_necessary', '是否必填')->options(BaseModel::$label_yes_or_no)->default(BaseModel::NO);
                            $table->text('desc', '参数说明');
                        })->saving(function ($v) {
                            return json_encode($v);
                        });
                        $form->table('body', function ($table) {
                            $table->text('key', '参数名')->required();
                            $table->text('type', '参数类型')->default('string');
                            $table->radio('is_necessary', '是否必填')->options(BaseModel::$label_yes_or_no)->default(BaseModel::NO);
                            $table->text('desc', '参数说明');
                        })->saving(function ($v) {
                            return json_encode($v);
                        });
                    });
                    $form->textarea('request_example');
                    $form->textarea('response_example');
                    $form->textarea('response_desc');

                    $form->width(9, 2);

                    $column->append(Box::make(trans('admin.new'), $form));
                });
            });
    }

    /**
     * @return \Dcat\Admin\Tree
     */
    protected function treeView()
    {
        return new Tree(new ApiModel(), function (Tree $tree) {
            $tree->query(function($query) {
                return $query->where(['project_id' => self::getProjectId(), 'status' => BaseModel::STATUS_NORMAL]);
            });
            $tree->disableCreateButton();
            $tree->disableQuickCreateButton();
            $tree->disableQuickEditButton();
            $tree->showEditButton();
            $tree->maxDepth(3);

            $tree->actions(function (Tree\Actions $actions) {
                $actions->prepend("&nbsp;<a href='/admin/run/{$this->getKey()}'><i title='运行' class='fa fa-paper-plane grid-action-icon'></i>&nbsp;</a>&nbsp;");
                $actions->prepend(new CopyApi());
                $actions->prepend("&nbsp;<a href='/admin/api/{$this->getKey()}'><i title='查看' class='feather icon-eye grid-action-icon'></i>&nbsp;</a>");
            });

            $tree->branch(function ($branch) {
                $payload = "<strong>{$branch['name']}</strong>";

                if (! isset($branch['children'])) {
                    $uri = $branch['url'];
                    $payload .= "&nbsp;&nbsp;<a href=\"/admin/run/{$branch['id']}\" class=\"dd-nodrag\">$uri</a>";
                }

                return $payload;
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
        return Show::make($id, Api::with(['project']), function (Show $show) {
            $show->field('id');
            $show->field('project.name', '项目');
            $show->field('name');
            $show->field('url')->label('info');
            $show->field('method')->label('success');
            $show->field('desc')->textarea();
            $show->header()->as(function ($header) {
                return $this->getParamTable($header);
            })->unescape();
            $show->body()->as(function ($body) {
                return $this->getParamTable($body);
            })->unescape();
            $show->field('request_example')->textarea();
            $show->field('response_example')->textarea();
            $show->field('response_desc')->textarea();
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
            $form->select('parent_id')->options(ApiModel::selectOptions(function($query) {
                return $query->where(['project_id' => self::getProjectId(), 'status' => BaseModel::STATUS_NORMAL]);
            }))->saving(function ($v) {
                return (int) $v;
            });
            $form->select('project_id')->options(ProjectModel::getAll()->pluck('name', 'id'))->default(self::getProjectId())->disable();
            $form->text('name')->required();
            $form->text('url');
            $form->select('method')->options(BaseModel::$label_request_methods)->default('GET')->required();
            $form->textarea('desc');
            $form->fieldset('参数设置', function ($form) {
                $form->table('header', function ($table) {
                    $table->text('key', '参数名')->required();
                    $table->text('type', '参数类型')->default('string');
                    $table->radio('is_necessary', '是否必填')->options(BaseModel::$label_yes_or_no)->default(BaseModel::NO);
                    $table->text('desc', '参数说明');
                })->saving(function ($v) {
                    return json_encode($v);
                });
                $form->table('body', function ($table) {
                    $table->text('key', '参数名')->required();
                    $table->text('type', '参数类型')->default('string');
                    $table->radio('is_necessary', '是否必填')->options(BaseModel::$label_yes_or_no)->default(BaseModel::NO);
                    $table->text('desc', '参数说明');
                })->saving(function ($v) {
                    return json_encode($v);
                });
            });
            $form->textarea('request_example');
            $form->textarea('response_example');
            $form->textarea('response_desc');

            $form->width(9, 2);

            $form->saving(function (Form $form) {
                $form->url = "/" . ltrim($form->url ?? '', "/");
                if ($form->isCreating()) {
                    $form->project_id = self::getProjectId();
                }
            });
            $form->footer(function ($footer) {
                $footer->disableViewCheck()->disableEditingCheck()->disableCreatingCheck();
            });
        });
    }

    public function unitTestList()
    {
        $api_id = intval($this->request->input('q', 0));
        if ($api_id <= 0) {
            return [];
        }

        $unitTest = UnitTestModel::getAll(['api_id' => $api_id])->toArray();
        $ret = [];
        foreach ($unitTest as $key => $unit) {
            $ret[$key] = [
                'id' => $unit['id'],
                'text' => $unit['name'],
            ];
        }
        return $ret;
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
                if (!empty($model->unitTest)) {
                    foreach ($model->unitTest as $unitTest) {
                        $unitTest->status = $model::STATUS_DELETED;
                        $unitTest->save();
                    }
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
