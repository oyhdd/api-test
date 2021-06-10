<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\LogCrontab;
use App\Models\BaseModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Modal;

class LogCrontabController extends AdminController
{
    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description()['index'] ?? trans('admin.list'))
            ->breadcrumb(
                ['text' => admin_trans_label('crontab'), 'url' => '/crontab'],
                ['text' => admin_trans_label('log-crontab')]
            )
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(LogCrontab::with(['crontab']), function (Grid $grid) {
            $crontab_id = $this->request->input('crontab_id', 0);
            $grid->model()->where(['project_id' => self::getProjectId(), 'crontab_id' => $crontab_id, 'status' => BaseModel::STATUS_NORMAL])->orderBy('id', 'desc');
            $grid->column('id')->sortable();
            $grid->column('crontab.title', '计划任务');
            $grid->column('success')->display(function($success) {
                $class = 'bg-success';
                if (!$success) {
                    $class = 'bg-danger';
                }
                return "<span class='label {$class}'>" . ($success ? '是' : '否') . "</span> &nbsp;";
            })->sortable();
            $grid->column('log')->display(function($log) {
                return self::getLogModal($log);
            });
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->padding(0, 0, '20px')->panel();

                $filter->between('updated_at')->datetime();
            });

            $grid->disableViewButton()->disableEditButton()->disableCreateButton();
        });
    }

    protected static function getLogModal($log)
    {
        return Modal::make()
        ->lg()
        ->title('标题')
        ->body(view('log_crontab.modal', ['data' => json_decode($log, true)]))
        ->button('<button class="btn btn-primary">查看结果</button>');
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new LogCrontab(), function (Form $form) {
        });
    }
}
