<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateTournamentScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->canManage()
            && $this->route('tournament')?->status === 'draft';
    }

    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:2', 'max:32'],
            'order.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
