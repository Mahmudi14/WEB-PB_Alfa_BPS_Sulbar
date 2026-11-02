<dialog id="pb-dialog" class="pb-dialog" aria-labelledby="pb-dialog-title" aria-describedby="pb-dialog-message">

    <div class="pb-dialog__content">

        <div class="pb-dialog__icon" aria-hidden="true">
            <span id="pb-dialog-symbol">!</span>
        </div>

        <div class="pb-dialog__heading">
            <h2 id="pb-dialog-title">
                Konfirmasi
            </h2>

            <p id="pb-dialog-message"></p>
        </div>

        <ul id="pb-dialog-errors" hidden></ul>

        <div class="pb-dialog__actions">

            <button type="button" id="pb-dialog-cancel" class="pb-dialog__cancel">
                Batal
            </button>

            <button type="button" id="pb-dialog-ok" class="pb-dialog__ok">
                Mengerti
            </button>

        </div>

    </div>

</dialog>

@if($errors->any() || session('error'))

<div id="pb-dialog-server-errors" hidden>

    @if(session('error'))
    <p data-pb-error>
        {{ session('error') }}
    </p>
    @endif

    @foreach($errors->all() as $message)
    <p data-pb-error>
        {{ $message }}
    </p>
    @endforeach

</div>

@endif