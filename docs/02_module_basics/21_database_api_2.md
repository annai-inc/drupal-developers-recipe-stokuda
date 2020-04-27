---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.22 データベースAPI (2)

---

このセクションでは、引き続きDrupalのデータベースAPIに関して、INSERT/UPDATE/DELETEなどのクエリを発行する方法を解説します。

---

<!-- _class: lead -->
## 2.22.1 独自のテーブルを定義する

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
## 2.22.2 Insert

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

INSERTクエリを発行するには [insert](https://www.drupal.org/docs/8/api/database-api/insert-queries) メソッドを使用します。

`fields` メソッドに挿入するカラム名とその値を配列で指定することができます。

前のセクションで使った [select](https://www.drupal.org/docs/8/api/database-api/dynamic-queries) メソッドとほとんど変わらないですね。

---

スクリプトを実行してみましょう。

```txt
$ vendor/bin/drush scr web/modules/custom/hello_world/scripts/insert.php
```

実行したら、bookテーブルのレコードを確認してください。以下のように2件のデータが挿入されていれば成功です。

```txt
sqlite> select * from book;
1|book 1|An awesome book
2|book 2|A wonderful book
```

<!-- _class: lead -->
## 2.22.3 Update

---

<!-- _class: lead -->
## 2.22.4 Delete

---

<!-- _class: lead -->
## 2.22.5 Transaction

---

<!-- _class: lead -->
## 2.22.6 他のモジュールが発行するクエリを変更する

---

<!-- _class: lead -->
## 2.22.7 Update hook

---

## まとめ

TBD

---

## ストレッチゴール

TBD