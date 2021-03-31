<style>
    body.dark-mode pre {
        background-color: black !important;
        color: darkgray;
    }

    ul.tabs {
        margin: 0;
        padding: 0;
        float: left;
        list-style: none;
        height: 32px;
        border-bottom: 1px solid #999;
        border-left: 1px solid #999;
        width: 100%;
    }

    ul.tabs li {
        float: left;
        margin: 0;
        padding: 0;
        height: 31px;
        line-height: 31px;
        border: 1px solid #999;
        border-left: none;
        border-bottom: none;
        margin-bottom: -1px;
        background: #837b7b;
        overflow: hidden;
        position: relative;
        color: #fff;
    }

    ul.tabs li a {
        color: #fff;
        text-decoration: none;
        display: block;
        font-size: 1.2em;
        padding: 0 20px;
        outline: none;
    }

    html ul.tabs li.active,
    html ul.tabs li.active a:hover {
        background: #21b978;
        border: 1px solid #21b978;
        color: white;
    }

    .tab_container {
        border: 1px solid #999;
        border-top: none;
        clear: both;
        float: left;
        width: 100%;
        border-radius: .25rem !important;
        -moz-border-radius-bottomright: 5px;
        -khtml-border-radius-bottomright: 5px;
        -webkit-border-bottom-right-radius: 5px;
        -moz-border-radius-bottomleft: 5px;
        -khtml-border-radius-bottomleft: 5px;
        -webkit-border-bottom-left-radius: 5px;
    }

    body.dark-mode .tab_container {
        animation: mymove 0.5s;
        background: url(/img/bg-2.jpg) fixed !important;
        -webkit-transition: none !important;
        transition: none !important;
        background-size: cover !important;
    }

    .submit-example {
        background: linear-gradient(to right, #4a9dd3, #3085d6);
    }

    textarea {
        width: 100%;
        overflow: hidden !important;
        padding-left: 10px;
        outline: none;
        resize: vertical;
        color: white!important;
        border-color: gray!important;
        background-color: rgba(0,0,0,.09);
    }

    body.dark-mode a:hover {
        color: white;
    }

</style>

<div class="run-panel col-md-12 col-sm-12">

    <pre id="api_desc"><b><br><span class="label bg-success">{{ $model->api->method }}</span>&nbsp;<span class="label bg-gray">{{ $model->api->url }}</span>
        <br>接口描述：{{ $model->api->desc }}<br>回归测试：<input id="save_reg_test" type="checkbox" {{ empty($model->regTest) ? "" : "checked" }} /><?php if (!empty($model->regTest)) : ?><br>完全匹配：<input type="radio" name="reg-model" value="{{ $model::REG_TYPE_ALL }}" {{ (!empty($model->regTest) && $model->regTest->type == $model::REG_TYPE_ALL) ? "checked" : "" }}/>&nbsp;请求成功：<input type="radio" name="reg-model" value="{{ $model::REG_TYPE_SUCCESS }} " {{ (!empty($model->regTest) && $model->regTest->type == $model::REG_TYPE_SUCCESS) ? "checked" : "" }}/><?php endif; ?>
    </b></pre>
    <br>
    <ul class="tabs">
        <li class="active"><a href="#tab1">接口文档</a></li>
        <li><a href="#tab2">在线测试</a></li>
    </ul>
    <div class="tab_container">
        <div id="tab1" class="tab_content" style="display: block; padding: 2%;">

            <h3><span class='label bg-success'>请求头</span></h3>
            {!! $model->getParamTable($model->header) !!}
            <br>

            <h3><span class='label bg-success'>请求体</span></h3>
            {!! $model->getParamTable($model->body) !!}
            <br>

            <h3><span class='label bg-success'>请求示例</span></h3>
            <textarea id="input_request_example" class="input-example" placeholder="请输入 curl请求示例"></textarea>
            <div class="form-group pull-right">
                <button name="request_example" type="button" class="btn btn-primary submit-example" data-loading-text="保存中..." autocomplete="off">保存</button>
            </div>
            <br>
            <br>

            <h3><span class='label bg-success'>返回示例</span></h3>
            <textarea id="input_response_example" class="input-example" placeholder="请输入 返回示例" style="min-height:200px;max-height:600px;"></textarea>
            <div class="form-group pull-right">
                <button name="response_example" type="button" class="btn btn-primary submit-example " data-loading-text="保存中..." autocomplete="off">保存</button>
            </div>
            <br>
            <br>

            <h3><span class='label bg-success'>返回值说明</span></h3>
            <textarea id="input_response_desc" class="input-example" placeholder="请输入 返回值说明" style="min-height:200px;max-height:600px;"></textarea>
            <div class="form-group pull-right">
                <button name="response_desc" type="button" class="btn btn-primary submit-example" data-loading-text="保存中..." autocomplete="off">保存</button>
            </div>

        </div>
        <div id="tab2" class="tab_content" style="display: none; ">

            <h3>返回值说明</h3>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        // 保存回归测试
        $(document).on('click', '#save_reg_test', function() {
            uploadExample();
        });
        // 保存回归测试
        $(document).on('click', ':radio[name=reg-model]', function() {
            uploadExample();
        });

        //tab栏
        $(".tab_content").hide();
        $("ul.tabs li:first").addClass("active").show();
        $(".tab_content:first").show();

        $("ul.tabs li").click(function() {
            $("ul.tabs li").removeClass("active");
            $(this).addClass("active");
            $(".tab_content").hide();
            var activeTab = $(this).find("a").attr("href");
            $(activeTab).fadeIn();
            return false;
        });
    });

    function uploadExample() {
        $.ajax({
            url: '/admin/regression-test/save-reg-test',
            type: 'POST',
            data: {
                unit_test_id: '<?php echo $model->id ?? ''; ?>',
                status: Number($("#save_reg_test").prop('checked')),
                type: $(":radio[name=reg-model]:checked").val(),
                _token: LA.token,
            },
            success: function(retData) {
                $.pjax.reload('#api_desc');
                toastr.success("操作成功");
            },
            error: function(retData) {
                toastr.error(retData.responseJSON.message, '', {
                    timeOut: 10000
                });
            }
        });
    }
</script>
