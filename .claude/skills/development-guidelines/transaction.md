# トランザクション

## 概要

複数の集約や複数のリポジトリ操作を**アトミックに実行**したい場合、`TransactionExecutor` インターフェースを使ってトランザクションを制御します。

UseCase 層はインフラ（DB）に依存してはいけないため、インターフェースを `App\UseCase\Share` に定義し、実装を `App\Infrastructure\Transaction` に置きます。

---

## 各クラスの実装

### インターフェース（UseCase 層 / 共通）

```php
namespace App\UseCase\Share;

interface TransactionExecutor
{
    public function perform(callable $callback): void;
}
```

### 本番実装（Infrastructure 層 / 共通）

```php
namespace App\Infrastructure\Transaction;

use App\UseCase\Share\TransactionExecutor;
use Illuminate\Support\Facades\DB;

class TransactionExecutorImpl implements TransactionExecutor
{
    public function perform(callable $callback): void
    {
        DB::transaction($callback);
    }
}
```

### 単体テスト用（トランザクションなし）

```php
namespace Tests\Helpers\Infrastructure\Transaction;

use App\UseCase\Share\TransactionExecutor;

class TestTransactionExecutor implements TransactionExecutor
{
    public function perform(callable $callback): void
    {
        $callback();
    }
}
```

### 統合テスト・Feature テスト用（強制ロールバック）

```php
namespace Tests\Helpers\Infrastructure\Transaction;

use App\UseCase\Share\TransactionExecutor;
use Exception;
use Illuminate\Support\Facades\DB;

class FailingTransactionExecutor implements TransactionExecutor
{
    public function perform(callable $callback): void
    {
        DB::transaction(function () use ($callback) {
            $callback();
            throw new Exception('force rollback');
        });
    }
}
```

---

## UseCase での使い方

`TransactionExecutor` をコンストラクタインジェクションして、更新処理全体を `perform()` に渡します。

```php
namespace Modules\MeetingRoomManagement\UseCase\UpdateMeetingRoom;

use App\UseCase\Share\TransactionExecutor;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepositoryInterface;

class UpdateMeetingRoomUseCase
{
    public function __construct(
        private MeetingRoomRepositoryInterface $meetingRoomRepository,
        private TransactionExecutor $transactionExecutor,
    ) {}

    public function execute(UpdateMeetingRoomInput $input): void
    {
        $this->transactionExecutor->perform(function () use ($input) {
            $meetingRoom = $this->meetingRoomRepository->findById($input->id);
            $meetingRoom->update($input->name, $input->capacity);
            $this->meetingRoomRepository->save($meetingRoom);
        });
    }
}
```

---

## DI バインディング

`AppServiceProvider`（または専用の `InfrastructureServiceProvider`）で実装をバインドします。

```php
use App\UseCase\Share\TransactionExecutor;
use App\Infrastructure\Transaction\TransactionExecutorImpl;

$this->app->bind(TransactionExecutor::class, TransactionExecutorImpl::class);
```

---

## テストでの使い方

### 単体テスト（UseCase テスト）

`TestTransactionExecutor` を直接インスタンス化して注入します。DB に依存しないため高速に実行できます。

```php
use Tests\Helpers\Infrastructure\Transaction\TestTransactionExecutor;

$useCase = new UpdateMeetingRoomUseCase(
    meetingRoomRepository: new InMemoryMeetingRoomRepository(),
    transactionExecutor: new TestTransactionExecutor(),
);
```

### 統合テスト・Feature テスト

`FailingTransactionExecutor` を使うと、コールバック実行後に強制ロールバックするため、テスト間でデータが汚染されません。

```php
use Tests\Helpers\Infrastructure\Transaction\FailingTransactionExecutor;

$useCase = new UpdateMeetingRoomUseCase(
    meetingRoomRepository: new EloquentMeetingRoomRepository(),
    transactionExecutor: new FailingTransactionExecutor(),
);
```

---

## トランザクションを使うかどうかの判断

### 🚨 原則: Command（更新系）は常にトランザクションを使う

> **UseCase の `execute()` が1つでも書き込み操作を含む場合、必ず `TransactionExecutor` を使うこと。**

| ケース | トランザクション |
|--------|----------------|
| Command（書き込みを含む execute()） | **常に必要** |
| 参照系クエリのみ（Query） | 不要 |