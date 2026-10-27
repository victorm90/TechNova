@extends('layouts.main')

@section('titulo', $producto->exists ? 'Editar producto' : 'Nuevo producto')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => $producto->exists ? 'package-search' : 'package-plus',
        'title' => $producto->exists ? 'Editar producto' : 'Nuevo producto',
        'description' => $producto->exists
            ? 'Actualiza los datos maestros sin alterar existencias ni precios'
            : 'El SKU se genera automáticamente al guardar',
        'accent' => 'green',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Productos', 'url' => route('productos.index')],
            ['label' => $producto->exists ? $producto->sku : 'Nuevo'],
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
        action="{{ $producto->exists ? route('productos.update', $producto) : route('productos.store') }}"
        enctype="multipart/form-data"
        class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        @csrf
        @if ($producto->exists)
            @method('PUT')
        @endif

        <div class="xl:col-span-2">
            <div class="card border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Datos del producto</h2>
                        <p class="nova-card-subtitle">Identificación, clasificación e imagen</p>
                    </div>

                    @if ($producto->exists)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                            {{ $producto->sku }}
                        </span>
                    @endif
                </div>

                <div class="card-body grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="nombre" class="mb-2 inline-block text-sm font-medium">Nombre del producto</label>
                        <input id="nombre" name="nombre" type="text" required maxlength="120"
                            value="{{ old('nombre', $producto->nombre) }}"
                            class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Ej. Caja corrugada 40×30">
                    </div>

                    <div>
                        <label for="categoria_producto_id" class="mb-2 inline-block text-sm font-medium">Categoría</label>
                        <select id="categoria_producto_id" name="categoria_producto_id" required
                            class="form-select w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}"
                                    @selected((int) old('categoria_producto_id', $producto->categoria_producto_id) === $categoria->id)>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @if ($categorias->isEmpty())
                            <p class="mt-1 text-xs text-amber-600">
                                No hay categorías activas.
                                <a href="{{ route('categorias.create') }}" class="font-semibold underline">Crea una primero</a>.
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="unidad_medida_id" class="mb-2 inline-block text-sm font-medium">Unidad de medida</label>
                        <select id="unidad_medida_id" name="unidad_medida_id" required
                            class="form-select w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                            <option value="">Selecciona una unidad</option>
                            @foreach ($unidades as $unidad)
                                <option value="{{ $unidad->id }}"
                                    @selected((int) old('unidad_medida_id', $producto->unidad_medida_id) === $unidad->id)>
                                    {{ $unidad->nombre }}{{ $unidad->abreviatura ? " ({$unidad->abreviatura})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="imagen" class="mb-2 inline-block text-sm font-medium">Imagen del producto</label>

                        <div class="flex flex-col gap-4 rounded-xl border border-dashed border-slate-300 p-4 sm:flex-row sm:items-center dark:border-zink-500">
                            <x-product-image :producto="$producto" id="imagen-preview" class="size-24 rounded-2xl" />

                            <div class="min-w-0 flex-1">
                                <input id="imagen" name="imagen" type="file"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">

                                <p class="mt-1 text-xs text-slate-400">
                                    Opcional. Si no subes una imagen se usará el ícono de placeholder del sistema.
                                </p>
                                <p class="text-xs text-slate-400">JPG, PNG o WEBP · máximo 2 MB.</p>

                                @if ($producto->exists && $producto->imagen)
                                    <p class="mt-1 text-xs text-emerald-600">
                                        Ya tiene una imagen. Sube otra solo si deseas reemplazarla.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($producto->exists)
                        <div class="md:col-span-2">
                            <label class="flex cursor-pointer items-center gap-2 text-sm">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1"
                                    class="size-4 rounded border-slate-300 text-nova-600 focus:ring-nova-500"
                                    @checked((bool) old('activo', $producto->activo ?? true))>
                                Producto activo
                            </label>
                            <p class="mt-1 text-xs text-slate-400">
                                Los productos inactivos no pueden recibir entradas ni salidas.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-slate-800">Resumen</h3>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">SKU</dt>
                            <dd class="font-semibold text-slate-700">{{ $producto->exists ? $producto->sku : 'Automático' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Categoría</dt>
                            <dd class="text-right font-medium text-slate-700">
                                {{ $producto->categoriaProducto?->nombre ?? 'Por asignar' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Unidad</dt>
                            <dd class="font-medium text-slate-700">
                                {{ $producto->unidadMedida?->abreviatura ?? $producto->unidadMedida?->nombre ?? 'Por asignar' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Estado</dt>
                            <dd>
                                <x-status-badge :status="old('activo', $producto->activo ?? true) ? 'active' : 'inactive'" />
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body space-y-2">
                    <button type="submit"
                        class="btn inline-flex w-full items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                        <i data-lucide="save" class="me-1.5 size-4"></i>
                        {{ $producto->exists ? 'Guardar cambios' : 'Crear producto' }}
                    </button>

                    <a href="{{ $producto->exists ? route('productos.show', $producto) : route('productos.index') }}"
                        class="btn inline-flex w-full items-center justify-center border-slate-200 text-slate-600">
                        Cancelar
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
                <div class="flex items-start gap-2">
                    <i data-lucide="info" class="mt-0.5 size-4 shrink-0"></i>
                    <p class="leading-6">
                        El SKU es consecutivo y no editable. Los precios, el stock mínimo y las existencias
                        se registran desde una entrada de almacén.
                    </p>
                </div>
            </div>
        </aside>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('imagen');
            const preview = document.getElementById('imagen-preview');

            if (!input || !preview) {
                return;
            }

            input.addEventListener('change', function () {
                const archivo = input.files?.[0];

                if (!archivo) {
                    return;
                }

                preview.src = URL.createObjectURL(archivo);
            });
        });
    </script>
@endpush
