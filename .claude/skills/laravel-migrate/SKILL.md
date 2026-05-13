---
name: laravel-migrate
argument-hint: "[サブドメイン名]"
description: ER図をもとにLaravelのマイグレーションファイルを作成し、マイグレーションを実行します。
allowed-tools: Agent, Read, Write, Glob, Bash
---

# Laravelマイグレーションスキル

このスキルは、ER図（`ER.mmd`）をもとに**Laravelのマイグレーションファイルを自動生成**し、`php artisan migrate` を実行します。

---

## 起動時の共通処理

### 1. サブドメイン名の解決

`resolve-subdomain` スキルを呼び出してビジネスサブドメインの英語名を取得します:

```
Skill("resolve-subdomain", "$1")
```

取得した `<英語名>` を以降のすべてのファイルパスで使用します。

---

## 実行手順

### 3. ER図の読み込み

以下のパスにあるER図ファイルを読み込みます:

```
docs/<英語名>/ER.mmd
```

ファイルが存在しない場合は、ユーザーに以下のメッセージを伝えてください:

```
docs/<英語名>/ER.mmd が見つかりません。
er-diagramスキルでER図を先に作成してください。
```

### 4. ER図の解析

ER図（Mermaid `erDiagram` 形式）を解析し、以下の情報を抽出します:

- **テーブル名**: `TABLE_NAME { ... }` ブロックのテーブル名（スネークケース小文字に変換）
- **カラム情報**: 各カラムの型・名前・制約（PK, FK, nullable）
- **リレーション**: テーブル間の外部キー関係

#### ER図の型からLaravel型へのマッピング

| ER図の型 | Laravelのカラムメソッド |
|---|---|
| `string` | `string('column_name')` |
| `int` | `integer('column_name')` または `id()` (PKの場合) |
| `decimal` | `decimal('column_name', 10, 2)` |
| `datetime` | `dateTime('column_name')` |
| `date` | `date('column_name')` |
| `boolean` | `boolean('column_name')` |
| `text` | `text('column_name')` |

#### 制約のマッピング

- `PK` かつ型が `string` → `$table->string('id')->primary()`
- `PK` かつ型が `int` または IDカラム → `$table->id()`
- `FK` → `$table->string('foreign_id')` + `$table->foreign('foreign_id')->references('id')->on('referenced_table')->onDelete('cascade')`
- `PK, FK`（中間テーブル）→ 複合主キー `$table->primary(['col1', 'col2'])`
- nullable でないことがデフォルト。NULL許容が明示されている場合は `->nullable()` を付ける

### 5. マイグレーションファイルの生成

テーブルごとに1ファイル作成します。

#### ファイル名の規則

```
database/migrations/YYYY_MM_DD_HHMMSS_create_<table_name>_table.php
```

- 現在の日時を使用（テーブルごとに1秒ずつインクリメント）
- テーブル名はスネークケース小文字（例: `meeting_rooms`, `reservations`）

#### ファイルテンプレート

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('<table_name>', function (Blueprint $table) {
            // カラム定義
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('<table_name>');
    }
};
```

#### カラム生成ルール

1. PKカラムを最初に定義する
2. 通常カラムを次に定義する
3. FKカラムを最後（`$table->timestamps()` の前）に定義する
4. FKの `foreign()` 制約はカラム定義の直後に続けて記述する
5. `$table->timestamps()` を必ず末尾に追加する

#### 生成例（`RESERVATION` テーブルの場合）

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->string('reservation_id')->primary();
            $table->string('name');
            $table->string('contact_person');
            $table->string('email');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->string('status');
            $table->string('meeting_room_id');
            $table->foreign('meeting_room_id')->references('meeting_room_id')->on('meeting_rooms')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
```

### 6. マイグレーションの実行順序の決定

外部キー制約により、参照先テーブルを先にマイグレーションする必要があります。

- リレーション定義（`TABLE_A ||--o{ TABLE_B`）を解析し、`TABLE_A`（参照される側）を先に作成する
- 依存関係をトポロジカルソートして実行順序を決定する
- ファイル名のタイムスタンプはこの順序に従ってインクリメントする

### 7. マイグレーションファイルの書き出し

決定した順序で各マイグレーションファイルを `database/migrations/` に書き出します。

既存のマイグレーションファイルで同名テーブルのものが存在する場合は、ユーザーに確認してから上書きしてください:

```
以下のマイグレーションファイルが既に存在します:
- <既存ファイル名>

上書きしますか？（yes/no）
```

### 8. マイグレーションの実行

以下のコマンドを実行します:

```bash
php artisan migrate
```

コマンドが失敗した場合は、エラーメッセージをそのままユーザーに伝えてください。

### 9. 完了メッセージ

マイグレーションが成功したら、ユーザーに以下のメッセージを伝えてください:

```
マイグレーションが完了しました。

作成したマイグレーションファイル:
- <ファイル名一覧>

作成されたテーブル:
- <テーブル名一覧>
```

---

## 使い方・実行イメージ

**入力例:**
```
/laravel-migrate 予約管理
/laravel-migrate 会議室管理
```
