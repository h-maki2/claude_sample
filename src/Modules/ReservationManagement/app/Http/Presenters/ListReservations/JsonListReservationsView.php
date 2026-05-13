<?php

namespace Modules\ReservationManagement\Http\Presenters\ListReservations;

use Illuminate\Http\JsonResponse;

class JsonListReservationsView
{
    public function response(ListReservationsPresenter $presenter): JsonResponse
    {
        return response()->json(
            $this->buildResponseArray($presenter),
            200,
        );
    }

    /** @return array<string, mixed> */
    private function buildResponseArray(ListReservationsPresenter $presenter): array
    {
        return [
            'reservations' => $presenter->getReservations(),
        ];
    }
}
