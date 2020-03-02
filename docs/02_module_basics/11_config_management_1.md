---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.11 Config Management (1)

---

このセクションでは、Config Managementについて解説します。

Config Managementの本質は「設定のコード化」です。

この機能とgit等のバージョン管理を組み合わせると、dev -> stg -> prodのように異なる環境間で手作業なく設定を同期したり、既存の設定を元に新しい環境を新規に作成することができます。

これは、現代的なシステムに求められるCI/CDを実現するためには必須の要素と言えます。

---

<!-- _class: lead -->
## Config Managemantの概要

---

Drupalのコンフィグは、パフォーマンス的な理由からデフォルトではデータベースに保存されます。

しかし、全てのコンフィグはymlファイルとしてエクスポート・インポートする事ができます。

Drupalでは、コンフィグのエクスポート・インポートを次に紹介する2つの方法で行うことができます。

---

<!-- _class: lead -->
## 管理UIからConfigをエクスポートする

---

それでは、まずは管理UIからコンフィグをエクスポートしてみましょう。

ツールバーから「環境設定 > Development > 同期」(/admin/config/development/configuration) にアクセスしてください。このUIでは、タブ毎に以下の操作を行うことができます。

---

|タブ|説明|
|---|---|
|同期|コードで管理されているコンフィグと現在のコンフィグの差分を表示する。また、コードを正としてコンフィグを同期する。|
|インポート|コード化されたコンフィグをアップロードする|
|エクスポート|コード化されたコンフィグをエクスポートする|

---

「同期」タブを見ると「インポートする構成の変更がありません。」となっています。まだコンフィグをコード化していないため、変更も検出されない状態です(とても危ない状態ですね)。

![Admin UI](../assets/02_module_basics/11_config_management_1/admin_ui.png)

---

それでは、「エクスポート」タブに移動してコード化されたコンフィグをエクスポートしましょう。

この管理UIには、「フルアーカイブ」と「シングルアイテム」の2つのタブがあります。

「フルアーカイブ」ではシステム全体のコンフィグ、「シングルアイテム」では指定した単一のコンフィグをエクスポートすることができます。

---

「フルアーカイブ」タブに移動して「エクスポート」ボタンを押し、コード化されたコンフィグをエクスポートしましょう。

![export](../assets/02_module_basics/11_config_management_1/export.png)

---

「フルアーカイブ」の場合、コンフィグは `.tar.gz` で圧縮された状態でダウンロードされます。このファイルは、後ほどインポートする際に利用するので保管しておいてください)。

解凍して中身を見てみましょう。

```txt
(パスは自身の環境に合わせて読み替えてください)

$ mkdir -p /tmp/config
$ cd /tmp/config
$ tar zxf /tmp/config-127-0-0-1_8088-2020-03-02-02-37.tar.gz
$ ls -l /tmp/config
total 956
drwxr-xr-x 4 aoyama aoyama    80 Mar  2 11:58 language
-rw------- 1 aoyama aoyama   103 Mar  2 11:37 automated_cron.settings.yml
-rw------- 1 aoyama aoyama   558 Mar  2 11:37 block.block.bartik_account_menu.yml
-rw------- 1 aoyama aoyama   509 Mar  2 11:37 block.block.bartik_branding.yml
...
```

---

多数のymlファイルが含まれていることが分かります。サンプルとして `system.site.yml` を見てみましょう。

```yml
uuid: 14814b92-3cc4-41c7-820d-27c54cb203b5
name: 'Drupal 8'
mail: admin@example.org
slogan: ''
page:
  403: ''
  404: ''
  front: /node
admin_compact_mode: false
weight_select_max: 100
langcode: ja
default_langcode: ja
_core:
  default_config_hash: yTxtFqBHnEWxQswuWvkjE8mKw2t8oKuCL1q8KnfHuGE
```

---

このファイルは `/admin/config/system/site-information` の管理UIの設定に対応します。

![height:580px](../assets/02_module_basics/11_config_management_1/site_information.png)

---

エクスポートされたymlと同じ値が管理UIで設定されていることが分かりますね。

基本的には、管理UIから設定可能な項目の1つ1つがymlのkeyとvalueのペアに対応しています。

---

ただし、「**必ずしも管理UIで全ての設定項目が開放されているわけではない**」という点に注意しましょう。

このコンフィグの例では、 `admin_compact_mode` や `weight_select_max` などは管理UIからは設定できません。

言い換えると、「**一部の設定値を変更する場合は、管理UIからではなくymlを直接変更する必要がある**」ということになります。

ある程度複雑なシステムを開発する場合、これが必要になるケースもあります。頭の片隅に入れておきましょう。

---

それでは、「サイト名」を `Drupal 8!` に変更して保存してください。

次に、再度コンフィグをエクスポートして、もう一度 `system.site.yml` を見てみましょう。手順は先ほどと同様です。

---

`name` キーの値が変更されていることがわかります。

```yml
uuid: 14814b92-3cc4-41c7-820d-27c54cb203b5
name: 'Drupal 8!'
mail: admin@example.org
slogan: ''
page:
  403: ''
  404: ''
  front: /node
admin_compact_mode: false
weight_select_max: 100
langcode: ja
default_langcode: ja
_core:
  default_config_hash: yTxtFqBHnEWxQswuWvkjE8mKw2t8oKuCL1q8KnfHuGE
```

---

<!-- _class: lead -->
## 管理UIからConfigをインポートする

---

次は、コンフィグをインポートしていきます。

先ほど「サイト名」を変更しましたが、変更前にエクスポートしたコンフィグをアップロードして、「サイト名」が変更前の状態に戻ることを確認しましょう。

![import](../assets/02_module_basics/11_config_management_1/import.png)

---

アップロードが成功すると、「同期」タブにリダイレクトされ、以下のように表示されます。

![sync 1](../assets/02_module_basics/11_config_management_1/sync_1.png)

---

メッセージの通り、この状態ではまだアップロードしたコンフィグは反映されていません。

この状態で、一度 `/admin/config/system/site-information` を確認しましょう。変更した「サイト名」がまだそのままの状態になっていることが分かります。

---

![](../assets/02_module_basics/11_config_management_1/site_information_before_sync.png)

---

それでは、 `/admin/config/development/configuration` に戻ってインポートを完了させましょう。

「同期」タブには「コード化されたコンフィグと現在のコンフィグの差分」が表示されます。

![](../assets/02_module_basics/11_config_management_1/sync_2.png)

---

「差分を表示」をクリックすると、GUI上で差分を確認できます。

左側の `ACTIVE` が `現在有効な設定値` で、右側の `ステージ済み` が `コードの設定値` になります。つまり、インポートすると左の状態から右の状態に更新されます。

![](../assets/02_module_basics/11_config_management_1/sync_3.png)

---

ここで、「サイト名」のみが変更されていることをしっかり確認してください。

確認できたら「インポート」ボタンを押して、インポートを実行してください。次のように表示されれば成功です。

![](../assets/02_module_basics/11_config_management_1/sync_4.png)

---

最後に、`/admin/config/system/site-information` で「サイト名」が元に戻っていることを確認しましょう。

![](../assets/02_module_basics/11_config_management_1/site_information_after_sync.png)

---

TBD.