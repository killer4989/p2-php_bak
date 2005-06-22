<?php
/**
 * �X���b�h��\������ �N���X
 */
class ShowThread{

    var $thread; // �X���b�h�I�u�W�F�N�g
    
    var $str_to_link_regex; // �����N���ׂ�������̐��K�\��
    
    var $url_handlers; // URL����������֐��E���\�b�h���Ȃǂ��i�[����z��
    
    /**
     * �R���X�g���N�^
     */
    function ShowThread(&$aThread)
    {
        // �X���b�h�I�u�W�F�N�g��o�^
        $this->thread = &$aThread;
        
        $this->str_to_link_regex = '{'
            . '(?P<link>(<[Aa] .+?>)(.*?)(</[Aa]>))' // �����N�iPCRE�̓�����A�K�����̃p�^�[�����ŏ��Ɏ��s����j
            . '|'
            . '(?:'
            .   '(?P<quote>' // ���p
            .       '((?:&gt;|��){1,2} ?)' // ���p��
            .       '('
            .           '(?:[1-9]\\d{0,3})' // 1�ڂ̔ԍ�
            .           '(?:'
            .               '(?: ?(?:[,=]|�A) ?[1-9]\\d{0,3})+' // �A��
            .               '|'
            .               '-(?:[1-9]\\d{0,3})?' // �͈�
            .           ')?'
            .       ')'
            .       '(?=\\D|$)'
            .   ')' // ���p�����܂�
            . '|'
            .   '(?P<url>'
            .       '(ftp|h?t?tps?)://([0-9A-Za-z][\\w/\\#~:;.,?+=&%@!\\-]+?)' // URL
            .       '(?=[^\\w/\\#~:;.,?+=&%@!\\-]|$)' // �����ȕ������s���̐�ǂ�
            .   ')'
            . '|'
            .   '(?P<id>ID: ?([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$))' // ID�i8,10�� +PC/�g�ю��ʃt���O�j
            . ')'
            . '}';
        
        $this->url_handlers = array();
    }
    
    /**
     * Dat��HTML�ϊ����ĕ\������
     */
    function datToHtml()
    {
        return '';
    }
    
    /**
     * Dat��HTML�ϊ��������̂��擾����
     */
    function getDatToHtml()
    {
        ob_start();
        $this->datToHtml();
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }

    /**
     * BE�v���t�@�C�������N�ϊ�
     */
    function replaceBeId($date_id, $i)
    {
        global $_conf;
        
        $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>Lv.\$2</a>";
        
        //<BE:23457986:1>
        $be_match = '|<BE:(\d+):(\d+)>|i';
        if (preg_match($be_match, $date_id)) {
            $date_id = preg_replace($be_match, $beid_replace, $date_id);
        
        } else {
        
            $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>?\$2</a>";
            $date_id = preg_replace('|BE: ?(\d+)-(#*)|i', $beid_replace, $date_id);
        }
        
        return $date_id;
    }


    /**
     * NG���ځ[��`�F�b�N
     */
    function ngAbornCheck($code, $resfield, $ic = FALSE)
    {
        global $ngaborns;
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('ngAbornCheck()');
        
        $method = $ic ? 'stristr' : 'strstr';
        
        if (isset($ngaborns[$code]['data']) && is_array($ngaborns[$code]['data'])) {
            foreach ($ngaborns[$code]['data'] as $k => $v) {
                if (strlen($v['word']) == 0) {
                    continue;
                }
                
                /*
                if ($method($resfield, $v['word'])) {
                    $this->ngAbornUpdate($code, $k);
                    $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                    return $v['word'];
                } else {
                    continue;
                }
                */
                
                // <�֐�:�I�v�V����>�p�^�[�� �`���̍s�͐��K�\���Ƃ��Ĉ���
                // �o�C�i���Z�[�t�łȂ��i���{��ŃG���[���o�邱�Ƃ�����j�̂�ereg()�n�͎g��Ȃ�
                if (preg_match('/^<(mb_ereg|preg_match|regex)(:[imsxeADSUXu]+)?>(.+)$/', $v['word'], $re)) {
                    // "regex"�̂Ƃ��͎����ݒ�
                    if ($re[1] == 'regex') {
                        if (P2_MBREGEX_AVAILABLE) {
                            $re_method = 'mb_ereg';
                            $re_pattern = $re[3];
                        } else {
                            $re_method = 'preg_match';
                            $re_pattern = '/' . str_replace('/', '\\/', $re[3]) . '/';
                        }
                    } else {
                        $re_method = $re[1];
                        $re_pattern = $re[3];
                    }
                    // �啶���������𖳎�
                    if ($re[2] && strstr($re[2], 'i')) {
                        if ($re_method == 'preg_match') {
                            $re_pattern .= 'i';
                        } else {
                            $re_method .= 'i';
                        }
                    }
                    // �}�b�`
                    if ($re_method($re_pattern, $resfield)) {
                        $this->ngAbornUpdate($code, $k);
                        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['word'];
                    //if ($re_method($re_pattern, $resfield, $matches)) {
                        //return htmlspecialchars($matches[0]);
                    }

                // �P���ɕ����񂪊܂܂�邩�ǂ������`�F�b�N
                } elseif ($method($resfield, $v['word'])) {
                    $this->ngAbornUpdate($code, $k);
                    $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                    return $v['word'];
                }
            }
        }
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
        return false;
    }

    /**
     * ���背�X�̓������ځ[��`�F�b�N
     */
    function abornResCheck($host, $bbs, $key, $resnum)
    {
        global $ngaborns;
        
        $target = $host . '/' . $bbs . '/' . $key . '/' . $resnum;
        
        if (isset($ngaborns[$code]['data']) && is_array($ngaborns['aborn_res']['data'])) {
            foreach ($ngaborns['aborn_res']['data'] as $k => $v) {
                if ($ngaborns['aborn_res']['data'][$k]['word'] == $target) {
                    $this->ngAbornUpdate('aborn_res', $k);
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * NG/���ځ`������Ɖ񐔂��X�V
     */
    function ngAbornUpdate($code, $k)
    {
        global $ngaborns;

        if (isset($ngaborns[$code]['data'][$k])) {
            $v =& $ngaborns[$code]['data'][$k];
            $v['lasttime'] = date('Y/m/d G:i'); // HIT���Ԃ��X�V
            if (empty($v['hits'])) {
                $v['hits'] = 1; // ��HIT
            } else {
                $v['hits']++; // HIT�񐔂��X�V
            }
        }
    }

    /**
     * url_handlers�Ɋ֐��E���\�b�h��ǉ�����
     *
     * url_handlers�͍Ō��addURLHandler()���ꂽ���̂�����s�����
     */
    function addURLHandler($name, $handler)
    {
        ;
    }

    /**
     * url_handlers����֐��E���\�b�h���폜����
     */
    function removeURLHandler($name)
    {
        ;
    }
    
    /**
     * ���X�t�B���^�����O�̃^�[�Q�b�g�𓾂�
     */
    function getFilterTarget(&$ares, &$i, &$name, &$mail, &$date_id, &$msg)
    {
        switch ($GLOBALS['res_filter']['field']) {
            case 'name':
                $target = $name; break;
            case 'mail':
                $target = $mail; break;
            case 'date':
                $target = preg_replace('| ?ID:[0-9A-Za-z/.+?]+.*$|', '', $date_id); break;
            case 'id':
                if ($target = preg_replace('|^.*ID:([0-9A-Za-z/.+?]+).*$|', '$1', $date_id)) {
                    break;
                } else {
                    return '';
                }
            case 'msg':
                $target = $msg; break;
            default: // 'hole'
                $target = strval($i) . '<>' . $ares;
        }

        $target = @strip_tags($target, '<>');
        
        return $target;
    }

    /**
     * ���X�t�B���^�����O�̃}�b�`����
     */
    function filterMatch(&$target, &$resnum)
    {
        global $_conf;
        global $filter_hits, $filter_range;
        
        $failed = ($GLOBALS['res_filter']['match'] == 'off') ? TRUE : FALSE;
        
        if ($GLOBALS['res_filter']['method'] == 'and') {
            $words_fm_hit = 0;
            foreach ($GLOBALS['words_fm'] as $word_fm_ao) {
                if (StrCtl::filterMatch($word_fm_ao, $target) == $failed) {
                    if ($GLOBALS['res_filter']['match'] != 'off') {
                        return false;
                    } else {
                        $words_fm_hit++;
                    }
                }
            }
            if ($words_fm_hit == count($GLOBALS['words_fm'])) {
                return false;
            }
        } else {
            if (StrCtl::filterMatch($GLOBALS['word_fm'], $target) == $failed) {
                return false;
            }
        }

        $GLOBALS['filter_hits']++;
        
        if ($_conf['filtering'] && !empty($filter_range) &&
            ($filter_hits < $filter_range['start'] || $filter_hits > $filter_range['to'])
        ) {
            return false;
        }
        
        $GLOBALS['last_hit_resnum'] = $resnum;

        if (empty($_conf['ktai'])) {
            echo <<<EOP
<script type="text/javascript">
<!--
filterCount({$GLOBALS['filter_hits']});
-->
</script>\n
EOP;
        }
        
        return true;
    }
}
?>