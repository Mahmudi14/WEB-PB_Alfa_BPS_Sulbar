<nav class="tr-steps" aria-label="Tahap pembuatan turnamen">
    @foreach(['Aturan', 'Pemain', 'Bagan', 'Selesai'] as $i => $label)
        <div class="tr-step {{ ($step ?? 1) === $i + 1 ? 'is-current' : '' }} {{ ($step ?? 1) > $i + 1 ? 'is-done' : '' }}" @if(($step ?? 1) === $i + 1) aria-current="step" @endif>
            <span>{{ $i + 1 }}</span>
            <strong>{{ $label }}</strong>
        </div>
    @endforeach
</nav>
