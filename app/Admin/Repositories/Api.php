<?php

namespace App\Admin\Repositories;

use App\Models\ApiModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Api extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
