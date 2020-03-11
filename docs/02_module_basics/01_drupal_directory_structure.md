---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.1 Drupalのディレクトリ構成

---

このセクションでは、DrupalのComposer templateで生成したアプリケーションがどのようなディレクトリ構造を持っているかを説明します。

---

## 2.1.1 ルートディレクトリのファイルの役割章

|ファイル|説明|
|---|---|
|composer.json|[composer](https://getcomposer.org/) の構成管理ファイルです。composer自体については本コンテンツではフォローしませんので、外部のコンテンツを参照してください。|
|composer.lock|composer.jsonの記述に従い、実際にインストールされた各ライブラリのバージョン等の情報を持っています。このファイルをバージョン管理することで、どの環境でも同一の構成を復元できることが保証されます。|

---

|ファイル|説明|
|---|---|
|LICENSE|ライセンスファイル。|
|load.environment.php|composerの [autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) から参照されるファイルです。`.env` をロードするために利用されています。|
|phpuint.xml.dist|[PHPUnit](https://phpunit.readthedocs.io/ja/latest/organizing-tests.html) の構成ファイルです。PHPUnit自体についてはは本コンテンツではフォローしませんので、外部のコンテンツを参照してください。|
|README.md|このアプリケーションのためのREADMEファイルです。有益な情報が多く含まれているので、一度目を通しましょう。|

---

|ファイル|説明|
|---|---|
|.editorconfig|[editorconfig](https://editorconfig.org/) のフォーマットファイルです。コードの品質を保つためにeditconfigをサポートしているエディタ環境を利用しましょう。|
|.env.example|[dotenv(.env)](https://github.com/vlucas/phpdotenv)のサンプルファイル。|
|.gitignore|gitでバージョン管理の対象外とするファイルを定義した[設定ファイル](https://git-scm.com/docs/gitignore)|
|.gitattributes|gitでファイルを管理する際の改行コードなどの[設定ファイル](https://git-scm.com/docs/gitattributes)|
|.travis.yml|[Travis CI](https://travis-ci.org/) の設定ファイル。デフォルトでは `system` モジュールの一部のテストが実行されるようになっています。|


---

## 2.1.2 ディレクトリの役割

|ディレクトリ|説明|
|---|---|
|config|Drupalの[Configration Manament](https://www.drupal.org/docs/8/configuration-management) 機能で管理される設定ファイル(yml)を格納するディレクトリです。|
|drush|[drush](https://www.drush.org/) の設定ファイルが含まれるディレクトリ。|
|scripts|composerの [autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) から参照されるスクリプトファイルが含まれています。スクリプトでは、Drupalが稼働する上で必須となるディレクトリのチェックおよび生成や、依存するツールのバージョンのチェック等が行われます。　|

---

|ディレクトリ|説明|
|---|---|
|vendor|composerによってインストールされるサードパーティのライブラリが格納されるディレクトリです。|
|web|Drupalのコア及びcontiribute module/themeが格納されるディレクトリです。|

---

## 2.1.3 webディレクトリ

webディレクトリには多数のディレクトリやファイルが含まれていますので、代表的なものだけ紹介します。

---

|ディレクトリ|説明|
|---|---|
|core|Drupalのコアのソースコードが格納されるディレクトリです。|
|modules|このディレクトリには、composerでインストールしたcontribute moduleや、特定のプロジェクト向けに開発したカスタムモジュールが格納されます。コアで管理されている標準のモジュールはここではなく `core/modules` 以下に格納される点に注意してください。|

---

|ディレクトリ|説明|
|---|---|
|profiles|Drupalのインストールプロファイルが格納されるディレクトリです。|
|sites|サイト毎の設定ファイルや、アップロードされたコンテンツファイル等が格納されるディレクトリです。コンテンツファイルの保存先は設定で変更可能なため、このディレクトリ外に配置される可能性もあることに注意してください。|

---

|ディレクトリ|説明|
|---|---|
|themes|このディレクトリには、composerでインストールしたcontribute themeや、特定のプロジェクト向けに開発したカスタムテーマが格納されます。コアで管理されている標準のテーマはここではなく `core/themes` 以下に格納される点に注意してください。|

---

|ファイル|説明|
|---|---|
|index.php|全てのDrupalへのアクセスのエントリポイントになるスクリプト。|
|install.php|ブラウザからDrupalをウィザード形式でインストールするためのスクリプト。 `{domain}/install.php` にアクセスすると実行されます。|
|update.php|ブラウザからDrupalのアップデート(の一部)をウィザード形式で実行するためのスクリプト。 `{domain}/update.php` にアクセスすると実行されます。|

---

## まとめ　

このセクションでは、Drupalのディレクトリ構成について説明しました。

ルートディレクトリにあるファイルだけを見ても、様々なOSSやサービスに依存している事がわかります。他のフレームワーク(例えばRuby on Rails)と比べ、Drupalは最新の技術やOSSをすぐに取り込む文化ではないので、それほど目新しいものは出てこなかったと思います。

このセクションでリンクしているOSSやツールに対して理解が不足していると感じる場合は無理に進まず、まずは依存している周辺の技術を把握しておきましょう。
