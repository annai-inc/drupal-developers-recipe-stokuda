---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# Drupalの動作環境

---

Drupalを利用するには、まずDrupalが使える環境を準備する必要があります。
DrupalはLAMPスタック上で動作するソフトウェアです。そのため、最低限の要素として、
- PHP
- Webサーバー
- データベースサーバー

が必要となります。
詳細は [System requirements](https://www.drupal.org/docs/8/system-requirements) で確認することができます。

---

## 最もシンプルなDrupalの実行環境

先述した通り、Drupalの動作にはLAMPスタックが必要です。

しかし、Drupal 8からは他のモダンなフレームワークと同様に、プログラミング言語自身に組み込まれたWebサーバーとsqliteを使って起動する事ができます。

以降、本コンテンツはこの動作環境を前提として説明していきます。

---

## PHPのインストール

PHP 7.2をインストールします。環境によりパッケージ名が異なるため、自身の実行環境に合わせてパッケージをインストールしてください。

インストールが完了したら、以下のようにバージョンが取得できるか確認してください(表記は環境により異なる可能性があります)。

```
$ php -v
PHP 7.2.22 (cli) (built: Nov 22 2019 12:16:46) ( NTS )
Copyright (c) 1997-2018 The PHP Group
...
```

---

### Drupalが依存するPHPの拡張機能

DrupalはいくつかのPHPの拡張機能を必須で要求します。これらの拡張機能も忘れずにインストールしてください。
詳細は [PHP extensions needed](https://www.drupal.org/docs/8/system-requirements/php-requirements#extensions) を参照してください。

PHPの拡張機能が有効になっているかどうかは、 `$ php -i |grep enabled` などで確認することができます。

---

## sqlite3のインストール

sqliteをインストールします。環境によりパッケージ名が異なるため、以下を参考に自身の実行環境に合わせてパッケージをインストールしてください。

```
# debian系Linux
$ sudo apt-get install sqlite3

# redhat系Linux
$ sudo yum install sqlite

# MacOS
$ brew install sqlite3
```

---

インストールが完了したら、以下のようにバージョンが取得できるか確認してください(表記は環境により異なる可能性があります)。

```
$ sqlite3 --version
3.30.1 2019-10-10 20:19:45 18db032d058f1436ce3dea84081f4ee5a0f2259ad97301d43c426bc7f3df1b0b
```

---

## Composerのインストール

Drupal 8以降では、構成管理のためにComposerを利用します。
[Download Composer](https://getcomposer.org/download/)に従い `composer` コマンドが実行可能になるようにインストールしてください。

インストールが完了したら、以下のようにバージョンが取得できるか確認してください(表記は環境により異なる可能性があります)。
```
$ composer -V
Composer version 1.9.1 2019-11-01 17:20:17
```

---

## Composerの初期設定

Composerは特に設定なしでもすぐに利用可能ですが、デフォルトの設定だと、
- パッケージを管理しているサーバーが遠いため遅い
- パッケージを並列にダウンロードができない

という問題があります。待ち時間を減らして作業効率を上げるために、いくつかの初期設定を行います。

---

### 日本のミラーリングサーバーを利用する

[packagist.jp](https://packagist.jp/) を参照するように設定を変更します。
([@hiraku](https://twitter.com/Hiraku)さんが個人で運営されていますが、とても安定しているサーバーです)。

```
$ composer config -g repos.packagist composer https://packagist.jp
```

---

### パッケージを並列でダウンロードできるようにする

composerで [hirak/prestissimo](https://github.com/hirak/prestissimo) を導入します。

```
$ composer global require hirak/prestissimo
```

---

これでDrupalを実行するための環境が準備できました。
次章では、実際にDrupalを起動していきます。
