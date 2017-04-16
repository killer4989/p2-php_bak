<?php

// {{{ P2Util

/**
 * rep2 - �]���̃T�[�o�[�Ƃ��b�����邽�߂̋@�\��P2Util���番���������[�e�B���e�B�N���X
 * �C���X�^���X����炸�ɃN���X���\�b�h�ŗ��p����
 *
 * @create  2017/04/06
 * @static
 */
class P2Commun
{
    // {{{ createHTTPRequest()

    /**
     * HTTP_Request2�N���X�̃C���X�^���X�𐶐�����
     *
     * @param string $url �������URL(��΂ɕK�{)
     * @param $method HTTP_Request2�Ɠ���
     * @return HTTP_Request2
     */
    static public function createHTTPRequest($url , $method = HTTP_Request2::METHOD_GET)
    {
        global $_conf;

        $purl = parse_url ($url);

        if(empty($url) || $purl === false)
        {
            throw new InvalidArgumentException ("URL�̎w�肪�ςł��B");
        }

        $req = new HTTP_Request2($url, $method);

        // �悭�g���w�b�_���w��
        // p2��HTTP�ʐM�͓��Ɏw��̖�������Monazilla�𖼏��悤�ɂ���
        $req->setHeader ('User-Agent', self::getP2UA(true,P2Util::isHost2chs($purl['host'])));
        $req->setHeader ('Acecpt-Language', 'ja,en-us;q=0.7,en;q=0.3');
        $req->setHeader ('Accept', '*/*');
        $req->setHeader ('Accept-Encoding', 'gzip, deflate');

        // �^�C���A�E�g�̐ݒ�
        $req->setConfig (array (
                'connect_timeout' => $_conf['http_conn_timeout'],
                'timeout' => $_conf['http_read_timeout'],
        ));

        // SSL�̐ݒ�
        if($purl['scheme'] == 'https') {
            $req->setAdapter($_conf['ssl_function']);

            if($_conf['ssl_capath'])
            {
                $req->setConfig ('ssl_capath', $_conf['ssl_capath']);
            }
        }

        // �v���L�V
        if ($_conf['tor_use'] && P2Util::isHostTor($purl['host'], 0)) { // Tor(.onion)��Tor�p�̐ݒ���Z�b�g
            $req->setConfig (array (
                    'proxy_host' => $_conf['tor_proxy_host'],
                    'proxy_port' => $_conf['tor_proxy_port'],
                    'proxy_user' => $_conf['tor_proxy_user'],
                    'proxy_password' => $_conf['tor_proxy_password']
            ));
            if($_conf['tor_proxy_mode'] == 'socks5'){
                $req->setConfig('proxy_type', $_conf['tor_proxy_mode']);
            }
        } elseif ($_conf['proxy_use']) {
            $req->setConfig (array (
                    'proxy_host' => $_conf['proxy_host'],
                    'proxy_port' => $_conf['proxy_port'],
                    'proxy_user' => $_conf['proxy_user'],
                    'proxy_password' => $_conf['proxy_password']
            ));
            if($_conf['proxy_mode'] == 'socks5'){
                $req->setConfig('proxy_type', $_conf['proxy_mode']);
            }
        }

        unset ($purl);

        return $req;
    }

    static public function getHTTPResponse($req) {
        if($req->getConfig('proxy_type') == 'socks5') {
            $socks = new HTTP_Request2_Adapter_Socket();
            $res = $socks->sendRequest($req);
            unset($socks);
        } else {
            $res = $req->send ();
        }
        return $res;
    }
    // }}}
    // {{{ getP2UA()
    /**
     * p2����API��UA��Ԃ�
     * @param   bool $withMonazilla true�Ȃ�Monazilla/1.00��t����
     * @param   bool $apiUA true�ŏ�����API�����p�\�ȂƂ���API��UA��Ԃ�
     * @return  string
     */
    static public function getP2UA($withMonazilla = true,$apiUA = false)
    {
        global $_conf;

        // API���g�p����ݒ�̏ꍇ��API��UA��Ԃ�
        if ($apiUA && $_conf['2chapi_use'] == 1) {
            if ($_conf['2chapi_appname'] != "") {
                $p2ua = $_conf['2chapi_appname'];
            } else {
                p2die("2ch�ƒʐM���邽�߂ɕK�v�ȏ�񂪐ݒ肳��Ă��܂���B");
            }

        } else {
            $p2ua = $_conf['p2ua'];
        }

        if ($withMonazilla) {
            $p2ua = sprintf('Monazilla/1.00 (%s)', $p2ua);
        }

        return $p2ua;
    }
    // }}}
    // {{{ getWebPage

    /**
     * Web�y�[�W���擾����
     *
     * 200 OK
     * 206 Partial Content
     * 304 Not Modified �� ���s����
     *
     * @return array|false ����������y�[�W���e��Ԃ��B���s������false��Ԃ��B
     */
    static public function getWebPage($url, &$error_msg, $timeout = 15)
    {
        try {
            $req = self::createHTTPRequest($url, HTTP_Request2::METHOD_GET);
            //$req->addHeader("X-PHP-Version", phpversion());

            $response = self::getHTTPResponse($req);

            $code = $response->getStatus();
            if ($code == 200 || $code == 206) { // || $code == 304) {
                return $response->getBody();
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    // }}}
    // {{{ fileDownload()

    /**
     *  �t�@�C�����_�E�����[�h�ۑ�����
     */
    static public function fileDownload($url, $localfile,
                                        $cache_time = 0,
                                        $disp_error = true,
                                        $trace_redirection = false)
    {
        global $_conf;

        if (file_exists($localfile)) {
            // �L���b�V���L�����ԂȂ�_�E�����[�h���Ȃ�
            if (filemtime($localfile) > time() - $cache_time) {
                return null;
            }
        }

        try {
            // DL
            $req = self::createHTTPRequest($url, HTTP_Request2::METHOD_GET);

            $req->setConfig(array('follow_redirects' => $trace_redirection));

            if (file_exists($localfile)) {
                $req->setHeader ('If-Modified-Since', http_date(filemtime($localfile)) );
            }

            $response = self::getHTTPResponse($req);

            $code = $response->getStatus();
            if (!($code == 200 || $code == 206 || $code == 304)) {
                $error_msg = $code;
            }
            $body = $response->getBody();

        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }

        // �G���[���o����null��Ԃ��ďI���
        if (isset($error_msg) && strlen($error_msg) > 0) {
            // �G���[���b�Z�[�W��ݒ�
            if ($disp_error) {
                $url_t = self::throughIme($url);
                $info_msg_ht = "<p class=\"info-msg\">Error: {$error_msg}<br>";
                $info_msg_ht .= "rep2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$url}</a> �ɐڑ��ł��܂���ł����B</p>";
                self::pushInfoHtml($info_msg_ht);
            }
            return null;
        }

        // �X�V����Ă�����ۑ�
        if ($code != 304) {
            if (FileCtl::file_write_contents($localfile, $body) === false) {
                p2die('cannot write file.');
            }
        }

        return $response;
    }

    // }}}
    public static function getResponseCode($url)
    {
        try {
            $req = self::createHTTPRequest ($url, HTTP_Request2::METHOD_HEAD);
            $response = self::getHTTPResponse($req);
            return $response->getStatus();

        } catch (Exception $e) {
            return false; // $error_msg
        }
    }
}