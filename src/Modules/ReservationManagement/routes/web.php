<?php

use Illuminate\Support\Facades\Route;
use Modules\ReservationManagement\Http\Controllers\ReservationManagement\ReservationManagementController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('reservationmanagements', ReservationManagementController::class)->names('reservationmanagement');
});
