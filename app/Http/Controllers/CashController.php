<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashController extends Controller
{
    // HALAMAN KAS PUBLIK


    public function index(Request $request)
    {
        // Hanya izinkan 25, 50, atau 100 data.
        $validated = $request->validate([
            'per_page' => [
                'nullable',
                Rule::in(['25', '50', '100']),
            ],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 25);

        // Riwayat terbaru dan pagination.
        $transactions = CashTransaction::query()
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        // Ringkasan menghitung SELURUH transaksi aktif,
        // bukan hanya transaksi di halaman sekarang.
        $summary = DB::table('cash_transactions')
            ->whereNull('deleted_at')
            ->selectRaw("
            COALESCE(
                SUM(
                    CASE
                        WHEN type = 'pemasukan'
                        THEN amount ELSE 0
                    END
                ), 0
            ) AS income
        ")
            ->selectRaw("
            COALESCE(
                SUM(
                    CASE
                        WHEN type = 'pengeluaran'
                        THEN amount ELSE 0
                    END
                ), 0
            ) AS expense
        ")
            ->first();

        $income = (int) $summary->income;
        $expense = (int) $summary->expense;
        $balance = $income - $expense;

        return view('cash.index', compact(
            'transactions',
            'income',
            'expense',
            'balance',
            'perPage'
        ));
    }


    // FORM TAMBAH

    public function create(Request $request)
    {
        $this->ensureManager($request);

        return view('cash.create');
    }

    // SIMPAN TRANSAKSI

    public function store(Request $request)
    {
        $this->ensureManager($request);

        $data = $this->validatedTransaction($request);

        CashTransaction::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('cash.index')
            ->with(
                'success',
                'Transaksi kas berhasil ditambahkan.'
            );
    }

    // FORM EDIT

    public function edit(
        Request $request,
        CashTransaction $transaction
    ) {
        $this->ensureManager($request);

        return view('cash.edit', compact('transaction'));
    }

    // UPDATE TRANSAKSI


    public function update(
        Request $request,
        CashTransaction $transaction
    ) {
        $this->ensureManager($request);

        $isAutomatic =
            $transaction->shuttlecock_player_id !== null;

        if ($isAutomatic) {
            $data = $request->validate([
                'amount' => [
                    'required',
                    'integer',
                    'between:0,999999999999',
                ],
            ]);

            $transaction->update([
                'amount' => $data['amount'],
                'shuttlecock_review_required' => false,
                'updated_by' => $request->user()->id,
            ]);

            // Jika koreksi pertandingan membuat kok menjadi nol,
            // dan admin sudah mengosongkan uangnya, sembunyikan
            // catatan otomatis yang tidak lagi diperlukan.
            if (
                (int) $transaction->shuttlecock_count === 0
                && (int) $transaction->amount === 0
            ) {
                $transaction->delete();
            }
        } else {
            $data = $this->validatedTransaction($request);

            $transaction->update([
                ...$data,
                'updated_by' => $request->user()->id,
            ]);
        }

        return redirect()
            ->route('cash.index')
            ->with(
                'success',
                'Transaksi kas berhasil diperbarui.'
            );
    }


    // SOFT DELETE TRANSAKSI

    public function destroy(
        Request $request,
        CashTransaction $transaction
    ) {
        $this->ensureManager($request);
        abort_if(
            $transaction->shuttlecock_player_id !== null,
            403,
            'Catatan kok otomatis tidak dapat dihapus langsung.'
        );

        DB::transaction(function () use (
            $request,
            $transaction
        ) {
            $transaction->deleted_by = $request->user()->id;
            $transaction->save();

            $transaction->delete();
        });

        return redirect()
            ->route('cash.index')
            ->with(
                'success',
                'Transaksi kas berhasil dihapus.'
            );
    }

    // VALIDASI EMPAT INPUT

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

    // KEAMANAN AKSES

    private function ensureManager(
        Request $request
    ): void {
        abort_unless(
            $request->user()
                && $request->user()->is_active
                && $request->user()->canManage(),
            403
        );
    }
}
