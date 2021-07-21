<?php

namespace App\Models;

use App\Helpers\Compress;

class LogCrontabModel extends BaseModel
{
    protected $table = 'log_crontab';
    
    protected $fillable = [
        'day',
        'project_id',
        'crontab_id',
        'success',
        'log',
        'status',
    ];

    public function crontab()
    {
        return $this->belongsTo(CrontabModel::class, 'crontab_id', 'id');
    }

    public static function saveLog($project_id, $crontab_id, $success = 0, $log = '[]')
    {
        $day = date("Y-m-d");
        $model = new LogCrontabModel(compact('day', 'project_id', 'crontab_id', 'success', 'log'));
        return $model->save();
    }

    public function setLogAttribute($value)
    {
        $this->attributes['log'] = Compress::compress($value);
    }

    public function getLogAttribute($value)
    {
        return Compress::decompress($value);
    }
}
