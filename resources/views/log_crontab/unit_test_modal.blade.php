<style>
    .panel {background: rgb(15 14 14 / 40%) !important;border: 1px solid gray;border-radius: 4px;}
    .panel-heading {padding: 10px;border-top-left-radius: 3px;border-top-right-radius: 3px;}
    .panel-title {margin-top: 0;margin-bottom: 0;font-size: 16px;color: inherit;}
    .panel-body {padding: 10px;}
    .collapsing {position: relative;height: 0;overflow: hidden;transition: height .1s ease-in}
</style>
@if (!empty($data))
<div class="panel">
    <div class="panel-heading bg-white">
        <div><a href="#project_response_{{ $data['id'] }}" data-toggle="collapse">
            <span class="label bg-info">项目：{{ $data['project_name'] }}&nbsp;&nbsp;</span>
            <span class="label bg-custom">{{ $data['domain_env'] }}: {{ $data['domain'] }}&nbsp;&nbsp;</span>
            <span class="label {{ $data['success_count'] < $data['total_count'] ? 'bg-danger' : 'bg-success'  }}">成功用例：{{ $data['success_count'] }} / {{ $data['total_count'] }}</span>
        </a></div>
    </div>
    <div id="project_response_{{ $data['id'] }}" class="panel-body collapse show">
        @foreach($data['apiList'] as $api)
        <div class="panel">
            <div class="panel-heading bg-white">
                <div>
                    <a href="#api_response_{{ $api['id'] }}" data-toggle="collapse">
                        <span class="label bg-custom">接口{{ $api['id'] }}：{{ $api['name'] }}&nbsp;&nbsp;</span>
                        <span class="label bg-custom">{{ $api['method'] }}&nbsp;&nbsp;</span>
                        <span class="label bg-gray text-light">{{ $api['url'] }}&nbsp;&nbsp;</span>
                        <span class="label {{ $api['success_count'] < $api['total_count'] ? 'bg-danger' : 'bg-success'  }}">成功用例：{{ $api['success_count'] }}  / {{ $api['total_count'] }}</span>
                    </a>
                    <a href="/admin/run/{{ $api['id'] }}" target="_blank" style="margin-left:20px;"><i title="调试运行" class="fa fa-paper-plane grid-action-icon"></i></a>
            </div>
            </div>
            <div id="api_response_{{ $api['id'] }}" class="panel-body collapse show">
                @foreach($api['unitTestList'] as $unitTest)
                <div class="panel">
                    <div class="panel-heading bg-white">
                        <div><a href="#unitTest_response_{{ $unitTest['id'] }}" data-toggle="collapse">
                            <span class="label bg-custom">回归用例{{ $unitTest['id'] }}：{{ $unitTest['name'] }}&nbsp;&nbsp;</span>
                            <span class="label {{ $unitTest['request_result'] ? 'bg-success' : 'bg-danger' }}">{{ $unitTest['request_result'] ? '请求成功' : '请求失败' }}&nbsp;&nbsp;</span>
                            @if (isset($unitTest['result']))
                            <span class="label {{ $unitTest['result'] ? 'bg-success' : 'bg-danger' }}">{{ $unitTest['result'] ? '匹配成功' : '匹配失败' }}&nbsp;&nbsp;</span>
                            @endif
                        </a></div>
                    </div>
                    <div id="unitTest_response_{{ $unitTest['id'] }}" class="panel-body collapse show">
                    @if (!empty($unitTest['ignore_fields']))
                    <pre>完全匹配时忽略字段：{{ $unitTest['ignore_fields'] }}</pre>
                    @endif
                    @php
                        echo view('json-compare', [
                            'id' => $id . "_" . $unitTest['id'],
                            'json_left'  => ['key' => '回归测试结果', 'value' => $unitTest['response_reg'] ?? ''],
                            'json_right' => ['key' => '当前运行结果', 'value' => $unitTest['response'] ?? ''],
                        ]);
                    @endphp
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
