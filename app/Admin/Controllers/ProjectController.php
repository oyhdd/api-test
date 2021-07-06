<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Project;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use App\Models\BaseModel;
use Dcat\Admin\Admin;
use App\Models\ApiModel;
use App\Models\ProjectModel;

/**
 * 项目管理
 */
class ProjectController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Project::with(['owner']), function (Grid $grid) {
            if (!Admin::user()->isAdministrator()) {
                $project_ids = BaseModel::getProjectIds(Admin::user()->id);
                $grid->model()->whereIn('id', $project_ids);
            }
            $grid->model()->where(['status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');

            $grid->column('id')->sortable();
            $grid->column('name')->sortable();
            $grid->column('intro')->limit(40);
            $grid->column('alarm_enable')->switch()->sortable();
            $grid->column('owner.name', '项目负责人');
            $grid->column('updated_at')->sortable();

            $grid->disableFilter();
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
        return Show::make($id, Project::with(['users', 'owner']), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('intro');
            $show->field('users')->as(function () {
                return $this->users->pluck('name');
            })->label('info');
            $show->field('owner.name', '项目负责人')->label('success');
            $show->domain()->as(function ($domain) {
                return $this->getParamTable($domain, ['key' => '环境名称', 'value' => '域名地址']);
            })->unescape();
            $show->field('alarm_enable')->as(function ($alarm_enable) {
                return BaseModel::$label_yes_or_no[$alarm_enable] ?? '';
            });
            $show->field('alarm_email')->as(function () {
                return implode("\n", array_column($this->alarm_param, 'alarm_email'));
            })->textarea();
            $show->field('alarm_qy_wechat')->as(function () {
                return implode("\n", array_column($this->alarm_param, 'alarm_qy_wechat'));
            })->textarea();
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
        return Form::make(Project::with('users'), function (Form $form) {
            $form->display('id');

            $form->text('name')->required();
            $form->textarea('intro');
            $form->multipleSelect('users')->options(BaseModel::getUserList())->customFormat(function ($v) {
                if (empty($v)) {
                    return [];
                }
                return array_column($v, 'id');
            });
            $form->select('owner_uid')->options(BaseModel::getUserList())->default(Admin::user()->id)->required();
            $form->table('domain', function ($table) {
                $table->text('key', '环境名称')->required();
                $table->text('value', '域名地址')->required();
            })->saving(function ($v) {
                return json_encode($v);
            });
            $form->switch('alarm_enable');
            $form->fieldset('告警设置', function (Form $form) {
                $form->table('alarm_param', '', function ($table) {
                    $table->email('alarm_email');
                    $table->text('alarm_qy_wechat', '企业微信群聊机器人的key');
                });
            });

            $form->footer(function ($footer) {
                $footer->disableViewCheck()->disableEditingCheck()->disableCreatingCheck();
            });
        });
    }

    public function apiList()
    {
        $project_id = intval($this->request->input('q', 0));
        if ($project_id <= 0) {
            return [];
        }

        $ret = [];
        $apis = ApiModel::getAll(['project_id' => $project_id])->toArray();
        foreach ($apis as $key => $api) {
            $ret[$key] = [
                'id' => $api['id'],
                'text' => $api['name'] . " : " . $api['url']
            ];
        }
        return $ret;
    }

    /**
     * 权限判断
     * @param int $id
     * @param bool $has_permission
     */
    protected function hasPermission($id, $has_permission = true)
    {
        if (!Admin::user()->isAdministrator()) {
            $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id')->toArray();
        } else {
            $projectList = ProjectModel::getAll()->pluck('name', 'id')->toArray();
        }
        if (!isset($projectList[$id])) {
            $has_permission = false;
        }

        return parent::hasPermission($id, $has_permission);
    }
}
