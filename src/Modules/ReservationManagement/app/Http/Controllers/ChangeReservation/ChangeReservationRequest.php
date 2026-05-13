<?php

namespace Modules\ReservationManagement\Http\Controllers\ChangeReservation;

use Illuminate\Foundation\Http\FormRequest;

class ChangeReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:1', 'max:50'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at'   => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
        ];
    }
}
