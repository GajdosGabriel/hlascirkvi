{{-- Zmena v percentách proti predošlému obdobiu; null = nie je z čoho počítať. --}}
@if ($value !== null)
    <span @class([
        'ar-delta',
        'ar-delta--up' => $value > 0,
        'ar-delta--down' => $value < 0,
        'ar-delta--flat' => $value === 0,
    ])>{{ $value > 0 ? '▲' : ($value < 0 ? '▼' : '•') }} {{ abs($value) }} %</span>
@endif
