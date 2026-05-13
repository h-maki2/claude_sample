---
name: tdd
argument-hint: "[サブドメイン名] [実装対象のファイルパスまたはクラス名]"
description: テスト駆動開発（TDD）のガイドライン。Red→Green→Refactorサイクルに従い、test-writingスキルを使ってテストを先に書き、実装を進めます。
allowed-tools: Read, Glob, Grep, Edit, Write, Bash, Agent, Skill
---

# TDD（テスト駆動開発）スキル

Red → Green → Refactor サイクルに従って実装を進めます。
テストコードは `test-writing` スキル、プロダクションコードは `development-guidelines` スキルのガイドラインに従います。

---

## 起動時の処理

1. `$1`（ビジネスサブドメイン名）が空なら確認する
2. `$2`（実装対象のファイルパスまたはクラス名）が空なら確認する

---

## TDDサイクル

### 🔴 Red

1. `Skill('test-writing', '<サブドメイン名> <実装対象>')` を呼び出してテストを作成する
2. テストを実行して失敗を確認する
   ```
   Agent(subagent_type='test-runner', prompt='path:Modules/<英語名>/tests/Unit/...')
   ```
   **重要**: パスは `Modules/` から始める。`src/Modules/` は誤り（`src/` 不要）。

### 🟢 Green

1. `Skill('development-guidelines')` を呼び出し、実装方針を確認する
2. テストを通す最小限の実装をする
3. 対象テストの成功を確認する
   ```
   Agent(subagent_type='test-runner', prompt='path:Modules/<英語名>/tests/Unit/...')
   ```
   **重要**: パスは `Modules/` から始める。`src/Modules/` は誤り（`src/` 不要）。
4. 既存の単体テスト全体でデグレがないことを確認する
   ```
   Agent(subagent_type='test-runner', prompt='<サブドメイン名> unit')
   ```

### 🔵 Refactor

1. コードを改善する
2. 全単体テストの通過を確認する
   ```
   Agent(subagent_type='test-runner', prompt='<サブドメイン名> unit')
   ```
