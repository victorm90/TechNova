@extends('layouts.main')

@section('titulo', $gasto->exists ? 'Editar gasto' : 'Nuevo gasto')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'receipt',
        'title' => $gasto->exists ? 'Editar gasto' : 'Nuevo gasto',
        'description' => 'Datos base que se reutilizarán al crear una oferta',
        'accent' => 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Gastos', 'url' => route('gastos.index')],
            ['label' => $gasto->exists ? $gasto->nombre : 'Nuevo'],
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
        action="{{ $gasto->exists ? route('gastos.update', $gasto) : route('gastos.store') }}"
        class="mx-auto max-w-2xl">
        @csrf
        @if ($gasto->exists)
            @method('PUT')
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body space-y-5">
                <div>
                    <label for="nombre" class="mb-2 inline-block text-sm font-medium">Nombre del gasto</label>
                    <input id="nombre" name="nombre" type="text" required maxlength="255"
                        value="{{ old('nombre', $gasto->nombre) }}"
                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                        placeholder="Ej. Transporte del equipamiento">
                </div>

                <div>
                    <label for="precio_unitario" class="mb-2 inline-block text-sm font-medium">Precio unitario</label>
                    <input id="precio_unitario" name="precio_unitario" type="number" required min="0" step="0.01"
                        value="{{ old('precio_unitario', $gasto->precio_unitario) }}"
                        class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700"
                        placeholder="0.00">
                    <p class="mt-1 text-xs text-slate-400">Precio base sugerido; puede ajustarse en cada oferta.</p>
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1"
                        class="size-4 rounded border-slate-300 text-nova-600 focus:ring-nova-500"
                        @checked((bool) old('activo', $gasto->activo ?? true))>
                    Gasto activo
                </label>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4 dark:border-zink-500">
                <a href="{{ route('gastos.index') }}" class="btn border-slate-200 text-slate-600">Cancelar</a>
                <button type="submit" class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="save" class="me-1.5 size-4"></i>
                    {{ $gasto->exists ? 'Guardar cambios' : 'Crear gasto' }}
                </button>
            </div>
        </div>
    </form>
@endsection
