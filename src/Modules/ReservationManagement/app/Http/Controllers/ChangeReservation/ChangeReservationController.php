<?php

namespace Modules\ReservationManagement\Http\Controllers\ChangeReservation;

use DateTimeImmutable;
use Illuminate\Http\Response;
use Modules\ReservationManagement\UseCase\ChangeReservation\ChangeReservationInput;
use Modules\ReservationManagement\UseCase\ChangeReservation\ChangeReservationUseCase;

class ChangeReservationController
{
    public function __construct(
        private ChangeReservationUseCase $useCase,
    ) {}

    public function __invoke(ChangeReservationRequest $request, string $reservationId): Response
    {
        $this->useCase->execute(new ChangeReservationInput(
            reservationId: $reservationId,
            name: $request->input('name'),
            startAt: new DateTimeImmutable($request->input('start_at')),
            endAt: new DateTimeImmutable($request->input('end_at')),
        ));

        return response()->noContent(Response::HTTP_NO_CONTENT);
    }
}
