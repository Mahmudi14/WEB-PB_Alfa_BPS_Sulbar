
@php
    $editing = isset($player) && $player?->exists;

    $gender = old(
        'gender',
        $editing ? $player->gender : 'L'
    );
@endphp

<form
    class="player-form"
    method="POST"
    action="{{ $editing
        ? route('players.update', $player)
        : route('players.store') }}"
    data-single-submit
>
    @csrf

    @if($editing)
        @method('PUT')
    @endif

    {{-- Validasi --}}

    @if($errors->any())
        <div class="player-form__alert" role="alert">
            <strong>Periksa kembali data pemain.</strong>

            <ul>
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Data pemain --}}

    <div class="player-form__grid">

        <div class="player-field">
            <label for="player-name">
                Nama Pemain <span class="player-required">*</span>
            </label>

            <input
                id="player-name"
                type="text"
                name="name"
                value="{{ old(
                    'name',
                    $editing ? $player->name : ''
                ) }}"
                placeholder="Nama lengkap pemain"
                maxlength="255"
                autocomplete="name"
                required
                autofocus
            >
        </div>

        <div class="player-field">
            <label for="player-email">
                Email Pemain
                <span class="player-optional">Opsional</span>
            </label>

            <input
                id="player-email"
                type="email"
                name="email"
                value="{{ old(
                    'email',
                    $editing ? $player->email : ''
                ) }}"
                placeholder="contoh@email.com"
                maxlength="255"
                autocomplete="email"
            >

            <small>
                Isi jika pemain membutuhkan akun login.
            </small>
        </div>

        {{-- Jenis kelamin --}}

        <fieldset class="player-field player-gender">
            <legend>
                Jenis Kelamin <span class="player-required">*</span>
            </legend>

            <div class="player-gender__options">

                <label class="player-gender__option">
                    <input
                        type="radio"
                        name="gender"
                        value="L"
                        @checked($gender === 'L')
                        required
                    >

                    <span>Laki-laki</span>
                </label>

                <label class="player-gender__option">
                    <input
                        type="radio"
                        name="gender"
                        value="P"
                        @checked($gender === 'P')
                        required
                    >

                    <span>Perempuan</span>
                </label>

            </div>
        </fieldset>

        {{-- Skor awal --}}

        <div class="player-field">
            <label for="player-score">
                Skor Awal <span class="player-required">*</span>
            </label>

            <input
                id="player-score"
                type="number"
                name="initial_score"
                value="{{ old(
                    'initial_score',
                    $editing ? $player->initial_score : 0
                ) }}"
                step="1"
                inputmode="numeric"
                placeholder="0"
                required
            >
        </div>

    </div>

    {{-- Informasi akun --}}

    @if($editing && $player->email)
        <p class="player-form__note">
            Mengosongkan email akan menonaktifkan akses login
            akun pemain yang terhubung, tanpa menghapus
            data pemain.
        </p>
    @endif

    {{-- Tombol --}}

    <div class="player-form__actions">

        <button
            type="submit"
            class="player-form__submit"
        >
            {{ $editing ? 'Simpan Perubahan' : 'Simpan Pemain' }}
        </button>

        <a
            href="{{ route('players.index') }}"
            class="player-form__cancel"
        >
            Batal
        </a>

    </div>

</form>
