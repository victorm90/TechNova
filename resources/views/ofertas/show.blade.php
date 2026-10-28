@extends('layouts.main')

@section('titulo', 'Oferta '.$oferta->codigo)

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'file-text',
        'title' => 'Oferta '.$oferta->codigo,
        'description' => 'Detalle de servicios y gastos ofertados',
        'accent' => 'green',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Ofertas', 'url' => route('ofertas.index')],
            ['label' => $oferta->codigo],
        ],
    ])

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body grid gap-4 sm:grid-cols-3">
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
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Estado</p>
                        <div class="mt-1">
                            <x-status-badge :status="$oferta->estadoBadge()" :label="$oferta->estadoLabel()" :dot="false" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-slate-700 dark:text-zink-100">Servicios</h2>
                        <span class="text-xs text-slate-400">{{ $oferta->servicios->count() }} {{ $oferta->servicios->count() === 1 ? 'línea' : 'líneas' }}</span>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-400">
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
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-slate-700 dark:text-zink-100">Gastos</h2>
                        <span class="text-xs text-slate-400">{{ $oferta->gastos->count() }} {{ $oferta->gastos->count() === 1 ? 'línea' : 'líneas' }}</span>
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
                            <span class="text-slate-500 dark:text-zink-300">Gastos</span>
                            <span class="font-semibold text-slate-700 dark:text-zink-100">
                                <x-money :amount="$oferta->total_gastos" />
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-zink-500">
                        <span class="font-semibold text-slate-700 dark:text-zink-100">Total</span>
                        <span class="text-lg font-bold text-slate-800 dark:text-zink-50">
                            <x-money :amount="$oferta->total" />
                        </span>
                    </div>

                    <div class="space-y-2 pt-1">
                        <a href="{{ route('ofertas.edit', $oferta) }}"
                            class="btn w-full border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                            <i data-lucide="pencil" class="me-1.5 size-4"></i>
                            Editar oferta
                        </a>

                        <a href="{{ route('ofertas.index') }}" class="btn w-full border-slate-200 text-slate-600">
                            Volver al listado
                        </a>

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
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection
