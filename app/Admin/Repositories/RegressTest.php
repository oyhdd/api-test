<?php

namespace App\Admin\Repositories;

use App\Models\RegressTestModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class RegressTest extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
