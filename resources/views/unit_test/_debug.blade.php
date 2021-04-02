<style>
    textarea.form-control {
        padding: 6px 12px;
        line-height: 1.42857;
        height: 34px;
    }

    .response_panel {
        padding: 18px 20px 10px 18px;
    }

    .save-btn {
        width: 110px;
        color: white;
        border-top-left-radius: 0;
    }

    .form-group input {
        width: 100%;
    }

    .input-group {
        margin: 10px 0;
    }

    #response {
        min-height: 235px;
        color: white;
    }
</style>
<div class="row">
    <div class="col-md-5 col-sm-12">
        @php

        $form = new \Dcat\Admin\Widgets\Form();
        $form->action(request()->fullUrl())->setFormId('run_api')->ajax(false);
        $form->hidden('_method')->default("PUT");
        $form->hidden('_token')->default(csrf_token());
        $form->hidden('project_id')->default($model->project->id);
        $form->hidden('api_id')->default($model->id);
        $form->select('domain', '运行环境')->options($model->getDomainOptions())->default(current($model->getDomainOptions()))->required();
        $form->select('unit_test_id', '测试用例')->options(array_column($model->unitTest->toArray(), 'name', "id"));
        $form->divider();

        $form->embeds('header', '请求头', function ($form) use ($model) {
            foreach ($model['header'] as $param) {
                if ($param['is_necessary']) {
                    $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->width(9, 3)->required();
                } else {
                    $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->width(9, 3);
                }
            }

            if (empty($model['header'])) {
                $form->html("Empty.");
            }
        })->saveAsJson();

        $form->embeds('body', '请求体', function ($form) use ($model) {
            foreach ($model['body'] as $key => $param) {
                if ($param['is_necessary']) {
                    $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->width(9, 3)->required();
                } else {
                    $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->width(9, 3);
                }
            }

            if (empty($model['body'])) {
                $form->html("Empty.");
            }
        })->saveAsJson();

        $form->width(9, 3);

        $form->addVariables(['footer' =>
        '<div class="box-footer row d-flex">
            <div class="col-md-3"> &nbsp;</div>
            <div class="col-md-9">
                <button type="submit" class="btn btn-success pull-left"><i class="fa fa-paper-plane"></i> 运行</button>
                <button id="delete_unit_test" type="button" class="btn btn-warning pull-right" disabled><i class="feather icon-trash"></i> 删除用例</button>
            </div>
        </div>'
        ]);
        echo $form->render();

        @endphp

    </div>

    <div class="response_panel col-md-7 col-sm-12">
        <h4>请求返回:</h4>
        <div class="input-group">
            <input id="unit_test_name" type="text" class="form-control" placeholder="请输入 用例名称">
            <span class="input-group-btn">
                <button id="save_unit_test" type="button" class="btn save-btn bg-success" autocomplete="off"><i class="feather icon-save"></i> 保存用例</button>
            </span>
        </div>
        <pre id="ret">HTTP状态码：</br>请求时间：</br>curl请求示例：</pre>
        <pre id="response">返回内容：</pre>
    </div>
</div>

<script type="text/javascript">
    Dcat.ready(function() {
        let header = {};
        let body = {};

        // 自动加载测试用例
        $("select[name='unit_test_id']").change(function() {
            var unit_test_id = $("select[name='unit_test_id']").val();
            var unitTest = <?php echo json_encode($model->unitTest->toArray()); ?>;
            for (const i in unitTest) {
                if (unitTest[i]['id'] != unit_test_id) {
                    continue;
                }
                var header = JSON.parse(unitTest[i]['header']);
                for (const key in header) {
                    $("textarea[name='header[" + key + "]']").val(header[key]);
                }
                var body = JSON.parse(unitTest[i]['body']);
                for (const key in body) {
                    $("textarea[name='body[" + key + "]']").val(body[key]);
                }
            }
            if (unit_test_id > 0) {
                $("#delete_unit_test").attr('disabled', false);
            } else {
                $("#delete_unit_test").attr('disabled', true);
            }

        });

        // 运行用例
        $('#run_api').form({
            validate: true, //开启表单验证
            before: function(fields, form, opt) {
                // fields 为表单内容
                console.log('所有表单字段的值', fields);
            },
            success: function(response) {
                var result = response.result;
                var detail = response.detail;
                $('#ret').html("HTTP状态码：" + detail.status_code + "<br>请求时间：" + detail.request_time + "ms" + "<br><hr>curl请求示例：" + detail.curl_example);
                if (detail.status_code == 200) {
                    $('#ret').css({
                        color: 'lightgreen'
                    });
                } else {
                    $('#ret').css({
                        color: "#dda451"
                    });
                }

                if (typeof result === 'string' && result.indexOf('content="text/html;') != -1) {
                    $('#response').html(result);
                } else {
                    if (typeof result == 'object') {
                        result = JSON.stringify(result);
                        var formatText = js_beautify(result, 4, ' ');
                    } else if (result.indexOf('<script> Sfdump = window.Sfdump') != -1) {
                        var formatText = result;
                    } else if (typeof result === 'string') {
                        //防中文乱码
                        result = eval("(" + result + ")")
                        result = JSON.stringify(result);
                        var formatText = js_beautify(result, 4, ' ');
                    } else {
                        var formatText = result;
                    }

                    $('#response').html(formatText);
                }
            },
            error: function(response) {
                var errorData = JSON.parse(response.responseText);
                if (errorData) {
                    Dcat.error(errorData.message);
                    var result = JSON.stringify(errorData);
                    var formatText = js_beautify(result, 4, ' ');
                    $('#response').html(formatText);
                }
                $('#ret').html("请求失败<br>" + errorData.message);
                $('#ret').css({
                    color: "#dda451"
                });
                return false;
            },
        });

        // 删除用例
        $('#delete_unit_test').click(function() {
            Dcat.confirm('确认删除此用例？', null, function() {
                $('#delete_unit_test').buttonLoading();
                var unit_test_id = $("select[name='unit_test_id']").val();
                $.ajax({
                    url: '/admin/unit-test/' + unit_test_id,
                    type: 'POST',
                    data: {
                        _method: "DELETE"
                    },
                    success: function(response) {
                        if (!response.status) {
                            Dcat.error(response.data.message);
                            return false;
                        }
                        Dcat.success(response.data.message);
                        Dcat.reload();
                    },
                    error: function(response) {
                        var errorData = JSON.parse(response.responseText);
                        if (errorData) {
                            Dcat.error(errorData.message);
                        }
                        $('#delete_unit_test').buttonLoading(false);
                    }
                });
            });
        });

        // 保存用例
        $('#save_unit_test').click(function() {
            var name = $("#unit_test_name").val();
            if (name == '') {
                Dcat.swal.error('请填写用例名称');
                return;
            }
            $('#delete_unit_test').buttonLoading();
            var data = $('#run_api').serializeArray();
            data.push({
                "name": "name",
                "value": name,
            });
            data.push({
                "name": "_method",
                "value": "POST",
            });

            $.ajax({
                url: '/admin/unit-test/save',
                type: 'POST',
                data: data,
                success: function(response) {
                    Dcat.success("保存成功");
                    Dcat.reload();
                },
                error: function(response) {
                    var errorData = JSON.parse(response.responseText);
                    if (errorData) {
                        Dcat.error(errorData.message);
                    }
                    $('#delete_unit_test').buttonLoading(false);
                }
            });
        });

    });
</script>