﻿# rep2 expack 全部入り by 2ch774

* rep2-expack https://github.com/rsky/p2-php
* rep2-expack +live https://github.com/pluslive/p2-php
* rep2-expack test https://github.com/orzisun/p2-php

上記やスレに上げられた修正を取り込んで全部入りを目指す闇鍋バージョンです。

独自の改良も行っています。

### 追加機能

各機能の説明はdocディレクトリのREADMEファイルを見てください。


## セットアップ

### Git & Composerで

1. 本体をclone
  <pre>git clone git://github.com/2ch774/p2-php.git
  cd p2-php</pre>

2. 依存ライブラリをダウンロード
  <pre>curl -O http://getcomposer.org/composer.phar
  php -d detect_unicode=0 composer.phar install</pre>

3. Webサーバが書き込めるようにディレクトリのアクセス権をセット  
  (CGI/suEXECIやCLI/Built-in web serverでは不要)
  <pre>chmod 0777 data/* rep2/ic</pre>


## 動作環境
Linux(openSUSE Leap)のPHP7+Apacheで動作確認しています。
PHP5.6以降で動くはずですが、PHP7.*推奨です。
PHP7での不具合修正を優先するため突然PHP5.xのサポートが終わる可能性があります。

以下のコマンドを実行して、全ての項目で `OK` が出たなら大丈夫です。

何かエラーが出たらがんばって環境を整えてください。

    php scripts/p2cmd.php check


## Built-in web serverで使ってみる (PHP 5.4+)

PHP 5.4の新機能、[ビルトインウェブサーバー](http://docs.php.net/manual/ja/features.commandline.webserver.php)で簡単に試せます。

以下のようにすると、Webサーバーの設定をしなくても `http://localhost:8080/` でrep2を使えます。**(Windowsでも!)**

    cd rep2
    php -S localhost:8080 web.php

moriyoshi++


## 画像を自動で保存したい

スレに貼られている画像を自動で保存する機能、**ImageCache2**があります。

see also [doc/ImageCache2/README.txt](https://github.com/rsky/p2-php/blob/master/doc/ImageCache2/README.txt), [doc/ImageCache2/INSTALL.txt](https://github.com/rsky/p2-php/blob/master/doc/ImageCache2/INSTALL.txt)

### 準備

1. SQLite以外のデータベースを使う場合はデータベースサーバーを立ち上げておく。  

2. conf/conf_admin_ex.inc.phpでImageCache2を有効にする。
  <pre>$_conf['expack.ic2.enabled'] = 3;</pre>

3. conf/conf_ic2.inc.phpで[DSN](http://pear.php.net/manual/ja/package.database.db.intro-dsn.php)を設定する。
  <pre>$_conf['expack.ic2.general.dsn'] = 'mysql://username:password@localhost:3306/database';</pre>

4. setupスクリプトを実行する。
  <pre>php scripts/ic2.php setup</pre>

### 注意

* PHP 5.4ではSQLite2がサポートされなくなったので、ImageCache2を使いたいときはMySQLかPostgreSQLが必要です。
* ホストに`localhost`を指定して接続できないときは、代わりに`127.0.0.1`にしてみてください。


## 設定を変えたい

細かい挙動の変更は `メニュー > 設定管理 > ユーザー設定編集` から行えます。

Webブラウザから変更できない項目は [conf/conf_admin.inc.php](https://github.com/rsky/p2-php/blob/master/conf/conf_admin.inc.php) (基本), [conf/conf_admin_ex.inc.php](https://github.com/rsky/p2-php/blob/master/conf/conf_admin_ex.inc.php) (拡張パック), [conf/conf_ic2.inc.php](https://github.com/rsky/p2-php/blob/master/conf/conf_ic2.inc.php) (ImageCache2) を直接編集します。

どういうことができるか書き起こすのが面倒なので設定ファイルのコメントを見てください。


## 更新

    php scripts/p2cmd.php update

これは下記コマンドを個別に実行するのと等価です。

    git pull
    php -d detect_unicode=0 composer.phar self-update
    php -d detect_unicode=0 composer.phar update


## Authors & Contributors

* **aki** *(original)* http://akid.s17.xrea.com/
* **rsk** *(expack)* https://github.com/rsky/p2-php/
* **unpush** https://github.com/unpush/p2-php/
* **thermon** https://github.com/thermon/p2-php/
* **part32の892** *(+live)* https://github.com/pluslive/p2-php/
* **orzisun** https://github.com/orzisun/p2-php
* **2ch774** https://github.com/2ch774/p2-php
* **killer4989** https://github.com/killer4989/p2-php
* **dgg712** https://github.com/dgg712/p2-php
* **2ch p2/rep2スレの>>1-1000**


## License

see [LICENSE.txt](https://github.com/rsky/p2-php/blob/master/LICENSE.txt)
