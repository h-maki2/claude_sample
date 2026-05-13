---
name: development-guidelines
argument-hint: "[サブドメイン名]"
description: クリーンアーキテクチャ × DDDの実装ガイドライン。ドメインモデル図に基づいた実装方針・設計原則・パターンを提供します。
allowed-tools: Agent, Read, Glob
---

# DDD 実装ガイドライン スキル

## 起動時の処理

1. 引数 `$1` をビジネスサブドメイン名として扱う。空の場合はユーザーに確認する
2. `resolve-subdomain` スキルでサブドメイン英語名を取得する
3. `docs/<英語名>/domain-model.mmd` を読み込む（**すべての実装の根拠。必ず最初に読む**）
   - ファイルが見つからない場合: `domain-model スキルでドメインモデル図を先に作成してください` と伝える

---

## 常に適用する絶対ルール

どのレイヤーを実装するときも例外なく守る:

- ドメイン層はドメインモデル図に**完全準拠**（図にない概念を追加しない）
- クラスに **`final` を付けない**
- **`declare(strict_types=1)` を書かない**
- 依存は **外 → 内のみ**（Controller/Infrastructure → UseCase → Domain）

---

## 今やっていることに応じて読むファイル

ドメインモデル図を確認したあと、**今実装する内容に対応するファイルだけを読む**。

| 今やっていること | 読むファイル |
|---|---|
| ディレクトリ構成・レイヤーの責務を確認したい | `layer-responsibilities.md` |
| ドメイン層（値オブジェクト・集約・ファクトリ・仕様・リポジトリI/F）を実装する | `ddd-patterns.md` |
| ドメインイベントを実装する | `domain-events.md` |
| ユースケース層を実装する | `design-principles.md` |
| 書き込み操作を含むユースケースを実装する | `transaction.md` |
| 複数集約をまたぐデータ取得を設計する | `cqrs.md` |
| プレゼンテーション層（Controller・Presenter・View）を実装する | `presenter-patterns.md` |
| クラス名・メソッド名・コメントに迷っている | `naming-and-comments.md` |
| 他サブドメインのデータや機能を利用する | `inter-subdomain-communication.md` |
