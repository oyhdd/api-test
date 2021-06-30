<?php

namespace App\Admin\Extensions\Grid\Action;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;
use App\Admin\Extensions\Form\CopyApi as CopyApiForm;

class CopyApi extends RowAction
{
    /**
     * @return string
     */
	protected $title = "复制";

    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = CopyApiForm::make()->payload(['id' => $this->getKey()]);

        return Modal::make()
            ->xl()
            ->title($this->title)
            ->body($form)
            ->button($this->title);
    }
}
