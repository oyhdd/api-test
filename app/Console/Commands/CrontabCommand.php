<?php

namespace App\Console\Commands;

use App\Models\CrontabModel;
use Illuminate\Console\Command;

class CrontabCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apitest:crontab {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '计划任务';

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
        $id = $this->argument('id');
        return CrontabModel::runCrontab($id);
    }

}
