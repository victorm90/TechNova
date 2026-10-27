@extends('layouts.main')

@section('titulo', $tipo === 'entrada' ? 'Entradas de inventario' : 'Salidas de inventario')

@php
    $esEntrada = $tipo === 'entrada';
    $rutaBase = $esEntrada ? 'entradas' : 'salidas';
    $titulo = $esEntrada ? 'Entradas de inventario' : 'Salidas de inventario';
    $descripcion = $esEntrada
        ? 'Recepciones de mercancía con múltiples líneas de detalle'
        : 'Despachos de mercancía con control de existencias';
@endphp

@section('contenido')
    @include('layouts.page-header', [
        'icon' => $esEntrada ? 'arrow-down-to-line' : 'arrow-up-from-line',
        'title' => $titulo,
        'description' => $descripcion,
        'accent' => $esEntrada ? 'green' : 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => $esEntrada ? 'Entradas' : 'Salidas'],
        ],
    ])

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => 'Operaciones', 'value' => number_format($resumen['operaciones'], 0, ',', '.'), 'hint' => 'Documentos registrados', 'icon' => 'files', 'tone' => 'bg-sky-50 text-sky-700'],
            ['label' => 'Unidades', 'value' => number_format($resumen['unidades'], 0, ',', '.'), 'hint' => 'Cantidad acumulada', 'icon' => 'boxes', 'tone' => 'bg-nova-50 text-nova-700'],
            ['label' => 'Valor', 'value' => '$'.number_format($resumen['valor'], 2, ',', '.'), 'hint' => $esEntrada ? 'Costo de las compras' : 'Costo promedio despachado', 'icon' => 'wallet', 'tone' => 'bg-amber-50 text-amber-700'],
        ] as $card)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900 is-numeric">{{ $card['value'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $card['hint'] }}</p>
                        </div>
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $card['tone'] }}">
                            <i data-lucide="{{ $card['icon'] }}" class="size-5"></i>
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="nova-table-card card overflow-hidden bg-white dark:bg-zink-700">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zink-500">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">
                    {{ $esEntrada ? 'Entradas registradas' : 'Salidas registradas' }}
                </span>
                <span class="inline-flex items-center rounded-full bg-nova-50 px-2.5 py-0.5 text-xs font-medium text-nova-700">
                    {{ $operaciones->total() }}
                </span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route($rutaBase.'.index') }}" role="search" class="relative w-full sm:w-64">
                    <label for="operacion-search" class="sr-only">Buscar operación</label>
                    <input id="operacion-search" type="search" name="search" value="{{ $search }}"
                        class="form-input nova-input-icon w-full border-slate-200 pe-9 dark:border-zink-500 dark:bg-zink-700"
                        placeholder="Buscar código, referencia...">
                    <i data-lucide="search"
                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"></i>
                </form>

                <a href="{{ route($rutaBase.'.create') }}"
                    class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                    {{ $esEntrada ? 'Nueva entrada' : 'Nueva salida' }}
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="nova-table-scroll w-full">
                <table class="nova-table w-full">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Código</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Fecha</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Almacén</th>
                            @if ($esEntrada)
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Proveedor</th>
                            @endif
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Referencia</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Líneas</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Unidades</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Total</th>
                            <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($operaciones as $operacion)
                            <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">
                                <td class="px-3 py-3">
                                    <a href="{{ route($rutaBase.'.show', $operacion) }}"
                                        class="font-semibold text-nova-700 hover:text-nova-900">
                                        {{ $operacion->codigo }}
                                    </a>
                                    <p class="text-xs text-slate-400">{{ $operacion->user?->name ?? 'Sistema' }}</p>
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-500">
                                    <x-date :value="$operacion->created_at" :with-time="true" />
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $operacion->almacen?->nombre ?? '—' }}
                                </td>

                                @if ($esEntrada)
                                    <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                        {{ $operacion->proveedor?->nombre ?? 'Sin proveedor' }}
                                    </td>
                                @endif

                                <td class="px-3 py-3 text-sm text-slate-500">
                                    {{ $operacion->referencia ?: '—' }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric">
                                    {{ number_format((int) $operacion->lineas_count, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-semibold text-slate-700 is-numeric dark:text-zink-100">
                                    {{ number_format((int) ($operacion->cantidad_total ?? 0), 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-semibold text-slate-700 is-numeric dark:text-zink-100">
                                    ${{ number_format((float) $operacion->total, 2, ',', '.') }}
                                </td>

                                <td class="px-2 py-3">
                                    <div class="flex justify-center">
                                        <a href="{{ route($rutaBase.'.show', $operacion) }}" title="Ver detalle"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:border-nova-500 hover:text-nova-500">
                                            <i data-lucide="eye" class="size-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $esEntrada ? 9 : 8 }}">
                                    <x-table-empty icon="{{ $esEntrada ? 'arrow-down-to-line' : 'arrow-up-from-line' }}"
                                        :title="$search !== '' ? 'Sin resultados' : ($esEntrada ? 'No hay entradas registradas' : 'No hay salidas registradas')"
                                        :message="$search !== ''
                                            ? 'No encontramos coincidencias para la búsqueda.'
                                            : 'Las operaciones aparecerán aquí cuando se registren.'">
                                        <a href="{{ route($rutaBase.'.create') }}"
                                            class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                            <i data-lucide="plus" class="me-1.5 size-4"></i>
                                            {{ $esEntrada ? 'Registrar entrada' : 'Registrar salida' }}
                                        </a>
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$operaciones" label="operaciones" />
        </div>
    </div>
@endsection
