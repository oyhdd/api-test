<style>
    textarea.form-control {
        display: block;
        width: 100%;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857;
        border: 1px solid rgb(204, 204, 204);
        border-radius: 4px;
        box-shadow: rgb(0 0 0 / 8%) 0px 1px 1px inset;
        transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;
        margin-top: 0px;
        margin-bottom: 0px;
        height: 34px;
    }
</style>
<div class="col-md-5 col-sm-12">
@php

    $form = new \Dcat\Admin\Widgets\Form();
    $form->action(request()->fullUrl());
    $form->select('unit_test_id', '测试用例')->options(array_column($model->unitTest->toArray(), 'name', "id"));
    $form->divider();

    $form->fieldset('Header', function ($form) use ($model) {
        foreach ($model['header'] as $param) {
            if ($param['is_necessary']) {
                $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->required();
            } else {
                $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}");
            }
        }
        if (empty($model['header'])) {
            $form->html("Empty.");
        }
    });

    $form->fieldset('Body', function ($form) use ($model) {
        foreach ($model['body'] as $param) {
            if ($param['is_necessary']) {
                $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->required();
            } else {
                $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}");
            }
        }
        if (empty($model['body'])) {
            $form->html("Empty.");
        }
    });
    $form->width(9, 3);
    echo $form->disableResetButton()->disableSubmitButton()->render();

@endphp
    <div class="box-footer row d-flex">
        <div class="col-md-3"> &nbsp;</div>
        <div class="col-md-9">
            <button id="submit-btn" type="submit" class="btn btn-primary pull-left" data-loading-text="运行中..."><i class="fa fa-paper-plane"></i> 运行</button>
            <button id="delete-btn" type="button" class="btn btn-danger pull-right" data-loading-text="删除中..." autocomplete="off"><i class="feather icon-trash"></i>删除用例</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    Dcat.ready(function() {
        $("select[name='unit_test_id']").change(function () {
            var unit_test_id = $("select[name='unit_test_id']").val();
            var unitTest = <?php echo json_encode($model->unitTest->toArray()); ?>;
            for (const i in unitTest) {
                if (unitTest[i]['id'] != unit_test_id) {
                    continue;
                }
                for (const j in unitTest[i]['header']) {
                    var param = unitTest[i]['header'][j];
                    $("textarea[name='" + param['key'] + "']").val(param['value']);
                }
                for (const j in unitTest[i]['body']) {
                    var param = unitTest[i]['body'][j];
                    $("textarea[name='" + param['key'] + "']").val(param['value']);
                }
            }
        });
    });
</script>