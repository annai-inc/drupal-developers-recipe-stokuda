---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.10 サービスの実装とDependecy Injection

---

本セクションでは、サービスやDIのインターフェースを使って、hello_worldモジュールの機能をモダンな設計に変更します。

ある程度経験がある開発者の方であればお気づきだったかもしれませんが、このセクションの解説を行うために、敢えてこれらの機能を使わずに実装を行ってきました。

このセクションで変更する全ての内容はリファクタリングであり、モジュールの振る舞いには何の変化もありません。しかし、セクションの最後にはコードの見通しが良くなり、メンテナンスや拡張性が格段に向上しているはずです。

---

このセクションでは、

1. コントローラーが持っているロジックを別クラスに切り出す
2. 切り出したクラスをサービス化してstaticに利用する
3. Dependecy Injectionを利用する

という3段階のリファクタリングを行っていきます。

---

<!-- _class: lead -->
## サービスを使う2つの方法

---

Drupalでサービスを利用する方法は大きく2つあります。

1つ目は、以下のようにstaticにサービスを取得する方法です。

```php
$service = \Drupal::service('hello_world.messenger');
```

この方法は、主に `.module` など外部からサービスへの依存を注入できない場合に利用します。

---

Drupalのコアだけでも非常に多数のサービスが実装されています。

これらのサービスの取得を簡単に行うために、 `\Drupal` クラスには `\Drupal::entityTypeManager()` など、主要なサービスを取得するための多数のヘルパーメソッドが用意されています。

[\Drupal クラスが提供するAPI](https://api.drupal.org/api/drupal/core%21lib%21Drupal.php/class/Drupal/) には一度目を通しておいてください。

---

もう一つの方法は、外部からサービスへの依存を注入する方法、いわゆるDIを使う方法です。

基本的に `.module`  (もしくは「サービスコンテナが初期化される前」にコードが実行される稀なケース)以外でサービスを利用する場合はこちらの方法を利用してください。

この手法ではコードの実装量は多少増えますが、それに見合った見返りが確実にあります。

特に、「テストを書く際に依存するサービスをコードを変更せずに差し替えられる」というメリットは実際は必須レベルで欲しくなる要素です。

---

<!-- _class: lead -->
## Step 1. コントローラーが持っているロジックを別クラスに切り出す

---

それでは、早速実装していきましょう。最初にコントローラーが持っているロジックを別クラスに切り出します。

まずは、ロジックが持つ機能のインターフェースを宣言しましょう。

`web/modules/custom/hello_world/EchoMessageServiceInterface.php` を以下のように実装してください。

---

```php
<?php

namespace Drupal\hello_world;

use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * A service interface the echo messages.
 */
interface EchoMessageServiceInterface {

  /**
   * Just say some message.
   *
   * @return string
   *   The hello message.
   */
  public function helloWorld();

  /**
   * Just say something genarated from arguments.
   *
   * @return string
   *   the message genarated from arguments.
   */
  public function saySomething(string $message);

  /**
   * Inspect user information.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   An object that, when cast to a string, returns the translated string.
   */
  public function inspectUser(AccountInterface $user);

  /**
   * Inspect node information.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   An object that, when cast to a string, returns the translated string.
   */
  public function inspectNode(NodeInterface $node);

}

```

---

ここでは、4つのメソッドをインターフェースとしました。
(`inspectNode` については 2.5章のストレッチゴールを参照)。

次にサービスの実装クラスである `HelloWorldMessenger.php` を追加します。

---

```php
<?php

namespace Drupal\hello_world\Service;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\hello_world\EchoMessageServiceInterface;
use Drupal\node\NodeInterface;

/**
 * A service that echo messages.
 */
class HelloWorldMessenger implements EchoMessageServiceInterface {

  use StringTranslationTrait;

  /**
   * Just say configured hello message.
   *
   * @inheritDoc
   */
  public function helloWorld() {
    return \Drupal::service('config.factory')->get('hello_world.settings')->get('hello_message');
  }

  /**
   * Just echo back message by from argument.
   *
   * @inheritDoc
   */
  public function saySomething(string $message) {
    return $message;
  }

  // (次のページへ続く)
```

---

```php
  // (前のページからの続き)

  /**
   * Inspect user information.
   *
   * @inheritDoc
   */
  public function inspectUser(AccountInterface $user) {
    if (\Drupal::moduleHandler()->moduleExists("devel")) {
      dpm($user);
    }

    return $this->t(
      "User id: %user_id, username: %user_name",
      ["%user_id" => $user->id(), '%user_name' => $user->getAccountName()]
    );
  }

  /**
   * Inspect node information.
   *
   * @inheritDoc
   */
  public function inspectNode(NodeInterface $node) {
    return $this->t(
      "Node id: %node_id, title: %title",
      ["%node_id" => $node->id(), '%title' => $node->getTitle()]
    );
  }

}

```

---

この時点ではこのクラスはサービスではありませんが、最終的にサービスにするためnamespaceやコメントに `Service` を含めています。

コントローラーの同名メソッドとの実装の違いは、文字列をarrayでラップして返すかどうかだけです。

最後に、`HelloWorldController` がこのサービスを使うように変更しましょう。

---

```php
<?php

namespace Drupal\hello_world\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\hello_world\Service\HelloWorldMessenger;
use Drupal\node\NodeInterface;
use Symfony\Component\Routing\Route;

/**
 * A example of custom controller.
 */
class HelloWorldController extends ControllerBase {

  /**
   * Just say a configured hello message.
   */
  public function helloWorld() {
    /** @var \Drupal\hello_world\EchoMessageServiceInterface $service */
    $service = new HelloWorldMessenger();

    return [
      "#markup" => $service->helloWorld(),
    ];
  }

  /**
   * Just say something by use param.
   */
  public function saySomething(string $message) {
    /** @var \Drupal\hello_world\EchoMessageServiceInterface $service */
    $service = new HelloWorldMessenger();

    return [
      "#markup" => $service->saySomething($message),
    ];
  }
```

---


```php
  /**
   * Inspect user information.
   */
  public function inspectUser(AccountInterface $user = NULL) {
    /** @var \Drupal\hello_world\EchoMessageServiceInterface $service */
    $service = new HelloWorldMessenger();

    return [
      "#markup" => $service->inspectUser($user),
    ];
  }

  /**
   * Inspect node information.
   */
  public function inspectNode(NodeInterface $node) {
    /** @var \Drupal\hello_world\EchoMessageServiceInterface $service */
    $service = new HelloWorldMessenger();

    return [
      "#markup" => $service->inspectNode($node),
    ];
  }
```

---

それでは、キャッシュをクリアして `/hello`, `say_something/{message}`, `/inspect_user/{user}`, `/inspect_node/{node}` にアクセスしてください。

今までと同じ振る舞いが維持されていればリファクタリングは成功です。

---

<!-- _class: lead -->
## Step 2. 切り出したクラスをサービス化してstaticに利用する

---

先ほどのコードは `HelloWorldMessenger` という具象クラスに依存しており、 `EchoMessageServiceInterface` を実装した他のクラスを使うように変更するには、コードを書き換える必要があります。

この問題を解決するために `hello_world.messenger` という名前で `HelloWorldMessenger` をサービスとして登録し、利用できるようにしていきます。

---

サービスの定義は `{module_name}.services.yml` というファイルで行います。`hello_world.services.yml` を以下の様に作成してください。

```yml
services:
  hello_world.messenger:
    class: '\Drupal\hello_world\Service\HelloWorldMessenger'
```

---

ルートレベルの要素は必ず `services` にする必要があります。

その子要素はサービス名です。ここでは `hello_world.messenger` としています。サービス名にモジュールの名称を必ずしも含める必要はありませんが、サービス名はシステム前提でユニークにする必要があります。

`class` にサービスの実装クラスを指定します。先のコードでは名前空間を `Service` で区切っていますが、サービスの名前空間に制約は特にありません。

Drupalの場合、「このクラスはサービスである」という宣言をするためのPHPのインターフェースは存在しないため、`Service` で名前空間を区切る実装例もあります。

---

最後に、`HelloWorldController` がこのサービスを使うように変更しましょう。

---

```php
  /**
   * Just say a configured hello message.
   */
  public function helloWorld() {
    /** @var \Drupal\hello_world\EchoMessageServiceInterface $service */
    $service = \Drupal::service('hello_world.messenger');
    return [
      "#markup" => $service->helloWorld(),
    ];
  }

  /**
   * Just say something by use param.
   */
  public function saySomething(string $message) {
    /** @var \Drupal\hello_world\EchoMessageServiceInterface $service */
    $service = \Drupal::service('hello_world.messenger');

    return [
      "#markup" => $service->saySomething($message),
    ];
  }

```

---

```php
  /**
   * Inspect user information.
   */
  public function inspectUser(AccountInterface $user = NULL) {
    /** @var \Drupal\hello_world\EchoMessageServiceInterface $service */
    $service = \Drupal::service('hello_world.messenger');

    return [
      "#markup" => $service->inspectUser($user),
    ];
  }

  /**
   * Inspect node information.
   */
  public function inspectNode(NodeInterface $node) {
    /** @var \Drupal\hello_world\EchoMessageServiceInterface $service */
    $service = \Drupal::service('hello_world.messenger');

    return [
      "#markup" => $service->inspectNode($node),
    ];
  }

```

---

`$services` 変数の代入の実装が変わっただけですね。

それでは、再度キャッシュをクリアして `/hello`, `say_something/{message}`, `/inspect_user/{user}`, `/inspect_node/{node}` にアクセスしてください。今までと同じ振る舞いが維持されていれば成功です。

最低限のサービスの実装はこれだけになります。意外と簡単でしたね。

---

<!-- _class: lead -->
## Step 3. サービスの確認方法

---

実際に開発をしていると、実装のミスでサービスが認識されなかったり、コアやモジュールでどんなサービスが提供されているか知りたい場合があります。

このような場合は、以降に紹介するいくつかの方法で、どのようなサービスが動いているか確認することができます。

---

### {module_name.services.yml} のコードを読む

サービスは `{module_name.services.yml}` で定義されているので、このコードを読むことでどんなサービスがあるか確認することができます。

また、先に解説したとおり `\Drupal` クラスには頻繁に利用するサービスを取得するためのヘルパーメソッドがあるので、このクラスのAPIを見てみることも有益です。

---

### develモジュールを有効にし、 /devel/container/service にアクセスする

develモジュールを有効にすると、管理UIからサービスの一覧を確認することができます。

さらにweb profilerモジュールを有効にすると、下部のリンクからの簡単にアクセスできるようになります。

---

先ほど実装した `hello_world.messenger` サービスが認識されているか確認してみましょう。web profilerを有効にして「コンテナ情報」(`/devel/container/service` へのリンク)をクリックしてください。

![service container menu](../assets/02_module_basics/10_service/service_container_list_menu.png)

---

以下のように独自に実装したサービスが認識されていることが分かります。

![service container list](../assets/02_module_basics/10_service/service_container_list.png)

---

### drush(CLI)で確認する

drushの `devel:services` サブコマンドでサービスの一覧を取得できます。しかし、情報量が少ないため、CLIから情報を取得する場合は次に紹介するDrupalConsoleを使う方法を推奨します。

```txt
$ vendor/bin/drush devel:services
- access_arguments_resolver_factory
- access_check.contact_personal
- access_check.cron
...
```

---

### DrupalConsole(CLI)で確認する

DrupalConsoleの `debug:containers` サブコマンドでサービスの一覧を取得できます。

```txt
$ vendor/bin/drupal debug:container
 Service ID                                                        Class Name                                                             
 access_arguments_resolver_factory                                 Drupal\Core\Access\AccessArgumentsResolverFactory                      
 access_check.contact_personal                                     Drupal\contact\Access\ContactPageAccess                                
 access_check.cron                                                 Drupal\system\Access\CronAccessCheck                                   
 access_check.csrf                                                 Drupal\Core\Access\CsrfAccessCheck
 ...
```

---

<!-- _class: lead -->
## Dependency Injection

---

## まとめ

TBD

---

## ストレッチゴール

TBD
