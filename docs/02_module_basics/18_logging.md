---
marp: true
theme: gaia
_class: invert
---

<!-- _class: lead -->
# 2.18 ロギング

---

このセクションではDrupalでログを出力する方法について解説します。

---

<!-- _class: lead -->
# 2.18.1 Database Logging (a.k.a Watchdog)

---

Drupalをstandard profileでインストールすると、デフォルトで `Database Logging` (dblog)モジュールが有効になっています。

このモジュールはデータベース上の `watchdog` というテーブルにログを保存します。

どんなデータ構造になっているか簡単に見てみましょう。

---

```txt
 ❯ vendor/bin/drush sqlc
SQLite version 3.31.1 2020-01-27 19:55:54
Enter ".help" for usage hints.
sqlite> .mode line
sqlite> .schema watchdog
CREATE TABLE watchdog (
wid INTEGER PRIMARY KEY AUTOINCREMENT, 
uid INTEGER NOT NULL CHECK (uid>= 0) DEFAULT 0, 
type VARCHAR(64) NOT NULL DEFAULT '', 
message TEXT NOT NULL, 
variables BLOB NOT NULL, 
severity INTEGER NOT NULL CHECK (severity>= 0) DEFAULT 0, 
link TEXT NULL DEFAULT NULL, 
location TEXT NOT NULL, 
referer TEXT NULL DEFAULT NULL, 
hostname VARCHAR(128) NOT NULL DEFAULT '', 
timestamp INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX watchdog_type ON watchdog (type);
CREATE INDEX watchdog_uid ON watchdog (uid);
CREATE INDEX watchdog_severity ON watchdog (severity);
```

---

```txt
sqlite> select * from watchdog limit 1
      wid = 109
      uid = 0
     type = mail
  message = Error sending email (from %from to %to with reply-to %reply).
variables = a:3:{s:5:"%from";s:17:"admin@example.org";s:3:"%to";s:17:"admin@example.org";s:6:"%reply"; ...
 severity = 3
     link = 
 location = http://127.0.0.1:8088/
  referer = 
 hostname = 127.0.0.1
timestamp = 1579867593
```

dblogモジュールは管理用のUIも提供しています。管理者ロールを持つアカウントで `/admin/reports/dblog` にアクセスするとブラウザからログを確認することができます。

---

![](../assets/02_module_basics/18_logging/dblog_admin_ui.png)

---

<!-- _class: lead -->
# 2.18.2 syslog

---

デフォルトでは有効になっていませんが、Drupalコアではsyslogモジュールも提供されています。名前の通り、ログは [syslog](https://en.wikipedia.org/wiki/Syslog) として出力されます。

Drupalでは複数のログ出力を併用できるようになっているため、dblogとsyslogは同時に利用可能です。

---

<!-- _class: lead -->
# 2.18.3 ログの書き込み

---

それでは、ログを出力してみましょう。

Drupal 7では `watchdog` というグローバル関数を使うことでdblog, syslogにログを出力することができました。

例によってこのAPIはDrupal 8では削除されています。しかし、これによりDrupalのロギングインターフェースは [PSR-3](https://www.php-fig.org/psr/psr-3/) に準拠したモダンな設計に変更されています。

---

TBD

---

## まとめ

このセクションではDrupalでログを出力する方法について解説しました。

TBD
