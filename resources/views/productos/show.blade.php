@extends('layouts.main')

@section('titulo', $producto->nombre)

@section('contenido')
    @php
        $stockTotal = (int) $stockTotal;
        $valorInventario = $producto->inventarios->sum(fn ($inventario) => $inventario->valorTotal());
        $estado = ! $producto->activo ? 'inactive' : ($stockTotal <= 0 ? 'out' : ($stockTotal <= (int) $producto->stock_minimo ? 'low' : 'ok'));
        $estadoLabel = match ($estado) {
            'inactive' => 'Inactivo',
            'out' => 'Sin stock',
            'low' => 'Bajo mínimo',
            default => 'Disponible',
        };
    @endphp

    @include('layouts.page-header', [
        'icon' => 'package',
        'title' => $producto->nombre,
        'description' => "SKU {$producto->sku} · {$producto->categoriaProducto?->nombre}",
        'accent' => 'green',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Productos', 'url' => route('productos.index')],
            ['label' => $producto->sku],
        ],
    ])

    <div class="mb-5 flex flex-wrap items-center gap-2">
        <x-status-badge :status="$estado" :label="$estadoLabel" />

        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
            <i data-lucide="{{ $producto->unidadMedida?->abreviatura ? 'ruler' : 'box' }}" class="size-3.5"></i>
            {{ $producto->unidadMedida?->nombre ?? 'Sin unidad' }}
        </span>

        <div class="ms-auto flex flex-wrap items-center gap-2">
            <a href="{{ route('productos.index') }}"
                class="btn inline-flex items-center border-slate-200 bg-white text-slate-600 hover:text-nova-700">
                <i data-lucide="arrow-left" class="me-1.5 size-4"></i>
                Volver
            </a>

            @can('registrar-movimientos')
                <a href="{{ route('salidas.create', ['producto' => $producto->id, 'almacen' => $producto->inventarios->first()?->almacen_id]) }}"
                    class="btn inline-flex items-center border-amber-500 bg-amber-500 text-white hover:bg-amber-600">
                    <i data-lucide="arrow-up-from-line" class="me-1.5 size-4"></i>
                    Salida
                </a>

                <a href="{{ route('entradas.create', ['producto' => $producto->id, 'almacen' => $producto->inventarios->first()?->almacen_id]) }}"
                    class="btn inline-flex items-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="arrow-down-to-line" class="me-1.5 size-4"></i>
                    Entrada
                </a>

                <a href="{{ route('ajustes.create', ['inventario' => $producto->inventarios->first()?->id]) }}"
                    class="btn inline-flex items-center border-sky-500 bg-sky-500 text-white hover:bg-sky-600">
                    <i data-lucide="sliders-horizontal" class="me-1.5 size-4"></i>
                    Ajustar stock
                </a>
            @endcan

            @can('manage-productos')
                <a href="{{ route('productos.edit', $producto) }}"
                    class="btn inline-flex items-center border-sky-500 bg-sky-500 text-white hover:bg-sky-600">
                    <i data-lucide="pencil" class="me-1.5 size-4"></i>
                    Editar
                </a>
            @endcan
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Stock total', 'value' => number_format($stockTotal, 0, ',', '.'), 'hint' => 'En '.$producto->inventarios->count().' almacén(es)', 'icon' => 'boxes', 'tone' => 'bg-sky-50 text-sky-700'],
            ['label' => 'Valor del inventario', 'value' => '$'.number_format($valorInventario, 2, ',', '.'), 'hint' => 'Costo promedio', 'icon' => 'wallet', 'tone' => 'bg-nova-50 text-nova-700'],
            ['label' => 'Precio de compra', 'value' => '$'.number_format((float) $producto->precio_compra, 2, ',', '.'), 'hint' => 'Última compra registrada', 'icon' => 'shopping-cart', 'tone' => 'bg-amber-50 text-amber-700'],
            ['label' => 'Precio de venta', 'value' => '$'.number_format((float) $producto->precio_venta, 2, ',', '.'), 'hint' => 'Margen bruto: $'.number_format((float) $producto->precio_venta - (float) $producto->precio_compra, 2, ',', '.'), 'icon' => 'tag', 'tone' => 'bg-emerald-50 text-emerald-700'],
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

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <div class="card border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Existencias por almacén</h2>
                        <p class="nova-card-subtitle">Cantidad disponible y costo promedio</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="nova-table-scroll">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Almacén</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Costo promedio</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Valor</th>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Último movimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($producto->inventarios as $inventario)
                                    <tr>
                                        <td class="px-3 py-3 text-sm font-medium text-slate-700 dark:text-zink-100">
                                            {{ $inventario->almacen?->nombre ?? '—' }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm font-semibold is-numeric">
                                            {{ number_format((int) $inventario->cantidad, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric">
                                            ${{ number_format((float) $inventario->costo_promedio, 2, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric">
                                            ${{ number_format($inventario->valorTotal(), 2, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-slate-500">
                                            @if ($inventario->ultimo_movimiento_at)
                                                <x-date :value="$inventario->ultimo_movimiento_at" :with-time="true" />
                                            @else
                                                Sin movimientos
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <x-table-empty icon="warehouse"
                                                title="Sin existencias registradas"
                                                message="Registra una entrada para asignar stock a este producto." />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Movimientos recientes</h2>
                        <p class="nova-card-subtitle">Últimos 20 movimientos auditables</p>
                    </div>

                    @can('registrar-movimientos')
                        <a href="{{ route('inventario.index', ['search' => $producto->sku]) }}"
                            class="text-sm font-semibold text-nova-700 hover:text-nova-900">
                            Ver inventario
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="nova-table-scroll">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Fecha</th>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Tipo</th>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Operación</th>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Almacén</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movimientos as $movimiento)
                                    @php
                                        $esEntrada = $movimiento->tipo === 'entrada';
                                        $esAjuste = $movimiento->tipo === 'ajuste';
                                        $rutaOperacion = match ($movimiento->tipo) {
                                            'entrada' => 'entradas.show',
                                            'salida' => 'salidas.show',
                                            'ajuste' => 'ajustes.show',
                                            default => null,
                                        };
                                        $signo = $esAjuste
                                            ? ((int) $movimiento->cantidad >= 0 ? '+' : '−')
                                            : ($esEntrada ? '+' : '−');
                                        $cantidadMovimiento = $esAjuste
                                            ? abs((int) $movimiento->cantidad)
                                            : (int) $movimiento->cantidad;
                                    @endphp

                                    <tr>
                                        <td class="px-3 py-3 text-sm text-slate-500">
                                            <x-date :value="$movimiento->created_at" :with-time="true" />
                                        </td>
                                        <td class="px-3 py-3">
                                            <x-status-badge
                                                :status="$esEntrada ? 'ok' : ($esAjuste ? 'draft' : 'low')"
                                                :label="ucfirst($movimiento->tipo)" :dot="false" />
                                        </td>
                                        <td class="px-3 py-3 text-sm">
                                            @if ($movimiento->operacion && $rutaOperacion)
                                                <a href="{{ route($rutaOperacion, $movimiento->operacion) }}"
                                                    class="font-semibold text-nova-700 hover:text-nova-900">
                                                    {{ $movimiento->operacion->codigo }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-sm text-slate-600">{{ $movimiento->almacen?->nombre ?? '—' }}</td>
                                        <td class="px-3 py-3 text-right text-sm font-semibold is-numeric">
                                            {{ $signo }}{{ number_format($cantidadMovimiento, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric">
                                            {{ number_format((int) $movimiento->saldo_resultante, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <x-table-empty icon="history"
                                                title="Sin movimientos"
                                                message="Las entradas, salidas y ajustes del producto aparecerán aquí." />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <aside class="space-y-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="text-sm font-semibold text-slate-800">Ficha del producto</h2>

                    <div class="mt-4 flex justify-center">
                        <x-product-image :producto="$producto" class="size-32 rounded-2xl" />
                    </div>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">SKU</dt>
                            <dd class="font-semibold text-slate-700">{{ $producto->sku }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Categoría</dt>
                            <dd class="text-right font-medium text-slate-700">{{ $producto->categoriaProducto?->nombre ?? '—' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Unidad</dt>
                            <dd class="font-medium text-slate-700">
                                {{ $producto->unidadMedida?->nombre }}
                                @if ($producto->unidadMedida?->abreviatura)
                                    ({{ $producto->unidadMedida->abreviatura }})
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Stock mínimo</dt>
                            <dd class="font-medium text-slate-700 is-numeric">
                                {{ number_format((int) $producto->stock_minimo, 0, ',', '.') }}
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Registrado</dt>
                            <dd class="text-right font-medium text-slate-700">
                                <x-date :value="$producto->created_at" />
                            </dd>
                        </div>
                    </dl>

                    @if ($producto->descripcion)
                        <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">
                            {{ $producto->descripcion }}
                        </div>
                    @endif
                </div>
            </div>

            @if ($stockTotal <= (int) $producto->stock_minimo)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <div class="flex items-start gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-amber-600">
                            <i data-lucide="alert-triangle" class="size-5"></i>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-amber-800">Reposición recomendada</p>
                            <p class="mt-1 text-xs leading-5 text-amber-700">
                                El stock total está en o por debajo del mínimo configurado.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @can('manage-productos')
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="text-sm font-semibold text-slate-800">Zona administrativa</h2>

                        <div class="mt-3 space-y-2">
                            <a href="{{ route('productos.edit', $producto) }}"
                                class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2.5 text-sm hover:border-nova-300 hover:bg-nova-50">
                                Editar datos
                                <i data-lucide="chevron-right" class="size-4 text-slate-400"></i>
                            </a>

                            <form method="POST" action="{{ route('productos.destroy', $producto) }}"
                                onsubmit="return confirm('¿Eliminar este producto? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="flex w-full items-center justify-between rounded-lg border border-rose-200 px-3 py-2.5 text-sm text-rose-600 hover:bg-rose-50">
                                    Eliminar producto
                                    <i data-lucide="trash-2" class="size-4"></i>
                                </button>
                            </form>
                        </div>

                        <p class="mt-3 text-xs leading-5 text-slate-400">
                            Solo se puede eliminar un producto sin movimientos ni existencias.
                        </p>
                    </div>
                </div>
            @endcan
        </aside>
    </div>
@endsection
