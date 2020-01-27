---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.4 フックの実装 (2) - hook_form_alter

---

前のセクションでは、`hook_help` を実装してヘルプコンテンツを追加しました。

このセクションでは、もう少し実践的に既存のフォームの振る舞いを変更する機能を追加します。

---

## 既存のフォームの振る舞いを変更するフック: hook_form_alter

Drupalは主にCMSとして利用されているフレームワークです。
そのため、コアや他のモジュールが生成したフォームに対して、振る舞いを変更するためのインターフェースがデフォルトで提供されています。

これは [hook_form_alter](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Form%21form.api.php/function/hook_form_alter/8.8.x) というフックを実装することで実現できます。

このフックを実装することで、既存のフォームの項目を追加・削除したり、バリデーションを変更したりすることができます。

---

## hook_form_alterの実装

それでは、`hook_form_alter` の実装をしてみましょう。
今回は例としてユーザー登録フォームに、**「入力されたパスワードが8文字以上かどうかをチェックして、8文字未満の場合はエラーメッセージを表示する」** 機能を追加します。

---

まずは、ユーザー登録フォームの標準の動きを確認してみましょう。
`/admin/people/create` にアクセスしてユーザー登録フォームを表示してください。

![user register form 1](../assets/02_module_basics/user_register_form_1.png)

---

以下のように入力して、ユーザーを新規に登録してください(記載がない項目はデフォルト値のままでOKです)

|キー|値|
|---|---|
|Email address|`user1@localhost.localdomain`|
|Username|`user1`|
|Password|`a`|
|Confirm password|`a`|

---

登録に成功すると以下のようなメッセージが表示されます。

![user register form 2](../assets/02_module_basics/user_register_form_2.png)

---

「おや？」っと思われたかもしれませんが、実はDrupalのデフォルトではパスワードの文字数はチェックされません。[かなり昔から議論はされています](https://www.drupal.org/project/drupal/issues/1824800)が、なかなか標準の機能としては取り込まれていません。

この振る舞いはセキュリティ的にはかなり弱いですよね。この問題を解決するために `hook_form_alter` を実装して文字数をチェックしましょう。

---

`hook_form_alter` を実装するには、まず振る舞いを変更する対象のフォームのIDが必要になります。フォームのIDはもちろんフォームを生成しているコードから読み取ることもできますが、それよりもブラウザ上でDOMから確認したほうが簡単です。

`/admin/people/create` にアクセスしてフォームの `id` 属性を確認すると、 `user-register-form` になっていることが分かります。この `id` 属性の `-` を `_` に置き換えたものがフォームのIDになります。

今回の場合だと、フォームIDは `user_register_form` です。

---

...

---

## まとめ

...