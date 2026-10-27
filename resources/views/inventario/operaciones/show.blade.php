@extends('layouts.main')

@php
    $esEntrada = $tipo === 'entrada';
    $rutaBase = $esEntrada ? 'entradas' : 'salidas';
    $titulo = $esEntrada ? 'Entrada de inventario' : 'Salida de inventario';
    $cantidadTotal = (int) $operacion->lineas->sum('cantidad');
@endphp

@section('titulo', $operacion->codigo)

@section('contenido')
    @include('layouts.page-header', [
        'icon' => $esEntrada ? 'arrow-down-to-line' : 'arrow-up-from-line',
        'title' => $titulo,
        'description' => "Documento {$operacion->codigo} · {$operacion->almacen?->nombre}",
        'accent' => $esEntrada ? 'green' : 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => $esEntrada ? 'Entradas' : 'Salidas', 'url' => route($rutaBase.'.index')],
            ['label' => $operacion->codigo],
        ],
    ])

    <div class="mb-5 flex flex-wrap items-center gap-2">
        <x-status-badge :status="$esEntrada ? 'ok' : 'low'" :label="ucfirst($operacion->tipo)" />

        <span class="text-sm text-slate-500">
            Registrado por {{ $operacion->user?->name ?? 'Sistema' }}
            el <x-date :value="$operacion->created_at" :with-time="true" />
        </span>

        <div class="ms-auto flex flex-wrap items-center gap-2">
            <a href="{{ route($rutaBase.'.index') }}"
                class="btn inline-flex items-center border-slate-200 bg-white text-slate-600 hover:text-nova-700">
                <i data-lucide="arrow-left" class="me-1.5 size-4"></i>
                Volver
            </a>

            <a href="{{ route(($esEntrada ? 'entradas' : 'salidas').'.create') }}"
                class="btn inline-flex items-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                <i data-lucide="plus" class="me-1.5 size-4"></i>
                Nueva {{ $esEntrada ? 'entrada' : 'salida' }}
            </a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => 'Líneas', 'value' => number_format($operacion->lineas->count(), 0, ',', '.'), 'hint' => 'Productos en el documento', 'icon' => 'list', 'tone' => 'bg-sky-50 text-sky-700'],
            ['label' => 'Unidades', 'value' => number_format($cantidadTotal, 0, ',', '.'), 'hint' => 'Cantidad total', 'icon' => 'boxes', 'tone' => 'bg-nova-50 text-nova-700'],
            ['label' => 'Valor', 'value' => '$'.number_format((float) $operacion->total, 2, ',', '.'), 'hint' => $esEntrada ? 'Costo de compra' : 'Costo promedio', 'icon' => 'wallet', 'tone' => 'bg-amber-50 text-amber-700'],
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
        <div class="xl:col-span-2">
            <div class="card border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Líneas del documento</h2>
                        <p class="nova-card-subtitle">Detalle de productos y costos</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="nova-table-scroll">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Costo unitario</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($operacion->lineas as $linea)
                                    <tr>
                                        <td class="px-3 py-3">
                                            @if ($linea->producto)
                                                <a href="{{ route('productos.show', $linea->producto) }}"
                                                    class="font-medium text-slate-700 hover:text-nova-700">
                                                    {{ $linea->producto->nombre }}
                                                </a>
                                                <p class="text-xs text-slate-400">{{ $linea->producto->sku }}</p>
                                            @else
                                                <span class="text-sm text-slate-400">Producto eliminado</span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-3 text-right text-sm font-semibold is-numeric">
                                            {{ number_format((int) $linea->cantidad, 0, ',', '.') }}
                                        </td>

                                        <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric">
                                            ${{ number_format((float) $linea->costo_unitario, 2, ',', '.') }}
                                        </td>

                                        <td class="px-3 py-3 text-right text-sm font-semibold is-numeric">
                                            ${{ number_format((float) $linea->subtotal, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="bg-slate-50">
                                    <td class="px-3 py-3 text-right text-sm font-semibold text-slate-600" colspan="3">Total</td>
                                    <td class="px-3 py-3 text-right text-sm font-bold is-numeric text-slate-900">
                                        ${{ number_format((float) $operacion->total, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-5 border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Trazabilidad</h2>
                        <p class="nova-card-subtitle">Movimientos generados por esta operación</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="nova-table-scroll">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Fecha</th>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Saldo</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Costo</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($operacion->movimientos as $movimiento)
                                    <tr>
                                        <td class="px-3 py-3 text-sm text-slate-500">
                                            <x-date :value="$movimiento->created_at" :with-time="true" />
                                        </td>
                                        <td class="px-3 py-3 text-sm text-slate-700">
                                            {{ $movimiento->producto?->nombre ?? '—' }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm font-semibold is-numeric">
                                            {{ $movimiento->tipo === 'entrada' ? '+' : '−' }}{{ number_format((int) $movimiento->cantidad, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric">
                                            {{ number_format((int) $movimiento->saldo_resultante, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric">
                                            ${{ number_format((float) $movimiento->costo_unitario, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <x-table-empty icon="history" title="Sin movimientos asociados" />
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
                    <h2 class="text-sm font-semibold text-slate-800">Datos del documento</h2>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Código</dt>
                            <dd class="font-semibold text-slate-700">{{ $operacion->codigo }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Tipo</dt>
                            <dd class="font-medium text-slate-700">{{ ucfirst($operacion->tipo) }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Almacén</dt>
                            <dd class="text-right font-medium text-slate-700">{{ $operacion->almacen?->nombre ?? '—' }}</dd>
                        </div>
                        @if ($esEntrada)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500">Proveedor</dt>
                                <dd class="text-right font-medium text-slate-700">
                                    @if ($operacion->proveedor)
                                        @can('manage-proveedores')
                                            <a href="{{ route('proveedores.edit', $operacion->proveedor) }}"
                                                class="text-nova-700 hover:text-nova-900">
                                                {{ $operacion->proveedor->nombre }}
                                            </a>
                                        @else
                                            {{ $operacion->proveedor->nombre }}
                                        @endcan
                                    @else
                                        Sin proveedor
                                    @endif
                                </dd>
                            </div>
                        @endif
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Referencia</dt>
                            <dd class="text-right font-medium text-slate-700">{{ $operacion->referencia ?: '—' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Registrado por</dt>
                            <dd class="text-right font-medium text-slate-700">{{ $operacion->user?->name ?? 'Sistema' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Fecha</dt>
                            <dd class="text-right font-medium text-slate-700">
                                <x-date :value="$operacion->created_at" :with-time="true" />
                            </dd>
                        </div>
                    </dl>

                    @if ($operacion->notas)
                        <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">
                            {{ $operacion->notas }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
                <div class="flex items-start gap-2">
                    <i data-lucide="shield-check" class="mt-0.5 size-4 shrink-0"></i>
                    <p class="leading-6">
                        Cada línea generó un movimiento con saldo resultante. La operación se aplicó
                        dentro de una transacción y no puede deshacerse desde la interfaz.
                    </p>
                </div>
            </div>
        </aside>
    </div>
@endsection
