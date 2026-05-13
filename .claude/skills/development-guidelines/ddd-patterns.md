# DDDパターン 実装ガイドライン

このファイルは `development-guidelines` スキルから常時読み込まれる DDD ビルディングブロックの実装指針です。
**ドメインモデル図の内容を参照しながら** 各パターンを適用してください。

---

## 値オブジェクト（Value Object）

### ドメインモデル図との対応

`<<ValueObject>>` ステレオタイプのクラスを値オブジェクトとして実装する。
`note for` に記載されたビジネスルールをコンストラクタのバリデーションに反映する。

### PHP実装規約

```php
// OK: readonly のみ宣言。public は付けない（冗長なため）
//     ゲッターメソッド（value() など）は実装しない → $obj->value で直接アクセス
class UserId
{
    public function __construct(
        readonly string $value
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('IDは空にできません');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

// NG: public readonly は冗長（readonly のみで public と同等）
// NG: ゲッターメソッドを実装する（$obj->value で直接アクセスできる）
```

---

## エンティティ（Entity）

### ドメインモデル図との対応

`<<Entity>>` ステレオタイプのクラスをエンティティとして実装する。
できる限りエンティティではなく値オブジェクトで表現できないかを先に検討する（ドメインモデルの複雑さを抑えるため）。

---

## 集約（Aggregate）

### ドメインモデル図との対応

`<<AggregateRoot>>` ステレオタイプのクラスを集約ルートとして実装する。
- `*--`（コンポジション）: 集約内のメンバー
- `-->`（ID参照）: 別集約への参照（IDのみ保持し、直接オブジェクト参照しない）

### PHP実装規約

```php
class MeetingRoom
{
    public function __construct(
        private readonly MeetingRoomId $meetingRoomId, // IDは不変 → private readonly
        private MeetingRoomName $name,                 // 変更可能 → private のみ（readonly 不要）
        private Capacity $capacity,
        private array $equipments,
    ) { ... }

    // 変更可能なプロパティはゲッターで公開する
    public function meetingRoomId(): MeetingRoomId { return $this->meetingRoomId; }
    public function name(): MeetingRoomName { return $this->name; }
    public function capacity(): Capacity { return $this->capacity; }
    public function equipments(): array { return $this->equipments; }

    // 状態変更はドメインメソッドで行う
    public function update(MeetingRoomName $name, Capacity $capacity, array $equipments): void
    {
        $this->name = $name;
        $this->capacity = $capacity;
        $this->equipments = $equipments;
    }
}
```

**プロパティの可視性ルール（集約ルート）:**

| プロパティの種類 | 修飾子 | 外部公開 |
|---|---|---|
| ID（識別子・不変） | `private readonly` | ゲッターメソッド |
| 変更可能な属性 | `private`（readonly なし） | ゲッターメソッド |

---

## ファクトリ（Factory）

### 名前付きコンストラクタの使いどころ

コンストラクタ1つで表現できる場合は `new` で生成する。名前付きコンストラクタは以下の場合のみ導入する:

- **新規生成とDB復元でロジックが異なる場合**
  - 新規生成: バリデーション・不変条件チェック・ID発行を行う
  - DB復元: `reconstruct` などの名前で、バリデーションをスキップして永続化済みの値を復元する

「名前をつけたいだけ」「可読性のため」といった理由での乱造は避ける。

### `create` ファクトリメソッドの生成ルール強制（必須）

集約の `create` ファクトリメソッドを実装する際は、**必ずドメインモデル図の `note for <集約名>` を確認し**、記載されている全ての生成ルールを漏れなく実装すること。ルール違反は即座に `\DomainException` を投げる。

```php
// OK: ドメインモデル図のnoteに記載された生成ルールを全て強制する
public static function create(
    ReservationId $id,
    ReservationTimeRange $timeRange,
    BufferTime $bufferTime,
    ReservablePeriod $reservablePeriod,
    ReservationList $reservationList,
    Clock $clock,
): self {
    // バッファタイム違反チェック（仕様クラスはisSatisfied=true → OK, false → 例外）
    if (!$bufferTime->isSatisfiedBetween($reservationList, $timeRange)) {
        throw new \DomainException('予約の前後には最低10分のバッファタイムが必要です。');
    }

    // 予約可能期間チェック
    if (!$reservablePeriod->isSatisfiedBy($timeRange, $clock)) {
        throw new \DomainException('予約は本日から2週間先までの期間で行う必要があります。');
    }

    // 重複チェック
    if ($reservationList->isOverlapping($timeRange)) {
        throw new \DomainException('指定された時間帯は既に予約されています。');
    }

    return new self($id, $timeRange, ReservationStatus::CONFIRMED);
}
```

**実装チェックリスト（`create` を書くたびに確認）:**

1. ドメインモデル図の `note for <集約名>` を開き、生成ルールを全て書き出す
2. 各ルールに対応するチェックを `create` の冒頭に実装する
3. 仕様クラス（`<<Specification>>`）を使う場合: `isSatisfiedBy` が `true` = 満たす、`false` = 違反。例外は `!isSatisfiedBy(...)` のときに投げる
4. `reconstruct` にはバリデーションを書かない（DB復元はルール適用済み）

---

## ドメインサービス（Domain Service）

名前はドメインの言葉を使う（`ReservationDomainService` より `ReservationConflictChecker` など具体的な名前）。

---

## リポジトリ（Repository）

- `findAll()` は作らない。必要な絞り込み条件を明示する

```php
// インターフェース例（ドメイン層）
interface ReservationRepository {
    public function findById(ReservationId $id): ?Reservation;
    public function findByMeetingRoomAndTimeRange(MeetingRoomId $roomId, ReservationTimeRange $range): array;
    public function save(Reservation $reservation): void;
    public function delete(ReservationId $id): void;
}
```

---

## 仕様クラス（Specification）

### ドメインモデル図との対応

`<<Specification>>` ステレオタイプのクラスを仕様として実装する（集約との関係が `-->` で表現されたもの）。

---

## ドメインイベント（Domain Event）

実装規約・コード例・テスト戦略（Spy パターン）・アンチパターンは `domain-events.md` に一元管理されています。

---

## アンチパターン（プロジェクト固有規約違反）

| アンチパターン | 対処 |
|---|---|
| 値オブジェクトに `public readonly` と書く | `readonly` のみにする（`public` は冗長） |
| 値オブジェクトにゲッターメソッドを実装する | `$obj->value` で直接アクセスする（値オブジェクトはすべて不変なので直接公開で問題ない） |
| 集約ルートの変更可能プロパティに `readonly` を付ける | `private` のみにして、ゲッターメソッドで公開する |
| 集約ルートのプロパティを `public` にする | `private` にしてゲッターメソッドで公開する（外部から直接書き換えられないようにする） |
| 名前付きコンストラクタの乱造 | 新規生成とDB復元の区別が必要な場合のみ `reconstruct` などを使う |
| ドメインイベントのアンチパターン | `domain-events.md` を参照 |

---

## 関連ドキュメント

- ドメインイベントを使う場合 → `domain-events.md`
- クラス名・メソッド名に迷った場合 → `naming-and-comments.md`
