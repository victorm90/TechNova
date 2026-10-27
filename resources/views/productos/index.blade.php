@extends('layouts.main')

@section('titulo', 'Productos')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'package',
        'title' => 'Productos',
        'description' => 'Catálogo con existencias consolidadas por almacén',
        'accent' => 'green',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Productos'],
        ],
    ])

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Productos', 'value' => number_format($resumen['total'], 0, ',', '.'), 'hint' => 'Registrados en el catálogo', 'icon' => 'package', 'tone' => 'bg-sky-50 text-sky-700'],
            ['label' => 'Activos', 'value' => number_format($resumen['activos'], 0, ',', '.'), 'hint' => 'Disponibles para operar', 'icon' => 'circle-check', 'tone' => 'bg-emerald-50 text-emerald-700'],
            ['label' => 'Bajo mínimo', 'value' => number_format($resumen['bajo_minimo'], 0, ',', '.'), 'hint' => 'Requieren reposición', 'icon' => 'alert-triangle', 'tone' => 'bg-amber-50 text-amber-700'],
            ['label' => 'Valor del inventario', 'value' => '$'.number_format($resumen['valor'], 0, ',', '.'), 'hint' => 'Según costo promedio', 'icon' => 'wallet', 'tone' => 'bg-nova-50 text-nova-700'],
        ] as $card)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                                {{ $card['label'] }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $card['value'] }}</p>
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
        <div class="nova-progress-bar h-1 w-full"></div>

        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between dark:border-zink-500">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">Catálogo</span>
                <span class="inline-flex items-center rounded-full bg-nova-50 px-2.5 py-0.5 text-xs font-medium text-nova-700">
                    {{ $productos->total() }}
                </span>
            </div>

            <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto">
                <form method="GET" action="{{ route('productos.index') }}" role="search"
                    class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:w-64">
                        <label for="producto-search" class="sr-only">Buscar producto</label>
                        <input id="producto-search" type="search" name="search" value="{{ $search }}"
                            class="form-input nova-input-icon w-full border-slate-200 pe-9 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Buscar por nombre o SKU...">
                        <i data-lucide="search"
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"></i>
                    </div>

                    <label for="producto-categoria" class="sr-only">Filtrar por categoría</label>
                    <select id="producto-categoria" name="categoria"
                        class="form-select w-full border-slate-200 sm:w-48 dark:border-zink-500 dark:bg-zink-700">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected($categoriaId === $categoria->id)>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn border-slate-200 bg-white text-slate-600 hover:border-nova-300">
                        Filtrar
                    </button>
                </form>

                @can('manage-productos')
                    <a href="{{ route('productos.create') }}"
                        class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                        <i data-lucide="plus" class="me-1.5 size-4"></i>
                        Nuevo producto
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="nova-table-scroll w-full">
                <table class="nova-table w-full">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Categoría</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Unidad</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Compra</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Stock</th>
                            <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($productos as $producto)
                            @php
                                $stock = (int) ($producto->stock_total ?? 0);
                                $minimo = (int) $producto->stock_minimo;
                                $estado = ! $producto->activo
                                    ? 'inactive'
                                    : ($stock <= 0 ? 'out' : ($stock <= $minimo ? 'low' : 'ok'));
                                $estadoLabel = match ($estado) {
                                    'inactive' => 'Inactivo',
                                    'out' => 'Sin stock',
                                    'low' => 'Bajo mínimo',
                                    default => 'Disponible',
                                };
                            @endphp

                            <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-product-image :producto="$producto" />
                                        <div class="min-w-0">
                                            <a href="{{ route('productos.show', $producto) }}"
                                                class="block truncate font-medium text-slate-700 hover:text-nova-700 dark:text-zink-100"
                                                title="{{ $producto->nombre }}">
                                                {{ $producto->nombre }}
                                            </a>
                                            <p class="text-xs text-slate-400">{{ $producto->sku }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $producto->categoriaProducto?->nombre ?? '—' }}
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $producto->unidadMedida?->abreviatura ?? $producto->unidadMedida?->nombre ?? '—' }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric dark:text-zink-200">
                                    ${{ number_format((float) $producto->precio_compra, 2, ',', '.') }}
                                </td>

                                <td class="px-3 py-3 text-right">
                                    <p class="text-sm font-semibold text-slate-700 is-numeric dark:text-zink-100">
                                        {{ number_format($stock, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[11px] text-slate-400">mín. {{ number_format($minimo, 0, ',', '.') }}</p>
                                </td>

                                <td class="px-2 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <x-status-badge :status="$estado" :label="$estadoLabel" :dot="false" />

                                        <a href="{{ route('productos.show', $producto) }}" title="Ver producto"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:border-nova-500 hover:text-nova-500 dark:border-zink-500">
                                            <i data-lucide="eye" class="size-4"></i>
                                        </a>

                                        @can('manage-productos')
                                            <a href="{{ route('productos.edit', $producto) }}" title="Editar producto"
                                                class="inline-flex size-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-600 hover:bg-sky-100 dark:border-sky-900/50 dark:bg-sky-900/20">
                                                <i data-lucide="pencil" class="size-4"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-table-empty icon="package"
                                        :title="$search !== '' || $categoriaId > 0 ? 'Sin resultados' : 'No hay productos registrados'"
                                        :message="$search !== '' || $categoriaId > 0
                                            ? 'No encontramos coincidencias con los filtros aplicados.'
                                            : 'Crea tu primer producto para comenzar a controlar el inventario.'">
                                        @can('manage-productos')
                                            <a href="{{ route('productos.create') }}"
                                                class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                                <i data-lucide="plus" class="me-1.5 size-4"></i>
                                                Nuevo producto
                                            </a>
                                        @endcan
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$productos" label="productos" />
        </div>
    </div>
@endsection
