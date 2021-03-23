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
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'parent_id' => 0,
                'order' => 1,
                'title' => '接口管理',
                'icon' => 'feather icon-menu',
                'uri' => '',
                'extension' => '',
                'show' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'parent_id' => 9,
                'order' => 1,
                'title' => '接口列表',
                'icon' => 'feather icon-grid',
                'uri' => '/api',
                'extension' => '',
                'show' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'parent_id' => 9,
                'order' => 1,
                'title' => '测试用例',
                'icon' => 'feather icon-flag',
                'uri' => '/unit_test',
                'extension' => '',
                'show' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'parent_id' => 0,
                'order' => 1,
                'title' => '回归测试',
                'icon' => 'feather icon-corner-right-up',
                'uri' => '/regression_test',
                'extension' => '',
                'show' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'parent_id' => 0,
                'order' => 1,
                'title' => '集成测试',
                'icon' => 'feather icon-activity',
                'uri' => '/integration_test',
                'extension' => '',
                'show' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
