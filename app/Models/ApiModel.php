<?php

declare(strict_types=1);

namespace App\Models;

class ApiModel extends BaseModel
{
    protected $table = 'api';

    public function project()
    {
        return $this->belongsTo(ProjectModel::class, 'project_id', 'id');
    }
    
}
