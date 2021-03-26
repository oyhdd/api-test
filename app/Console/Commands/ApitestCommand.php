<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApitestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apitest:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '接口测试平台数据库安装命令';

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
        $this->initDatabase();
    }

    /**
     * Create tables and seed it.
     *
     * @return void
     */
    public function initDatabase()
    {
        $this->call('migrate');

        $userModel = config('admin.database.users_model');

        if ($userModel::count() == 0) {
            $this->call('db:seed', ['--class' => \Dcat\Admin\Models\AdminTablesSeeder::class]);
            $this->call('db:seed', ['--class' => \Database\Seeders\ApiTestSeeder::class]);
        }

    }
}
