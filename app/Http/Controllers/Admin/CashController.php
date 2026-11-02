<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'jenis' => [
                'nullable',
                Rule::in(['pemasukan', 'pengeluaran']),
            ],
            'sumber' => [
                'nullable',
                Rule::in(['manual', 'otomatis']),
            ],
            'per_page' => [
                'nullable',
                Rule::in(['25', '50', '100']),
            ],
        ]);

        $cari = trim($filters['cari'] ?? '');
        $jenis = $filters['jenis'] ?? '';
        $sumber = $filters['sumber'] ?? '';
        $perPage = (int) ($filters['per_page'] ?? 25);

        $transactions = CashTransaction::query()
            ->when(
                $cari !== '',
                fn($query) => $query->where(
                    'description',
                    'like',
                    '%' . $cari . '%'
                )
            )
            ->when(
                $jenis !== '',
                fn($query) => $query->where(
                    'type',
                    $jenis
                )
            )
            ->when(
                $sumber === 'manual',
                fn($query) => $query->whereNull(
                    'shuttlecock_player_id'
                )
            )
            ->when(
                $sumber === 'otomatis',
                fn($query) => $query->whereNotNull(
                    'shuttlecock_player_id'
                )
            )
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $summary = CashTransaction::query()
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN type = 'pemasukan'
                        THEN amount
                        ELSE 0
                    END
                ), 0) AS income
            ")
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN type = 'pengeluaran'
                        THEN amount
                        ELSE 0
                    END
                ), 0) AS expense
            ")
            ->first();

        $income = (int) ($summary?->income ?? 0);
        $expense = (int) ($summary?->expense ?? 0);
        $balance = $income - $expense;

        $reviewCount = CashTransaction::query()
            ->whereNotNull('shuttlecock_player_id')
            ->where('shuttlecock_review_required', true)
            ->count();

        return response()
            ->view('admin.cash.index', compact(
                'transactions',
                'income',
                'expense',
                'balance',
                'reviewCount',
                'cari',
                'jenis',
                'sumber',
                'perPage'
            ))
            ->header(
                'Cache-Control',
                'private, no-store, max-age=0'
            );
    }

    public function show(CashTransaction $transaction)
    {
        $transaction->load(['creator', 'updater']);

        $player = $transaction->shuttlecock_player_id !== null
            ? Player::withTrashed()->find(
                $transaction->shuttlecock_player_id
            )
            : null;

        return response()
            ->view('admin.cash.show', compact(
                'transaction',
                'player'
            ))
            ->header(
                'Cache-Control',
                'private, no-store, max-age=0'
            );
    }

    public function create()
    {
        return view('admin.cash.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedTransaction($request);

        $transaction = CashTransaction::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.cash.show', $transaction)
            ->with(
                'success',
                'Transaksi kas berhasil ditambahkan.'
            );
    }

    public function edit(CashTransaction $transaction)
    {
        $transaction->load(['creator', 'updater']);

        return view('admin.cash.edit', compact(
            'transaction'
        ));
    }

    public function update(
        Request $request,
        CashTransaction $transaction
    ) {
        $isAutomatic =
            $transaction->shuttlecock_player_id !== null;

        $data = $isAutomatic
            ? $request->validate([
                'amount' => [
                    'required',
                    'integer',
                    'between:0,999999999999',
                ],
            ])
            : $this->validatedTransaction($request);

        $deletedAutomatically = false;

        DB::transaction(function () use (
            $transaction,
            $data,
            $request,
            &$deletedAutomatically
        ) {
            $locked = CashTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->shuttlecock_player_id !== null) {
                $locked->update([
                    'amount' => $data['amount'],
                    'shuttlecock_review_required' => false,
                    'updated_by' => $request->user()->id,
                ]);

                if (
                    (int) $locked->shuttlecock_count === 0
                    && (int) $locked->amount === 0
                ) {
                    $locked->delete();
                    $deletedAutomatically = true;
                }

                return;
            }

            abort_if(
                $transaction->shuttlecock_player_id !== null,
                409,
                'Jenis transaksi telah berubah. Muat ulang halaman.'
            );

            $locked->update([
                ...$data,
                'updated_by' => $request->user()->id,
            ]);
        }, 3);

        if ($deletedAutomatically) {
            return redirect()
                ->route('admin.cash.index')
                ->with(
                    'success',
                    'Koreksi tersimpan. Catatan kok nol telah disembunyikan.'
                );
        }

        return redirect()
            ->route('admin.cash.show', $transaction->id)
            ->with(
                'success',
                'Transaksi kas berhasil diperbarui.'
            );
    }

    public function destroy(
        Request $request,
        CashTransaction $transaction
    ) {
        DB::transaction(function () use (
            $request,
            $transaction
        ) {
            $locked = CashTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if(
                $locked->shuttlecock_player_id !== null,
                403,
                'Catatan kok otomatis tidak dapat dihapus langsung.'
            );

            $locked->deleted_by = $request->user()->id;
            $locked->save();
            $locked->delete();
        }, 3);

        return redirect()
            ->route('admin.cash.index')
            ->with(
                'success',
                'Transaksi kas berhasil dihapus.'
            );
    }

    private function validatedTransaction(
        Request $request
    ): array {
        $request->merge([
            'description' => trim(
                (string) $request->input('description', '')
            ),
        ]);

        return $request->validate([
            'transaction_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'type' => [
                'required',
                Rule::in([
                    'pemasukan',
                    'pengeluaran',
                ]),
            ],
            'amount' => [
                'required',
                'integer',
                'between:1,999999999999',
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'transaction_date.required' =>
            'Tanggal wajib diisi.',
            'type.required' =>
            'Pilih pemasukan atau pengeluaran.',
            'amount.required' =>
            'Nominal wajib diisi.',
            'amount.min' =>
            'Nominal harus lebih besar dari nol.',
            'description.required' =>
            'Keterangan wajib diisi.',
        ]);
    }
}
