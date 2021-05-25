<?php

declare(strict_types=1);

namespace App\Models;

class RegressionTestModel extends BaseModel
{
    protected $table = 'regression_test';

    protected $fillable = [
        'project_id',
        'api_id',
        'unit_test_id',
        'response_md5',
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
     * @name   保存回归测试用例
     * @param  array      $params
     * @return bool
     */
    public static function saveRegTest(array $params): bool
    {
        if (empty($params['type'])) {
            $params['type'] = RegressionTestModel::REG_TYPE_ALL;
        }

        $model = RegressionTestModel::where(['unit_test_id' => $params['unit_test_id']])->first();
        if (empty($model)) {
            $model = new RegressionTestModel();
            $params['status'] = RegressionTestModel::STATUS_NORMAL;
        }

        $model->fill($params);
        return $model->save();
    }
}
