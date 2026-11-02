<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => [
                'nullable',
                Rule::in(['25', '50', '100']),
            ],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 25);

        $transactions = CashTransaction::query()
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $summary = DB::table('cash_transactions')
            ->whereNull('deleted_at')
            ->selectRaw("
                COALESCE(SUM(
                    CASE WHEN type = 'pemasukan'
                    THEN amount ELSE 0 END
                ), 0) AS income
            ")
            ->selectRaw("
                COALESCE(SUM(
                    CASE WHEN type = 'pengeluaran'
                    THEN amount ELSE 0 END
                ), 0) AS expense
            ")
            ->first();

        $income = (int) $summary->income;
        $expense = (int) $summary->expense;
        $balance = $income - $expense;

        return view('admin.modules.cash', compact(
            'transactions',
            'income',
            'expense',
            'balance',
            'perPage'
        ));
    }

    public function create(Request $request)
    {
        $this->ensureAdmin($request);

        return view('cash.create');
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $data = $this->validatedTransaction($request);

        CashTransaction::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.cash.index')
            ->with(
                'success',
                'Transaksi kas berhasil ditambahkan.'
            );
    }

    public function edit(
        Request $request,
        CashTransaction $transaction
    ) {
        $this->ensureAdmin($request);

        return view('cash.edit', compact('transaction'));
    }

    public function update(
        Request $request,
        CashTransaction $transaction
    ) {
        $this->ensureAdmin($request);

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
            ->route('admin.cash.index')
            ->with(
                'success',
                'Transaksi kas berhasil diperbarui.'
            );
    }

    public function destroy(
        Request $request,
        CashTransaction $transaction
    ) {
        $this->ensureAdmin($request);

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
            ->route('admin.cash.index')
            ->with(
                'success',
                'Transaksi kas berhasil dihapus.'
            );
    }

    private function validatedTransaction(Request $request): array
    {
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
                Rule::in(['pemasukan', 'pengeluaran']),
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

    private function ensureAdmin(Request $request): void
    {
        abort_unless(
            $request->user()
                && $request->user()->is_active
                && $request->user()->isAdmin(),
            403
        );
    }
}
