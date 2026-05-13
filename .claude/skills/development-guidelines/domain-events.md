# ドメインイベント（Domain Event）実装ガイドライン

このファイルは `development-guidelines` スキルから常時読み込まれる、ドメインイベントに特化した実装指針です。

---

## 実装規約

- プロパティは**プリミティブ型**（string, int, DateTimeImmutable）で持つ（値オブジェクトをそのまま持ち込まない）
- **コンストラクタプロモーション禁止**（`occurredOn` のみデフォルト値付きプロモーションを許可）
- `DomainEvent` インターフェースは `eventVersion()` と `occurredOn()` を持つ
- 集約ルートのビジネスメソッドの引数に `DomainEventPublisher` を受け取り、メソッド内で `publish()` する
- `Subscriber` の抽象クラスをドメイン層に置く（テスト用 Spy を継承で作れるようにする）
- 具体 Subscriber（フレームワーク依存実装）はインフラ層に置く
- ユースケースは抽象 Subscriber を DI で受け取る（具体 Subscriber を直接 new しない）
- `afterCommit()` でトランザクションコミット後にジョブ投入（ロールバック時の誤実行を防ぐ）

> レイヤー配置は `layer-responsibilities.md` を参照。

---

## コード例

```php
// src/app/Domains/Models/Share/DomainEvent/DomainEvent.php
namespace App\Domains\Models\Share\DomainEvent;

interface DomainEvent
{
    public function eventVersion(): int;
    public function occurredOn(): DateTimeImmutable;
}

// src/app/Domains/Models/Share/DomainEvent/DomainEventSubscriber.php
namespace App\Domains\Models\Share\DomainEvent;

interface DomainEventSubscriber
{
    public function subscribedToEventType(): string;
    public function handleEvent(DomainEvent $domainEvent): void;
}

// src/app/Domains/Models/Share/DomainEvent/DomainEventPublisher.php
namespace App\Domains\Models\Share\DomainEvent;

class DomainEventPublisher
{
    private array $subscriberList = [];

    public function subscribe(DomainEventSubscriber $subscriber): void
    {
        $this->subscriberList[] = $subscriber;
    }

    public function publish(DomainEvent $event): void
    {
        foreach ($this->subscriberList as $subscriber) {
            if ($subscriber->subscribedToEventType() === get_class($event)
                || $subscriber->subscribedToEventType() === DomainEvent::class) {
                $subscriber->handleEvent($event);
            }
        }
    }
}

// 各モジュールのドメイン層: OK: ビジネスプロパティはクラス本体で明示的に宣言し、コンストラクタで代入
//                              occurredOn はデフォルト値付きプロモーションを許可
use App\Domains\Models\Share\DomainEvent\DomainEvent;

class ReservationCancelled implements DomainEvent
{
    private int $eventVersion = 1;
    private string $reservationId;

    public function __construct(
        string $reservationId,
        private DateTimeImmutable $occurredOn = new DateTimeImmutable(),
    ) {
        $this->reservationId = $reservationId;
    }

    public function eventVersion(): int { return $this->eventVersion; }
    public function occurredOn(): DateTimeImmutable { return $this->occurredOn; }
    public function reservationId(): string { return $this->reservationId; }
}

// 各モジュールのドメイン層: 抽象 Subscriber（subscribedToEventType を固定）
use App\Domains\Models\Share\DomainEvent\DomainEvent;
use App\Domains\Models\Share\DomainEvent\DomainEventSubscriber;

abstract class ReservationCancelledSubscriber implements DomainEventSubscriber
{
    public function subscribedToEventType(): string
    {
        return ReservationCancelled::class;
    }

    abstract public function handleEvent(DomainEvent $event): void;
}

// 各モジュールのインフラ層: 具体 Subscriber
use App\Domains\Models\Share\DomainEvent\DomainEvent;

class LaravelReservationCancelledMailSubscriber extends ReservationCancelledSubscriber
{
    public function handleEvent(DomainEvent $event): void
    {
        if (!$event instanceof ReservationCancelled) {
            throw new BadMethodCallException('無効なイベント: ' . get_class($event));
        }
        // トランザクションコミット後に投入（ロールバック時の誤実行を防ぐ）
        SendCancellationMailJob::dispatch($event)->afterCommit()->onQueue('high');
    }
}

// 各モジュールのドメイン層: 集約内で Publisher を受け取り publish() する
use App\Domains\Models\Share\DomainEvent\DomainEventPublisher;

class Reservation
{
    public function cancel(DomainEventPublisher $publisher): void
    {
        if ($this->status->isCancelled()) {
            throw new DomainException('キャンセル済みの予約はキャンセルできません');
        }
        $this->status = ReservationStatus::CANCELLED;
        // 値オブジェクトからプリミティブ値を取り出してイベントに渡す
        $publisher->publish(new ReservationCancelled($this->id->value));
    }
}

// 各モジュールのアプリケーション層: 抽象 Subscriber を DI で受け取り Publisher に登録
use App\Domains\Models\Share\DomainEvent\DomainEventPublisher;

class CancelReservationUseCase
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private DomainEventPublisher $publisher,
        ReservationCancelledSubscriber $subscriber,
    ) {
        $this->publisher->subscribe($subscriber);
    }

    public function execute(CancelReservationCommand $command): void
    {
        $reservation = $this->reservationRepository->findById($command->reservationId());
        $reservation->cancel($this->publisher);
        $this->reservationRepository->save($reservation);
    }
}
```

---

## テスト戦略（Spy パターン）

抽象 Subscriber を継承した Spy クラスでフレームワーク非依存のユニットテストを書く。

```php
use App\Domains\Models\Share\DomainEvent\DomainEvent;

class ReservationCancelledSubscriberSpy extends ReservationCancelledSubscriber
{
    private bool $wasCalled = false;
    private ?ReservationCancelled $receivedEvent = null;

    public function handleEvent(DomainEvent $event): void
    {
        if (!$event instanceof ReservationCancelled) {
            throw new BadMethodCallException('無効なイベント');
        }
        $this->wasCalled = true;
        $this->receivedEvent = $event;
    }

    public function wasCalled(): bool { return $this->wasCalled; }
    public function receivedEvent(): ?ReservationCancelled { return $this->receivedEvent; }
}
```

---

## アンチパターン

| アンチパターン | 対処 |
|---|---|
| ドメインイベントのプロパティをコンストラクタプロモーションで宣言 | クラス本体で宣言してコンストラクタで代入（`occurredOn` のみ例外） |
| イベントプロパティに値オブジェクトを持つ | プリミティブ型（string, int 等）に変換して渡す |
| 具体 Subscriber をユースケースで直接 new する | 抽象 Subscriber を DI で受け取る |
| トランザクション内でジョブを即時投入する | `afterCommit()` でコミット後に投入する |
