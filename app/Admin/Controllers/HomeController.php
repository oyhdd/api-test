<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\Examples;
use App\Models\ProjectModel;
use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\Dashboard;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

class HomeController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->header('Dashboard')
            ->description('Description...')
            ->body(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $column->row(Dashboard::title());
                    $column->row(new Examples\Tickets());
                });

                $row->column(6, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(6, new Examples\NewUsers());
                        $row->column(6, new Examples\NewDevices());
                    });

                    $column->row(new Examples\Sessions());
                    $column->row(new Examples\ProductOrders());
                });
            });
    }

    public function changeProject()
    {
        if (!Admin::user()->isAdministrator()) {
            $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id')->toArray();
        } else {
            $projectList = ProjectModel::getAll()->pluck('name', 'id')->toArray();
        }
        $project_id = $this->request->input('project_id');
        if (!isset($projectList[$project_id])) {
            return Admin::json()->error('项目不存在');
        }

        $this->request->session()->put('project_id', $project_id);
        return Admin::json()->success("已切换至项目：{$projectList[$project_id]}")->refresh()->data(['project' => $projectList[$project_id]]);
    }
}
