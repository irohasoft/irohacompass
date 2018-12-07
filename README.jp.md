# iroha Compass

iroha Compass とは自律学習支援システムです。
学習者一人ひとりの主体的で深い学びを支援します。

## 公式サイト
http://irohacompass.irohasoft.jp/

## デモサイト
http://demoib.irohasoft.com/

## 動作環境
PHP : 5.4以上
MySQL : 5.1以上
CakePHP : 2.10.x

##インストール方法
1. iroha Compass のソースをダウンロードし、解凍します。  
https://github.com/irohasoft/irohacompass/releases
* CakePHP 2.7 のソースをダウンロードし、解凍します。  
https://github.com/cakephp/cakephp/releases/tag/2.10.13
* Webサーバ上の非公開ディレクトリに cake フォルダを作成し、CakePHP 2.10 のソースを全てアップロードします。
* 公開ディレクトリに irohaBoard をアップロードします。
* データベース(Config/database.php)の設定を行います。  
  ※事前に空のデータベースを作成しておく必要があります。(推奨文字コード : UTF-8)  
* ディレクトリ構成が以下のようになっていない場合、設定ファイル(webroot/index.php)を書き換えます。  
/cake  
┗ /lib  
/public_html  
┣ /Config  
┣ /Controller  
┣ /Model  
┣ ・・・  
┣ /View  
┗ /webroot  
* ブラウザを開き、http://(your-domain-name)/install にてインストールを実行します。  
画面上にインストール完了のメッセージが表示されればインストールは完了です。

## 主な機能
### 受講者側
* 学習テーマ／学習目標の設定
* 課題の管理
* 進捗の登録
* 最近の進捗状況の表示
* お知らせの表示
* メール通知機能

### 管理者側
* ユーザ管理
* グループ管理
* お知らせ管理
* 学習者の学習テーマの管理
* 学習者の課題の管理
* 学習者の学習進捗の検索
* コメントの追加
* 学習者の最近の進捗の表示
* メール通知機能
* システム設定
  

## License
GPLv3
