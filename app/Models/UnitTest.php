<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class UnitTest extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'unit_test';
    
}
