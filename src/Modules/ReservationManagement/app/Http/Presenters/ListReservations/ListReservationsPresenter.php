<?php

namespace Modules\ReservationManagement\Http\Presenters\ListReservations;

use DateTimeImmutable;
use Modules\ReservationManagement\UseCase\ListReservations\ReservationListItem;

class ListReservationsPresenter
{
    /** @param ReservationListItem[] $listItems */
    public function __construct(private array $listItems) {}

    /** @return list<array<string, string>> */
    public function getReservations(): array
    {
        return array_map(
            fn(ReservationListItem $item) => [
                'reservationId'   => $item->reservationId,
                'meetingRoomId'   => $item->meetingRoomId,
                'meetingRoomName' => $item->meetingRoomName,
                'startAt'         => $this->formatDateTime($item->startAt),
                'endAt'           => $this->formatDateTime($item->endAt),
            ],
            $this->listItems,
        );
    }

    private function formatDateTime(DateTimeImmutable $dateTime): string
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $days[(int) $dateTime->format('w')];
        return $dateTime->format('Y年n月j日') . ' 【' . $dayOfWeek . '】 ' . $dateTime->format('H:i');
    }
}
