<?php
/**
 * rep2 - 2ch API ���O�C��
 */

// {{{ authenticate_2chapi()


/**
 * 2chAPI�� SID ���擾����
 *
 * @return string|false  �擾�ł����ꍇ��SID��Ԃ�
 */
    function authenticate_2chapi()
    {
        global $_conf;

        if ($_conf['2chapi_ssl.auth']) {
            $authAPI_url = 'https://api.2ch.net/v1/auth/';
        } else {
            $authAPI_url = 'http://api.2ch.net/v1/auth/';
        }

        $CT = time();
        $AppKey = $_conf['2chapi_appkey'];
        $HMKey = $_conf['2chapi_hmkey'];
        $message = $AppKey.$CT;
        $HB = hash_hmac("sha256", $message, $HMKey);

        $AppName = $_conf['2chapi_appname'];
        $AuthUA = sprintf($_conf['2chapi_ua.auth'],$AppName);

        if(empty($AppKey) || empty($HMKey) || empty($AppName)) {
            P2Util::pushInfoHtml("<p>p2 Error: 2ch API �̔F�؂ɕK�v�ȏ�񂪐ݒ肳��Ă��܂���B</p>");
            return '';
        }

        $login2chID = "";
        $login2chPW = "";

        // 2ch��ID, PW�ݒ��ǂݍ���
        if ($array = P2Util::readIdPw2ch()) {
            list($login2chID, $login2chPW, $autoLogin2ch) = $array;
        }

        try {
            $req = P2Commun::createHTTPRequest($authAPI_url,HTTP_Request2::METHOD_POST);

            // �w�b�_�[
            $req->setHeader('User-Agent', $AuthUA);
            $req->setHeader('X-2ch-UA', $AppName);

            // POST�f�[�^
            $req->addPostParameter('KY', $AppKey);
            $req->addPostParameter('CT', $CT);
            $req->addPostParameter('HB', $HB);
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
            P2Util::pushInfoHtml("<p>p2 Error: 2ch API �̔F�؃T�[�o�ɐڑ��o���܂���ł����B({$e->getMessage()})</p>");
        }

        // �ڑ����s�Ȃ��
        if (empty($body)) {
            if (file_exists($_conf['sid2chapi_php'])) { unlink($_conf['sid2chapi_php']); }

            P2Util::pushInfoHtml('<p>p2 info: 2�����˂�� API ���g�p����ɂ́APHP��<a href="'.
                    P2Util::throughIme("http://www.php.net/manual/ja/ref.curl.php").
                    '">cURL�֐�</a>����<a href="'.
                    P2Util::throughIme("http://www.php.net/manual/ja/ref.openssl.php").
                    '">OpenSSL�֐�</a>���L���ł���K�v������܂��B</p>');

            P2Util::pushInfoHtml("<p>p2 error: 2ch API �F�؂Ɏ��s���܂����B{$curl_msg}</p>");
            return false;
        }

        $body = rtrim($body);

        // ����
        if (!preg_match('/SESSION-ID=(.+?):(.+)/', $body, $matches)) {
            if (file_exists($_conf['sid2chapi_php'])) { unlink($_conf['sid2chapi_php']); }
            P2Util::pushInfoHtml("<p>p2 error: 2ch API �̃��X�|���X����SessionID���擾�o���܂���ł����B</p>");
            return false;
        }
        $uaMona = $matches[1];
        $SID2ch = $matches[1] . ':' . $matches[2];

        // SID �̋L�^�ێ�
        $cont = sprintf('<?php $uaMona = %s; $SID2chAPI = %s;', var_export($uaMona, true), var_export($SID2ch, true));
        if (false === file_put_contents($_conf['sid2chapi_php'], $cont, LOCK_EX)) {
            P2Util::pushInfoHtml("<p>p2 Error: {$_conf['sid2chapi_php']} ��ۑ��ł��܂���ł����B���O�C���o�^���s�B</p>");
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
