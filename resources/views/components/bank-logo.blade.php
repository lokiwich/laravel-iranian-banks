@php
    $markup = $bank?->iconSvg($variant);
@endphp

@if ($markup)
    <span
        {{ $attributes->merge([
            'class' => trim('iranian-banks-logo inline-flex items-center justify-center '.($class ?? '')),
            'role' => 'img',
            'aria-label' => $bank?->displayName(),
        ])->style([
            'width' => $width ? "{$width}px" : null,
            'height' => $height ? "{$height}px" : null,
            'display' => 'inline-flex',
        ]) }}
    >
        {!! $markup !!}
    </span>
@endif
