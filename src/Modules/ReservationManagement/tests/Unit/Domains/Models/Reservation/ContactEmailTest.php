<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use Modules\ReservationManagement\Domains\Models\Reservation\ContactEmail;
use PHPUnit\Framework\TestCase;

class ContactEmailTest extends TestCase
{
    public function test_有効なメールアドレスでインスタンス化できる(): void
    {
        // Given
        $value = 'user@example.com';

        // When
        $email = new ContactEmail($value);

        // Then
        $this->assertSame($value, $email->value);
    }

    public function test_同じ値を持つ2つのインスタンスは等値である(): void
    {
        // Given
        $email1 = new ContactEmail('user@example.com');
        $email2 = new ContactEmail('user@example.com');

        // Then
        $this->assertTrue($email1->equals($email2));
    }

    public function test_異なる値を持つ2つのインスタンスは等値でない(): void
    {
        // Given
        $email1 = new ContactEmail('user1@example.com');
        $email2 = new ContactEmail('user2@example.com');

        // Then
        $this->assertFalse($email1->equals($email2));
    }

    public function test_空文字列を渡した場合は例外が発生する(): void
    {
        // Given
        $value = '';

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new ContactEmail($value);
    }

    public function test_不正なメールアドレス形式を渡した場合は例外が発生する(): void
    {
        // Given
        $value = 'not-an-email';

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new ContactEmail($value);
    }
}
