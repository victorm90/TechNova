@extends('layouts.main')

@section('titulo', 'Nuevo ajuste de inventario')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'sliders-horizontal',
        'title' => 'Nuevo ajuste de inventario',
        'description' => 'Corrige la cantidad en stock de un producto existente',
        'accent' => 'blue',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => 'Ajustes', 'url' => route('ajustes.index')],
            ['label' => 'Nuevo'],
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

    @if ($inventarios->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <x-table-empty icon="warehouse"
                    title="No hay existencias registradas"
                    message="Primero registra una entrada para que el producto exista en un almacén.">
                    @can('registrar-movimientos')
                        <a href="{{ route('entradas.create') }}"
                            class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                            Registrar entrada
                        </a>
                    @endcan
                </x-table-empty>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('ajustes.store') }}" class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            @csrf

            <div class="xl:col-span-2">
                <div class="card border-0 shadow-sm">
                    <div class="nova-card-header">
                        <div>
                            <h2 class="nova-card-title">Datos del ajuste</h2>
                            <p class="nova-card-subtitle">El código se genera automáticamente al guardar</p>
                        </div>
                    </div>

                    <div class="card-body grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="inventario_id" class="mb-2 inline-block text-sm font-medium">
                                Producto y almacén
                            </label>
                            <select id="inventario_id" name="inventario_id" required
                                class="form-select w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                                <option value="">Selecciona una existencia</option>
                                @foreach ($inventarios as $inventario)
                                    <option value="{{ $inventario->id }}"
                                        data-stock="{{ (int) $inventario->cantidad }}"
                                        data-unidad="{{ $inventario->producto?->unidadMedida?->abreviatura ?? 'unidades' }}"
                                        @selected((int) old('inventario_id', $inventarioId) === $inventario->id)>
                                        {{ $inventario->producto?->nombre }}
                                        ({{ $inventario->producto?->sku }})
                                        — {{ $inventario->almacen?->nombre }}
                                        · Stock actual: {{ number_format((int) $inventario->cantidad, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-400">
                                Solo se listan productos que ya tienen existencia registrada en un almacén.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                <span class="font-semibold text-slate-700">Stock actual:</span>
                                <span id="stock-actual" class="is-numeric">—</span>
                                <span id="stock-unidad"></span>
                            </div>
                        </div>

                        <div>
                            <label for="cantidad" class="mb-2 inline-block text-sm font-medium">
                                Nueva cantidad en stock
                            </label>
                            <input id="cantidad" name="cantidad" type="number" step="1" min="0" required
                                value="{{ old('cantidad') }}"
                                class="form-input w-full text-right is-numeric border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                                placeholder="0">
                            <p class="mt-1 text-xs text-slate-400">
                                Escribe la cantidad real contada. Debe ser un número entero.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    <div class="flex items-start gap-2">
                        <i data-lucide="triangle-alert" class="mt-0.5 size-4 shrink-0"></i>
                        <p class="leading-6">
                            Un ajuste solo cambia la cantidad en stock. No crea productos ni modifica
                            el precio. Si el precio cambió, registra una nueva entrada.
                        </p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body space-y-2">
                        <button type="submit"
                            class="btn inline-flex w-full items-center justify-center border-sky-500 bg-sky-500 text-white hover:bg-sky-600">
                            <i data-lucide="save" class="me-1.5 size-4"></i>
                            Registrar ajuste
                        </button>

                        <a href="{{ route('ajustes.index') }}"
                            class="btn inline-flex w-full items-center justify-center border-slate-200 text-slate-600">
                            Cancelar
                        </a>
                    </div>
                </div>
            </aside>
        </form>
    @endif
@endsection

@push('scripts')
    @if ($inventarios->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const select = document.getElementById('inventario_id');
                const stockActual = document.getElementById('stock-actual');
                const stockUnidad = document.getElementById('stock-unidad');

                if (!select || !stockActual || !stockUnidad) {
                    return;
                }

                const integerFormat = new Intl.NumberFormat('es-ES', {
                    maximumFractionDigits: 0,
                });

                function actualizarStock() {
                    const option = select.options[select.selectedIndex];

                    if (!select.value) {
                        stockActual.textContent = '—';
                        stockUnidad.textContent = '';
                        return;
                    }

                    stockActual.textContent = integerFormat.format(Number(option.dataset.stock || 0));
                    stockUnidad.textContent = option.dataset.unidad ? ' ' + option.dataset.unidad : '';
                }

                select.addEventListener('change', actualizarStock);
                actualizarStock();
            });
        </script>
    @endif
@endpush
