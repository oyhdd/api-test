<link href="/css/json-viewer.css" type="text/css" rel="stylesheet" />
<style>
    .TreeMenuList>div {border: ridge;margin-bottom: 20px;}
    body.dark-mode pre {background-color: black !important;color: lightgray;margin-bottom: 10px;}
    ul.tabs {margin: 0;padding: 0;float: left;list-style: none;height: 32px;border-bottom: 1px solid #999;border-left: 1px solid #999;width: 100%;}
    ul.tabs li {float: left;margin: 0;padding: 0;height: 31px;line-height: 31px;border: 1px solid #999;border-left: none;border-bottom: none;margin-bottom: -1px;background: #837b7b;overflow: hidden;position: relative;color: #fff;}
    ul.tabs li a {color: #fff;text-decoration: none;display: block;font-size: 1.2em;padding: 0 20px;outline: none;}
    html ul.tabs li.active,
    html ul.tabs li.active a:hover {background: #21b978;border: 1px solid #21b978;color: white;}
    .tab_container {border: 1px solid #999;border-top: none;clear: both;float: left;width: 100%;border-radius: .25rem !important;-moz-border-radius-bottomright: 5px;-khtml-border-radius-bottomright: 5px;-webkit-border-bottom-right-radius: 5px;-moz-border-radius-bottomleft: 5px;-khtml-border-radius-bottomleft: 5px;-webkit-border-bottom-left-radius: 5px;}
    body.dark-mode .tab_container {background: url(/img/bg-2.jpg) fixed !important;-webkit-transition: none !important;transition: none !important;background-size: cover !important;}
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
    .panel {background: rgb(15 14 14 / 40%) !important;border: 1px solid gray;border-radius: 4px;}
    .panel-heading {padding: 10px;border-top-left-radius: 3px;border-top-right-radius: 3px;}
    .panel-title {margin-top: 0;margin-bottom: 0;font-size: 16px;color: inherit;}
    .panel-body {padding: 10px;}
    .collapsing {position: relative;height: 0;overflow: hidden;transition: height .1s ease-in}
    body.dark-mode .vs-checkbox-con .vs-checkbox {border: 2px solid white;}
    body.dark-mode .vs-checkbox-con input:checked~.vs-checkbox {border-color: #4e9876;}
    .header {height: 32px;background-color: #666666;font-family: monospace;padding: 0 32px;}
    .header span {line-height: 32px;}
    .text-line {float: left;height: 100%;box-sizing: content-box;border-right: 1px solid #3c3a3a;color: #999999; margin-right: 5px;}
    .text-num {padding: 0 8px 0 5px;white-space: nowrap; font-family: inconsolata, monospace;}
    .input-buttons{position: absolute; top: 5px; right: 5px; font-size: 28px;}
</style>
<!-- 模态弹出窗 -->
<div id="mymodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden='true' data-backdrop='static'>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="padding:15px 15px 5px;">
                <h2 id="myModalLabel" class="modal-title">回归测试
                    <h6 id="static_info" class="mt-1" style="display: none;">
                        <span class="label bg-info">项目数：<span id="total_project"></span></span>
                        <span class="label bg-info">接口数：<span id="total_api"></span></span>
                        <span class="label bg-info mr-2">用例数：<span id="total_unit"></span></span>
                        <span class="label">成功用例：<span id="success_count"></span></span>
                    </h6>
                </h2>
            </div>
            <div class="modal-body">
                @php
                    $regressList = \App\Models\RegressionTestModel::getRegressList();
                    $projectIds = array_keys($regressList);
                @endphp

                @if (empty($regressList))
                    <pre>当前无可用的回归用例，请先添加回归用例.</pre>
                @endif
                @foreach($regressList as $project)
                <div class="panel">
                    <div class="panel-heading bg-white">
                        <div id="project_{{ $project['id'] ?? '' }}">
                            <h5 style="line-height: 34px;">
                                <div class="row">
                                    <div class="col-md-1">
                                        <div class="vs-checkbox-con float-right" style="margin-top: 9px;">
                                            <input type="checkbox" class="select-all-items {{ $project['id'] }}_select-all-items" data-project_id="{{ $project['id'] }}" value="0">
                                            <span class="vs-checkbox"><span class="vs-checkbox--check"><i class="vs-icon feather icon-check"></i></span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-left">
                                        <span class="label bg-info">
                                            <a href="#project_request_{{ $project['id'] ?? '' }}" data-toggle="collapse">{{ $project['name'] ?? '' }}</a>
                                        </span>
                                    </div>
                                    <br>
                                    <small>选择域名：</small>
                                    <div class="col-md-6">
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
                    <div id="project_request_{{ $project['id'] ?? '' }}" class="panel-body collapse">
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
                                        <th>接口id</th>
                                        <th>接口名称</th>
                                        <th>请求方式</th>
                                        <th>接口地址</th>
                                        <th>接口描述</th>
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
                                        <td>{{ $api['id'] ?? '' }}</td>
                                        <td>{{ $api['name'] ?? '' }}</td>
                                        <td>{{ $api['method'] ?? '' }}</td>
                                        <td>{{ $api['url'] ?? '' }}</td>
                                        <td>{{ $api['desc'] ?? '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach

                <hr>
                <div id="regression_testing_detail"> </div>
            </div>
            <div class="modal-footer">
                <span id="select_rows">已选择 0 项</span>&nbsp;&nbsp;&nbsp;&nbsp;
                <span id="start_regression_test_time">将运行选中的回归用例，运行时可关闭面板，稍后再次点击查看！</span>
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
            <br>接口名称：</b>{{ $model->name }}<br><b>接口描述：</b>{{ $model->desc }}
        </pre>
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
                    echo view('run.debug', [
                        'model' => $model
                    ]);
                @endphp
            </div>
        </div>
    </div>
</div>
<script src="/js/json-viewer.js"></script>
<script src="/js/jsbeautify.js"></script>
<script src="/js/diff.js"></script>
<script type="text/javascript">
    Dcat.ready(function() {
        $(".nav-item a").each(function() {
            var url = $(this).attr('href');
            if (url != undefined && url.indexOf('/admin/run') != -1) {
                $(this).addClass('active');
            }
        })

        let projectIds = <?php echo json_encode($projectIds) ?>;
        let api_url = '<?php echo $model->url ?>';

        $(".breadcrumb").html(
            "<a href='/admin/run/regress-test' class='btn btn-sm btn-success' target='_blank'>" +
            "<i class='feather icon-corner-right-up'></i>" +
            "<span class='d-none d-sm-inline'> 回归测试</span>" +
            "</a>"
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
            var all_selected = 0;
            var selected = 0;
            for (let i = 0; i < projectIds.length; i++) {
                selected = Dcat.grid.selectedRows(projectIds[i]).length;
                all_selected += selected;
                if (selected <= 0) {
                    if ($("." + projectIds[i] + "_select-all-items").val() == 1) {
                        clickSelectAllItems(projectIds[i]);
                    }
                } else {
                    if ($("." + projectIds[i] + "_select-all-items").val() == 0) {
                        clickSelectAllItems(projectIds[i]);
                    }
                }
            }
            if (all_selected) {
                $("#select_rows").html("已选择 " + all_selected + " 项");
            } else {
                $("#select_rows").html("已选择 0 项");
            }
        });

        $(document).on("click", ".left-input .input-split,.left-input .input-expand", onPaneResizeLeftClick);
        $(document).on("click", ".right-input .input-split,.right-input .input-expand", onPaneResizeRightClick);
        function onPaneResizeLeftClick(e) {
            onResize(e, 'left');
        }
        function onPaneResizeRightClick(e) {
            onResize(e, 'right');
        }
        function onResize(e, side) {
            e.preventDefault();
            var otherSide = side === 'left' ? 'right' : 'left';
            var clickClass = e.currentTarget.className;
            if (clickClass === 'input-expand') { // 展开
                $("." + side + "-input").width("100%");
                $("." + otherSide + "-input").hide();
                $("." + side + "-input .input-expand").hide()
                $("." + side + "-input .input-split").show()
                $(".header-" + otherSide).hide();
                $(".header-" + side).show();
            } else { // 分裂
                $("." + side + "-input").width("50%");
                $("." + otherSide + "-input").show();
                $("." + side + "-input .input-expand").show()
                $("." + side + "-input .input-split").hide()
                $(".header-" + otherSide).show();
                $(".header-" + side).show();
            }
        }

        // $("body").addClass("sidebar-collapse");
        let api_id = '<?php echo $model->id ?>';

        $(".select-all-items").click(function (e) {
            // 鼠标点击
            if (e.originalEvent) {
                var project_id = $(this).data("project_id");
                var selector = "." + project_id + "_select-all-items";
                if ($(selector).val() == 1) {
                    $(selector).val(0);
                } else {
                    $(selector).val(1);
                }
                $('.' + project_id + '_grid-select-all').click();
            }
        })

        function clickSelectAllItems(project_id) {
            var selector = "." + project_id + "_select-all-items";
            if ($(selector).val() == 1) {
                $(selector).val(0);
            } else {
                $(selector).val(1);
            }
            $(selector).click();
        }

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
                    url: api_url,
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
            superLevel: 1,
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
            $('#start_regression_test').buttonLoading();
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
            $("#static_info").hide();
            $("#total_project").empty();
            $("#total_api").empty();
            $("#total_unit").empty();
            $("#success_count").empty();
            $('#regression_testing_detail').empty();
            $('#start_regression_test_time').empty().html('正在进行回归测试，可稍后查看！');
            $.ajax({
                url: '/admin/run/regress',
                type: 'POST',
                data: data,
                success: function(retData) {
                    $("#static_info").show();
                    $('#start_regression_test').buttonLoading(false);
                    if (retData.code == 0) {
                        $('#regression_testing_detail').html("<h4>测试结果</4>");
                        $("#total_project").html(retData.data.total_project);
                        $("#total_api").html(retData.data.total_api);
                        $("#total_unit").html(retData.data.total_unit);
                        if (retData.data.success_count < retData.data.total_unit) {
                            $("#success_count").parent().removeClass("bg-success");
                            $("#success_count").parent().addClass("bg-danger")
                        } else {
                            $("#success_count").parent().removeClass("bg-danger");
                            $("#success_count").parent().addClass("bg-success");
                        }
                        $("#success_count").html(retData.data.success_count + " / " + retData.data.total_unit);
                        $('#start_regression_test_time').empty().html('回归测试已完成！');

                        var html = "";
                        var list = retData.data.list;

                        for (let i in list) {
                            var project = list[i];
                            var success_count_class = 'bg-success';
                            if (project["success_count"] < project["total_count"]) {
                                success_count_class = 'bg-danger';
                            }
                            html += '<div class="panel">' + 
                                '<div class="panel-heading bg-white">' + 
                                    '<div><a href="#project_response_' + project["id"] +'" data-toggle="collapse">' + 
                                    '<span class="label bg-info">项目：' + project["name"] + '</span>' +
                                    '&nbsp;&nbsp;<span class="label bg-custom">' + project["domain"] + '</span>' +
                                    '&nbsp;&nbsp;<span class="label ' + success_count_class + '">成功用例：' + project["success_count"] + " / " + project["total_count"] + '</span>' +
                                    '</a></div>' + 
                                '</div>' +
                                '<div id="project_response_' + project["id"] +'" class="panel-body collapse">';
 
                            var apiList = project['apiList'];
                            for (let j in apiList) {
                                var api = apiList[j];
                                var success_count_class = 'bg-success';
                                if (api["success_count"] < api["total_count"]) {
                                    success_count_class = 'bg-danger';
                                }
                                html += '<div class="panel">' + 
                                    '<div class="panel-heading bg-white">' + 
                                        '<div><a href="#api_response_' + api["id"] +'" data-toggle="collapse">' + 
                                        '<span class="label bg-custom">接口：' + api["name"] + '</span>' +
                                        '&nbsp;&nbsp;<span class="label bg-custom">' + api["method"] + '</span>' +
                                        '&nbsp;&nbsp;<span class="label bg-gray text-light">' + api["url"] + '</span>' +
                                        '&nbsp;&nbsp;<span class="label ' + success_count_class + '">成功用例：' + api["success_count"] + " / " + api["total_count"] + '</span>' +
                                        '</a>' +
                                        '<a href="/admin/run/' + api["id"] + '" target="_blank" style="margin-left:20px;"><i title="调试运行" class="fa fa-paper-plane grid-action-icon"></i></a></div>' + 
                                    '</div>' +
                                    '<div id="api_response_' + api["id"] +'" class="panel-body collapse">';

                                var unitTestList = api['unitTestList'];
                                for (let k in unitTestList) {
                                    var unitTest = unitTestList[k];

                                    var result_class = 'bg-danger';
                                    var request_result_class = 'bg-danger';
                                    var request_result = '请求失败';
                                    var result = '匹配失败';
                                    if (unitTest['result']) {
                                        result_class = 'bg-success';
                                        result = '匹配成功';
                                    }
                                    if (unitTest['request_result']) {
                                        request_result_class = 'bg-success';
                                        request_result = '请求成功';
                                    }

                                    var response = toJson(unitTest["response"]);
                                    var response_reg = toJson(unitTest["response_reg"]);
                                    var diff = Diff.diffJson(response_reg, response);

                                    var leftText = rightText = '';
                                    var left_num = right_num = 0;
                                    var modify_flag = left_num_str = right_num_str = '';
                                    diff.forEach(function(part, index){
                                        // green for additions, red for deletions
                                        var color = part.added ? 'green' : (part.removed ? 'red' : 'transparent');
                                        var span = '<span style="background-color: ' + color + '">' + part.value + '</span>';

                                        if (part.removed) {
                                            modify_flag = 'removed';
                                            leftText += span;
                                            var flag = ! (diff[index + 1].added && diff[index + 1].count == part.count);
                                            for (let count = 1; count <= part.count; count++) {
                                                left_num ++;
                                                left_num_str += "<span class='text-num'>" + left_num + "</span><br>";
                                                if (flag) {
                                                    modify_flag = '';
                                                    right_num ++;
                                                    rightText += '<br>';
                                                    right_num_str += "<span class='text-num'>- " + right_num + "</span><br>";
                                                }
                                            }
                                        } else if (part.added) {
                                            rightText += span;
                                            var flag = modify_flag == '' || modify_flag != 'removed';
                                            for (let count = 1; count <= part.count; count++) {
                                                right_num ++;
                                                right_num_str += "<span class='text-num'>" + right_num + "</span><br>";
                                                if (flag) {
                                                    left_num ++;
                                                    leftText += '<br>';
                                                    left_num_str += "<span class='text-num'>- " + left_num + "</span><br>";
                                                }
                                            }
                                            modify_flag = 'added';
                                        } else {
                                            leftText += span;
                                            rightText += span;
                                            modify_flag = '';
                                            for (let count = 1; count <= part.count; count++) {
                                                left_num ++;
                                                right_num ++;
                                                left_num_str += "<span class='text-num'>" + left_num + "</span><br>";
                                                right_num_str += "<span class='text-num'>" + right_num + "</span><br>";
                                            }
                                        }
                                    });

                                    var text_num = left_num - right_num;
                                    if (text_num >= 0) {
                                        for (let i = 0; i < text_num; i++) {
                                            right_num_str += "<span class='text-num'></span><br>";
                                        }
                                    } else {
                                        for (let i = 0; i < -text_num; i++) {
                                            left_num_str += "<span class='text-num'></span><br>";
                                        }
                                    }

                                    html += '<div class="panel">' + 
                                    '<div class="panel-heading bg-white">' + 
                                        '<div><a href="#unitTest_response_' + unitTest["id"] +'" data-toggle="collapse">' + 
                                        '<span class="label bg-custom">用例：' + unitTest["name"] + '</span>' +
                                        '&nbsp;&nbsp;<span class="label ' + request_result_class + '">' + request_result + '</span>';
                                    if (unitTest['result'] !== undefined) {
                                        html += '&nbsp;&nbsp;<span class="label ' + result_class + '">' + result + '</span>';
                                    }
                                    html += '</a></div>' +
                                    '</div>' +
                                    '<div id="unitTest_response_' + unitTest["id"] +'" class="panel-body collapse">' +
                                    '<header class="header"><div class="float-left"><span class="header-left"> 回归测试结果 </span></div><div class="float-right"><span class="header-right"> 当前运行结果 </span></div></header>' +
                                    '<div style="display: flex; max-height: 600px; overflow: auto;">' +
                                    '<div class="left-input" style="position: relative; width:50%;">' +
                                    '<span class="input-buttons"><a class="input-split" href="#" style="display:none;">◫</a><a class="input-expand" href="#">☐</a></span>' +
                                    '<pre><div class="text-line">' + left_num_str + '</div>' + leftText + '</pre></div>' +
                                    '<div class="right-input" style="position: relative; width:50%;">' +
                                    '<span class="input-buttons"><a class="input-split" href="#" style="display:none;">◫</a><a class="input-expand" href="#">☐</a></span>' +
                                    '<pre><div class="text-line">' + right_num_str + '</div>' + rightText + '</pre></div>' +
                                    '</div></div></div>';
                                }
                                html += '</div></div>';
                            }

                            html += '</div></div>';
                        }
                        $('#regression_testing_detail').append(html);
                    } else {
                        $('#start_regression_test_time').empty().html('<span class="text-warning">' + retData.message + '</span>');
                    }
                },
                error: function(retData) {
                    $('#start_regression_test').buttonLoading(false);
                    $('#regression_testing_detail').empty();
                    $('#start_regression_test_time').empty().html('<span class="text-warning">回归测试失败,请重试！</span>');
                }
            });
        });

        function toJson(string) {
            var content = string;
            try {
                if (typeof json != 'object') {
                    content = JSON.parse(string);
                }
            } catch (error) {
                content = eval("(" + json + ")");
            }
            return content;
        }
    });
</script>