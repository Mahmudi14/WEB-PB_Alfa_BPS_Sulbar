@php
$editing = isset($transaction) && $transaction?->exists;
$isAuto = $editing
&& $transaction->shuttlecock_player_id !== null;

$type = old(
'type',
$editing ? $transaction->type : 'pemasukan'
);
@endphp

<form class="cash-form" method="POST" action="{{ $editing
        ? route('cash.update', $transaction)
        : route('cash.store') }}" data-single-submit>
    @csrf

    @if($editing)
    @method('PUT')
    @endif

    {{-- =====================================================
    1. VALIDASI FORM
    ===================================================== --}}

    @if($errors->any())
    <div class="cash-alert" role="alert">
        <strong>Periksa kembali data transaksi.</strong>

        <ul>
            @foreach($errors->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- =====================================================
    2. DATA TRANSAKSI
    ===================================================== --}}

    <div class="cash-form__grid">

        {{-- Tanggal --}}

        <div class="cash-field cash-field--date">
            <label for="transaction_date">
                Tanggal Transaksi
            </label>

            <input id="transaction_date" @readonly($isAuto) type="date" name="transaction_date" value="{{ old(
                    'transaction_date',
                    $editing
                        ? \Illuminate\Support\Carbon::parse(
                            $transaction->transaction_date
                        )->format('Y-m-d')
                        : now()->toDateString()
                ) }}" required>
        </div>

        {{-- Nominal --}}

        <div class="cash-field cash-field--amount">
            <label for="amount">
                Nominal Transaksi
            </label>

            <div class="cash-money">
                <span aria-hidden="true">Rp</span>

                <input id="amount" type="number" name="amount" value="{{ old(
                        'amount',
                        $editing ? $transaction->amount : ''
                    ) }}" placeholder="0" min="{{ $isAuto ? 0 : 1 }}" step="1" inputmode="numeric" required>
            </div>
        </div>

        {{-- Jenis transaksi --}}

        <fieldset class="cash-field cash-field--type">
            <legend>Jenis Transaksi</legend>

            <div class="cash-type">

                <label>
                    <input type="radio" name="type" value="pemasukan" @checked($type==='pemasukan' ) @disabled($isAuto)
                        required>

                    <span>Pemasukan</span>
                </label>

                <label>
                    <input type="radio" name="type" value="pengeluaran" @checked($type==='pengeluaran' )>

                    <span>Pengeluaran</span>
                </label>

            </div>
        </fieldset>

        {{-- Keterangan --}}

        <div class="cash-field cash-field--description">
            <label for="description">
                Keterangan
            </label>

            <textarea id="description" name="description" rows="2" @readonly($isAuto)
                placeholder="Masukkan keterangan transaksi" required>{{ old(
                'description',
                $editing ? $transaction->description : ''
            ) }}</textarea>
        </div>

    </div>

    {{-- =====================================================
    3. TOMBOL AKSI
    ===================================================== --}}

    <div class="cash-form__actions">

        <button type="submit" class="cash-submit">
            {{ $editing
            ? 'Simpan Perubahan'
            : 'Simpan Transaksi' }}
        </button>

        <a href="{{ route('cash.index') }}" class="cash-cancel">
            Batal
        </a>

    </div>

</form>