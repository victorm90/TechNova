@extends('layouts.main')

@section('titulo', $oferta->exists ? 'Editar oferta' : 'Nueva oferta')

@php
    $lineasServicios = old('servicios');

    if ($lineasServicios === null) {
        $lineasServicios = $oferta->exists
            ? $oferta->servicios
                ->map(fn ($linea): array => [
                    'servicio_id' => $linea->servicio_id,
                    'cantidad' => $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                ])
                ->all()
            : [];
    }

    if (! is_array($lineasServicios) || $lineasServicios === []) {
        $lineasServicios = [['servicio_id' => '', 'cantidad' => '', 'precio_unitario' => '']];
    }

    $lineasGastos = old('gastos');

    if ($lineasGastos === null) {
        $lineasGastos = $oferta->exists
            ? $oferta->gastos
                ->map(fn ($linea): array => [
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

    $totalGastos = array_reduce(
        $lineasGastos,
        fn (float $total, array $linea): float => $total + ((float) ($linea['cantidad'] ?? 0) * (float) ($linea['precio_unitario'] ?? 0)),
        0.0,
    );

    $formatoImporte = fn (float $valor): string => '$ '.number_format($valor, 2, ',', '.');

    $siguienteServicio = max(array_map('intval', array_keys($lineasServicios))) + 1;
    $siguienteGasto = max(array_map('intval', array_keys($lineasGastos))) + 1;

    $clienteActual = old('customer_id', $oferta->customer_id);
    $fechaActual = old('fecha', $oferta->fecha?->format('Y-m-d') ?? now()->toDateString());
    $estadoActual = old('estado', $oferta->estado ?? \App\Models\Oferta::ESTADO_BORRADOR);
@endphp

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'file-text',
        'title' => $oferta->exists ? 'Editar oferta' : 'Nueva oferta',
        'description' => 'Seleccione el cliente y agregue los servicios y gastos de la oferta',
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
                        <div class="md:col-span-1">
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
                                    <option value="{{ $valor }}" @selected((string) $estadoActual === $valor)>
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-semibold text-slate-700 dark:text-zink-100">Servicios</h2>
                                <p class="mt-0.5 text-xs text-slate-400">Cantidad y precio pueden ajustarse para esta oferta.</p>
                            </div>

                            <button type="button" data-agregar-linea="servicio"
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

                        <div class="mt-4 flex items-center justify-end gap-3 border-t border-slate-200 pt-3 dark:border-zink-500">
                            <span class="text-sm text-slate-500 dark:text-zink-300">Subtotal servicios</span>
                            <span data-total-servicios class="text-sm font-semibold text-slate-700 dark:text-zink-100">
                                {{ $formatoImporte($totalServicios) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-semibold text-slate-700 dark:text-zink-100">Gastos</h2>
                                <p class="mt-0.5 text-xs text-slate-400">Costos adicionales que forman parte de la oferta.</p>
                            </div>

                            <button type="button" data-agregar-linea="gasto"
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
                                <span class="text-slate-500 dark:text-zink-300">Gastos</span>
                                <span data-total-gastos class="font-semibold text-slate-700 dark:text-zink-100">
                                    {{ $formatoImporte($totalGastos) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-zink-500">
                            <span class="font-semibold text-slate-700 dark:text-zink-100">Total</span>
                            <span data-total-oferta class="text-lg font-bold text-slate-800 dark:text-zink-50">
                                {{ $formatoImporte($totalServicios + $totalGastos) }}
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

    <script>
        (() => {
            const form = document.getElementById('oferta-form');

            if (!form) {
                return;
            }

            const nextIndex = {
                servicio: {{ $siguienteServicio }},
                gasto: {{ $siguienteGasto }},
            };

            const containers = {
                servicio: form.querySelector('[data-lineas="servicio"]'),
                gasto: form.querySelector('[data-lineas="gasto"]'),
            };

            const templates = {
                servicio: document.getElementById('servicio-row-template'),
                gasto: document.getElementById('gasto-row-template'),
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

            const recalc = () => {
                const totals = { servicio: 0, gasto: 0 };

                Object.entries(containers).forEach(([section, container]) => {
                    if (!container) {
                        return;
                    }

                    container.querySelectorAll('[data-linea]').forEach((row) => {
                        const quantity = number(row.querySelector('[data-cantidad]')?.value);
                        const price = number(row.querySelector('[data-precio]')?.value);
                        const total = round(quantity * price);

                        const output = row.querySelector('[data-linea-total]');

                        if (output) {
                            output.textContent = money(total);
                        }

                        totals[section] += total;
                    });

                    document.querySelectorAll('[data-total-' + section + 's]').forEach((element) => {
                        element.textContent = money(round(totals[section]));
                    });
                });

                const total = round(totals.servicio + totals.gasto);

                document.querySelectorAll('[data-total-oferta]').forEach((element) => {
                    element.textContent = money(total);
                });
            };

            const bindRow = (row) => {
                row.querySelector('[data-eliminar-linea]')?.addEventListener('click', () => {
                    row.remove();
                    recalc();
                });

                row.querySelector('[data-selector]')?.addEventListener('change', (event) => {
                    const option = event.target.selectedOptions[0];
                    const priceInput = row.querySelector('[data-precio]');
                    const quantityInput = row.querySelector('[data-cantidad]');

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

                row.querySelectorAll('[data-cantidad], [data-precio]').forEach((input) => {
                    input.addEventListener('input', recalc);
                });
            };

            form.querySelectorAll('[data-linea]').forEach(bindRow);

            document.querySelectorAll('[data-agregar-linea]').forEach((button) => {
                button.addEventListener('click', () => {
                    const section = button.dataset.agregarLinea;
                    const template = templates[section];
                    const container = containers[section];

                    if (!template || !container) {
                        return;
                    }

                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex[section]++));

                    const row = wrapper.firstElementChild;

                    if (!row) {
                        return;
                    }

                    container.appendChild(row);
                    bindRow(row);
                    recalc();
                    row.querySelector('[data-selector]')?.focus();
                });
            });

            recalc();
        })();
    </script>
@endsection
