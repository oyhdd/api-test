<style>
    .TreeMenuList>div {border: ridge;margin-bottom: 20px;}
    body.dark-mode pre {animation: mymove 0.5s;background-color: black !important;color: lightgray;margin-bottom: 10px;}
    ul.tabs {animation: mymove 0.5s;margin: 0;padding: 0;float: left;list-style: none;height: 32px;border-bottom: 1px solid #999;border-left: 1px solid #999;width: 100%;}
    ul.tabs li {float: left;margin: 0;padding: 0;height: 31px;line-height: 31px;border: 1px solid #999;border-left: none;border-bottom: none;margin-bottom: -1px;background: #837b7b;overflow: hidden;position: relative;color: #fff;}
    ul.tabs li a {color: #fff;text-decoration: none;display: block;font-size: 1.2em;padding: 0 20px;outline: none;}
    html ul.tabs li.active,
    html ul.tabs li.active a:hover {background: #21b978;border: 1px solid #21b978;color: white;}
    .tab_container {border: 1px solid #999;border-top: none;clear: both;float: left;width: 100%;border-radius: .25rem !important;-moz-border-radius-bottomright: 5px;-khtml-border-radius-bottomright: 5px;-webkit-border-bottom-right-radius: 5px;-moz-border-radius-bottomleft: 5px;-khtml-border-radius-bottomleft: 5px;-webkit-border-bottom-left-radius: 5px;}
    body.dark-mode .tab_container {animation: mymove 0.5s;background: url(/img/bg-2.jpg) fixed !important;-webkit-transition: none !important;transition: none !important;background-size: cover !important;}
    .submit-example {background: linear-gradient(to right, #4a9dd3, #3085d6);}
    .input-example {width: 100%;overflow: auto !important;padding-left: 10px;outline: none;resize: vertical;border-color: gray !important;height: 350px;}
    body.dark-mode .input-example {color: white !important;background-color: rgba(0, 0, 0, .09);}
    #input_request_example {height: 100px;}
    body.dark-mode a:hover {color: white;}
    body.dark-mode a.edit-api:hover {color: limegreen;}
    h4 {margin: 10px 0;}
    #mymodal {background-color: rgba(0, 0, 0, .3) !important;}
    @media (min-width:900px) {.modal-dialog {max-width: 75%;}}
    .modal-footer {display: block;}
    #start_regression_test_time {line-height: 34px;font-size: 12px;}
    .table td {height: 34px;line-height: 34px;}
    option {color: #ececec;background: #2b4048;}
    .panel {background: url(/img/bg-1.jpg) fixed !important;border: 2px solid transparent;border-radius: 4px;}
    .panel-heading {padding: 10px;border-top-left-radius: 3px;border-top-right-radius: 3px;}
    .panel-title {margin-top: 0;margin-bottom: 0;font-size: 16px;color: inherit;}
    .panel-body {padding: 10px;}
    .collapsing {position: relative;height: 0;overflow: hidden;transition: height .1s ease-in}</style>
</style>
<!-- 模态弹出窗 -->
<div id="mymodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden='true' data-backdrop='static'>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="padding:15px 15px 5px;">
                <h2 id="myModalLabel" class="modal-title">回归测试
                    <h6 id="static_info" class="mt-1" style="display: block;">
                        <span id="total_project" class="label bg-info">项目数：0</span>
                        <span id="total_api" class="label bg-info">接口数：0</span>
                        <span id="total_unit" class="label bg-info mr-2">用例数：0</span>
                        <span id="fail_count" class="label bg-success">成功：0</span>
                        <span id="success_count" class="label bg-success">失败：0</span>
                    </h6>
                </h2>
            </div>
            <div class="modal-body">
                @php
                    $regressList = $model::getRegressList();
                    $projectIds = array_keys($regressList);
                @endphp

                @foreach($regressList as $project)
                <div class="panel">
                    <div class="panel-heading bg-white">
                        <div id="project_{{ $project['id'] ?? '' }}">
                            <h5 style="line-height: 34px;">
                                <div class="row">
                                    <div class="col-lg-3 text-right">
                                        <span class="label bg-info">
                                            <a href="#project_content_{{ $project['id'] ?? '' }}" data-toggle="collapse">{{ $project['name'] ?? '' }}</a>
                                        </span>
                                    </div>
                                    <br>
                                    <div class="col-lg-6">
                                        <select id="run_env_{{ $project['id'] ?? '' }}" class="form-control">
                                            @foreach($project['domain'] as $domain)
                                            <option>{{ $domain }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </h5>
                        </div>
                    </div>
                    <div id="project_content_{{ $project['id'] ?? '' }}" class="panel-body collapse">
                        <div>
                            <table class="table default-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="vs-checkbox-con vs-checkbox-primary checkbox-grid checkbox-grid-header">
                                                <input type="checkbox" class="select-all {{ $project['id'] ?? '' }}_grid-select-all">
                                                <span class="vs-checkbox"><span class="vs-checkbox--check"><i class="vs-icon feather icon-check"></i></span></span>
                                            </div>
                                        </th>
                                        <th>接口名称</th>
                                        <th>测试描述</th>
                                        <th>接口地址</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project['apiList'] as $api)
                                    <tr>
                                        <td>
                                            <div class="vs-checkbox-con vs-checkbox-primary checkbox-grid checkbox-grid-column" style="height: 34px;">
                                                <input type="checkbox" class="{{ $project['id'] ?? '' }}_grid-row-checkbox grid-row-checkbox" data-id="{{ $api['id'] ?? '' }}" data-label="{{ $api['name'] ?? '' }}">
                                                <span class="vs-checkbox"><span class="vs-checkbox--check"><i class="vs-icon feather icon-check"></i></span></span>
                                            </div>
                                        </td>
                                        <td>{{ $api['name'] ?? '' }}</td>
                                        <td>{{ $api['desc'] ?? '' }}</td>
                                        <td>{{ $api['url'] ?? '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <hr>
                @endforeach

                <br>
                <div id="regression_testing_detail"> </div>
            </div>
            <div class="modal-footer">
                <span id="select_rows">已选择 0 项</span>&nbsp;&nbsp;&nbsp;&nbsp;
                <span id="start_regression_test_time">将运行选中的回归测试用例，运行时可关闭面板，稍后再次点击查看！</span>
                <button id="start_regression_test" type="button" class="btn btn-success  pull-right">开始测试</button>
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

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
        let projectIds = <?php echo json_encode($projectIds) ?>;

        $(".breadcrumb").html(
            "<div data-toggle='modal' data-target='#mymodal' class='btn btn-sm btn-success'>" +
            "<i class='feather icon-corner-right-up'></i>" +
            "<span class='d-none d-sm-inline'> 回归测试</span>" +
            "</div>"
        );

        // 回归测试面板行选择器
        for (let i = 0; i < projectIds.length; i++) {
            var checkboxSelector = '.' + projectIds[i] + '_grid-row-checkbox';
            var selectAllSelector = '.' + projectIds[i] + '_grid-select-all';
            var selector = Dcat.RowSelector({
                checkboxSelector: checkboxSelector,
                selectAllSelector: selectAllSelector,
                clickRow: false,
                background: '#f6fbff',
            });
            Dcat.grid.addSelector(selector, projectIds[i]);
        }
        $(document).off('change', '.grid-row-checkbox').on('change', '.grid-row-checkbox', function() {
            var selected = 0;
            for (let i = 0; i < projectIds.length; i++) {
                selected += Dcat.grid.selectedRows(projectIds[i]).length;
            }
            if (selected) {
                $("#select_rows").html("已选择 " + selected + " 项");
            } else {
                $("#select_rows").html("已选择 0 项");
            }
        });


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

        //回归测试
        $('#start_regression_test').click(function() {
            var data = [];
            for (let i = 0; i < projectIds.length; i++) {
                var project_id = projectIds[i];
                var api_ids = Dcat.grid.selected(project_id);
                data.push({
                    "name": "api[" + project_id + "][api_ids]",
                    "value": Dcat.grid.selected(project_id),
                }, {
                    "name": "api[" + project_id + "][domain]",
                    "value": $("#run_env_" + project_id).val(),
                });
            }
            $('#start_regression_test').buttonLoading();
            $('#regression_testing_detail').empty();
            $('#start_regression_test_time').empty();
            $("#total_api").empty();
            $("#total_unit").empty();
            $("#success_count").empty();
            $("#fail_count").empty();
            $('#start_regression_test_time').append('正在进行回归测试，可稍后查看！');
            $.ajax({
                url: '/admin/run/regress',
                type: 'POST',
                data: data,
                success: function(retData) {

                    $("#static_info").show();
                    $('#start_regression_test').buttonLoading(false);
                    $('#regression_testing_detail').empty();
                    if (retData.code == 0) {
                        $("#total_api").html('接口：' + retData.data.total_api);
                        $("#total_unit").html('用例：' + retData.data.total_unit);
                        $("#success_count").html('成功：' + retData.data.success_count);
                        $("#fail_count").html('失败：' + retData.data.fail_count);
                        if (retData.data.fail_count > 0) {
                            $("#fail_count").addClass('label-danger');
                        }

                        var html = "";
                        var list = retData.data.list;

                        for (var i in list) {
                            var temp = list[i];

                            var fail_count_class = 'label label-success';
                            if (temp.fail_count > 0) {
                                fail_count_class = 'label label-danger';
                            }

                            html += "<div class='panel panel-default'><div class='panel-heading' style='background-color: #e9e9ec;'>" +
                                "<h3 class='panel-title' style='word-break: break-word;'><span class='label label-primary'>" +
                                temp.title + "</span>&nbsp;<span class='label label-info'>" + temp.method + "</span>&nbsp;&nbsp;<span class='label label-default'>" +
                                temp.url + "</span>&nbsp;" +
                                "<span class='" + fail_count_class + "'>失败：" +
                                temp.fail_count + "</span>&nbsp;" +
                                "<a data-toggle='collapse' href='#collapse_api_" + i + "'>" +
                                "<span  class='collapse_click glyphicon glyphicon-chevron-right'></span></a>" +
                                "</h3></div><div id='collapse_api_" + i + "' class='panel-collapse collapse'><div class='panel-body'>";

                            for (var j in temp.list) {
                                var sub_temp = temp.list[j];

                                var success_status = '失败';
                                var success_class = "label label-danger";
                                if (sub_temp.success) {
                                    success_status = '成功';
                                    success_class = "label label-success";
                                }

                                var response = sub_temp.response
                                response = JSON.stringify(response);
                                response = js_beautify(response, 4, ' ');

                                html += "<div class='panel panel-default'><div class='panel-heading' style='padding: 10px 5px;'><h4 class='panel-title'>" +
                                    "<span class='label label-info'>" + sub_temp.test_title + "</span>&nbsp;&nbsp;<span class='" +
                                    success_class + "'>" + success_status + "</span>&nbsp;&nbsp;<a data-toggle='collapse' href='#collapse_unit_test" +
                                    sub_temp.id + "'><span class='label label-warning'>查看结果</span></a></h4></div><div id='collapse_unit_test" +
                                    sub_temp.id + "' class='panel-collapse collapse'><div class='panel-body'><pre><xmp>" +
                                    response + "</xmp></pre></div></div></div>";
                            }
                            html += "</div></div></div>";
                        }
                        $('#regression_testing_detail').append(html);

                        $(".collapse_click").on("click", function() {
                            $(this).toggleClass('glyphicon-chevron-down');
                        });
                    } else {
                        $('#start_regression_test_time').empty();
                        $('#start_regression_test_time').append('<span class="text-warning">回归测试失败,请重试！</span>');
                    }
                },
                error: function(retData) {
                    $('#start_regression_test').buttonLoading(false);
                    $('#start_regression_test_time').empty();
                    $('#regression_testing_detail').empty();
                    $('#start_regression_test_time').append('<span class="text-warning">回归测试失败,请重试！</span>');
                }
            });
        });
    });
</script>