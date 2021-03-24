<?php

namespace App\Admin\Repositories;

use App\Models\RegressionTestModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class RegressionTest extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
