# 設計原則（プロジェクト固有ルール）

このプロジェクトにおける設計原則の固有適用ルールをまとめます。

---

## アーキテクチャ適用ルール

- 1ユースケース = 1操作（複数のビジネス操作を1ユースケースに詰め込まない）
- ドメイン層はインフラ層を直接参照しない（リポジトリインターフェース経由）
- 外部サービス依存は Port インターフェースで抽象化し、実装はインフラ層
- ユースケースはドメインオブジェクトの組み立て・調整に専念し、ビジネスルールはドメイン層へ委ねる
- プレゼンテーション層はデータ受け渡しに専念し、ビジネスロジックを持たない

---

## フレームワーク制約（最重要）

**ドメイン層・ユースケース層は Laravel などのフレームワークに依存してはならない。**

### 禁止事項

| 層 | 禁止例 | 理由 |
|---|---|---|
| Domain | `Str::uuid7()`, `Carbon::now()`, Eloquent | フレームワーク依存でドメインが汚染される |
| UseCase | `Str::uuid7()`, `request()`, `Auth::user()` | ユースケースはフレームワークを知らない |

```php
// NG: UseCase層でフレームワークを使う
class CreateMeetingRoomUseCase
{
    public function execute(CreateMeetingRoomInput $input): void
    {
        $id = new MeetingRoomId((string) Str::uuid7()); // ← フレームワーク依存！
    }
}

// OK: ID生成はリポジトリに委ねる
class CreateMeetingRoomUseCase
{
    public function execute(CreateMeetingRoomInput $input): void
    {
        $id = $this->meetingRoomRepository->nextId(); // ← フレームワーク非依存
    }
}
```

### ID生成のパターン（必須）

フレームワーク依存のID生成（`Str::uuid7()` など）は **リポジトリの `nextId()` メソッドに委譲する**。

```php
// ドメイン層: インターフェースに nextId() を定義
interface MeetingRoomRepository
{
    public function findById(MeetingRoomId $id): ?MeetingRoom;
    public function save(MeetingRoom $meetingRoom): void;
    public function delete(MeetingRoomId $id): void;
    public function nextId(): MeetingRoomId;
}

// インフラ層: Laravelを使ってID生成（フレームワーク依存はここだけに閉じ込める）
class EloquentMeetingRoomRepository implements MeetingRoomRepository
{
    public function nextId(): MeetingRoomId
    {
        return new MeetingRoomId((string) Str::uuid7());
    }
}
```

テスト用フェイク（InMemory）における `nextId()` の実装は `test-writing` スキルを参照。

---

## デメテルの法則：例外判断

**値オブジェクトのチェーン**（`reservation.timeRange().startAt()`）は許容する。  
判断基準: 「内部構造を外部に露出しているか」

---

## Tell, Don't Ask の適用

- `getStatus()` を外部で条件分岐するのではなく `cancel()` `complete()` などの意図を表すメソッドを用意する
- ビジネスロジックをドメインオブジェクトにカプセル化する

---

## Fail Fast の適用

- 値オブジェクトのコンストラクタで即バリデーション（不正な状態で生成させない）
- 不正な状態遷移（キャンセル済みを再キャンセルなど）はメソッド冒頭でガード節

```php
class ReservationName {
    private function __construct(private string $value) {
        if (empty($value)) {
            throw new InvalidArgumentException('予約名は空にできません');
        }
        if (mb_strlen($value) > 50) {
            throw new InvalidArgumentException('予約名は50文字以内にしてください');
        }
    }
}
```

---

## DRY の判断軸

「この2箇所は同じ理由で変更されるか？」→ YES なら共通化、NO なら別管理  
コードの見た目が似ていても変更理由が異なれば別物として扱う（SRP との兼ね合い）。

---

## YAGNI の適用

ドメインモデル図に存在しない概念をコードに先取りして追加しない。

---

## PHPコーディング規約

### `declare(strict_types=1)` 禁止

**PHPファイルの先頭に `declare(strict_types=1);` を絶対に記述しない。**

```php
// NG: 絶対に書かない
<?php

declare(strict_types=1);

namespace Modules\MeetingRoomManagement\Domains\Models\MeetingRoom;

// OK: そのまま名前空間から始める
<?php

namespace Modules\MeetingRoomManagement\Domains\Models\MeetingRoom;
```

---

## 関連ドキュメント

- 書き込み操作を含むユースケースを実装する場合 → `transaction.md`
- 複数集約をまたぐデータ取得を設計する場合 → `cqrs.md`
