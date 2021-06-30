<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Form\CrontabLog;
use App\Admin\Repositories\LogCrontab;
use App\Models\BaseModel;
use App\Models\LogCrontabModel;
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
                return "<span class='label {$class}'>" . BaseModel::$label_yes_or_no[$success] . "</span> &nbsp;";
            })->sortable();
            $grid->column('log')->display(function() {
                return self::getLogModal($this->id);
            });
            $grid->column('updated_at', '执行时间')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->padding(0, 0, '20px')->panel();

                $filter->equal('success')->select(BaseModel::$label_yes_or_no)->width(4);
                $filter->between('updated_at')->datetime()->width(8);
            });
            $grid->tools('<a class="btn btn-primary disable-outline">测试按钮</a>');
            $grid->disableViewButton()->disableEditButton()->disableCreateButton();
        });
    }

    protected static function getLogModal($id)
    {
        return Modal::make()
        ->xl()
        ->title('执行结果')
        ->body(CrontabLog::make(['id' => $id]))
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

    public function destroy($ids)
    {
        $data = [
            'status'  => true,
            'data' => [
                'alert' => true,
                'message' => trans('admin.delete_succeeded'),
            ],
        ];

        try {
            $ids = explode(",", $ids);
            LogCrontabModel::whereIn('id', $ids)->delete();
        } catch (\Throwable $th) {
            $data['status'] = false;
            $data['data']['message'] = $th->getMessage();
        }

        return response()->json($data);
    }
}
