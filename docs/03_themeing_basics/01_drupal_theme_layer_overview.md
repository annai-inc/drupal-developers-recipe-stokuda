---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 3.1 Drupalのテーマレイヤーの概要

---

このセクションでは、Drupalのテーマが全体の処理の流れでどの部分を担当するか、どのような機能を持つかなどの概要を解説します。

---

<!-- _class: lead -->
## 3.1.1 ビジネスロジックとプレゼンテーション

---

ビジネスロジックで生成したデータをどのように[プレゼンテーションレイヤー](https://en.wikipedia.org/wiki/Multitier_architecture)で出力するかは、Drupalに限らず多くのCMSやフレームワークで重要な要素の１つです。

一般的には、ビジネスロジックとプレゼンテーションレイヤーを分離し、お互いの責任範囲を明確にするとともに粗結合にするアーキテクチャが採用されます。

つまり、ビジネスロジックはデータの取扱だけに注力し、それがどのように出力されるかはケアしません。逆にプレゼンテーションレイヤーは、渡されたデータをどのように出力するかのみに注力します。

---

Drupalでは、他のCMSやWebアプリケーションでも採用されているように「テーマ」と呼ばれるプレゼンテーションレイヤーの機能を差し替えることで、システムのデザインを自由に変更することができます。

このセクションを通して、Drupalがデータをレンダリング（表示）する流れや、テーマを構成する要素を紹介します。

---

<!-- _class: lead -->
## 3.1.2 全体シーケンス (TBD)

@see https://www.drupal.org/docs/8/api/render-api/the-drupal-8-render-pipeline

---

<!-- _class: lead -->
## 3.1.3 Twig

---

Drupal 7では、テンプレートエンジンとしてPHPそのものが使われていました。

つまり、テンプレートは以下のように実装されます。

```php
<?php

print '<div class="wrapper">' . $data . '</div>';

?>
```

---

このサンプルコードの場合、`$data` という変数には、ビジネスロジックでアクセス制御を行い、なんらかの処理を実行した結果が格納されています。

しかし、テンプレート自身がPHPで実装されているため、下記のようにこの変数を無視してテンプレート側でDBにアクセスし、表示したいデータを取得するような実装もできてしまいます。

```php
<?php

$my_data = get_some_secret_data();
print '<div class="wrapper">' . $my_data . '</div>';

?>
```

---

もちろん、後述するtheme_hookのような「ビジネスロジックがテーマに変数を渡す仕組み」はDrupal 7の時点で提供されていました。しかし、これを使わずにテンプレートを魔改造する実装が一定の割合で存在するのが悲しい現実です。

これは、特にDrupal初学者やOSSを活用して開発する経験が少ない開発者が陥りがちな問題です。

このような各レイヤーの責任区分が曖昧で蜜結合な実装は、メンテナンスを困難にし、場合によってはセキュリティーの問題を発生させます。

---

Drupal 8では、テンプレートエンジンに[twig](https://twig.symfony.com/)が採用され、このような問題は発生しなくなりました。

先のテンプレートはtwigだと以下のようになります。

```twig
<div class="wrapper">{{ data }}</div>
```

twigではPHPは書けないため、テンプレートレイヤーではビジネスロジックから渡された変数のみで出力を生成することが強制されます。これはThemeingに関するDrupal 7からの大きな違いであり、制約であり、同時に大きなメリットでもあります。

---

<!-- _class: lead -->
## 3.1.4 Theme hooks

---

Drupalの機能を拡張する方法の１つとしてフックが利用できることを２章で学びました。テーマも同様にフックで拡張することができます。

TBD: もう少しアーキテクチャレベルの解説を追加する。

主な拡張ポイントは次の2つです。

- hook_theme: テンプレート名とそのテンプレートに渡す変数のメタデータを定義する
- [preprocess](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21theme.api.php/group/themeable#sec_preprocess_templates): テンプレートに渡す変数の値を設定する

---

ここで、例としてコアのuserモジュールのコードを見てみましょう。

`web/modules/user/user.module` には `hook_theme` の実装である `user_theme` が次のように実装されています。

---

```php
/**
 * Implements hook_theme().
 */
function user_theme() {
  return [
    'user' => [
      'render element' => 'elements',
    ],
    'username' => [
      'variables' => ['account' => NULL, 'attributes' => [], 'link_options' => []],
    ],
  ];
}
```

---

`hook_theme` が返す配列のキーは、テンプレート名になります。

つまり、フックでは `user.html.twig`、 `username.html.twig` という2つのテンプレートが定義されます。

先に `user` の方から見ていきましょう。


---

<!-- _class: lead -->
## 3.1.5

---

## まとめ

TBD