<?php

namespace App\Models;

use Dcat\Admin\Admin;

class ApiModel extends BaseModel
{
    protected $table = 'api';

    public function project()
    {
        return $this->belongsTo(ProjectModel::class, 'project_id', 'id');
    }

    /**
     * 获取用户的接口列表
     */
    public static function getApiList(int $user_id)
    {
        $apiIds = self::getApiIds($user_id);
        return self::whereIn('id', $apiIds)->where(['status' => self::STATUS_NORMAL])->get();
    }

    public function regTest()
    {
        return $this->hasMany(RegressionTestModel::class, 'api_id')->where('status', self::STATUS_NORMAL);
    }

    public function unitTest()
    {
        return $this->hasMany(UnitTestModel::class, 'api_id')->where('status', self::STATUS_NORMAL);
    }

    public function getNavItems()
    {
        if (!Admin::user()->isAdministrator()) {
            $project_ids = self::getProjectIds(Admin::user()->id);
        } else {
            $project_ids = ProjectModel::getAll()->pluck('id')->toArray();
        }
        $apiLists = ApiModel::getAll()->whereIn('project_id', $project_ids);

        $list = $navItems = [];
        foreach ($apiLists as $api) {
            $list[$api->project->name][$api->id] = $api;
        }
        foreach ($list as $project_name => $apis) {
            $subMenus = [];
            foreach ($apis as $api) {
                $subMenus[] = [
                    "name" => $api->name,
                    "href" => "/admin/run/{$api->id}",
                    "edit_href" => "/admin/api/{$api->id}/edit",
                    "active" => $this->id == $api->id,
                ];
            }

            $navItems[] = [
                "name" => $project_name,
                "href" => "",
                "subMenus" => $subMenus,
            ];
        }

        return $navItems;
    }


    /**
     * 获取回归测试列表
     */
    public static function getRegressList()
    {
        if (!Admin::user()->isAdministrator()) {
            $project_ids = self::getProjectIds(Admin::user()->id);
        } else {
            $project_ids = ProjectModel::getAll()->pluck('id')->toArray();
        }
        $apiLists = ApiModel::getAll()->whereIn('project_id', $project_ids);

        $regressList = RegressionTestModel::getAll([], ['api_id'])->toArray();
        $apiIds = array_unique(array_column($regressList, 'api_id'));

        $list = [];
        foreach ($apiLists as $api) {
            if (! in_array($api->id, $apiIds)) {
                continue;
            }
            if (!isset($list[$api->project->id])) {
                $list[$api->project->id] = [
                    'id' => $api->project->id,
                    'name' => $api->project->name,
                    'domain' => [$api->project->domain_text, $api->project->domain_prod],
                    'apiList' => [[
                        'id' => $api->id,
                        'name' => $api->name,
                        'method' => $api->method,
                        'url' => $api->url,
                        'desc' => $api->desc,
                    ]],
                ];
            } else {
                $list[$api->project->id]['apiList'][] = [
                    'id' => $api->id,
                    'name' => $api->name,
                    'method' => $api->method,
                    'url' => $api->url,
                    'desc' => $api->desc,
                ];
            }
        }

        return $list;
    }

    public function getDomainOptions()
    {
        return [
            $this->project->domain_text => $this->project->domain_text,
            $this->project->domain_prod => $this->project->domain_prod,
        ];
    }

    public function getHeaderAttribute($value)
    {
        return array_values(@json_decode($value, true) ?: []);
    }

    public function getBodyAttribute($value)
    {
        return array_values(@json_decode($value, true) ?: []);
    }
}
