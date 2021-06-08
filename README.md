# iroha Compass

iroha Compass とは知識創造型学習支援システムです。[[English]](/README.en.md)

学習者一人ひとりの主体的で深い学びを支援します。

## 公式サイト
https://irohacompass.irohasoft.jp/

## デモサイト
https://demoic.irohasoft.com/

## 動作環境
* PHP : 5.4以上
* MySQL : 5.1以上
* CakePHP : 2.10

## インストール方法
1. CakePHP のソースをダウンロードし、解凍します。
https://github.com/cakephp/cakephp/releases/tag/2.10.24
2. iroha Compass のソースをダウンロードし、解凍します。
https://github.com/irohasoft/irohacompass/releases
3. CakePHP の app ディレクトリ内のソースを iroha Compass のソースに差し替えます。
4. データベース(app/Config/database.php)の設定を行います。
   ※事前に空のデータベースを作成しておく必要があります。(推奨文字コード : UTF-8)
5. 公開ディレクトリに全ソースをアップロードします。
6. ブラウザを開き、http://(your-domain-name)/install にてインストールを実行します。

## 主な機能
### 学習者側
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
