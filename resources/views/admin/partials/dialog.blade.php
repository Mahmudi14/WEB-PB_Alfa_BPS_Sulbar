<dialog id="adm-dialog" class="adm-dialog" aria-labelledby="adm-dialog-title" aria-describedby="adm-dialog-message">

    <div class="adm-dialog__body">

        <div class="adm-dialog__symbol" id="adm-dialog-symbol" aria-hidden="true">
            ?
        </div>

        <h2 id="adm-dialog-title">
            Konfirmasi
        </h2>

        <p id="adm-dialog-message"></p>

        <ul id="adm-dialog-errors" class="adm-dialog__errors" hidden></ul>

        <div class="adm-dialog__actions">

            <button type="button" id="adm-dialog-cancel" class="adm-btn adm-btn--secondary">
                Batal
            </button>

            <button type="button" id="adm-dialog-ok" class="adm-btn adm-btn--primary">
                Mengerti
            </button>

        </div>

    </div>

</dialog>


@if($errors->any())

<div id="adm-server-errors" hidden>

    @foreach($errors->all() as $message)

    <p data-admin-error>
        {{ $message }}
    </p>

    @endforeach

</div>

@endif