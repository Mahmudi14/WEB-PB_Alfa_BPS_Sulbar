<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active
            && $this->user()->canManage();
    }

    public function rules(): array
    {
        $isLiga = $this->input('format') === 'liga';

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1500',
            ],

            'format' => [
                'required',
                Rule::in(['liga', 'cup']),
            ],

            'match_type' => [
                'required',
                Rule::in(['tunggal', 'ganda']),
            ],

            'set_format' => [
                'required',
                Rule::in([
                    'two_draw',
                    'two_aggregate',
                    'best_of_three',
                ]),
                Rule::prohibitedIf(
                    fn() =>
                    $this->input('format') === 'cup'
                        && $this->input('set_format') === 'two_draw'
                ),
            ],

            'target_points' => [
                'required',
                Rule::in([15, 21, '15', '21']),
            ],

            'league_win_points' => [
                Rule::excludeIf(!$isLiga),
                'required',
                'integer',
                'between:-100,100',
            ],

            'league_draw_points' => [
                Rule::excludeIf(!$isLiga),
                'required',
                'integer',
                'between:-100,100',
            ],

            'league_loss_points' => [
                Rule::excludeIf(!$isLiga),
                'required',
                'integer',
                'between:-100,100',
            ],

            'league_legs' => [
                Rule::excludeIf(!$isLiga),
                'required',
                Rule::in([1, 2, '1', '2']),
            ],
        ];
    }
}
