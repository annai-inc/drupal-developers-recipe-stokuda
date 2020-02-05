---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.7 権限の定義とアクセス制御

---

Drupalはコアの機能として [ロースベースのアクセス制御(RBAC)](https://en.wikipedia.org/wiki/Role-based_access_control) をサポートしています。

言葉にすると簡単ですが、これはDrupalを採用する上での非常に大きなメリットの一つです。

---

例えばRuby on RailsやLaravelではフレームワーク自体にはパーミッションやロールの機能がないため、外部のライブラリを組み合わせて実装する必要があります。

前者だと [Devise](https://github.com/heartcombo/devise)(ユーザー認証) + [cancancan](https://github.com/CanCanCommunity/cancancan)(パーミッション) + [rofiy](https://github.com/RolifyCommunity/rolify)(ロール)、後者だと [spatie/laravel-permission](https://github.com/spatie/laravel-permission) や [jeremykenedy/laravel-roles](https://github.com/jeremykenedy/laravel-roles) などのライブラリが多く利用されていると思います。

(Laravel詳しくなので詳しい方教えてください！)

---

フレームワーク自体に機能がない場合、ビジネスやプロジェクトの都合に合わせて使うライブラリを選択できるという柔軟性がある半面、以下のような問題もあります。

- 採用したライブラリが活発にメンテナンスされなくなる、フレームワークのメジャーアップデートにすぐに(もしくは永久に)対応しない場合がある
- マイナーなライブラリを選択すると技術情報が少ない
- 複数のライブラリを組み合わせる場合、組み合わせて動くかどうかの検証が必要になる
- 複数のライブラリを組み合わせる場合、UI/UXが統一されず場合によっては独自に作り込む必要がある

---

ちなみにRuby on Railsのユーザー、パーミッション、ロールは個別のライブラリに分割されているものが多く、1.x〜3.xくらいの世代ではライブラリ自体の隆盛も変化が早かったため、苦労した覚えがあります。

フレームワーク自体にRBACの基本的な機能が提供されていると、このような（ビジネス的にはあまり本質的ではない)問題が発生しないのは大きなメリットです。

---

このセクションでは、モジュールでの権限の定義やアクセス制御の実装について解説します。

Drupalのロールと権限についての基本的な理解がある前提となりますので、自信がない方は先に [【Drupal 8入門】モジュールのインストール、ユーザー作成と権限の設定](https://thinkit.co.jp/article/10082?page=0%2C2) を参照してください。

---

<!-- _class: lead -->
## 静的な権限の定義

---

権限を定義する場合は、 `{module_name}.permissions.yml` というファイルを新規に追加します。

`hello_world.permissions.yml` を次のように作成してください。

```yml
show hello message:
  title: 'Show hello message'
```

---

ymlのトップレベルが権限の内部名称になります。権限には `title` キーが必ず必要です。

それでは、 `/admin/people/permissions` にアクセスして定義した権限が認識されているか確認しましょう。以下のように `Show hello message` という権限が追加されていると思います。

![width:1100px](../assets/02_module_basics/07_permission_and_access_control/permissoin_show_hello_message_1.png)

---

`description` キーを設定すると、権限の説明を定義することができます。

`hello_world.permissions.yml` を次のように変更してください。

```yml
show hello message:
  title: 'Show hello message'
  description: 'Show hello message on /hello'
```

---

再度 `/admin/people/permissions` にアクセスすると、以下のように `description`  で定義した説明が表示されています。

![width:1100px](../assets/02_module_basics/07_permission_and_access_control/permissoin_show_hello_message_2.png)

---

また、ymlで `rectrict access` キーを `true` に設定すると、管理UI上に」その権限は信頼できるロールにのみ付与してください」という趣旨のメッセージが追加されます。

付与することによってアクセスをバイパスするような強い権限を定義する場合は、このキーを設定してください。

---

<!-- _class: lead -->
## ルートに対して静的に権限のチェックを行う

---

さて、権限が定義できたところで `/hello` のルートにアクセスると、 `show hello message` 権限を持っているかどうかチェックするように変更しましょう。

ルートに対しての静的な権限チェックは、 `{module_name}.routing.yml` のみで実現できます。

---

`hello_world.routing.yml` の `hello_world.hello` ルートの定義を以下の様に変更してください。

```yml
hello_world.hello:
  path: '/hello'
  defaults:
    _controller: '\Drupal\hello_world\Controller\HelloWorldController::helloWorld'
    _title: 'Hello World!'
  requirements:
    _permission: 'show hello message'
```

`requirements` キーの子要素に `_permission` を定義し、値に権限の内部名称を設定することで、ルートにアクセスした際に権限があるかどうかがチェックされます。

---

それでは、以下の2点を確認してください。
- 管理者ユーザーで `/hello` にアクセスするとメッセージが表示されること
- ログアウトした状態で `/hello` にアクセスするとアクセスが拒否されること

---

TBD

---

## まとめ

TBD

---

## ストレッチゴール

TBD