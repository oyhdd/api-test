<style>
    .header {height: 32px;background-color: #666666;font-family: monospace;padding: 0 32px;}
    .header span {line-height: 32px;}
    .input-buttons{position: absolute; top: 5px; right: 5px; font-size: 28px;}
    .text-line {float: left;height: 100%;box-sizing: content-box;border-right: 1px solid #3c3a3a;color: #999999; margin-right: 5px;}
    .text-num {padding: 0 8px 0 5px;white-space: nowrap; font-family: inconsolata, monospace;}
    .clear {clear: both;}
</style>

<header class="header compare-content-{{ $id ?? 0 }}">
    <div class="float-left">
        <span class="header-left"> {{ $json_left['key'] ?? '回归测试结果' }} </span>
    </div>
    <div class="float-right">
        <span class="header-right"> {{ $json_right['key'] ?? '当前运行结果' }} </span>
    </div>
</header>

<div class="compare-content-{{ $id ?? 0 }}" style="display: flex; max-height: 600px; overflow: auto;">
    <div class="left-input" style="position: relative; width:50%;">
        <span class="input-buttons">
            <a class="input-split" href="#" style="display:none;" data-id="{{ $id ?? 0 }}">◫</a>
            <a class="input-expand" href="#" data-id="{{ $id ?? 0 }}">☐</a>
        </span>
        <pre style="display: flex;">
            <div id="left_line_{{ $id ?? 0 }}" class="text-line"></div>
            <div id="left_text_{{ $id ?? 0 }}"></div>
        </pre>
    </div>
    <div class="right-input" style="position: relative; width:50%;">
        <span class="input-buttons">
            <a class="input-split" href="#" style="display:none;" data-id="{{ $id ?? 0 }}">◫</a>
            <a class="input-expand" href="#" data-id="{{ $id ?? 0 }}">☐</a>
        </span>
        <pre style="display: flex;">
            <div id="right_line_{{ $id ?? 0 }}" class="text-line"></div>
            <div id="right_text_{{ $id ?? 0 }}"></div>
        </pre>
    </div>
</div>
<div class="clear"></div>

<script src="/js/diff.js"></script>
<script type="text/javascript">
    Dcat.onPjaxLoaded(function() {

        var id = "<?php echo $id ?? 0; ?>";
        var leftJson = <?php echo $json_left["value"] ?? '{}'; ?>;
        var rightJson = <?php echo $json_right["value"] ?? '{}'; ?>;

        var diff = Diff.diffJson(leftJson, rightJson);
        var leftText = rightText = '';
        var left_num = right_num = 0;
        diff.forEach(function(part){
            // green for additions, red for deletions
            var color = part.added ? 'green' : (part.removed ? 'red' : 'transparent');
            var span = '<span style="background-color: ' + color + '">' + part.value + '</span>';
            if (part.removed) {
                leftText += span;
                left_num += part.count;
            } else if (part.added) {
                rightText += span;
                right_num += part.count;
            } else {
                leftText += span;
                rightText += span;
                left_num += part.count;
                right_num += part.count;
            }
        });

        var response_num = left_num > right_num ? left_num : right_num;
        var response_num_str = "";
        for (let index = 1; index <= response_num; index++) {
            response_num_str += "<span class='text-num'>" + index + "</span><br>";
        }
        $('#left_line_' + id).html(response_num_str);
        $('#right_line_' + id).html(response_num_str);
        $('#left_text_' + id).html(leftText);
        $('#right_text_' + id).html(rightText);

        $(document).on("click", ".left-input .input-split,.left-input .input-expand", onPaneResizeLeftClick);
        $(document).on("click", ".right-input .input-split,.right-input .input-expand", onPaneResizeRightClick);
        function onPaneResizeLeftClick(e) {
            onResize(e, 'left', $(this).attr('data-id'));
        }
        function onPaneResizeRightClick(e) {
            onResize(e, 'right', $(this).attr('data-id'));
        }
        function onResize(e, side, id) {
            e.preventDefault();
            var otherSide = side === 'left' ? 'right' : 'left';
            var clickClass = e.currentTarget.className;
            if (clickClass === 'input-expand') { // 展开
                $(".compare-content-" + id +  " ." + side + "-input").width("100%");
                $(".compare-content-" + id +  " ." + otherSide + "-input").hide();
                $(".compare-content-" + id +  " ." + side + "-input .input-expand").hide()
                $(".compare-content-" + id +  " ." + side + "-input .input-split").show()
                $(".compare-content-" + id +  " .header-" + otherSide).hide();
                $(".compare-content-" + id +  " .header-" + side).show();
            } else { // 分裂
                $(".compare-content-" + id +  " ." + side + "-input").width("50%");
                $(".compare-content-" + id +  " ." + otherSide + "-input").show();
                $(".compare-content-" + id +  " ." + side + "-input .input-expand").show()
                $(".compare-content-" + id +  " ." + side + "-input .input-split").hide()
                $(".compare-content-" + id +  " .header-" + otherSide).show();
                $(".compare-content-" + id +  " .header-" + side).show();
            }
        }
    });
</script>