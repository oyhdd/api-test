<?php

namespace App\Models;

use App\Admin\Controllers\AdminController;
use Spatie\EloquentSortable\Sortable;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Traits\ModelTree;

class ApiModel extends BaseModel implements Sortable
{
    use HasDateTimeFormatter,
    ModelTree {
        allNodes as treeAllNodes;
        ModelTree::boot as treeBoot;
    }

    protected $table = 'api';

    protected $fillable = [
        'parent_id',
        'order',
        'project_id',
        'name',
        'url',
        'method',
        'desc',
        'header',
        'body',
        'request_example',
        'response_example',
        'response_desc',
        'status',
    ];

    /**
     * @var array
     */
    protected $sortable = [
        'sort_when_creating' => true,
    ];

    protected $titleColumn = 'name';

    public function project()
    {
        return $this->belongsTo(ProjectModel::class, 'project_id', 'id');
    }

    /**
     * 获取用户的接口列表
     */
    public static function getApiList(int $user_id, int $project_id = 0)
    {
        $apiIds = self::getApiIds($user_id, $project_id);
        return self::whereIn('id', $apiIds)->where(['status' => self::STATUS_NORMAL])->get();
    }

    public function regTest()
    {
        return $this->hasMany(RegressionTestModel::class, 'api_id')->where('status', self::STATUS_NORMAL);
    }

    public function unitTest()
    {
        return $this->hasMany(UnitTestModel::class, 'api_id')->where('status', self::STATUS_NORMAL);
    }

    public function getNavItems()
    {
        $project_id = AdminController::getProjectId();
        $apiLists = ApiModel::select(['id', 'parent_id', 'order', 'name'])
            ->where(['project_id' => $project_id, 'status' => self::STATUS_NORMAL])
            ->orderBy('order', 'ASC')
            ->get()
            ->toArray();

        $apiLists = array_column($apiLists, null, 'id');

        $navItems = [];
        foreach ($apiLists as $id => $api) {
            unset($apiLists[$id]['order']);
            $apiLists[$id]['href'] = "/admin/run/{$api['id']}";
            $apiLists[$id]['edit_href'] = "/admin/api/{$api['id']}/edit";
            $apiLists[$id]['active'] = $this->id == $api['id'];
            if (isset($apiLists[$api['parent_id']])) {
                $apiLists[$api['parent_id']]['subMenus'][] = &$apiLists[$id];
            } else {
                $navItems[] = &$apiLists[$id];
            }
        }

        return $navItems;
    }

    public function getHeaderAttribute($value)
    {
        return array_values(@json_decode($value, true) ?: []);
    }

    public function getBodyAttribute($value)
    {
        return array_values(@json_decode($value, true) ?: []);
    }
}
