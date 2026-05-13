<?php

namespace Modules\ReservationManagement\Http\Controllers\CancelReservation;

use Illuminate\Http\Response;
use Modules\ReservationManagement\UseCase\CancelReservation\CancelReservationUseCase;

class CancelReservationController
{
    public function __construct(
        private CancelReservationUseCase $useCase,
    ) {}

    public function __invoke(string $reservationId): Response
    {
        $this->useCase->execute($reservationId);

        return response()->noContent(Response::HTTP_NO_CONTENT);
    }
}
