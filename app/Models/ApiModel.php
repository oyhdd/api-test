<?php

namespace App\Models;

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

    public function getHeaderAttribute($value)
    {
        return array_values(@json_decode($value, true) ?: []);
    }

    public function getBodyAttribute($value)
    {
        return array_values(@json_decode($value, true) ?: []);
    }
}
