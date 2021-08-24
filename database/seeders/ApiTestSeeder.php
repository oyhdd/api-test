<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ApiTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // 添加菜单
        \Dcat\Admin\Models\Menu::insert([
            [
                'parent_id' => 0,
                'order' => 1,
                'title' => '项目管理',
                'icon' => 'feather icon-box',
                'uri' => '/project',
                'extension' => '',
                'show' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'parent_id' => 0,
                'order' => 1,
                'title' => '接口管理',
                'icon' => 'feather icon-menu',
                'uri' => '',
                'extension' => '',
                'show' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'parent_id' => 9,
                'order' => 1,
                'title' => '接口列表',
                'icon' => 'feather icon-grid',
                'uri' => '/api',
                'extension' => '',
                'show' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'parent_id' => 9,
                'order' => 1,
                'title' => '测试用例',
                'icon' => 'feather icon-flag',
                'uri' => '/unit-test',
                'extension' => '',
                'show' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'parent_id' => 0,
                'order' => 1,
                'title' => '接口调试',
                'icon' => 'fa-paper-plane',
                'uri' => '/run',
                'extension' => '',
                'show' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'parent_id' => 9,
                'order' => 1,
                'title' => '回归用例',
                'icon' => 'feather icon-corner-right-up',
                'uri' => '/regression-test',
                'extension' => '',
                'show' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'parent_id' => 9,
                'order' => 1,
                'title' => '集成测试',
                'icon' => 'feather icon-activity',
                'uri' => '/integration-test',
                'extension' => '',
                'show' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'parent_id' => 0,
                'order' => 1,
                'title' => '计划任务',
                'icon' => 'fa-clock-o',
                'uri' => '/crontab',
                'extension' => '',
                'show' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        \Dcat\Admin\Models\Permission::insert([
            [
                'id'          => 7,
                'name'        => '普通用户',
                'slug'        => 'normal_user',
                'http_method' => '',
                'http_path'   => '/project*,/api*,/unit-test*,/regression-test*,/integration-test*,/run*',
                'parent_id'   => 0,
                'order'       => 7,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);

        \Dcat\Admin\Models\Role::insert([
            [
                'name'       => '普通用户',
                'slug'       => 'user',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
        \Dcat\Admin\Models\Role::find(2)->permissions()->save(\Dcat\Admin\Models\Permission::find(7));
    }
}
