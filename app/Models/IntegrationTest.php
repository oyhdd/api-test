<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class IntegrationTest extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'integration_test';
    
}
