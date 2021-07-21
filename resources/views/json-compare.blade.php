<link rel="stylesheet" href="/css/codemirror.css" charset="utf-8" />
<link rel="stylesheet" href="/css/tomorrow-night.css" charset="utf-8" />
<style>
    .header {height: 32px;background-color: #666666;font-family: monospace;padding: 0 32px;}
    .header span {line-height: 32px;}
    .clear {clear: both;}
    div.CodeMirror-code > div > pre {border: none !important;}

    .json-diff-input {height: 100%; box-sizing: border-box; display: inline-block; float: left; position: relative;}
    .json-diff-input .CodeMirror {height: 100%;}
    .json-diff-input:hover .input-buttons {opacity: 1;}
    .json-diff-input.collapse .input-buttons {display: none;}
    .input-buttons a:hover {opacity: .7;}
    .json-diff-input .input-buttons {transition: opacity .2s; opacity: 0; position: absolute; right: 19px; top: 0; z-index: 4;}
    .json-diff-input .input-buttons a {color: white; text-decoration: none; font-size: 26px;}
    .lighttheme .json-diff-input .input-buttons a {color: #1D1F21;}
    .json-diff-input .input-buttons a.input-split {font-size: 33px; position: relative; top: 5px;}
    .json-diff-input.split {width: 50%;}
    .json-diff-input.collapse {width: 0%;}
    .json-diff-input.expand {width: 100%;}
    .json-diff-input.split .input-split {display: none;}
    .json-diff-input.collapse .input-collapse {display: none;}
    .json-diff-input.expand .input-expand {display: none;}
    .diff-inputs {width: 100%; float: left; height: calc(100% - 48px);}


</style>

<header class="header">
    <div class="float-left">
        <span class="header-left"> {{ $json_left['key'] ?? '回归测试结果' }} </span>
    </div>
    <div class="float-right">
        <span class="header-right"> {{ $json_right['key'] ?? '当前运行结果' }} </span>
    </div>
</header>

<div class="diff-inputs">
    <div class="left-input json-diff-input split">
        <textarea id="json-diff-left-{{ $id ?? 0 }}"></textarea>
        <span class="input-buttons">
            <a class="input-split" href="#">◫</a>
            <a class="input-expand" href="#">☐</a>
        </span>
    </div>
    <div class="right-input json-diff-input split">
        <textarea id="json-diff-right-{{ $id ?? 0 }}"></textarea>
        <span class="input-buttons">
            <a class="input-split" href="#">◫</a>
            <a class="input-expand" href="#">☐</a>
        </span>
    </div>
</div>

<div class="clear"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
<script src="/js/json-patch-duplex.min.js" charset="utf-8"></script>
<script src="/js/backbone-events-standalone.min.js" charset="utf-8"></script>
<script src="/js/codemirror.js" charset="utf-8"></script>
<script src="/js/json-source-map.js" charset="utf-8"></script>
<script src="/js/jsbeautify.js"></script>
<script type="text/javascript">
    Dcat.onPjaxLoaded(function() {
        var id = "<?php echo $id ?? 0; ?>";

        $('.left-input').on('click', '.input-collapse,.input-split,.input-expand', onPaneResizeLeftClick);
        $('.right-input').on('click', '.input-collapse,.input-split,.input-expand', onPaneResizeRightClick);

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
            $('.json-diff-input').removeClass('split');
            $('.json-diff-input').removeClass('expand');
            $('.json-diff-input').removeClass('collapse');
            var sideClass = 'split';
            var otherSideClass = 'split';
            if (clickClass === 'input-collapse') {
                sideClass = 'collapse';
                otherSideClass = 'expand';
            } else if (clickClass === 'input-expand') {
                sideClass = 'expand';
                otherSideClass = 'collapse';
                $(".header-" + otherSide).hide();
            } else {
                $(".header-" + side).show();
                $(".header-" + otherSide).show();
            }
            console.log(side,clickClass)
            $('.' + side + '-input').addClass(sideClass);
            $('.' + otherSide + '-input').addClass(otherSideClass);
        }

        function JsonInputView(el) {
            this.el = el;
            this.codemirror = CodeMirror.fromTextArea(this.el, {
                lineNumbers: true,
                mode: {name: "javascript", json: true},
                matchBrackets: true,
                theme: 'tomorrow-night',
                readOnly: true,
                styleActiveLine: false,
            });
        }
    
        JsonInputView.prototype.getText = function () {
            return this.codemirror.getValue();
        };
    
        JsonInputView.prototype.setText = function (text) {
            return this.codemirror.setValue(text);
        };
    
        JsonInputView.prototype.highlightRemoval = function (diff) {
            this._highlight(diff, '#DD4444');
        };
    
        JsonInputView.prototype.highlightAddition = function (diff) {
            this._highlight(diff, isLightTheme() ? '#4ba2ff' : '#2E6DFF');
        };
    
        JsonInputView.prototype.highlightChange = function (diff) {
            this._highlight(diff, isLightTheme() ? '#E5E833' : '#9E9E00');
        };
    
        JsonInputView.prototype._highlight = function (diff, color) {
            var pos = getStartAndEndPosOfDiff(this.getText(), diff);
            this.codemirror.markText(pos.start, pos.end, {
                css: 'background-color: ' + color
            });
        };
    
        JsonInputView.prototype.clearMarkers = function () {
            this.codemirror.getAllMarks().forEach(function (marker) {
                marker.clear();
            });
        };
    
        function getStartAndEndPosOfDiff(textValue, diff) {
            var result = parse(textValue);
            var pointers = result.pointers;
            var path = diff.path;
            var start = {
                line: pointers[path].key ? pointers[path].key.line : pointers[path].value.line,
                ch: pointers[path].key ? pointers[path].key.column : pointers[path].value.column
            };
            var end = {
                line: pointers[path].valueEnd.line,
                ch: pointers[path].valueEnd.column
            };
            return {
                start: start,
                end: end
            }
        }

        function isLightTheme() {
            return $('body').hasClass('lighttheme');
        }
    
        BackboneEvents.mixin(JsonInputView.prototype);
    
        var leftInputView = new JsonInputView(document.getElementById('json-diff-left-' + id));
        var rightInputView = new JsonInputView(document.getElementById('json-diff-right-' + id));
    
        leftInputView.codemirror.on('scroll', function () {
            var scrollInfo = leftInputView.codemirror.getScrollInfo();
            rightInputView.codemirror.scrollTo(scrollInfo.left, scrollInfo.top);
        });
        rightInputView.codemirror.on('scroll', function () {
            var scrollInfo = rightInputView.codemirror.getScrollInfo();
            leftInputView.codemirror.scrollTo(scrollInfo.left, scrollInfo.top);
        });

        function compareJson(leftJson, rightJson) {
            leftText = JSON.stringify(leftJson);
            leftText = js_beautify(leftText, 4, ' ');
            rightText = JSON.stringify(rightJson);
            rightText = js_beautify(rightText, 4, ' ');

            leftInputView.clearMarkers();
            rightInputView.clearMarkers();
            leftInputView.codemirror.setValue(leftText)
            rightInputView.codemirror.setValue(rightText)
            var diffs = jsonpatch.compare(leftJson, rightJson);
            window.diff = diffs;
            
            diffs.forEach(function (diff) {
                try {
                    if (diff.op === 'remove') {
                        leftInputView.highlightRemoval(diff);
                    } else if (diff.op === 'add') {
                        rightInputView.highlightAddition(diff);
                    } else if (diff.op === 'replace') {
                        rightInputView.highlightChange(diff);
                        leftInputView.highlightChange(diff);
                    }
                } catch(e) {
                    console.warn('error while trying to highlight diff', e);
                }
            });
        }

        var leftJson = <?php echo $json_left["value"] ?? '{}'; ?>;
        var rightJson = <?php echo $json_right["value"] ?? '{}'; ?>;

        compareJson(leftJson, rightJson);
    });
</script>