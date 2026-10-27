@extends('layouts.main')

@section('titulo', 'Ajustes de inventario')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'sliders-horizontal',
        'title' => 'Ajustes de inventario',
        'description' => 'Correcciones de stock sin modificar precios ni crear productos',
        'accent' => 'blue',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => 'Ajustes'],
        ],
    ])

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Ajustes registrados', 'value' => number_format($resumen['operaciones'], 0, ',', '.'), 'hint' => 'Documentos de ajuste', 'icon' => 'files', 'tone' => 'bg-sky-50 text-sky-700'],
            ['label' => 'Productos ajustados', 'value' => number_format($resumen['lineas'], 0, ',', '.'), 'hint' => 'Líneas de detalle', 'icon' => 'package', 'tone' => 'bg-nova-50 text-nova-700'],
            ['label' => 'Aumentos', 'value' => number_format($resumen['aumentos'], 0, ',', '.'), 'hint' => 'Stock incrementado', 'icon' => 'trending-up', 'tone' => 'bg-emerald-50 text-emerald-700'],
            ['label' => 'Disminuciones', 'value' => number_format($resumen['disminuciones'], 0, ',', '.'), 'hint' => 'Stock reducido', 'icon' => 'trending-down', 'tone' => 'bg-amber-50 text-amber-700'],
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
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">Ajustes registrados</span>
                <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700">
                    {{ $operaciones->total() }}
                </span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('ajustes.index') }}" role="search" class="relative w-full sm:w-64">
                    <label for="ajuste-search" class="sr-only">Buscar ajuste</label>
                    <input id="ajuste-search" type="search" name="search" value="{{ $search }}"
                        class="form-input nova-input-icon w-full border-slate-200 pe-9 dark:border-zink-500 dark:bg-zink-700"
                        placeholder="Buscar código, producto o SKU...">
                    <i data-lucide="search"
                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"></i>
                </form>

                <a href="{{ route('ajustes.create') }}"
                    class="btn inline-flex items-center justify-center border-sky-500 bg-sky-500 text-white hover:bg-sky-600">
                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                    Nuevo ajuste
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
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Stock final</th>
                            <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($operaciones as $operacion)
                            @php
                                $linea = $operacion->lineas->first();
                            @endphp

                            <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">
                                <td class="px-3 py-3">
                                    <a href="{{ route('ajustes.show', $operacion) }}"
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

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    @if ($linea?->producto)
                                        {{ $linea->producto->nombre }}
                                        <p class="text-xs text-slate-400">{{ $linea->producto->sku }}</p>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-semibold text-slate-700 is-numeric dark:text-zink-100">
                                    {{ number_format((int) ($operacion->cantidad_total ?? 0), 0, ',', '.') }}
                                </td>

                                <td class="px-2 py-3">
                                    <div class="flex justify-center">
                                        <a href="{{ route('ajustes.show', $operacion) }}" title="Ver detalle"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:border-nova-500 hover:text-nova-500">
                                            <i data-lucide="eye" class="size-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-table-empty icon="sliders-horizontal"
                                        :title="$search !== '' ? 'Sin resultados' : 'No hay ajustes registrados'"
                                        :message="$search !== ''
                                            ? 'No encontramos coincidencias para la búsqueda.'
                                            : 'Los ajustes de stock aparecerán aquí cuando se registren.'">
                                        <a href="{{ route('ajustes.create') }}"
                                            class="btn border-sky-500 bg-sky-500 text-white hover:bg-sky-600">
                                            <i data-lucide="plus" class="me-1.5 size-4"></i>
                                            Registrar ajuste
                                        </a>
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$operaciones" label="ajustes" />
        </div>
    </div>
@endsection
