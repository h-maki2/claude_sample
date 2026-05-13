<?php

namespace Modules\ReservationManagement\Http\Controllers\CreateReservation;

use DateTimeImmutable;
use Illuminate\Http\Response;
use Modules\ReservationManagement\UseCase\CreateReservation\CreateReservationInput;
use Modules\ReservationManagement\UseCase\CreateReservation\CreateReservationUseCase;

class CreateReservationController
{
    public function __construct(
        private CreateReservationUseCase $useCase,
    ) {}

    public function __invoke(CreateReservationRequest $request): Response
    {
        $this->useCase->execute(new CreateReservationInput(
            meetingRoomId: $request->input('meeting_room_id'),
            name: $request->input('name'),
            contactPerson: $request->input('contact_person'),
            email: $request->input('email'),
            startAt: new DateTimeImmutable($request->input('start_at')),
            endAt: new DateTimeImmutable($request->input('end_at')),
        ));

        return response()->noContent(Response::HTTP_CREATED);
    }
}
