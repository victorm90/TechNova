@extends('layouts.main')

@section('titulo', 'Inventario')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'warehouse',
        'title' => 'Inventario',
        'description' => 'Existencias por producto y almacén con costo promedio',
        'accent' => 'blue',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Inventario'],
        ],
    ])

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Unidades en stock', 'value' => number_format($resumen['unidades'], 0, ',', '.'), 'hint' => 'Suma de todos los almacenes', 'icon' => 'boxes', 'tone' => 'bg-sky-50 text-sky-700'],
            ['label' => 'Valor del inventario', 'value' => '$'.number_format($resumen['valor'], 2, ',', '.'), 'hint' => 'Cantidad por costo promedio', 'icon' => 'wallet', 'tone' => 'bg-nova-50 text-nova-700'],
            ['label' => 'Registros bajo mínimo', 'value' => number_format($resumen['bajo_minimo'], 0, ',', '.'), 'hint' => 'Producto y almacén', 'icon' => 'alert-triangle', 'tone' => 'bg-amber-50 text-amber-700'],
            ['label' => 'Almacenes activos', 'value' => number_format($resumen['almacenes'], 0, ',', '.'), 'hint' => 'Disponibles para operar', 'icon' => 'building-2', 'tone' => 'bg-emerald-50 text-emerald-700'],
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
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 xl:flex-row xl:items-center xl:justify-between dark:border-zink-500">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">Existencias</span>
                <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700">
                    {{ $inventarios->total() }}
                </span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                <form method="GET" action="{{ route('inventario.index') }}" role="search"
                    class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:w-56">
                        <label for="inventario-search" class="sr-only">Buscar producto</label>
                        <input id="inventario-search" type="search" name="search" value="{{ $search }}"
                            class="form-input nova-input-icon w-full border-slate-200 pe-9 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Buscar producto o SKU...">
                        <i data-lucide="search"
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"></i>
                    </div>

                    <label for="inventario-almacen" class="sr-only">Filtrar por almacén</label>
                    <select id="inventario-almacen" name="almacen"
                        class="form-select w-full border-slate-200 sm:w-44 dark:border-zink-500 dark:bg-zink-700">
                        <option value="">Todos los almacenes</option>
                        @foreach ($almacenes as $almacen)
                            <option value="{{ $almacen->id }}" @selected($almacenId === $almacen->id)>
                                {{ $almacen->nombre }}
                            </option>
                        @endforeach
                    </select>

                    <label class="flex items-center gap-2 whitespace-nowrap text-sm text-slate-600">
                        <input type="checkbox" name="bajo" value="1" class="size-4 rounded border-slate-300"
                            @checked($soloBajo)>
                        Solo bajo mínimo
                    </label>

                    <button type="submit" class="btn border-slate-200 bg-white text-slate-600 hover:border-nova-300">
                        Filtrar
                    </button>
                </form>

                @can('registrar-movimientos')
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('salidas.create') }}"
                            class="btn inline-flex items-center justify-center border-amber-500 bg-amber-500 text-white hover:bg-amber-600">
                            <i data-lucide="arrow-up-from-line" class="me-1.5 size-4"></i>
                            Salida
                        </a>

                        <a href="{{ route('entradas.create') }}"
                            class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                            <i data-lucide="arrow-down-to-line" class="me-1.5 size-4"></i>
                            Entrada
                        </a>

                        <a href="{{ route('ajustes.create') }}"
                            class="btn inline-flex items-center justify-center border-sky-500 bg-sky-500 text-white hover:bg-sky-600">
                            <i data-lucide="sliders-horizontal" class="me-1.5 size-4"></i>
                            Ajuste
                        </a>

                        <a href="{{ route('ajustes.index') }}"
                            class="btn inline-flex items-center justify-center border-slate-200 bg-white text-slate-600 hover:text-sky-600">
                            <i data-lucide="history" class="me-1.5 size-4"></i>
                            Historial
                        </a>
                    </div>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="nova-table-scroll w-full">
                <table class="nova-table w-full">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Almacén</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Categoría</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Mínimo</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Costo prom.</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Valor</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($inventarios as $inventario)
                            @php
                                $producto = $inventario->producto;
                                $cantidad = (int) $inventario->cantidad;
                                $minimo = (int) ($producto?->stock_minimo ?? 0);
                                $umbralCerca = $minimo > 0 ? (int) ceil($minimo * 1.2) : 0;

                                if ($cantidad <= 0) {
                                    $estado = 'out';
                                    $estadoLabel = 'Sin stock';
                                    $minimoClase = 'text-rose-600 dark:text-rose-400';
                                    $minimoIcono = 'circle-x';
                                    $minimoTexto = 'Sin existencias';
                                } elseif ($cantidad < $minimo) {
                                    $estado = 'out';
                                    $estadoLabel = 'Bajo mínimo';
                                    $minimoClase = 'text-rose-600 dark:text-rose-400';
                                    $minimoIcono = 'trending-down';
                                    $minimoTexto = 'Por debajo del mínimo';
                                } elseif ($minimo > 0 && $cantidad <= $umbralCerca) {
                                    $estado = 'low';
                                    $estadoLabel = 'Cerca del mínimo';
                                    $minimoClase = 'text-amber-600 dark:text-amber-400';
                                    $minimoIcono = 'triangle-alert';
                                    $minimoTexto = 'Cerca del mínimo';
                                } else {
                                    $estado = 'ok';
                                    $estadoLabel = 'Disponible';
                                    $minimoClase = 'text-emerald-600 dark:text-emerald-400';
                                    $minimoIcono = 'circle-check';
                                    $minimoTexto = 'Stock saludable';
                                }
                            @endphp

                            <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">
                                <td class="px-3 py-3">
                                    @if ($producto)
                                        <a href="{{ route('productos.show', $producto) }}"
                                            class="block truncate font-medium text-slate-700 hover:text-nova-700 dark:text-zink-100">
                                            {{ $producto->nombre }}
                                        </a>
                                        <p class="text-xs text-slate-400">{{ $producto->sku }}</p>
                                    @else
                                        <span class="text-sm text-slate-400">Producto eliminado</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $inventario->almacen?->nombre ?? '—' }}
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $producto?->categoriaProducto?->nombre ?? '—' }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-semibold text-slate-700 is-numeric dark:text-zink-100">
                                    {{ number_format($cantidad, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-3 text-right is-numeric">
                                    <span class="inline-flex items-center justify-end gap-1 text-sm font-semibold {{ $minimoClase }}"
                                        title="{{ $minimoTexto }}">
                                        <i data-lucide="{{ $minimoIcono }}" class="size-3.5"></i>
                                        {{ number_format($minimo, 0, ',', '.') }}
                                    </span>
                                    <p class="mt-0.5 text-[11px] {{ $minimoClase }}">{{ $minimoTexto }}</p>
                                </td>

                                <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric">
                                    ${{ number_format((float) $inventario->costo_promedio, 2, ',', '.') }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm text-slate-700 is-numeric dark:text-zink-100">
                                    ${{ number_format($inventario->valorTotal(), 2, ',', '.') }}
                                </td>

                                <td class="px-3 py-3">
                                    <x-status-badge :status="$estado" :label="$estadoLabel" :dot="false" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-table-empty icon="warehouse"
                                        :title="$search !== '' || $almacenId > 0 || $soloBajo ? 'Sin resultados' : 'No hay existencias registradas'"
                                        :message="$search !== '' || $almacenId > 0 || $soloBajo
                                            ? 'No encontramos coincidencias con los filtros aplicados.'
                                            : 'Registra una entrada de inventario para comenzar.'">
                                        @can('registrar-movimientos')
                                            <a href="{{ route('entradas.create') }}"
                                                class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                                <i data-lucide="plus" class="me-1.5 size-4"></i>
                                                Registrar entrada
                                            </a>
                                        @endcan
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$inventarios" label="existencias" />
        </div>
    </div>
@endsection
