<?php

namespace App\Admin\Extensions\Grid\Action;

use Dcat\Admin\Tree\RowAction;
use Dcat\Admin\Widgets\Modal;
use App\Admin\Extensions\Form\CopyApi as CopyApiForm;

class CopyApi extends RowAction
{
    /**
     * @return string
     */
	protected $title = "<a href='javascript:void(0);'><i title='复制' class='fa fa-copy'></i></a>";

    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = CopyApiForm::make()->payload(['id' => $this->getKey(), 'project_id' => $this->getRow()->project_id]);

        return Modal::make()
            ->xl()
            ->title('复制')
            ->body($form)
            ->button($this->title);
    }
}
