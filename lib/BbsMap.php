<?php

// {{{ BbsMap

/**
 * BbsMapクラス
 * 板-ホストの対応表を作成し、それに基づいてホストの同期を行う
 * 外部板がrep2に登録されているかどうかの判定も兼ねる
 *
 * @static
 */
class BbsMap
{
    // {{{ static properties

    /**
     * 板-ホストの対応表
     *
     * @var array
     */
    static private $_map = null;

    // }}}
    // {{{ getCurrentHost()

    /**
     * 最新のホストを取得する
     *
     * @param   string  $host   ホスト名
     * @param   string  $bbs    板名
     * @param   bool    $autosync   移転を検出したときに自動で同期するか否か
     * @return  string  板に対応する最新のホスト
     */
    static public function getCurrentHost($host, $bbs, $autosync = true)
    {
        static $synced = false;

        // マッピング読み込み
        $map = self::_getMapping();
        if (!$map) {
            return $host;
        }
        $type = self::_detectHostType($host);

        // チェック
        if (isset($map[$type]) && isset($map[$type][$bbs])) {
            $new_host = $map[$type][$bbs]['host'];
            if ($host != $new_host && $autosync && !$synced) {
                // 移転を検出したらお気に板、お気にスレ、最近読んだスレを自動で同期
                $msg_fmt = '<p>rep2 info: ホストの移転を検出しました。(%s/%s → %s/%s)<br>';
                $msg_fmt .= 'お気に板、お気にスレ、最近読んだスレを自動で同期します。</p>';
                P2Util::pushInfoHtml(sprintf($msg_fmt, $host, $bbs, $new_host, $bbs));
                self::syncFav();
                $synced = true;
            }
            $host = $new_host;
        }

        return $host;
    }

    // }}}
    // {{{ getBbsName()

    /**
     * 板名LONGを取得する
     *
     * @param   string  $host   ホスト名
     * @param   string  $bbs    板名
     * @return  string  板メニューに記載されている板名
     */
    static public function getBbsName($host, $bbs)
    {
        // マッピング読み込み
        $map = self::_getMapping();
        if (!$map) {
            return $bbs;
        }
        $type = self::_detectHostType($host);

        // チェック
        if (isset($map[$type]) && isset($map[$type][$bbs])) {
            $itaj = $map[$type][$bbs]['itaj'];
        } else {
            $itaj = $bbs;
        }

        return $itaj;
    }

    // }}}
    // {{{ isRegisteredBbs()

    /**
     * 板がrep2に登録されているかどうか
     *
     * @param   string  $host   ホスト名
     * @param   string  $bbs    板名
     * @return  bool  rep2に追加されている板ならtrue
     */
    static public function isRegisteredBbs($host, $bbs)
    {
        global $_conf;

        $type = self::_detectHostType($host);

        // 登録無しでもrep2で扱える板はチェック無しでtrue
        if($host != $type) {
            return true;
        }

        // マッピング読み込み
        $map = self::_getMapping();
        if (!$map) {
            return false;
        }

        // チェック
        if (isset($map[$type]) && isset($map[$type][$bbs])) {
            return true;
        }

        // もし見つからなければお気に板の内容も確認(外部板が登録可能になっているため)
        if ($lines = FileCtl::file_read_lines($_conf['favita_brd'], FILE_IGNORE_NEW_LINES)) {
            foreach ($lines as $l) {
                if (preg_match("/^\t?(.+)\t(.+)\t(.+)\$/", $l, $matches)) {
                    if ($host == $matches[1])
                    {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    // }}}
    // {{{ syncBrd()

    /**
     * お気に板などのbrdファイルを同期する
     *
     * @param   string  $brd_path   brdファイルのパス
     * @return  void
     */
    static public function syncBrd($brd_path)
    {
        global $_conf;
        static $done = array();

        // {{{ 読込

        if (isset($done[$brd_path])) {
            return;
        }

        if (!($lines = FileCtl::file_read_lines($brd_path))) {
            return;
        }
        $map = self::_getMapping();
        if (!$map) {
            return;
        }
        $neolines = array();
        $updated = false;

        // }}}
        // {{{ 同期

        foreach ($lines as $line) {
            $setitaj = false;
            $data = explode("\t", rtrim($line, "\n"));
            $hoge = $data[0]; // 予備?
            $host = $data[1];
            $bbs  = $data[2];
            $itaj = $data[3];
            $type = self::_detectHostType($host);

            if (isset($map[$type]) && isset($map[$type][$bbs])) {
                $newhost = $map[$type][$bbs]['host'];
                if ($itaj === '') {
                    $itaj = $map[$type][$bbs]['itaj'];
                    if ($itaj != $bbs) {
                        $setitaj = true;
                    } else {
                        $itaj = '';
                    }
                }
            } else {
                $newhost = $host;
            }

            if ($host != $newhost || $setitaj) {
                $neolines[] = "{$hoge}\t{$newhost}\t{$bbs}\t{$itaj}\n";
                $updated = true;
            } else {
                $neolines[] = $line;
            }
        }

        // }}}
        // {{{ 書込

        $brd_name = p2h(basename($brd_path));
        if ($updated) {
            self::_writeData($brd_path, $neolines);
            P2Util::pushInfoHtml(sprintf('<p class="info-msg">rep2 info: %s を同期しました。</p>', $brd_name));
        } else {
            P2Util::pushInfoHtml(sprintf('<p class="info-msg">rep2 info: %s は変更されませんでした。</p>', $brd_name));
        }
        $done[$brd_path] = true;

        // }}}
    }

    // }}}
    // {{{ syncIdx()

    /**
     * お気にスレなどのidxファイルを同期する
     *
     * @param   string  $idx_path   idxファイルのパス
     * @return  void
     */
    static public function syncIdx($idx_path)
    {
        global $_conf;
        static $done = array();

        // {{{ 読込

        if (isset($done[$idx_path])) {
            return;
        }

        if (!($lines = FileCtl::file_read_lines($idx_path))) {
            return;
        }
        $map = self::_getMapping();
        if (!$map) {
            return;
        }
        $neolines = array();
        $updated = false;

        // }}}
        // {{{ 同期

        foreach ($lines as $line) {
            $data = explode('<>', rtrim($line, "\n"));
            $host = $data[10];
            $bbs  = $data[11];
            $type = self::_detectHostType($host);

            if (isset($map[$type]) && isset($map[$type][$bbs])) {
                $newhost = $map[$type][$bbs]['host'];
            } else {
                $newhost = $host;
            }

            if ($host != $newhost) {
                $data[10] = $newhost;
                $neolines[] = implode('<>', $data) . "\n";
                $updated = true;
            } else {
                $neolines[] = $line;
            }
        }

        // }}}
        // {{{ 書込

        $idx_name = p2h(basename($idx_path));
        if ($updated) {
            self::_writeData($idx_path, $neolines);
            P2Util::pushInfoHtml(sprintf('<p class="info-msg">rep2 info: %s を同期しました。</p>', $idx_name));
        } else {
            P2Util::pushInfoHtml(sprintf('<p class="info-msg">rep2 info: %s は変更されませんでした。</p>', $idx_name));
        }
        $done[$idx_path] = true;

        // }}}
    }

    // }}}
    // {{{ syncFav()

    /**
     * お気に板、お気にスレ、最近読んだスレを同期する
     *
     * @return  void
     */
    static public function syncFav()
    {
        global $_conf;
        self::syncBrd($_conf['favita_brd']);
        self::syncIdx($_conf['favlist_idx']);
        self::syncIdx($_conf['recent_idx']);
    }

    // }}}
    // {{{ _getMapping()

    /**
     * 2ch公式メニューをパースし、板-ホストの対応表を作成する
     *
     * @return  array   site/bbs/(host,itaj) の多次元連想配列
     *                  ダウンロードに失敗したときは false
     */
    static private function _getMapping()
    {
        global $_conf;

        // {{{ 設定
        $map_cache_path = $_conf['cache_dir'] . '/host_bbs_map.txt';
        $map_cache_lifetime = 60 * 10; // 10分おきにキャッシュを再構築するが、BrdCtl側で最低30分はアクセスしない。

        // }}}
        // {{{ キャッシュ確認

        if (!is_null(self::$_map)) {
            return self::$_map;
        } elseif (file_exists($map_cache_path)) {
            $mtime = filemtime($map_cache_path);
            $expires = $mtime + $map_cache_lifetime;
            if (time() < $expires) {
                $map_cahce = file_get_contents($map_cache_path);
                self::$_map = unserialize($map_cahce);
                return self::$_map;
            }
        } else {
            FileCtl::mkdirFor($map_cache_path);
        }
        touch($map_cache_path);
        clearstatcache();

        // }}}
        // {{{ メニューをダウンロード
        $brd_menus_online = BrdCtl::read_brds();
        $map = array();

        foreach ($brd_menus_online as $a_brd_menu) {
            foreach ($a_brd_menu->categories as $cate) {
                if ($cate->num > 0) {
                    foreach ($cate->menuitas as $mita) {
                        $host = $mita->host;
                        $bbs  = $mita->bbs;
                        $itaj = $mita->itaj;
                        $type = self::_detectHostType($host);
                        if (!isset($map[$type])) {
                            $map[$type] = array();
                        }
                        $map[$type][$bbs] = array('host' => $host, 'itaj' => $itaj);
                    }
                }
            }
        }
        unset ($brd_menus_online);
        // }}}
        // {{{ キャッシュする

        $map_cache = serialize($map);
        if (FileCtl::file_write_contents($map_cache_path, $map_cache) === false) {
            p2die("cannot write file. ({$map_cache_path})");
        }

        // }}}

        return (self::$_map = $map);
    }

    // }}}
    // {{{ _writeData()

    /**
     * 更新後のデータを書き込む
     *
     * @param   string  $path   書き込むファイルのパス
     * @param   array   $neolines   書き込むデータの配列
     * @return  void
     */
    static private function _writeData($path, $neolines)
    {
        if (is_array($neolines) && count($neolines) > 0) {
            $cont = implode('', $neolines);
        /*} elseif (is_scalar($neolines)) {
            $cont = strval($neolines);*/
        } else {
            $cont = '';
        }
        if (FileCtl::file_write_contents($path, $cont) === false) {
            p2die("cannot write file. ({$path})");
        }
    }

    // }}}
    // {{{ _detectHostType()

    /**
     * ホストの種類を判定する
     *
     * @param   string  $host   ホスト名
     * @return  string  ホストの種類
     */
    static private function _detectHostType($host)
    {
        if (P2Util::isHostBbsPink($host)) {
            $type = 'bbspink';
        } elseif (P2Util::isHost2chs($host)) {
            $type = '2channel';
        } elseif (P2Util::isHostMachiBbs($host)) {
            $type = 'machibbs';
        } elseif (P2Util::isHostJbbsShitaraba($host)) {
            $type = 'jbbs';
        } else {
            $type = $host;
        }
        return $type;
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
