<?php

namespace Modules\ReservationManagement\Http\Controllers\CreateReservation;

use Illuminate\Foundation\Http\FormRequest;

class CreateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'meeting_room_id' => ['required', 'string'],
            'name'            => ['required', 'string', 'min:1', 'max:50'],
            'contact_person'  => ['required', 'string', 'min:1', 'max:30'],
            'email'           => ['required', 'email'],
            'start_at'        => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at'          => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
