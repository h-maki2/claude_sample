# サブドメイン間の通信（コントラクト結合）

## 基本ルール

モジュラモノリスにおいて、サブドメイン間の結合は **コントラクト結合** のみを許可する。  
あるサブドメインが他サブドメインの内部実装（`Domains/`・`UseCase/`・`Infrastructure/` の具体クラス）に直接依存してはいけない。

> **コントラクト結合**: 依存先サブドメインが `Contracts/` に公開したインターフェースのみを介して連携する方式。実装の詳細は完全に隠蔽される。

---

## ディレクトリ構成

他サブドメインに機能を公開するサブドメイン（例: User）は `Contracts/` ディレクトリを `app/` の中に設ける。

```
src/Modules/User/
├── app/
│   ├── Contracts/                                      # 他サブドメインへの公開 API（必要な場合のみ作成）
│   │   └── UserProfile/
│   │       ├── UserProfileFetcher.php                  # 公開インターフェース（名前は概念に応じて自由に）
│   │       └── UserProfileDTO.php                      # データ転送オブジェクト
│   ├── Domains/                                        # 内部実装（他サブドメインから直接参照禁止）
│   ├── UseCase/
│   ├── Infrastructure/
│   │   └── UserProfile/
│   │       └── EloquentUserProfileFetcher.php          # Contracts の実装クラス
│   └── Http/
└── tests/
```

`app/Contracts/` は当該サブドメインの「公開 API」であり、他サブドメインが参照して良い唯一の場所。

---

## 実装例（User → Order の方向で公開する場合）

### Step 1: `app/Contracts/` にインターフェースと DTO を定義する（Userサブドメイン側）

```php
// src/Modules/User/app/Contracts/UserProfile/UserProfileFetcher.php
namespace App\Modules\User\Contracts\UserProfile;

interface UserProfileFetcher
{
    public function fetchById(int $userId): ?UserProfileDTO;
}
```

```php
// src/Modules/User/app/Contracts/UserProfile/UserProfileDTO.php
namespace App\Modules\User\Contracts\UserProfile;

class UserProfileDTO
{
    public function __construct(
        readonly int $id,
        readonly string $name,
        readonly string $email,
    ) {}
}
```

**DTO 設計原則:**
- すべてのプロパティは `readonly` にする
- プリミティブ型のみ使用する（ドメインオブジェクト・Eloquentモデルを含めない）
- `app/Contracts/` 内で完結させる（内部の `Domains/` クラスを参照しない）

---

### Step 2: インフラ層でインターフェースを実装する（Userサブドメイン側）

```php
// src/Modules/User/app/Infrastructure/UserProfile/Fetcher/EloquentUserProfileFetcher.php
namespace App\Modules\User\Infrastructure\UserProfile\Fetcher;

use App\Modules\User\Contracts\UserProfile\UserProfileFetcher;
use App\Modules\User\Contracts\UserProfile\UserProfileDTO;

class EloquentUserProfileFetcher implements UserProfileFetcher
{
    public function fetchById(int $userId): ?UserProfileDTO
    {
        // 内部実装の詳細（Eloquent等）はここに閉じ込める
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        return new UserProfileDTO(
            id: $user->id,
            name: $user->name,
            email: $user->email,
        );
    }
}
```

実装クラスは `app/Contracts/` のインターフェースと DTO のみを参照し、内部ドメインオブジェクトをそのまま外部に渡さない。

---

### Step 3: サービスプロバイダーでバインドする（Userサブドメイン側）

```php
// src/Modules/User/app/Providers/UserServiceProvider.php
namespace App\Modules\User\Providers;

use App\Modules\User\Contracts\UserProfile\UserProfileFetcher;
use App\Modules\User\Infrastructure\UserProfile\Fetcher\EloquentUserProfileFetcher;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserProfileFetcher::class, EloquentUserProfileFetcher::class);
    }
}
```

---

### Step 4: 利用側でインターフェースのみに依存する（Orderサブドメイン側）

```php
// src/Modules/Order/app/UseCase/CreateOrder/CreateOrderUseCase.php
namespace App\Modules\Order\UseCase\CreateOrder;

use App\Modules\User\Contracts\UserProfile\UserProfileFetcher;

class CreateOrderUseCase
{
    public function __construct(
        private UserProfileFetcher $userProfileFetcher,
        // ...
    ) {}

    public function execute(CreateOrderInput $input): void
    {
        $userProfile = $this->userProfileFetcher->fetchById($input->userId);

        if (!$userProfile) {
            throw new \RuntimeException('ユーザーが見つかりません');
        }

        // $userProfile->name などを使って後続の処理を行う
    }
}
```

---

## OK / NG パターン

### NG: 他サブドメインの内部クラスに直接依存する

```php
// NG: Userサブドメインの内部実装を直接参照している
use App\Modules\User\app\Domains\Models\User\User;
use App\Modules\User\app\Infrastructure\Repository\Eloquent\User\EloquentUserRepository;
```

### NG: 他サブドメインのUseCaseを直接呼び出す

```php
// NG: UseCaseはサブドメイン固有の業務フロー。外部から直接呼び出してはいけない
use App\Modules\User\UseCase\GetUser\GetUserUseCase;

class CreateOrderUseCase
{
    public function __construct(private GetUserUseCase $getUserUseCase) {}
}
```

### OK: `app/Contracts/` に公開されたインターフェースのみに依存する

```php
// OK: app/Contracts に公開されたインターフェースのみを参照
use App\Modules\User\Contracts\UserProfile\UserProfileFetcher;
```

---

## テスト戦略

`UserProfileFetcher` は外部サブドメインへの依存なので、ユースケーステストでは PHPUnit の `createMock` を使う（InMemoryフェイクは作らない）。

### DTO ファクトリ

DTO の生成には、公開サブドメイン（User）側が提供するファクトリクラスを使う。

```php
// tests/User/Helpers/Contracts/UserProfile/TestUserProfileDtoFactory.php
namespace Tests\User\Helpers\Contracts\UserProfile;

use App\Modules\User\Contracts\UserProfile\UserProfileDTO;

class TestUserProfileDtoFactory
{
    public static function create(
        ?string $id = null,
        ?string $name = null,
        ?string $email = null
    ): UserProfileDTO {
        return new UserProfileDTO(
            id: $id ?? 'testId',
            name: $name ?? 'test-user-name',
            email: $email ?? 'test@example.com'
        );
    }
}
```

### スタブの作成

特定の `userId` に対して特定の DTO を返すスタブは `willReturnMap` で表現する。

```php
// Orderサブドメインの CreateOrderUseCase テスト
use Tests\User\Helpers\Contracts\UserProfile\TestUserProfileDtoFactory;

$userProfile = TestUserProfileDtoFactory::create(id: 'user-1');

$userProfileFetcher = $this->createMock(UserProfileFetcher::class);
$userProfileFetcher->method('fetchById')
    ->willReturnMap([
        ['user-1', $userProfile],
    ]);

$useCase = new CreateOrderUseCase($userProfileFetcher, /* ... */);
```

`willReturnMap` の各エントリは `[引数, ..., 戻り値]` の配列。マップに存在しない `userId` が渡された場合は `null` が返る。

---

## 配置まとめ

| 役割 | 配置場所 | namespace |
|------|---------|-----------|
| 公開インターフェース | `Modules/<SubDomain>/app/Contracts/<概念名>/` | `Modules\<SubDomain>\Contracts\<概念名>` |
| DTO | `Modules/<SubDomain>/app/Contracts/<概念名>/` | `Modules\<SubDomain>\Contracts\<概念名>` |
| インターフェース実装 | `Modules/<SubDomain>/app/Infrastructure/<概念名>/` | `Modules\<SubDomain>\Infrastructure\...` |
| DI バインド | `Modules/<SubDomain>/app/Providers/` | `Modules\<SubDomain>\Providers` |
| 利用側（他サブドメイン） | `Modules/<他SubDomain>/app/UseCase/` | `Modules\<他SubDomain>\UseCase\...` |
