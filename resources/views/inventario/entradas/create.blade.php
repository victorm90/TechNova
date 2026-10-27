@extends('layouts.main')

@php
    $productosJson = $productos->map(fn ($producto) => [
        'id' => $producto->id,
        'nombre' => $producto->nombre,
        'sku' => $producto->sku,
        'precio_compra' => (float) $producto->precio_compra,
        'stock_minimo' => (int) $producto->stock_minimo,
        'abreviatura' => $producto->unidadMedida?->abreviatura,
    ])->values();

    $lineasIniciales = old('lineas');

    if (! is_array($lineasIniciales) || $lineasIniciales === []) {
        $lineasIniciales = [[
            'producto_id' => request('producto', ''),
            'precio_compra' => '',
            'stock_minimo' => '',
            'cantidad' => 1,
        ]];
    }

    $almacenSeleccionado = (int) old('almacen_id', $almacenId ?? 0);
    $sinAlmacenes = $almacenes->isEmpty();
@endphp

@section('titulo', 'Nueva entrada')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'arrow-down-to-line',
        'title' => 'Nueva entrada',
        'description' => 'Registra varios productos en una misma recepción',
        'accent' => 'green',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => 'Entradas', 'url' => route('entradas.index')],
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
                    message="Necesitas al menos un producto activo para registrar entradas de inventario.">
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
        <div x-data="entradaInventario({
            almacenUrl: '{{ route('entradas.almacenes.store') }}',
            proveedorUrl: '{{ route('entradas.proveedores.store') }}',
            abrirAlmacen: {{ $sinAlmacenes ? 'true' : 'false' }},
            tieneAlmacen: {{ $sinAlmacenes ? 'false' : 'true' }}
        })">
        <form method="POST" action="{{ route('entradas.store') }}" id="entrada-form">
            @csrf

            <div class="card border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Datos de la entrada</h2>
                        <p class="nova-card-subtitle">El código se genera automáticamente al guardar</p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        <i data-lucide="warehouse" class="size-3.5"></i>
                        Destino y proveedor
                    </span>
                </div>

                <div class="card-body grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <label for="almacen_id" class="text-sm font-medium">Almacén de destino</label>
                            <button type="button" @click="modalAlmacen = true"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-nova-700 hover:text-nova-900">
                                <i data-lucide="plus" class="size-3.5"></i>
                                Nuevo almacén
                            </button>
                        </div>

                        <select id="almacen_id" name="almacen_id" required
                            class="form-select w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                            <option value="">Selecciona un almacén</option>
                            @foreach ($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" @selected($almacenSeleccionado === $almacen->id)>
                                    {{ $almacen->nombre }}
                                </option>
                            @endforeach
                        </select>

                        <p x-show="!tieneAlmacen" x-cloak class="mt-1 text-xs font-medium text-amber-600">
                            No hay almacenes activos. Crea uno para poder guardar la entrada.
                        </p>

                        <p x-show="tieneAlmacen" x-cloak class="mt-1 text-xs text-slate-400">
                            Las existencias quedarán asociadas al almacén seleccionado.
                        </p>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <label for="proveedor_id" class="text-sm font-medium">Proveedor (opcional)</label>
                            <button type="button" @click="modalProveedor = true"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-nova-700 hover:text-nova-900">
                                <i data-lucide="plus" class="size-3.5"></i>
                                Nuevo proveedor
                            </button>
                        </div>

                        <select id="proveedor_id" name="proveedor_id"
                            class="form-select w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                            <option value="">Sin proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" @selected((int) old('proveedor_id') === $proveedor->id)>
                                    {{ $proveedor->nombre }}{{ $proveedor->activo ? '' : ' (inactivo)' }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-1 text-xs text-slate-400">
                            Puedes dejarlo vacío si no aplica.
                        </p>
                    </div>

                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800 md:col-span-2">
                        <div class="flex items-start gap-2">
                            <i data-lucide="info" class="mt-0.5 size-4 shrink-0"></i>
                            <p class="leading-6">
                                El precio de compra queda guardado en esta entrada. Si el precio cambia,
                                registra una nueva entrada para conservar el historial de costos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-5 border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Productos recibidos</h2>
                        <p class="nova-card-subtitle">
                            Precio de compra, stock mínimo y cantidad por cada producto
                        </p>
                    </div>

                    <button type="button" id="add-line"
                        class="btn inline-flex items-center border-slate-200 bg-white text-slate-600 hover:border-nova-300">
                        <i data-lucide="plus" class="me-1.5 size-4"></i>
                        Agregar producto
                    </button>
                </div>

                <div class="card-body">
                    <div class="nova-table-scroll">
                        <table class="nova-table w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Producto</th>
                                    <th class="w-36 px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Precio de compra</th>
                                    <th class="w-32 px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Stock mínimo</th>
                                    <th class="w-32 px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Cantidad</th>
                                    <th class="w-36 px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Subtotal</th>
                                    <th class="w-14 px-2 py-3"></th>
                                </tr>
                            </thead>

                            <tbody id="lineas-body"></tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-zink-500">
                        <p class="text-xs text-slate-500">
                            El costo promedio del almacén se recalcula automáticamente con cada entrada.
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
                <a href="{{ route('entradas.index') }}"
                    class="btn inline-flex items-center justify-center border-slate-200 text-slate-600">
                    Cancelar
                </a>

                <button type="submit"
                    class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="save" class="me-1.5 size-4"></i>
                    Registrar entrada
                </button>
            </div>
        </form>

        <template id="linea-template">
            <tr>
                <td class="px-3 py-3">
                    <select name="lineas[__INDEX__][producto_id]" required
                        class="form-select w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700"
                        data-producto></select>
                    <p class="mt-1 text-[11px] text-slate-400" data-producto-hint></p>
                </td>

                <td class="px-3 py-3">
                    <input type="number" step="0.01" min="0" name="lineas[__INDEX__][precio_compra]" required
                        class="form-input w-full text-right text-sm is-numeric border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                        data-precio>
                </td>

                <td class="px-3 py-3">
                    <input type="number" step="1" min="0" name="lineas[__INDEX__][stock_minimo]" required
                        class="form-input w-full text-right text-sm is-numeric border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                        data-minimo>
                </td>

                <td class="px-3 py-3">
                    <input type="number" step="1" min="1" name="lineas[__INDEX__][cantidad]" value="1" required
                        class="form-input w-full text-right text-sm is-numeric border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                        data-cantidad>
                </td>

                <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700" data-subtotal>
                    $0,00
                </td>

                <td class="px-2 py-3">
                    <button type="button" data-remove-line title="Quitar producto"
                        class="inline-flex size-8 items-center justify-center rounded-lg border border-rose-200 text-rose-500 hover:bg-rose-50">
                        <i data-lucide="trash-2" class="size-4"></i>
                    </button>
                </td>
            </tr>
        </template>

        {{-- MODAL: NUEVO ALMACÉN --}}
        <div x-show="modalAlmacen" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1050" role="dialog"
            aria-modal="true" aria-labelledby="modal-almacen-title">
            <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="cerrarAlmacen()" aria-hidden="true"></div>

            <div class="relative flex min-h-full items-start justify-center px-4 pb-8 pt-16 md:pt-20">
                <div @click.outside="cerrarAlmacen()" @keydown.escape.window="cerrarAlmacen()"
                    class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-zink-700">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-zink-500">
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-lg bg-emerald-500/10">
                                <i data-lucide="warehouse" class="size-5 text-emerald-600"></i>
                            </span>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Inventario</p>
                                <h5 id="modal-almacen-title" class="text-lg font-semibold text-slate-800 dark:text-zink-100">
                                    Nuevo almacén
                                </h5>
                            </div>
                        </div>

                        <button type="button" @click="cerrarAlmacen()"
                            class="inline-flex size-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-zink-600">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form @submit.prevent="guardarAlmacen()" class="space-y-4 p-6">
                        <div>
                            <label for="nuevo-almacen-nombre" class="mb-2 inline-block text-sm font-medium">
                                Nombre del almacén
                            </label>
                            <input id="nuevo-almacen-nombre" x-ref="almacenNombre" x-model="nuevoAlmacen" type="text"
                                required maxlength="80" autocomplete="off"
                                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                                placeholder="Ej. Almacén secundario">
                            <p class="mt-1 text-xs text-slate-400">
                                Podrás completar dirección y teléfono después desde Almacenes.
                            </p>
                            <p x-show="errorAlmacen" x-text="errorAlmacen" x-cloak class="mt-1 text-xs font-medium text-rose-600"></p>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-zink-500">
                            <button type="button" @click="cerrarAlmacen()" class="btn border-slate-200 text-slate-500">
                                Cancelar
                            </button>

                            <button type="submit" :disabled="guardandoAlmacen"
                                class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600 disabled:opacity-60">
                                <i data-lucide="save" class="me-1.5 size-4"></i>
                                <span x-text="guardandoAlmacen ? 'Guardando...' : 'Crear almacén'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL: NUEVO PROVEEDOR --}}
        <div x-show="modalProveedor" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1050" role="dialog"
            aria-modal="true" aria-labelledby="modal-proveedor-title">
            <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="cerrarProveedor()" aria-hidden="true"></div>

            <div class="relative flex min-h-full items-start justify-center px-4 pb-8 pt-16 md:pt-20">
                <div @click.outside="cerrarProveedor()" @keydown.escape.window="cerrarProveedor()"
                    class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-zink-700">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-zink-500">
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-lg bg-sky-500/10">
                                <i data-lucide="truck" class="size-5 text-sky-600"></i>
                            </span>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-sky-600">Abastecimiento</p>
                                <h5 id="modal-proveedor-title" class="text-lg font-semibold text-slate-800 dark:text-zink-100">
                                    Nuevo proveedor
                                </h5>
                            </div>
                        </div>

                        <button type="button" @click="cerrarProveedor()"
                            class="inline-flex size-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-zink-600">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form @submit.prevent="guardarProveedor()" class="space-y-4 p-6">
                        <div>
                            <label for="nuevo-proveedor-nombre" class="mb-2 inline-block text-sm font-medium">
                                Nombre del proveedor
                            </label>
                            <input id="nuevo-proveedor-nombre" x-ref="proveedorNombre" x-model="nuevoProveedor" type="text"
                                required maxlength="120" autocomplete="off"
                                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                                placeholder="Ej. Suministros del Norte">
                            <p class="mt-1 text-xs text-slate-400">
                                Podrás completar contacto, teléfono y dirección después desde Proveedores.
                            </p>
                            <p x-show="errorProveedor" x-text="errorProveedor" x-cloak class="mt-1 text-xs font-medium text-rose-600"></p>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-zink-500">
                            <button type="button" @click="cerrarProveedor()" class="btn border-slate-200 text-slate-500">
                                Cancelar
                            </button>

                            <button type="submit" :disabled="guardandoProveedor"
                                class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600 disabled:opacity-60">
                                <i data-lucide="save" class="me-1.5 size-4"></i>
                                <span x-text="guardandoProveedor ? 'Guardando...' : 'Crear proveedor'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    @endif
@endsection

@push('scripts')
    @if ($productos->isNotEmpty())
        <script>
            window.entradaInventario = function (config) {
                return {
                    modalAlmacen: config.abrirAlmacen ?? false,
                    modalProveedor: false,
                    tieneAlmacen: config.tieneAlmacen ?? true,
                    nuevoAlmacen: '',
                    nuevoProveedor: '',
                    errorAlmacen: '',
                    errorProveedor: '',
                    guardandoAlmacen: false,
                    guardandoProveedor: false,

                    cabeceras() {
                        return {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        };
                    },

                    async guardarAlmacen() {
                        const nombre = this.nuevoAlmacen.trim();

                        if (nombre.length < 2) {
                            this.errorAlmacen = 'Escribe un nombre de al menos 2 caracteres.';
                            return;
                        }

                        this.guardandoAlmacen = true;
                        this.errorAlmacen = '';

                        try {
                            const respuesta = await fetch(config.almacenUrl, {
                                method: 'POST',
                                headers: this.cabeceras(),
                                body: JSON.stringify({ nombre }),
                            });

                            const datos = await respuesta.json();

                            if (!respuesta.ok) {
                                this.errorAlmacen = datos.errors?.nombre?.[0]
                                    ?? datos.message
                                    ?? 'No se pudo crear el almacén.';
                                return;
                            }

                            const select = document.getElementById('almacen_id');
                            const opcion = new Option(datos.nombre, datos.id, true, true);

                            select.add(opcion);
                            select.value = String(datos.id);

                            this.tieneAlmacen = true;
                            this.nuevoAlmacen = '';
                            this.modalAlmacen = false;
                        } catch (error) {
                            this.errorAlmacen = 'No se pudo conectar con el servidor. Inténtalo nuevamente.';
                        } finally {
                            this.guardandoAlmacen = false;
                        }
                    },

                    async guardarProveedor() {
                        const nombre = this.nuevoProveedor.trim();

                        if (nombre.length < 2) {
                            this.errorProveedor = 'Escribe un nombre de al menos 2 caracteres.';
                            return;
                        }

                        this.guardandoProveedor = true;
                        this.errorProveedor = '';

                        try {
                            const respuesta = await fetch(config.proveedorUrl, {
                                method: 'POST',
                                headers: this.cabeceras(),
                                body: JSON.stringify({ nombre }),
                            });

                            const datos = await respuesta.json();

                            if (!respuesta.ok) {
                                this.errorProveedor = datos.errors?.nombre?.[0]
                                    ?? datos.message
                                    ?? 'No se pudo crear el proveedor.';
                                return;
                            }

                            const select = document.getElementById('proveedor_id');
                            const opcion = new Option(datos.nombre, datos.id, true, true);

                            select.add(opcion);
                            select.value = String(datos.id);

                            this.nuevoProveedor = '';
                            this.modalProveedor = false;
                        } catch (error) {
                            this.errorProveedor = 'No se pudo conectar con el servidor. Inténtalo nuevamente.';
                        } finally {
                            this.guardandoProveedor = false;
                        }
                    },

                    cerrarAlmacen() {
                        this.modalAlmacen = false;
                        this.errorAlmacen = '';
                    },

                    cerrarProveedor() {
                        this.modalProveedor = false;
                        this.errorProveedor = '';
                    },
                };
            };

            document.addEventListener('DOMContentLoaded', function () {
                const productos = @json($productosJson);
                const lineasIniciales = @json(array_values($lineasIniciales));

                const tbody = document.getElementById('lineas-body');
                const template = document.getElementById('linea-template');
                const addButton = document.getElementById('add-line');
                const totalUnidades = document.getElementById('total-unidades');
                const totalValor = document.getElementById('total-valor');

                if (!tbody || !template) {
                    return;
                }

                const productMap = new Map(productos.map((producto) => [String(producto.id), producto]));
                const moneyFormat = new Intl.NumberFormat('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
                const integerFormat = new Intl.NumberFormat('es-ES', {
                    maximumFractionDigits: 0,
                });

                const money = (value) => '$' + moneyFormat.format(Number(value) || 0);
                let index = 0;

                function crearFila(valores) {
                    const fragment = template.content.cloneNode(true);
                    const row = fragment.querySelector('tr');
                    const select = row.querySelector('[data-producto]');
                    const precio = row.querySelector('[data-precio]');
                    const minimo = row.querySelector('[data-minimo]');
                    const cantidad = row.querySelector('[data-cantidad]');

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
                        select.appendChild(option);
                    });

                    tbody.appendChild(row);

                    const fila = tbody.lastElementChild;

                    if (valores.producto_id) {
                        select.value = String(valores.producto_id);

                        const producto = productMap.get(String(valores.producto_id));

                        if (producto) {
                            precio.value = valores.precio_compra !== undefined && valores.precio_compra !== ''
                                ? valores.precio_compra
                                : Number(producto.precio_compra || 0).toFixed(2);
                            minimo.value = valores.stock_minimo !== undefined && valores.stock_minimo !== ''
                                ? valores.stock_minimo
                                : (producto.stock_minimo ?? 0);
                            fila.dataset.ultimoProducto = String(valores.producto_id);
                        }
                    }

                    cantidad.value = valores.cantidad ?? 1;

                    index++;
                    actualizarFila(fila);
                    actualizarTotales();
                }

                function actualizarFila(row) {
                    const select = row.querySelector('[data-producto]');
                    const precioInput = row.querySelector('[data-precio]');
                    const minimoInput = row.querySelector('[data-minimo]');
                    const cantidadInput = row.querySelector('[data-cantidad]');
                    const hint = row.querySelector('[data-producto-hint]');
                    const productoId = select.value;
                    const producto = productMap.get(productoId);
                    const cantidad = Math.max(0, Number(cantidadInput.value) || 0);

                    if (!productoId) {
                        row.dataset.ultimoProducto = '';
                        hint.textContent = '';
                    }

                    if (producto && row.dataset.ultimoProducto !== productoId) {
                        precioInput.value = Number(producto.precio_compra || 0).toFixed(2);
                        minimoInput.value = producto.stock_minimo ?? 0;
                        row.dataset.ultimoProducto = productoId;
                    }

                    if (producto) {
                        const stockMinimo = Number(minimoInput.value) || 0;
                        hint.textContent = 'Mínimo configurado: ' + integerFormat.format(stockMinimo)
                            + (producto.abreviatura ? ' ' + producto.abreviatura : '');
                    }

                    const precio = Math.max(0, Number(precioInput.value) || 0);
                    row.querySelector('[data-subtotal]').textContent = money(cantidad * precio);
                }

                function actualizarTotales() {
                    let unidades = 0;
                    let valor = 0;

                    tbody.querySelectorAll('tr').forEach((row) => {
                        const cantidad = Math.max(0, Number(row.querySelector('[data-cantidad]').value) || 0);
                        const precio = Math.max(0, Number(row.querySelector('[data-precio]').value) || 0);

                        unidades += cantidad;
                        valor += cantidad * precio;
                    });

                    totalUnidades.textContent = integerFormat.format(unidades);
                    totalValor.textContent = money(valor);
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
                        row.querySelector('[data-precio]').value = '';
                        row.querySelector('[data-minimo]').value = '';
                        row.querySelector('[data-cantidad]').value = 1;
                        row.querySelector('[data-subtotal]').textContent = '$0,00';
                        row.dataset.ultimoProducto = '';
                        actualizarFila(row);
                    } else {
                        row.remove();
                    }

                    actualizarTotales();
                });

                addButton?.addEventListener('click', function () {
                    crearFila({ producto_id: '', precio_compra: '', stock_minimo: '', cantidad: 1 });
                });

                lineasIniciales.forEach((linea) => {
                    crearFila(linea);
                });

                if (tbody.querySelectorAll('tr').length === 0) {
                    crearFila({ producto_id: '', precio_compra: '', stock_minimo: '', cantidad: 1 });
                }

                actualizarTotales();
            });
        </script>
    @endif
@endpush
