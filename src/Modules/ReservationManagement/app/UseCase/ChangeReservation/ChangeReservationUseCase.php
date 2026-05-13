<?php

namespace Modules\ReservationManagement\UseCase\ChangeReservation;

use App\Domains\Models\Share\Clock\Clock;
use App\UseCase\Share\TransactionExecutor;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationName;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;

class ChangeReservationUseCase
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private TransactionExecutor   $transactionExecutor,
        private Clock                 $clock,
    ) {}

    public function execute(ChangeReservationInput $input): void
    {
        $reservationId = new ReservationId($input->reservationId);
        $newTimeRange = new ReservationTimeRange($input->startAt, $input->endAt);

        $this->transactionExecutor->perform(function () use ($reservationId, $input, $newTimeRange) {
            $reservation = $this->reservationRepository->findById($reservationId);

            if ($reservation === null) {
                throw new \DomainException('指定された予約が見つかりません。');
            }

            $reservationList = $this->reservationRepository->findActiveByMeetingRoomIdAndDate(
                $reservation->meetingRoomId(),
                $input->startAt,
            )->excluding($reservationId);

            $reservation->change(
                name:            new ReservationName($input->name),
                newTimeRange:    $newTimeRange,
                reservationList: $reservationList,
                clock:           $this->clock,
            );

            $this->reservationRepository->save($reservation);
        });
    }
}
