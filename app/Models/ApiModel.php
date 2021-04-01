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
        return $this->belongsTo(RegressionTestModel::class, 'id', 'unit_test_id')->where('status', self::STATUS_NORMAL);
    }

    public function unitTest()
    {
        return $this->hasMany(UnitTestModel::class, 'api_id')->where('status', self::STATUS_NORMAL);
    }

    public function getNavItems()
    {
        if (!Admin::user()->isAdministrator()) {
            $apiLists = self::getApiList(Admin::user()->id);
        } else {
            $apiLists = ApiModel::getAll();
        }

        $list = $navItems = [];
        foreach ($apiLists as $api) {
            $list[$api->project->name][$api->id] = $api;
        }
        foreach ($list as $project_name => $apis) {
            $subMenus = [];
            foreach ($apis as $api) {
                $subMenus[] = [
                    "name" => $api->name,
                    "href" => "/admin/api/run/{$api->id}",
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

    public function getHeaderAttribute($value)
    {
        return array_values(@json_decode($value, true) ?: []);
    }

    public function getBodyAttribute($value)
    {
        return array_values(@json_decode($value, true) ?: []);
    }
}
