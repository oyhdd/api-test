<?php

namespace App\Admin\Forms;

use App\Models\LogCrontabModel;
use Dcat\Admin\Support\LazyRenderable;

class CrontabLog extends LazyRenderable
{

    protected $title = ['#', '标题', '内容'];

    public function render()
    {
        $logCrontab = LogCrontabModel::getOne(['id' => $this->id])->toArray();
        return view('log_crontab.modal', ['id' => $this->id, 'data' => json_decode($logCrontab['log'], true)]);
    }
}
