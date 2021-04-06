<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->get('auth/login', 'AuthController@getLogin');

    // 项目管理
    $router->get('project/api-list', 'ProjectController@apiList');
    $router->resource('project', ProjectController::class);

    // 接口管理
    $router->get('api/unit-test-list', 'ApiController@unitTestList');
    $router->resource('api', ApiController::class);

    // 接口运行
    $router->resource('run', RunController::class);

    // 测试用例
    $router->post('unit-test/save', 'UnitTestController@save');
    $router->get('unit-test/api-detail/{api_id}', 'UnitTestController@apiDetail');
    $router->resource('unit-test', UnitTestController::class);

    // 回归测试
    $router->resource('regression-test', RegressionTestController::class);

    // 单元测试
    $router->resource('unit-test', UnitTestController::class);

    // 集成测试
    $router->resource('integration-test', IntegrationTestController::class);

});
