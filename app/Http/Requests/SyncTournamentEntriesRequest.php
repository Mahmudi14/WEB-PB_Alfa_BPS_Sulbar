<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncTournamentEntriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->canManage()
            && $this->route('tournament')?->status === 'draft';
    }

    public function rules(): array
    {
        return [
            'entries' => ['required', 'array', 'min:2', 'max:32'],
            'entries.*.players' => ['required', 'array'],
            'entries.*.players.*' => ['required', 'integer', Rule::exists('players', 'id')->whereNull('deleted_at')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $number = $this->route('tournament')?->match_type === 'ganda' ? 2 : 1;
            $used = [];
            $entries = $this->input('entries', []);
            if (!is_array($entries)) return;
            foreach ($entries as $index => $entry) {
                $players = is_array($entry) ? ($entry['players'] ?? null) : null;
                if (!is_array($players) || count($players) !== $number) {
                    $validator->errors()->add("entries.$index", "Setiap peserta harus berisi {$number} pemain.");
                    continue;
                }
                foreach ($players as $id) {
                    if (!is_scalar($id) || !ctype_digit((string) $id)) continue;
                    if (isset($used[(string) $id])) {
                        $validator->errors()->add("entries.$index", 'Pemain yang sama tidak boleh didaftarkan lebih dari sekali dalam turnamen.');
                    }
                    $used[(string) $id] = true;
                }
            }
        });
    }
}
