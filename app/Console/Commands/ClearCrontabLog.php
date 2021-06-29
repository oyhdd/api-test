<?php

namespace App\Console\Commands;

use App\Models\CrontabModel;
use App\Models\LogCrontabModel;
use Illuminate\Console\Command;

class ClearCrontabLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apitest:clear_crontab_log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理计划任务日志';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tasks = CrontabModel::getCrontabFromCache();
        foreach ($tasks as $task) {
            if ($task['retain_day'] > 0) {
                $day = date('Y-m-d', strtotime("-{$task['retain_day']} day"));
                LogCrontabModel::where(['crontab_id' => $task['id']])->where('day', '<', $day)->delete();
            }
        }
    }
}
