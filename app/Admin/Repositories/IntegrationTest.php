<?php

namespace App\Admin\Repositories;

use App\Models\IntegrationTest as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class IntegrationTest extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
