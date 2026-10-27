@props(['producto'])

<img src="{{ $producto->imagenUrl() }}" alt="Imagen de {{ $producto->nombre }}"
    {{ $attributes->merge(['class' => 'size-9 shrink-0 rounded-xl object-cover ring-1 ring-slate-200 dark:ring-zink-500']) }}
    loading="lazy">
