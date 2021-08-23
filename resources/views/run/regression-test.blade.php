<?php
    use Dcat\Admin\Widgets\Form;
    use Dcat\Admin\Widgets\Box;
    use Dcat\Admin\Layout\Column;
    use Dcat\Admin\Layout\Row;

    $row = new Row();
    $row->column(4, function (Column $column) use ($project_id, $domain, $api_ids) {
        $form = new Form();
        $form->action(admin_url('run/regress'))->setFormId('run_regression_test')->ajax(false);
        $domainList = \App\Models\ProjectModel::getDomainOptions($project_id);
        if (empty($domain)) {
            $domain = array_key_first($domainList);
        }
        $form->select('domain', '运行环境')->options($domainList)->default($domain)->required();

        $form->treeSelect('api_ids', '回归用例')
            ->expand(false)
            ->nodes(\App\Models\RegressionTestModel::getRegressList($domain, $api_ids));
        $form->width(10, 2)->disableResetButton();
        $form->addVariables(['footer' =>
        '<div class="box-footer row d-flex">
            <div class="col-md-2"> &nbsp;</div>
            <div class="col-md-8">
                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-paper-plane"></i> 运行</button>
            </div>
        </div>'
        ]);

        $column->append(Box::make('选择回归用例', $form));
    });
    $row->column(8, function (Column $column) {
        
        $column->append(Box::make('运行结果', '<div id="run_regression_test_response"></div>'));
    });

    echo $row->render();
?>

<script>
    Dcat.ready(function() {
        $("select[name='domain']").change(function () {
            var domain = $("select[name='domain']").val();
            if (domain != null) {
                Dcat.reload('/admin/run/regress-test?domain=' + domain);
            }
        });
        // ajax表单提交
        $('#run_regression_test').form({
            validate: true, //开启表单验证
            success: function (response) {
                $("#run_regression_test_response").html(response)
                
            },
            error: function (response) {
                console.log(2)
                // 当提交表单失败的时候会有默认的处理方法，通常使用默认的方式处理即可
                var errorData = JSON.parse(response.responseText);

                if (errorData) {
                    Dcat.error(errorData.message);
                } else {
                    console.log('提交出错', response.responseText);
                }

                // 终止后续逻辑执行
                return false;
            },
        });
    });
</script>