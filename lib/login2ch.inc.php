<?php
/**
 * rep2 - 2ch���O�C��
 */

// {{{ login2ch()

/**
 * 2ch ID�Ƀ��O�C������
 *
 * @return string|false  ����������2ch SID��Ԃ�
 */
    function login2ch()
    {
        global $_conf;

        if ($_conf['2ch_ssl.maru']) {
            $auth2ch_url = 'https://2chv.tora3.net/futen.cgi';
        } else {
            $auth2ch_url = 'http://2chv.tora3.net/futen.cgi';
        }

        $dolib2ch = 'DOLIB/1.00';
        if($_conf['2chapi_use'] == 1) {
            if(empty($_conf['2chapi_appname'])) {
                P2Util::pushInfoHtml("<p>p2 error: 2ch�ƒʐM���邽�߂ɕK�v�ȏ�񂪐ݒ肳��Ă��܂���B</p>");
                return false;
            }
            $x_2ch_ua = $_conf['2chapi_appname'];
        } else {
            $x_2ch_ua = P2Commun::getP2UA(false, false);
        }

        // 2ch��ID, PW�ݒ��ǂݍ���
        if ($array = P2Util::readIdPw2ch()) {
            list($login2chID, $login2chPW, $autoLogin2ch) = $array;

        } else {
            P2Util::pushInfoHtml("<p>p2 error: 2ch���O�C���̂��߂�ID�ƃp�X���[�h��o�^���ĉ������B[<a href=\"login2ch.php\" target=\"subject\">2ch���O�C���Ǘ�</a>]</p>");
            return false;
        }

        // �Q�l�̗L�����m�F(�v ID / PW)
        if (!empty($login2chID) && !empty($login2chPW)) {
            P2Util::checkRoninExpiration();
        }

        try {
            $req = P2Commun::createHTTPRequest($auth2ch_url,HTTP_Request2::METHOD_POST);

            // �w�b�_�[
            $req->setHeader('User-Agent', $dolib2ch);
            $req->setHeader('X-2ch-UA', $x_2ch_ua);

            // POST�f�[�^
            $req->addPostParameter('ID', $login2chID);
            $req->addPostParameter('PW', $login2chPW);

            // POST�f�[�^�̑��M
            $res = P2Commun::getHTTPResponse($req);

            $code = $res->getStatus();
            if ($code =! 200) {
                P2Util::pushInfoHtml("<p>p2 Error: HTTP Error({$code})</p>");
            } else {
                $body = $res->getBody();
            }

        } catch (Exception $e) {
            P2Util::pushInfoHtml("<p>p2 Error: ���̔F�؃T�[�o�ɐڑ��o���܂���ł����B({$e->getMessage()})</p>");
        }

        // �ڑ����s�Ȃ��
        if (empty($body)) {
            if (file_exists($_conf['idpw2ch_php'])) { unlink($_conf['idpw2ch_php']); }
            if (file_exists($_conf['sid2ch_php']))  { unlink($_conf['sid2ch_php']); }

            P2Util::pushInfoHtml('<p>p2 info: 2�����˂�ւ́�ID���O�C�����s���ɂ́APHP��<a href="'.
                    P2Util::throughIme("http://www.php.net/manual/ja/ref.curl.php").
                    '">cURL�֐�</a>����<a href="'.
                    P2Util::throughIme("http://www.php.net/manual/ja/ref.openssl.php").
                    '">OpenSSL�֐�</a>���L���ł���K�v������܂��B</p>');

            P2Util::pushInfoHtml("<p>p2 error: 2ch���O�C�������Ɏ��s���܂����B{$curl_msg}</p>");
            return false;
        }

        $body = rtrim($body);

        // ����
        if (!preg_match('/SESSION-ID=(.+?):(.+)/', $body, $matches)) {
            if (file_exists($_conf['sid2ch_php'])) { unlink($_conf['sid2ch_php']); }
            P2Util::pushInfoHtml("<p>p2 error: 2ch�����O�C���ڑ��Ɏ��s���܂����B</p>");
            return false;
        }
        $uaMona = $matches[1];
        $SID2ch = $matches[1] . ':' . $matches[2];

        // �F�؏ƍ����s�Ȃ�
        if ($uaMona == 'ERROR') {
            file_exists($_conf['idpw2ch_php']) and unlink($_conf['idpw2ch_php']);
            file_exists($_conf['sid2ch_php']) and unlink($_conf['sid2ch_php']);
            P2Util::pushInfoHtml("<p>p2 error: 2ch�����O�C����SESSION-ID�̎擾�Ɏ��s���܂����BID�ƃp�X���[�h���m�F�̏�A���O�C���������ĉ������B</p>");
            return false;
        }

        // SID �̋L�^�ێ�
        $cont = sprintf('<?php $uaMona = %s; $SID2ch = %s;', var_export($uaMona, true), var_export($SID2ch, true));
        if (false === file_put_contents($_conf['sid2ch_php'], $cont, LOCK_EX)) {
            P2Util::pushInfoHtml("<p>p2 Error: {$_conf['sid2ch_php']} ��ۑ��ł��܂���ł����B���O�C���o�^���s�B</p>");
            return false;
        }

        return $SID2ch;
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
