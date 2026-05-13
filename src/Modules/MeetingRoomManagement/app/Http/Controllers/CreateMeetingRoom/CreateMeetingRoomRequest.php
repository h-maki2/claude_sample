<?php

namespace Modules\MeetingRoomManagement\Http\Controllers\CreateMeetingRoom;

use Illuminate\Foundation\Http\FormRequest;

class CreateMeetingRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: 管理者ロールのチェックを実装する（PRD非機能要件: セキュリティ）
        // 現状は auth:sanctum ミドルウェアで認証のみ担保
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'min:1', 'max:50'],
            'capacity'     => ['required', 'integer', 'min:1', 'max:50'],
            'equipments'   => ['nullable', 'array'],
            'equipments.*' => ['integer'],
        ];
    }
}
