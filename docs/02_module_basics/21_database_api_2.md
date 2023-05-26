---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.21 データベースAPI (2)

---

このセクションでは、引き続きDrupalのデータベースAPIに関して、INSERT/UPDATE/DELETEなどのクエリを発行する方法を解説します。

---

<!-- _class: lead -->
## 2.21.1 独自のテーブルを定義する

---

では早速INSERTのサンプルを...と行きたいところですが、ご存じの通り、Drupalのテーブルの多くは正規化され非常に細かく分割されています。

サンプルコードと動作確認を簡単に行うために、まずはシンプルなテーブルをDB上に定義しましょう。

---

DrupalでDBに独自のテーブルを定義するには [hook_scheme](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21database.api.php/function/hook_schema/) を実装します。

この関数を `{module_name}.install` というファイルに定義すると、モジュールがインストールされた際に自動的にDBにテーブルの定義を行ってくれます。

それでは、`hello_world.install` を次のように実装してください。

---

```php
<?php

/**
 * Implements hook_schema().
 */
function hello_world_schema() {
  $schema = [];

  $schema['book'] = [
    'description' => 'The table holds book data.',
    'fields' => [
      'id' => [
        'description' => 'The primary identifier.',
        'type' => 'serial',
        'unsigned' => TRUE,
        'not null' => TRUE,
      ],
      'name' => [
        'description' => 'The book title.',
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
      ],
      'description' => [
        'description' => 'The book description.',
        'type' => 'text',
        'size' => 'normal',
      ],
    ],
    'primary key' => ['id'],
  ];

  return $schema;
}

```

---

`hook_schema` では、モジュールが独自に定義するテーブルの構造を配列として返します。このサンプルコードでは、おおまかには次の定義しています。

- `book` という名前のテーブルを定義する
- `book` は、 `id`、 `name`、 `description` という3つのカラムを持ち、プライマリキーは `id` カラムとする
- `id` カラムは自動でインクリメントされ、unsignedかつnot null制約がある
- `name` カラムは255文字までのテキストを格納できる
- `description` にもテキストを格納できる

---

他の多くのフレームワークと同様に、Drupalでも永続化層のデータ構造の定義は抽象化された記法で宣言します。そのため、この実際にどのようなテーブル定義が生成されるかは利用するデータベースバックエンドによって異なります。

フォーマットについての詳細は [Schema API](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21database.api.php/group/schemaapi/) を参照してください。

---

それでは動作を確認しましょう。`hook_schema` が実行されるのはモジュールをインストールした時のみ(※)です。そのため、モジュールを一度アンインストールし、再度インストールしてください。

```txt
$ vendor/bin/drush -y pmu hello_world && vendor/bin/drush -y en hello_world
```

※ `drupal_install_schema('hello_world')` のようにPHPコード中で任意に実行することもできますが、テストコードを除いてはあまり使うことはないと思います。

---

Drushの `sqlc` サブコマンドでテーブルが生成されているか確認してみましょう。

```txt
$ vendor/bin/drush sqlc

sqlite> .schema book
CREATE TABLE book (
id INTEGER PRIMARY KEY AUTOINCREMENT CHECK (id>= 0),
name VARCHAR(255) NOT NULL,
description TEXT DEFAULT NULL
)
```

---

<!-- _class: lead -->
## 2.21.2 INSERTクエリの実行

---

bookテーブルが作成できたので、INSERTクエリを発行してデータを挿入してみましょう。

UIまで作り込むと大変なので、実行可能なPHPのスニペットとして実装します。

前のセクションの通り、データベースにアクセスするには `database` サービス経由でインスタンスを取得する必要があります。しかし、このサービスにアクセスするには、まずDrupal自体を初期化(bootstrap)する必要があります。

もちろん、必要なファイルを読み込んで各種APIを実行することで、Drupalの初期化を全てコードで書いていくこともできますが、それなりにボリュームのあるコード量になります。

---

このような場合、Drushの `scr (php:script)` サブコマンドを使うと便利です。このコマンドを使うと、Drupalを初期化してから任意のスニペットが実行できます。

つまり、モジュールのコードを書く時と同じ条件で任意のスクリプトを実装することができます。

RailsやLaravelなど、今時のフレームワークではこのような仕組みは必ず用意されていますね。

(残念ながら、Drupalには `rails console` のようなインタラクティブなコンソールはありませんが...)

---

それでは、 `web/module/custom/hello_world/scripts/insert.php` を次のように実装してください。

---

```php
<?php

/**
 * @file
 * An example of INSERT query.
 */

/**
 * The database service.
 *
 * @var \Drupal\Core\Database\Connection $database
 */
$database = \Drupal::database();
$id = $database->insert('book')
  ->fields(['name' => 'book 1', 'description' => 'An awesome book'])
  ->execute();

var_dump($id);

$database = \Drupal::database();
$id = $database->insert('book')
  ->fields(['name' => 'book 2', 'description' => 'A wonderful book'])
  ->execute();

var_dump($id);

```

---

INSERTクエリを発行するには [insert](https://www.drupal.org/docs/8/api/database-api/insert-queries) メソッドを使用します。

`fields` メソッドに挿入するカラム名とその値を配列で指定することができます。

`execute` メソッドでクエリが発行されるのは前のセクションと同じですが、insertの場合は戻り値は挿入したレコードのIDになります。

前のセクションで使った [select](https://www.drupal.org/docs/8/api/database-api/dynamic-queries) メソッドとほとんど変わらないですね。

---

スクリプトを実行してみましょう。

```txt
$ vendor/bin/drush scr web/modules/custom/hello_world/scripts/insert.php
```

---

実行したら、bookテーブルのレコードを確認してください。以下のように2件のデータが挿入されていれば成功です。

```txt
$ vendor/bin/drush sqlc

sqlite> .mode line
sqlite> select * from book;
         id = 13
       name = book 1
description = An awesome book

         id = 14
       name = book 2
description = A wonderful book```
```

---

<!-- _class: lead -->
## 2.21.3 UPDATEクエリの実行

---

次はUPDATEクエリを実行してみましょう。

`web/module/custom/hello_world/scripts/update.php` を次のように実装してください。

---

```php
<?php

/**
 * @file
 * An example of UPDATE query.
 */

/**
 * The database service.
 *
 * @var \Drupal\Core\Database\Connection $database
 */
$database = \Drupal::database();
$result = $database->update('book')
  ->fields(['description' => 'The awesome book'])
  ->condition('description', '%' . $database->escapeLike('awesome') . '%', 'LIKE')
  ->execute();

var_dump($result);

$database = \Drupal::database();
$result = $database->update('book')
  ->fields(['description' => 'The wonderful book'])
  ->condition('description', '%' . $database->escapeLike('wonderful') . '%', 'LIKE')
  ->execute();

var_dump($result);

```

---

UPDATEクエリを発行するには [update](https://www.drupal.org/docs/8/api/database-api/update-queries) メソッドを使用します。

`fields` メソッドで、insertの時と同様に更新対象のカラム名とその値を配列で指定することができます。

`condition` メソッドでは、更新対象のデータをフィルターしています。このサンプルコードで生成されるSQLは `WHERE book.description LIKE "%awesome%"` のようになります。

`escapeLike` メソッドであいまい検索用の記号(%)をエスケープしている点に注意してください。`dbLike` という同様のメソッドも提供されており、これを使った情報がWeb上に多くありますが、このAPIはDeprecatedでありDrupal 9.0で削除される予定です。

---

`execute` メソッドの戻り値は更新したレコードの件数です。

特に目新しい要素は出てこなかったですね。

コードが概ね理解できてところで、スクリプトを実行してみましょう。

```txt
$ vendor/bin/drush scr web/modules/custom/hello_world/scripts/update.php
```

---

実行したら、bookテーブルのレコードを確認してください。次のように2件のデータの先頭の冠詞が「The」に更新されていれば成功です。

```txt
$ vendor/bin/drush sqlc

sqlite> .mode line
sqlite> select * from book;
         id = 13
       name = book 1
description = The awesome book

         id = 14
       name = book 2
description = The wonderful book
```

---

<!-- _class: lead -->
## 2.21.4 Deleteクエリの実行

---

同様に、DELETEクエリを実行してみましょう。

`web/module/custom/hello_world/scripts/delete.php` を次のように実装してください。

---

```php
<?php

/**
 * @file
 * An example of DELETE query.
 */

/**
 * The database service.
 *
 * @var \Drupal\Core\Database\Connection $database
 */
$database = \Drupal::database();
$result = $database->delete('book')->execute();

var_dump($result);

```

---

DELETEクエリを発行するには [delete](https://www.drupal.org/docs/8/api/database-api/delete-queries) メソッドを使用します。

`execute` メソッドの戻り値は削除したレコードの件数です。

それでは、スクリプトを実行してみましょう。

```txt
$ vendor/bin/drush scr web/modules/custom/hello_world/scripts/delete.php
```

---

実行したら、bookテーブルのレコードを確認してください。次のようにデータが0件になっていれば成功です。

```txt
$ vendor/bin/drush sqlc

sqlite> .mode line
sqlite> select COUNT(*) from book;
COUNT(*) = 0

```

---

<!-- _class: lead -->
## 2.21.5 トランザクションの制御

---

DrupalのDatabase APIには、もちろんトランザクションを制御するためのものもあります。

トランザクションを明示的に開始するには、[startTransaction](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21Connection.php/function/Connection%3A%3AstartTransaction/) メソッドを利用します。

このAPIを実行すると [\Drupal\Core\Database\Transaction](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21Transaction.php/class/Transaction/) のオブジェクトが取得することができるので、必要に応じてロールバックの処理などを実装することができます。

---

<!-- _class: lead -->
## 2.21.6 他のモジュールが発行するクエリを変更する

---

DrupalのhookシステムはDatebase APIでも活用することができます。

他のモジュールに自身が発行するクエリの変更を許可するには、クエリを発行する際に [addTag](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21Query%21Select.php/function/Select%3A%3AaddTag/) メソッドでクエリに対してタグ付けを行う必要があります。

```php
$result = $this->database->select('book', 'b')
  ->fields('b', ['id', 'name', 'description'])
  ->addTag('book_selection')
  ->execute();
```

---

他のモジュールでは、以下のように [hook_query_alter](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21database.api.php/function/hook_query_alter/) を実装することで特定のタグがついたクエリを変更することができます。

```php

/**
 * Implements hook_query_alter().
 */
function module_name_query_alter(Drupal\Core\Database\Query\AlterableInteface $query) {
  if ($query->hasTag('book_selection')) {
    // altering query!
  }
}
```

2.4章で解説したフォーム向けのフックと同じように、[hook_query_TAG_alter](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21database.api.php/function/hook_query_TAG_alter/) というフックも提供されています。

---

<!-- _class: lead -->
## 2.21.7 Update hook

---

システムが育ってくると、既存のデータ構造を変更する必要が必ず出てきます。

Railsでは[Active Record Migrations](https://edgeguides.rubyonrails.org/active_record_migrations.html)、Laravelでは[Database: Migrations](https://laravel.com/docs/master/migrations) で紹介されるような機能が提供されています。

Drupalでは、同様の機能は [hook_scheme](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21database.api.php/function/hook_schema/) と [hook_update_N](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Extension%21module.api.php/function/hook_update_N/) というフックで実装します。hook_schema はbookテーブルを定義するためにすでに利用しましたね。

---

それでは、 hook_update_N を実装してnameカラムの最大文字数を255から128に変更してみましょう。

hook_update_Nもhook_schemaと同様に `{module_name}.install` にグローバル関数として実装します。

次のように `hello_world_update_8001` を実装してください。

---

```php
/**
 * Change length of name to 128.
 */
function hello_world_update_8001(&$sandbox) {
  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection $database
   */
  $database = \Drupal::database();

  /**
   * The database schema.
   *
   * @var  \Drupal\Core\Database\Schema $schema
   */
  $schema = $database->schema();

  $new_definition = [
    'description' => 'The book title.',
    'type' => 'varchar',
    'length' => 128,
    'not null' => TRUE,
  ];

  $schema->changeField('book', 'name', 'name', $new_definition);

}

```

---

`hook_update_N` の `N` はモジュールのスキーマバージョンに対応します。

スキーマバージョンは `XYZZ` のような4桁の数字で、次のフォーマットに従う必要があります。

- X: Drupalコアのメジャーバージョン。Drupal 8の場合は8。
- Y: モジュールのマイナーバージョン。8.x-1.xの場合は1、8.x-2.xの場合は2となる。ただし、.info.ymlのversionを見て厳粛にチェックされるわけではないので、0固定で実装されているケースも多い。
- ZZ: モジュールのマイナーバージョン内でのシーケンス番号

※Drupal 10になるとXが2桁に拡張される予定のようです。

---

`hook_update_N` が実装されていない状態でモジュールをインストールすると、スキーマバージョンは `8000` に初期設定されます。

現在のモジュールのスキーマバージョンは、`key_value` テーブルに保存されており、次のように確認することができます。

```txt
sqlite> select * from key_value where collection = 'system.schema' and name = 'hello_world';
collection = system.schema
      name = hello_world
     value = i:8000;
```

---

`database` サービスの [schema](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21Connection.php/function/Connection%3A%3Aschema/) メソッドをコールすると、スキーマ情報のオブジェクトを取得する事ができます。

このオブジェクトは [\Drupal\Core\Database\Schema](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21Schema.php/class/Schema/) のインスタンスであり、次のようなAPIを提供します。

- addIndex
- changeField
- createTable
- dropField
- etc...

---

サンプルコードでは、[changeField](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21Schema.php/function/Schema%3A%3AchangeField/) メソッドを使って既存のフィールドの定義を変更しています。

---

それでは、`hello_world_update_8001` を実行してテーブル定義を実際に変更しましょう。`hook_update_N` を実行するには、Drushの `updatedb(updb)` サブコマンドを利用します。

次のようにコマンドを実行してください。

```txt
$ vendor/bin/drush updb
 ------------- ----------- --------------- --------------------------------
  Module        Update ID   Type            Description
 ------------- ----------- --------------- --------------------------------
  hello_world   8001        hook_update_n   Change length of name to 128.
 ------------- ----------- --------------- --------------------------------
 Do you wish to run the specified pending updates? (yes/no) [yes]:
 > yes
>  [notice] Update started: hello_world_update_8001
>  [notice] Update completed: hello_world_update_8001
 [success] Finished performing updates.
```

---

テーブルの定義とスキーマバージョンが期待通り変更されているか確認しましょう。

```txt
$ vendor/bin/drush sqlc
sqlite> mode .line
sqlite> select * from key_value where collection = 'system.schema' and name = 'hello_world';
collection = system.schema
      name = hello_world
     value = i:8001;

sqlite> .schema book
CREATE TABLE IF NOT EXISTS "book" (
id INTEGER NOT NULL DEFAULT '',
description TEXT NULL DEFAULT 'NULL',
name VARCHAR(128) NOT NULL,
 PRIMARY KEY (id)
);
```

nameカラムの最大長が128に変更されていることが分かります。

---

`drush updb` が実行されると、Drupalはkey_valueテーブルに保存されている現在のスキーマバージョンと `hook_update_N` の「N」の部分を比較します。

その結果、新しいスキーマバージョンが定義されていると判断すると、該当する関数群を全て実行します。

つまり、「hook_update_N」の「N」の部分をインクリメントした関数を順次追加していくことで、データ構造やデータ自体のマイグレーションを実現することができます。

なお、残念ながらスキーマバージョンを過去のバージョンに戻すような機能はコアでは提供されていません。。

---

## まとめ

このセクションでは、引き続きDrupalのデータベースAPIついて解説しました。

[Drupal.orgのドキュメント](https://www.drupal.org/docs/8/api/database-api) から比較的よく使う機能を紹介しましたが、このドキュメントにも一度目を通しておきいてください。

※ちなみに実際のプロダクト開発では、サンプルのような制約が強まる変更を既存データへの影響を確認せずに雑に行ってはいけませんよ！

---

## ストレッチゴール

1. bookテーブルに `price` カラムを追加し、初期値が全て `1000` にするようにマイグレーションしてください。

2. 2.21.3で作成したupdate.phpを実行する際に引数として `raise-exception` を受け取り、この引数が指定された場合は、1つ目のUPDATEクエリの発行後に例外を発生させてロールバックするように変更してください。
