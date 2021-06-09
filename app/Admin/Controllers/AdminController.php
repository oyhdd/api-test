<?php

namespace App\Admin\Controllers;

use App\Models\BaseModel;
use App\Models\ProjectModel;
use Dcat\Admin\Admin;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Alert;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    /**
     * Set description for following 4 action pages.
     *
     * @var array
     */
    protected $description = [
        //        'index'  => 'Index',
        //        'show'   => 'Show',
        //        'edit'   => 'Edit',
        //        'create' => 'Create',
    ];

    /**
     * Get content title.
     *
     * @return string
     */
    protected function title()
    {
        return $this->title ?: admin_trans_label();
    }

    /**
     * Get description for following 4 action pages.
     *
     * @return array
     */
    protected function description()
    {
        return $this->description;
    }

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
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        $this->hasPermission($id);
        return $content
            ->title($this->title())
            ->description($this->description()['show'] ?? trans('admin.show'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $this->hasPermission($id);
        return $content
            ->title($this->title())
            ->description($this->description()['edit'] ?? trans('admin.edit'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description()['create'] ?? trans('admin.create'))
            ->body($this->form());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $this->hasPermission($id);
        return $this->form()->update($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        return $this->form()->store();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
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
            DB::beginTransaction();

            $ids = explode(",", $ids);
            foreach ($ids as $id) {
                $this->hasPermission($id);
                $model = $this->form()->repository()->model()->findOrFail($id);
                if (isset($model->status)) {
                    $model->status = $model::STATUS_DELETED;
                    $ret = $model->save();
                } else {
                    $ret = $this->form()->destroy($id);
                }

                if (!$ret) {
                    throw new \Exception(trans('admin.delete_failed'), 1);
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            $data['status'] = false;
            $data['data']['message'] = $th->getMessage();
            DB::rollBack();
        }

        return response()->json($data);
    }

    // 获取当前的项目id
    public static function getProjectId()
    {
        $project_id = request()->session()->get('project_id', 0);
        if (!empty($project_id)) {
            return $project_id;
        }

        if (!Admin::user()->isAdministrator()) {
            $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id')->toArray();
        } else {
            $projectList = ProjectModel::getAll()->pluck('name', 'id')->toArray();
        }
        return key($projectList);
    }

    // 获取当前的项目
    public static function getProject()
    {
        $project_id = request()->session()->get('project_id', 0);
        if (!empty($project_id)) {
            return ProjectModel::getOne(['id' => $project_id]);
        }

        if (!Admin::user()->isAdministrator()) {
            $projectList = ProjectModel::getProjectList(Admin::user()->id);
        } else {
            $projectList = ProjectModel::getAll();
        }

        return $projectList->first();
    }

    /**
     * 权限判断
     * @param int $id
     * @param bool $has_permission
     */
    protected function hasPermission($id, $has_permission = true)
    {
        try {
            $model = $this->form()->repository()->model()->findOrFail($id);
            if (isset($model->project_id)) {
                if (!Admin::user()->isAdministrator()) {
                    $projectList = ProjectModel::getProjectList(Admin::user()->id)->pluck('name', 'id')->toArray();
                } else {
                    $projectList = ProjectModel::getAll()->pluck('name', 'id')->toArray();
                }
                if (!isset($projectList[$model->project_id])) {
                    $has_permission = false;
                }
            }
        } catch (\Throwable $th) {
            admin_exit(
                Content::make()->body(Alert::make($th->getMessage(), '服务器错误')->danger())
            );
        }

        if (!$has_permission) {
            admin_exit(
                Content::make()->body(Alert::make('无权访问此页面~', '无权访问')->danger())
            );
        }
    }
}
