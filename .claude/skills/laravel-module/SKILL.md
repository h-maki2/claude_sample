---
name: laravel-module
argument-hint: "[サブドメイン名]"
description: nwidart/laravel-modulesで新しいモジュールを作成します。ビジネスサブドメイン名（日本語名 or 英語名）を引数に取り、英語名でモジュールを作成してクリーンアーキテクチャのディレクトリ構造を整備します。
allowed-tools: Agent, Read, Bash, Write, Glob
---

# Laravel Module 作成スキル

このスキルは、ビジネスサブドメイン名をもとに `nwidart/laravel-modules` で新しいモジュールを作成し、クリーンアーキテクチャ × DDD のディレクトリ構造を整備します。

---

## 実行手順

### 1. サブドメイン名の解決

`resolve-subdomain` スキルを呼び出してビジネスサブドメインの英語名を取得します:

```
Skill("resolve-subdomain", "$1")
```

取得した `<英語名>` をモジュール名として以降のすべての処理で使用します。

### 2. composer.json の autoload 設定

`/var/www/html/src/composer.json` に以下の2エントリを登録します:

| セクション | キー | 値 |
|---|---|---|
| `autoload.psr-4` | `"Modules\\<英語名>\\"` | `"Modules/<英語名>/app/"` |
| `autoload-dev.psr-4` | `"Tests\\<英語名>\\"` | `"Modules/<英語名>/tests/"` |

まず既に登録済みか確認します:

```bash
grep -q "Modules\\\\\\\\<英語名>" /var/www/html/src/composer.json
```

登録済みの場合はスキップします。未登録の場合は `jq` で追記します:

```bash
cd /var/www/html/src && jq \
  --arg akey "Modules\\\\<英語名>\\\\" \
  --arg aval "Modules/<英語名>/app/" \
  --arg tkey "Tests\\\\<英語名>\\\\" \
  --arg tval "Modules/<英語名>/tests/" \
  '.autoload["psr-4"][$akey] = $aval | .["autoload-dev"]["psr-4"][$tkey] = $tval' \
  composer.json > composer.json.tmp && mv composer.json.tmp composer.json
```

`autoload-dev` キーが存在しない場合は上記 `jq` コマンドで自動生成されます。`jq` が使えない場合は Read/Edit ツールで `src/composer.json` を直接編集してください。

追記後は `composer dump-autoload` を実行します:

```bash
cd /var/www/html/src && composer dump-autoload
```

追記後はユーザーに以下を伝えてください:

```
composer.json に以下を追加しました:
  autoload.psr-4       : "Modules\<英語名>\": "Modules/<英語名>/app/"
  autoload-dev.psr-4   : "Tests\<英語名>\": "Modules/<英語名>/tests/"
```

### 4. 既存モジュールの確認

`Modules/<英語名>/` ディレクトリが既に存在するか確認します:

```bash
ls /var/www/html/src/Modules/<英語名>/ 2>/dev/null
```

既に存在する場合は、ユーザーに以下のメッセージを伝えてください:

```
モジュール「<英語名>」は既に存在します（/var/www/html/src/Modules/<英語名>/）。
```

### 5. モジュールの作成

`php artisan module:make` コマンドでモジュールを作成します:

```bash
cd /var/www/html/src && php artisan module:make <英語名>
```

コマンドが失敗した場合は、エラーメッセージをそのままユーザーに伝えてください。

---

## 使い方

```
/laravel-module 予約管理
/laravel-module 会議室管理
/laravel-module MeetingRoomManagement
```
