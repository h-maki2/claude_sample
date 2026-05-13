---
name: test-writing
argument-hint: "[サブドメイン名] [テスト対象のファイルパスまたはクラス名]"
description: テストコードの書き方ガイドライン。価値あるテストの4本柱に基づいた原則・パターン・アンチパターンを提供し、実際のテストコード作成を支援します。
allowed-tools: Agent, Read, Glob, Grep, Edit, Write, Bash
---

# テストコード作成スキル

## 起動時の処理

1. `resolve-subdomain` スキルでサブドメインの英語名を取得する（`Skill("resolve-subdomain", "$1")`）
2. 以下のディレクトリが存在しなければ作成する（`Unit/` と `Integration/` のサブ構成は `app/` に合わせる）:
   - `src/Modules/<英語名>/tests/Unit`
   - `src/Modules/<英語名>/tests/Integration`
   - `src/Modules/<英語名>/tests/Feature`
   - `src/Modules/<英語名>/tests/Helpers`
3. 引数 `$2` をテスト対象として扱う。空の場合は「テスト対象のファイルやクラスを教えてください」と質問する
4. テスト対象のソースコードを読み込み、公開メソッド・ビジネスロジックの境界・依存コンポーネントを把握する
5. 以下のガイドラインを読んでからテストコードを生成する

---

## ガイドラインの読み込み順

テストの種類に応じて必要なファイルを読む。

| 状況 | 読むファイル |
|------|------------|
| どの TestCase を継承するか・統合テストが必要か・Given-When-Then の書き方・setUp・命名 | `.claude/skills/test-writing/guidelines/test-structure.md` |
| Factory・InMemory リポジトリ・TestDataCreator・Fetcher・Clock などのヘルパーが必要 | `.claude/skills/test-writing/guidelines/test-helpers.md` |
| テストコードが完成したら（書き終えたあと必ず確認） | `.claude/skills/test-writing/guidelines/antipatterns.md` |

**原則として3ファイルすべてを読んでからコードを生成すること。**

---

## ディレクトリ対応

テストディレクトリは `src/Modules/<英語名>/app/` のディレクトリ構成を反映する。詳細は `development-guidelines` スキルの「ディレクトリ構成（モジュラモノリス）」を参照。
