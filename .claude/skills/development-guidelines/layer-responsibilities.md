# ディレクトリ構成と各レイヤーの責務

## ディレクトリ構成（モジュラモノリス）

本プロジェクトはモジュラモノリスを採用しています。ビジネスサブドメインごとに `src/Modules/` 配下にモジュールを作成します。

```
src/
├── app/                                  # Laravel 標準アプリケーション層（共通処理）
│   ├── Domains/
│   │   └── Models/
│   │       └── Share/
│   │           └── DomainEvent/          # サブドメイン横断のドメインイベント共通クラス
│   │               ├── DomainEvent.php
│   │               ├── DomainEventSubscriber.php
│   │               └── DomainEventPublisher.php
│   ├── UseCase/
│   │   └── Share/                        # サブドメイン横断の共通インターフェース
│   │       └── TransactionExecutor.php   # トランザクションインターフェース
│   ├── Http/
│   │   └── Presenters/
│   │       └── Shared/                   # サブドメイン横断の共通プレゼンター
│   │           └── JsonResponseFormatter.php
│   └── Infrastructure/
│       └── Transaction/
│           └── TransactionExecutorImpl.php  # Laravel DB::transaction() の実装
├── tests/                                # 共通テストヘルパー（サブドメイン横断）
│   └── Helpers/
│       └── Infrastructure/
│           └── Transaction/
│               ├── TestTransactionExecutor.php    # 単体テスト用（トランザクションなし）
│               └── FailingTransactionExecutor.php # 統合/Featureテスト用（強制ロールバック）
└── Modules/
    └── <サブドメイン名(英語名)>/
        ├── app/
        │   ├── Contracts/                    # 他サブドメインへの公開 API（必要な場合のみ作成）
        │   │   └── <概念名>/                 # 例: UserProfile
        │   │       ├── <インターフェース名>.php  # 公開インターフェース（命名は概念に応じて自由に）
        │   │       └── <概念名>DTO.php           # データ転送オブジェクト（readonlyプリミティブ型のみ）
        │   ├── Domains/                  # ドメイン層（モデル・ドメインサービス）
        │   │   ├── Models/
        │   │   │   └── <集約名>/         # 集約単位でディレクトリを切る
        │   │   │       ├── <集約名>.php
        │   │   │       ├── <集約名>Id.php
        │   │   │       └── <集約名>RepositoryInterface.php
        │   │   └── Services/             # ドメインサービス
        │   │       └── <ドメインサービス名>.php
        │   ├── UseCase/                  # アプリケーション層（ユースケース）
        │   │   └── <ユースケース名>/      # 業務単位でディレクトリを切る
        │   │       ├── <ユースケース名>UseCase.php
        │   │       └── <ユースケース名>Input.php
        │   ├── Infrastructure/           # インフラ層（DB・メール・外部API）
        │   │   └── Repository/
        │   │       └── Eloquent/
        │   │           └── <集約名>/
        │   │               └── Eloquent<集約名>Repository.php
        │   └── Http/                     # プレゼンテーション層（Controller・Presenter）
        │       ├── Controllers/
        │       │   └── <ユースケース名>/         # ユースケース単位でサブディレクトリを切る
        │       │       ├── <ユースケース名>Controller.php
        │       │       └── <ユースケース名>Request.php   # リクエストがある場合
        │       └── Presenters/
        │           └── <ユースケース名>/
        │               ├── <ユースケース名>Presenter.php
        │               └── Json<ユースケース名>View.php
        └── tests/
            ├── Unit/                     # app/ の構成を鏡のように反映
            │   ├── Domains/
            │   │   ├── Models/
            │   │   │   └── <集約名>/
            │   │   │       └── <クラス名>Test.php
            │   │   └── Services/
            │   │       └── <ドメインサービス名>Test.php
            │   ├── UseCase/
            │   │   └── <ユースケース名>/
            │   │       └── <ユースケース名>UseCaseTest.php
            │   └── Http/
            │       └── Presenters/
            │           └── <ユースケース名>/
            │               └── <ユースケース名>PresenterTest.php
            ├── Integration/              # app/ の構成を鏡のように反映
            │   └── Infrastructure/
            │       └── Repository/
            │           └── Eloquent/
            │               └── <集約名>/
            │                   └── Eloquent<集約名>RepositoryTest.php
            ├── Feature/                  # ユースケース単位（HTTPレベルの結合テスト）
            │   └── <ユースケース名>Test.php
            └── Helpers/                  # テスト用共通ヘルパー（ファクトリ・フェイク等）
                ├── Domains/
                │   └── Models/
                │       └── <集約名>/
                │           ├── Test<集約名>Factory.php
                │           └── <集約名>TestDataCreator.php
                └── Infrastructure/
                    └── Repository/
                        └── InMemory/
                            └── InMemory<集約名>Repository.php
```

### 具体例（会議室管理サブドメイン）

共通処理（トランザクション等）は `src/app/` 配下、モジュール固有のコードは `src/Modules/` 配下に配置します。

```
src/
├── app/
│   ├── Domains/
│   │   └── Models/
│   │       └── Share/
│   │           └── DomainEvent/
│   │               ├── DomainEvent.php           # namespace App\Domains\Models\Share\DomainEvent
│   │               ├── DomainEventSubscriber.php # namespace App\Domains\Models\Share\DomainEvent
│   │               └── DomainEventPublisher.php  # namespace App\Domains\Models\Share\DomainEvent
│   ├── UseCase/
│   │   └── Share/
│   │       └── TransactionExecutor.php          # namespace App\UseCase\Share
│   └── Infrastructure/
│       └── Transaction/
│           └── TransactionExecutorImpl.php       # namespace App\Infrastructure\Transaction
├── tests/
│   └── Helpers/
│       └── Infrastructure/
│           └── Transaction/
│               ├── TestTransactionExecutor.php   # namespace Tests\Helpers\Infrastructure\Transaction
│               └── FailingTransactionExecutor.php
└── Modules/
    └── MeetingRoomManagement/
        ├── app/
        │   ├── Domains/
        │   │   ├── Models/
        │   │   │   └── MeetingRoom/
        │   │   │       ├── MeetingRoom.php
        │   │   │       ├── MeetingRoomId.php
        │   │   │       ├── Capacity.php
        │   │   │       └── MeetingRoomRepositoryInterface.php
        │   │   └── Services/
        │   │       └── MeetingRoomAvailabilityService.php
        │   ├── UseCase/
        │   │   ├── CreateMeetingRoom/
        │   │   │   ├── CreateMeetingRoomUseCase.php
        │   │   │   └── CreateMeetingRoomInput.php
        │   │   └── ListMeetingRooms/
        │   │       └── ListMeetingRoomsUseCase.php
        │   ├── Infrastructure/
        │   │   └── Repository/
        │   │       └── Eloquent/
        │   │           └── MeetingRoom/
        │   │               └── EloquentMeetingRoomRepository.php
        │   └── Http/
        │       ├── Controllers/
        │       │   ├── CreateMeetingRoom/
        │       │   │   ├── CreateMeetingRoomController.php
        │       │   │   └── CreateMeetingRoomRequest.php
        │       │   └── ListMeetingRooms/
        │       │       └── ListMeetingRoomsController.php
        │       └── Presenters/
        │           ├── CreateMeetingRoom/
        │           │   ├── CreateMeetingRoomPresenter.php
        │           │   └── JsonCreateMeetingRoomView.php
        │           └── ListMeetingRooms/
        │               ├── ListMeetingRoomsPresenter.php
        │               └── JsonListMeetingRoomsView.php
        └── tests/
            ├── Unit/
            │   ├── Domains/
            │   │   ├── Models/
            │   │   │   └── MeetingRoom/
            │   │   │       ├── MeetingRoomTest.php
            │   │   │       ├── MeetingRoomIdTest.php
            │   │   │       └── CapacityTest.php
            │   │   └── Services/
            │   │       └── MeetingRoomAvailabilityServiceTest.php
            │   ├── UseCase/
            │   │   ├── CreateMeetingRoom/
            │   │   │   └── CreateMeetingRoomUseCaseTest.php
            │   │   └── ListMeetingRooms/
            │   │       └── ListMeetingRoomsUseCaseTest.php
            │   └── Http/
            │       └── Presenters/
            │           ├── CreateMeetingRoom/
            │           │   └── CreateMeetingRoomPresenterTest.php
            │           └── ListMeetingRooms/
            │               └── ListMeetingRoomsPresenterTest.php
            ├── Integration/
            │   └── Infrastructure/
            │       └── Repository/
            │           └── Eloquent/
            │               └── MeetingRoom/
            │                   └── EloquentMeetingRoomRepositoryTest.php
            ├── Feature/
            │   └── CreateMeetingRoomTest.php
            └── Helpers/
                ├── Domains/
                │   └── Models/
                │       └── MeetingRoom/
                │           ├── TestMeetingRoomFactory.php
                │           └── MeetingRoomTestDataCreator.php
                └── Infrastructure/
                    └── Repository/
                        └── InMemory/
                            └── InMemoryMeetingRoomRepository.php
```

### ドメインイベントのレイヤー配置

| コンポーネント | 配置場所 | namespace |
|---|---|---|
| `DomainEvent` インターフェース | `src/app/Domains/Models/Share/DomainEvent/` | `App\Domains\Models\Share\DomainEvent` |
| `DomainEventSubscriber` インターフェース | `src/app/Domains/Models/Share/DomainEvent/` | `App\Domains\Models\Share\DomainEvent` |
| `DomainEventPublisher` | `src/app/Domains/Models/Share/DomainEvent/` | `App\Domains\Models\Share\DomainEvent` |
| 具体イベントクラス（例: `ReservationCancelled`） | 各モジュールのドメイン層 | `Modules\<SubDomain>\app\Domains\Models\<Aggregate>` |
| 抽象 Subscriber クラス | 各モジュールのドメイン層 | `Modules\<SubDomain>\app\Domains\Models\<Aggregate>` |
| 具体 Subscriber（例: `LaravelReservationCancelledSubscriber`） | 各モジュールのインフラ層 | `Modules\<SubDomain>\app\Infrastructure\<Aggregate>` |

---

# 各レイヤーの責務

## コントローラは1ユースケース = 1クラス（単一責任）

コントローラは **ユースケース単位で1クラス** に分割する。Laravel のリソースコントローラのように CRUD 操作を1クラスに集約してはいけない。

```
# NG: CRUD を1クラスに集約
MeetingRoomController.php
├── index()
├── show()
├── create()
├── store()   ← 登録ユースケース
├── edit()
├── update()  ← 更新ユースケース
└── destroy() ← 削除ユースケース

# OK: ユースケース単位で1クラス、かつユースケース名のサブディレクトリに配置
Controllers/
├── ListMeetingRooms/
│   └── ListMeetingRoomsController.php
├── ShowMeetingRoom/
│   └── ShowMeetingRoomController.php
├── CreateMeetingRoom/
│   ├── CreateMeetingRoomController.php
│   └── CreateMeetingRoomRequest.php
├── UpdateMeetingRoom/
│   ├── UpdateMeetingRoomController.php
│   └── UpdateMeetingRoomRequest.php
└── DeleteMeetingRoom/
    └── DeleteMeetingRoomController.php
```

---

## プレゼンターはユースケース単位のサブディレクトリに配置する

プレゼンターは `Http/Presenters/<ユースケース名>/` のサブディレクトリに配置する。コントローラと同じ業務単位でディレクトリを切ることで、対応関係を明確に保つ。

```
# NG: Presenters/ 直下にフラットに置く
Presenters/
├── ListMeetingRoomsPresenter.php
└── CreateMeetingRoomPresenter.php

# OK: ユースケース単位でサブディレクトリを切る
Presenters/
├── ListMeetingRooms/
│   └── ListMeetingRoomsPresenter.php
└── CreateMeetingRoom/
    └── CreateMeetingRoomPresenter.php
```

対応するテストも同じ構造を `tests/Unit/Http/Presenters/` 配下に反映する:

```
tests/Unit/Http/Presenters/
├── ListMeetingRooms/
│   └── ListMeetingRoomsPresenterTest.php
└── CreateMeetingRoom/
    └── CreateMeetingRoomPresenterTest.php
```

---

## ディレクトリ分割の原則

### 技術駆動ではなく業務単位で分ける

ドメイン層・ユースケース層などの中は、**技術駆動ではなく業務の単位でディレクトリを分ける**。

```
# NG: 技術駆動
UseCase/
├── CreateUseCase.php
├── UpdateUseCase.php
└── DeleteUseCase.php

# OK: 業務単位
UseCase/
├── Order/           # 注文に関するユースケース群
│   ├── PlaceOrder.php
│   └── CancelOrder.php
└── Shipping/        # 出荷に関するユースケース群
    └── ShipOrder.php
```

### DDDパターン種別でディレクトリ分けしない

ドメイン層（`Domains/`）の中を、値オブジェクト・エンティティ・集約などの **DDDパターン種別でディレクトリ分けしてはいけない**。

`Domains/` 直下には `Models/` と `Services/` の2つのサブディレクトリのみを置く。

- **`Domains/Models/`**: 値オブジェクト・エンティティ・集約などのドメインオブジェクト。集約単位でさらにサブディレクトリを切る。
- **`Domains/Services/`**: ドメインサービス（複数の集約にまたがるビジネスロジック）。

```
# NG: DDDパターン種別でディレクトリ分け
Domains/
├── Entities/
│   └── Order.php
├── ValueObjects/
│   ├── OrderId.php
│   ├── Price.php
│   └── Quantity.php
└── Enums/
    └── OrderStatus.php

# OK: Models/ 配下を業務の集約単位で分け、Services/ にドメインサービスを置く
Domains/
├── Models/
│   └── Order/            # 注文集約に関するクラス群をまとめたディレクトリ
│       ├── Order.php         # 集約ルート（エンティティ）
│       ├── OrderItem.php     # エンティティ
│       ├── OrderId.php       # 値オブジェクト
│       ├── Price.php         # 値オブジェクト
│       ├── Quantity.php      # 値オブジェクト
│       ├── OrderStatus.php   # enum
│       ├── OrderRepositoryInterface.php  # リポジトリインターフェース
│       └── OrderNotFoundException.php   # ドメイン例外
└── Services/
    └── PricingDomainService.php  # 複数集約にまたがるドメインサービス
```

### Input DTO は複数パラメータをまとめる場合にのみ作成する

`{ユースケース名}Input.php` は **2つ以上の入力パラメータがある場合にのみ作成する**。
入力が単一のプリミティブ値（IDだけ、など）の場合は `execute()` に直接渡す。

```php
// NG: 引数が1つのプリミティブなのにInputクラスを作る
class DeleteMeetingRoomInput
{
    public function __construct(readonly string $meetingRoomId) {}
}
class DeleteMeetingRoomUseCase
{
    public function execute(DeleteMeetingRoomInput $input): void { ... }
}

// OK: 単一プリミティブは直接渡す
class DeleteMeetingRoomUseCase
{
    public function execute(string $meetingRoomId): void { ... }
}

// OK: 複数パラメータの場合はInputクラスにまとめる
class UpdateMeetingRoomInput
{
    public function __construct(
        readonly string $meetingRoomId,
        readonly string $name,
        readonly int $capacity,
        readonly array $equipments,
    ) {}
}
class UpdateMeetingRoomUseCase
{
    public function execute(UpdateMeetingRoomInput $input): void { ... }
}
```

---

### `Application/` ラッパーを作らない（UseCase は直接 `UseCase/` 配下）

ユースケース層のディレクトリは `UseCase/` が直接のトップであり、`Application/` や `Application/UseCases/` などのラッパーを **挟んではいけない**。

```
# NG: Application/ ラッパーを挟む
Application/
└── UseCases/
    ├── CreateOrder/
    └── CancelOrder/

# OK: UseCase/ が直接のトップ
UseCase/
├── CreateOrder/
│   ├── CreateOrderUseCase.php
│   └── CreateOrderInput.php
└── CancelOrder/
    └── CancelOrderUseCase.php
```

---

## 実装を進める

構成を把握したら、今実装するレイヤーに対応するファイルを読む:

| 実装するレイヤー | 次に読むファイル |
|---|---|
| ドメイン層（値オブジェクト・集約・仕様） | `ddd-patterns.md` |
| ユースケース層（フレームワーク制約・SOLID原則） | `design-principles.md` |
| プレゼンテーション層（Controller・Presenter・View） | `presenter-patterns.md` |

新規機能をフルスタックで実装する場合の順序:
1. ドメイン層 → `ddd-patterns.md`
2. ユースケース層 → `design-principles.md`（書き込みがある場合は `transaction.md` も）
3. インフラ層・プレゼンテーション層 → `presenter-patterns.md`
