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

if (in_array('cool-mode', config('admin.layout.body_class'))) {
    \Dcat\Admin\Color::extend('green', [
        'primary'        => '#fbbd08',
        'primary-darker' => '#fbbd08',
        'link'           => '#fbbd08',
    ]);
    config([
        'admin.layout.body_class' => array_merge(config('admin.layout.body_class'), []),
        'admin.layout.color' => 'green',
    ]);
    Admin::css('/css/cool-mode.css');
}

