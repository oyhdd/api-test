<?php

namespace App\Admin\Repositories;

use App\Models\LogCrontabModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class LogCrontab extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
