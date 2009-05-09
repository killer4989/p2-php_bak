/*
 * ImageCache2::LightBox_Plus
 */

/**
 * ランク表示用コンテナを作成する
 */
LightBox.prototype._ic2_create_elements = function()
{
	var self = this;

	var rankbox = document.createElement('span');
	rankbox.id = 'lightboxIC2Rank';
	rankbox.style.display = 'none';
	rankbox.style.position = 'absolute';
	rankbox.style.zIndex = '70';

	var ngimg = document.createElement('img');
	ngimg.setAttribute('src', 'img/sn0.png');
	ngimg.setAttribute('width', '16');
	ngimg.setAttribute('height', '16');
	ngimg.setAttribute('alt', '-1');
	ngimg.onclick = self._ic2_generate_setrank(-1);
	rankbox.appendChild(ngimg);

	var zeroimg = document.createElement('img');
	zeroimg.setAttribute('src', 'img/sz1.png');
	zeroimg.setAttribute('width', '10');
	zeroimg.setAttribute('height', '16');
	zeroimg.setAttribute('alt', '0');
	zeroimg.onclick = self._ic2_generate_setrank(0);
	rankbox.appendChild(zeroimg);

	for (var i = 1; i <= 5; i++) {
		var rankimg = document.createElement('img');
		rankimg.setAttribute('src', 'img/s0.png');
		rankimg.setAttribute('width', '16');
		rankimg.setAttribute('height', '16');
		rankimg.setAttribute('alt', String(i));
		rankimg.onclick = self._ic2_generate_setrank(i);
		rankbox.appendChild(rankimg);
	}

	return rankbox;
};

/**
 * ランク表示をトグルする
 */
LightBox.prototype._ic2_show_rank = function(enable)
{
	var self = this;
	var rankbox = document.getElementById('lightboxIC2Rank');
	if (!rankbox) {
		return;
	}

	if (!enable || rankbox.childNodes.length == 0) {
		rankbox.style.display = 'none';
	} else {
		// now display rankbox
		rankbox.style.top = [10 + self._img.height - (16 + 2 * 2 + 1), 'px'].join('');
		rankbox.style.left = '10px';
		rankbox.style.width = [16 + 10 + 16 * 5, 'px'].join('');
		rankbox.style.height = '16px';
		rankbox.style.display = 'block';

		var rank;
		if (self._open == -1 || !self._imgs[self._open].id) {
			rank = 0;
		} else {
			rank = self._ic2_get_rank(self._imgs[self._open].id);
		}
		self._ic2_draw_rank(rank);
	}
};

/**
 * ランク描画
 */
LightBox.prototype._ic2_draw_rank = function(rank)
{
	var rankbox = document.getElementById('lightboxIC2Rank');
	var pos = rank + 1;
	if (!rankbox) {
		return;
	}

	var rankimgs = rankbox.getElementsByTagName('img');
	rankimgs[0].setAttribute('src', 'img/sn' + ((rank == -1) ? '1' : '0') + '.png');
	for (var i = 2; i < rankimgs.length; i++) {
		rankimgs[i].setAttribute('src', 'img/s' + ((i > pos) ? '0' : '1') + '.png');
	}
};

/**
 * ランク取得
 */
LightBox.prototype._ic2_get_rank = function(id)
{
	var info  = getImageInfo('id', id);
	if (!info) {
		alert('画像情報を取得できませんでした');
		return 0;
	}

	var info_array = info.split(',');
	if (info_array.length < 6) {
		alert('画像情報を取得できませんでした');
		return 0;
	}

	return parseInt(info_array[4]);
};

/**
 * ランク変更
 */
LightBox.prototype._ic2_set_rank = function(rank)
{
	var self = this;
	if (self._open == -1 || !self._imgs[self._open].id) {
		return;
	}

	var objHTTP = getXmlHttp();
	if (!objHTTP) {
		alert("Error: XMLHTTP 通信オブジェクトの作成に失敗しました。") ;
	}
	var url = 'ic2_setrank.php?id=' + self._imgs[self._open].id + '&rank=' + rank;
	var res = getResponseTextHttp(objHTTP, url, 'nc');
	if (res == '1') {
		self._ic2_draw_rank(rank);
		return true;
	}
	alert("Error: 画像のランクを変更できませんでした。") ;
	return false;
};

/**
 * ランク変更をコールする関数を生成する
 */
LightBox.prototype._ic2_generate_setrank = function(rank)
{
	var self = this;
	return (function(){
		self._ic2_set_rank(rank);
		return false;
	});
};

/**
 * カーソルキー, ESDX (emacs風), HJKL (vi風) で上下左右の画像に切り替える
 */
LightBox.prototype._ic2_keydown_chimg = function(evt, num, len)
{
	var self = this;
	var change_img  = true;
	var is_forward  = true;
	var is_vertical = false;
	var no_loop     = false;
	var no_updown   = false;

	if (typeof ic2_cols !== 'number' || ic2_cols < 1 || len == 0) {
		return true;
	}
	if (evt.altKey || evt.ctrlKey || evt.metaKey || (evt.shiftKey && evt.keyCode != 191)) {
		return true;
	}
	if (typeof ic2_lightbox_options === 'object') {
		if (ic2_lightbox_options.no_loop) {
			no_loop = true;
		}
		if (ic2_lightbox_options.no_updown) {
			no_updown = true;
		}
	}

	switch (evt.keyCode) {
		// 左
		case 37: // LEFT
		case 72: // 'H'
		case 83: // 'S'
			is_forward  = false;
			is_vertical = false;
			break;

		// 上
		case 38: // UP
		case 75: // 'K'
		case 69: // 'E'
			is_forward  = false;
			is_vertical = true;
			break;

		// 右
		case 39: // RIGHT
		case 76: // 'L'
		case 68: // 'D'
			is_forward  = true;
			is_vertical = false;
			break;

		// 下
		case 40: // DOWN
		case 74: // 'J'
		case 88: // 'X'
			is_forward  = true;
			is_vertical = true;
			break;

		// キーバインドを表示
		case 191: // '/' ('?')
			change_img = false;
			if (evt.shiftKey) {
				alert("[画像切替キー]\n"
					+ " 上: ↑, E, K \n"
					+ " 下: ↓, X, J \n"
					+ " 左: ←, S, H \n"
					+ " 右: →, D, L ");
			}
			break;

		// Lightboxを閉じる
		case 27: // ESC
			change_img = false;
			self._close(null);
			break;

		// 何もしない
		default:
			change_img = false;
	}

	// 別の画像を表示
	if (change_img && !(is_vertical && no_updown)) {
		var cols = ic2_cols;
		var rows = Math.ceil(len / ic2_cols);
		var end = len - 1;
		var direction;

		if (is_vertical && cols > 1 && rows > 1) {
			var x, y, z, pos;

			// 左上角を0とした横(Z字)方向の通し番号(num)を
			// 左上角を0とした縦(N字)方向の通し番号(pos)に変換
			x = num % cols;
			y = Math.floor(num / cols);
			z = len % cols;
			pos = x * rows + y;
			if (z && x > z) {
				pos -= x - z;
			}

			// 次の画像番号を求める
			if (is_forward) {
				pos = (pos == end) ? 0 : pos + 1;
			} else {
				pos = (pos == 0) ? end : pos - 1;
			}

			// 横方向の通し番号に再変換し、現在の画像番号との差を求める
			z *= rows;
			if (z && pos >= z + rows - 1) {
				pos += Math.floor((pos - z) / (rows - 1));
			}
			x = Math.floor(pos / rows);
			y = pos % rows;
			direction = x + y * cols - num;
		} else {
			if (is_forward) {
				direction = (num == end) ? -end : 1;
			} else {
				direction = (num == 0) ? end : -1;
			}
		}

		if (no_loop) {
			direction = ((is_forward) ? Math.max : Math.min)(0, direction);
		}
		if (direction) {
			self._show_next(direction);
		}
	}

	return Event.stop(evt);
};

/*
 * Local Variables:
 * mode: javascript
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: t
 * End:
 */
/* vim: set syn=javascript fenc=cp932 ai noet ts=4 sw=4 sts=4 fdm=marker: */
