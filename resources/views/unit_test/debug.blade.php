<style>
    textarea.form-control {padding: 6px 12px;line-height: 1.42857;height: 34px;}
    .response_panel {padding: 18px 20px 10px 18px;}
    .save-btn {width: 110px;color: white;border-top-left-radius: 0;}
    .form-group input {width: 100%;}
    .input-group {margin: 10px 0;}
    #response {min-height: 235px;color: white;}
    .help-block {font-size: 12px;}
    .form-group .control-label {text-align: left !important;}
    .json-string {color: #FFFFFF;}
    .json-literal {color: #FFFFFF;font-weight: bold;}
    a .json-string{color: lightgreen;}
    #response .btn-pre-copy{-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;-khtml-user-select: none;user-select: none;position: absolute;right: 30px;font-size: 12px;line-height: 1;cursor: pointer;color: hsla(0,0%,54.9%,.8);transition: color .1s;}
    h4.pull-right{float: left !important; margin-left: 12px;}
</style>
<div class="row">
    <div class="col-md-5 col-sm-12">
        @php
        $regTest = $model->regTest->toArray();
        $domain = $model->project->domain;
        foreach ($domain as $key => $value) {
            $domain[$key]['value'] = $value['key'] . ' : ' . $value['value'];
        }
        $domainOptions = array_column($domain, 'value', 'key');

        $form = new \Dcat\Admin\Widgets\Form();
        $form->action(request()->fullUrl())->setFormId('run_api')->ajax(false);
        $form->hidden('_method')->default("PUT");
        $form->hidden('_token')->default(csrf_token());
        $form->hidden('url')->default($model->url);
        $form->hidden('method')->default($model->method);
        $form->hidden('project_id')->default($model->project->id);
        $form->hidden('api_id')->default($model->id);
        $form->select('domain', '运行环境')->options($domainOptions)->default(key($domainOptions))->required();
        $form->select('unit_test_id', '测试用例')->options(array_column($model->unitTest->toArray(), 'name', "id"));
        $form->divider();

        $form->embeds('header', '请求头', function ($form) use ($model) {
            foreach ($model['header'] as $param) {
                if ($param['is_necessary']) {
                    $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->width(12, 12)->required();
                } else {
                    $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->width(12, 12);
                }
            }

            if (empty($model['header'])) {
                $form->html("Empty.");
            }
        })->saveAsJson();

        $form->embeds('body', '请求体', function ($form) use ($model) {
            foreach ($model['body'] as $key => $param) {
                if ($param['is_necessary']) {
                    $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->width(12, 12)->required();
                } else {
                    $form->textarea($param['key'], $param['key'])->placeholder($param['desc'] ? $param['desc'] : "请输入 {$param['key']}")->width(12, 12);
                }
            }

            if (empty($model['body'])) {
                $form->html("Empty.");
            }
        })->saveAsJson();

        $form->width(12, 12);

        $form->addVariables(['footer' =>
        '<div class="box-footer row d-flex">
            <div class="col-md-2"> &nbsp;</div>
            <div class="col-md-8">
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

        <hr>
        <div>
            <div>回归测试：<input id="save_reg_test" type="checkbox" /></div>
            <div class="regression-type" style="display: none;">
                请求成功：<input class="reg-model" type="radio" name="reg-model" value="{{ $model::REG_TYPE_SUCCESS }}" />&nbsp;完全匹配：<input type="radio" class="reg-model" name="reg-model" value="{{ $model::REG_TYPE_ALL }}" />
            </div>
            <div class="regression-type-all form-group row form-field" style="display: none;">
                <label class="col-md-3 text-capitalize control-label">匹配时忽略字段:</label>
                <div class="col-md-9">
                    <select class="form-control" id="ignore_fields" multiple="multiple"></select>
                </div>
            </div>
        </div>
        <div class="input-group">
            <input id="unit_test_name" type="text" class="form-control" placeholder="请输入 用例名称">
            <span class="input-group-btn">
                <button id="save_unit_test" type="button" class="btn save-btn bg-success" autocomplete="off"><i class="feather icon-save"></i> 保存用例</button>
            </span>
        </div>
        <span class="help-block">
            <i class="fa feather icon-help-circle"></i>&nbsp;若用例名称不存在，则新建测试用例
        </span>

        <hr>
        <pre id="ret">HTTP状态码：</br>请求时间：</br>curl请求示例：</pre>
        <pre id="response">返回内容：</pre>
    </div>
</div>

<script type="text/javascript">

    Dcat.ready(function() {
        $("#ignore_fields").select2({
            language : "zh-CN",
            placeholder:"请选择 需要忽略的字段",
            allowClear: true,
            multiple: true,
            width: '100%'
        })

        let api_response = '';
        let can_save = false;
        let unitTest = <?php echo json_encode($model->unitTest->toArray()); ?>;
        let regTest = <?php echo json_encode($regTest); ?>;

        // 显示与隐藏回归模式
        $('#save_reg_test').click(function() {
            if ($("#save_reg_test").prop('checked')) {
                $(".regression-type").show();
                if (typeof $(":radio[name='reg-model']:checked").val() == 'undefined') {
                    $(":radio[name='reg-model']").eq(0).prop("checked", true);
                }
            } else {
                $(".regression-type").hide();
            }
        });

        // 显示与隐藏忽略字段
        $(":radio[name='reg-model']").change(function() {
            if ($(":radio[name='reg-model']:checked").val() == 1) {
                $(".regression-type-all").show();
            } else {
                $(".regression-type-all").hide();
            }
        });

        // 执行复制代码操作
        Dcat.init('.btn-pre-copy', function ($this, id) {
            $this.on('click', function () {
                let btn = $(this);
                let pre = btn.parent();
                //为了实现复制功能。新增一个临时的textarea节点。使用他来复制内容
                let temp = $("<textarea></textarea>");
                //避免复制内容时把按钮文字也复制进去。先临时置空
                btn.text("");
                temp.text(pre.text());
                temp.appendTo(pre);
                temp.select();
                document.execCommand("Copy");
                temp.remove();
                //修改按钮名
                btn.text("复制成功");
                //3s后把按钮名改回来
                setTimeout(()=> {
                    btn.text("复制代码");
                },3000);
            });
        });

        $("select[name='domain']").change(function() {
            switchRegression();
        });

        // 自动加载测试用例
        $("select[name='unit_test_id']").change(function() {
            var unit_test_id = $("select[name='unit_test_id']").val();

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

            $("#unit_test_name").val($("select[name='unit_test_id'] :selected").text());

            switchRegression();
        });

        function switchRegression() {
            var unit_test_id = $("select[name='unit_test_id']").val();
            $(".regression-type-all").hide();
            $("#ignore_fields").empty();
            $("#save_reg_test").prop('checked', false);
            $(".regression-type").hide();
            $(":radio[name='reg-model']").prop("checked", false);
            for (const key in regTest) {
                if (regTest[key]['unit_test_id'] == unit_test_id && regTest[key]['domain'] == $("select[name='domain'] :selected").val()) {
                    $("#save_reg_test").prop('checked', true);
                    $(".regression-type").show();
                    $(":radio[name='reg-model'][value='" + regTest[key]['type'] + "']").prop("checked", true);
                    if (regTest[key]['type'] == 1) {
                        $(".regression-type-all").show();
                        if (regTest[key]['ignore_fields'] != null && regTest[key]['ignore_fields'] != '') {
                            var ignore_fields = (regTest[key]['ignore_fields']).split(',');
                            for (const key in ignore_fields) {
                                var option = new Option(ignore_fields[key], ignore_fields[key], true, true);
                                $("#ignore_fields").append(option);
                            }
                            $("#ignore_fields").trigger('change');
                        }
                    }
                }
            }
        }

        // 运行用例
        $('#run_api').form({
            validate: true, //开启表单验证
            success: function(response) {
                can_save = true;
                if (typeof response != 'object') {
                    $('#response').html(response);
                    $('#response').prepend("<span class=\"btn-pre-copy\">复制代码</span>");
                    return;
                }
                var result = {};
                var detail = response.detail;
                api_response = response.result;
                $('#ret').html("HTTP状态码：" + detail.status_code + "<br>请求时间：" + detail.request_time + "ms" + "<br><hr>curl请求示例：<br>" + detail.curl_example);
                if (detail.status_code == 200) {
                    $('#ret').css({
                        color: 'lightgreen'
                    });
                } else {
                    $('#ret').css({
                        color: "#dda451"
                    });
                }

                try {
                    if (typeof api_response === 'string') {
                        //防中文乱码
                        result = eval("(" + api_response + ")")
                    }
                    $('#response').jsonViewer(result, {withQuotes:true});
                } catch (error) {
                    $('#response').html(api_response);
                }
                $('#response').prepend("<span class=\"btn-pre-copy\">复制代码</span>");

                var unit_test_id = $("select[name='unit_test_id']").val();
                var ori_ignore_fields = [];

                if (regTest[unit_test_id] != null && regTest[unit_test_id]['ignore_fields'] != null && regTest[unit_test_id]['ignore_fields'] != '') {
                    ori_ignore_fields = (regTest[unit_test_id]['ignore_fields']).split(',');
                }
                var ignore_fields = Object.keys(result);
                for (const key in ignore_fields) {
                    if (ori_ignore_fields.indexOf(ignore_fields[key]) === -1) {
                        var option = new Option(ignore_fields[key], ignore_fields[key], false, false);
                        $("#ignore_fields").append(option);
                    }
                }
                $("#ignore_fields").trigger('change');
            },
            error: function(response) {
                var errorData = JSON.parse(response.responseText);
                if (errorData) {
                    Dcat.error(errorData.message);
                    var result = JSON.stringify(errorData);
                    var formatText = js_beautify(result, 4, ' ');
                    $('#response').html(formatText);
                    $('#response').prepend("<span class=\"btn-pre-copy\">复制代码</span>");
                }
                $('#ret').html("服务器内部错误！请联系管理员<br>" + errorData.message);
                $('#ret').css({
                    color: "orangered"
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
            if (!can_save) {
                Dcat.swal.error('', '请先运行用例并确保测试结果正确');
                return;
            }

            var name = $("#unit_test_name").val();
            if (name == '') {
                Dcat.swal.error('', '请填写用例名称');
                return;
            }
            $('#save_unit_test').buttonLoading();
            var data = $('#run_api').serializeArray();
            data.push({
                "name": "name",
                "value": name,
            }, {
                "name": "_method",
                "value": "POST",
            }, {
                "name": "api_response",
                "value": api_response,
            }, {
                "name": "type",
                "value": $(":radio[name=reg-model]:checked").val(),
            }, {
                "name": "regression_status",
                "value": Number($("#save_reg_test").prop('checked')),
            }, {
                "name": "ignore_fields",
                "value": $("#ignore_fields").val(),
            });

            $.ajax({
                url: '/admin/unit-test/save',
                type: 'POST',
                data: data,
                success: function(response) {
                    $('#save_unit_test').buttonLoading(false);
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
                    $('#save_unit_test').buttonLoading(false);
                }
            });
        });

    });
</script>