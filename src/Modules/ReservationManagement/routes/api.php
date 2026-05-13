<?php

use Illuminate\Support\Facades\Route;
use Modules\ReservationManagement\Http\Controllers\CancelReservation\CancelReservationController;
use Modules\ReservationManagement\Http\Controllers\ChangeReservation\ChangeReservationController;
use Modules\ReservationManagement\Http\Controllers\CreateReservation\CreateReservationController;
use Modules\ReservationManagement\Http\Controllers\ListReservations\ListReservationsController;

Route::prefix('v1')->group(function () {
    Route::get('reservations', ListReservationsController::class);
    Route::post('reservations', CreateReservationController::class);
    Route::put('reservations/{reservationId}', ChangeReservationController::class);
    Route::delete('reservations/{reservationId}', CancelReservationController::class);
});
