---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.16 イベントサブスクライバーとリダイレクト

---

このセクションでは、イベントサブスクライバーによるリダイレクトの実装について解説します。

システムがあるリクエストを受け取った時に、別のページにリダイレクトするケースがよくあります。

条件が毎回同じであればアプリケーションではなくWebサーバーなどで実現することもできますが、条件が動的に変化する場合はアプリケーションで実装する必要があります。

---

Drupal 7では、`hook_init` という全てのリクエスト受信時にトリガーされるフックがありました。このフックで条件を判断し、`drupal_goto` をコールすることで動的なリダイレクトを実現するというのが、Drupal 7の典型的な実装例です。

Drupal 8では、このインターフェースは完全に撤廃されました。

その代わりに、Symfonyの [kernel.request](https://symfony.com/doc/current/components/http_kernel.html) イベントに対してイベントハンドラーを登録 (Subscribe)し、リダイレクトを実現することになります。

---

このセクションを読み進めるためには、Drupalがリエクストを受けてからレスポンスを返すまでの一連のシーケンスを把握しておく必要があります。

これは、「1.1.5 リクエストからレスポンスまでの流れ」ですでに解説しています。

忘れてしまった方は、先に進む前にもう一度読み返しておきましょう。

---

<!-- _class: lead -->
# 2.16.1 リダイレクト

---

まず、Drupal 8でリダイレクトをどのように実装するかを簡単に解説します。

「1.1.5 リクエストからレスポンスまでの流れ」ですでに解説している通り、Drupal 8の一連のライフライクルは、Symfony HttpFoundationコンポーネントの [Request](https://symfony.com/doc/current/components/http_foundation.html#request) を受け取り [Response](https://symfony.com/doc/current/components/http_foundation.html#response) を返すのが基本的な流れになります。

ここまでのセクションでは、コントローラーが返すレスポンスはDrupalの[Render Arrays](https://www.drupal.org/docs/8/api/render-api/render-array) として実装してきました。

コントローラーが返したRender Arraysは、最終的にDrupalコアによってResponseオブジェクトに変換されます。

---

しかし、もちろんRender Arraysではなく、コントローラーが直接Responseオブジェクトを返すこともできます。

例えば、以下の様に実装すると、`hello` という文字を含んだブランクページを表示することができます。

```php
return new \Symfony\Component\HttpFoundation\Response('hello');
```

この実装方法はDrupalのテーマレイヤーをバイパスするので、通常のユースケースでは使う必要性はありません。

では、なぜこの例を提示したかというと、コントローラーでリダイレクトを行う場合も設計としては全く同じだからです。

---

リダイレクトする場合は、単にResponseではなく [RedirectResponse](https://api.drupal.org/api/drupal/vendor%21symfony%21http-foundation%21RedirectResponse.php/class/RedirectResponse/) を返すだけです。

[RedirectResponse](https://github.com/symfony/http-foundation/blob/v4.4.7/RedirectResponse.php#L19) クラスのコードを見ると、Responseクラスのサブクラスであることが分かりますね。

例えば、`/node/1` にリダイレクトする場合は以下のようなコードになります。

```php
return new \Symfony\Component\HttpFoundation\RedirectResponse('node/1');
```

---

実際にプロダクトのコードを書く際は、RedirectResponseではなくRedirectResponseのサブクラスを使うほうが良いでしょう。

Drupalでは、よりセキュリティに考慮したリダイレクトを実現するために、以下のサブクラスが用意されています。
- [LocalRedirectResponse](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Routing/LocalRedirectResponse.php)
- [TrustedRedirectResponse](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Routing/TrustedRedirectResponse.php)

---

<!-- _class: lead -->
# 2.16.2 イベントサブスクライバー

TBD

---

## まとめ

TBD
