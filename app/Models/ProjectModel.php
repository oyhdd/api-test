<?php

declare(strict_types=1);

namespace App\Models;

use Dcat\Admin\Models\Administrator;

class ProjectModel extends BaseModel
{
    protected $table = 'project';

    /**
     * 获取用户的项目列表
     */
    public static function getProjectList(int $user_id)
    {
        $projectIds = self::getProjectIds($user_id);
        return self::whereIn('id', $projectIds)->where(['status' => self::STATUS_NORMAL])->get();
    }

    public function owner()
    {
        return $this->belongsTo(Administrator::class, 'owner_uid', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(Administrator::class, 'project_user', 'project_id', 'user_id')->withTimestamps();
    }

    public function getAlarmParamAttribute($value)
    {
        if (!empty($value)) {
            return array_values(json_decode($value, true) ?: []);
        }
        return [];
    }

    public function setAlarmParamAttribute($value)
    {
        $this->attributes['alarm_param'] = json_encode(array_values($value));
    }
}
