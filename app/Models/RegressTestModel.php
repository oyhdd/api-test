<?php

declare(strict_types=1);

namespace App\Models;

use App\Admin\Controllers\AdminController;
use App\Helpers\Compress;

class RegressTestModel extends BaseModel
{
    protected $table = 'regress_test';

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
            $params['type'] = RegressTestModel::REG_TYPE_ALL;
        }

        $model = RegressTestModel::where(['unit_test_id' => $params['unit_test_id'], 'domain' => $params['domain']])->first();
        if (empty($model)) {
            if ($params['status'] == RegressTestModel::STATUS_DELETED) {
                return true;
            }
            $model = new RegressTestModel();
        }

        $model->fill($params);
        return $model->save();
    }

    /**
     * 获取回归测试列表
     */
    public static function getRegressList($domain, $apiIds = [])
    {
        $apiModels = ApiModel::with(['unitTest'])->where(['project_id' => AdminController::getProjectId(), 'status' => self::STATUS_NORMAL])->orderBy('order', 'ASC')->get(['id', 'name', 'parent_id', 'order', 'url']);

        $list = [];
        foreach ($apiModels as $apiModel) {
            $disabled = true;
            foreach ($apiModel->unitTest as $unitTest) {
                foreach ($unitTest->regTest as $regTest) {
                    if ($regTest->domain == $domain) {
                        $disabled = false;
                        break 2;
                    }
                }
            }
            $list[] = [
                'id' => $apiModel->id,
                'name' => $apiModel->name,
                'parent_id' => $apiModel->parent_id,
                'state' => [
                    'disabled' => $disabled,
                    'selected' => in_array($apiModel->id, $apiIds),
                ]
            ];
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
