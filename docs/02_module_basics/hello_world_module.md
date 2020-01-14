---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 空モジュールの作成

---

## はじめに

Drupalのモジュールで実現できる機能は非常に多岐に渡ります。
また、Drupal 8以降は[Symfony](https://symfony.com/doc/4.4/configuration.html)やいくつかの[PSR](https://www.php-fig.org/psr/)の知識が必要となっており、Drupal自体の理解の前に覚えなくてはならない要素がたくさんあります。
[Exampleモジュール](https://www.drupal.org/project/examples)やDrupalConsoleでscaffolding(自動生成)されたコードは非常に包括的で参考になりますが、未経験者への情報量としては多すぎます。

そのため、まずは何も機能を持たないhello_worldモジュールを開発するところから始めましょう。

---

## hello_worldモジュールの開発

何も機能を持たないhello_worldモジュールを開発してみましょう。
TBD (先にmachine nameの説明やDrupalのディレクトリ構成の説明が必要)

|項目|設定値|
|---|---|
|マシン名|`hello_world`|
|モジュールのディレクトリ|`{site_root}/modules/custom/hello_world`|
|.info.yml ファイル名|`hello_world.info.yml`|

---

`hello_world.info.yml` の内容は以下の通りになります。

```yml
name: Hello World
type: module
core: 8.x
```

---

## .info.ymlのキー

TBD

---

## ...

TBD

## ...

TBD