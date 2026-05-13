<?php

namespace Tests\ReservationManagement\Feature;

use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation\EloquentReservationRepository;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\TestCase;

class ListReservationsTest extends TestCase
{
    use RefreshDatabase;

    private ReservationTestDataCreator $reservationCreator;
    private MeetingRoomDtoTestDataCreator $meetingRoomCreator;

    public function setUp(): void
    {
        parent::setUp();
        $this->reservationCreator = new ReservationTestDataCreator(
            app(EloquentReservationRepository::class),
        );
        $this->meetingRoomCreator = new MeetingRoomDtoTestDataCreator(
            new EloquentMeetingRoomDtoTestDataStore(),
        );
    }

    public function test_予約が0件のとき空のリストを返す(): void
    {
        // Given: 予約なし

        // When
        $response = $this->getJson('/api/v1/reservations?date=2026-05-07');

        // Then
        $response->assertStatus(200);
        $response->assertJson(['reservations' => []]);
    }

    public function test_指定日付の予約一覧を返す(): void
    {
        // Given
        $meetingRoomId = '01957b3c-1234-7abc-8def-000000000099';
        $meetingRoomName = '第1会議室';
        $this->meetingRoomCreator->create(
            meetingRoomId: $meetingRoomId,
            name: $meetingRoomName,
        );
        $this->reservationCreator->create(
            meetingRoomId: new MeetingRoomId($meetingRoomId),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-05-07 10:00:00'),
                new DateTimeImmutable('2026-05-07 11:30:00'),
            ),
        );

        // When
        $response = $this->getJson('/api/v1/reservations?date=2026-05-07');

        // Then
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'reservations');
        $response->assertJsonFragment([
            'meetingRoomId'   => $meetingRoomId,
            'meetingRoomName' => $meetingRoomName,
            'startAt'         => '2026年5月7日 【木】 10:00',
            'endAt'           => '2026年5月7日 【木】 11:30',
        ]);
    }

    public function test_dateが未指定の場合は422エラーを返す(): void
    {
        // Given: date パラメータなし

        // When
        $response = $this->getJson('/api/v1/reservations');

        // Then
        $response->assertStatus(422);
    }

    public function test_dateが不正な形式の場合は422エラーを返す(): void
    {
        // Given: Y-m-d 形式でない日付
        $invalidDate = '2026/05/07';

        // When
        $response = $this->getJson('/api/v1/reservations?date=' . $invalidDate);

        // Then
        $response->assertStatus(422);
    }
}
