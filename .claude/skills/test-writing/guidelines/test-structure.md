# テスト構造ガイドライン

## TestCase の継承

| テスト種別 | 配置先 | 継承クラス |
|-----------|--------|-----------|
| 単体テスト | `tests/Unit/` | `PHPUnit\Framework\TestCase` |
| 統合テスト | `tests/Integration/` | `Tests\TestCase` |
| フィーチャーテスト | `tests/Feature/` | `Tests\TestCase` |

単体テストで `Tests\TestCase` を使うのは禁止。

---

## 統合テストは write 系ユースケースにのみ作成する

- `save()` / `delete()` を含むユースケース → 統合テストを作成する
- 読み取りのみのユースケース → **統合テストは不要**（単体テストで十分）

読み取りユースケースは InMemory リポジトリを使った単体テストで振る舞いを検証する。

---

## setUp で共通インスタンスを初期化する

`setUp()` には依存オブジェクト（リポジトリ・ユースケース・ヘルパー）のみ書く。テストごとの入力値・エンティティは各テストメソッド内の Given に書く。`parent::setUp()` を先頭で必ず呼ぶ。ヘルパーメソッド（`makeUseCase()` など）でインスタンス生成をラップするパターンは使わない。

```php
class CreateMeetingRoomUseCaseTest extends TestCase
{
    private InMemoryMeetingRoomRepository $meetingRoomRepository;
    private CreateMeetingRoomUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->meetingRoomRepository = new InMemoryMeetingRoomRepository();
        $this->useCase = new CreateMeetingRoomUseCase(
            meetingRoomRepository: $this->meetingRoomRepository,
            transactionExecutor: new TestTransactionExecutor(),
        );
    }
}
```

### 統合テスト・フィーチャーテストは `app()` でインスタンスを取得する

`Tests\TestCase` を継承するテストでは、`new` で直接生成するとサービスコンテナのバインディングが通らない。

```php
// OK: app() で取得
public function setUp(): void
{
    parent::setUp();
    $this->repository = app(EloquentMeetingRoomRepository::class);
    $this->useCase = app(ChangeReservationUseCase::class);
}
```

依存を差し替えたい場合は `app()` より前に `$this->app->instance()` を呼ぶ。

```php
// OK: Clock だけ差し替え、他はプロダクションのバインディングを維持
public function setUp(): void
{
    parent::setUp();
    $this->app->instance(Clock::class, new TestFixedClock(new DateTimeImmutable('2026-05-01 09:00:00')));
    $this->useCase = app(ChangeReservationUseCase::class);
}
```

---

## Given-When-Then パターン

すべてのテストに適用する。3フェーズのコメントを必ず記述する。

```php
// 単体テスト
public function test_有効な入力で会議室を登録できる(): void
{
    // Given
    $input = new CreateMeetingRoomInput(name: '第1会議室', capacity: 10, equipments: []);

    // When
    $this->useCase->execute($input);

    // Then
    $saved = $this->meetingRoomRepository->findById(TestMeetingRoomIdFactory::create());
    $this->assertNotNull($saved);
}
```

```php
// 統合テスト（Eloquentリポジトリ永続化）
public function test_備品なしで会議室を登録できる(): void
{
    // Given
    $name = '第1会議室';
    $capacity = 10;
    $meetingRoom = TestMeetingRoomFactory::create(
        name: new MeetingRoomName($name),
        capacity: new Capacity($capacity),
        equipments: [],
    );

    // When
    $this->repository->save($meetingRoom);

    // Then
    $saved = $this->repository->findById($meetingRoom->meetingRoomId());
    $this->assertNotNull($saved);
    $this->assertSame($name, $saved->name()->value);
    $this->assertSame($capacity, $saved->capacity()->value);
}
```

```php
// フィーチャーテスト
public function test_会議室を登録できる(): void
{
    // Given
    $name = '第1会議室';
    $capacity = 10;

    // When
    $response = $this->postJson('/api/meeting-rooms', [
        'name' => $name,
        'capacity' => $capacity,
        'equipments' => [],
    ]);

    // Then（assertDatabaseHas は使わず、リポジトリ経由で検証する）
    $response->assertStatus(201);
    $rooms = $this->repository->findAll();
    $this->assertSame($name, $rooms[0]->name()->value);
    $this->assertSame($capacity, $rooms[0]->capacity()->value);
}
```

> フィーチャーテストでは `assertDatabaseHas` を使わない。ドメイン層のマッピングが壊れていても通過してしまうため。

---

## テスト命名規則

日本語・ユビキタス言語でビジネスの振る舞いを記述する。実装の詳細（プロパティ名・例外クラス名・メソッド名）はメソッド名に含めない。

```php
// OK
public function test_割引率20パーセントの受注の請求金額が正しく算出される(): void {}
public function test_終了時刻が開始時刻より前の場合は例外が発生する(): void {}

// NG: 例外クラス名という実装の詳細を含んでいる
public function test_終了時刻が開始時刻より前の場合はInvalidArgumentExceptionが発生する(): void {}

// NG: プロパティ名 `value` という実装の詳細を含んでいる
public function test_プロパティvalueで文字列値を取得できる(): void {}
```

ユビキタス言語は `ubiquitous-language` スキルで確認する。
