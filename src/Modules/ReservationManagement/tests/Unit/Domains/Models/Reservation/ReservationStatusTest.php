<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use PHPUnit\Framework\TestCase;

class ReservationStatusTest extends TestCase
{
    public function test_CONFIRMEDケースが存在する(): void
    {
        // When
        $status = ReservationStatus::CONFIRMED;

        // Then
        $this->assertInstanceOf(ReservationStatus::class, $status);
        $this->assertSame(1, $status->value);
    }

    public function test_CANCELLEDケースが存在する(): void
    {
        // When
        $status = ReservationStatus::CANCELLED;

        // Then
        $this->assertInstanceOf(ReservationStatus::class, $status);
        $this->assertSame(2, $status->value);
    }

    public function test_予約確定ステータスの表示名は予約確定である(): void
    {
        // Given
        $status = ReservationStatus::CONFIRMED;

        // When
        $label = $status->label();

        // Then
        $this->assertSame('予約確定', $label);
    }

    public function test_キャンセル済みステータスの表示名はキャンセル済みである(): void
    {
        // Given
        $status = ReservationStatus::CANCELLED;

        // When
        $label = $status->label();

        // Then
        $this->assertSame('キャンセル済み', $label);
    }
}
