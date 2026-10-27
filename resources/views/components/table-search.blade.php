@props([
    'action',
    'search' => '',
    'placeholder' => 'Buscar...',
    'id' => 'table-search',
])

<form method="GET" action="{{ $action }}" class="relative w-72 max-w-full" role="search" data-table-search>
    <label for="{{ $id }}" class="sr-only">{{ $placeholder }}</label>

    <input id="{{ $id }}" type="search" name="search" value="{{ $search }}"
        class="form-input nova-input-icon w-full border-slate-200 pe-9 focus:border-nova-500 focus:ring-0 dark:border-zink-500 dark:bg-zink-700"
        placeholder="{{ $placeholder }}" autocomplete="off">

    <i data-lucide="search"
        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"></i>

    <button type="submit" class="sr-only">Buscar</button>

    @if ($search !== '')
        <a href="{{ $action }}" title="Limpiar búsqueda" aria-label="Limpiar búsqueda"
            class="absolute right-2 top-1/2 flex size-6 -translate-y-1/2 items-center justify-center rounded text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-zink-600">
            <i data-lucide="x" class="size-4"></i>
        </a>
    @endif
</form>
