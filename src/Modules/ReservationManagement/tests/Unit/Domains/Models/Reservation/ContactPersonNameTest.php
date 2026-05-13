<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use Modules\ReservationManagement\Domains\Models\Reservation\ContactPersonName;
use PHPUnit\Framework\TestCase;

class ContactPersonNameTest extends TestCase
{
    public function test_1文字の担当者名でインスタンス化できる(): void
    {
        // Given
        $value = '山';

        // When
        $name = new ContactPersonName($value);

        // Then
        $this->assertSame($value, $name->value);
    }

    public function test_30文字の担当者名でインスタンス化できる(): void
    {
        // Given
        $value = str_repeat('山', 30);

        // When
        $name = new ContactPersonName($value);

        // Then
        $this->assertSame($value, $name->value);
    }

    public function test_同じ値を持つ2つのインスタンスは等値である(): void
    {
        // Given
        $name1 = new ContactPersonName('山田太郎');
        $name2 = new ContactPersonName('山田太郎');

        // Then
        $this->assertTrue($name1->equals($name2));
    }

    public function test_異なる値を持つ2つのインスタンスは等値でない(): void
    {
        // Given
        $name1 = new ContactPersonName('山田太郎');
        $name2 = new ContactPersonName('鈴木花子');

        // Then
        $this->assertFalse($name1->equals($name2));
    }

    public function test_空文字列を渡した場合は例外が発生する(): void
    {
        // Given
        $value = '';

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new ContactPersonName($value);
    }

    public function test_31文字の文字列を渡した場合は例外が発生する(): void
    {
        // Given
        $value = str_repeat('山', 31);

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new ContactPersonName($value);
    }
}
