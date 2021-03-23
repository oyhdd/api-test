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
    $router->get('project/api_list', 'ProjectController@apiList');
    $router->resource('project', ProjectController::class);

    // 接口管理
    $router->resource('api', ApiController::class);

    // 测试用例
    $router->get('unit_test/run', 'UnitTestController@run');
    $router->resource('unit_test', UnitTestController::class);

    // 回归测试
    $router->post('regression_test/save_reg_test', 'RegressionTestController@saveRegTest');
    $router->resource('regression_test', RegressionTestController::class);

});
