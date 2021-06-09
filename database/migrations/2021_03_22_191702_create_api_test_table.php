<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateApiTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64)->default('')->comment('项目名');
            $table->string('intro', 128)->default('')->comment('简介');
            $table->tinyInteger('alarm_enable')->default(0)->comment('是否告警 0关闭 1开启');
            $table->text('alarm_param')->nullable()->comment('告警接收者 alarm_email,alarm_sms,alarm_qy_wechat');
            $table->text('domain')->nullable()->comment('域名');
            $table->integer('owner_uid')->default(0)->comment('负责人id');
            $table->tinyInteger('status')->default(1)->comment('状态：0已删除 1正常');
            $table->timestamps();

            $table->index('name');
            $table->index('owner_uid');
        });
        DB::statement("ALTER TABLE `project` comment 'api_test 项目表'");

        Schema::create('project_user', function (Blueprint $table) {
            $table->integer('project_id')->default(0)->comment('项目id');
            $table->integer('user_id')->default(0)->comment('用户id');
            $table->tinyInteger('status')->default(1)->comment('状态：0已删除 1正常');
            $table->timestamps();

            $table->index('project_id');
            $table->index('user_id');
        });
        DB::statement("ALTER TABLE `project_user` comment 'api_test 项目用户表'");

        Schema::create('api', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->default(0)->comment('项目id');
            $table->string('name', 64)->default('')->comment('接口名称');
            $table->string('url', 255)->default('')->comment('接口地址');
            $table->enum('method', ['GET', 'POST'])->default('GET')->comment('请求方法');
            $table->string('desc', 255)->default('')->comment('接口描述');
            $table->text('header')->nullable()->comment('header参数，json格式');
            $table->text('body')->nullable()->comment('body参数，json格式');
            $table->text('request_example')->nullable()->comment('请求示例');
            $table->text('response_example')->nullable()->comment('返回示例');
            $table->text('response_desc')->nullable()->comment('返回值说明');
            $table->tinyInteger('alarm_enable')->default(0)->comment('是否告警 0关闭 1开启');
            $table->tinyInteger('status')->default(1)->comment('状态：0已删除 1正常');
            $table->timestamps();

            $table->index('project_id');
            $table->index('name');
            $table->index('url');
        });
        DB::statement("ALTER TABLE `api` comment 'api_test 接口表'");

        Schema::create('unit_test', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->default(0)->comment('项目id');
            $table->integer('api_id')->default(0)->comment('接口id');
            $table->string('name', 64)->default('')->comment('用例名称');
            $table->text('header')->nullable()->comment('header参数，json格式');
            $table->text('body')->nullable()->comment('body参数，json格式');
            $table->tinyInteger('status')->default(1)->comment('状态：0已删除 1正常');
            $table->timestamps();

            $table->index('project_id');
            $table->index('api_id');
            $table->index('name');
        });
        DB::statement("ALTER TABLE `unit_test` comment 'api_test 测试用例表'");

        Schema::create('regression_test', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->default(0)->comment('项目id');
            $table->string('domain', 64)->default('')->comment('域名');
            $table->integer('api_id')->default(0)->comment('接口id');
            $table->integer('unit_test_id')->default(0)->comment('测试用例id');
            $table->string('response_md5', 32)->default('')->comment('返回值的md5');
            $table->tinyInteger('type')->default(1)->comment('回归模式：1完全匹配 2请求成功');
            $table->text('ignore_fields')->nullable()->comment('匹配时忽略字段');
            $table->tinyInteger('status')->default(1)->comment('状态：0已删除 1正常');
            $table->timestamps();

            $table->index('project_id');
            $table->index('api_id');
            $table->index('unit_test_id');
        });
        DB::statement("ALTER TABLE `regression_test` comment 'autotest 回归测试表'");

        Schema::create('integration_test', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->default(0)->comment('项目id');
            $table->integer('parent_id')->default(0)->comment('父id');
            $table->integer('unit_test_id')->default(0)->comment('测试用例id');
            $table->string('name', 64)->default('')->comment('用例名称');
            $table->tinyInteger('status')->default(1)->comment('状态：0已删除 1正常');
            $table->timestamps();

            $table->index('project_id');
            $table->index('parent_id');
            $table->index('unit_test_id');
            $table->index('name');
        });

        DB::statement("ALTER TABLE `integration_test` comment 'api_test 集成测试表'");

        Schema::create('crontab', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->default(0)->comment('项目id');
            $table->string('domain', 64)->default('')->comment('域名');
            $table->string('title', 64)->comment('任务名称');
            $table->string('desc', 255)->default('')->comment('任务描述');
            $table->tinyInteger('task_type')->default(1)->comment('任务类型：1测试用例 2集成测试');
            $table->text('task_value')->nullable()->comment('任务id');
            $table->string('crontab', 32)->default('* * * * *')->comment('crontab: * * * * *');
            $table->tinyInteger('status')->default(1)->comment('状态：0已删除 1正常');
            $table->timestamp('last_time')->nullable()->comment('上次执行时间');
            $table->timestamps();

            $table->index('title');
            $table->index('last_time');
        });
        DB::statement("ALTER TABLE `crontab` comment 'api_test 计划任务'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('api');
        Schema::dropIfExists('unit_test');
        Schema::dropIfExists('regression_test');
        Schema::dropIfExists('integration_test');
    }
}
