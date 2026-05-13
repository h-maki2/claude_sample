<?php

namespace Modules\ReservationManagement\UseCase\ListReservations;

use DateTimeImmutable;
use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomFetcher;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;

class ListReservationsUseCase
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private MeetingRoomFetcher $meetingRoomFetcher,
    ) {}

    /** @return ReservationListItem[] */
    public function execute(DateTimeImmutable $date): array
    {
        $reservations = $this->reservationRepository->findActiveByDate($date);

        if ($reservations->isEmpty()) {
            return [];
        }

        $meetingRooms = [];
        foreach ($this->meetingRoomFetcher->fetchAll() as $dto) {
            $meetingRooms[$dto->meetingRoomId] = $dto;
        }

        $items = [];
        foreach ($reservations as $reservation) {
            $items[] = new ReservationListItem(
                reservationId: $reservation->reservationId()->value,
                meetingRoomId: $reservation->meetingRoomId()->value,
                meetingRoomName: $meetingRooms[$reservation->meetingRoomId()->value]->name,
                startAt: $reservation->timeRange()->startAt,
                endAt: $reservation->timeRange()->endAt,
                status: $reservation->status()->name,
            );
        }
        return $items;
    }
}
