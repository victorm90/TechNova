@extends('layouts.main')

@php
    $esEntrada = $tipo === 'entrada';
    $rutaBase = $esEntrada ? 'entradas' : 'salidas';
    $titulo = $esEntrada ? 'Nueva entrada' : 'Nueva salida';
    $lineasIniciales = old('lineas', [['producto_id' => '', 'cantidad' => 1, 'costo_unitario' => '']]);
    $lineasIniciales = is_array($lineasIniciales) ? array_values($lineasIniciales) : [];
    $productosJson = $productos->map(fn ($producto) => [
        'id' => $producto->id,
        'nombre' => $producto->nombre,
        'sku' => $producto->sku,
        'precio_compra' => (float) $producto->precio_compra,
        'abreviatura' => $producto->unidadMedida?->abreviatura,
    ])->values();
    $stockJson = $stock ?? [];
    $costosJson = $costos ?? [];
@endphp

@section('titulo', $titulo)

@section('contenido')
    @include('layouts.page-header', [
        'icon' => $esEntrada ? 'arrow-down-to-line' : 'arrow-up-from-line',
        'title' => $titulo,
        'description' => $esEntrada
            ? 'Registra una recepción con varios productos en un mismo documento'
            : 'Registra un despacho verificando las existencias disponibles',
        'accent' => $esEntrada ? 'green' : 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => $esEntrada ? 'Entradas' : 'Salidas', 'url' => route($rutaBase.'.index')],
            ['label' => 'Nueva'],
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

    @if ($productos->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <x-table-empty icon="package"
                    title="No hay productos activos"
                    message="Necesitas al menos un producto activo para registrar movimientos de inventario.">
                    @can('manage-productos')
                        <a href="{{ route('productos.create') }}"
                            class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                            Crear producto
                        </a>
                    @endcan
                </x-table-empty>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route($rutaBase.'.store') }}" id="operacion-form">
            @csrf

            <div class="card border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Datos del documento</h2>
                        <p class="nova-card-subtitle">El código se genera automáticamente al guardar</p>
                    </div>
                </div>

                <div class="card-body grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="almacen_id" class="mb-2 inline-block text-sm font-medium">Almacén</label>
                        <select id="almacen_id" name="almacen_id" required
                            class="form-select w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                            <option value="">Selecciona un almacén</option>
                            @foreach ($almacenes as $almacen)
                                <option value="{{ $almacen->id }}"
                                    @selected((int) old('almacen_id', request('almacen', $almacenes->first()?->id)) === $almacen->id)>
                                    {{ $almacen->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="referencia" class="mb-2 inline-block text-sm font-medium">Referencia</label>
                        <input id="referencia" name="referencia" type="text" maxlength="60"
                            value="{{ old('referencia') }}"
                            class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Factura, guía, orden...">
                    </div>

                    @if ($esEntrada)
                        <div>
                            <label for="proveedor_id" class="mb-2 inline-block text-sm font-medium">Proveedor</label>
                            <select id="proveedor_id" name="proveedor_id"
                                class="form-select w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                <option value="">Sin proveedor</option>
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" @selected((int) old('proveedor_id') === $proveedor->id)>
                                        {{ $proveedor->nombre }}{{ $proveedor->activo ? '' : ' (inactivo)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="proveedor_nombre" class="mb-2 inline-block text-sm font-medium">
                                Nuevo proveedor
                            </label>
                            <input id="proveedor_nombre" name="proveedor_nombre" type="text" maxlength="120"
                                value="{{ old('proveedor_nombre') }}"
                                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                                placeholder="Solo el nombre">
                            <p class="mt-1 text-xs text-slate-400">
                                Se crea con el nombre o se reutiliza si ya existe.
                            </p>
                        </div>
                    @endif

                    <div class="{{ $esEntrada ? 'xl:col-span-2' : 'xl:col-span-2' }}">
                        <label for="notas" class="mb-2 inline-block text-sm font-medium">Notas</label>
                        <input id="notas" name="notas" type="text" maxlength="1000" value="{{ old('notas') }}"
                            class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Observaciones opcionales">
                    </div>
                </div>
            </div>

            <div class="card mt-5 border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Líneas del documento</h2>
                        <p class="nova-card-subtitle">
                            {{ $esEntrada ? 'Cantidad y costo unitario de cada producto recibido' : 'Cantidad a despachar de cada producto' }}
                        </p>
                    </div>

                    <button type="button" id="add-line"
                        class="btn inline-flex items-center border-slate-200 bg-white text-slate-600 hover:border-nova-300">
                        <i data-lucide="plus" class="me-1.5 size-4"></i>
                        Agregar línea
                    </button>
                </div>

                <div class="card-body">
                    <div class="nova-table-scroll">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                                    <th class="w-32 px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                    @if ($esEntrada)
                                        <th class="w-36 px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Costo unitario</th>
                                    @else
                                        <th class="w-32 px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Costo prom.</th>
                                    @endif
                                    <th class="w-36 px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Subtotal</th>
                                    <th class="w-14 px-2 py-3"></th>
                                </tr>
                            </thead>

                            <tbody id="lineas-body"></tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-zink-500">
                        <p class="text-xs text-slate-500">
                            @if ($esEntrada)
                                El costo unitario actualiza el costo promedio del almacén.
                            @else
                                El sistema rechaza la operación completa si alguna línea excede el stock disponible.
                            @endif
                        </p>

                        <div class="flex items-center gap-6 text-sm">
                            <div>
                                <span class="text-slate-500">Unidades:</span>
                                <span id="total-unidades" class="font-semibold is-numeric text-slate-800">0</span>
                            </div>
                            <div>
                                <span class="text-slate-500">Valor:</span>
                                <span id="total-valor" class="font-semibold is-numeric text-slate-800">$0,00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <a href="{{ route($rutaBase.'.index') }}"
                    class="btn inline-flex items-center justify-center border-slate-200 text-slate-600">
                    Cancelar
                </a>

                <button type="submit"
                    class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="save" class="me-1.5 size-4"></i>
                    Registrar {{ $esEntrada ? 'entrada' : 'salida' }}
                </button>
            </div>
        </form>

        <template id="linea-template">
            <tr>
                <td class="px-3 py-3">
                    <select name="lineas[__INDEX__][producto_id]" required
                        class="form-select w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700"
                        data-producto></select>
                    <p class="mt-1 text-[11px] text-slate-400" data-stock-hint></p>
                </td>

                <td class="px-3 py-3">
                    <input type="number" step="1" min="1" name="lineas[__INDEX__][cantidad]" value="1" required
                        class="form-input w-full text-right text-sm is-numeric border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                        data-cantidad>
                </td>

                <td class="px-3 py-3 text-right">
                    @if ($esEntrada)
                        <input type="number" step="0.01" min="0" name="lineas[__INDEX__][costo_unitario]" value="0.00" required
                            class="form-input w-full text-right text-sm is-numeric border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                            data-costo>
                    @else
                        <span class="text-sm is-numeric text-slate-600" data-costo-promedio>—</span>
                    @endif
                </td>

                <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700" data-subtotal>
                    $0,00
                </td>

                <td class="px-2 py-3">
                    <button type="button" data-remove-line title="Quitar línea"
                        class="inline-flex size-8 items-center justify-center rounded-lg border border-rose-200 text-rose-500 hover:bg-rose-50">
                        <i data-lucide="trash-2" class="size-4"></i>
                    </button>
                </td>
            </tr>
        </template>
    @endif
@endsection

@push('scripts')
    @if ($productos->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const esEntrada = @json($esEntrada);
                const productos = @json($productosJson);
                const stock = @json($stockJson);
                const costos = @json($costosJson);
                const lineasIniciales = @json($lineasIniciales);

                const tbody = document.getElementById('lineas-body');
                const template = document.getElementById('linea-template');
                const almacen = document.getElementById('almacen_id');
                const addButton = document.getElementById('add-line');
                const totalUnidades = document.getElementById('total-unidades');
                const totalValor = document.getElementById('total-valor');

                if (!tbody || !template || !almacen) {
                    return;
                }

                const productMap = new Map(productos.map((producto) => [String(producto.id), producto]));
                const numberFormat = new Intl.NumberFormat('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
                const integerFormat = new Intl.NumberFormat('es-ES', {
                    maximumFractionDigits: 0,
                });

                const money = (value) => '$' + numberFormat.format(Number(value) || 0);

                let index = 0;

                function stockKey(productoId) {
                    return almacen.value + '-' + productoId;
                }

                function disponible(productoId) {
                    return Number(stock[stockKey(productoId)] ?? 0);
                }

                function costoPromedio(productoId) {
                    return Number(costos[stockKey(productoId)] ?? 0);
                }

                function crearFila(valores) {
                    const fragment = template.content.cloneNode(true);
                    const row = fragment.querySelector('tr');
                    const select = row.querySelector('[data-producto]');

                    row.querySelectorAll('[name]').forEach((element) => {
                        element.name = element.name.replace('__INDEX__', String(index));
                    });

                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Selecciona un producto';
                    select.appendChild(placeholder);

                    productos.forEach((producto) => {
                        const option = document.createElement('option');
                        option.value = String(producto.id);
                        option.textContent = producto.nombre + ' (' + producto.sku + ')';
                        option.dataset.precio = String(producto.precio_compra ?? 0);
                        select.appendChild(option);
                    });

                    tbody.appendChild(row);

                    const fila = tbody.lastElementChild;

                    if (valores.producto_id) {
                        select.value = String(valores.producto_id);
                        fila.dataset.ultimoProducto = String(valores.producto_id);
                    }

                    const cantidad = fila.querySelector('[data-cantidad]');
                    cantidad.value = valores.cantidad ?? 1;

                    if (esEntrada) {
                        const costo = fila.querySelector('[data-costo]');

                        if (valores.costo_unitario !== undefined && valores.costo_unitario !== '') {
                            costo.value = valores.costo_unitario;
                        }
                    }

                    index++;
                    actualizarFila(fila);
                    actualizarTotales();
                }

                function actualizarFila(row) {
                    const select = row.querySelector('[data-producto]');
                    const cantidadInput = row.querySelector('[data-cantidad]');
                    const productoId = select.value;
                    const producto = productMap.get(productoId);
                    const cantidad = Math.max(0, Number(cantidadInput.value) || 0);

                    if (esEntrada) {
                        const costoInput = row.querySelector('[data-costo]');

                        if (producto && row.dataset.ultimoProducto !== productoId) {
                            costoInput.value = Number(producto.precio_compra ?? 0).toFixed(2);
                            row.dataset.ultimoProducto = productoId;
                        }

                        const costo = Math.max(0, Number(costoInput.value) || 0);
                        row.querySelector('[data-subtotal]').textContent = money(cantidad * costo);
                    } else {
                        const costo = costoPromedio(productoId);
                        const costoElemento = row.querySelector('[data-costo-promedio]');
                        const hint = row.querySelector('[data-stock-hint]');

                        costoElemento.textContent = productoId ? money(costo) : '—';
                        row.querySelector('[data-subtotal]').textContent = money(cantidad * costo);

                        if (productoId) {
                            const disponibleActual = disponible(productoId);
                            cantidadInput.max = String(disponibleActual);
                            hint.textContent = 'Disponible: ' + integerFormat.format(disponibleActual);
                            hint.classList.toggle('text-rose-600', cantidad > disponibleActual);
                        } else {
                            cantidadInput.removeAttribute('max');
                            hint.textContent = '';
                            hint.classList.remove('text-rose-600');
                        }
                    }
                }

                function actualizarTotales() {
                    let unidades = 0;
                    let valor = 0;

                    tbody.querySelectorAll('tr').forEach((row) => {
                        const select = row.querySelector('[data-producto]');
                        const productoId = select.value;
                        const cantidad = Math.max(0, Number(row.querySelector('[data-cantidad]').value) || 0);
                        let costo = 0;

                        if (esEntrada) {
                            costo = Math.max(0, Number(row.querySelector('[data-costo]').value) || 0);
                        } else {
                            costo = costoPromedio(productoId);
                        }

                        unidades += cantidad;
                        valor += cantidad * costo;
                    });

                    totalUnidades.textContent = integerFormat.format(unidades);
                    totalValor.textContent = money(valor);
                }

                function actualizarTodo() {
                    tbody.querySelectorAll('tr').forEach(actualizarFila);
                    actualizarTotales();
                }

                tbody.addEventListener('input', function (event) {
                    const row = event.target.closest('tr');

                    if (row) {
                        actualizarFila(row);
                        actualizarTotales();
                    }
                });

                tbody.addEventListener('change', function (event) {
                    const row = event.target.closest('tr');

                    if (row) {
                        actualizarFila(row);
                        actualizarTotales();
                    }
                });

                tbody.addEventListener('click', function (event) {
                    const button = event.target.closest('[data-remove-line]');

                    if (!button) {
                        return;
                    }

                    const row = button.closest('tr');
                    const rows = tbody.querySelectorAll('tr');

                    if (rows.length <= 1) {
                        row.querySelector('[data-producto]').value = '';
                        row.querySelector('[data-cantidad]').value = 1;
                        row.querySelector('[data-subtotal]').textContent = '$0,00';

                        if (esEntrada) {
                            row.querySelector('[data-costo]').value = '0.00';
                        }

                        row.dataset.ultimoProducto = '';
                        actualizarFila(row);
                    } else {
                        row.remove();
                    }

                    actualizarTotales();
                });

                addButton?.addEventListener('click', function () {
                    crearFila({ producto_id: '', cantidad: 1, costo_unitario: '' });
                });

                almacen.addEventListener('change', actualizarTodo);

                const params = new URLSearchParams(window.location.search);
                const productoPrefill = params.get('producto');

                if (Array.isArray(lineasIniciales) && lineasIniciales.length > 0) {
                    lineasIniciales.forEach((linea, lineaIndex) => {
                        crearFila({
                            ...linea,
                            producto_id: linea.producto_id || (lineaIndex === 0 ? (productoPrefill || '') : ''),
                        });
                    });
                } else {
                    crearFila({ producto_id: productoPrefill || '', cantidad: 1, costo_unitario: '' });
                }

                actualizarTodo();
            });
        </script>
    @endif
@endpush
