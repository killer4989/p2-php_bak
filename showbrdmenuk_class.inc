<?php
// p2 - �{�[�h���j���[��\������ �N���X(�g��)

class ShowBrdMenuK{
	var $cate_id; //�J�e�S���[ID
	
	function ShowBrdMenuK(){
		$this->cate_id = 1;
	}

	//���j���[�J�e�S�����v�����g���郁�\�b�h for �g��===============================
	function printCate($categories){
		global $k_sb_disp_range, $list_navi_ht;
		global $k_at_a, $k_at_q, $k_input_ht;

		if($categories){
			
			//�\��������====================
			if($_GET['from']){
				$list_disp_from=$_GET['from'];
			}else{
				$list_disp_from=1;
			}
			$list_disp_all_num = sizeof($categories);
			$disp_navi = getListNaviRange($list_disp_from, $k_sb_disp_range, $list_disp_all_num);
		
			if($disp_navi['from'] > 1){
				$mae_ht = <<<EOP
<a href="menu_k.php?view=cate&amp;from={$disp_navi['mae_from']}&amp;nr=1{$k_at_a}">�O</a>
EOP;
			}
			if($disp_navi['end'] < $list_disp_all_num){
				$tugi_ht = <<<EOP
<a href="menu_k.php?view=cate&amp;from={$disp_navi['tugi_from']}&amp;nr=1{$k_at_a}">��</a>
EOP;
			}
			
			if(!$disp_navi['all_once']){
				$list_navi_ht = <<<EOP
{$disp_navi['range_st']}{$mae_ht} {$tugi_ht}<br>
EOP;
			}
						
			foreach($categories as $cate){
				if($this->cate_id >= $disp_navi['from'] and $this->cate_id <= $disp_navi['end']){
					echo "<a href=\"menu_k.php?cateid={$this->cate_id}&amp;nr=1{$k_at_a}\">{$cate->name}</a>($cate->num)<br>\n";//$this->cate_id
				}
				$this->cate_id++;
			}
		}
	}


	//���j���[�J�e�S���̔��v�����g���郁�\�b�h for �g��===============================
	function printIta($categories){
		global $subject_php, $accesskey, $k_sb_disp_range, $list_navi_ht;
		global $k_at_a, $k_at_q, $k_input_ht;

		if($categories){

			foreach($categories as $cate){
				if($cate->num > 0){
					if($this->cate_id == $_GET['cateid']){
					
						echo "{$cate->name}<hr>\n";

	
						//�\��������====================
						if($_GET['from']){
							$list_disp_from=$_GET['from'];
						}else{
							$list_disp_from=1;
						}
						$list_disp_all_num = $cate->num;
						$disp_navi = getListNaviRange($list_disp_from, $k_sb_disp_range, $list_disp_all_num);
				
						if($disp_navi['from'] > 1){
							$mae_ht = <<<EOP
<a href="menu_k.php?cateid={$this->cate_id}&amp;from={$disp_navi['mae_from']}&amp;nr=1{$k_at_a}">�O</a>
EOP;
						}
						if($disp_navi['end'] < $list_disp_all_num){
							$tugi_ht = <<<EOP
<a href="menu_k.php?cateid={$this->cate_id}&amp;from={$disp_navi['tugi_from']}&amp;nr=1{$k_at_a}">��</a>
EOP;
						}
						
						if(!$disp_navi['all_once']){
							$list_navi_ht = <<<EOP
{$disp_navi['range_st']}{$mae_ht} {$tugi_ht}<br>
EOP;
						}


						$i=0;
						foreach($cate->menuitas as $mita){
							$i++;
							if($i<=9){
								$access_num_st="$i ";
								$akey_at=" {$accesskey}=\"{$i}\"";
							}else{
								$access_num_st="";
								$akey_at="";
							}
							if($i >= $disp_navi['from'] and $i <= $disp_navi['end']){
								echo "{$access_num_st}<a href=\"{$subject_php}?host={$mita->host}&amp;bbs={$mita->bbs}&amp;itaj_en={$mita->itaj_en}{$k_at_a}\"{$akey_at}>{$mita->itaj_ht}</a><br>\n";
							}
						}
					
					}
				}
				$this->cate_id++;
			}
		}
	}

	//�����������ăv�����g���郁�\�b�h for �g��===============================
	function printItaSearch($categories){
		global $subject_php, $_info_msg_ht, $word, $mikke;
		global $k_sb_disp_range, $list_navi_ht;
		global $k_at_a, $k_at_q, $k_input_ht;
	
		if($categories){
		
			//�\��������====================
			if($_GET['from']){
				$list_disp_from=$_GET['from'];
			}else{
				$list_disp_from=1;
			}
			$list_disp_all_num = $mikke; //
			$disp_navi = getListNaviRange($list_disp_from, $k_sb_disp_range, $list_disp_all_num);
		
			if($disp_navi['from'] > 1){
				$mae_ht = <<<EOP
<a href="menu_k.php?word={$word}&amp;from={$disp_navi['mae_from']}&amp;nr=1{$k_at_a}">�O</a>
EOP;
			}
			if($disp_navi['end'] < $list_disp_all_num){
				$tugi_ht = <<<EOP
<a href="menu_k.php?word={$word}&amp;from={$disp_navi['tugi_from']}&amp;nr=1{$k_at_a}">��</a>
EOP;
			}
			
			if(!$disp_navi['all_once']){
				$list_navi_ht = <<<EOP
{$disp_navi['range_st']}{$mae_ht} {$tugi_ht}<br>
EOP;
			}
	
			$i=0;
			foreach($categories as $cate){
				if($cate->num > 0){

					$t=false;
					foreach($cate->menuitas as $mita){
						$i++;
						if($i >= $disp_navi['from'] and $i <= $disp_navi['end']){
							if(!$t){echo "<b>{$cate->name}</b><br>\n";}
							$t=true;
							echo "�@<a href=\"{$subject_php}?host={$mita->host}&amp;bbs={$mita->bbs}&amp;itaj_en={$mita->itaj_en}{$k_at_a}\">{$mita->itaj_ht}</a><br>\n";
						}
					}

				}
				$this->cate_id++;
			}
		}
	}

	//=========================================================
	// ���C�ɔ��v�����g����֐� for �g��
	//=========================================================
	function print_favIta(){
		global $favita_path, $subject_php, $accesskey;
		global $k_at_a, $k_at_q, $k_input_ht;
		
		$lines= @file($favita_path); // favita�ǂݍ���
		if($lines){
			echo "���C�ɔ�<hr>";
			$i=0;
			foreach($lines as $l){
				$i++;
				$l = rtrim($l);
				if( preg_match("/^\t?(.+)\t(.+)\t(.+)$/", $l, $matches) ){
					$itaj = rtrim($matches[3]);
					$itaj_en = base64_encode($itaj);
					if($i<=9){
						$access_at=" {$accesskey}={$i}";
						$key_num_st="$i ";
					}else{
						$access_at="";
						$key_num_st="";					
					}
					echo <<<EOP
	{$key_num_st}<a href="{$subject_php}?host={$matches[1]}&amp;bbs={$matches[2]}&amp;itaj_en={$itaj_en}{$k_at_a}"{$access_at}>{$matches[3]}</a><br>
EOP;
				}
			}
		}
	}


}
?>