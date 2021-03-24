<?php

namespace App\Admin\Repositories;

use App\Models\UnitTestModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UnitTest extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
