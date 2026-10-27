@extends('layouts.main')

@section('titulo', $almacen->exists ? 'Editar almacén' : 'Nuevo almacén')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'building-2',
        'title' => $almacen->exists ? 'Editar almacén' : 'Nuevo almacén',
        'description' => 'Datos de la ubicación física del inventario',
        'accent' => 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Almacenes', 'url' => route('almacenes.index')],
            ['label' => $almacen->exists ? $almacen->nombre : 'Nuevo'],
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
        action="{{ $almacen->exists ? route('almacenes.update', $almacen) : route('almacenes.store') }}"
        class="mx-auto max-w-2xl">
        @csrf
        @if ($almacen->exists)
            @method('PUT')
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body space-y-5">
                <div>
                    <label for="nombre" class="mb-2 inline-block text-sm font-medium">Nombre</label>
                    <input id="nombre" name="nombre" type="text" required maxlength="80"
                        value="{{ old('nombre', $almacen->nombre) }}"
                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                        placeholder="Ej. Almacén principal">
                </div>

                <div>
                    <label for="direccion" class="mb-2 inline-block text-sm font-medium">Dirección</label>
                    <input id="direccion" name="direccion" type="text" maxlength="180"
                        value="{{ old('direccion', $almacen->direccion) }}"
                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                </div>

                <div>
                    <label for="telefono" class="mb-2 inline-block text-sm font-medium">Teléfono</label>
                    <input id="telefono" name="telefono" type="text" maxlength="20"
                        value="{{ old('telefono', $almacen->telefono) }}"
                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1"
                        class="size-4 rounded border-slate-300 text-nova-600 focus:ring-nova-500"
                        @checked((bool) old('activo', $almacen->activo ?? true))>
                    Almacén activo
                </label>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4 dark:border-zink-500">
                <a href="{{ route('almacenes.index') }}" class="btn border-slate-200 text-slate-600">Cancelar</a>
                <button type="submit" class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="save" class="me-1.5 size-4"></i>
                    {{ $almacen->exists ? 'Guardar cambios' : 'Crear almacén' }}
                </button>
            </div>
        </div>
    </form>
@endsection
