<?php

declare(strict_types=1);

namespace App\Models;

use App\Admin\Controllers\AdminController;
use App\Helpers\Compress;

class RegressionTestModel extends BaseModel
{
    protected $table = 'regression_test';

    protected $fillable = [
        'project_id',
        'domain',
        'api_id',
        'unit_test_id',
        'response',
        'type',
        'ignore_fields',
        'status',
    ];

    public function api()
    {
        return $this->belongsTo(ApiModel::class, 'api_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(ProjectModel::class, 'project_id', 'id');
    }

    public function unitTest()
    {
        return $this->belongsTo(UnitTestModel::class, 'unit_test_id', 'id');
    }

    /**
     * @name   保存回归用例
     * @param  array      $params
     * @return bool
     */
    public static function saveRegTest(array $params): bool
    {
        if (empty($params['type'])) {
            $params['type'] = RegressionTestModel::REG_TYPE_ALL;
        }

        $model = RegressionTestModel::where(['unit_test_id' => $params['unit_test_id'], 'domain' => $params['domain']])->first();
        if (empty($model)) {
            if ($params['status'] == RegressionTestModel::STATUS_DELETED) {
                return true;
            }
            $model = new RegressionTestModel();
        }

        $model->fill($params);
        return $model->save();
    }

    /**
     * 获取回归测试列表
     */
    public static function getRegressList()
    {
        $models = RegressionTestModel::getAll(['project_id' => AdminController::getProjectId()])->groupBy('api_id');

        $list = [];
        foreach ($models as $model) {
            $model = $model->shift();
            if (!isset($list[$model->project_id])) {
                $domain = array_column($model->project->domain, 'value', 'key');

                $list[$model->project_id] = [
                    'id' => $model->project_id,
                    'name' => $model->project->name,
                    'domain' => $domain,
                    'apiList' => [
                        [
                            'id' => $model->api->id,
                            'name' => $model->api->name,
                            'method' => $model->api->method,
                            'url' => $model->api->url,
                            'desc' => $model->api->desc,
                        ]
                    ],
                ];
            } else {
                $list[$model->project_id]['apiList'][] = [
                    'id' => $model->api_id,
                    'name' => $model->api->name,
                    'method' => $model->api->method,
                    'url' => $model->api->url,
                    'desc' => $model->api->desc,
                ];
            }
        }

        return $list;
    }

    public function getNameAttribute($value)
    {
        return sprintf("%s : %s", $this->api->name, $this->unitTest->name);
    }

    public function setResponseAttribute($value)
    {
        $this->attributes['response'] = Compress::compress($value);
    }

    public function getResponseAttribute($value)
    {
        return Compress::decompress($value);
    }
}
