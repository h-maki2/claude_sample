<?php

namespace Tests\ReservationManagement\Unit\Http\Presenters\ListReservations;

use DateTimeImmutable;
use Modules\ReservationManagement\Http\Presenters\ListReservations\ListReservationsPresenter;
use Modules\ReservationManagement\UseCase\ListReservations\ReservationListItem;
use PHPUnit\Framework\TestCase;

class ListReservationsPresenterTest extends TestCase
{
    public function test_予約が0件のとき空のリストを返す(): void
    {
        // Given
        $presenter = new ListReservationsPresenter([]);

        // When
        $result = $presenter->getReservations();

        // Then
        $this->assertSame([], $result);
    }

    public function test_予約一覧を正しい形式に変換できる(): void
    {
        // Given
        $reservationId = '01957b3c-1234-7abc-8def-000000000001';
        $meetingRoomId = '01957b3c-1234-7abc-8def-000000000099';
        $meetingRoomName = '第1会議室';

        $presenter = new ListReservationsPresenter([
            new ReservationListItem(
                reservationId: $reservationId,
                meetingRoomId: $meetingRoomId,
                meetingRoomName: $meetingRoomName,
                startAt: new DateTimeImmutable('2026-05-07 10:00:00'),
                endAt: new DateTimeImmutable('2026-05-07 11:30:00'),
                status: 'CONFIRMED',
            ),
        ]);

        // When
        $result = $presenter->getReservations();

        // Then
        $this->assertCount(1, $result);
        $this->assertSame($reservationId, $result[0]['reservationId']);
        $this->assertSame($meetingRoomId, $result[0]['meetingRoomId']);
        $this->assertSame($meetingRoomName, $result[0]['meetingRoomName']);
        $this->assertSame('2026年5月7日 【木】 10:00', $result[0]['startAt']);
        $this->assertSame('2026年5月7日 【木】 11:30', $result[0]['endAt']);
    }

    public function test_startAtとendAtが日本語の日付と曜日に変換される(): void
    {
        // Given: 2026-05-10 は日曜日
        $presenter = new ListReservationsPresenter([
            new ReservationListItem(
                reservationId: '01957b3c-1234-7abc-8def-000000000001',
                meetingRoomId: '01957b3c-1234-7abc-8def-000000000099',
                meetingRoomName: '第1会議室',
                startAt: new DateTimeImmutable('2026-05-10 09:00:00'),
                endAt: new DateTimeImmutable('2026-05-10 10:00:00'),
                status: 'CONFIRMED',
            ),
        ]);

        // When
        $result = $presenter->getReservations();

        // Then
        $this->assertSame('2026年5月10日 【日】 09:00', $result[0]['startAt']);
        $this->assertSame('2026年5月10日 【日】 10:00', $result[0]['endAt']);
    }

    public function test_担当者名と連絡先がレスポンスに含まれない(): void
    {
        // Given
        $presenter = new ListReservationsPresenter([
            new ReservationListItem(
                reservationId: '01957b3c-1234-7abc-8def-000000000001',
                meetingRoomId: '01957b3c-1234-7abc-8def-000000000099',
                meetingRoomName: '第1会議室',
                startAt: new DateTimeImmutable('2026-05-07 10:00:00'),
                endAt: new DateTimeImmutable('2026-05-07 11:00:00'),
                status: 'CONFIRMED',
            ),
        ]);

        // When
        $result = $presenter->getReservations();

        // Then
        $this->assertArrayNotHasKey('contactEmail', $result[0]);
        $this->assertArrayNotHasKey('contactPersonName', $result[0]);
    }
}
