# CQS / CQRS パターン 実装ガイドライン

このファイルは `development-guidelines` スキルから参照される CQS/CQRS パターンの詳細です。

---

## データ取得における CQRS 適用方針

### 基本原則：リポジトリを優先する

表示用のデータ取得は、**まずリポジトリで実装する**。CQRSのQueryサービスは複雑性を増すため、単純なケースには使わない。

| ケース | 採用するアプローチ |
|---|---|
| 単一集約のデータ取得 | リポジトリ（`Repository`） |
| 複数リポジトリを組み合わせる必要がある | QueryサービスによるCQRS |

### 判断フロー

```
表示用データを取得したい
  ↓
単一リポジトリで取得できる？
  ├─ YES → リポジトリを使う（CQRS不要）
  └─ NO（複数集約をJOIN/結合が必要）→ QueryサービスでCQRS適用
```

### なぜリポジトリを優先するか

- 単純な一覧・詳細取得にはその複雑性のコストが見合わない
- リポジトリで賄えない複雑なデータ結合が発生した時点でCQRSに切り替える

---

## CQS（Command Query Separation）

### 例外的な許容ケース

```php
// ドメインイベントの取り出しはCQS違反だが実用上許容する
public function pullDomainEvents(): array {
    // CQS violation: 取り出しと同時にリストをクリアする
    // ドメインイベントの発行パターン上、やむを得ない設計
    $events = $this->domainEvents;
    $this->domainEvents = [];
    return $events;
}
```

---

## CQRS（Command Query Responsibility Segregation）

### 更新系（Command側）と参照系（Query側）の役割

| | Command（更新系） | Query（参照系） |
|---|---|---|
| 配置 | `Domains/Models/`, `UseCase/` | `UseCase/`（インターフェース・参照系モデル） |
| データソース | リポジトリ（集約経由） | クエリサービス（DBを直接参照可） |

### 参照系モデルの実装指針

```
- 参照系モデルはドメインモデル図に含めない
  → ドメインモデル図は更新系（ビジネスルール）の表現に集中させる
```

### ディレクトリ構成

```
UseCase/                               # コマンド系（更新）+ クエリ系インターフェース
├── CreateReservation/
│   ├── CreateReservationUseCase.php
│   └── CreateReservationInput.php
├── CancelReservation/
│   ├── CancelReservationUseCase.php
│   └── CancelReservationInput.php
└── ReservationList/                   # クエリ系（参照）
    ├── ReservationListQuery.php       # クエリサービスのインターフェース
    ├── ReservationDetailQuery.php
    └── ReservationListItem.php        # 参照系モデル（画面専用DTO）

Infrastructure/
└── QueryService/                      # クエリサービスの実装
    ├── EloquentReservationListQuery.php
    └── EloquentReservationDetailQuery.php
```

### 実装例

```php
// 参照系モデル（ユースケース専用DTO）
class ReservationListItem {
    public function __construct(
        readonly string $reservationId,
        readonly string $reservationName,
        readonly string $meetingRoomName,
        readonly DateTimeImmutable $startAt,
        readonly DateTimeImmutable $endAt,
        readonly string $status,
    ) {}
}

// クエリサービスのインターフェース（Application層）
interface ReservationListQuery {
    /** @return ReservationListItem[] */
    public function findByStatus(ReservationStatus $status): array;
}

// クエリサービスの実装（Infrastructure層）
class EloquentReservationListQuery implements ReservationListQuery {
    public function findByStatus(ReservationStatus $status): array {
        return ReservationModel::query()
            ->join('meeting_rooms', ...)
            ->where('status', $status->value)
            ->get()
            ->map(fn($row) => new ReservationListItem(...))
            ->toArray();
    }
}
```
