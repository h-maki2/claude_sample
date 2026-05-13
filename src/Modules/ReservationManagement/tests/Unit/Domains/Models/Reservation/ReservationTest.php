<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use DateTimeImmutable;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactEmail;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactPersonName;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\Reservation;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationList;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationName;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Domains\Models\Share\Clock\TestFixedClock;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\TestReservationFactory;

class ReservationTest extends TestCase
{
    public function test_有効な値で予約を生成でき各ゲッターが正しい値を返す(): void
    {
        // Given
        $reservationId = new ReservationId('01957b3c-1234-7abc-8def-000000000001');
        $meetingRoomId = new MeetingRoomId('01957b3c-1234-7abc-8def-000000000099');
        $name = new ReservationName('第3回プロジェクト定例');
        $contactPerson = new ContactPersonName('山田太郎');
        $email = new ContactEmail('yamada@example.com');
        $timeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-05-01 10:00:00'),
            new DateTimeImmutable('2026-05-01 11:30:00'),
        );
        $status = ReservationStatus::CONFIRMED;

        // When
        $reservation = new Reservation($reservationId, $meetingRoomId, $name, $contactPerson, $email, $timeRange, $status);

        // Then
        $this->assertSame($reservationId, $reservation->reservationId());
        $this->assertSame($meetingRoomId, $reservation->meetingRoomId());
        $this->assertSame($name, $reservation->name());
        $this->assertSame($contactPerson, $reservation->contactPerson());
        $this->assertSame($email, $reservation->email());
        $this->assertSame($timeRange, $reservation->timeRange());
        $this->assertSame($status, $reservation->status());
    }

    public function test_キャンセルするとステータスがCANCELLEDに変わる(): void
    {
        // Given
        $reservation = TestReservationFactory::create(status: ReservationStatus::CONFIRMED);

        // When
        $reservation->cancel();

        // Then
        $this->assertSame(ReservationStatus::CANCELLED, $reservation->status());
    }

    public function test_キャンセル済みの予約を再度キャンセルすると例外が発生する(): void
    {
        // Given
        $reservation = TestReservationFactory::create(status: ReservationStatus::CANCELLED);

        // When / Then
        $this->expectException(\DomainException::class);
        $reservation->cancel();
    }

    public function test_有効な内容で予約名と時間帯を変更できる(): void
    {
        // Given
        $reservation = TestReservationFactory::create(
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-05-01 10:00:00'),
                new DateTimeImmutable('2026-05-01 11:00:00'),
            ),
        );
        $newName = new ReservationName('変更後会議名');
        $newTimeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-05-01 14:00:00'),
            new DateTimeImmutable('2026-05-01 15:00:00'),
        );
        $clock = new TestFixedClock(new DateTimeImmutable('2026-05-01 09:00:00'));

        // When
        $reservation->change(
            name: $newName,
            newTimeRange: $newTimeRange,
            reservationList: new ReservationList([]),
            clock: $clock,
        );

        // Then
        $this->assertSame($newName, $reservation->name());
        $this->assertSame($newTimeRange, $reservation->timeRange());
    }

    public function test_キャンセル済みの予約は変更できない(): void
    {
        // Given
        $reservation = TestReservationFactory::create(status: ReservationStatus::CANCELLED);
        $clock = new TestFixedClock(new DateTimeImmutable('2026-05-01 09:00:00'));

        // When / Then
        $this->expectException(\DomainException::class);
        $reservation->change(
            name: new ReservationName('変更後会議名'),
            newTimeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-05-01 14:00:00'),
                new DateTimeImmutable('2026-05-01 15:00:00'),
            ),
            reservationList: new ReservationList([]),
            clock: $clock,
        );
    }

    public function test_バッファタイムを満たさない時間帯への変更は例外が発生する(): void
    {
        // Given
        $reservation = TestReservationFactory::create();
        // 他の予約: 14:00 終了
        $otherReservation = TestReservationFactory::create(
            id: new ReservationId('01957b3c-1234-7abc-8def-000000000002'),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-05-01 13:00:00'),
                new DateTimeImmutable('2026-05-01 14:00:00'),
            ),
        );
        // 変更先: 14:05 開始（バッファ5分 < 必要な10分）
        $newTimeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-05-01 14:05:00'),
            new DateTimeImmutable('2026-05-01 15:05:00'),
        );
        $clock = new TestFixedClock(new DateTimeImmutable('2026-05-01 09:00:00'));

        // When / Then
        $this->expectException(\DomainException::class);
        $reservation->change(
            name: new ReservationName('変更後会議名'),
            newTimeRange: $newTimeRange,
            reservationList: new ReservationList([$otherReservation]),
            clock: $clock,
        );
    }

    public function test_受付可能期間外の日付への変更は例外が発生する(): void
    {
        // Given
        $reservation = TestReservationFactory::create();
        // 2026-05-01 基準で 15日後 = 受付可能期間外
        $newTimeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-05-16 10:00:00'),
            new DateTimeImmutable('2026-05-16 11:00:00'),
        );
        $clock = new TestFixedClock(new DateTimeImmutable('2026-05-01 09:00:00'));

        // When / Then
        $this->expectException(\DomainException::class);
        $reservation->change(
            name: new ReservationName('変更後会議名'),
            newTimeRange: $newTimeRange,
            reservationList: new ReservationList([]),
            clock: $clock,
        );
    }

    public function test_時間帯が他の予約と重複する場合は例外が発生する(): void
    {
        // Given
        $reservation = TestReservationFactory::create();
        // 他の予約: 14:00-15:00
        $otherReservation = TestReservationFactory::create(
            id: new ReservationId('01957b3c-1234-7abc-8def-000000000002'),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-05-01 14:00:00'),
                new DateTimeImmutable('2026-05-01 15:00:00'),
            ),
        );
        // 変更先: 14:30-15:30（重複）
        $newTimeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-05-01 14:30:00'),
            new DateTimeImmutable('2026-05-01 15:30:00'),
        );
        $clock = new TestFixedClock(new DateTimeImmutable('2026-05-01 09:00:00'));

        // When / Then
        $this->expectException(\DomainException::class);
        $reservation->change(
            name: new ReservationName('変更後会議名'),
            newTimeRange: $newTimeRange,
            reservationList: new ReservationList([$otherReservation]),
            clock: $clock,
        );
    }
}
