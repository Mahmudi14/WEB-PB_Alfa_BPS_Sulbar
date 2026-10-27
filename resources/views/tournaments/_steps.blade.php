@php
$isLiga = $isLiga
?? (isset($tournament) && $tournament->format === 'liga');

$steps = [
'Aturan',
'Pemain',
$isLiga ? 'Daftar' : 'Bagan',
'Selesai',
];
@endphp

<nav class="tr-steps" aria-label="Tahap pembuatan turnamen">

    @foreach($steps as $i => $label)

    @php
    $number = $i + 1;
    $currentStep = $step ?? 1;
    @endphp

    <div class="tr-step
                {{ $currentStep === $number ? 'is-current' : '' }}
                {{ $currentStep > $number ? 'is-done' : '' }}" @if($currentStep===$number) aria-current="step" @endif>

        <span>{{ $number }}</span>
        <strong>{{ $label }}</strong>

    </div>

    @endforeach

</nav>