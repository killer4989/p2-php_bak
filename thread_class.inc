<?php
// p2 - �X���b�h �N���X

require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X

//=============================================================================
// �X���b�h�N���X
//=============================================================================

class Thread{
	var $ttitle; //�X���^�C�g�� //datline[0]
	var $key; //�X���b�hID //datline[1]
	var $length; //local Dat Bytes(int) //datline[2]
	var $rnum; //���ǃ��X�� //datline[3]
	var $rescount; //�����X��
	var $modified; //dat��Last-Modified //datline[4]
	//last res
	var $fav; //���C�ɓ���(bool) //datline[6]
	//name
	//mail
	var $newline; //�V�K���X�� //datline[9]
	
	var $host; // ex)pc.2ch.net //datline[10]
	var $bbs; // ex)mac //datline[11]
	var $itaj; //�� ex)�V�Emac
	
	var $torder; //�X���b�h�V�������ԍ�
	var $unum; //���ǁi�V�����X�j��
	
	var $keyidx; //idx�t�@�C���p�X
	var $keydat; //���[�J��dat�t�@�C���p�X
	
	var $isonline; //�T�[�o�ɂ����true
	var $new; //�V�K�X���Ȃ�true
	var $ttitle_ht; //HTML�\���p�^�C�g��
	var $dayres; //���������̃��X���B�����B
	
	var $dat_type; // dat�̌`���i2ch�̋��`��dat�i,��؂�j�Ȃ�"2ch_old"�j

	function Thread(){
	}

	//============================================================================
	// getThreadInfoFromExtIdxLine -- fav, recent�p�̊g��idx���X�g���烉�C���f�[�^���擾���郁�\�b�h
	//============================================================================	

	function getThreadInfoFromExtIdxLine($l){
		$l = rtrim($l);
		$la = explode("<>", $l);
		$this->host = $la[10];
		$this->bbs = $la[11];
		$this->key = $la[1];
		
		if(! $this->ttitle){
			if($la[0]){
				$this->ttitle=rtrim($la[0]);
			}
		}
		
		//if($la[6]){$this->fav = $la[6];}
	}

	//============================================================================
	// setThreadPathInfo -- Set Path info���\�b�h
	//============================================================================	

	function setThreadPathInfo($host, $bbs, $key){	
		$this->host = $host;
		$this->bbs = $bbs;
		$this->key = $key;
		
		$datdir_host = datdirOfHost($this->host);
		$this->keyidx = "{$datdir_host}/{$this->bbs}/{$this->key}.idx";
		$this->keydat = "{$datdir_host}/{$this->bbs}/{$this->key}.dat";
	}

	/**
	 * ���X���b�h�������ς݂Ȃ�true��Ԃ�
	 */
	function isKitoku() {
		if ($this->rnum > 0) {
			return true;
		} else {
			return false;
		}
	}

	//============================================================================
	// getThreadInfoFromIdx -- �����X���b�h�f�[�^��key.idx����擾���郁�\�b�h
	//============================================================================	

	function getThreadInfoFromIdx($keyidx){
		if(! $lines=@file($keyidx) ){
			return false;
		}
		
		$key_line = rtrim($lines[0]);
		$lar = explode("<>", $key_line);
		if(! $this->ttitle){
			if($lar[0]){
				$this->ttitle = rtrim($lar[0]);
			}
		}
		
		if($lar[9]){
			$this->newline = $lar[9];
		}else{
			$this->newline = 1;
		}
		
		if ($lar[3]) {
			$this->rnum = $lar[3];
			if($this->rescount){
				$this->unum = $this->rescount - ($this->newline -1);
				if($this->unum<0){ $this->unum=0; } //machi bbs ��subject�̍X�V�Ƀf�B���C������悤�Ȃ̂Œ������Ă���
			}
		}
		//if($lar[2]){ $this->length=$lar[2]; }
		if($lar[4]){ $this->modified = $lar[4]; }
		
		return $key_line; 
	}
	
	//============================================================================
	// getDatBytesFromLocalDat -- ���[�J��DAT�̃t�@�C���T�C�Y���擾���郁�\�b�h
	//============================================================================	

	function getDatBytesFromLocalDat(){
		clearstatcache();
		if( $this->length=@filesize($this->keydat) ){
			return $this->length;
		}else{
			return false;
		}
	}
	
	//============================================================================
	// subject.txt �̈�s����X�������擾���郁�\�b�h
	//============================================================================	
	function getThreadInfoFromSubjectTxtLine($l)
	{
		preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $l, $matches);
		$this->isonline = true;
		$this->key = $matches[1];
		$this->ttitle = rtrim($matches[4]);
		
		// be.2ch.net �Ȃ�EUC��SJIS�ϊ�
		if (P2Util::isHostBe2chNet($this->host)) {
			include_once './strctl_class.inc';
			$this->ttitle = StrCtl::p2EUCtoSJIS($this->ttitle);
		}
		
		$this->rescount = $matches[6];
		if($this->newline){
			$this->unum = $this->rescount - ($this->newline -1);
			if($this->unum<0){$this->unum=0;} //machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
		}
	}

	//=========================================
	// �X���^�C�g���擾���\�b�h
	//=========================================
	function setTitleFromLocal()
	{
		if (!$this->ttitle) {
		
			if ($this->datlines) {
				$firstdatline = rtrim($this->datlines[0]);
				$d = $this->explodeDatLine($firstdatline);
				$this->ttitle = $d[4];
			
			// ���[�J��dat��1�s�ڂ���擾
			} elseif (is_readable($this->keydat)){
				$fd = fopen($this->keydat, "r");
				$l = fgets ($fd,32800);
				fclose ($fd);
				$firstdatline = rtrim($l);
				if (strstr($firstdatline, "<>")) {
					$datline_sepa = "<>";
				} else {
					$datline_sepa = ",";
					$this->dat_type = "2ch_old";
				}
				$d = explode($datline_sepa, $firstdatline);
				$this->ttitle = $d[4];
				
				// be.2ch.net �Ȃ�EUC��SJIS�ϊ�
				if (P2Util::isHostBe2chNet($this->host)) {
					include_once './strctl_class.inc';
					$this->ttitle = StrCtl::p2EUCtoSJIS($this->ttitle);
				}
			}
			
		}
		
		return $this->ttitle;
	}

	/**
	 * �����X��URL��Ԃ����\�b�h
	 */
	function getMotoThread($ls = "")
	{
		global $ktai;
		
		if (P2Util::isHostMachiBbs($this->host)) {
			$motothre_url = "http://{$this->host}/bbs/read.pl?BBS={$this->bbs}&KEY={$this->key}";
		} elseif (P2Util::isHostMachiBbsNet($this->host)) {
			$motothre_url = "http://{$this->host}/test/read.cgi?bbs={$this->bbs}&key={$this->key}";	
		} elseif (P2Util::isHostJbbsShitaraba($this->host)) {
			$motothre_url = "http://{$this->host}/bbs/read.cgi?BBS={$this->bbs}&KEY={$this->key}";
		} elseif (P2Util::isHost2chs($this->host)) {
			if ($ktai) {
				if (P2Util::isHostBbsPink($this->host)) {
					$motothre_url = "http://{$this->host}/test/r.i/{$this->bbs}/{$this->key}/{$ls}";
				} else {
					$motothre_url = "http://c.2ch.net/test/-/{$this->bbs}/{$this->key}/{$ls}";
				}
			} else {
				$motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$ls}";
			}
		} else {
			$motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$ls}";
		}
		
		return $motothre_url;
	}
	
	/**
	 * �������i���X/���j���Z�b�g����
	 */
	function setDayRes($nowtime = false)
	{
		if (!isset($this->key) || !isset($this->rescount)) {
			return false;
		}
		
		if (!$nowtime) {
			$nowtime = time();
		}
		$pastsc = $nowtime - $this->key;
		$this->dayres = $this->rescount / $pastsc * 60 * 60 * 24;
		return true;
	}

	/**
	 * �����X�Ԋu�i����/���X�j���擾����
	 */
	function getTimePerRes()
	{
		$noresult_st = "-";
	
		if (!isset($this->dayres)) {
			if (!$this->setDayRes(time())) {
				return $noresult_st;
			}
		}
		
		if ($this->dayres <= 0) {
			return $noresult_st;
			
		} elseif ($this->dayres < 1/365) {
			$spd = 1/365 / $this->dayres;
			$spd_suffix = "�N";
		} elseif ($this->dayres < 1/30.5) {
			$spd = 1/30.5 / $this->dayres;
			$spd_suffix = "����";
		} elseif ($this->dayres < 1) {
			$spd = 1 / $this->dayres;
			$spd_suffix = "��";
		} elseif ($this->dayres < 24) {
			$spd = 24 / $this->dayres;
			$spd_suffix = "����";
		} elseif ($this->dayres < 24*60) {
			$spd = 24*60 / $this->dayres;
			$spd_suffix = "��";
		} elseif ($this->dayres < 24*60*60) {
			$spd = 24*60*60 / $this->dayres;
			$spd_suffix = "�b";
		} else {
			$spd = 1;
			$spd_suffix = "�b�ȉ�";
		}
		if ($spd > 0) {
			$spd_st = sprintf("%01.1f", @round($spd, 2)) . $spd_suffix;
		} else {
			$spd_st = "-";
		}
		return $spd_st;
	}

}
?>