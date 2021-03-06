<?php
/**
 * rep2 - サブジェクト - iPhoneヘッダ表示
 * for subject.php
 */

//===============================================================
// HTML表示用変数
//===============================================================
$newtime = date('gis');
$norefresh_q = '&amp;norefresh=1';
$bbs_q = '&amp;bbs=' . $aThreadList->bbs;
$host_bbs_q = 'host=' . $aThreadList->host . $bbs_q;
$paging_q = $host_bbs_q . '&amp;spmode=' . $aThreadList->spmode . $norefresh_q;

// {{{ ページタイトル部分URL設定

$p2_subject_url = "{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$_conf['k_at_a']}";

// 通常 板
if (!$aThreadList->spmode) {
    // 検索語あり
    if ((isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) || !empty($GLOBALS['wakati_words'])) {
        $ptitle_url = $p2_subject_url;

    // その他
    } else {
        $ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/";
        // 特別なパターン index2.html
        // match登録よりheadなげて聞いたほうがよさそうだが、ワンレスポンス増えるのが困る
        if (!strcasecmp($aThreadList->host, 'livesoccer.net')) {
            $ptitle_url .= 'index2.html';
        }
    }

// あぼーん or 倉庫
} elseif ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
    $ptitle_url = $p2_subject_url;

// 書き込み履歴
} elseif ($aThreadList->spmode == 'res_hist') {
    $ptitle_url = "./read_res_hist.php{$_conf['k_at_q']}#footer";
}

// }}}
// {{{ ページタイトル部分HTML設定

if ($aThreadList->spmode == 'fav' && $_conf['expack.misc.multi_favs']) {
    $ptitle_hd = FavSetManager::getFavSetPageTitleHt('m_favlist_set', $aThreadList->ptitle);
} else {
    $ptitle_hd = p2h($aThreadList->ptitle);
}

if ($aThreadList->spmode == 'taborn') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\">{$aThreadList->itaj_hd}</a> <span class=\"thin\">(あぼーん中)</span>";
} elseif ($aThreadList->spmode == 'soko') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\">{$aThreadList->itaj_hd}</a> <span class=\"thin\">(dat倉庫)</span>";
} elseif (!empty($ptitle_url)) {
    $ptitle_ht = "<a href=\"{$ptitle_url}\">{$ptitle_hd}</a>";
} else {
    $ptitle_ht = $ptitle_hd;
}

// }}}
// フォーム ==================================================
$sb_form_hidden_ht = <<<EOP
<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
<input type="hidden" name="host" value="{$aThreadList->host}">
<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}{$_conf['m_favita_set_input_ht']}
EOP;

// フィルタ検索 ==================================================

$hd['word'] = p2h($word);

// iPhone用ヘッダ要素
$_conf['extra_headers_ht'] .= <<<EOS
<link rel="stylesheet" type="text/css" href="iui/toggle-only.css?{$_conf['p2_version_id']}">
<script type="text/javascript" src="js/json2.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/sb_iphone.js?{$_conf['p2_version_id']}"></script>
EOS;

// 板情報
if (!$aThreadList->spmode) {
    if (!function_exists('get_board_info')) {
        include P2_LIB_DIR . '/get_info.inc.php';
    }
    $board_info = get_board_info($aThreadList->host, $aThreadList->bbs);
} else {
    $board_info = null;
}

//=================================================
//ヘッダプリント
//=================================================
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
{$_conf['extra_headers_ht']}
<title>{$ptitle_hd}</title>
</head>
<body class="nopad">
<div class="ntoolbar" id="header">
EOP;

// {{{ 各種ボタン類

// 戻る
if ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
    $escaped_url = "{$_conf['subject_php']}?{$host_bbs_q}{$_conf['k_at_a']}";
    echo toolbar_i_back_button('板に戻る', $escaped_url);
} else {
    echo toolbar_i_back_button('TOP', "index.php{$_conf['k_at_q']}");
}
echo '<div id="toolbar_header">';
// 新着まとめ読み
$shinchaku_norefresh_ht = '';

if ($aThreadList->spmode != 'soko') {
    $shinchaku_matome_url = "{$_conf['read_new_k_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}&amp;nt={$newtime}{$_conf['k_at_a']}";

    if ($aThreadList->spmode == 'merge_favita') {
        $shinchaku_matome_url .= $_conf['m_favita_set_at_a'];
    }

    if ($shinchaku_attayo) {
        $shinchaku_norefresh_ht = '<input type="hidden" name="norefresh" value="1">';
        echo toolbar_i_badged_button('img/glyphish/icons2/104-index-cards.png', null,
                                      $shinchaku_matome_url . $norefresh_q, $shinchaku_num);
    } else {
        echo toolbar_i_standard_button('img/glyphish/icons2/104-index-cards.png', null, $shinchaku_matome_url);
    }
}

// スレ検索
echo toolbar_i_showhide_button('img/glyphish/icons2/06-magnifying-glass.png', null, 'sb_toolbar_filter');

// お気に板
if ($board_info) {
    echo toolbar_i_favita_button('img/glyphish/icons2/28-star.png', null, $board_info);
}

// その他
echo toolbar_i_showhide_button('img/gp0-more.png', null, 'sb_toolbar_extra');

echo '</div>';

// }}}
// {{{ その他のツール

echo '<div id="sb_toolbar_extra" class="extra">';

// {{{ その他 - お気に入りセット

if ($board_info && $_conf['expack.misc.multi_favs']) {
    echo '<table><tbody><tr>';
    for ($i = 1; $i <= $_conf['expack.misc.favset_num']; $i++) {
        echo '<td>';
        echo toolbar_i_favita_button('img/glyphish/icons2/28-star.png', '-', $board_info, $i);
        echo '</td>';
        if ($i % 5 === 0 && $i != $_conf['expack.misc.favset_num']) {
            echo '</tr><tr>';
        }
    }
    $mod_cells = $_conf['expack.misc.favset_num'] % 5;
    if ($mod_cells) {
        $mod_cells = 5 - $mod_cells;
        for ($i = 0; $i < $mod_cells; $i++) {
            echo '<td>&nbsp;</td>';
        }
    }
    echo '</tr></tbody></table>';
}

// }}}
// {{{ その他 - 未読数制限つき新着まとめ読み

echo <<<EOP
<form method="get" action="{$_conf['read_new_k_php']}">
{$sb_form_hidden_ht}
<div class="input-group" >
<input type="hidden" name="nt" value="1">{$shinchaku_norefresh_ht}
<input type="text" class="form-control" name="unum_limit" value="100" size="4"  placeholder="未読数" autocorrect="off" autocapitalize="off" placeholder="#">
<span class="input-group-btn"><button type="submit" class="btn">未満をまとめ読み</button></span></div>
</form>
EOP;

// }}}
// {{{ その他 - 並び替え

$sorts = array('midoku' => '新着', 'res' => 'レス', 'no' => 'No.', 'title' => 'タイトル');

if ($aThreadList->spmode && $aThreadList->spmode != 'taborn' && $aThreadList->spmode != 'soko') {
    $sorts['ita'] = '板';
}
if ($_conf['sb_show_spd']) {
    $sorts['spd'] = 'すばやさ';
}
if ($_conf['sb_show_ikioi']) {
    $sorts['ikioi'] = '勢い';
}
$sorts['bd'] = 'Birthday';
if ($_conf['sb_show_fav'] and $aThreadList->spmode != 'taborn') {
    $sorts['fav'] = '☆';
}

$htm['change_sort'] = "<form method=\"get\" action=\"{$_conf['subject_php']}\">";
$htm['change_sort'] .= $_conf['k_input_ht'];
$htm['change_sort'] .= '<input type="hidden" name="norefresh" value="1">';
// spmode時
if ($aThreadList->spmode) {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"spmode\" value=\"{$aThreadList->spmode}\">";
}
// spmodeでない、または、spmodeがあぼーん or dat倉庫なら
if (!$aThreadList->spmode || $aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"host\" value=\"{$aThreadList->host}\">";
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"bbs\" value=\"{$aThreadList->bbs}\">";
}

$htm['change_sort'] .= '<div class="input-group"><select name="sort" class="form-control">';
foreach ($sorts as $k => $v) {
    if ($GLOBALS['now_sort'] == $k) {
        $sb_sort_selected_at = ' selected';
    } else {
        $sb_sort_selected_at = '';
    }
    $htm['change_sort'] .= "<option value=\"{$k}\"{$sb_sort_selected_at}>{$v}</option>";
}
$htm['change_sort'] .= '</select>';

if (!empty($_REQUEST['sb_view'])) {
    $htm['change_sort'] .= '<input type="hidden" name="sb_view" value="'
                        . p2h($_REQUEST['sb_view']) . '">';
}

if (!empty($_REQUEST['rsort'])) {
    $sb_rsort_checked_at = ' checked';
} else {
    $sb_rsort_checked_at = '';
}
$htm['change_sort'] .= ' <span class="input-group-addon"><input type="checkbox" id="sb_rsort" name="rsort" value="1"'
                    . $sb_rsort_checked_at . '><label for="sb_rsort">逆順</label></span>';
$htm['change_sort'] .= ' <span class="input-group-btn"><button type="submit" class="btn" >並び替え</button></span></div></form>';

echo $htm['change_sort'];

// }}}

echo '</div>';

// }}}
// {{{ スレ検索フォーム
if (array_key_exists('method', $sb_filter) && $sb_filter['method'] == 'or') {
    $hd['method_checked_at'] = ' checked';
} else {
    $hd['method_checked_at'] = '';
}

echo <<<EOP
<div id="sb_toolbar_filter" class="extra">
<form id="sb_filter" method="get" action="{$_conf['subject_php']}" accept-charset="{$_conf['accept_charset']}">
<div class="input-group">
{$sb_form_hidden_ht}<input type="text" id="sb_filter_word" name="word" class="form-control" value="{$hd['word']}" size="15" autocorrect="off" autocapitalize="off">
<span class="input-group-addon">
<label for="sb_filter_method"><input type="checkbox" id="sb_filter_method" name="method" value="or"{$hd['method_checked_at']}>OR</label>
</span>
<span class="input-group-btn">
<button type="submit" class="btn" name="submit_kensaku" value="検索">検索</button>
</span>
</div>
</form>
</div>
<div class="ntoolbar" id="pager">
<table><tbody><tr>
<td colspan="4" id="thread_title"><div>
{$ptitle_hd}
</div></td>
<td>
EOP;

// 下へ
echo toolbar_i_standard_button('img/gp2-down.png', null, '#footer');

echo <<<EOP
</td>
</tr></tbody></table></div>
EOP;

// }}}
// {{{ 各種通知

$info_ht = P2Util::getInfoHtml();
if (strlen($info_ht)) {
    echo "<div class=\"info\">{$info_ht}</div>";
}

if ($GLOBALS['sb_mikke_num']) {
    echo "<div class=\"hits\">&quot;{$hd['word']}&quot; {$GLOBALS['sb_mikke_num']}hit!</div>";
}

// }}}

echo '</div>';

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
