<?php

namespace Tests\ReservationManagement\Feature;

use App\Domains\Models\Share\Clock\Clock;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;
use Tests\Helpers\Domains\Models\Share\Clock\TestFixedClock;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\TestCase;

class CreateReservationTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000099';

    private ReservationRepository $reservationRepository;
    private MeetingRoomDtoTestDataCreator $meetingRoomCreator;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->instance(Clock::class, new TestFixedClock(new DateTimeImmutable('2026-05-07 09:00:00')));
        $this->reservationRepository = app(ReservationRepository::class);
        $this->meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
    }

    public function test_会議室を予約できる(): void
    {
        // Given
        $this->meetingRoomCreator->create(meetingRoomId: self::DEFAULT_MEETING_ROOM_ID);
        $name = '第1回企画会議';
        $contactPerson = '鈴木一郎';
        $email = 'suzuki@example.com';
        $startAt = '2026-05-07 10:00:00';
        $endAt = '2026-05-07 11:00:00';

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'meeting_room_id' => self::DEFAULT_MEETING_ROOM_ID,
            'name'            => $name,
            'contact_person'  => $contactPerson,
            'email'           => $email,
            'start_at'        => $startAt,
            'end_at'          => $endAt,
        ]);

        // Then
        $response->assertStatus(201);
        $reservations = $this->reservationRepository->findActiveByDate(new DateTimeImmutable($startAt));
        $this->assertCount(1, iterator_to_array($reservations));
        $saved = iterator_to_array($reservations)[0];
        $this->assertSame($name, $saved->name()->value);
        $this->assertSame($contactPerson, $saved->contactPerson()->value);
        $this->assertSame($email, $saved->email()->value);
        $this->assertSame(self::DEFAULT_MEETING_ROOM_ID, $saved->meetingRoomId()->value);
    }

    public function test_会議室IDが未入力の場合は予約登録に失敗する(): void
    {
        // Given: meeting_room_id を省略

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'name'           => '第1回企画会議',
            'contact_person' => '鈴木一郎',
            'email'          => 'suzuki@example.com',
            'start_at'       => '2026-05-07 10:00:00',
            'end_at'         => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['meeting_room_id']);
    }

    public function test_予約名が未入力の場合は予約登録に失敗する(): void
    {
        // Given: name を省略

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'meeting_room_id' => self::DEFAULT_MEETING_ROOM_ID,
            'contact_person'  => '鈴木一郎',
            'email'           => 'suzuki@example.com',
            'start_at'        => '2026-05-07 10:00:00',
            'end_at'          => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_予約名が50文字を超える場合は予約登録に失敗する(): void
    {
        // Given: 51文字の name
        $tooLongName = str_repeat('あ', 51);

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'meeting_room_id' => self::DEFAULT_MEETING_ROOM_ID,
            'name'            => $tooLongName,
            'contact_person'  => '鈴木一郎',
            'email'           => 'suzuki@example.com',
            'start_at'        => '2026-05-07 10:00:00',
            'end_at'          => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_担当者名が未入力の場合は予約登録に失敗する(): void
    {
        // Given: contact_person を省略

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'meeting_room_id' => self::DEFAULT_MEETING_ROOM_ID,
            'name'            => '第1回企画会議',
            'email'           => 'suzuki@example.com',
            'start_at'        => '2026-05-07 10:00:00',
            'end_at'          => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact_person']);
    }

    public function test_担当者名が30文字を超える場合は予約登録に失敗する(): void
    {
        // Given: 31文字の contact_person
        $tooLongName = str_repeat('あ', 31);

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'meeting_room_id' => self::DEFAULT_MEETING_ROOM_ID,
            'name'            => '第1回企画会議',
            'contact_person'  => $tooLongName,
            'email'           => 'suzuki@example.com',
            'start_at'        => '2026-05-07 10:00:00',
            'end_at'          => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact_person']);
    }

    public function test_連絡先が未入力の場合は予約登録に失敗する(): void
    {
        // Given: email を省略

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'meeting_room_id' => self::DEFAULT_MEETING_ROOM_ID,
            'name'            => '第1回企画会議',
            'contact_person'  => '鈴木一郎',
            'start_at'        => '2026-05-07 10:00:00',
            'end_at'          => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_連絡先が不正な形式の場合は予約登録に失敗する(): void
    {
        // Given: 不正なメール形式
        $invalidEmail = 'not-an-email';

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'meeting_room_id' => self::DEFAULT_MEETING_ROOM_ID,
            'name'            => '第1回企画会議',
            'contact_person'  => '鈴木一郎',
            'email'           => $invalidEmail,
            'start_at'        => '2026-05-07 10:00:00',
            'end_at'          => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_予約開始日時が未入力の場合は予約登録に失敗する(): void
    {
        // Given: start_at を省略

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'meeting_room_id' => self::DEFAULT_MEETING_ROOM_ID,
            'name'            => '第1回企画会議',
            'contact_person'  => '鈴木一郎',
            'email'           => 'suzuki@example.com',
            'end_at'          => '2026-05-07 11:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_at']);
    }

    public function test_予約終了日時が未入力の場合は予約登録に失敗する(): void
    {
        // Given: end_at を省略

        // When
        $response = $this->postJson('/api/v1/reservations', [
            'meeting_room_id' => self::DEFAULT_MEETING_ROOM_ID,
            'name'            => '第1回企画会議',
            'contact_person'  => '鈴木一郎',
            'email'           => 'suzuki@example.com',
            'start_at'        => '2026-05-07 10:00:00',
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_at']);
    }
}
