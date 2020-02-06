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

フレームワーク自体に機能がない場合、ビジネスやプロジェクトの都合に合わせて使うライブラリを選択できる柔軟性がある半面、以下のような問題もあります。

- 採用したライブラリが活発にメンテナンスされなくなる、フレームワークのメジャーアップデートにすぐに(もしくは永久に)対応しない場合がある
- マイナーなライブラリを選択すると技術情報が少ない
- 複数のライブラリを組み合わせる場合、組み合わせて動くかどうかの検証が必要になる
- UI/UXが統一されず、場合によっては独自に作り込む必要がある

---

ちなみにRuby on Railsのユーザー、パーミッション、ロールは個別のライブラリに分割されているものが多く、1.x〜3.xくらいの世代ではライブラリ自体の隆盛も変化が早かったため、Railsのメジャーアップデートの度に苦労した覚えがあります。

フレームワーク自体でRBACの機能が提供されていると、このような（ビジネスとしてはあまり本質的ではない)問題が発生しないのは大きなメリットです。

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

ymlのトップレベル要素、つまり `show hello message` が権限の内部名称になります。権限には `title` キーが必ず必要です。

---

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

また、`rectrict access` キーを `true` に設定すると、管理UI上に「この権限は信頼できるロールにのみ付与してください」という趣旨の警告メッセージが追加されます。

付与することによってアクセスをバイパスするような強い権限を定義する場合は、このキーを設定してください。

---

<!-- _class: lead -->
## ルートに対して静的に権限のチェックを行う

---

さて、権限が定義できたところで `/hello` のルートにアクセスした時に、 `show hello message` 権限を持っているかどうかチェックするようにしましょう。

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

それでは、`AUTHENTICATED USER` ロールに `show hello message` 権限を付与してください。その後、以下の2点の動作を確認しましょう。
- (2.4章で作成した) user1でログインし `/hello` にアクセスするとメッセージが表示されること
- ログアウトした状態で `/hello` にアクセスするとアクセスが拒否されること

---

複数の権限を持っているかチェックしたい場合は、 `_permissions` に複数の権限を設定し、 `,` か `+` で区切ります。

複数の権限をANDでチェックする例
```yml
  requirements:
    # 'show hello message' と `use advanced search' の両方の権限があればアクセスを許可
    _permission: 'show hello message,use advanced search'
```

複数の権限をORでチェックする例
```yml
  requirements:
    # 'show hello message' と `use advanced search' のどちらかの権限があればアクセスを許可
    _permission: 'show hello message+use advanced search'
```

---

`AUTHENTICATED USER` ロールに付与する権限を以下4つのパターンに従って変更し、「複数の権限をANDでチェックする例」、「複数の権限をORでチェックする例」が期待通り動くかどうか確認してみてください。

|show hello message|use advanced search|
|--|--|
|OFF|OFF|
|OFF|ON|
|ON|OFF|
|ON|ON|

---

なお、 `,` と `+` の前後にスペースを含めると権限の名称が正しく認識されないので注意が必要です。

詳細は [Structure of routes](https://www.drupal.org/docs/8/api/routing-system/structure-of-routes) を参照してください。

---

<!-- _class: lead -->
## ルートに対してロールのチェックを行う

---

今度は権限ではなくロールでチェックしてみましょう。ロールでのチェックも権限のチェックとほぼ同じです。

 `{module_name}.routing.yml` の `requirements` キーの子要素に `_role` を定義し、値にロールの内部名称を設定することで、ルートにアクセスした際に権限があるかどうかがチェックされます。

---

`hello_world.routing.yml` の `hello_world.say_something` ルートの定義を以下の様に変更してください。この変更により、 `/say_something/{message}` にアクセスするためには `administrator` ロールが必要になります。

```yml
hello_world.say_something:
  path: '/say_something/{message}'
  defaults:
    _controller: '\Drupal\hello_world\Controller\HelloWorldController::saySomething'
    _title: 'Say Something!'
  requirements:
    _role: 'administrator'
```

---

それでは、次の動作を確認しましょう。

- 管理者ユーザーでログインし、 `/say_something/{message}'` にアクセスするとメッセージが表示されること
- user1でログインし `/say_something/{message}'` にアクセスするとアクセスが拒否されること

複数のロールでチェックしたい場合は、 `_role` に複数のロールを設定し、 `,` か `+` で区切ります。これも権限でチェックする場合と同様です。

---

TBD

---


## まとめ

TBD

---

## ストレッチゴール

TBD