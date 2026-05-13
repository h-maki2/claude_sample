<?php

namespace Modules\ReservationManagement\UseCase\CancelReservation;

use App\UseCase\Share\TransactionExecutor;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;

class CancelReservationUseCase
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private TransactionExecutor   $transactionExecutor,
    ) {}

    public function execute(string $reservationId): void
    {
        $id = new ReservationId($reservationId);

        $this->transactionExecutor->perform(function () use ($id) {
            $reservation = $this->reservationRepository->findById($id);

            if ($reservation === null) {
                throw new \DomainException('指定された予約が見つかりません。');
            }

            $reservation->cancel();

            $this->reservationRepository->save($reservation);
        });
    }
}
