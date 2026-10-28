@extends('layouts.main')

@section('titulo', $oferta->exists ? 'Editar oferta' : 'Nueva oferta')

@php
    $lineasServicios = old('servicios');

    if ($lineasServicios === null) {
        $lineasServicios = $oferta->exists
            ? $oferta->servicios
                ->map(fn ($linea): array => [
                    'id' => $linea->id,
                    'servicio_id' => $linea->servicio_id,
                    'cantidad' => $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                    'productos' => $linea->productos
                        ->map(fn ($producto): array => [
                            'id' => $producto->id,
                            'producto_id' => $producto->producto_id,
                            'cantidad' => $producto->cantidad,
                            'precio_venta' => $producto->precio_venta,
                        ])
                        ->all(),
                ])
                ->all()
            : [];
    }

    if (! is_array($lineasServicios) || $lineasServicios === []) {
        $lineasServicios = [['servicio_id' => '', 'cantidad' => '', 'precio_unitario' => '', 'productos' => []]];
    }

    $lineasServicios = array_map(function (array $linea): array {
        $linea['productos'] = is_array($linea['productos'] ?? null) ? $linea['productos'] : [];

        return $linea;
    }, $lineasServicios);

    $lineasGastos = old('gastos');

    if ($lineasGastos === null) {
        $lineasGastos = $oferta->exists
            ? $oferta->gastos
                ->map(fn ($linea): array => [
                    'id' => $linea->id,
                    'gasto_id' => $linea->gasto_id,
                    'cantidad' => $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                ])
                ->all()
            : [];
    }

    if (! is_array($lineasGastos) || $lineasGastos === []) {
        $lineasGastos = [['gasto_id' => '', 'cantidad' => '', 'precio_unitario' => '']];
    }

    $totalServicios = array_reduce(
        $lineasServicios,
        fn (float $total, array $linea): float => $total + ((float) ($linea['cantidad'] ?? 0) * (float) ($linea['precio_unitario'] ?? 0)),
        0.0,
    );

    $totalProductos = array_reduce(
        $lineasServicios,
        fn (float $total, array $linea): float => $total + array_reduce(
            $linea['productos'],
            fn (float $subtotal, array $producto): float => $subtotal + ((float) ($producto['cantidad'] ?? 0) * (float) ($producto['precio_venta'] ?? 0)),
            0.0,
        ),
        0.0,
    );

    $totalGastos = array_reduce(
        $lineasGastos,
        fn (float $total, array $linea): float => $total + ((float) ($linea['cantidad'] ?? 0) * (float) ($linea['precio_unitario'] ?? 0)),
        0.0,
    );

    $formatoImporte = fn (float $valor): string => '$ '.number_format($valor, 2, ',', '.');

    $siguienteServicio = max(array_map('intval', array_keys($lineasServicios))) + 1;
    $siguienteGasto = max(array_map('intval', array_keys($lineasGastos))) + 1;
    $siguienteProducto = 1000;

    $clienteActual = old('customer_id', $oferta->customer_id);
    $fechaActual = old('fecha', $oferta->fecha?->format('Y-m-d') ?? now()->toDateString());
    $estadoActual = old('estado', $oferta->estado ?? \App\Models\Oferta::ESTADO_BORRADOR);
    $almacenFormulario = old('almacen_id', $almacenActual);
    $notaActual = old('nota', $oferta->nota);
    $formaPagoActual = old('forma_pago', $oferta->forma_pago);
    $observacionesActual = old('observaciones', $oferta->observaciones);
    $firmaActual = old('firma_pie', $oferta->firma_pie);
    $vigenciaActual = old('vigencia_dias', $oferta->vigencia_dias ?? config('empresa.vigencia_dias', 15));
@endphp

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'file-text',
        'title' => $oferta->exists ? 'Editar oferta' : 'Nueva oferta',
        'description' => 'Complete el cliente, los servicios, los materiales y los datos del documento',
        'accent' => 'green',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Ofertas', 'url' => route('ofertas.index')],
            ['label' => $oferta->exists ? $oferta->codigo : 'Nueva'],
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

    <form method="POST"
        action="{{ $oferta->exists ? route('ofertas.update', $oferta) : route('ofertas.store') }}"
        id="oferta-form">
        @csrf
        @if ($oferta->exists)
            @method('PUT')
        @endif

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="customer_id" class="mb-2 inline-block text-sm font-medium">Cliente</label>
                            <select id="customer_id" name="customer_id" required
                                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                <option value="">Seleccione un cliente</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}"
                                        @selected((string) $clienteActual === (string) $cliente->id)>
                                        {{ $cliente->name }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($clientes->isEmpty())
                                <p class="mt-1 text-xs text-amber-600">
                                    No hay clientes registrados.
                                    <a href="{{ route('customers.create') }}" class="font-semibold underline">Cree uno primero</a>.
                                </p>
                            @endif
                        </div>

                        <div>
                            <label for="fecha" class="mb-2 inline-block text-sm font-medium">Fecha</label>
                            <input id="fecha" name="fecha" type="date" required value="{{ $fechaActual }}"
                                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                        </div>

                        <div>
                            <label for="estado" class="mb-2 inline-block text-sm font-medium">Estado</label>
                            <select id="estado" name="estado" required
                                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                @foreach (\App\Models\Oferta::estados() as $valor => $etiqueta)
                                    @continue($valor === \App\Models\Oferta::ESTADO_FACTURADO)
                                    <option value="{{ $valor }}" @selected((string) $estadoActual === $valor)>
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body space-y-4">
                        <div>
                            <h2 class="font-semibold text-slate-700 dark:text-zink-100">Datos del documento</h2>
                            <p class="mt-0.5 text-xs text-slate-400">
                                Se imprimen en la oferta y en el PDF. El almacén define de dónde se descontarán los materiales al facturar.
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label for="almacen_id" class="mb-2 inline-block text-sm font-medium">Almacén de materiales</label>
                                <select id="almacen_id" name="almacen_id"
                                    class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                    <option value="">Usar almacén predeterminado</option>
                                    @foreach ($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}"
                                            @selected((string) $almacenFormulario === (string) $almacen->id)>
                                            {{ $almacen->nombre }}{{ $almacen->activo ? '' : ' (inactivo)' }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-[11px] text-slate-400">El stock mostrado corresponde al almacén elegido al abrir el formulario.</p>
                            </div>

                            <div>
                                <label for="forma_pago" class="mb-2 inline-block text-sm font-medium">Forma de pago</label>
                                <input id="forma_pago" name="forma_pago" type="text" maxlength="120"
                                    value="{{ $formaPagoActual }}" placeholder="Ej.: 50% anticipo, 50% contra entrega"
                                    class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                            </div>

                            <div>
                                <label for="vigencia_dias" class="mb-2 inline-block text-sm font-medium">Vigencia (días)</label>
                                <input id="vigencia_dias" name="vigencia_dias" type="number" min="1" max="365"
                                    value="{{ $vigenciaActual }}"
                                    class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                            </div>

                            <div class="md:col-span-3">
                                <label for="nota" class="mb-2 inline-block text-sm font-medium">Nota libre de la oferta</label>
                                <textarea id="nota" name="nota" rows="3" maxlength="2000"
                                    class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                                    placeholder="Nota que se mostrará en el documento (condiciones especiales, alcance, etc.)">{{ $notaActual }}</textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label for="observaciones" class="mb-2 inline-block text-sm font-medium">Observaciones</label>
                                <textarea id="observaciones" name="observaciones" rows="2" maxlength="2000"
                                    class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">{{ $observacionesActual }}</textarea>
                            </div>

                            <div>
                                <label for="firma_pie" class="mb-2 inline-block text-sm font-medium">Pie de firma</label>
                                <input id="firma_pie" name="firma_pie" type="text" maxlength="150"
                                    value="{{ $firmaActual }}" placeholder="{{ config('empresa.firma_pie') }}"
                                    class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-semibold text-slate-700 dark:text-zink-100">Servicios</h2>
                                <p class="mt-0.5 text-xs text-slate-400">Cantidad y precio pueden ajustarse para esta oferta. Cada cambio de precio queda auditado.</p>
                            </div>

                            <button type="button" data-agregar-servicio
                                class="btn inline-flex items-center justify-center border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100">
                                <i data-lucide="plus" class="me-1.5 size-4"></i>
                                Agregar servicio
                            </button>
                        </div>

                        <div class="mt-4 space-y-3" data-lineas="servicio">
                            @foreach ($lineasServicios as $indice => $linea)
                                @include('ofertas.partials.linea-servicio', ['indice' => $indice, 'linea' => $linea])
                            @endforeach
                        </div>

                        <div class="mt-4 space-y-1 border-t border-slate-200 pt-3 dark:border-zink-500">
                            <div class="flex items-center justify-end gap-3">
                                <span class="text-sm text-slate-500 dark:text-zink-300">Subtotal servicios</span>
                                <span data-total-servicios class="text-sm font-semibold text-slate-700 dark:text-zink-100">
                                    {{ $formatoImporte($totalServicios) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-end gap-3">
                                <span class="text-sm text-slate-500 dark:text-zink-300">Materiales</span>
                                <span data-total-productos class="text-sm font-semibold text-slate-700 dark:text-zink-100">
                                    {{ $formatoImporte($totalProductos) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-semibold text-slate-700 dark:text-zink-100">Gastos</h2>
                                <p class="mt-0.5 text-xs text-slate-400">Costos adicionales que forman parte de la oferta, con historial de precios.</p>
                            </div>

                            <button type="button" data-agregar-gasto
                                class="btn inline-flex items-center justify-center border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100">
                                <i data-lucide="plus" class="me-1.5 size-4"></i>
                                Agregar gasto
                            </button>
                        </div>

                        <div class="mt-4 space-y-3" data-lineas="gasto">
                            @foreach ($lineasGastos as $indice => $linea)
                                @include('ofertas.partials.linea-gasto', ['indice' => $indice, 'linea' => $linea])
                            @endforeach
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-3 border-t border-slate-200 pt-3 dark:border-zink-500">
                            <span class="text-sm text-slate-500 dark:text-zink-300">Subtotal gastos</span>
                            <span data-total-gastos class="text-sm font-semibold text-slate-700 dark:text-zink-100">
                                {{ $formatoImporte($totalGastos) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="xl:sticky xl:top-24 xl:self-start">
                <div class="card border-0 shadow-sm">
                    <div class="card-body space-y-4">
                        <div>
                            <h2 class="font-semibold text-slate-700 dark:text-zink-100">Resumen</h2>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ $oferta->exists ? 'Actualice los datos y guarde los cambios.' : 'Revise el total antes de guardar.' }}
                            </p>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-zink-300">Servicios</span>
                                <span data-total-servicios class="font-semibold text-slate-700 dark:text-zink-100">
                                    {{ $formatoImporte($totalServicios) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-zink-300">Materiales</span>
                                <span data-total-productos class="font-semibold text-slate-700 dark:text-zink-100">
                                    {{ $formatoImporte($totalProductos) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-zink-300">Gastos</span>
                                <span data-total-gastos class="font-semibold text-slate-700 dark:text-zink-100">
                                    {{ $formatoImporte($totalGastos) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-zink-500">
                            <span class="font-semibold text-slate-700 dark:text-zink-100">Total</span>
                            <span data-total-oferta class="text-lg font-bold text-slate-800 dark:text-zink-50">
                                {{ $formatoImporte($totalServicios + $totalProductos + $totalGastos) }}
                            </span>
                        </div>

                        <div class="space-y-2 pt-1">
                            <button type="submit"
                                class="btn w-full border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                <i data-lucide="save" class="me-1.5 size-4"></i>
                                {{ $oferta->exists ? 'Guardar cambios' : 'Crear oferta' }}
                            </button>

                            <a href="{{ $oferta->exists ? route('ofertas.show', $oferta) : route('ofertas.index') }}"
                                class="btn w-full border-slate-200 text-slate-600">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </form>

    <template id="servicio-row-template">
        @include('ofertas.partials.linea-servicio', ['indice' => '__INDEX__', 'linea' => []])
    </template>

    <template id="gasto-row-template">
        @include('ofertas.partials.linea-gasto', ['indice' => '__INDEX__', 'linea' => []])
    </template>

    <template id="producto-row-template">
        @include('ofertas.partials.linea-producto', [
            'servicioIndice' => '__PARENT__',
            'indice' => '__INDEX__',
            'linea' => [],
        ])
    </template>

    <script>
        (() => {
            const form = document.getElementById('oferta-form');

            if (!form) {
                return;
            }

            const nextIndex = {
                servicio: {{ $siguienteServicio }},
                gasto: {{ $siguienteGasto }},
                producto: {{ $siguienteProducto }},
            };

            const containers = {
                servicio: form.querySelector('[data-lineas="servicio"]'),
                gasto: form.querySelector('[data-lineas="gasto"]'),
            };

            const templates = {
                servicio: document.getElementById('servicio-row-template'),
                gasto: document.getElementById('gasto-row-template'),
                producto: document.getElementById('producto-row-template'),
            };

            const amountFormat = new Intl.NumberFormat('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            const money = (value) => '$ ' + amountFormat.format(value);

            const number = (value) => {
                const parsed = Number.parseFloat(String(value ?? '').replace(',', '.'));

                return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
            };

            const round = (value) => Math.round((value + Number.EPSILON) * 100) / 100;

            const warehouse = () => form.querySelector('[name="almacen_id"]')?.value ?? '';

            const updateStockHint = (productRow) => {
                const select = productRow.querySelector('[data-selector-producto]');
                const hint = productRow.querySelector('[data-stock-hint]');

                if (!select || !hint) {
                    return;
                }

                const option = select.selectedOptions[0];
                const stocks = JSON.parse(option?.dataset.stock || '{}');
                const amount = Number.parseInt(stocks[warehouse()] ?? stocks[String(warehouse())] ?? '0', 10);
                const unit = option?.dataset.unidad || 'ud';

                hint.textContent = 'Stock: ' + new Intl.NumberFormat('es-ES').format(Number.isFinite(amount) ? amount : 0) + ' ' + unit;
            };

            const recalc = () => {
                const totals = { servicio: 0, producto: 0, gasto: 0 };

                containers.servicio?.querySelectorAll('[data-linea="servicio"]').forEach((row) => {
                    const serviceTotal = round(
                        number(row.querySelector('[data-cantidad-servicio]')?.value) *
                        number(row.querySelector('[data-precio-servicio]')?.value),
                    );

                    const serviceOutput = row.querySelector('[data-total-servicio]');

                    if (serviceOutput) {
                        serviceOutput.textContent = money(serviceTotal);
                    }

                    let lineProducts = 0;

                    row.querySelectorAll('[data-producto]').forEach((productRow) => {
                        const productTotal = round(
                            number(productRow.querySelector('[data-cantidad-producto]')?.value) *
                            number(productRow.querySelector('[data-precio-producto]')?.value),
                        );

                        const productOutput = productRow.querySelector('[data-total-producto]');

                        if (productOutput) {
                            productOutput.textContent = money(productTotal);
                        }

                        lineProducts += productTotal;
                    });

                    const lineOutput = row.querySelector('[data-total-productos-linea]');

                    if (lineOutput) {
                        lineOutput.textContent = money(round(lineProducts));
                    }

                    totals.servicio += serviceTotal;
                    totals.producto += lineProducts;
                });

                containers.gasto?.querySelectorAll('[data-linea="gasto"]').forEach((row) => {
                    const total = round(
                        number(row.querySelector('[data-cantidad-gasto]')?.value) *
                        number(row.querySelector('[data-precio-gasto]')?.value),
                    );

                    const output = row.querySelector('[data-total-gasto]');

                    if (output) {
                        output.textContent = money(total);
                    }

                    totals.gasto += total;
                });

                document.querySelectorAll('[data-total-servicios]').forEach((element) => {
                    element.textContent = money(round(totals.servicio));
                });

                document.querySelectorAll('[data-total-productos]').forEach((element) => {
                    element.textContent = money(round(totals.producto));
                });

                document.querySelectorAll('[data-total-gastos]').forEach((element) => {
                    element.textContent = money(round(totals.gasto));
                });

                document.querySelectorAll('[data-total-oferta]').forEach((element) => {
                    element.textContent = money(round(totals.servicio + totals.producto + totals.gasto));
                });
            };

            const bindProductRow = (row) => {
                row.querySelector('[data-eliminar-producto]')?.addEventListener('click', () => {
                    row.remove();
                    recalc();
                });

                row.querySelector('[data-selector-producto]')?.addEventListener('change', (event) => {
                    const option = event.target.selectedOptions[0];
                    const priceInput = row.querySelector('[data-precio-producto]');
                    const quantityInput = row.querySelector('[data-cantidad-producto]');

                    if (option?.value) {
                        if (quantityInput && quantityInput.value === '') {
                            quantityInput.value = '1';
                        }

                        if (priceInput) {
                            priceInput.value = Number(option.dataset.precio || 0).toFixed(2);
                        }
                    }

                    updateStockHint(row);
                    recalc();
                });

                row.querySelectorAll('[data-cantidad-producto], [data-precio-producto]').forEach((input) => {
                    input.addEventListener('input', recalc);
                });

                updateStockHint(row);
            };

            const addProductRow = (serviceRow) => {
                const template = templates.producto;

                if (!template) {
                    return;
                }

                const wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML
                    .replaceAll('__PARENT__', serviceRow.dataset.indice)
                    .replaceAll('__INDEX__', String(nextIndex.producto++));

                const productRow = wrapper.firstElementChild;

                if (!productRow) {
                    return;
                }

                serviceRow.querySelector('[data-productos]')?.appendChild(productRow);
                bindProductRow(productRow);
                recalc();
                productRow.querySelector('[data-selector-producto]')?.focus();
            };

            const bindServiceRow = (row) => {
                row.querySelector('[data-eliminar-servicio]')?.addEventListener('click', () => {
                    row.remove();
                    recalc();
                });

                row.querySelector('[data-selector-servicio]')?.addEventListener('change', (event) => {
                    const option = event.target.selectedOptions[0];
                    const priceInput = row.querySelector('[data-precio-servicio]');
                    const quantityInput = row.querySelector('[data-cantidad-servicio]');

                    if (option?.value) {
                        if (quantityInput && quantityInput.value === '') {
                            quantityInput.value = '1';
                        }

                        if (priceInput) {
                            priceInput.value = Number(option.dataset.precio || 0).toFixed(2);
                        }
                    }

                    recalc();
                });

                row.querySelectorAll('[data-cantidad-servicio], [data-precio-servicio]').forEach((input) => {
                    input.addEventListener('input', recalc);
                });

                row.querySelector('[data-agregar-producto]')?.addEventListener('click', () => addProductRow(row));
                row.querySelectorAll('[data-producto]').forEach(bindProductRow);
            };

            const bindGastoRow = (row) => {
                row.querySelector('[data-eliminar-gasto]')?.addEventListener('click', () => {
                    row.remove();
                    recalc();
                });

                row.querySelector('[data-selector-gasto]')?.addEventListener('change', (event) => {
                    const option = event.target.selectedOptions[0];
                    const priceInput = row.querySelector('[data-precio-gasto]');
                    const quantityInput = row.querySelector('[data-cantidad-gasto]');

                    if (option?.value) {
                        if (quantityInput && quantityInput.value === '') {
                            quantityInput.value = '1';
                        }

                        if (priceInput) {
                            priceInput.value = Number(option.dataset.precio || 0).toFixed(2);
                        }
                    }

                    recalc();
                });

                row.querySelectorAll('[data-cantidad-gasto], [data-precio-gasto]').forEach((input) => {
                    input.addEventListener('input', recalc);
                });
            };

            containers.servicio?.querySelectorAll('[data-linea="servicio"]').forEach(bindServiceRow);
            containers.gasto?.querySelectorAll('[data-linea="gasto"]').forEach(bindGastoRow);

            document.querySelectorAll('[data-agregar-servicio]').forEach((button) => {
                button.addEventListener('click', () => {
                    const template = templates.servicio;
                    const container = containers.servicio;

                    if (!template || !container) {
                        return;
                    }

                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex.servicio++));

                    const row = wrapper.firstElementChild;

                    if (!row) {
                        return;
                    }

                    container.appendChild(row);
                    bindServiceRow(row);
                    recalc();
                    row.querySelector('[data-selector-servicio]')?.focus();
                });
            });

            document.querySelectorAll('[data-agregar-gasto]').forEach((button) => {
                button.addEventListener('click', () => {
                    const template = templates.gasto;
                    const container = containers.gasto;

                    if (!template || !container) {
                        return;
                    }

                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex.gasto++));

                    const row = wrapper.firstElementChild;

                    if (!row) {
                        return;
                    }

                    container.appendChild(row);
                    bindGastoRow(row);
                    recalc();
                    row.querySelector('[data-selector-gasto]')?.focus();
                });
            });

            form.querySelector('[name="almacen_id"]')?.addEventListener('change', () => {
                form.querySelectorAll('[data-producto]').forEach(updateStockHint);
            });

            recalc();
        })();
    </script>
@endsection
