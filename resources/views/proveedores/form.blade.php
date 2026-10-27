@extends('layouts.main')

@section('titulo', $proveedor->exists ? 'Editar proveedor' : 'Nuevo proveedor')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => $proveedor->exists ? 'truck' : 'truck',
        'title' => $proveedor->exists ? 'Editar proveedor' : 'Nuevo proveedor',
        'description' => $proveedor->exists
            ? 'Completa los datos de contacto y facturación'
            : 'Puedes crearlo solo con el nombre y completar el resto después',
        'accent' => 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Proveedores', 'url' => route('proveedores.index')],
            ['label' => $proveedor->exists ? $proveedor->nombre : 'Nuevo'],
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
        action="{{ $proveedor->exists ? route('proveedores.update', $proveedor) : route('proveedores.store') }}"
        class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        @csrf
        @if ($proveedor->exists)
            @method('PUT')
        @endif

        <div class="xl:col-span-2">
            <div class="card border-0 shadow-sm">
                <div class="nova-card-header">
                    <div>
                        <h2 class="nova-card-title">Datos del proveedor</h2>
                        <p class="nova-card-subtitle">Identificación y datos de contacto</p>
                    </div>
                </div>

                <div class="card-body grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="nombre" class="mb-2 inline-block text-sm font-medium">Nombre o razón social</label>
                        <input id="nombre" name="nombre" type="text" required maxlength="120"
                            value="{{ old('nombre', $proveedor->nombre) }}"
                            class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Ej. Suministros Industriales S.A.">
                    </div>

                    <div>
                        <label for="contacto" class="mb-2 inline-block text-sm font-medium">Persona de contacto</label>
                        <input id="contacto" name="contacto" type="text" maxlength="120"
                            value="{{ old('contacto', $proveedor->contacto) }}"
                            class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                    </div>

                    <div>
                        <label for="telefono" class="mb-2 inline-block text-sm font-medium">Teléfono</label>
                        <input id="telefono" name="telefono" type="text" maxlength="20"
                            value="{{ old('telefono', $proveedor->telefono) }}"
                            class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Ej. 5551234567">
                    </div>

                    <div>
                        <label for="email" class="mb-2 inline-block text-sm font-medium">Correo electrónico</label>
                        <input id="email" name="email" type="email" maxlength="120"
                            value="{{ old('email', $proveedor->email) }}"
                            class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                    </div>

                    <div>
                        <label for="identificacion_fiscal" class="mb-2 inline-block text-sm font-medium">Identificación fiscal</label>
                        <input id="identificacion_fiscal" name="identificacion_fiscal" type="text" maxlength="30"
                            value="{{ old('identificacion_fiscal', $proveedor->identificacion_fiscal) }}"
                            class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="RIF, NIT, CIF...">
                    </div>

                    <div class="md:col-span-2">
                        <label for="direccion" class="mb-2 inline-block text-sm font-medium">Dirección</label>
                        <input id="direccion" name="direccion" type="text" maxlength="180"
                            value="{{ old('direccion', $proveedor->direccion) }}"
                            class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                    </div>

                    <div class="md:col-span-2">
                        <label for="notas" class="mb-2 inline-block text-sm font-medium">Notas</label>
                        <textarea id="notas" name="notas" rows="3" maxlength="1000"
                            class="form-input w-full resize-none border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Condiciones de pago, horarios, observaciones...">{{ old('notas', $proveedor->notas) }}</textarea>
                    </div>

                    <div>
                        <label class="flex cursor-pointer items-center gap-2 text-sm">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1"
                                class="size-4 rounded border-slate-300 text-nova-600 focus:ring-nova-500"
                                @checked((bool) old('activo', $proveedor->activo ?? true))>
                            Proveedor activo
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-slate-800">Resumen</h3>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Estado</dt>
                            <dd><x-status-badge :status="old('activo', $proveedor->activo ?? true) ? 'active' : 'inactive'" /></dd>
                        </div>
                        @if ($proveedor->exists)
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">Compras</dt>
                                <dd class="font-semibold text-slate-700">{{ $proveedor->operaciones()->count() }}</dd>
                            </div>
                        @endif
                    </dl>

                    <p class="mt-4 text-xs leading-5 text-slate-400">
                        El nombre debe ser único. Si el proveedor ya existe, reutilízalo desde una entrada
                        en lugar de crearlo de nuevo.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body space-y-2">
                    <button type="submit"
                        class="btn inline-flex w-full items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                        <i data-lucide="save" class="me-1.5 size-4"></i>
                        {{ $proveedor->exists ? 'Guardar cambios' : 'Crear proveedor' }}
                    </button>

                    <a href="{{ route('proveedores.index') }}"
                        class="btn inline-flex w-full items-center justify-center border-slate-200 text-slate-600">
                        Cancelar
                    </a>
                </div>
            </div>
        </aside>
    </form>
@endsection
