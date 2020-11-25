---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.12 Config Management (2)

---

このセクションでは、既存のコンフィグを元に設定を同期するのではなく、新規にサイトを立ち上げる方法を解説します。

「そんなことをしなくても、既存サイトのDBを元に立ち上げればいいのでは？」と考えるかもしれません。

しかし、以下のようなケースではどうでしょうか？

- 大量の個人情報が保存されている
- 定期的に動作するバッチ処理が実装されており、その際にメールやチャット等に通知が送信される

---

このような機能が実装されている場合、既存サイトのDBをリストアするのは非常に危険です。

また、機能に関係なく、セキュリティーポリシーとしてNGの場合もあります。

このセクションを通して、既存のコンフィグ(つまり空のDB)からサイトを立ち上げる方法を理解しましょう。

---

<!-- _class: lead -->
## 2.12.1 事前準備(パッチの適用)

---

それではスタートしましょう、といきたいところなのですが、運悪くバグにあたってしまったのでまずは、Drupalにパッチを適用する必要があります。

`composer.json` の `extra` キーに以下の値を追加してください。

```json
        "patches": {
            "drupal/core": {
                "Allow an install hook in profiles installing from configuration": "https://www.drupal.org/files/issues/2020-01-09/2982052-37.patch",
                "avoid null reference on drush site:install --existing-config": "https://gist.githubusercontent.com/blauerberg/3ce158b4c7ec93417974291a68f99aa1/raw/46df1a6da1473477009fcfbb261e7a3a3da2fd3c/gistfile1.txt"
            }
        }
```

---

これで `composer install` した時に2つのパッチが適用されます。

これはcomposerの標準機能ではなく、 [composer-patches](https://github.com/cweagans/composer-patches) により提供される機能です。

ある程度複雑なプロダクトを開発していると、このように(多くの場合は一時的に)composerで構成管理しているライブラリにパッチが必要なケースも発生します。

composer-patchesの使い方は押さえておきましょう。

---

※利用しているDrupalコアのバージョンが `">=8.8.7 <8.9.0"` に該当する場合、1つ目のパッチはconflictするので当たりません。この場合はDrupalコアのバージョンを8.8.6もしくは8.9.xに変更してください。

---

<!-- _class: lead -->
## 2.12.2 既存のコードツリーから新規にサイトを立ち上げる

---

それでは、既存のコードツリーから新規にサイトを立ち上げてみましょう。

既存サイトのパスを `/some_path/drupal`、新しいサイトのパスを `/somewhere_else/drupal` とした場合のコマンドは以下になります。
(自身の環境に合わせてパスは読み替えてください)

---

```txt
# 既存サイトのコードツリーをコピー
cp -rfp ~/some_path/drupal /somewhere_else/drupal

# コピー先に移動
cd /somewhere_else/drupal

# サイトのインストールプロセスでファイル書き込みができるようにパーミッションを変更
sudo chmod 775 web/sites/default

# sqliteのDBファイルを削除
sudo rm -rf web/sites/default/files/.ht.sqlite*
```

---

次に、先ほどのパッチを適用するために `composer install` を実行します。

```txt
$ composer install
 
...

- Applying patches for drupal/core
https://www.drupal.org/files/issues/2020-01-09/2982052-37.patch (Allow an install hook in profiles installing from configuration)
https://gist.githubusercontent.com/blauerberg/3ce158b4c7ec93417974291a68f99aa1/raw/46df1a6da1473477009fcfbb261e7a3a3da2fd3c/gistfile1.txt (avoid null reference on drush site:install --existing-config)

...
```

上記のように drupal/core に対して2件のパッチが適用されることを確認してください。　

---

次にdrushコマンドでサイトを初期化します。サイトの初期化には [site:install](https://drushcommands.com/drush-9x/site/site:install/) サブコマンドを利用します。このコマンドには `--existing-config` というオプションがあり、これを指定することでコード化されたコンフィグからサイトを立ち上げることができます。

```txt
$ vendor/bin/drush -y site:install --existing-config --account-name="admin" --account-pass="admin" --db-url=sqlite://web/sites/default/files/.ht.sqlite

 You are about to CREATE the 'web/sites/default/files/.ht.sqlite' database. Do you want to continue? (yes/no) [yes]:
 > yes

 [notice] Starting Drupal installation. This takes a while.
 [success] Installation complete.
```

`--existing-config` 以外に指定しているオプションの意味は、[Drushのドキュメント](https://drushcommands.com/drush-9x/site/site:install/) を参照してください。　

---

本来であればこれで完了なのですが、多言語の機能にバグがあり一部のラベルの翻訳が正しく反映されません。`drush cim` を実行してコード側に合わせます。

(drupal.orgでissueがあるか探したのですが見当たりませんでした。知っている方がいれば教えてください!)

```txt
$ vendor/bin/drush -y cim
```

---

最後に、既存のサイトと同じように `drupal` コマンドでサーバーを立ち上げます。

```txt
$ vendor/bin/drupal server

 [OK] Executing php from "/home/aoyama/.anyenv/envs/phpenv/versions/7.2.22/bin/php".                                    
 Listening on "http://127.0.0.1:9919".                                                        
```

デフォルトではポート8080でサイトが起動しますが、このポートが使用中の場合は別のポートが利用されることもあります。つまり、drupalコマンドで同時に複数のサイトを立ち上げることができます。

上記の例はポート9919でサイトが立ち上がっています。

それでは、サイトにアクセスして既存のサイトと同じ設定になっていることを確認してください。

---

## まとめ

このセクションでは、既存のコンフィグを元に新規にサイトを立ち上げる方法を解説しました。

実際のプロダクト開発では、1つの環境をずっと使うのではなく、デバッグや検証のためにサンドボックス的に別の環境を立ち上げると、効率よく開発を進めることができます。

これが気軽にできるようになるだけで、アウトプットの効率が大きく向上します。

---

実行環境は本セクションで解説したものに固執する必要はありません。

本コンテンツの趣旨ではないので詳しい解説は省略しますが、DockerやLXDなどを使えば、ほんの1,2分で新しい環境を立ち上げる事ができます。

何かの目的のために新しい環境を用意しようとした時に、毎回数分でできるか30分かかるかでは、生産性に大きな違いが生まれます。

何らかの方法で簡単にDrupal環境を立ち上げ・破棄できるようにしておきましょう。
