<style>
    .TreeMenuList>div {
        border: ridge;
        margin-bottom: 20px;
    }

    body.dark-mode pre {
        animation: mymove 0.5s;
        background-color: black !important;
        color: lightgray;
        margin-bottom: 10px;
    }

    ul.tabs {
        animation: mymove 0.5s;
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

    .input-example {
        width: 100%;
        overflow: auto !important;
        padding-left: 10px;
        outline: none;
        resize: vertical;
        border-color: gray !important;
        height: 350px;
    }
    body.dark-mode .input-example {
        color: white !important;
        background-color: rgba(0, 0, 0, .09);
    }

    #input_request_example {
        height: 100px;
    }

    body.dark-mode a:hover {
        color: white;
    }

    body.dark-mode a.edit-api:hover {
        color: limegreen;
    }

    h4 {
        margin: 10px 0;
    }
</style>
<div class="row">
    <div class="TreeMenuList col-md-3 col-sm-12">
        <div id="TreeMenu"></div>
    </div>
    <div class="run-panel col-md-9 col-sm-12">

        <pre id="api_desc"><b><br><span class="label bg-success">{{ $model->method }}</span>&nbsp;<span class="label bg-gray">{{ $model->url }}</span>
            <br>接口名称：{{ $model->name }}<br>接口描述：{{ $model->desc }}
        </b></pre>
        <ul class="tabs">
            <li class="active"><a href="#tab1">接口文档</a></li>
            <li><a href="#tab2">在线测试</a></li>
        </ul>
        <div class="tab_container">
            <div id="tab1" class="tab_content" style="display: block; padding: 2%;">

                <h4><span class='label bg-success'>请求头</span></h4>
                @if (!empty($model->header))
                    {!! $model->getParamTable($model->header) !!}
                @else
                    <pre>Empty.</pre>
                @endif
                <br>

                <h4><span class='label bg-success'>请求体</span></h4>
                @if (!empty($model->body))
                    {!! $model->getParamTable($model->body) !!}
                @else
                    <pre>Empty.</pre>
                @endif
                <hr>

                <h4><span class='label bg-success'>请求示例</span></h4>
                <textarea id="input_request_example" class="input-example" placeholder="请输入 curl请求示例">{{ $model->request_example }}</textarea>
                <div class="form-group pull-right">
                    <button name="request_example" type="button" class="btn btn-primary submit-example" data-loading-text="保存中..." autocomplete="off">保存</button>
                </div>
                <br>
                <br>

                <h4><span class='label bg-success'>返回示例</span></h4>
                <textarea id="input_response_example" class="input-example" placeholder="请输入 返回示例">{{ $model->response_example }}</textarea>
                <div class="form-group pull-right">
                    <button name="response_example" type="button" class="btn btn-primary submit-example " data-loading-text="保存中..." autocomplete="off">保存</button>
                </div>
                <br>
                <br>

                <h4><span class='label bg-success'>返回值说明</span></h4>
                <textarea id="input_response_desc" class="input-example" placeholder="请输入 返回值说明">{{ $model->response_desc }}</textarea>
                <div class="form-group pull-right">
                    <button name="response_desc" type="button" class="btn btn-primary submit-example" data-loading-text="保存中..." autocomplete="off">保存</button>
                </div>

            </div>
            <div id="tab2" class="tab_content" style="display: none; ">
                @php
                echo view('unit_test._debug', [
                    'model' => $model
                ]);
                @endphp
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    Dcat.ready(function() {
        $("body").addClass("sidebar-collapse");
        let api_id = '<?php echo $model->id ?>';

        // 保存请求响应示例
        $('.submit-example').click(function() {
            var type = $(this).attr('name');
            var desc = $('#input_' + type).val();
            $(this).buttonLoading();
            $.ajax({
                url: '/admin/api/' + api_id,
                type: 'POST',
                data: {
                    [type]: desc,
                    _method: "PUT"
                },
                success: function(retData) {
                    Dcat.success("操作成功");
                    Dcat.reload();
                },
                error: function(retData) {
                    Dcat.error(retData.responseJSON.message, null, {
                        timeOut: 10000
                    });
                    $('.submit-example').buttonLoading(false);
                }
            });
        });

        let navItems = <?php echo json_encode($model->getNavItems()); ?>;
        //左侧菜单栏
        var menuConfig = {
            treeMenuId: "#TreeMenu",
            superLevel: 2,
            multiple: true,
        };
        treeMenu.init(navItems, menuConfig);

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

</script>