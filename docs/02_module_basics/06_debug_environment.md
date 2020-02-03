---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.6 デバッグ環境の構築

---

ここまでのセクションでhello_worldモジュールのコードもある程度の量になってきました。

そろそろ、コードを読み書きするためのデバッグ環境を整備していきましょう。

---

<!-- _class: lead -->
## コーディングスタンダード

---

### editorconfig

2.1章で説明したとおり、Drupalのコードには `.editorconfig` が含まれ ています (editorconfigって何だっけ？という方は2.1章を読み直しましょう)。

editorconfigでは、インデント幅や改行コードなどコードに関する基本的ないくつかのフォーマットを定義することができます。

Visual Studio CodeやVim、Emacsなどの主要なエディタや、PHPStormなどのIDEでサポートされていますので、利用するようにしてください。

---

### phpcsによるコーディング規約のチェック

Drupalのコーディングスタンダードのドキュメントは [Coding standards](https://www.drupal.org/docs/develop/standards) で公開されています。

これに対応する [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) のルールも [drupal/coder](https://packagist.org/packages/drupal/coder) というライブラリ名で packagistに公開されているので、これを使って自分の書いたコードがコーディングスタンダードに準拠しているか確認できるようにしましょう。

---

```sh
# "drupal/coder" をglobalにインストール
$ composer global requre drupal/coder

# phpcsコマンドにパスを通す
$ export PATH=$PATH:"$(composer -q global config data-dir)/$(composer -q global config bin-dir)"

# "drupal/coder が定義しているコーディングスタンダードをphpcsのコンフィグに追加
$ phpcs --config-set installed_paths \
  "$(composer -q global config data-dir)/$(composer -q global config vendor-dir)/drupal/coder/coder_sniffer"

$ phpcsがデフォルトでロードするコーディングスタンダードを設定
$ phpcs --config-set default_standard Drupal,DrupalPractice
```

---

それでは、hello_worldモジュールのコードをチェックしてみましょう。

```sh
$ phpcs web/modules/custom/hello_world -v
Registering sniffs in the Drupal standard... 
Registering sniffs in the DrupalPractice standard... 
DONE (151 sniffs registered)
Creating file list... DONE (1 files in queue)
...
Processing HelloWorldController.php [PHP => 306 tokens in 54 lines]... DONE in 23ms (0 errors, 0 warnings)

```

特に問題がなければ、上記のように `errors` と `warnings` が0件になります。もし、コーディングスタンダードに準拠していないコードが検出された場合は、先に進まずにここで直してしまいましょう。

---

なお、`phpcs` の代わりに `phpcbf` を使うとコードの修正までやってくれます。

ここでは手動でphpcsコマンドを実行しましたが、実際に使う場合はファイルを保存した時に自動的にチェックされるようにしたほうが簡単です。

先に名前を挙げたエディタやIDEを使えば実現できますので、自動化してコードの開発自体に集中できるようにしましょう。以下のリンクが参考になります。

[Installing Drupal Code Sniffer on Vim, Sublime Text, Visual Studio Code, Komodo, TextMate, Atom, Emacs & Geany](https://www.drupal.org/docs/8/modules/code-review-module/installing-drupal-code-sniffer-on-vim-sublime-text-visual-studio)

---

コードのフォーマットを「動作に関係ないから」という理由で軽視すると、どんどんメンテナンスコストが増大していきます。

正しいコードのフォーマットは、エンジニアのアウトプットやシステムの品質の一部です。しっかりと押さえておきましょう。

Drupalのコーディングスタンダードやdrupal/coderについて、もっと詳しく知りたい場合は以下を参照してください。

- [Coding standards](https://www.drupal.org/docs/develop/standards)
- [Installing Coder Sniffer](https://www.drupal.org/docs/8/modules/code-review-module/installing-coder-sniffer)

---

<!-- _class: lead -->
## develモジュール

---

Drupalには [devel](https://www.drupal.org/project/devel) というデバッグを支援するためのモジュールがあります(残念ながら、コアには含まれていません)。

develには以下のような機能が含まれています。
- ダミーコンテンツの生成
- パフォーマンス等を可視化するプロファイラー
- デバッグ支援のためのヘルパー関数

---

TBD

---


<!-- _class: lead -->
## xdebugによるリモートデバッグ

---

PHPの [xdebug](https://xdebug.org/) 拡張を使うと、任意の場所(ブレークポイント)で処理を止めてオブジェクトや変数の状態を確認・変更したり、コールスタックと呼ばれる「どの関数がどんな順番で実行されたか」といった情報を確認することができます。

これらの機能はコードを書く時だけではなく、読む時にも非常に有用です。

xdebugに関してDrupalに独自なものは全くありませんので解説は割愛しますが、先に進む前にxdebugによるリモートデバッグができる状態にしておいてください。

---

## まとめ

TBD

---

## ストレッチゴール

1. 利用しているエディタ・IDE環境で、ファイルを保存した時にphpcs(またはphpcbf)によるコードフォーマットのチェックを自動的に行うようにしてください。

2. 利用しているエディタ・IDE環境で、xdebugによるリモートデバッグが実行できるようにしてください。
