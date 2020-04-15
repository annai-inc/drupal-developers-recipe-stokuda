---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.17 ルートサブスクライバー

---

前のセクションでは、イベントサブスクライバーを実装してDrupalの検索機能をGoogleにリダイレクトするようにしました。

しかし、`KernelEvents::REQUEST` に対してサブスクライバーを登録しているため、全てのリクエスト毎に処理が動いてしまいます。

性能要件などがシビアなプロダクトでは、この実装は問題になる可能性もあります。

---

既存のルートに対して単にリダイレクトを行いたい場合や、チェックされる権限だけを変更したいような場合、[RouteSubscriber](https://www.drupal.org/docs/8/api/routing-system/altering-existing-routes-and-adding-new-routes-based-on-dynamic-ones) を実装したほうが効率が良いケースもあります。

このセクションでは、RouteSubscriberの実装方法を解説します。

----

<!-- _class: lead -->
# 2.17.1 イベントサブスクライバーとルートサブスクライバーの違い

----

まず、イベントサブスクライバーとルートサブスクライバーの違いを把握しておきましょう。

ルートサブスクライバーは「**特定のイベントのみを受信できるイベントサブスクライバー**」です。

具体的には、Drupalで独自に定義されている `routing.route_alter` というイベントのみを受信できます。

※正確には他のイベントも受信できるようにoverrideは可能ですが、コードの可読性が低下するのでオススメはしません。

---

このイベントのペイロードには、Drupal全体のルーティング情報が渡されます。

これを書き換えることにより、あるルートに対して処理を行うクラスを変更したり、チェックされる権限を変更したりすることができます。

つまり、`*.routing.yml` の内容を変更できるということになります。

---

<!-- _class: lead -->
# 2.17.2 ルートサブスクライバーの実装

---

サンプルとして、2.3章でアクセスしたヘルプページ (/admin/help) のルート情報を変更してみましょう。

Drupalのルート情報は、DrupalConsoleの `debug:router` サブコマンドで確認することができます。 `/admin/help` のルート情報を確認してみましょう。

```txt
$ vendor/bin/drupal debug:router
 Route name                                                                         Path
 ...

 help.main                                                                          /admin/help
 help.page                                                                          /admin/help/{name}
 ...
```

---

ルート名は `help.main` であることが分かります。詳細を見てみましょう。

```txt
$ vendor/bin/drupal debug:router help.main
 Route           help.main
 Path            /admin/help
 Defaults
  _controller    \Drupal\help\Controller\HelpController::helpMain
  _title         Help
 Requirements
  _permission    access administration pages
 Options
  compiler_class Drupal\Core\Routing\RouteCompiler
  utf8           1
  _admin_route   1
  _access_checks access_check.permission
```

---

`/admin/help`へのアクセスには `access administration pages` (日本語だと「管理ページとヘルプを利用」)の権限が必要であることが分かります。

この権限チェックをバイパスするようにルートサブスクライバーを実装してみましょう。

イベントサブスクライバーと同様に、ルートサブスクライバーもサービスとして実装する必要があります。まずは `hello_world.routing.yml` に次のコードを追加してください。

---

```yml
  hello_world.route_subscriber:
    class: Drupal\hello_world\Routing\HelloWorldRouteSubscriber
    tags:
      - { name: event_subscriber }
```

ルートサブスクライバーも、実体はイベントサブスクライバーです。そのため、`tags` に `event_subscriber` を設定する必要があります。

前のセクションの内容がしっかりと身についていれば、特に新しい要素はありませんね。

それでは `class` で指定したクラスのコードを次のように実装してください。

---

```php
<?php

namespace Drupal\hello_world\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * An example of the RouteSubscriber.
 *
 * Bypass access check on the 'help.main' route.
 */
class HelloWorldRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('help.main')) {
      $route->setRequirements(['_access' => 'TRUE']);
    }
  }

}

```

---

TBD(parent classを含めたコードの解説)

---

## まとめ

このセクションでは、ルートサブスクライバーによるリダイレクトの実装について解説しました。

TBD.

---

## ストレッチゴール

TBD.
