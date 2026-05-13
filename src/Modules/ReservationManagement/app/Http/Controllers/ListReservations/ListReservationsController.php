<?php

namespace Modules\ReservationManagement\Http\Controllers\ListReservations;

use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Modules\ReservationManagement\Http\Presenters\ListReservations\JsonListReservationsView;
use Modules\ReservationManagement\Http\Presenters\ListReservations\ListReservationsPresenter;
use Modules\ReservationManagement\UseCase\ListReservations\ListReservationsUseCase;

class ListReservationsController extends Controller
{
    public function __construct(
        private ListReservationsUseCase $useCase,
    ) {}

    public function __invoke(ListReservationsRequest $request): JsonResponse
    {
        $items = $this->useCase->execute(new DateTimeImmutable($request->input('date')));
        $presenter = new ListReservationsPresenter($items);
        return (new JsonListReservationsView())->response($presenter);
    }
}
