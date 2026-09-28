{{--
    Flash → toast bridge.
    Renders hidden `data-flash-*` attributes that assets/js/app.js reads on boot and
    turns into toasts. A <noscript> block keeps the message visible without JS.
--}}
@php
    $fhFlash = array_filter([
        'success' => session('success'),
        'error' => session('error'),
        'info' => session('info'),
        'warning' => session('warning'),
    ], function ($value) {
        return is_scalar($value) && (string) $value !== '';
    });
@endphp

@if($fhFlash !== [])
    <div
        id="fh-flash"
        class="fh-flash-data"
        hidden
        @foreach($fhFlash as $fhKind => $fhMessage) data-flash-{{ $fhKind }}="{{ $fhMessage }}" @endforeach
    ></div>

    <noscript>
        <div class="container-fluid px-3 pt-3">
            @foreach($fhFlash as $fhKind => $fhMessage)
                <div class="alert alert-{{ $fhKind === 'error' ? 'danger' : ($fhKind === 'warning' ? 'warning' : ($fhKind === 'success' ? 'success' : 'info')) }} mb-2" role="alert">
                    <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                    <span>{{ $fhMessage }}</span>
                </div>
            @endforeach
        </div>
    </noscript>
@endif
