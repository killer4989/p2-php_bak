<?php
/**
 * rep2 - �T�u�W�F�N�g - iPhone�w�b�_�\��
 * for subject.php
 */

//===============================================================
// HTML�\���p�ϐ�
//===============================================================
$newtime = date('gis');
$norefresh_q = '&amp;norefresh=1';
$bbs_q = '&amp;bbs=' . $aThreadList->bbs;
$host_bbs_q = 'host=' . $aThreadList->host . $bbs_q;
$paging_q = $host_bbs_q . '&amp;spmode=' . $aThreadList->spmode . $norefresh_q;

// {{{ �y�[�W�^�C�g������URL�ݒ�

$p2_subject_url = "{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$_conf['k_at_a']}";

// �ʏ� ��
if (!$aThreadList->spmode) {
    // �����ꂠ��
    if ((isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) || !empty($GLOBALS['wakati_words'])) {
        $ptitle_url = $p2_subject_url;

    // ���̑�
    } else {
        $ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/";
        // ���ʂȃp�^�[�� index2.html
        // match�o�^���head�Ȃ��ĕ������ق����悳���������A�������X�|���X������̂�����
        if (!strcasecmp($aThreadList->host, 'livesoccer.net')) {
            $ptitle_url .= 'index2.html';
        }
    }

// ���ځ[�� or �q��
} elseif ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
    $ptitle_url = $p2_subject_url;

// �������ݗ���
} elseif ($aThreadList->spmode == 'res_hist') {
    $ptitle_url = "./read_res_hist.php{$_conf['k_at_q']}#footer";
}

// }}}
// {{{ �y�[�W�^�C�g������HTML�ݒ�

if ($aThreadList->spmode == 'fav' && $_conf['expack.misc.multi_favs']) {
    $ptitle_hd = FavSetManager::getFavSetPageTitleHt('m_favlist_set', $aThreadList->ptitle);
} else {
    $ptitle_hd = p2h($aThreadList->ptitle);
}

if ($aThreadList->spmode == 'taborn') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\">{$aThreadList->itaj_hd}</a> <span class=\"thin\">(���ځ[��)</span>";
} elseif ($aThreadList->spmode == 'soko') {
    $ptitle_ht = "<a href=\"{$ptitle_url}\">{$aThreadList->itaj_hd}</a> <span class=\"thin\">(dat�q��)</span>";
} elseif (!empty($ptitle_url)) {
    $ptitle_ht = "<a href=\"{$ptitle_url}\">{$ptitle_hd}</a>";
} else {
    $ptitle_ht = $ptitle_hd;
}

// }}}
// �t�H�[�� ==================================================
$sb_form_hidden_ht = <<<EOP
<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
<input type="hidden" name="host" value="{$aThreadList->host}">
<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}{$_conf['m_favita_set_input_ht']}
EOP;

// �t�B���^���� ==================================================

$hd['word'] = p2h($word);

// iPhone�p�w�b�_�v�f
$_conf['extra_headers_ht'] .= <<<EOS
<link rel="stylesheet" type="text/css" href="iui/toggle-only.css?{$_conf['p2_version_id']}">
<script type="text/javascript" src="js/json2.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/sb_iphone.js?{$_conf['p2_version_id']}"></script>
EOS;

// ���
if (!$aThreadList->spmode) {
    if (!function_exists('get_board_info')) {
        include P2_LIB_DIR . '/get_info.inc.php';
    }
    $board_info = get_board_info($aThreadList->host, $aThreadList->bbs);
} else {
    $board_info = null;
}

//=================================================
//�w�b�_�v�����g
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

// {{{ �e��{�^����

// �߂�
if ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
    $escaped_url = "{$_conf['subject_php']}?{$host_bbs_q}{$_conf['k_at_a']}";
    echo toolbar_i_back_button('�ɖ߂�', $escaped_url);
} else {
    echo toolbar_i_back_button('TOP', "index.php{$_conf['k_at_q']}");
}
echo '<div id="toolbar_header">';
// �V���܂Ƃߓǂ�
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

// �X������
echo toolbar_i_showhide_button('img/glyphish/icons2/06-magnifying-glass.png', null, 'sb_toolbar_filter');

// ���C�ɔ�
if ($board_info) {
    echo toolbar_i_favita_button('img/glyphish/icons2/28-star.png', null, $board_info);
}

// ���̑�
echo toolbar_i_showhide_button('img/gp0-more.png', null, 'sb_toolbar_extra');

echo '</div>';

// }}}
// {{{ ���̑��̃c�[��

echo '<div id="sb_toolbar_extra" class="extra">';

// {{{ ���̑� - ���C�ɓ���Z�b�g

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
// {{{ ���̑� - ���ǐ��������V���܂Ƃߓǂ�

echo <<<EOP
<form method="get" action="{$_conf['read_new_k_php']}">
{$sb_form_hidden_ht}
<div class="input-group" >
<input type="hidden" name="nt" value="1">{$shinchaku_norefresh_ht}
<input type="text" class="form-control" name="unum_limit" value="100" size="4"  placeholder="���ǐ�" autocorrect="off" autocapitalize="off" placeholder="#">
<span class="input-group-btn"><button type="submit" class="btn">�������܂Ƃߓǂ�</button></span></div>
</form>
EOP;

// }}}
// {{{ ���̑� - ���ёւ�

$sorts = array('midoku' => '�V��', 'res' => '���X', 'no' => 'No.', 'title' => '�^�C�g��');

if ($aThreadList->spmode && $aThreadList->spmode != 'taborn' && $aThreadList->spmode != 'soko') {
    $sorts['ita'] = '��';
}
if ($_conf['sb_show_spd']) {
    $sorts['spd'] = '���΂₳';
}
if ($_conf['sb_show_ikioi']) {
    $sorts['ikioi'] = '����';
}
$sorts['bd'] = 'Birthday';
if ($_conf['sb_show_fav'] and $aThreadList->spmode != 'taborn') {
    $sorts['fav'] = '��';
}

$htm['change_sort'] = "<form method=\"get\" action=\"{$_conf['subject_php']}\">";
$htm['change_sort'] .= $_conf['k_input_ht'];
$htm['change_sort'] .= '<input type="hidden" name="norefresh" value="1">';
// spmode��
if ($aThreadList->spmode) {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"spmode\" value=\"{$aThreadList->spmode}\">";
}
// spmode�łȂ��A�܂��́Aspmode�����ځ[�� or dat�q�ɂȂ�
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
                    . $sb_rsort_checked_at . '><label for="sb_rsort">�t��</label></span>';
$htm['change_sort'] .= ' <span class="input-group-btn"><button type="submit" class="btn" >���ёւ�</button></span></div></form>';

echo $htm['change_sort'];

// }}}

echo '</div>';

// }}}
// {{{ �X�������t�H�[��
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
<button type="submit" class="btn" name="submit_kensaku" value="����">����</button>
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

// ����
echo toolbar_i_standard_button('img/gp2-down.png', null, '#footer');

echo <<<EOP
</td>
</tr></tbody></table></div>
EOP;

// }}}
// {{{ �e��ʒm

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
