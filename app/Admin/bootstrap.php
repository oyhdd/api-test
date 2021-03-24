<?php

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Show;

/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

\Dcat\Admin\Show\Field::extend('textarea', App\Extensions\Show\Textarea::class);

if (in_array('cool-mode', config('admin.layout.body_class'))) {
    config([
        'admin.layout.body_class' => array_merge(config('admin.layout.body_class'), []),
        'admin.layout.color' => 'green',
    ]);
    Admin::css('/css/cool-mode.css');
}

