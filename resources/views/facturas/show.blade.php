@extends('layouts.main')

@section('titulo', 'Factura '.$factura->codigo)

@php
    $moneda = config('empresa.moneda', 'MN');
    $servicios = $factura->lineasDe(\App\Models\FacturaLinea::TIPO_SERVICIO);
    $productos = $factura->lineasDe(\App\Models\FacturaLinea::TIPO_PRODUCTO);
    $gastos = $factura->lineasDe(\App\Models\FacturaLinea::TIPO_GASTO);
@endphp

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'receipt-text',
        'title' => 'Factura '.$factura->codigo,
        'description' => 'Documento final generado a partir de la oferta '.$factura->oferta?->codigo,
        'accent' => 'green',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Ofertas', 'url' => route('ofertas.index')],
            ['label' => $factura->oferta?->codigo ?? 'Oferta', 'url' => $factura->oferta ? route('ofertas.show', $factura->oferta) : null],
            ['label' => $factura->codigo],
        ],
    ])

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Cliente</p>
                        <p class="mt-1 font-medium text-slate-700 dark:text-zink-100">
                            {{ $factura->customer?->name ?? '—' }}
                        </p>
                        @if ($factura->customer?->phone)
                            <p class="text-xs text-slate-400">{{ $factura->customer->phone }}</p>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Fecha</p>
                        <p class="mt-1 font-medium text-slate-700 dark:text-zink-100">
                            {{ $factura->fecha?->format('d/m/Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Oferta de origen</p>
                        @if ($factura->oferta)
                            <a href="{{ route('ofertas.show', $factura->oferta) }}"
                                class="mt-1 block font-medium text-nova-600 hover:underline dark:text-nova-400">
                                {{ $factura->oferta->codigo }}
                            </a>
                        @else
                            <p class="mt-1 font-medium text-slate-700 dark:text-zink-100">—</p>
                        @endif
                        <p class="text-xs text-slate-400">
                            {{ $factura->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Almacén descontado</p>
                        <p class="mt-1 font-medium text-slate-700 dark:text-zink-100">
                            {{ $factura->almacen?->nombre ?? '—' }}
                        </p>
                        <p class="text-xs text-slate-400">
                            Facturada por {{ $factura->user?->name ?? 'Sistema' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-semibold text-slate-700 dark:text-zink-100">Servicios</h2>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ $servicios->count() }} {{ $servicios->count() === 1 ? 'línea' : 'líneas' }} facturadas
                            </p>
                        </div>
                    </div>

                    <div class="nova-table-scroll mt-3 w-full">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Descripción</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Precio unitario</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($servicios as $linea)
                                    <tr>
                                        <td class="px-3 py-3 text-sm font-medium text-slate-700 dark:text-zink-100">
                                            {{ $linea->descripcion }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                            {{ number_format((float) $linea->cantidad, 2, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                            <x-money :amount="$linea->precio_unitario" />
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                            <x-money :amount="$linea->subtotal" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-sm text-slate-400">
                                            La factura no incluye servicios.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-3 py-3 text-right text-sm font-semibold text-slate-600 dark:text-zink-200">
                                        Subtotal servicios
                                    </td>
                                    <td class="px-3 py-3 text-right text-sm font-bold is-numeric text-slate-700 dark:text-zink-100">
                                        <x-money :amount="$factura->subtotal_servicios" />
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold text-slate-700 dark:text-zink-100">Recursos y materiales</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Productos descontados del almacén al facturar.</p>
                        </div>
                        <span class="text-xs text-slate-400">{{ $productos->count() }} productos</span>
                    </div>

                    <div class="nova-table-scroll mt-3 w-full">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Precio venta</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($productos as $linea)
                                    <tr>
                                        <td class="px-3 py-3 text-sm font-medium text-slate-700 dark:text-zink-100">
                                            {{ $linea->descripcion }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                            {{ number_format((float) $linea->cantidad, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                            <x-money :amount="$linea->precio_unitario" />
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                            <x-money :amount="$linea->subtotal" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-sm text-slate-400">
                                            La factura no incluye productos de almacén.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-3 py-3 text-right text-sm font-semibold text-slate-600 dark:text-zink-200">
                                        Subtotal materiales
                                    </td>
                                    <td class="px-3 py-3 text-right text-sm font-bold is-numeric text-slate-700 dark:text-zink-100">
                                        <x-money :amount="$factura->subtotal_productos" />
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-semibold text-slate-700 dark:text-zink-100">Gastos</h2>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ $gastos->count() }} {{ $gastos->count() === 1 ? 'línea' : 'líneas' }} facturadas
                            </p>
                        </div>
                    </div>

                    <div class="nova-table-scroll mt-3 w-full">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Descripción</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Precio unitario</th>
                                    <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($gastos as $linea)
                                    <tr>
                                        <td class="px-3 py-3 text-sm font-medium text-slate-700 dark:text-zink-100">
                                            {{ $linea->descripcion }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                            {{ number_format((float) $linea->cantidad, 2, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                            <x-money :amount="$linea->precio_unitario" />
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                            <x-money :amount="$linea->subtotal" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-sm text-slate-400">
                                            La factura no incluye gastos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-3 py-3 text-right text-sm font-semibold text-slate-600 dark:text-zink-200">
                                        Subtotal gastos
                                    </td>
                                    <td class="px-3 py-3 text-right text-sm font-bold is-numeric text-slate-700 dark:text-zink-100">
                                        <x-money :amount="$factura->subtotal_gastos" />
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if ($factura->nota || $factura->observaciones)
                <div class="card border-0 shadow-sm">
                    <div class="card-body space-y-3">
                        @if ($factura->nota)
                            <div>
                                <h2 class="font-semibold text-slate-700 dark:text-zink-100">Nota</h2>
                                <p class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-zink-200">
                                    {{ $factura->nota }}
                                </p>
                            </div>
                        @endif

                        @if ($factura->observaciones)
                            <div>
                                <h2 class="font-semibold text-slate-700 dark:text-zink-100">Observaciones</h2>
                                <p class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-zink-200">
                                    {{ $factura->observaciones }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <aside class="xl:sticky xl:top-24 xl:self-start">
            <div class="card border-0 shadow-sm">
                <div class="card-body space-y-4">
                    <div>
                        <h2 class="font-semibold text-slate-700 dark:text-zink-100">Totales de la factura</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Valores congelados al confirmar la facturación.</p>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-zink-300">Servicios</span>
                            <span class="font-semibold text-slate-700 dark:text-zink-100">
                                <x-money :amount="$factura->subtotal_servicios" />
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-zink-300">Materiales</span>
                            <span class="font-semibold text-slate-700 dark:text-zink-100">
                                <x-money :amount="$factura->subtotal_productos" />
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-zink-300">Gastos</span>
                            <span class="font-semibold text-slate-700 dark:text-zink-100">
                                <x-money :amount="$factura->subtotal_gastos" />
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-zink-500">
                        <span class="font-semibold text-slate-700 dark:text-zink-100">Total {{ $moneda }}</span>
                        <span class="text-lg font-bold text-slate-800 dark:text-zink-50">
                            <x-money :amount="$factura->total" />
                        </span>
                    </div>

                    @if ($factura->forma_pago)
                        <div class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-500 dark:border-zink-500 dark:text-zink-300">
                            <span class="font-semibold">Forma de pago:</span>
                            {{ $factura->forma_pago }}
                        </div>
                    @endif

                    <div class="space-y-2 pt-1">
                        <a href="{{ route('facturas.pdf', $factura) }}"
                            class="btn w-full border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100">
                            <i data-lucide="file-down" class="me-1.5 size-4"></i>
                            Descargar PDF
                        </a>
                        <a href="{{ route('facturas.imprimir', $factura) }}" target="_blank"
                            class="btn w-full border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
                            <i data-lucide="printer" class="me-1.5 size-4"></i>
                            Imprimir factura
                        </a>

                        @if ($factura->oferta)
                            <a href="{{ route('ofertas.show', $factura->oferta) }}"
                                class="btn w-full border-slate-200 text-slate-600">
                                <i data-lucide="file-text" class="me-1.5 size-4"></i>
                                Ver oferta
                            </a>
                        @endif

                        <a href="{{ route('ofertas.index') }}" class="btn w-full border-slate-200 text-slate-600">
                            Volver al listado
                        </a>
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection
