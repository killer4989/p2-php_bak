<?php

// {{{ BrdCtl

/**
 * rep2 - BrdCtl -- ���X�g�R���g���[���N���X for menu.php
 *
 * @static
 */
class BrdCtl
{
    // {{{ read_brds()

    /**
     * board��S�ēǂݍ���
     */
    static public function read_brds()
    {
        $brd_menus_dir = BrdCtl::read_brd_dir();
        $brd_menus_online = BrdCtl::read_brd_online();
        $brd_menus = array_merge($brd_menus_dir, $brd_menus_online);
        return $brd_menus;
    }

    // }}}
    // {{{ read_brd_dir()

    /**
     * board�f�B���N�g���𑖍����ēǂݍ���
     */
    static public function read_brd_dir()
    {
        global $_conf;
        $brd_menus = array();
        $brd_dir = $_conf['data_dir'] . '/board';

        // �f�B���N�g�����Ȃ��ꍇ�͐V�K�ō쐬
        if (!file_exists($brd_dir)) {
            FileCtl::mkdirRecursive($brd_dir);
            if(!is_writable($brd_dir)){
                // �������݌����𓾂��Ȃ������ꍇ�̓p�[�~�b�V�����̒��ӊ��N������
                p2die("�e�f�B���N�g���̃p�[�~�b�V�������������ĉ������B");
            }
            return $brd_menus;
        }

        if ($cdir = @dir($brd_dir)) {
            // �f�B���N�g������
            while ($entry = $cdir->read()) {
                if ($entry[0] == '.') {
                    continue;
                }
                $filepath = $brd_dir.'/'.$entry;
                if ($data = FileCtl::file_read_lines($filepath)) {
                    $aBrdMenu = new BrdMenu();    // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
                    $aBrdMenu->setBrdMatch($filepath);    // �p�^�[���}�b�`�`����o�^
                    $aBrdMenu->setBrdList($data);    // �J�e�S���[�Ɣ��Z�b�g
                    $brd_menus[] = $aBrdMenu;

                } else {
                    P2Util::pushInfoHtml("<p>p2 error: ���X�g {$entry} ���ǂݍ��߂܂���ł����B</p>");
                }
            }
            $cdir->close();
        }

        return $brd_menus;
    }

    // }}}
    // {{{ read_brd_online()

    /**
    * �I�����C�����X�g��Ǎ���
    */
    static public function read_brd_online()
    {
        global $_conf;

        $brd_menus = array();
        $isNewDL = false;

        if ($_conf['brdfile_online']) {
            $cachefile = P2Util::cacheFileForDL($_conf['brdfile_online']);

            $read_html_flag = false;

            // DL����A������norefresh�Ȃ�DL���Ȃ�
            if (empty($_GET['nr']) || !file_exists($cachefile.'.p2.brd')) {
                //echo "DL!<br>";//
                $cache_time = time() - 60 * 30 * $_conf['menu_dl_interval'];
                $brdfile_online_res = P2Commun::fileDownload($_conf['brdfile_online'], $cachefile, $cache_time);
                if (isset($brdfile_online_res) && $brdfile_online_res->getStatus() != 304) {
                    $isNewDL = true;
                }

                unset($brdfile_online_res);
            }

            // html�`���Ȃ�
            if (preg_match('/html?$/', $_conf['brdfile_online'])) {

                // �X�V����Ă�����V�K�L���b�V���쐬
                if ($isNewDL) {
                    // �������ʂ��L���b�V�������̂����
                    if (isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) {
                        $_tmp = array($GLOBALS['word'], $GLOBALS['word_fm'], $GLOBALS['words_fm']);
                        $GLOBALS['word'] = null;
                        $GLOBALS['word_fm'] = null;
                        $GLOBALS['words_fm'] = null;
                    } else {
                        $_tmp = null;
                    }

                    //echo "NEW!<br>"; //
                    $aBrdMenu = new BrdMenu(); // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
                    $aBrdMenu->makeBrdFile($cachefile); // .p2.brd�t�@�C���𐶐�
                    $brd_menus[] = $aBrdMenu;
                    unset($aBrdMenu);

                    if ($_tmp) {
                        list($GLOBALS['word'], $GLOBALS['word_fm'], $GLOBALS['words_fm']) = $_tmp;
                        $brd_menus = array();
                    } else {
                        $read_html_flag = true;
                    }
                }

                if (file_exists($cachefile.'.p2.brd')) {
                    $cache_brd = $cachefile.'.p2.brd';
                } else {
                    $cache_brd = $cachefile;
                }

            } else {
                $cache_brd = $cachefile;
            }

            if (!$read_html_flag) {
                if ($data = FileCtl::file_read_lines($cache_brd)) {
                    $aBrdMenu = new BrdMenu(); // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
                    $aBrdMenu->setBrdMatch($cache_brd); // �p�^�[���}�b�`�`����o�^
                    $aBrdMenu->setBrdList($data); // �J�e�S���[�Ɣ��Z�b�g
                    if ($aBrdMenu->num) {
                        $brd_menus[] = $aBrdMenu;
                    } else {
                        P2Util::pushInfoHtml("<p>p2 error: {$cache_brd} ������j���[�𐶐����邱�Ƃ͂ł��܂���ł����B</p>");
                    }
                    unset($data, $aBrdMenu);
                } else {
                    P2Util::pushInfoHtml("<p>p2 error: {$cachefile} �͓ǂݍ��߂܂���ł����B</p>");
                }
            }
        }

        return $brd_menus;
    }

    // }}}
}

// }}}

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
