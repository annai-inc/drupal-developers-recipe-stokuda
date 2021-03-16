---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.19 イベントのディスパッチ

---

これまでのセクションでは、イベントサブスクライバーやその派生系であるルートサブスクライバーを実装することで、発生したイベントを受信して任意の処理方法について解説してきました。

このセクションでは、逆に任意のイベントを発生させる方法を解説します。

---

<!-- _class: lead -->
# 2.19.1 イベントの定義

---

少し前に書いたコードを思い出してみましょう。

`HelloWorldMessenger::helloWorld` は、コンフィグで指定されたメッセージを表示する機能を提供しています。

独自のイベントを定義して、メッセージを表示する前に他のモジュールが文字列を上書きできるようにしてみましょう。

まず、イベントのクラスを次のように作成してください。

---

```
<?php

namespace Drupal\hello_world\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * An example of Event implementation.
 */
class HelloMessageEvent extends Event {
  const EVENT = 'hello_world.hello_message';

  /**
   * Hello message.
   *
   * @var string
   */
  protected $message;

  /**
   * Set the hello message.
   *
   * @param string $message
   *   Hello message.
   */
  public function setValue($message) {
    $this->message = $message;
  }

  /**
   * Get the hello message.
   *
   * @return string
   *   Hello message.
   */
  public function getValue() {
    return $this->message;
  }

}

```

---

名前空間のモジュール名の下位に `Event` を含めていますが、これは単にコードの可読性を上げるためです。イベントクラスには名前空間の制約は特にありません。

Drupalのイベントスシステムは、内部的にSymfonyの[EventDispatcher](https://symfony.com/doc/current/components/event_dispatcher.html) コンポーネントを利用しています。

そのため、独自のイベントクラスを定義する場合は、 `Symfony\Component\EventDispatcher\Event` から派生させる必要があります。

---

`EVENT` プロパティにはイベントを識別するためのイベント名を定義しています。イベント名には 詳細は [The EventDispatcher Component - Naming Convention](https://symfony.com/doc/current/components/event_dispatcher.html#naming-conventions) を参照してください。

`$message` プロパティがこのイベントのペイロードです。`getValue`、`setValue` メソッドでRWを行うインターフェースとしました。

このクラス自体には特に難しいところはありませんね。

---

<!-- _class: lead -->
# 2.19.2 イベントの起動

---

次に HelloWorldMessenger がこのイベントを起動するように変更します。

イベントの制御は `event_dispatcher` サービス経由で行います。

このサービスをDIで HelloWorldMessenger に注入して参照するようにしましょう。

まずは、DrupalConsoleの `debug:container` サブコマンドで、このサービスがどのクラスで実装されているかを調べます。

---

```txt
$ vendor/bin/drupal debug:container
 Service ID                                                        Class Name
 ...
 event_dispatcher                                                  Drupal\webprofiler\EventDispatcher\TraceableEventDispatcher
 ...
```

おや、何か違和感がありますね。`event_dispatcher` はコアとして提供されるべきサービスですが、実装クラスがコアには含まれていない webprofiler モジュールで提供されています。

これは、webprofilerが `event_dispatcher` サービスを[自身が実装するクラスに差し替える](https://gitlab.com/drupalcontrib/devel/-/blob/8.x-2.1/webprofiler/src/WebprofilerServiceProvider.php#L109) ためです。

---

ちなみに、webprofilerモジュールが無効な場合は `event_dispatcher` の実装クラスは以下のようになります。これは [core.services.yml](https://github.com/drupal/drupal/blob/8.8.0/core/core.services.yml#L704) で定義されています。


```txt
$ vendor/bin/drupal debug:container
 Service ID                                                        Class Name
 ...
 event_dispatcher                                                  Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
 ...
```

当たり前ですが、どちらのクラスも「同じインタフェースを提供する差し替え可能な機能」として提供されるため、どちらの場合でも正常に動作します。

---

どちらのクラスも、親を辿っていくと共通のインタフェースは `Symfony\Component\EventDispatcher\EventDispatcherInterface` であることが分かります。

つまり、以下のように実装を変更すれば良いことになります。

- `hello_world.messenger` サービスの `arguments` に `@event_disptcher` を追加する
- `HelloWorldMessenger` クラスのコンストラクタで引数として `EventDispatcherInterface` を受け取る

ここまでのセクションをしっかりと理解できていれば、新しい要素は何もないですね。

---

それでは、hello_world.messengerサービスの定義を次のように変更してください。

```yml
  hello_world.messenger:
    class: '\Drupal\hello_world\Service\HelloWorldMessenger'
    arguments: ['@config.factory', '@event_dispatcher']
```

最後に、`HelloWorldMessenger` クラスのコードを次のように変更してください。

---

```php
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// ...

class HelloWorldMessenger implements EchoMessageServiceInterface {
  // ...

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * HelloWorldMessenger constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory sevice.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EventDispatcherInterface $eventDispatcher) {
    $this->configFactory = $config_factory;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * Just say configured hello message.
   *
   * @inheritDoc
   */
  public function helloWorld() {
    /** @var string $default_message */
    $default_message = $this->configFactory->get('hello_world.settings')->get('hello_message');

    /** @var \Drupal\hello_world\Event\HelloMessageEvent $event */
    $event = new HelloMessageEvent();
    $event->setValue($default_message);
    $this->eventDispatcher->dispatch(HelloMessageEvent::EVENT, $event);
    return $event->getValue();
  }

```

---

helloWorldメソッドで、コンフィグに設定されたメッセージをイベントのペイロードとして設定し、`EventDispatcherInterface::dispatch` でイベントを起動しています。

これにより、このイベントを受信した他のモジュールがメッセージを変更する機会が与えられます。

---

動作確認のために、 `/hello` にアクセスしてください。このイベントを受信するモジュールはまだ存在しないので、今までと同じ振る舞いが維持されていればOKです。

---

## まとめ

このセクションでは、任意のイベントを発生させる方法を解説しました。

Drupal 8.8時点ではまだhookによる拡張のインターフェースも残っていますが、今後はEventによる拡張が主流になっていくと思われます。

先のセクションも含め、イベントの実装方法をしっかりと把握しておきましょう。

---

## ストレッチゴール

1. hello_message_override モジュールを新しく作成し、このセクションで定義したイベントを受信して表示されるメッセージが「Hello message override!」になるように変更してください。

2. bonjour_message_override モジュールを新しく作成し、このセクションで定義したイベントを受信して表示されるメッセージが 「Bonjour message override!」 になるように変更してください。なお、実装については以下の制約を満たすようにしてください。
   - hello_message_override モジュールと同時に有効化した場合でも、メッセージが「Bonjour message override!」となること
   - bonjour_message_override → hello_message_override の順にメソッドが実行されること
