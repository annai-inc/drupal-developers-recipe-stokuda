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

一言で表現すると「コンフィグはアプリケーションのコードの一部だと思え」ということです(実体もまさにその通りなのですが)。

「GUIから手軽に変更できるから」「PHPのソースコードではないから」という理由で詳細を理解せずに安易に扱えるものではない、という認識を持つことをまずはスタートラインにしましょう。

---

<!-- _class: lead -->
## Content Entity と Config Entity

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

ポイントは「ある機能を実現するためにContent EntityとConfig Entityの両方が利用される場合がある」という点です。

「ノード」を例に考えてみましょう。
コンテンツ作成者が入力する「タイトル」や「本文」等のデータは `Content Entity` として保存されます。一方で、「ノードがどんなフィールドを持っているか」、つまりコンテンツタイプやフィールドの定義は `Config Entity` として保存されます。

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

権限設定によっては一般ユーザーが「サイトの設定変更」を行うこともできますし、設定次第で「コンテンツ編集」と「サイトの設定変更」のテーマ(UI)を同じにも別々にもできます。

そのため、「何がContent Entityで何がConfig Entityか」を把握するには少し時間が必要かもしれません。

完全な切り分けではありませんが、データを変更する際のパスが `/admin` 以下であれば、それは Config Entityである可能性が高いです。

---

<!-- _class: lead -->
## TBD

---
