<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'project_user';
    
}
