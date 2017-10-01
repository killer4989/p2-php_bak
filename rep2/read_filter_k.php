<?php
/**
 * rep2 - �g�єŃ��X�t�B���^�����O
 */

require_once __DIR__ . '/../init.php';

$_login->authorize(); // ���[�U�F��

/**
 * �X���b�h���
 */
$host = $_GET['host'];
$bbs  = $_GET['bbs'];
$key  = $_GET['key'];
$ttitle = UrlSafeBase64::decode($_GET['ttitle_en']);
$ttitle_back = (isset($_SERVER['HTTP_REFERER']))
    ? '<a href="' . p2h($_SERVER['HTTP_REFERER']) . '" title="�߂�">' . $ttitle . '</a>'
    : $ttitle;

$hidden_fields_ht = ResFilterElement::getHiddenFields($host, $bbs, $key);
if ($_conf['iphone']) {
    $word_field_ht = ResFilterElement::getWordField(array(
        'autocorrect' => 'off',
        'autocapitalize' => 'off',
        'class' => 'form-control',
    ));
} else {
    $word_field_ht = ResFilterElement::getWordField();
}
$field_field_ht = ResFilterElement::getFieldField();
$method_field_ht = ResFilterElement::getMethodField();
$match_field_ht = ResFilterElement::getMatchField();
$include_field_ht = ResFilterElement::getIncludeField();

/**
 * �����t�H�[����\��
 */
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOF
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
{$_conf['extra_headers_ht']}
<title>rep2 - �X��������</title>
</head>
<body{$_conf['k_colors']}>
<p>{$ttitle_back}</p>
<hr>
<form id="header" method="get" action="{$_conf['read_php']}" accept-charset="{$_conf['accept_charset']}">
{$hidden_fields_ht}
<div>
{$word_field_ht}
<input type="submit" id="submit1" name="submit_filter" value="����">
</div>
<hr>
<div style="white-space:nowrap">
�����I�v�V����:<br>
{$field_field_ht}��<br>
{$method_field_ht}��<br>
{$match_field_ht}<br>
<input type="submit" id="submit2" name="submit_filter" value="����"><br>
{$include_field_ht}
</div>
{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
</form>
</body>
</html>
EOF;

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
