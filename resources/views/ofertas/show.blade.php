@extends('layouts.main')

@section('titulo', 'Oferta '.$oferta->codigo)

@php
    $moneda = config('empresa.moneda', 'MN');
@endphp

@section('contenido')
    <div x-data="{ modal: null, linea: null, producto: null }">
        @include('layouts.page-header', [
            'icon' => 'file-text',
            'title' => 'Oferta '.$oferta->codigo,
            'description' => 'Servicios, materiales y gastos ofertados al cliente',
            'accent' => 'green',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Ofertas', 'url' => route('ofertas.index')],
                ['label' => $oferta->codigo],
            ],
        ])

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <div class="flex items-start gap-2">
                    <i data-lucide="circle-alert" class="mt-0.5 size-4 shrink-0"></i>
                    <ul class="list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if ($oferta->estaFacturada())
            <div class="mb-5 flex flex-col gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-2">
                    <i data-lucide="badge-check" class="mt-0.5 size-4 shrink-0"></i>
                    <div>
                        <p class="font-semibold">Oferta facturada</p>
                        <p class="text-xs">
                            {{ $oferta->facturada_at?->format('d/m/Y H:i') }}
                            @if ($oferta->factura)
                                · Factura {{ $oferta->factura->codigo }}
                            @endif
                            · El stock confirmado ya fue descontado.
                        </p>
                    </div>
                </div>

                @if ($oferta->factura)
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('facturas.show', $oferta->factura) }}"
                            class="btn border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-100">
                            Ver factura
                        </a>
                        <a href="{{ route('facturas.pdf', $oferta->factura) }}"
                            class="btn border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
                            PDF factura
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Cliente</p>
                            <p class="mt-1 font-medium text-slate-700 dark:text-zink-100">
                                {{ $oferta->customer?->name ?? '—' }}
                            </p>
                            @if ($oferta->customer?->phone)
                                <p class="text-xs text-slate-400">{{ $oferta->customer->phone }}</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Fecha</p>
                            <p class="mt-1 font-medium text-slate-700 dark:text-zink-100">
                                {{ $oferta->fecha?->format('d/m/Y') }}
                            </p>
                            <p class="text-xs text-slate-400">Vigencia: {{ $oferta->vigencia_dias }} días</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Estado</p>
                            <div class="mt-1">
                                <x-status-badge :status="$oferta->estadoBadge()" :label="$oferta->estadoLabel()" :dot="false" />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Almacén</p>
                            <p class="mt-1 font-medium text-slate-700 dark:text-zink-100">
                                {{ $oferta->almacen?->nombre ?? 'Predeterminado' }}
                            </p>
                            <p class="text-xs text-slate-400">Origen de los materiales</p>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-semibold text-slate-700 dark:text-zink-100">Servicios</h2>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $oferta->servicios->count() }} {{ $oferta->servicios->count() === 1 ? 'línea' : 'líneas' }}
                                </p>
                            </div>

                            @unless ($oferta->estaFacturada())
                                <button type="button" @click="modal = 'servicio'"
                                    class="btn inline-flex items-center justify-center border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100">
                                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                                    Añadir servicio
                                </button>
                            @endunless
                        </div>

                        <div class="nova-table-scroll mt-3 w-full">
                            <table class="nova-table w-full">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Servicio</th>
                                        <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">U/M</th>
                                        <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                        <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Precio unitario</th>
                                        <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Total</th>
                                        <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">Materiales</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($oferta->servicios as $linea)
                                        <tr>
                                            <td class="px-3 py-3 text-sm font-medium text-slate-700 dark:text-zink-100">
                                                {{ $linea->servicio?->nombre ?? 'Servicio eliminado' }}
                                            </td>
                                            <td class="px-3 py-3 text-sm text-slate-500 dark:text-zink-300">
                                                {{ $linea->servicio?->unidadMedida?->abreviatura ?: ($linea->servicio?->unidadMedida?->nombre ?? '—') }}
                                            </td>
                                            <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                                {{ number_format((float) $linea->cantidad, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                                <x-money :amount="$linea->precio_unitario" />
                                            </td>
                                            <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                                <x-money :amount="$linea->total" />
                                            </td>
                                            <td class="px-2 py-3 text-center">
                                                @unless ($oferta->estaFacturada())
                                                    <button type="button"
                                                        @click="modal = 'producto'; linea = {{ $linea->id }}"
                                                        class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                                        <i data-lucide="plus" class="size-3"></i>
                                                        Producto
                                                    </button>
                                                @else
                                                    <span class="text-xs text-slate-400">
                                                        {{ $linea->productos->count() }} prod.
                                                    </span>
                                                @endunless
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-400">
                                                Esta oferta no tiene servicios.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="px-3 py-3 text-right text-sm font-semibold text-slate-600 dark:text-zink-200">
                                            Subtotal servicios
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm font-bold is-numeric text-slate-700 dark:text-zink-100">
                                            <x-money :amount="$oferta->total_servicios" />
                                        </td>
                                        <td></td>
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
                                <p class="mt-0.5 text-xs text-slate-400">Productos de almacén consumidos por los servicios.</p>
                            </div>
                            <span class="text-xs text-slate-400">
                                {{ $oferta->servicios->sum(fn ($linea) => $linea->productos->count()) }} productos
                            </span>
                        </div>

                        <div class="nova-table-scroll mt-3 w-full">
                            <table class="nova-table w-full">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                                        <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Servicio</th>
                                        <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                        <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Precio venta</th>
                                        <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($oferta->servicios as $linea)
                                        @foreach ($linea->productos as $producto)
                                            <tr>
                                                <td class="px-3 py-3 text-sm font-medium text-slate-700 dark:text-zink-100">
                                                    {{ $producto->producto?->nombre ?? 'Producto eliminado' }}
                                                    <span class="block text-xs text-slate-400">{{ $producto->producto?->sku }}</span>
                                                </td>
                                                <td class="px-3 py-3 text-sm text-slate-500 dark:text-zink-300">
                                                    {{ $linea->servicio?->nombre ?? '—' }}
                                                </td>
                                                <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                                    {{ number_format((float) $producto->cantidad, 0, ',', '.') }}
                                                </td>
                                                <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                                    <x-money :amount="$producto->precio_venta" />
                                                </td>
                                                <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                                    <x-money :amount="$producto->total" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-400">
                                                No hay productos asociados a los servicios.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="px-3 py-3 text-right text-sm font-semibold text-slate-600 dark:text-zink-200">
                                            Subtotal materiales
                                        </td>
                                        <td class="px-3 py-3 text-right text-sm font-bold is-numeric text-slate-700 dark:text-zink-100">
                                            <x-money :amount="$oferta->total_productos" />
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
                                    {{ $oferta->gastos->count() }} {{ $oferta->gastos->count() === 1 ? 'línea' : 'líneas' }}
                                </p>
                            </div>

                            @unless ($oferta->estaFacturada())
                                <button type="button" @click="modal = 'gasto'"
                                    class="btn inline-flex items-center justify-center border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100">
                                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                                    Añadir gasto
                                </button>
                            @endunless
                        </div>

                        <div class="nova-table-scroll mt-3 w-full">
                            <table class="nova-table w-full">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Gasto</th>
                                        <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                        <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Precio unitario</th>
                                        <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($oferta->gastos as $linea)
                                        <tr>
                                            <td class="px-3 py-3 text-sm font-medium text-slate-700 dark:text-zink-100">
                                                {{ $linea->gasto?->nombre ?? 'Gasto eliminado' }}
                                            </td>
                                            <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                                {{ number_format((float) $linea->cantidad, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                                <x-money :amount="$linea->precio_unitario" />
                                            </td>
                                            <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                                <x-money :amount="$linea->total" />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-6 text-center text-sm text-slate-400">
                                                Esta oferta no tiene gastos.
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
                                            <x-money :amount="$oferta->total_gastos" />
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="xl:sticky xl:top-24 xl:self-start">
                <div class="card border-0 shadow-sm">
                    <div class="card-body space-y-4">
                        <div>
                            <h2 class="font-semibold text-slate-700 dark:text-zink-100">Resumen</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Totales calculados a partir de las líneas.</p>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-zink-300">Servicios</span>
                                <span class="font-semibold text-slate-700 dark:text-zink-100">
                                    <x-money :amount="$oferta->total_servicios" />
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-zink-300">Materiales</span>
                                <span class="font-semibold text-slate-700 dark:text-zink-100">
                                    <x-money :amount="$oferta->total_productos" />
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-zink-300">Gastos</span>
                                <span class="font-semibold text-slate-700 dark:text-zink-100">
                                    <x-money :amount="$oferta->total_gastos" />
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-zink-500">
                            <span class="font-semibold text-slate-700 dark:text-zink-100">Total {{ $moneda }}</span>
                            <span class="text-lg font-bold text-slate-800 dark:text-zink-50">
                                <x-money :amount="$oferta->total" />
                            </span>
                        </div>

                        <div class="space-y-2 pt-1">
                            @unless ($oferta->estaFacturada())
                                <button type="button" @click="modal = 'facturar'"
                                    class="btn w-full border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
                                    <i data-lucide="receipt-text" class="me-1.5 size-4"></i>
                                    Facturar oferta
                                </button>

                                <a href="{{ route('ofertas.edit', $oferta) }}"
                                    class="btn w-full border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                    <i data-lucide="pencil" class="me-1.5 size-4"></i>
                                    Editar oferta
                                </a>
                            @endunless

                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('ofertas.imprimir', $oferta) }}" target="_blank"
                                    class="btn border-slate-200 text-slate-600">
                                    <i data-lucide="printer" class="me-1.5 size-4"></i>
                                    Imprimir
                                </a>
                                <a href="{{ route('ofertas.pdf', $oferta) }}"
                                    class="btn border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100">
                                    <i data-lucide="file-down" class="me-1.5 size-4"></i>
                                    PDF
                                </a>
                            </div>

                            <a href="{{ route('ofertas.index') }}" class="btn w-full border-slate-200 text-slate-600">
                                Volver al listado
                            </a>

                            @unless ($oferta->estaFacturada())
                                <form method="POST" action="{{ route('ofertas.destroy', $oferta) }}"
                                    onsubmit="return confirm('¿Eliminar la oferta {{ $oferta->codigo }}?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="btn w-full border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100">
                                        <i data-lucide="trash-2" class="me-1.5 size-4"></i>
                                        Eliminar oferta
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <div class="card mt-5 border-0 shadow-sm">
            <div class="card-body">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-700 dark:text-zink-100">Historial de precios</h2>
                        <p class="mt-0.5 text-xs text-slate-400">
                            Cambios de precio de servicios, gastos y productos dentro de esta oferta.
                        </p>
                    </div>
                    <span class="text-xs text-slate-400">{{ $historiales->count() }} registros</span>
                </div>

                <div class="nova-table-scroll mt-3 w-full">
                    <table class="nova-table w-full">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Fecha</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Línea</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Tipo</th>
                                <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Anterior</th>
                                <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Nuevo</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historiales as $historial)
                                @php
                                    $tipoLinea = match ($historial->historialable_type) {
                                        \App\Models\OfertaServicio::class => 'Servicio',
                                        \App\Models\OfertaGasto::class => 'Gasto',
                                        \App\Models\OfertaServicioProducto::class => 'Producto',
                                        default => 'Línea',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-3 py-2.5 text-sm text-slate-600 dark:text-zink-200">
                                        {{ $historial->created_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-sm text-slate-700 dark:text-zink-100">
                                        {{ $historial->descripcionLinea() }}
                                    </td>
                                    <td class="px-3 py-2.5 text-sm text-slate-500 dark:text-zink-300">
                                        {{ $tipoLinea }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right text-sm is-numeric text-slate-500 dark:text-zink-300">
                                        <x-money :amount="$historial->precio_anterior" />
                                    </td>
                                    <td class="px-3 py-2.5 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                        <x-money :amount="$historial->precio_nuevo" />
                                    </td>
                                    <td class="px-3 py-2.5 text-sm text-slate-500 dark:text-zink-300">
                                        {{ $historial->user?->name ?? 'Sistema' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-400">
                                        Todavía no se han registrado cambios de precio.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @unless ($oferta->estaFacturada())
            {{-- Modal: añadir servicio --}}
            <div x-show="modal === 'servicio'" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1050"
                role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="modal = null" aria-hidden="true"></div>
                <div class="relative flex min-h-full items-start justify-center px-4 pb-8 pt-16">
                    <div @click.outside="modal = null" class="w-full max-w-lg rounded-xl bg-white shadow-2xl dark:bg-zink-700">
                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-zink-500">
                            <h5 class="text-lg font-semibold text-slate-800 dark:text-zink-100">Añadir servicio</h5>
                            <button type="button" @click="modal = null" class="text-slate-500 hover:text-slate-700">
                                <i data-lucide="x" class="size-5"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('ofertas.servicios.store', $oferta) }}" class="space-y-4 p-6"
                            x-data="{ precio: '' }">
                            @csrf

                            <div>
                                <label for="nuevo-servicio" class="mb-2 inline-block text-sm font-medium">Servicio</label>
                                <select id="nuevo-servicio" name="servicio_id" required
                                    @change="precio = $event.target.selectedOptions[0]?.dataset.precio ?? ''"
                                    class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                    <option value="">Seleccione un servicio</option>
                                    @foreach ($serviciosDisponibles as $servicio)
                                        <option value="{{ $servicio->id }}" data-precio="{{ $servicio->precio_unitario }}">
                                            {{ $servicio->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="nuevo-servicio-cantidad" class="mb-2 inline-block text-sm font-medium">Cantidad</label>
                                    <input id="nuevo-servicio-cantidad" name="cantidad" type="number" min="0.01" step="0.01"
                                        value="1" required
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                </div>

                                <div>
                                    <label for="nuevo-servicio-precio" class="mb-2 inline-block text-sm font-medium">Precio unitario</label>
                                    <input id="nuevo-servicio-precio" name="precio_unitario" type="number" min="0" step="0.01"
                                        x-model="precio" required
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                </div>
                            </div>

                            <p class="text-xs text-slate-400">El precio base del catálogo no cambia. Si modifica el precio, quedará registrado en el historial con su usuario.</p>

                            <div class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-zink-500">
                                <button type="button" @click="modal = null" class="btn border-slate-200 text-slate-500">Cancelar</button>
                                <button type="submit" class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">Agregar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal: añadir gasto --}}
            <div x-show="modal === 'gasto'" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1050"
                role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="modal = null" aria-hidden="true"></div>
                <div class="relative flex min-h-full items-start justify-center px-4 pb-8 pt-16">
                    <div @click.outside="modal = null" class="w-full max-w-lg rounded-xl bg-white shadow-2xl dark:bg-zink-700">
                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-zink-500">
                            <h5 class="text-lg font-semibold text-slate-800 dark:text-zink-100">Añadir gasto</h5>
                            <button type="button" @click="modal = null" class="text-slate-500 hover:text-slate-700">
                                <i data-lucide="x" class="size-5"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('ofertas.gastos.store', $oferta) }}" class="space-y-4 p-6"
                            x-data="{ precio: '' }">
                            @csrf

                            <div>
                                <label for="nuevo-gasto" class="mb-2 inline-block text-sm font-medium">Gasto</label>
                                <select id="nuevo-gasto" name="gasto_id" required
                                    @change="precio = $event.target.selectedOptions[0]?.dataset.precio ?? ''"
                                    class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                    <option value="">Seleccione un gasto</option>
                                    @foreach ($gastosDisponibles as $gasto)
                                        <option value="{{ $gasto->id }}" data-precio="{{ $gasto->precio_unitario }}">
                                            {{ $gasto->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="nuevo-gasto-cantidad" class="mb-2 inline-block text-sm font-medium">Cantidad</label>
                                    <input id="nuevo-gasto-cantidad" name="cantidad" type="number" min="0.01" step="0.01"
                                        value="1" required
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                </div>

                                <div>
                                    <label for="nuevo-gasto-precio" class="mb-2 inline-block text-sm font-medium">Precio unitario</label>
                                    <input id="nuevo-gasto-precio" name="precio_unitario" type="number" min="0" step="0.01"
                                        x-model="precio" required
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                </div>
                            </div>

                            <p class="text-xs text-slate-400">El precio del catálogo no cambia. Cada ajuste queda en el historial de precios.</p>

                            <div class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-zink-500">
                                <button type="button" @click="modal = null" class="btn border-slate-200 text-slate-500">Cancelar</button>
                                <button type="submit" class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">Agregar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal: añadir producto a un servicio --}}
            <div x-show="modal === 'producto'" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1050"
                role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="modal = null" aria-hidden="true"></div>
                <div class="relative flex min-h-full items-start justify-center px-4 pb-8 pt-16">
                    <div @click.outside="modal = null" class="w-full max-w-lg rounded-xl bg-white shadow-2xl dark:bg-zink-700">
                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-zink-500">
                            <h5 class="text-lg font-semibold text-slate-800 dark:text-zink-100">Añadir producto al servicio</h5>
                            <button type="button" @click="modal = null" class="text-slate-500 hover:text-slate-700">
                                <i data-lucide="x" class="size-5"></i>
                            </button>
                        </div>

                        <form method="POST"
                            :action="'{{ route('ofertas.servicios.productos.store', [$oferta, '__LINEA__']) }}'.replace('__LINEA__', linea)"
                            class="space-y-4 p-6" x-data="{ precio: '', stock: null, almacen: @js((string) ($almacenActual ?? '')) }">
                            @csrf

                            <div>
                                <label for="nuevo-producto" class="mb-2 inline-block text-sm font-medium">Producto</label>
                                <select id="nuevo-producto" name="producto_id" required
                                    @change="const opcion = $event.target.selectedOptions[0]; precio = opcion?.dataset.precio ?? ''; stock = JSON.parse(opcion?.dataset.stock || '{}')[almacen] ?? 0"
                                    class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                    <option value="">Seleccione un producto</option>
                                    @foreach ($productosDisponibles as $producto)
                                        <option value="{{ $producto->id }}" data-precio="{{ $producto->precio_venta }}"
                                            data-stock="{{ ($stockPorAlmacen[$producto->id] ?? collect())->toJson() }}">
                                            {{ $producto->nombre }} ({{ $producto->sku }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-slate-400">
                                    Stock disponible:
                                    <span x-text="stock === null ? '—' : stock"></span>
                                    {{ $oferta->almacen?->nombre ? '('.$oferta->almacen->nombre.')' : '' }}
                                </p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="nuevo-producto-cantidad" class="mb-2 inline-block text-sm font-medium">Cantidad</label>
                                    <input id="nuevo-producto-cantidad" name="cantidad" type="number" min="1" step="1"
                                        value="1" required
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                </div>

                                <div>
                                    <label for="nuevo-producto-precio" class="mb-2 inline-block text-sm font-medium">Precio de venta</label>
                                    <input id="nuevo-producto-precio" name="precio_venta" type="number" min="0" step="0.01"
                                        x-model="precio" required
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                </div>
                            </div>

                            <p class="rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                                El sistema valida que la cantidad total no supere el stock del almacén antes de guardar.
                            </p>

                            <div class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-zink-500">
                                <button type="button" @click="modal = null" class="btn border-slate-200 text-slate-500">Cancelar</button>
                                <button type="submit" class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">Agregar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal: facturar con ajustes reales --}}
            <div x-show="modal === 'facturar'" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1100"
                role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" @click="modal = null" aria-hidden="true"></div>
                <div class="relative flex min-h-full items-start justify-center px-4 pb-10 pt-10">
                    <div @click.outside="modal = null"
                        class="w-full max-w-4xl overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-zink-700">
                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-zink-500">
                            <div>
                                <h5 class="text-lg font-semibold text-slate-800 dark:text-zink-100">Confirmar facturación</h5>
                                <p class="text-xs text-slate-400">
                                    Ajuste las cantidades reales y los precios finales. Estos valores quedarán en la factura y el stock se descontará al confirmar.
                                </p>
                            </div>
                            <button type="button" @click="modal = null" class="text-slate-500 hover:text-slate-700">
                                <i data-lucide="x" class="size-5"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('ofertas.facturar', $oferta) }}" class="space-y-5 p-6"
                            onsubmit="return confirm('¿Confirmar la facturación? Se descontará el stock confirmado y la oferta quedará bloqueada.');">
                            @csrf

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <label for="factura-almacen" class="mb-2 inline-block text-sm font-medium">Almacén a descontar</label>
                                    <select id="factura-almacen" name="almacen_id" required
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                        <option value="">Seleccione un almacén</option>
                                        @foreach ($almacenes as $almacen)
                                            <option value="{{ $almacen->id }}"
                                                @selected((string) $almacenActual === (string) $almacen->id)>
                                                {{ $almacen->nombre }}{{ $almacen->activo ? '' : ' (inactivo)' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="factura-fecha" class="mb-2 inline-block text-sm font-medium">Fecha de factura</label>
                                    <input id="factura-fecha" name="fecha" type="date" value="{{ now()->toDateString() }}"
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                </div>
                            </div>

                            <div>
                                <h6 class="mb-2 text-sm font-semibold text-slate-700 dark:text-zink-100">Servicios</h6>
                                <div class="space-y-2">
                                    @forelse ($oferta->servicios as $indice => $linea)
                                        <div class="grid gap-2 rounded-lg border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_110px_140px] sm:items-center dark:border-zink-500">
                                            <div class="text-sm text-slate-600 dark:text-zink-200">
                                                {{ $linea->servicio?->nombre ?? 'Servicio eliminado' }}
                                                <input type="hidden" name="servicios[{{ $indice }}][id]" value="{{ $linea->id }}">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-[11px] text-slate-400">Cantidad real</label>
                                                <input name="servicios[{{ $indice }}][cantidad]" type="number" min="0.01" step="0.01"
                                                    value="{{ $linea->cantidad }}" required
                                                    class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-[11px] text-slate-400">Precio final</label>
                                                <input name="servicios[{{ $indice }}][precio_unitario]" type="number" min="0" step="0.01"
                                                    value="{{ $linea->precio_unitario }}" required
                                                    class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-400">La oferta no tiene servicios.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <h6 class="mb-2 text-sm font-semibold text-slate-700 dark:text-zink-100">Gastos</h6>
                                <div class="space-y-2">
                                    @forelse ($oferta->gastos as $indice => $linea)
                                        <div class="grid gap-2 rounded-lg border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_110px_140px] sm:items-center dark:border-zink-500">
                                            <div class="text-sm text-slate-600 dark:text-zink-200">
                                                {{ $linea->gasto?->nombre ?? 'Gasto eliminado' }}
                                                <input type="hidden" name="gastos[{{ $indice }}][id]" value="{{ $linea->id }}">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-[11px] text-slate-400">Cantidad real</label>
                                                <input name="gastos[{{ $indice }}][cantidad]" type="number" min="0.01" step="0.01"
                                                    value="{{ $linea->cantidad }}" required
                                                    class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-[11px] text-slate-400">Precio final</label>
                                                <input name="gastos[{{ $indice }}][precio_unitario]" type="number" min="0" step="0.01"
                                                    value="{{ $linea->precio_unitario }}" required
                                                    class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-400">La oferta no tiene gastos.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <h6 class="mb-2 text-sm font-semibold text-slate-700 dark:text-zink-100">Productos y materiales</h6>
                                <div class="space-y-2">
                                    @php($productoIndice = 0)
                                    @forelse ($oferta->servicios as $linea)
                                        @foreach ($linea->productos as $producto)
                                            <div class="grid gap-2 rounded-lg border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_110px_140px] sm:items-center dark:border-zink-500">
                                                <div class="text-sm text-slate-600 dark:text-zink-200">
                                                    {{ $producto->producto?->nombre ?? 'Producto eliminado' }}
                                                    <span class="block text-xs text-slate-400">
                                                        {{ $linea->servicio?->nombre }}
                                                        · Stock:
                                                        {{ number_format((int) ($stockPorProducto[$producto->producto_id] ?? 0), 0, ',', '.') }}
                                                    </span>
                                                    <input type="hidden" name="productos[{{ $productoIndice }}][id]" value="{{ $producto->id }}">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-[11px] text-slate-400">Cantidad real</label>
                                                    <input name="productos[{{ $productoIndice }}][cantidad]" type="number" min="1" step="1"
                                                        value="{{ $producto->cantidad }}" required
                                                        class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-[11px] text-slate-400">Precio final</label>
                                                    <input name="productos[{{ $productoIndice }}][precio_venta]" type="number" min="0" step="0.01"
                                                        value="{{ $producto->precio_venta }}" required
                                                        class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">
                                                </div>
                                            </div>
                                            @php($productoIndice++)
                                        @endforeach
                                    @empty
                                    @endforelse
                                    @if ($productoIndice === 0)
                                        <p class="text-sm text-slate-400">La oferta no tiene productos de almacén.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="factura-forma-pago" class="mb-2 inline-block text-sm font-medium">Forma de pago</label>
                                    <input id="factura-forma-pago" name="forma_pago" type="text" maxlength="120"
                                        value="{{ $oferta->forma_pago }}"
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                </div>
                                <div>
                                    <label for="factura-firma" class="mb-2 inline-block text-sm font-medium">Pie de firma</label>
                                    <input id="factura-firma" name="firma_pie" type="text" maxlength="150"
                                        value="{{ $oferta->firma_pie }}"
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="factura-nota" class="mb-2 inline-block text-sm font-medium">Nota</label>
                                    <textarea id="factura-nota" name="nota" rows="2" maxlength="2000"
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">{{ $oferta->nota }}</textarea>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="factura-observaciones" class="mb-2 inline-block text-sm font-medium">Observaciones</label>
                                    <textarea id="factura-observaciones" name="observaciones" rows="2" maxlength="2000"
                                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">{{ $oferta->observaciones }}</textarea>
                                </div>
                            </div>

                            <p class="rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                                Al confirmar, la oferta pasa a estado <strong>Facturado</strong>, se genera la factura final y se descuenta el stock de las cantidades indicadas. Esta acción no se puede deshacer.
                            </p>

                            <div class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-zink-500">
                                <button type="button" @click="modal = null" class="btn border-slate-200 text-slate-500">Cancelar</button>
                                <button type="submit" class="btn border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
                                    <i data-lucide="receipt-text" class="me-1.5 size-4"></i>
                                    Confirmar facturación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endunless
    </div>
@endsection
