@extends('layouts.main')

@php
    $linea = $operacion->lineas->first();
    $movimiento = $operacion->movimientos->first();
    $diferencia = $movimiento ? (int) $movimiento->cantidad : 0;
    $signo = $diferencia >= 0 ? '+' : '−';
@endphp

@section('titulo', $operacion->codigo)

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'sliders-horizontal',
        'title' => 'Ajuste de inventario',
        'description' => "Documento {$operacion->codigo} · {$operacion->almacen?->nombre}",
        'accent' => 'blue',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => 'Ajustes', 'url' => route('ajustes.index')],
            ['label' => $operacion->codigo],
        ],
    ])

    <div class="mb-5 flex flex-wrap items-center gap-2">
        <x-status-badge status="draft" label="Ajuste" />

        <span class="text-sm text-slate-500">
            Registrado por {{ $operacion->user?->name ?? 'Sistema' }}
            el <x-date :value="$operacion->created_at" :with-time="true" />
        </span>

        <div class="ms-auto flex flex-wrap items-center gap-2">
            <a href="{{ route('ajustes.index') }}"
                class="btn inline-flex items-center border-slate-200 bg-white text-slate-600 hover:text-nova-700">
                <i data-lucide="arrow-left" class="me-1.5 size-4"></i>
                Volver
            </a>

            <a href="{{ route('ajustes.create') }}"
                class="btn inline-flex items-center border-sky-500 bg-sky-500 text-white hover:bg-sky-600">
                <i data-lucide="plus" class="me-1.5 size-4"></i>
                Nuevo ajuste
            </a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => 'Stock anterior', 'value' => number_format((int) $linea?->cantidad - $diferencia, 0, ',', '.'), 'hint' => 'Antes del ajuste', 'icon' => 'history', 'tone' => 'bg-slate-100 text-slate-700'],
            ['label' => 'Diferencia', 'value' => $signo.number_format(abs($diferencia), 0, ',', '.'), 'hint' => 'Unidades ajustadas', 'icon' => $diferencia >= 0 ? 'trending-up' : 'trending-down', 'tone' => $diferencia >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'],
            ['label' => 'Stock final', 'value' => number_format((int) $linea?->cantidad, 0, ',', '.'), 'hint' => 'Cantidad resultante', 'icon' => 'boxes', 'tone' => 'bg-sky-50 text-sky-700'],
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
                        <h2 class="nova-card-title">Detalle del ajuste</h2>
                        <p class="nova-card-subtitle">Producto, almacén y cantidad resultante</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="nova-table-scroll">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Almacén</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Diferencia</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Stock final</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td class="px-3 py-3">
                                        @if ($linea?->producto)
                                            <a href="{{ route('productos.show', $linea->producto) }}"
                                                class="font-medium text-slate-700 hover:text-nova-700">
                                                {{ $linea->producto->nombre }}
                                            </a>
                                            <p class="text-xs text-slate-400">{{ $linea->producto->sku }}</p>
                                        @else
                                            <span class="text-sm text-slate-400">Producto eliminado</span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3 text-sm text-slate-600">
                                        {{ $operacion->almacen?->nombre ?? '—' }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm font-semibold is-numeric">
                                        {{ $signo }}{{ number_format(abs($diferencia), 0, ',', '.') }}
                                    </td>

                                    <td class="px-3 py-3 text-right text-sm font-semibold is-numeric">
                                        {{ number_format((int) $linea?->cantidad, 0, ',', '.') }}
                                    </td>
                                </tr>
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
                            <dd class="font-medium text-slate-700">Ajuste de stock</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-slate-500">Almacén</dt>
                            <dd class="text-right font-medium text-slate-700">{{ $operacion->almacen?->nombre ?? '—' }}</dd>
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
                        El ajuste quedó registrado como un movimiento auditable. El precio de compra
                        y el costo promedio no se modificaron.
                    </p>
                </div>
            </div>
        </aside>
    </div>
@endsection
