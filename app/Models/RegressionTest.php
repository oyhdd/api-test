<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class RegressionTest extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'regression_test';
    
}
