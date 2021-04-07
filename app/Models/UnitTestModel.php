<?php

namespace App\Models;

class UnitTestModel extends BaseModel
{
    protected $table = 'unit_test';

    protected $fillable = [
        'project_id',
        'api_id',
        'name',
        'header',
        'body',
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

    public function regTest()
    {
        return $this->belongsTo(RegressionTestModel::class, 'id', 'unit_test_id')->where('status', self::STATUS_NORMAL);
    }

    public static function formatTableData($params = [])
    {
        $data = [];
        if (!is_array($params)) {
            $params = json_decode($params, true);
        }
        foreach ($params as $key => $value) {
            $data[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        return $data;
    }

    /**
     * @name   保存测试用例
     * @param  array      $params
     * @return false | model
     */
    public static function saveUnitTest(array $params)
    {
        if (!empty($params['header']) && is_array($params['header'])) {
            $params['header'] = json_encode($params['header']);
        }
        if (!empty($params['body']) && is_array($params['body'])) {
            $params['body'] = json_encode($params['body']);
        }

        $model = UnitTestModel::where(['id' => $params['unit_test_id']])->first();
        if (empty($model) || $model->name != $params['name']) {
            $model = new UnitTestModel();
            $params['status'] = UnitTestModel::STATUS_NORMAL;
        }

        $model->fill($params);
        if ($model->save()) {
            return $model;
        }
        return false;
    }

    /**
     * @name   运行测试用例
     * @param  array      $params
     * @return bool
     */
    public function run(array $params)
    {
        # code...
    }
}
