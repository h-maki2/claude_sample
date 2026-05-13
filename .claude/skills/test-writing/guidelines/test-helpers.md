# テストヘルパーパターン

## TestIdFactory（テスト用IDファクトリ）

InMemory リポジトリの `nextId()` が固定IDを返すことでテストを決定的にする。

**命名**: `Test{集約名}IdFactory`
**配置先**: `tests/Helpers/Domains/Models/<集約名>/Test<集約名>IdFactory.php`

```php
class TestMeetingRoomIdFactory
{
    public static function create(): MeetingRoomId
    {
        return new MeetingRoomId('01957b3c-1234-7abc-8def-000000000001');
    }
}
```

InMemory リポジトリの `nextId()` はこのファクトリに委譲する。

```php
class InMemoryMeetingRoomRepository implements MeetingRoomRepository
{
    public function nextId(): MeetingRoomId
    {
        return TestMeetingRoomIdFactory::create();
    }
}
```

---

## TestFactory（テスト用ファクトリ）

必要なプロパティだけを指定してドメインオブジェクトを生成できるようにする。引数の型はプリミティブではなくドメインオブジェクト型を使う。

**命名**: `Test{集約名}Factory`（先頭に `Test` を付ける）
**配置先**: `tests/Helpers/Domains/Models/<集約名>/Test<集約名>Factory.php`

```php
class TestMeetingRoomFactory
{
    public static function create(
        ?MeetingRoomId $id = null,
        ?MeetingRoomName $name = null,
        ?Capacity $capacity = null,
        ?array $equipments = null,
    ): MeetingRoom {
        return new MeetingRoom(
            $id ?? TestMeetingRoomIdFactory::create(),
            $name ?? new MeetingRoomName('第1会議室'),
            $capacity ?? new Capacity(10),
            $equipments ?? [],
        );
    }
}

// テストでは必要なデータだけ指定
$room = TestMeetingRoomFactory::create(name: new MeetingRoomName('大会議室'));
```

---

## InMemory リポジトリ（フェイク）

リポジトリ（書き込み・状態保持が必要な依存）に使う。

**配置先**: `tests/Helpers/Infrastructure/Repository/InMemory/InMemory<集約名>Repository.php`

```php
class InMemoryOrderRepository implements OrderRepositoryInterface
{
    private array $orders = [];

    public function save(Order $order): void
    {
        $this->orders[$order->id()->value()] = $order;
    }

    public function findById(OrderId $id): ?Order
    {
        return $this->orders[$id->value()] ?? null;
    }
}
```

---

## InMemory Fetcher（サブドメイン間参照系のフェイク）

Fetcher クラス（サブドメイン間の参照系インターフェース）は InMemory フェイク + `DtoTestDataCreator` パターンを使う。スタブ（`createStub()` / `willReturn()`）は使わない。

### 構成クラスと配置先

| クラス | 配置先 | 役割 |
|--------|--------|------|
| `*DtoTestDataStore` インターフェース | `Helpers/Contracts/<集約名>/` | DTOを登録するストアの契約 |
| `*DtoTestDataCreator` クラス | `Helpers/Contracts/<集約名>/` | ファクトリ＋store登録を1メソッドに集約 |
| `InMemory*Fetcher` クラス | `Helpers/Infrastructure/Fetcher/InMemory/<集約名>/` | `*Fetcher` と `*DtoTestDataStore` を両方実装 |
| `Eloquent*DtoTestDataStore` クラス | `Helpers/Contracts/<集約名>/Eloquent/` | 統合テスト用のEloquent実装 |

### 実装例

```php
// Helpers/Contracts/MeetingRoom/MeetingRoomDtoTestDataStore.php
interface MeetingRoomDtoTestDataStore
{
    public function store(MeetingRoomDTO $meetingRoomDto): void;
}

// Helpers/Contracts/MeetingRoom/MeetingRoomDtoTestDataCreator.php
class MeetingRoomDtoTestDataCreator
{
    public function __construct(private MeetingRoomDtoTestDataStore $dataStore) {}

    public function create(
        ?string $meetingRoomId = null,
        ?string $name = null,
        ?int $capacity = null,
        ?array $equipments = null,
    ): MeetingRoomDTO {
        $dto = TestMeetingRoomDtoFactory::create(
            meetingRoomId: $meetingRoomId,
            name: $name,
            capacity: $capacity,
            equipments: $equipments,
        );
        $this->dataStore->store($dto);
        return $dto;
    }
}

// Helpers/Infrastructure/Fetcher/InMemory/MeetingRoom/InMemoryMeetingRoomFetcher.php
class InMemoryMeetingRoomFetcher implements MeetingRoomFetcher, MeetingRoomDtoTestDataStore
{
    private array $testData = [];

    public function store(MeetingRoomDTO $meetingRoomDto): void
    {
        $this->testData[$meetingRoomDto->meetingRoomId] = $meetingRoomDto;
    }

    public function fetchById(string $meetingRoomId): ?MeetingRoomDTO
    {
        return $this->testData[$meetingRoomId] ?? null;
    }

    public function fetchAll(): array
    {
        return array_values($this->testData);
    }
}
```

### テストでの使い方

```php
public function setUp(): void
{
    parent::setUp();
    $this->meetingRoomFetcher = new InMemoryMeetingRoomFetcher();
    $this->meetingRoomDtoTestDataCreator = new MeetingRoomDtoTestDataCreator($this->meetingRoomFetcher);
    $this->useCase = new ListReservationsUseCase(
        reservationRepository: $this->reservationRepository,
        meetingRoomFetcher: $this->meetingRoomFetcher,
    );
}

public function test_指定日付の予約一覧を会議室名付きで取得できる(): void
{
    // Given
    $this->meetingRoomDtoTestDataCreator->create(
        meetingRoomId: '01957b3c-1234-7abc-8def-000000000099',
        name: '第1会議室',
    );
    // ...
}
```

統合テスト・フィーチャーテストでは `InMemoryMeetingRoomFetcher` の代わりに `EloquentMeetingRoomDtoTestDataStore` を使う。

---

## TestDataCreator（前提データ永続化ヘルパー）

**用途**: ユースケーステスト・フィーチャーテストで前提データをリポジトリに永続化するための Given フェーズ専用ヘルパー。

**禁止**: リポジトリ永続化テスト（`Eloquent*RepositoryTest`）での使用は禁止。`save()` がテスト対象なのに TestDataCreator 内で隠れてしまうため（後述）。

**命名**: `{集約名}TestDataCreator`
**配置先**: `tests/Helpers/Domains/Models/<集約名>/<集約名>TestDataCreator.php`

```php
class MeetingRoomTestDataCreator
{
    public function __construct(private MeetingRoomRepository $repository) {}

    public function create(
        ?MeetingRoomId $id = null,
        ?MeetingRoomName $name = null,
        ?Capacity $capacity = null,
        ?array $equipments = null,
    ): MeetingRoom {
        $room = TestMeetingRoomFactory::create($id, $name, $capacity, $equipments);
        $this->repository->save($room);
        return $room;
    }
}
```

```php
// 単体テスト: InMemory リポジトリで高速に実行
$creator = new MeetingRoomTestDataCreator(new InMemoryMeetingRoomRepository());

// 統合テスト・フィーチャーテスト: app() 経由で Eloquent リポジトリを渡す
$creator = new MeetingRoomTestDataCreator(app(EloquentMeetingRoomRepository::class));
```

---

## リポジトリ永続化テストのパターン

`Eloquent*RepositoryTest` では **TestDataCreator を使わない**。`save()` が When（テスト対象）であるため、TestDataCreator 内に隠れると何を検証しているか不明確になる。

**正しいパターン**: Factory でドメインオブジェクトを生成 → `repository->save()` を When で直接呼ぶ → `repository->findById()` で検証。

```php
public function test_備品ありで会議室を登録できる(): void
{
    // Given
    $equipments = [Equipment::WHITEBOARD, Equipment::PROJECTOR];
    $meetingRoom = TestMeetingRoomFactory::create(equipments: $equipments);

    // When
    $this->repository->save($meetingRoom);

    // Then
    $saved = $this->repository->findById($meetingRoom->meetingRoomId());
    $this->assertNotNull($saved);
    $this->assertEqualsCanonicalizing($equipments, $saved->equipments());
}
```

---

## 他サブドメインの前提データは Contracts 層経由で作成する

統合テスト・フィーチャーテストで他サブドメインのデータをDBに用意する場合、相手サブドメインのドメイン層（Repository・値オブジェクト）に直接依存してはならない。

```php
// NG: 他サブドメインのドメイン層に直接依存
use Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom\EloquentMeetingRoomRepository;
$meetingRoomRepository = app(EloquentMeetingRoomRepository::class);
// ...

// OK: Contracts 層のテストヘルパー経由で永続化する
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;

$meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
$meetingRoomCreator->create(meetingRoomId: '01957b3c-...');
```

---

## Clock インターフェイスと TestFixedClock

現在日時に依存するロジックのテストでは `new DateTimeImmutable()` を直接使わない。`Clock` インターフェイスを依存として受け取り、テストでは `TestFixedClock` で固定時刻を注入する。

**TestFixedClock 配置先**: `tests/Helpers/Domains/Models/Share/Clock/TestFixedClock.php`

```php
class TestFixedClock implements Clock
{
    public function __construct(private DateTimeImmutable $fixedTime) {}

    public function setTestDateTime(DateTimeImmutable $dateTime): void
    {
        $this->fixedTime = $dateTime;
    }

    public function now(): DateTimeImmutable
    {
        return $this->fixedTime;
    }
}
```

```php
// OK: 固定日時を注入してテストを決定的にする
public function setUp(): void
{
    parent::setUp();
    $this->clock = new TestFixedClock(new DateTimeImmutable('2026-04-30 10:00:00'));
    $this->period = new ReservablePeriod();
}

public function test_当日の日付は受付可能期間内である(): void
{
    // Given: 固定日時の「当日」を指定
    $today = new DateTimeImmutable('2026-04-30');

    // When
    $result = $this->period->isSatisfiedBy($today, $this->clock);

    // Then
    $this->assertTrue($result);
}
```
