<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Support\Facades\Cache;

class CrontabModel extends BaseModel
{
	use HasDateTimeFormatter;
    protected $table = 'crontab';

    const CACHE_KEY = 'crontab_task';
    const CACHE_TIME = 86400;

    /**
     * 数据状态
     */
    const STATUS_DELETED = 0;
    const STATUS_NORMAL  = 1;
    public static $label_status = [
        self::STATUS_DELETED => '禁用',
        self::STATUS_NORMAL  => '启用',
    ];

    protected $fillable = [
        'project_id',
        'domain',
        'title',
        'desc',
        'task_type',
        'task_value',
        'retain_day',
        'crontab',
        'alarm_enable',
        'status',
        'last_time',
    ];

    public function project()
    {
        return $this->belongsTo(ProjectModel::class, 'project_id', 'id');
    }

    public function setTaskValueAttribute($value)
    {
        $this->attributes['task_value'] = json_encode(array_values($value));
    }

    public static function getCrontabFromCache()
    {
        $task = Cache::store('redis')->get(self::CACHE_KEY);
        if (empty($task)) {
            $task = CrontabModel::getAll([], ['id', 'crontab', 'retain_day'])->toArray();
            Cache::store('redis')->set(self::CACHE_KEY, $task, self::CACHE_TIME);
        }
        return $task;
    }

    public static function boot()
    {
        parent::boot();

        CrontabModel::created(function () {
            Cache::store('redis')->delete(self::CACHE_KEY);
        });

        CrontabModel::updated(function () {
            Cache::store('redis')->delete(self::CACHE_KEY);
        });

        CrontabModel::saved(function () {
            Cache::store('redis')->delete(self::CACHE_KEY);
        });

        CrontabModel::deleted(function () {
            Cache::store('redis')->delete(self::CACHE_KEY);
        });
    }
}
