---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.13 Config Management (3)

---

これまでの2つのセクションでは、Config Managementの使って設定をコード化し、別の環境に同期できることを「使い方」の視点から学びました。

しかし、Config Managementは「銀の弾丸」ではありません。この機能では管理できないデータや、人間の操作ミスにより意図しないコードが生成されるケースなどももちろんあります。

非常に強力な機能ではありますが、その反面、しっかりと理解して使いこなすことが重要になります。

このセクションではConfig Managementのデータ構造や、「このような場合はうまくいかない」という例も合わせて紹介します。

---

具体的な解説に入る前に、「Drupal 8 Module Development Second Edition」を一部引用したものを紹介します。

>Some configuration can actually be critical to the proper functioning of the application. Certain code might break without a paramter having a value it can use. For exapmle, if there is no site-wide email adddress set, what email will the system use to send its automated mails to the user? For thie reason, many of these configuration parameters come with sane defaults (upon installation). However, this also shows that configuration is a part of the application and just as important as the actual code is.

(Drupal 8 Module Development Second Edition P153)

---

一言で表現すると「**コンフィグはアプリケーションのコードの一部だと思え**」ということです(実体もまさにその通りなのですが)。

「GUIから手軽に変更できるから」「PHPのソースコードではないから」という理由で詳細を理解せずに安易に扱えるものではない、という認識を持つことをまずはスタートラインにしましょう。

---

<!-- _class: lead -->
## 2.13.1 Content Entity と Config Entity

---

1章で少し紹介したとおり、Drupal上のほぼ全てのデータは「エンティティ」という概念の上に成り立っています。

「エンティティ」には `Content Entity` と `Config Entity` の2種類があります。

そして、Config Managementで管理できるのは後者の `Config Entity` のみです。

文章で書くと簡単ですが、これを理解することがConfig Managementを使う上で一番重要なポイントになります。

---

では、何がContent Entityで何がConfig Entityなのでしょうか？

開発者が一番簡単にこれを理解する方法は、クラスの継承関係を把握することです。

---

![height:600px](https://www.drupal.org/files/classDrupal_Entities.png)

---

細かくて見えませんね。。以下のリンクからアクセスしてください。
https://www.drupal.org/files/classDrupal_Entities.png

Content Entityは `ContentEntityBase`、 Config Entityは `ConfigEntityBase` のサブクラスとして実装されています。

---

ここで重要なポイントは「ある機能を実現するためにContent EntityとConfig Entityの両方が利用される場合がある」という点です。

「ノード」を例に考えてみましょう。

コンテンツ作成者が入力する「タイトル」や「本文」等のデータは `Content Entity` として保存されます。一方で、「ノードがどんなフィールドを持っているか」、つまりコンテンツタイプやフィールドの定義(メタデータ)は `Config Entity` として保存されます。

---

ノード以外のよく使う機能でも、以下のようにエンティティが分割して管理されています。

- ブロック
  - ブロックレイアウト: Config Entity
  - ブロックコンテンツ: Content Entity
- メニュー
  - メニュー: Config Entity
  - メニューリンクコンテンツ: Content Entity
- タクソノミー
  - ボキャブラリ: Config Entity
  - ターム: Content Entity

---

Drupalでは、一般ユーザーの「コンテンツ編集」と管理者ユーザーの「サイトの設定変更」に、固定された明確な線引きがあるわけではありません。

権限設定によっては一般ユーザーが「サイトの設定変更」を行うこともできますし(これはサイトの実運用的に起こり得ます)、設定次第で「コンテンツ編集」と「サイトの設定変更」のテーマ(UI)を同じにも別々にもできます。

そのため、「何がContent Entityで何がConfig Entityか」を把握するには少し時間が必要かもしれません。完全な切り分けではありませんが、データを変更する際のパスが `/admin` 以下であれば、それはConfig Entityである可能性が高いです。

---

<!-- _class: lead -->
## 2.13.2 DB内でコンフィグがどのように管理されているか

---

前のセクションでコンフィグがymlファイルとしてエクスポート出来ることが分かりました。

一方、ymlファイルの元データとなるコンフィグは、DB内の `config` というテーブルに保管されています。

このテーブルは、 `collection`, `name`, `data` という3つのカラムを持っており、ファイル名の `.yml` の除く部分が `name` に対応します。

`data` には、PHPのオブジェクトをシリアライズした状態でコンフィグの各キーとバリューが格納されています。

---

```txt
$ sqlite3 web/sites/default/files/.ht.sqlite

sqlite> .schema config
CREATE TABLE config (
collection VARCHAR(255) NOT NULL DEFAULT '', 
name VARCHAR(255) NOT NULL DEFAULT '', 
data BLOB NULL DEFAULT NULL, 
 PRIMARY KEY (collection, name)
);

sqlite> select * from config where name = 'system.site';
collection  name         data 
----------  -----------  --------
            system.site  a:10:{s:4:"uuid";s:36:"14814b92-3cc4-41c7-820d-27c54cb203b5";s:4:"name";s:9:"Drupal 8!";s:4:"mail";s:17:"admin@example.org";...
```

---

Drupalがコンフィグをファイルとしてエクスポートする時は単にDBに保存されているPHPのオブジェクトをデシリアライズして、ymlに変換しているだけです。シンプルですね。

Drupal 7では [Features](https://www.drupal.org/project/features) というモジュールでコンフィグをphpのコードとして生成する方法が多く採用されていました。

Drupal 8ではymlとして出力されるため可読性が大きく向上し、内部のデータ構造も非常にシンプルに設計し直されています。

---

<!-- _class: lead -->
## 2.13.3 Configの衝突や不整合について

---

冒頭でも書いたとおり、Config Managementは「銀の弾丸」ではありません。

適切なコードのレビューやタスクのコントロールとセットで利用する必要があります。

以下のような2つのタスクが同時に進行する例で考えてみましょう。
- Aさん: サイト名を `Drupal 8!` に変更する
- Bさん: 管理者のメールアドレスを `administrator@example.org` に変更する

---

Aさんのアウトプットは以下のようなコンフィグの差分になります。

```diff
diff --git a/config/sync/system.site.yml b/config/sync/system.site.yml
index 6173215..0a6c4d9 100644
--- a/config/sync/system.site.yml
+++ b/config/sync/system.site.yml
@@ -1,5 +1,5 @@
 uuid: 14814b92-3cc4-41c7-820d-27c54cb203b5
-name: 'Drupal 8'
+name: 'Drupal 8!'
 mail: admin@example.org
 slogan: ''
 page:
```

問題ないですね。

---

続いてBさんのアウトプットは以下のようなコンフィグの差分になります。これも問題ないように見えます。

```diff
diff --git a/config/sync/system.site.yml b/config/sync/system.site.yml
index 6173215..95ca0b6 100644
--- a/config/sync/system.site.yml
+++ b/config/sync/system.site.yml
@@ -1,6 +1,6 @@
 uuid: 14814b92-3cc4-41c7-820d-27c54cb203b5
 name: 'Drupal 8'
-mail: admin@example.org
+mail: administrator@example.org
 slogan: ''
 page:
   403: ''
```

---

では、AさんのパッチをマージしてからBさんのパッチをマージするとどうなるでしょうか？

先にマージした Aさんのパッチで `name` の行が変更されているため、衝突が発生してBさんのパッチはマージできません。

このようなケースでは、先にAさん(Bさん)のパッチをマージしてから、そのコンフィグをインポートした後、Bさん(Aさん)のタスクを開始する必要があります。

つまり、タスクとしては並列には実行できない、ということです。

---

先にAさんのパッチをマージした状態で、Bさんがコンフィグを変更した場合のアウトプットは以下になります。このパッチは問題なくマージ可能です。

```diff
diff --git a/config/sync/system.site.yml b/config/sync/system.site.yml
index 0a6c4d9..c932893 100644
--- a/config/sync/system.site.yml
+++ b/config/sync/system.site.yml
@@ -1,6 +1,6 @@
 uuid: 14814b92-3cc4-41c7-820d-27c54cb203b5
 name: 'Drupal 8!'
-mail: admin@example.org
+mail: administrator@example.org
 slogan: ''
 page:
   403: ''
```

---

もう一つ注意すべき点があります。

前のセクションで解説したとおり、コンフィグは単にファイルを更新しただけでは適用されず、明示的にインポートを行う必要があります。

もし、Aさんのパッチをマージ後にBさんがコンフィグのインポートをし忘れて自分のタスクを実施し、コンフィグをエクスポートした場合はどうなるでしょうか？

---

この場合のパッチは以下のようになります。このパッチでは最終的には意図した結果になりません。しかし、このパッチはコードの衝突はないためマージ可能です。

```diff
diff --git a/config/sync/system.site.yml b/config/sync/system.site.yml
index 0a6c4d9..95ca0b6 100644
--- a/config/sync/system.site.yml
+++ b/config/sync/system.site.yml
@@ -1,6 +1,6 @@
 uuid: 14814b92-3cc4-41c7-820d-27c54cb203b5
-name: 'Drupal 8!'
-mail: admin@example.org
+name: 'Drupal 8'
+mail: administrator@example.org
 slogan: ''
 page:
   403: ''
```

---

簡単な例として同じコンフィグを複数の人が変更するパターンで説明しましたが、「新しくノードにフィールドを追加し、それをViewsにも出力したい」のような場合でも同様です。

このケースでは、「Viewsでフィールドを出力する」ためには、手前のタスクとして「ノードにフィールドを追加する」事が必要になります。

チームの人数やスキルにもよりますが、タスクを細かく分解して担当者をバラバラにすることで、逆に想定外の問題が発生してコストが高く付くこともあります。

---

このように、実際のプロダクト開発でConfig Managementを適切に運用するには、使い方だけではなく以下のような要素も合わせて考える必要があります。

- コンフィグ同士の依存関係の把握
- タスクの順序を適切にコントロールする
- コンフィグの変更の前に最新のコンフィグを必ずインポートする
- 個々のタスクだけではなく、最終的に必要となるアウトプットを理解して変更の実施やコードレビューを行う

---

## まとめ

このセクションでは、Config Managementの少し踏み入った内容を解説しました。

Drupal 7ではFeaturesというモジュールでコンフィグをphpのコードとして生成する方法が多く採用されていましたが、Drupal 8ではymlとして出力されるため、可読性は大きく向上しています。

いくつか注意すべきポイントはあるものの、使いこなせば生産性が大きく向上する機能なので、積極的に活用してください。
