@props(['value', 'format' => 'd/m/Y', 'withTime' => false])

@php
    $date = $value instanceof \Illuminate\Support\Carbon || $value instanceof \Carbon\Carbon
        ? $value->copy()
        : \Carbon\Carbon::parse($value);

    $iso = $withTime ? $date->toIso8601String() : $date->toDateString();
    $display = $date->locale('es')->translatedFormat($withTime ? $format.' · H:i' : $format);
@endphp

<time datetime="{{ $iso }}" title="{{ $iso }}" {{ $attributes }}>{{ $display }}</time>
