/*
 * rep2expack - DOMを操作してiPhoneに最適化する
 */

// {{{ GLOBALS

var _IPHONE_JS_OLD_ONLOAD = window.onload;

// }}}
// {{{ window.onload()

/*
 * iPhone用に要素を調整する
 *
 * @return void
 */
window.onload = (function(){
	if (_IPHONE_JS_OLD_ONLOAD) {
		_IPHONE_JS_OLD_ONLOAD();
	}

	// accesskey属性とキー番号表示を削除
	var anchors = document.evaluate('.//a[@accesskey]',
	                                document.body,
	                                null,
	                                XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
	                                null
	                                );
	var re = new RegExp('^[0-9#*]\\.');

	for (var i = 0; i < anchors.snapshotLength; i++) {
		var node = anchors.snapshotItem(i);
		var txt = node.firstChild;

		if (txt && txt.nodeType == 3 && re.test(txt.nodeValue)) {
			// TOPへのリンクをボタン化
			if (txt.nodeValue == '0.TOP') {
				node.className = 'button';
				if (node.parentNode.childNodes.length == 1) {
					node.parentNode.style.textAlign = 'center';
				} else if (node.parentNode == document.body) {
					var container = document.createElement('div');
					container.style.textAlign = 'center';
					document.body.insertBefore(container, node);
					document.body.removeChild(node);
					container.appendChild(node);
				}
			}

			// キー番号表示を削除
			txt.nodeValue = txt.nodeValue.replace(re, '');
		}

		// accceskey属性を削除
		node.removeAttribute('accesskey');
	}

	// 外部リンクを書き換える
	rewrite_external_link(document.body);

	// textareaの幅を調整
	adjust_textarea_size();

	// 回転時のイベントハンドラを設定
	document.body.onorientationchange = (function(){
		adjust_textarea_size();
	});

	// ロケーションバーを隠す
	if (!location.hash) {
		scrollTo(0, 0);
	}
});

// }}}
// {{{ rewrite_external_link()

/*
 * 外部リンクを確認してから新しいタブで開くように変更する
 *
 * @param Element contextNode
 * @return void
 */
function rewrite_external_link(contextNode)
{
	var anchors = document.evaluate('.//a[@href and starts-with(@href, "http")]',
	                                contextNode,
	                                null,
	                                XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
	                                null
	                                );
	var re = new RegExp('^https?://(.+?@)?([^:/]+)');

	for (var i = 0; i < anchors.snapshotLength; i++) {
		var node = anchors.snapshotItem(i);
		var url = node.getAttribute('href');
		var m = re.exec(url);

		if (m && m[2] != location.host) {
			if (!node.onclick) {
				node.onclick = confirm_open_external_link;
			}

			if (!node.hasAttribute('target')) {
				node.setAttribute('target', '_blank');
			}
		}
	}
}

// }}}
// {{{ confirm_open_external_link()

/*
 * 外部サイトを開くかどうかを確認する
 *
 * @return Boolean
 */
function confirm_open_external_link()
{
	return confirm('外部サイトを開きますか?\n' + this.href);
}

// }}}
// {{{ check_prev()

/*
 * 前のcheckbox要素をトグルする。疑似label効果
 *
 * @param Element elem
 * @return void
 */
function check_prev(elem)
{
	elem.previousSibling.checked = !elem.previousSibling.checked;
}

// }}}
// {{{ check_next()

/*
 * 次のcheckbox要素をトグルする。疑似label効果
 *
 * @return void
 */
function check_next(elem)
{
	elem.nextSibling.checked = !elem.nextSibling.checked;
}

// }}}
// {{{ adjust_textarea_size()

/*
 * textareaの幅を最大化する
 *
 * @return void
 */
function adjust_textarea_size()
{
	var areas = document.body.getElementsByTagName('textarea');

	for (var i = 0; i < areas.length; i++) {
		var width = areas[i].parentNode.clientWidth;
		if (width > 100) {
			width -= 12; // (borderWidth + padding) * 2
			if (width > 480) {
				width = 480; // maxWidth
			}
			areas[i].style.width = width.toString() + 'px';
		}
	}
}

// }}}
// {{{ change_link_target()

/*
 * リンクターゲットを切り替える
 *
 * @param String expr
 * @param Boolean toggle
 * @return void
 */
function change_link_target(expr, toggle)
{
	var anchors = document.evaluate(expr,
	                                document.body,
	                                null,
	                                XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
	                                null
	                                );

	if (toggle) {
		for (var i = 0; i < anchors.snapshotLength; i++) {
			anchors.snapshotItem(i).setAttribute('target', '_blank');
		}
	} else {
		for (var i = 0; i < anchors.snapshotLength; i++) {
			anchors.snapshotItem(i).removeAttribute('target');
		}
	}
}

// }}}
// {{{ override event object

/*
 * EventオブジェクトにX座標を得るメソッドとY座標を得るメソッドを追加する
 * iPhone/iPod TouchのSafari以外では必要に応じてこれらのメソッドを上書きする
 */
Event.prototype.getOffsetX = (function(){ return this.pageX; });
Event.prototype.getOffsetY = (function(){ return this.pageY; });

// }}}

/*
 * Local Variables:
 * mode: javascript
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: t
 * End:
 */
/* vim: set syn=css fenc=cp932 ai noet ts=4 sw=4 sts=4 fdm=marker: */
