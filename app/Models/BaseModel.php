<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Widgets\Table;

class BaseModel extends Model
{
    use HasFactory;
    use HasDateTimeFormatter;

    /**
     * 模型日期列的存储格式
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 数据状态
     */
    const STATUS_DELETED = 0;
    const STATUS_NORMAL  = 1;
    public static $label_status = [
        self::STATUS_DELETED => '已删除',
        self::STATUS_NORMAL  => '正常',
    ];

    const YES = 1;
    const NO  = 0;
    public static $label_yes_or_no = [
        self::YES  => '是',
        self::NO => '否',
    ];

    /**
     * 回归模式
     */
    const REG_TYPE_ALL     = 1;
    const REG_TYPE_SUCCESS = 2;
    public static $label_reg_type = [
        self::REG_TYPE_ALL     => '完全匹配',
        self::REG_TYPE_SUCCESS => '请求成功',
    ];

    /**
     * 环境
     */
    const DOMAIN_TYPE_TEST = 1;
    const DOMAIN_TYPE_PROD = 2;
    public static $label_domain_type = [
        self::DOMAIN_TYPE_TEST => '测试环境',
        self::DOMAIN_TYPE_PROD => '正式环境',
    ];

    /**
     * 计划任务类型
     */
    const TASK_TYPE_UNIT_TEST        = 1;
    const TASK_TYPE_INTEGRATION_TEST = 2;
    public static $label_task_type = [
        self::TASK_TYPE_UNIT_TEST        => '测试用例',
        self::TASK_TYPE_INTEGRATION_TEST => '集成测试',
    ];

    /**
     * 请求方法
     */
    public static $label_request_methods = [
        'GET'     => 'GET',
        'POST'    => 'POST',
    ];

    /**
     * 获取所有数据
     * @param  array   $where
     * @param  array   $select
     * @return models
     */
    public static function getAll(array $where = [], array $select = ['*'])
    {
        if (!isset($where['status'])) {
            $where['status'] = self::STATUS_NORMAL;
        }

        return self::select($select)->where($where)->get();
    }

    /**
     * 获取单条数据
     * @param  array   $where
     * @param  array   $select
     * @return model
     */
    public static function getOne(array $where = [], array $select = ['*'])
    {
        if (!isset($where['status'])) {
            $where['status'] = self::STATUS_NORMAL;
        }

        return self::select($select)->where($where)->first();
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * 获取用户列表
     * @param  array   $uids
     * @return array
     */
    public static function getUserList(array $uids = []): array
    {
        return Administrator::where(function ($query) use ($uids) {
            if (!empty($uids)) {
                return $query->whereIn('id', $uids);
            }
        })->get()->pluck('name', 'id')->toArray();
    }

    /**
     * 获取项目id
     * @param  int      $user_id
     * @return array
     */
    public static function getProjectIds(int $user_id): array
    {
        $projectUsers = ProjectUserModel::getAll(['user_id' => $user_id], ['project_id'])->toArray();
        $projects = ProjectModel::getAll(['owner_uid' => $user_id], ['id'])->toArray();
        $project_ids = array_merge(array_column($projectUsers, 'project_id'), array_column($projects, 'id'));
        return array_unique($project_ids);
    }

    /**
     * 获取接口id
     * @param  int      $user_id
     * @return array
     */
    public static function getApiIds(int $user_id, int $project_id = 0): array
    {
        $project_ids = [$project_id];
        if (empty($project_id)) {
            $project_ids = self::getProjectIds($user_id);
        }
        $apiIds = ApiModel::whereIn('project_id', $project_ids)->where('status', self::STATUS_NORMAL)->get('id')->toArray();
        return array_column($apiIds, 'id');
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($value === null) {
            return $this;
        }
        return parent::setAttribute($key, $value);
    }

    public static function getParamTable($params = [], $headerLabels = [])
    {
        if (empty($params)) {
            return '';
        }

        if (empty($headerLabels)) {
            $headerLabels = ['key' => '参数名', 'type' => '类型', 'is_necessary' => '必填', 'desc' => '备注', 'value' => '参数值'];
        }
        $headers = array_intersect_key($headerLabels, $params[0]);

        $body = array_map(function($item){
            if (isset($item['is_necessary'])) {
                $item['is_necessary'] = self::$label_yes_or_no[$item['is_necessary']];
            }
            return $item;
        }, $params);

        $table = new Table($headers, $body);
        return $table->render();
    }
}
