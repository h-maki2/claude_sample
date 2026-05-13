<?php

namespace Tests\ReservationManagement\Feature;

use App\Domains\Models\Share\Clock\Clock;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use Tests\Helpers\Domains\Models\Share\Clock\TestFixedClock;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\TestCase;

class ChangeReservationTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000099';

    private ReservationRepository $reservationRepository;
    private ReservationTestDataCreator $reservationCreator;
    private MeetingRoomDtoTestDataCreator $meetingRoomCreator;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->instance(Clock::class, new TestFixedClock(new DateTimeImmutable('2026-05-07 09:00:00')));
        $this->reservationRepository = app(ReservationRepository::class);
        $this->reservationCreator = new ReservationTestDataCreator($this->reservationRepository);
        $this->meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
    }

    public function test_予約を変更できる(): void
    {
        // Given
        $this->meetingRoomCreator->create(meetingRoomId: self::DEFAULT_MEETING_ROOM_ID);
        $reservation = $this->reservationCreator->create(
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-05-07 10:00:00'),
                new DateTimeImmutable('2026-05-07 11:00:00'),
            ),
        );
        $newName = '変更後の会議名';
        $newStartAt = '2026-05-07 13:00:00';
        $newEndAt = '2026-05-07 14:00:00';

        // When
        $response = $this->putJson(
            '/api/v1/reservations/' . $reservation->reservationId()->value,
            [
                'name'     => $newName,
                'start_at' => $newStartAt,
                'end_at'   => $newEndAt,
            ],
        );

        // Then
        $response->assertStatus(204);
        $saved = $this->reservationRepository->findById($reservation->reservationId());
        $this->assertNotNull($saved);
        $this->assertSame($newName, $saved->name()->value);
        $this->assertSame($newStartAt, $saved->timeRange()->startAt->format('Y-m-d H:i:s'));
        $this->assertSame($newEndAt, $saved->timeRange()->endAt->format('Y-m-d H:i:s'));
    }

    public function test_予約名が未入力の場合は変更に失敗する(): void
    {
        // Given: name を省略

        // When
        $response = $this->putJson('/api/v1/reservations/any-id', [
            'start_at' => '2026-05-07 10:00:00',
            'end_at'   => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_予約名が50文字を超える場合は変更に失敗する(): void
    {
        // Given: 51文字の name
        $tooLongName = str_repeat('あ', 51);

        // When
        $response = $this->putJson('/api/v1/reservations/any-id', [
            'name'     => $tooLongName,
            'start_at' => '2026-05-07 10:00:00',
            'end_at'   => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_予約開始日時が未入力の場合は変更に失敗する(): void
    {
        // Given: start_at を省略

        // When
        $response = $this->putJson('/api/v1/reservations/any-id', [
            'name'   => '変更後の会議名',
            'end_at' => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_at']);
    }

    public function test_予約終了日時が未入力の場合は変更に失敗する(): void
    {
        // Given: end_at を省略

        // When
        $response = $this->putJson('/api/v1/reservations/any-id', [
            'name'     => '変更後の会議名',
            'start_at' => '2026-05-07 10:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_at']);
    }

    public function test_予約開始日時が不正なフォーマットの場合は変更に失敗する(): void
    {
        // Given: 不正な日時フォーマット
        $invalidFormat = '2026/05/07 10:00';

        // When
        $response = $this->putJson('/api/v1/reservations/any-id', [
            'name'     => '変更後の会議名',
            'start_at' => $invalidFormat,
            'end_at'   => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_at']);
    }

    public function test_予約終了日時が不正なフォーマットの場合は変更に失敗する(): void
    {
        // Given: 不正な日時フォーマット
        $invalidFormat = '2026/05/07 11:00';

        // When
        $response = $this->putJson('/api/v1/reservations/any-id', [
            'name'     => '変更後の会議名',
            'start_at' => '2026-05-07 10:00:00',
            'end_at'   => $invalidFormat,
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_at']);
    }

    public function test_予約終了日時が開始日時より前の場合は変更に失敗する(): void
    {
        // Given: end_at が start_at より前
        $startAt = '2026-05-07 11:00:00';
        $endAt = '2026-05-07 10:00:00';

        // When
        $response = $this->putJson('/api/v1/reservations/any-id', [
            'name'     => '変更後の会議名',
            'start_at' => $startAt,
            'end_at'   => $endAt,
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_at']);
    }
}
