<?php

namespace Tests\MeetingRoomManagement\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;
use Tests\TestCase;

class CreateMeetingRoomTest extends TestCase
{
    use RefreshDatabase;

    private MeetingRoomRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(MeetingRoomRepository::class);
    }

    public function test_備品なしで会議室を登録できる(): void
    {
        // Given
        $name = '第1会議室';
        $capacity = 10;

        // When
        $response = $this->post('/api/v1/meeting-rooms', [
            'name'       => $name,
            'capacity'   => $capacity,
            'equipments' => [],
        ]);

        // Then
        $response->assertStatus(201);
        $rooms = $this->repository->findAll();
        $this->assertSame($name, $rooms[0]->name()->value);
        $this->assertSame($capacity, $rooms[0]->capacity()->value);
        $this->assertSame([], $rooms[0]->equipments());
    }

    public function test_備品ありで会議室を登録できる(): void
    {
        // Given
        $name = '第2会議室';
        $capacity = 20;

        // When
        $response = $this->post('/api/v1/meeting-rooms', [
            'name'       => $name,
            'capacity'   => $capacity,
            'equipments' => [1, 2],
        ]);

        // Then
        $response->assertStatus(201);
        $rooms = $this->repository->findAll();
        $this->assertSame($name, $rooms[0]->name()->value);
        $this->assertSame($capacity, $rooms[0]->capacity()->value);
        $this->assertEqualsCanonicalizing([Equipment::WHITEBOARD, Equipment::PROJECTOR], $rooms[0]->equipments());
    }

    public function test_nameが未入力の場合はバリデーションエラーになる(): void
    {
        // Given: name を省略

        // When
        $response = $this->postJson('/api/v1/meeting-rooms', [
            'capacity'   => 10,
            'equipments' => [],
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_capacityが範囲外の場合はバリデーションエラーになる(): void
    {
        // Given: capacity に 0 を指定（1未満）

        // When
        $response = $this->postJson('/api/v1/meeting-rooms', [
            'name'       => '第1会議室',
            'capacity'   => 0,
            'equipments' => [],
        ]);

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['capacity']);
    }
}
