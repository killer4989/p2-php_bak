<?php
// p2 -  �X���b�h�\�� -  �w�b�_���� -  �g�їp for read.php

//�ϐ�=====================================
$diedat_msg="";

$info_st="��";
$delete_st="��";
$prev_st="�O";
$next_st="��";
$shinchaku_st="�V��";
$moto_thre_st="��";
$latest_st="�V";
$dores_st="ڽ";

$motothre_url = $aThread->getMotoThread($GLOBAL['ls']);
$ttitle_en = base64_encode($aThread->ttitle);
$ttitle_en_q = "&amp;ttitle_en=".$ttitle_en;
$bbs_q = "&amp;bbs=".$aThread->bbs;
$key_q = "&amp;key=".$aThread->key;
$offline_q = "&amp;offline=1";

//=================================================================
// �w�b�_
//=================================================================

//���C�Ƀ}�[�N�ݒ�==================================================

if($aThread->fav){$favmark="<span class=\"fav\">��</span>";}else{$favmark="<span class=\"fav\">+</span>";}
if($aThread->fav){$favdo=0;}else{$favdo=1;}

//���X�i�r�ݒ�=====================================================

$rnum_range = $k_rnum_range;
$latest_show_res_num = $k_rnum_range; //�ŐVXX

$read_navi_range="";
$read_navi_previous="";
$read_navi_previous_btm="";
$read_navi_next="";
$read_navi_next_btm="";
$read_footer_navi_new="";
$read_footer_navi_new_btm="";
$read_navi_latest="";
$read_navi_latest_btm="";

//----------------------------------------------
// $read_navi_range -- 1- 101- 201-

for($i=1; $i <= $aThread->rescount; $i=$i+$rnum_range){
	$offline_range_q="";
	$accesskey_at = "";
	if($i==1){
		$accesskey_at = " $accesskey=\"1\"";
	}
	$ito=$i+$rnum_range-1;
	if($ito <= $aThread->rnum){
		$offline_range_q=$offline_q;
	}
	$read_navi_range = $read_navi_range."<a{$accesskey_at} href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$i}-{$ito}{$offline_range_q}{$k_at_a}\">{$i}-</a>\t";
	break;//1-�̂ݕ\��
}


//----------------------------------------------
// $read_navi_previous -- �O100
$before_rnum=$aThread->resrange['start']-$rnum_range;
if($before_rnum<1){$before_rnum=1;}
if($aThread->resrange['start']==1){
	$read_navi_previous_isInvisible=true;
}
//if($before_rnum!=1){
//	$read_navi_previous_anchor="#r{$before_rnum}";
//}

if(!$read_navi_previous_isInvisible){
	$read_navi_previous = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$before_rnum}-{$aThread->resrange['start']}n{$offline_q}{$k_at_a}{$read_navi_previous_anchor}\">{$prev_st}</a>";
	$read_navi_previous_btm = "<a {$accesskey}=\"{$k_accesskey['prev']}\" href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$before_rnum}-{$aThread->resrange['start']}n{$offline_q}{$k_at_a}{$read_navi_previous_anchor}\">{$k_accesskey['prev']}.{$prev_st}</a>";
}

//----------------------------------------------
//$read_navi_next -- ��100
if($aThread->resrange['to'] >= $aThread->rescount){
	$aThread->resrange['to'] = $aThread->rescount;
	//$read_navi_next_anchor="#r{$aThread->rescount}";
	$read_navi_next_isInvisible=true;
}else{
	//$read_navi_next_anchor="#r{$aThread->resrange['to']}";
}
if($aThread->resrange['to'] == $aThread->rescount){
	$read_navi_next_anchor="#r{$aThread->rescount}";
}
$after_rnum=$aThread->resrange['to']+$rnum_range;

if(!$read_navi_next_isInvisible){
	$read_navi_next = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$after_rnum}n{$offline_q}&amp;nt={$newtime}{$k_at_a}{$read_navi_next_anchor}\">{$next_st}</a>";
	$read_navi_next_btm = "<a {$accesskey}=\"{$k_accesskey['next']}\" href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$after_rnum}n{$offline_q}&amp;nt={$newtime}{$k_at_a}{$read_navi_next_anchor}\">{$k_accesskey['next']}.{$next_st}</a>";
}

//----------------------------------------------
// $read_footer_navi_new  ������ǂ� �V�����X�̕\��

if($aThread->resrange['to'] == $aThread->rescount){
	$read_footer_navi_new="<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-n&amp;nt={$newtime}{$k_at_a}#r{$aThread->rescount}\">{$shinchaku_st}</a>";
	$read_footer_navi_new_btm="<a {$accesskey}=\"{$k_accesskey['next']}\" href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-n&amp;nt={$newtime}{$k_at_a}#r{$aThread->rescount}\">{$k_accesskey['next']}.{$shinchaku_st}</a>";
}
$read_navi_next_isInvisible; 
if(!$read_navi_next_isInvisible){
	$read_navi_latest=<<<EOP
<a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}{$k_at_a}">{$latest_st}{$latest_show_res_num}</a> 
EOP;
	$read_navi_latest_btm=<<<EOP
<a {$accesskey}="{$k_accesskey['latest']}" href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}{$k_at_a}">{$k_accesskey['latest']}.{$latest_st}{$latest_show_res_num}</a> 
EOP;
}

//====================================================================
// HTML�v�����g
//====================================================================

//�c�[���o�[����HTML=======
$toolbar_right_ht=<<<EOTOOLBAR
	<a href="{$subject_php}?host={$aThread->host}{$bbs_q}{$key_q}{$k_at_a}" {$accesskey}="{$k_accesskey['above']}">{$k_accesskey['above']}.{$aThread->itaj}</a>
	<a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$k_at_a}">{$info_st}</a> 
	<a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;dele=true{$k_at_a}">{$delete_st}</a> 
	<a href="{$motothre_url}">{$moto_thre_st}</a>
EOTOOLBAR;

//=====================================
header_content_type();
if($doctype){ echo $doctype;}
echo <<<EOHEADER
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<title>{$ptitle_ht}</title>\n
EOHEADER;

echo <<<EOP
</head>
<body>\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht="";

//�X�����T�[�o�ɂȂ����============================
if($aThread->diedat){ 

	if($dat_response_msg){
		$diedat_msg=$dat_response_msg;
	}else{
		$diedat_msg="<p><b>p2 info - �T�[�o����ŐV�̃X���b�h�����擾�ł��܂���ł����B</b></p>";
	}

	$motothre_ht = "<a href=\"{$motothre_url}\">{$motothre_url}</a>";

	echo $diedat_msg;
	echo "<p>";
	echo  $motothre_ht;
	echo "</p>";
	echo "<hr>";
	
	if(!$aThread->rescount){ //�������X���Ȃ���΃c�[���o�[�\��
		echo <<<EOP
<p>
	{$toolbar_right_ht}
</p>
EOP;
	}
}


/*
if($aThread->rescount and (!$_GET['renzokupop']) ){
//���X�t�B���^===============================
	if($res_filter['field']=="name"){$selected_name=" selected";}
	elseif($res_filter['field']=="mail"){$selected_mail=" selected";}
	elseif($res_filter['field']=="id"){$selected_id=" selected";}
	elseif($res_filter['field']=="msg"){$selected_msg=" selected";}
	if($res_filter['match']=="off"){$selected_off=" selected";}

	echo <<<EOP
<form id="header" method="GET" action="{$_conf['read_php']}>
	{$k_input_ht}
	<input type="hidden" name="bbs" value="{$aThread->bbs}">
	<input type="hidden" name="key" value="{$aThread->key}">
	<input type="hidden" name="host" value="{$aThread->host}">
	<input type="hidden" name="ls" value="all">
	<select id="field" name="field">
		<option value="name"{$selected_name}>���O
		<option value="mail"{$selected_mail}>���[��
		<option value="id"{$selected_id}>ID
		<option value="msg"{$selected_msg}>���b�Z�[�W
	</select>
	��
	<input id="word" name="word" value="{$word}" size="24">
	��
	<select id="match" name="match">
		<option value="on">�܂�
		<option value="off"{$selected_off}>�܂܂Ȃ�
	</select>
	���X��
	<input type="submit" name="submit" value="�t�B���^�\��">

</form>\n
EOP;
}
*/

if( ($aThread->rescount or $_GET['one'] && !$aThread->diedat) and (!$_GET['renzokupop'])){

	//if($_GET['one']){
		$pointer_header=" {$pointer_at}=\"header\"";
	//}
	echo <<<EOP
<p{$pointer_header}>
	{$read_navi_range}
	{$read_navi_previous}
	{$read_navi_next}
	{$read_navi_latest}
	<a {$accesskey}="8" href="#footer">8.��</a>
</p>\n
EOP;

}

echo "<hr>";
echo "<h3>{$aThread->ttitle}</h3>\n";
echo "<hr>";

 ?>