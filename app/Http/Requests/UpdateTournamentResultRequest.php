<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTournamentResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->canManage();
    }

    public function rules(): array
    {
        return [
            'scores' => ['nullable', 'array', 'max:3'],
            'scores.*' => ['array'],
            'scores.*.a' => ['nullable', 'integer', 'between:0,999'],
            'scores.*.b' => ['nullable', 'integer', 'between:0,999'],
            'jus_a' => ['nullable', 'integer', 'between:0,999'],
            'jus_b' => ['nullable', 'integer', 'between:0,999'],
            'shuttlecock_count' => ['required', 'integer', 'between:0,65535'],
            'confirm_reset' => ['nullable', 'boolean'],
            'played_on' => [
                'required',
                'date_format:Y-m-d',
            ],
        ];
    }
}
