<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

use App\Domains\Models\Share\Clock\Clock;

class Reservation
{
    public function __construct(
        private readonly ReservationId $reservationId,
        private readonly MeetingRoomId $meetingRoomId,
        private ReservationName $name,
        private ContactPersonName $contactPerson,
        private ContactEmail $email,
        private ReservationTimeRange $timeRange,
        private ReservationStatus $status,
    ) {}

    public function reservationId(): ReservationId
    {
        return $this->reservationId;
    }

    public function meetingRoomId(): MeetingRoomId
    {
        return $this->meetingRoomId;
    }

    public function name(): ReservationName
    {
        return $this->name;
    }

    public function contactPerson(): ContactPersonName
    {
        return $this->contactPerson;
    }

    public function email(): ContactEmail
    {
        return $this->email;
    }

    public function timeRange(): ReservationTimeRange
    {
        return $this->timeRange;
    }

    public function status(): ReservationStatus
    {
        return $this->status;
    }

    public static function create(
        ReservationId $reservationId,
        MeetingRoomId $meetingRoomId,
        ReservationName $name,
        ContactPersonName $contactPerson,
        ContactEmail $email,
        ReservationTimeRange $timeRange,
        ReservationList $reservationList,
        Clock $clock,
    ): self {
        $bufferTime = new BufferTime();
        $reservablePeriod = new ReservablePeriod();

        if (!$bufferTime->isSatisfiedBetween($reservationList, $timeRange)) {
            throw new \DomainException('予約の前後には最低10分のバッファタイムが必要です。');
        }

        if (!$reservablePeriod->isSatisfiedBy($timeRange, $clock)) {
            throw new \DomainException('予約は本日から2週間先までの期間で行う必要があります。');
        }

        if ($reservationList->isOverlapping($timeRange)) {
            throw new \DomainException('指定された時間帯は既に予約されています。');
        }

        return new self(
            $reservationId,
            $meetingRoomId,
            $name,
            $contactPerson,
            $email,
            $timeRange,
            ReservationStatus::CONFIRMED,
        );
    }

    public static function reconstruct(
        ReservationId $reservationId,
        MeetingRoomId $meetingRoomId,
        ReservationName $name,
        ContactPersonName $contactPerson,
        ContactEmail $email,
        ReservationTimeRange $timeRange,
        ReservationStatus $status,
    ): self {
        return new self(
            $reservationId,
            $meetingRoomId,
            $name,
            $contactPerson,
            $email,
            $timeRange,
            $status,
        );
    }

    public function cancel(): void
    {
        if ($this->status === ReservationStatus::CANCELLED) {
            throw new \DomainException('キャンセル済みの予約は再度キャンセルできません。');
        }
        $this->status = ReservationStatus::CANCELLED;
    }

    public function change(
        ReservationName $name,
        ReservationTimeRange $newTimeRange,
        ReservationList $reservationList,
        Clock $clock,
    ): void {
        if ($this->status === ReservationStatus::CANCELLED) {
            throw new \DomainException('キャンセル済みの予約は変更できません。');
        }

        $bufferTime = new BufferTime();
        $reservablePeriod = new ReservablePeriod();

        if (!$bufferTime->isSatisfiedBetween($reservationList, $newTimeRange)) {
            throw new \DomainException('予約の前後には最低10分のバッファタイムが必要です。');
        }

        if (!$reservablePeriod->isSatisfiedBy($newTimeRange, $clock)) {
            throw new \DomainException('予約は本日から2週間先までの期間で行う必要があります。');
        }

        if ($reservationList->isOverlapping($newTimeRange)) {
            throw new \DomainException('指定された時間帯は既に予約されています。');
        }

        $this->name = $name;
        $this->timeRange = $newTimeRange;
    }
}
