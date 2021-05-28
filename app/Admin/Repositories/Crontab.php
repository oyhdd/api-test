<?php

namespace App\Admin\Repositories;

use App\Models\CrontabModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Crontab extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
