<?php

namespace App\Admin\Repositories;

use App\Models\IntegraTestModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class IntegraTest extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
