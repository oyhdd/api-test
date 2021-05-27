<?php

namespace App\Admin\Repositories;

use App\Models\ApiModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;
use Dcat\Admin\Form;

class Api extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;

    /**
     * 查询编辑页面数据.
     *
     * @param Form $form
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable
     */
    public function edit(Form $form)
    {
        $query = $this->newQuery();

        if ($this->isSoftDeletes) {
            $query->withTrashed();
        }

        $this->model = $query
            ->with($this->getRelations())
            ->findOrFail($form->getKey(), $this->getFormColumns());

        return $this->model;
    }
}
