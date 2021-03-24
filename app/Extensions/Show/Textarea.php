<?php

namespace App\Extensions\Show;

use Dcat\Admin\Show\AbstractField;

class Textarea extends AbstractField
{
    public $border = false;
    public $escape = false;

    public function render($arg = '')
    {
        return '<pre style="margin: 0;">'.htmlspecialchars($this->value).' </pre>';
    }
}
