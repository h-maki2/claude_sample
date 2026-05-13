**Claude Code のスキル・サブエージェント・スラッシュコマンドを活用した開発自動化のサンプル実装です。**

ドキュメント先行 → TDD 実装というワークフローを、Claude Code の各種機能でどう自動化・標準化できるかを示すことが主目的です。
サンプルドメインとして会議室予約・管理システムを使用していますが、ドメイン自体は本質ではありません。

---

## 目次

1. [プロジェクト概要](#プロジェクト概要)
2. [技術スタック](#技術スタック)
3. [環境構築](#環境構築)
4. [開発ワークフロー](#開発ワークフロー)
5. [スラッシュコマンド](#スラッシュコマンド)
6. [スキル一覧](#スキル一覧)
7. [サブエージェント一覧](#サブエージェント一覧)
8. [ドキュメント構造](#ドキュメント構造)

---

## プロジェクト概要

### このプロジェクトで示すこと

- **スキル** — 作業種別ごとに呼び出すガイドラインをスキルとして定義し、Claude の振る舞いを標準化する
- **サブエージェント** — テスト実行・静的解析・コードレビューを専用エージェントに委譲し、メインの会話コンテキストを汚染しない
- **スラッシュコマンド** — `/create-docs` / `/implement` でドキュメント生成から TDD 実装までのフローを一コマンドで再現可能にする
- **ステアリングファイル** — 作業ごとに `docs/steering/` へ要求・設計・タスクリストを記録し、作業指示を明文化する

### サンプルドメイン（会議室予約・管理）

このリポジトリでは、上記ワークフローの動作確認用ドメインとして会議室予約・管理システムを使用しています。

| サブドメイン | 説明 |
|---|---|
| `MeetingRoomManagement`（会議室管理） | 会議室の登録・変更・削除・一覧・詳細 |
| `ReservationManagement`（予約管理） | 会議室の予約・予約変更・予約キャンセル・予約一覧 |

---

## 技術スタック

| カテゴリ | 技術 |
|---|---|
| 言語 | PHP 8.4 |
| フレームワーク | Laravel 13 |
| モジュール管理 | nwidart/laravel-modules |
| DB | MySQL 8.0 |
| Web サーバー | Nginx |
| コンテナ | Docker / Docker Compose |
| 静的解析 | PHPStan |
| AI エージェント | Claude Code (claude-sonnet-4-6) |

---

## 環境構築

### 前提条件

Docker / Docker Compose がインストール済みであること

### 手順

```bash
# 1. リポジトリをクローン
git clone <repository-url>
cd <repository-dir>

# 2. コンテナを起動
docker compose up -d

# 3. アプリコンテナに入る
docker compose exec app bash

# 4. 依存パッケージをインストール
cd /var/www/html/src
composer install

# 5. 環境ファイルを作成
cp .env.example .env

# 6. アプリキーを生成
php artisan key:generate

# 7. マイグレーションを実行
php artisan migrate
```

### Claude Code の起動

```bash
# コンテナ内から起動
docker compose exec app bash
cd /var/www/html
claude
```

---

## 開発ワークフロー

このプロジェクトは **要件定義 → ドキュメント生成 → TDD 実装** の 3 ステップで開発を進めます。

### ステップ 1 — 要件定義（人間が行う）

ステークホルダーとの対話を通じて要件を整理し、以下の 2 つの成果物を作成します。

- **要件定義メモ** — 機能要件・非機能要件・制約など
- **ユビキタス言語集**（`docs/ubiquitousLanguage/ubiquitous_language.csv`）— ドメイン用語の日英対応と意味

これらは人間が作成する唯一の入力物です。以降のステップはすべてスラッシュコマンドで自動化されます。

### ステップ 2 — `/create-docs` によるドキュメント自動生成

ステップ 1 で作成した要件定義メモとユビキタス言語集をもとに、実装に必要なドキュメント一式を自動生成します。

```
/create-docs <サブドメイン名>
       ↓  PRD・ドメインモデル図・ER図・オブジェクト図・ユースケース図を自動生成
```

### ステップ 3 — `/implement` による実装

ステップ 2 で生成したドキュメントをもとに、ステアリングファイルの作成・TDD 実装・コードレビュー・修正を一括実行します。

```
/implement <サブドメイン名> <実装内容>
       ↓  ステアリングファイル作成 → TDD（Red→Green→Refactor）で実装
       ↓  クリーンアーキテクチャ（Domain / UseCase / Infrastructure / Presentation）に従い層ごとに実装
```

---

### `/create-docs` — ドキュメント一式の生成

新しいサブドメインの開発を始める際に使います。ユビキタス言語 CSV をもとに、以下のドキュメントを `docs/<サブドメイン英語名>/` に自動生成します。

| 生成ファイル | 内容 |
|---|---|
| `product-requirements.md` | プロダクト要求定義書（PRD） |
| `domain-model.mmd` | ドメインモデル図（Mermaid） |
| `ER.mmd` | ER 図（Mermaid） |
| `object-diagram.mmd` | オブジェクト図（Mermaid） |
| `usecase.mmd` | ユースケース図（Mermaid） |

**使い方:**

```
/create-docs <サブドメイン名>
```

**例:**

```
/create-docs NotificationManagement
/create-docs 通知管理
```

### `/implement` — ステアリング作成 + TDD 実装

指定サブドメインの実装内容について、ステアリングファイルを作成してから TDD で実装します。
実装は「ドメイン層 → ユースケース層 → インフラ層 → プレゼンテーション層」の順に層ごとに依頼するのが推奨です。

**使い方:**

```
/implement <サブドメイン名> <実装内容>
```

**例:**

```
/implement MeetingRoomManagement ドメイン層を実装して
/implement MeetingRoomManagement 会議室を登録するユースケース層の処理を実装して
/implement MeetingRoomManagement 会議室登録のインフラ層とプレゼンテーション層を実装して
/implement ReservationManagement 予約を作成するユースケース層を実装して
```

**内部処理の流れ:**

1. `steering` スキルでステアリングファイル（`docs/steering/YYYYMMDD-<タスク名>/`）を作成
   - `requirements.md`：今回の要求
   - `design.md`：実装アプローチ
   - `tasklist.md`：具体的なタスクリスト
2. `development-guidelines` スキルでアーキテクチャ方針を確認
3. `tdd` スキルに従い Red → Green → Refactor サイクルで実装
4. `test-runner` サブエージェントで単体テストを自動実行・確認

---

## スラッシュコマンド

| コマンド | 用途 |
|---|---|
| `/create-docs <サブドメイン名>` | 新規サブドメインのドキュメント一式（PRD・各種図）を生成 |
| `/implement <サブドメイン名> <実装内容>` | ステアリングファイル作成 → TDD 実装まで一括実行 |

---

## スキル一覧

Claude Code のスキルは、特定の作業を始める際に必ず呼び出す**ルール付きのガイドライン**です。

### ドキュメント作成

| スキル | 用途 |
|---|---|
| `prd-writing` | PRD（`product-requirements.md`）作成 |
| `domain-model` | ドメインモデル図（`domain-model.mmd`）生成・更新 |
| `er-diagram` | ER 図（`ER.mmd`）生成 |
| `object-diagram` | オブジェクト図（`object-diagram.mmd`）生成 |
| `usecase-diagram` | ユースケース図（`usecase.mmd`）生成 |

### 開発・実装

| スキル | 用途 | 呼び出しタイミング |
|---|---|---|
| `development-guidelines` | クリーンアーキテクチャ × DDD 実装ガイドライン | プロダクションコードを実装・修正するとき |
| `tdd` | テスト駆動開発（Red→Green→Refactor）サイクル | TDD で実装するとき |
| `test-writing` | テストコード作成ガイドライン | テストコードを作成・修正するとき |
| `steering` | ステアリングファイル作成・実装・振り返り管理 | 作業計画・実装・振り返り時 |
| `laravel-migrate` | ER 図から Laravel マイグレーションファイル作成・実行 | ER 図からマイグレーションを作成するとき |
| `laravel-module` | nwidart/laravel-modules で新モジュールを作成 | 新しいモジュールを作成するとき |

### ユビキタス言語

| スキル | 用途 |
|---|---|
| `ubiquitous-language` | ユビキタス言語集から用語を検索・一覧表示 |
| `resolve-subdomain` | サブドメイン名（日本語/英語）の名前解決（内部共通処理） |

---

## サブエージェント一覧

| サブエージェント | 用途 |
|---|---|
| `test-runner` | テストを実行する。モード1（単体テストのみ）/ モード2（全テスト）/ モード3（特定テスト指定）を自動判別 |
| `code-reviewer` | 実装コードをガイドライン・テスト要件に照らしてレビュー |
| `phpstan-analyser` | PHPStan で静的解析を実行し、エラーを整形して返す |
| `Explore` | コードベース全体を横断的に検索・調査する読み取り専用エージェント |
| `ubiquitous-finder` | ユビキタス言語 CSV から指定サブドメインの用語を抽出 |

---

## ドキュメント構造

```
docs/
├── <サブドメイン英語名>/          # 永続ドキュメント
│   ├── domain-model.mmd          # ドメインモデル図（Mermaid）
│   ├── ER.mmd                    # ER 図（Mermaid）
│   ├── object-diagram.mmd        # オブジェクト図（Mermaid）
│   ├── product-requirements.md   # PRD
│   └── usecase.mmd               # ユースケース図（Mermaid）
├── ideas/                        # 壁打ち・ブレインストーミング（自由形式）
├── steering/                     # 作業単位ドキュメント（作業ごとに新規作成）
│   └── YYYYMMDD-<タスク名>/
│       ├── requirements.md       # 今回の要求内容
│       ├── design.md             # 実装アプローチ
│       └── tasklist.md           # タスクリスト（実装中に更新）
└── ubiquitousLanguage/
    └── ubiquitous_language.csv   # ユビキタス言語集（全サブドメイン共通）
```

### ユビキタス言語 CSV のフォーマット

```
ビジネスサブドメイン, ビジネスサブドメイン英名, 日本語名, 英語名, 意味, 使い方
```

実装時はこの CSV を参照し、ドメイン用語の命名を統一します。
