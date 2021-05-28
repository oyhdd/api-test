<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

class CrontabModel extends BaseModel
{
	use HasDateTimeFormatter;
    protected $table = 'crontab';

    protected $fillable = [
        'project_id',
        'title',
        'desc',
        'task_type',
        'task_value',
        'crontab',
        'status',
        'last_time',
    ];

    public function project()
    {
        return $this->belongsTo(ProjectModel::class, 'project_id', 'id');
    }
}
