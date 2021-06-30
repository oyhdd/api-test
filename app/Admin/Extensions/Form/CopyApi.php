<?php

namespace App\Admin\Extensions\Form;

use App\Models\ApiModel;
use App\Models\BaseModel;
use App\Models\ProjectModel;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;

class CopyApi extends Form implements LazyRenderable
{

    use LazyWidget; // 使用异步加载功能

    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        if (! ApiModel::create($input)) {
            return $this->response()->error('复制失败');
        }

        return $this->response()->success('复制成功')->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->select('project_id', '项目')->options(ProjectModel::getAll()->pluck('name', 'id'));
        $this->text('name')->required();
        $this->text('url', '接口地址');
        $this->select('method')->options(BaseModel::$label_request_methods)->required();
        $this->textarea('desc', '描述');
        $this->fieldset('参数设置', function ($model) {
            $model->table('header', '请求头', function ($table) {
                $table->text('key', '参数名')->required();
                $table->text('type', '参数类型');
                $table->radio('is_necessary', '是否必填')->options(BaseModel::$label_yes_or_no);
                $table->text('desc', '参数说明');
            })->saving(function ($v) {
                return json_encode($v);
            });
            $model->table('body', '请求体', function ($table) {
                $table->text('key', '参数名')->required();
                $table->text('type', '参数类型');
                $table->radio('is_necessary', '是否必填')->options(BaseModel::$label_yes_or_no);
                $table->text('desc', '参数说明');
            })->saving(function ($v) {
                return json_encode($v);
            });
        });
        $this->textarea('request_example', '请求示例');
        $this->textarea('response_example', '返回示例');
        $this->textarea('response_desc', '返回值说明');

        $this->width(9, 2);

    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        $api_id = $this->payload['id'] ?? 0;
        $model = ApiModel::getOne(['id' => $api_id]);
        $model->name .= "_copy";
        return $model->toArray();
    }
}
