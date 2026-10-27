@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
    @php
        $warehouseHealth = $mock['warehouse_health'] ?? [];
        $topProducts = $mock['top_products'] ?? [];
        $lowStockCount = count($mock['low_stock']);
        $pendingCount = count($mock['pending_invoices']);
    @endphp

    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto space-y-5">

        {{-- ============================================================
             CABECERA
             ============================================================ --}}
        <section class="nova-hero-dashboard">
            <div class="relative flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($hasInventoryData)
                            <span class="nova-demo-badge">
                                <i data-lucide="database" class="size-3" aria-hidden="true"></i>
                                Inventario real
                            </span>
                        @else
                            <span class="nova-demo-badge">
                                <i data-lucide="flask-conical" class="size-3" aria-hidden="true"></i>
                                Datos de demostración
                            </span>
                        @endif

                        <span class="text-xs font-medium text-slate-600">
                            Actualizado el {{ $fechaHoy }}
                        </span>
                    </div>

                    <h1 class="mt-2.5 text-2xl font-semibold leading-tight sm:text-3xl">
                        Panel de control
                    </h1>

                    <p class="mt-1.5 text-sm leading-6 text-slate-600">
                        Inventario, ventas y facturación del almacén en una sola vista.
                        Revisa primero las alertas y luego el comportamiento del periodo.
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/70 px-2.5 py-1 font-semibold text-emerald-800 ring-1 ring-inset ring-white/80">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            Operación estable
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/70 px-2.5 py-1 font-semibold text-slate-700 ring-1 ring-inset ring-white/80">
                            <i data-lucide="warehouse" class="size-3.5" aria-hidden="true"></i>
                            {{ number_format($totalAlmacenes, 0, ',', '.') }}
                            {{ $totalAlmacenes === 1 ? 'almacén activo' : 'almacenes activos' }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-xl border border-white/80 bg-white/70 px-3 py-2 text-sm font-medium text-slate-700 shadow-sm">
                        <i data-lucide="calendar-range" class="size-4 text-slate-500" aria-hidden="true"></i>
                        {{ $mock['period'] }}
                    </span>

                    <a href="{{ route('customers.index', ['modal' => 'create']) }}"
                        class="btn inline-flex items-center border-nova-500 bg-nova-500 px-3.5 py-2 text-white shadow-sm hover:border-nova-600 hover:bg-nova-600">
                        <i data-lucide="plus" class="me-1.5 size-4" aria-hidden="true"></i>
                        Nuevo cliente
                    </a>
                </div>
            </div>
        </section>

        {{-- ============================================================
             INDICADORES
             ============================================================ --}}
        <section aria-labelledby="kpis-title">
            <h2 id="kpis-title" class="sr-only">Indicadores principales</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($mock['kpis'] as $kpi)
                    <div class="nova-reveal">
                        <x-metric-card :label="$kpi['label']" :value="$kpi['value']" :format="$kpi['format']"
                            :variation="$kpi['variation']" :tone="$kpi['tone']" :icon="$kpi['icon']"
                            :accent="$kpi['accent']" :hint="$kpi['hint']" :spark="$kpi['spark'] ?? []" />
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ============================================================
             ALERTAS
             ============================================================ --}}
        <section id="alertas" class="grid grid-cols-1 gap-4 xl:grid-cols-2" style="scroll-margin-top: 5.5rem"
            aria-labelledby="alertas-title">
            <h2 id="alertas-title" class="sr-only">Alertas del almacén</h2>

            {{-- STOCK BAJO --}}
            <div class="card overflow-hidden">
                <div class="h-1 w-full bg-gradient-to-r from-amber-300 via-amber-400 to-rose-300"></div>

                <div class="nova-card-header">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700"
                            aria-hidden="true">
                            <i data-lucide="alert-triangle" class="size-5"></i>
                        </span>

                        <div>
                            <h3 class="nova-card-title">Stock bajo</h3>
                            <p class="nova-card-subtitle">Productos que requieren reposición</p>
                        </div>
                    </div>

                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200">
                        {{ $lowStockCount }} alertas
                    </span>
                </div>

                <div>
                    @forelse ($mock['low_stock'] as $product)
                        @php
                            $stockPercent = $product['min'] > 0 ? min(100, round(($product['stock'] / $product['min']) * 100)) : 0;
                            $isCritical = $product['stock'] <= 5;
                        @endphp

                        <div class="nova-alert-row">
                            <span @class([
                                'nova-alert-dot',
                                'bg-rose-50 text-rose-700' => $isCritical,
                                'bg-amber-50 text-amber-800' => ! $isCritical,
                            ]) aria-hidden="true">
                                {{ number_format((float) $product['stock'], 0, ',', '.') }}
                            </span>

                            <div class="min-w-0 grow">
                                <p class="truncate text-sm font-semibold text-slate-800 dark:text-zink-100">
                                    {{ $product['product'] }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    SKU {{ $product['sku'] }} · mínimo
                                    {{ number_format((float) $product['min'], 0, ',', '.') }} unidades
                                </p>

                                <div class="mt-1.5 flex items-center gap-2">
                                    <div class="nova-stock-track w-full max-w-[11rem]">
                                        <span style="width: {{ max(6, $stockPercent) }}%; background: {{ $isCritical ? 'linear-gradient(90deg, #d76a7f, #b04358)' : 'linear-gradient(90deg, #e0b25a, #a86a12)' }}"></span>
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-500">{{ $stockPercent }}% del mínimo</span>
                                </div>
                            </div>

                            <x-status-badge :status="$isCritical ? 'critical' : 'low'"
                                :label="$isCritical ? 'Crítico' : 'Bajo'" />
                        </div>
                    @empty
                        <div class="px-5 py-8">
                            <x-table-empty icon="circle-check"
                                title="Sin alertas de stock"
                                message="Todos los productos activos están por encima de su stock mínimo." />
                        </div>
                    @endforelse
                </div>

                <div class="nova-table-foot !py-2.5">
                    <span>Reposición sugerida en las próximas 48 h</span>
                    @can('manage-proveedores')
                        <a href="{{ route('proveedores.index') }}" class="font-semibold text-nova-700 hover:text-nova-900">
                            Gestionar proveedores
                        </a>
                    @endcan
                </div>
            </div>

            {{-- FACTURAS PENDIENTES --}}
            <div class="card overflow-hidden">
                <div class="h-1 w-full bg-gradient-to-r from-rose-300 via-amber-300 to-amber-200"></div>

                <div class="nova-card-header">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-rose-50 text-rose-700"
                            aria-hidden="true">
                            <i data-lucide="file-text" class="size-5"></i>
                        </span>

                        <div>
                            <h3 class="nova-card-title">Facturas pendientes</h3>
                            <p class="nova-card-subtitle">Cobros próximos o vencidos</p>
                        </div>
                    </div>

                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800 ring-1 ring-inset ring-rose-200">
                        {{ $pendingCount }} facturas
                    </span>
                </div>

                <div>
                    @foreach ($mock['pending_invoices'] as $invoice)
                        <div class="nova-alert-row">
                            <span
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"
                                aria-hidden="true">
                                <i data-lucide="receipt" class="size-4"></i>
                            </span>

                            <div class="min-w-0 grow">
                                <p class="truncate text-sm font-semibold text-slate-800 dark:text-zink-100">
                                    {{ $invoice['client'] }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $invoice['invoice'] }} · vence
                                    <x-date :value="$invoice['due']" />
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-800 is-numeric dark:text-zink-100">
                                    <x-money :amount="$invoice['amount']" />
                                </p>
                                <div class="mt-1">
                                    <x-status-badge :status="$invoice['status']" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="nova-table-foot !py-2.5">
                    <span>Total por cobrar: <strong class="text-slate-700">$8.440,50</strong></span>
                    <a href="{{ route('customers.index') }}" class="font-semibold text-nova-700 hover:text-nova-900">
                        Ver facturación
                    </a>
                </div>
            </div>
        </section>

        {{-- ============================================================
             GRÁFICAS PRINCIPALES
             ============================================================ --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3" aria-label="Gráficas de ventas e inventario">
            <div class="card xl:col-span-2">
                <div class="nova-card-header">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-nova-50 text-nova-700"
                            aria-hidden="true">
                            <i data-lucide="bar-chart" class="size-5"></i>
                        </span>

                        <div>
                            <h2 class="nova-card-title">Ventas por periodo</h2>
                            <p class="nova-card-subtitle">Comparativo contra el periodo anterior</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-2 sm:flex-row sm:items-center">
                        <div class="nova-segmented" role="group" aria-label="Rango de la gráfica de ventas">
                            <button type="button" data-sales-range="3" aria-pressed="false">3M</button>
                            <button type="button" data-sales-range="6" aria-pressed="false">6M</button>
                            <button type="button" data-sales-range="12" aria-pressed="true">12M</button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-3 flex flex-wrap items-center gap-4 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="size-2 rounded-full bg-[#2f7f78]"></span> Periodo actual
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="size-2 rounded-full bg-[#8fb4c9]"></span> Periodo anterior
                        </span>
                    </div>

                    <div id="salesChart" class="nova-chart" role="img"
                        aria-label="Gráfica comparativa de ventas de los últimos doce meses frente al periodo anterior"></div>
                </div>
            </div>

            <div class="card">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Inventario por categoría</h2>
                        <p class="nova-card-subtitle">Distribución del stock disponible</p>
                    </div>
                </div>

                <div class="card-body">
                    <div id="inventoryChart" class="nova-chart" role="img"
                        aria-label="Distribución porcentual del inventario por categoría"></div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             FACTURACIÓN Y SALUD DEL ALMACÉN
             ============================================================ --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3" aria-label="Facturación y salud del almacén">
            <div class="card xl:col-span-2">
                <div class="nova-card-header">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700"
                            aria-hidden="true">
                            <i data-lucide="file-text" class="size-5"></i>
                        </span>

                        <div>
                            <h2 class="nova-card-title">Facturación por periodo</h2>
                            <p class="nova-card-subtitle">Cobrado, pendiente y vencido en los últimos 6 meses</p>
                        </div>
                    </div>

                    <span class="hidden text-xs font-medium text-slate-500 sm:inline">USD</span>
                </div>

                <div class="card-body">
                    <div id="billingChart" class="nova-chart" role="img"
                        aria-label="Gráfica de facturación apilada por estado de cobro"></div>
                </div>
            </div>

            <div class="card">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Salud del almacén</h2>
                        <p class="nova-card-subtitle">Indicadores operativos del día</p>
                    </div>
                </div>

                <div class="card-body space-y-4">
                    @foreach ($warehouseHealth as $health)
                        <div>
                            <div class="flex items-end justify-between gap-3">
                                <p class="text-xs font-semibold text-slate-600">{{ $health['label'] }}</p>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $health['value'] }}{{ $health['suffix'] }}
                                </p>
                            </div>

                            <div class="nova-stock-track mt-1.5">
                                <span style="width: {{ min(100, (float) $health['value']) }}%"></span>
                            </div>

                            <p class="mt-1 text-[11px] text-slate-500">{{ $health['hint'] }}</p>
                        </div>
                    @endforeach

                    <div class="border-t border-slate-100 pt-3 dark:border-zink-500">
                        @foreach ($mock['activity'] as $activity)
                            <div class="flex items-center gap-2.5 py-1.5">
                                <span
                                    class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"
                                    aria-hidden="true">
                                    <i data-lucide="{{ $activity['icon'] }}" class="size-3.5"></i>
                                </span>

                                <p class="min-w-0 grow truncate text-xs text-slate-600">{{ $activity['label'] }}</p>
                                <p class="text-xs font-semibold text-slate-800">{{ $activity['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             ÚLTIMAS VENTAS Y CLIENTES
             ============================================================ --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3" aria-label="Movimientos recientes">
            <div class="card xl:col-span-2 nova-table-card" x-data="{
                q: '',
                matches: {{ count($mock['recent_sales']) }},
                filterRows() {
                    const term = this.q.trim().toLowerCase();
                    this.matches = [...this.$root.querySelectorAll('[data-search]')].filter((row) => row.dataset.search.includes(term)).length;
                },
            }">
                <div class="nova-card-header">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"
                            aria-hidden="true">
                            <i data-lucide="shopping-cart" class="size-5"></i>
                        </span>

                        <div>
                            <h2 class="nova-card-title">Últimas ventas</h2>
                            <p class="nova-card-subtitle">Movimientos recientes del periodo</p>
                        </div>
                    </div>

                    <div class="relative w-52 max-w-full">
                        <label for="sales-search" class="sr-only">Buscar ventas por cliente o referencia</label>
                        <input id="sales-search" type="search" x-model="q" @input="filterRows()"
                            class="form-input nova-input-icon w-full border-slate-200 pe-9 focus:border-nova-500 focus:ring-0 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Buscar cliente o referencia..." autocomplete="off">
                        <i data-lucide="search"
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>

                <div class="nova-table-scroll">
                    <table class="nova-table">
                        <thead>
                            <tr>
                                <th scope="col">Fecha</th>
                                <th scope="col">Cliente</th>
                                <th scope="col" class="is-numeric">Ítems</th>
                                <th scope="col" class="is-numeric">Total</th>
                                <th scope="col">Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($mock['recent_sales'] as $sale)
                                <tr data-search="{{ strtolower($sale['client'].' '.$sale['reference']) }}"
                                    x-show="q === '' || $el.dataset.search.includes(q.trim().toLowerCase())">
                                    <td class="text-sm text-slate-600">
                                        <x-date :value="$sale['date']" />
                                    </td>

                                    <td>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-zink-100">
                                            {{ $sale['client'] }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $sale['reference'] }}</p>
                                    </td>

                                    <td class="is-numeric text-sm text-slate-700">
                                        {{ $sale['items'] }}
                                    </td>

                                    <td class="is-numeric text-sm font-semibold text-slate-900">
                                        <x-money :amount="$sale['total']" />
                                    </td>

                                    <td>
                                        <x-status-badge :status="$sale['status']" />
                                    </td>
                                </tr>
                            @endforeach

                            <tr x-show="matches === 0">
                                <td colspan="5">
                                    <div class="py-8 text-center">
                                        <p class="text-sm font-semibold text-slate-700">Sin coincidencias</p>
                                        <p class="mt-1 text-xs text-slate-500">Prueba con otro cliente o referencia.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="nova-table-foot">
                    <span>
                        Mostrando <strong class="text-slate-700">{{ count($mock['recent_sales']) }}</strong> ventas de
                        demostración
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-amber-500"></span>
                        Datos mock
                    </span>
                </div>
            </div>

            <div class="card">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Clientes recientes</h2>
                        <p class="nova-card-subtitle">Datos reales del directorio</p>
                    </div>

                    <a href="{{ route('customers.index') }}"
                        class="text-xs font-semibold text-nova-700 hover:text-nova-900">
                        Ver todos
                    </a>
                </div>

                <div class="card-body">
                    @forelse ($recentCustomers as $customer)
                        <a href="{{ route('customers.show', $customer) }}"
                            class="flex items-center gap-3 rounded-xl px-2 py-2.5 transition hover:bg-slate-50 dark:hover:bg-zink-600">
                            <span
                                class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-100 to-sky-100 text-xs font-semibold text-nova-700">
                                {{ strtoupper(mb_substr($customer->name, 0, 1)) }}
                            </span>

                            <span class="min-w-0 grow">
                                <span class="block truncate text-sm font-medium text-slate-700 dark:text-zink-100">
                                    {{ $customer->name }}
                                </span>
                                <span class="block truncate text-xs text-slate-500">
                                    {{ $customer->phone }}
                                </span>
                            </span>

                            <i data-lucide="chevron-right" class="size-4 shrink-0 text-slate-300"></i>
                        </a>
                    @empty
                        <x-table-empty icon="users" title="Sin clientes registrados"
                            message="Los clientes aparecerán aquí cuando se registren." />
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ============================================================
             PRODUCTOS DESTACADOS Y DIRECTORIO
             ============================================================ --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3" aria-label="Productos y directorio">
            <div class="card xl:col-span-2">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Productos con mayor rotación</h2>
                        <p class="nova-card-subtitle">Unidades vendidas y aporte a la facturación</p>
                    </div>

                    @if ($mock['top_products_real'] ?? false)
                        <span class="nova-demo-badge">Salidas reales</span>
                    @else
                        <span class="nova-demo-badge">Demo</span>
                    @endif
                </div>

                <div class="nova-table-scroll">
                    <table class="nova-table">
                        <thead>
                            <tr>
                                <th scope="col">Producto</th>
                                <th scope="col" class="is-numeric">Unidades</th>
                                <th scope="col" class="is-numeric">Ingresos</th>
                                <th scope="col" class="is-numeric">Variación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topProducts as $product)
                                <tr>
                                    <td>
                                        <p class="text-sm font-semibold text-slate-800">{{ $product['product'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">SKU {{ $product['sku'] }}</p>
                                    </td>
                                    <td class="is-numeric text-sm text-slate-700">{{ number_format($product['units']) }}</td>
                                    <td class="is-numeric text-sm font-semibold text-slate-900">
                                        <x-money :amount="$product['revenue']" />
                                    </td>
                                    <td class="is-numeric">
                                        <span @class([
                                            'inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold',
                                            'bg-emerald-50 text-emerald-800' => $product['tone'] === 'positive',
                                            'bg-rose-50 text-rose-800' => $product['tone'] !== 'positive',
                                        ])>
                                            <i data-lucide="{{ $product['tone'] === 'positive' ? 'arrow-up-right' : 'arrow-down-right' }}"
                                                class="size-3.5" aria-hidden="true"></i>
                                            {{ $product['trend'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Directorio del sistema</h2>
                        <p class="nova-card-subtitle">Datos reales del sistema</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach ($realSummary as $item)
                            @if ($item['url'])
                                <a href="{{ $item['url'] }}"
                                    class="rounded-xl border border-slate-100 px-3 py-2.5 transition hover:border-nova-200 hover:bg-nova-50/60 dark:border-zink-500 dark:hover:bg-nova-500/10">
                            @else
                                <div class="rounded-xl border border-slate-100 px-3 py-2.5 dark:border-zink-500">
                            @endif
                                <p class="text-lg font-semibold text-slate-900 dark:text-zink-100">
                                    {{ number_format($item['value']) }}
                                </p>
                                <p class="mt-0.5 truncate text-[11px] font-semibold text-slate-600 dark:text-zink-200">
                                    {{ $item['label'] }}
                                </p>
                                <p class="mt-0.5 truncate text-[10px] text-slate-500">
                                    {{ $item['hint'] }}
                                </p>
                            @if ($item['url'])
                                </a>
                            @else
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <p class="pb-1 text-xs leading-5 text-slate-500">
            @if ($hasInventoryData)
                <strong class="font-semibold text-slate-600">Datos reales:</strong>
                el inventario, las alertas y la rotación provienen de la base de datos.
                Las métricas de ventas y facturación continúan siendo de demostración.
            @else
                <strong class="font-semibold text-slate-600">Datos de demostración:</strong>
                las métricas de inventario, ventas y facturación son valores de ejemplo.
                Registra productos para activar los indicadores reales de inventario.
            @endif
        </p>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.ApexCharts) {
                return;
            }

            const sales = @json($mock['sales_chart']);
            const inventory = @json($mock['inventory_chart']);
            const billing = @json($mock['billing_chart']);
            const inventoryInUnits = inventory.total_label === 'Unidades';

            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#a8bcc0' : '#6b7f86';
            const gridColor = isDark ? '#243a40' : '#e8f0ee';

            const currency = (value) => '$' + Number(value).toLocaleString('es-ES', { maximumFractionDigits: 0 });
            const compactCurrency = (value) => '$' + Math.round(Number(value) / 1000) + 'k';

            const baseChart = {
                fontFamily: 'Instrument Sans, ui-sans-serif, system-ui, sans-serif',
                foreColor: textColor,
                toolbar: { show: false },
                zoom: { enabled: false },
                animations: { speed: 420 },
            };

            const baseGrid = {
                borderColor: gridColor,
                strokeDashArray: 4,
                padding: { left: 8, right: 8 },
            };

            const baseLegend = {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '12px',
                markers: { size: 6 },
                itemMargin: { horizontal: 8, vertical: 0 },
            };

            const salesChart = new ApexCharts(document.querySelector('#salesChart'), {
                chart: { ...baseChart, type: 'area', height: 320 },
                series: [
                    { name: 'Periodo actual', data: sales.current },
                    { name: 'Periodo anterior', data: sales.previous },
                ],
                colors: ['#2f7f78', '#8fb4c9'],
                stroke: { curve: 'smooth', width: 2.5 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0.02, stops: [0, 100] },
                },
                dataLabels: { enabled: false },
                grid: baseGrid,
                legend: baseLegend,
                xaxis: {
                    categories: sales.categories,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { fontSize: '11px', colors: textColor } },
                },
                yaxis: {
                    labels: {
                        formatter: compactCurrency,
                        style: { fontSize: '11px', colors: textColor },
                    },
                },
                tooltip: { y: { formatter: currency } },
            });

            salesChart.render();

            document.querySelectorAll('[data-sales-range]').forEach((button) => {
                button.addEventListener('click', () => {
                    const range = Number(button.dataset.salesRange);

                    document.querySelectorAll('[data-sales-range]').forEach((item) => {
                        item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
                    });

                    salesChart.updateOptions({
                        xaxis: { categories: sales.categories.slice(-range) },
                    });

                    salesChart.updateSeries([
                        { name: 'Periodo actual', data: sales.current.slice(-range) },
                        { name: 'Periodo anterior', data: sales.previous.slice(-range) },
                    ]);
                });
            });

            new ApexCharts(document.querySelector('#inventoryChart'), {
                chart: { ...baseChart, type: 'donut', height: 320 },
                series: inventory.series,
                labels: inventory.labels,
                colors: ['#2f7f78', '#2b7096', '#c08a2e', '#7fc3b8', '#9aa9ad'],
                dataLabels: {
                    enabled: true,
                    formatter: (value) => Math.round(value) + '%',
                    style: { fontSize: '11px', fontWeight: 600 },
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    markers: { size: 6 },
                    itemMargin: { horizontal: 6, vertical: 4 },
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '62%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: inventoryInUnits ? 'Unidades' : 'Categorías',
                                    fontSize: '11px',
                                    color: textColor,
                                },
                                value: {
                                    fontSize: '22px',
                                    fontWeight: 600,
                                    color: isDark ? '#e3edee' : '#1e2f34',
                                    formatter: (value) => inventoryInUnits
                                        ? Number(value).toLocaleString('es-ES', { maximumFractionDigits: 0 })
                                        : value + '%',
                                },
                            },
                        },
                    },
                },
                stroke: { width: 3, colors: [isDark ? '#13252a' : '#ffffff'] },
                tooltip: {
                    y: {
                        formatter: (value) => inventoryInUnits
                            ? Number(value).toLocaleString('es-ES', { maximumFractionDigits: 2 }) + ' uds'
                            : value + '%',
                    },
                },
            }).render();

            new ApexCharts(document.querySelector('#billingChart'), {
                chart: { ...baseChart, type: 'bar', height: 320, stacked: true },
                series: [
                    { name: 'Pagado', data: billing.paid },
                    { name: 'Pendiente', data: billing.pending },
                    { name: 'Vencido', data: billing.overdue },
                ],
                colors: ['#3aa981', '#d3a03f', '#cf6b80'],
                plotOptions: {
                    bar: { borderRadius: 4, columnWidth: '45%', borderRadiusApplication: 'end' },
                },
                dataLabels: { enabled: false },
                grid: baseGrid,
                legend: baseLegend,
                xaxis: {
                    categories: billing.categories,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { fontSize: '11px', colors: textColor } },
                },
                yaxis: {
                    labels: {
                        formatter: compactCurrency,
                        style: { fontSize: '11px', colors: textColor },
                    },
                },
                tooltip: { y: { formatter: currency } },
            }).render();
        });
    </script>
@endpush
