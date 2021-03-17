---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 3.9 Template suggestionとTheme hook

---

ここまでのセクションでは「テーマはこのようなルールで動きます」という前提を元に、実際に手を動かしてテーマを作ってきました。

このセクションでは、適用されるテンプレートとテーマのフックがどのように決定されるかについて、コアのコードの実装を見ていきます。

---

<!-- _class: lead -->
## 3.9.1 全体の流れ

---

それでは、まずは全体の流れから見ていきましょう。ここまでのセクションで次のことを学んでいます。

---

- Drupalは [MainContentViewSubscriber::onViewRenderArray](https://github.com/drupal/drupal/blob/8.8.x/core/lib/Drupal/Core/EventSubscriber/MainContentViewSubscriber.php#L78) を SymfonyのRender Pipelineの `Kernel.view` に対するイベントサブスクライバーとして登録することでレスポンスを返す
- このイベントサブスクライバーが `MainContentRendererInterface::renderResponse` を呼び出して[Response](https://symfony.com/doc/current/components/http_foundation.html#response) オブジェクトを生成する
- テンプレートに変数を渡すためのPreprocessは、特定の名前の規則で実装されたグローバル関数であり優先度順に全て実行される
- テンプレートファイルの候補は複数あり、その中から一番優先度が高いものが利用される

---

これがコードレベルでどのような実装になっているかを追っていきましょう。

まずはDrupalConsoleの `debug:event` サブコマンドで `kernel.view` に登録されているイベントサブスクライバーを確認します。

```txt
$ vendor/bin/drupal debug:event kernel.view
 ---------------------------------------------------------- ---------------------- 
  Class                                                      Method                
 ---------------------------------------------------------- ---------------------- 
  Drupal\Core\Form\EventSubscriber\FormAjaxSubscriber        onView: 1             
  Drupal\Core\EventSubscriber\PsrResponseSubscriber          onKernelView: 0       
  Drupal\Core\EventSubscriber\MainContentViewSubscriber      onViewRenderArray: 0  
  Drupal\Core\EventSubscriber\RenderArrayNonHtmlSubscriber   onRespond: -10        
 ---------------------------------------------------------- ---------------------- 
```

---

[MainContentViewSubscriber::onViewRenderArray](https://github.com/drupal/drupal/blob/8.8.x/core/lib/Drupal/Core/EventSubscriber/MainContentViewSubscriber.php#L78) というメソッドが登録されていますね。このメソッドのコードの一部を見てみましょう。

---

```php
  /**
   * Sets a response given a (main content) render array.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
   *   The event to process.
   */
  public function onViewRenderArray(GetResponseForControllerResultEvent $event) {
    $request = $event->getRequest();
    $result = $event->getControllerResult();

    // Render the controller result into a response if it's a render array.
    if (is_array($result) && ($request->query->has(static::WRAPPER_FORMAT) || $request->getRequestFormat() == 'html')) {
      $wrapper = $request->query->get(static::WRAPPER_FORMAT, 'html');

      // Fall back to HTML if the requested wrapper envelope is not available.
      $wrapper = isset($this->mainContentRenderers[$wrapper]) ? $wrapper : 'html';

      $renderer = $this->classResolver->getInstanceFromDefinition($this->mainContentRenderers[$wrapper]);
      $response = $renderer->renderResponse($result, $request, $this->routeMatch);
      // The main content render array is rendered into a different Response
      // object, depending on the specified wrapper format.
      if ($response instanceof CacheableResponseInterface) {
        $main_content_view_subscriber_cacheability = (new CacheableMetadata())->setCacheContexts(['url.query_args:' . static::WRAPPER_FORMAT]);
        $response->addCacheableDependency($main_content_view_subscriber_cacheability);
      }
      $event->setResponse($response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::VIEW][] = ['onViewRenderArray'];

    return $events;
  }
```

---

2.16章でモジュールにイベントサブスクライバーを実装しましたが、テーマでも全く同じ仕組みを利用していることが分かりますね。

`onViewRenderArray()` メソッドの中身を見ると、レンダラーのインスタンスを作成し、 `renderResponse()` メソッドを呼び出して `Response` オブジェクトを生成しています。

レンダラーのインスタンスは `$this->mainContentRenderers` の値を元にクラスが決定されるようです。

2.16章で学んだ通り、イベントハンドラー(サービス)のコンストラクタの引数は、DIの機能を使って `*.services.yml` で指定した値が注入されます。

---

`MainContentViewSubscriber` を利用しているクラスの定義を確認すると、`$this->mainContentRenderers` には `%main_content_renderers%` が設定されることが分かります。

```txt
$ grep -rn MainContentViewSubscriber -A3 -B3 web/core/core.services.yml
1084-
1085-  # Main content view subscriber plus the renderers it uses.
1086-  main_content_view_subscriber:
1087:    class: Drupal\Core\EventSubscriber\MainContentViewSubscriber
1088-    arguments: ['@class_resolver', '@current_route_match', '%main_content_renderers%']
1089-    tags:
1090-      - { name: event_subscriber }
```

---

`%main_content_renderers%` は `MainContentRenderersPass` というクラスで設定されているようです。

```txt
$ grep -rn main_content_renderers web
web/core/core.services.yml:1088:    arguments: ['@class_resolver', '@current_route_match', '%main_content_renderers%']
web/core/lib/Drupal/Core/EventSubscriber/MainContentViewSubscriber.php:63:   * @param array $main_content_renderers
web/core/lib/Drupal/Core/EventSubscriber/MainContentViewSubscriber.php:66:  public function __construct(ClassResolverInterface $class_resolver, RouteMatchInterface $route_match, array $main_content_renderers) {
web/core/lib/Drupal/Core/EventSubscriber/MainContentViewSubscriber.php:69:    $this->mainContentRenderers = $main_content_renderers;
web/core/lib/Drupal/Core/Render/MainContent/MainContentRenderersPass.php:9: * Adds main_content_renderers parameter to the container.
web/core/lib/Drupal/Core/Render/MainContent/MainContentRenderersPass.php:17:   * main_content_renderers parameter, keyed by format.
web/core/lib/Drupal/Core/Render/MainContent/MainContentRenderersPass.php:20:    $main_content_renderers = [];
web/core/lib/Drupal/Core/Render/MainContent/MainContentRenderersPass.php:24:        $main_content_renderers[$format] = $id;
web/core/lib/Drupal/Core/Render/MainContent/MainContentRenderersPass.php:27:    $container->setParameter('main_content_renderers', $main_content_renderers);
```

このコードも見てみましょう。

---

```php
<?php

namespace Drupal\Core\Render\MainContent;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds main_content_renderers parameter to the container.
 */
class MainContentRenderersPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   *
   * Collects the available main content renderer service IDs into the
   * main_content_renderers parameter, keyed by format.
   */
  public function process(ContainerBuilder $container) {
    $main_content_renderers = [];
    foreach ($container->findTaggedServiceIds('render.main_content_renderer') as $id => $attributes_list) {
      foreach ($attributes_list as $attributes) {
        $format = $attributes['format'];
        $main_content_renderers[$format] = $id;
      }
    }
    $container->setParameter('main_content_renderers', $main_content_renderers);
  }

}
```

---

`render.main_content_renderer` というタグが付いたサービスが複数あり、リクエストのフォーマット毎にレンダラーが決まるようです。このタグが付いたサービスを検索してみましょう。

```txt
vendor/bin/drupal debug:container --tag=render.main_content_renderer
 Service ID                           Class Name                                       
 main_content_renderer.ajax           Drupal\Core\Render\MainContent\AjaxRenderer      
 main_content_renderer.dialog         Drupal\Core\Render\MainContent\DialogRenderer    
 main_content_renderer.html           Drupal\Core\Render\MainContent\HtmlRenderer      
 main_content_renderer.modal          Drupal\Core\Render\MainContent\ModalRenderer     
 main_content_renderer.off_canvas     Drupal\Core\Render\MainContent\OffCanvasRenderer 
 main_content_renderer.off_canvas_top Drupal\Core\Render\MainContent\OffCanvasRenderer
```

3.1章で解説した通り、リクエストのフォーマットがHTMLの場合は[HtmlRenderer::rederResponse](https://github.com/drupal/drupal/blob/8.8.x/core/lib/Drupal/Core/Render/MainContent/HtmlRenderer.php#L116) がレスポンスを生成することがコードからも確認できましたね。次はこのメソッドのコードを見てみます。

---

```php
  /**
   * {@inheritdoc}
   *
   * The entire HTML: takes a #type 'page' and wraps it in a #type 'html'.
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match) {
    list($page, $title) = $this->prepare($main_content, $request, $route_match);

    if (!isset($page['#type']) || $page['#type'] !== 'page') {
      throw new \LogicException('Must be #type page');
    }

    $page['#title'] = $title;

    // Now render the rendered page.html.twig template inside the html.html.twig
    // template, and use the bubbled #attached metadata from $page to ensure we
    // load all attached assets.
    $html = [
      '#type' => 'html',
      'page' => $page,
    ];

    // The special page regions will appear directly in html.html.twig, not in
    // page.html.twig, hence addmain_c them here, just before rendering html.html.twig.
    $this->buildPageTopAndBottom($html);

    // Render, but don't replace placeholders yet, because that happens later in
    // the render pipeline. To not replace placeholders yet, we use
    // RendererInterface::render() instead of RendererInterface::renderRoot().
    // @see \Drupal\Core\Render\HtmlResponseAttachmentsProcessor.
    $render_context = new RenderContext();
    $this->renderer->executeInRenderContext($render_context, function () use (&$html) {
      // RendererInterface::render() renders the $html render array and updates
      // it in place. We don't care about the return value (which is just
      // $html['#markup']), but about the resulting render array.
      // @todo Simplify this when https://www.drupal.org/node/2495001 lands.
      $this->renderer->render($html);
    });
    // RendererInterface::render() always causes bubbleable metadata to be
    // stored in the render context, no need to check it conditionally.
    $bubbleable_metadata = $render_context->pop();
    $bubbleable_metadata->applyTo($html);
    $content = $this->renderCache->getCacheableRenderArray($html);

    // Also associate the required cache contexts.
    // (Because we use ::render() above and not ::renderRoot(), we manually must
    // ensure the HTML response varies by the required cache contexts.)
    $content['#cache']['contexts'] = Cache::mergeContexts($content['#cache']['contexts'], $this->rendererConfig['required_cache_contexts']);

    // Also associate the "rendered" cache tag. This allows us to invalidate the
    // entire render cache, regardless of the cache bin.
    $content['#cache']['tags'][] = 'rendered';

    $response = new HtmlResponse($content, 200, [
      'Content-Type' => 'text/html; charset=UTF-8',
    ]);

    return $response;
  }
```

---

`$this->renderer->render($html);` で更に別のクラスのインスタンスにレンダリングを委譲していることが分かります。

前のページではコードの掲載は省略していますが、先ほどと同様に `$this->renderer` はこのクラスのコンストラクタでDIの機能を使って `*.services.yml` で指定した値が注入されます。

何の値が注入されているか見てみましょう。

```txt
$ grep -A3 -B1 HtmlRenderer web/core/core.services.yml
  main_content_renderer.html:
    class: Drupal\Core\Render\MainContent\HtmlRenderer
    arguments: ['@title_resolver', '@plugin.manager.display_variant', '@event_dispatcher', '@module_handler', '@renderer', '@render_cache', '%renderer.config%']
    tags:
      - { name: render.main_content_renderer, format: html }
```

---

2.16章で学んだように、 `arguments` の `@***` は `***` という別のサービスへの依存です。

`renderder` サービスの定義を見てみましょう。

```txt
$ grep -A2 -E "\s+renderer:$" web/core/core.services.yml
  renderer:
    class: Drupal\Core\Render\Renderer
    arguments: ['@controller_resolver', '@theme.manager', '@plugin.manager.element_info', '@render_placeholder_generator', '@render_cache', '@request_stack', '%renderer.config%']
```

`Drupal\Core\Render\Renderer` が指定されています。

---

ここまでのコードの調査から、最終的なレンダリングは `renderer` というサービスに委譲されており、実行されるメソッドは [Drupal\Core\Render\Renderer::render](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Render/Renderer.php#L187) であることが分かりました。

「コードではなくドキュメントを見ればいいのでは？」と思われたかもしれません。残念な事にこのレベルの細かいドキュメントは存在しないか、存在してもメンテナンスがされていないケースも多いです。

また、フレームワークの仕組み的に利用するサービスは差し替えることもできます。そのため「Drupal 8であれば、これを処理するのはこのクラスのこのメソッドだ」という決まった答えは厳粛には存在しません。

---

決まった答えを覚えるよりも、答えに至るための材料となる知識と思考方法を鍛える方がより有益です。

そのために、少し詳細にコードを調査する流れを説明していきました。同様の方法で、テーマだけではなくコアやモジュールのコードの流れも追うことができます。

今回のような「実際に動くクラスやメソッドを特定したい」という例だと、デバッガを利用してブレークポイントで処理を停止し、変数の値を確認するという方法もあります(2.5章を参照)。ただし、この場合は「値が固定なのか可変なのか」も意識するようにしましょう。

---

<!-- _class: lead -->
## 3.9.2 Theme Manager

---

[Drupal\Core\Render\Renderer::render](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Render/Renderer.php#L187) から先のコードを更に見てみましょう。

このメソッドは同じクラスの [doRender()](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Render/Renderer.php#L200) を呼び出しているだけですね。

---

```php
  /**
   * {@inheritdoc}
   */
  public function render(&$elements, $is_root_call = FALSE) {
    // Since #pre_render, #post_render, #lazy_builder callbacks and theme
    // functions or templates may be used for generating a render array's
    // content, and we might be rendering the main content for the page, it is
    // possible that any of them throw an exception that will cause a different
    // page to be rendered (e.g. throwing
    // \Symfony\Component\HttpKernel\Exception\NotFoundHttpException will cause
    // the 404 page to be rendered). That page might also use
    // Renderer::renderRoot() but if exceptions aren't caught here, it will be
    // impossible to call Renderer::renderRoot() again.
    // Hence, catch all exceptions, reset the isRenderingRoot property and
    // re-throw exceptions.
    try {
      return $this->doRender($elements, $is_root_call);
    }
    catch (\Exception $e) {
      // Mark the ::rootRender() call finished due to this exception & re-throw.
      $this->isRenderingRoot = FALSE;
      throw $e;
    }
  }
```

---

[doRender](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Render/Renderer.php#L212) メソッドを見てみましょう。

このメソッドで行われる処理はキャッシュや遅延ロードの設定など多岐に渡るので、全体の流れを把握するためのポイントのみに解説を絞ります。

ポイントとなる部分は [L426-458](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Render/Renderer.php#L426) です。

---

```php
    // Call the element's #theme function if it is set. Then any children of the
    // element have to be rendered there. If the internal #render_children
    // property is set, do not call the #theme function to prevent infinite
    // recursion.
    if ($theme_is_implemented && !isset($elements['#render_children'])) {
      $elements['#children'] = $this->theme->render($elements['#theme'], $elements);

      // If ThemeManagerInterface::render() returns FALSE this means that the
      // hook in #theme was not found in the registry and so we need to update
      // our flag accordingly. This is common for theme suggestions.
      $theme_is_implemented = ($elements['#children'] !== FALSE);
    }

    // If #theme is not implemented or #render_children is set and the element
    // has an empty #children attribute, render the children now. This is the
    // same process as Renderer::render() but is inlined for speed.
    if ((!$theme_is_implemented || isset($elements['#render_children'])) && empty($elements['#children'])) {
      foreach ($children as $key) {
        $elements['#children'] .= $this->doRender($elements[$key]);
      }
      $elements['#children'] = Markup::create($elements['#children']);
    }

    // If #theme is not implemented and the element has raw #markup as a
    // fallback, prepend the content in #markup to #children. In this case
    // #children will contain whatever is provided by #pre_render prepended to
    // what is rendered recursively above. If #theme is implemented then it is
    // the responsibility of that theme implementation to render #markup if
    // required. Eventually #theme_wrappers will expect both #markup and
    // #children to be a single string as #children.
    if (!$theme_is_implemented && isset($elements['#markup'])) {
      $elements['#children'] = Markup::create($elements['#markup'] . $elements['#children']);
    }
```

---

この部分では次の処理が行われています。

- 1. テーマが実装されており(Render Arrayに `#theme` キーがある場合)、かつレンダリングすべき子要素を持っていない場合は `$this->theme->render` を呼び出してHTMLを生成する
- 2. テーマが実装されていない(Render Arrayに `#theme` キーがない場合)、またはレンダリングすべき子要素を持っている場合は `doRender` メソッドを再帰的に呼び出す
- 3. テーマが実装されておらず、Render Arrayに `#markup` キーがある場合、`Markup::create` を呼び出してHTMLを生成する
 
---

3章でブラックボックスとして利用していたRender Arrayの `#markup` キーはここの3.で利用されます。

3.1章で「レンダリングはRender Arrayのコンポーネントの階層毎に再帰的に行われる」と説明しましたが、これが2.の部分です。

残りの1.の部分を見てみましょう。例によって `*.services.yml` で注入される `$this->theme` のクラスをまず確認します。3.9.1章で調べた結果を見ると、 `@theme.manager` というサービスへの依存になっています。

---

```txt
$ grep -A4 -E "\s+theme.manager:$" web/core/core.services.yml
  theme.manager:
    class: Drupal\Core\Theme\ThemeManager
    arguments: ['@app.root', '@theme.negotiator', '@theme.initialization', '@module_handler']
    calls:
      - [setThemeRegistry, ['@theme.registry']
```

つまり、`$this->theme->render` で実行されるのは [ThemeManager::render](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Theme/ThemeManager.php#L130) ということになります。

---

このメソッドの中では以下が行われています。

- [Template Suggestionのフック呼び出し](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Theme/ThemeManager.php#L240
)
- [Preprocessのフック呼び出し](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Theme/ThemeManager.php#L287)

- [HTML文字列の生成](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Theme/ThemeManager.php#L384)

それぞれの処理について細かく見ていきましょう。

---

<!-- _class: lead -->
## 3.9.3 Template Suggestionのフック呼び出し

---

Template Suggestionのフックは、名前の通りデータを出力する際に利用するテンプレートの候補を定義するためのフックです。また、このフックには、他のテーマやモジュールのフックを上書きするためのalterフックも存在します。

[ThemeManager.php#L218-245](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Theme/ThemeManager.php#L218) で次のように対象のフックの定義と呼び出しがされています。

---

```php
    // Set base hook for later use. For example if '#theme' => 'node__article'
    // is called, we run hook_theme_suggestions_node_alter() rather than
    // hook_theme_suggestions_node__article_alter(), and also pass in the base
    // hook as the last parameter to the suggestions alter hooks.
    if (isset($info['base hook'])) {
      $base_theme_hook = $info['base hook'];
    }
    else {
      $base_theme_hook = $hook;
    }

    // Invoke hook_theme_suggestions_HOOK().
    $suggestions = $this->moduleHandler->invokeAll('theme_suggestions_' . $base_theme_hook, [$variables]);
    // If the theme implementation was invoked with a direct theme suggestion
    // like '#theme' => 'node__article', add it to the suggestions array before
    // invoking suggestion alter hooks.
    if (isset($info['base hook'])) {
      $suggestions[] = $hook;
    }

    // Invoke hook_theme_suggestions_alter() and
    // hook_theme_suggestions_HOOK_alter().
    $hooks = [
      'theme_suggestions',
      'theme_suggestions_' . $base_theme_hook,
    ];
    $this->moduleHandler->alter($hooks, $suggestions, $variables, $base_theme_hook);
    $this->alter($hooks, $suggestions, $variables, $base_theme_hook);
```

---

以下の順に3種類の異なるフックが順次実行され、最終的なテンプレートの候補が定義されることになります。

- 1. [hook_theme_suggestions_HOOK](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21theme.api.php/function/hook_theme_suggestions_HOOK/8.8.x)
- 2. [hook_theme_suggestions_alter](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21theme.api.php/function/hook_theme_suggestions_alter/8.8.x)
- 3. [hook_theme_suggestions_HOOK_alter](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21theme.api.php/function/hook_theme_suggestions_HOOK_alter/8.8.x)

※それぞれ、先のページに引用した [ThemeManager.php](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Theme/ThemeManager.php) のL230, L244, L245で実行されます。

---

テーマのフックもモジュールのフックと同様に次の設計思想になっていることが分かりますね。

- モジュールやテーマ毎にそれぞれが同じフックを実装して実行することができる (ThemeManager.php#L230)
- 既存のフックのデータを変更するためのalterフックが存在する (ThemeManager.php#L244)
- コンテキストが詳細に特定できるフックほど優先順位が高い (ThemeManager.php#L245)

---

このメソッドのコードでは `HOOK` の部分は `$base_theme_hook` の値が使われています。

この変数の値は次の2通りの方法で決定されます。

- 1. テーマレジストリのキーに `base hook` があればそれを利用
- 2. それ以外の場合は、Render Arrayの `#theme` キーの値を利用 (`page`, `node`, `block` など)

---

<!-- _class: lead -->
## 3.9.4 Preprocessのフック呼び出し

---

Preprocessフックの呼び出しの主要部分は、先のTemplate suggestionの処理の直後にある[ThemeManager.php#L267-289](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Theme/ThemeManager.php#L267) です。

---

```php
    // Invoke the variable preprocessors, if any.
    if (isset($info['base hook'])) {
      $base_hook = $info['base hook'];
      $base_hook_info = $theme_registry->get($base_hook);
      // Include files required by the base hook, since its variable
      // preprocessors might reside there.
      if (!empty($base_hook_info['includes'])) {
        foreach ($base_hook_info['includes'] as $include_file) {
          include_once $this->root . '/' . $include_file;
        }
      }
      if (isset($base_hook_info['preprocess functions'])) {
        // Set a variable for the 'theme_hook_suggestion'. This is used to
        // maintain backwards compatibility with template engines.
        $theme_hook_suggestion = $hook;
      }
    }
    if (isset($info['preprocess functions'])) {
      foreach ($info['preprocess functions'] as $preprocessor_function) {
        if (function_exists($preprocessor_function)) {
          $preprocessor_function($variables, $hook, $info);
        }
      }
      ...
```

---

最後のif文のブロックで、テーマレジストリの `preprocess functions` に登録されている複数のPreprocessフックが順に実行されます。

---

<!-- _class: lead -->
## 3.9.5 HTML文字列の生成

---

テーマレジストリの `function` というキーで明示的に上書きされない限り、HTML文字列の生成は [ThemeManager.php#L321](https://github.com/drupal/drupal/blob/8.8.0/core/lib/Drupal/Core/Theme/ThemeManager.php#L321) の通り [twig_render_template](twig_render_template()) で行われます。

この関数の実装を見ると、デバッグ機能を有効にした時にHTMLに含まれている `THEME HOOK` や `FILE NAME SUGGESTIONS` などが出力されていることが分かります。

逆に言えば、`function` というキーを設定してtwigを使わずにレンダリングすることも可能です(推奨はしません)。

---

## まとめ

このセクションでは、テーマがHTMLを出力する全体の流れと、Preprocessや適用されるテンプレートがどのように決定されるかについて、コアの実装を深く見ていきました。

繰り返しになりますが、手順で物事を理解したつもりになるのはとても大きなリスクです。

手順ではなくフレームワークの設計思想を理解し、実際の問題を解決する際に応用が効く知識が身につくように学習を積み重ねてください。そのために、必要に応じてフレームワーク側(Drupalだとコアやモジュール)のコードを読む習慣をつけましょう。
