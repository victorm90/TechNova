@extends('layouts.main')

@section('titulo', 'Proveedores')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'truck',
        'title' => 'Proveedores',
        'description' => 'Directorio de proveedores y compras asociadas',
        'accent' => 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Proveedores'],
        ],
    ])

    <div class="nova-table-card card overflow-hidden bg-white dark:bg-zink-700">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zink-500">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">Proveedores</span>
                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                    {{ $proveedores->total() }}
                </span>
                <span class="text-xs text-slate-400">
                    {{ number_format($resumen['activos'], 0, ',', '.') }} activos ·
                    {{ number_format($resumen['con_compras'], 0, ',', '.') }} con compras
                </span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <x-table-search :action="route('proveedores.index')" :search="$search"
                    placeholder="Buscar proveedor..." id="proveedor-search" />

                <a href="{{ route('proveedores.create') }}"
                    class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                    Nuevo proveedor
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="nova-table-scroll w-full">
                <table class="nova-table w-full">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Proveedor</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Contacto</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Teléfono</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Identificación fiscal</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Compras</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Estado</th>
                            <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($proveedores as $proveedor)
                            <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-50 text-amber-700">
                                            <i data-lucide="truck" class="size-4"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-slate-700 dark:text-zink-100" title="{{ $proveedor->nombre }}">
                                                {{ $proveedor->nombre }}
                                            </p>
                                            <p class="text-xs text-slate-400">Proveedor #{{ $proveedor->id }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    <p>{{ $proveedor->contacto ?: '—' }}</p>
                                    @if ($proveedor->email)
                                        <p class="text-xs text-slate-400">{{ $proveedor->email }}</p>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $proveedor->telefono ?: '—' }}
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $proveedor->identificacion_fiscal ?: '—' }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                    {{ number_format((int) $proveedor->operaciones_count, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-3">
                                    <x-status-badge :status="$proveedor->activo ? 'active' : 'inactive'" :dot="false" />
                                </td>

                                <td class="px-2 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('proveedores.edit', $proveedor) }}" title="Editar proveedor"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-600 hover:bg-sky-100 dark:border-sky-900/50 dark:bg-sky-900/20">
                                            <i data-lucide="pencil" class="size-4"></i>
                                        </a>

                                        <form method="POST" action="{{ route('proveedores.destroy', $proveedor) }}"
                                            onsubmit="return confirm('¿Eliminar el proveedor {{ $proveedor->nombre }}?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" title="Eliminar proveedor"
                                                class="inline-flex size-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100">
                                                <i data-lucide="trash-2" class="size-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-table-empty icon="truck"
                                        :title="$search !== '' ? 'Sin resultados' : 'No hay proveedores registrados'"
                                        :message="$search !== ''
                                            ? 'No encontramos coincidencias para la búsqueda.'
                                            : 'Crea un proveedor o regístralo directamente desde una entrada de inventario.'">
                                        <a href="{{ route('proveedores.create') }}"
                                            class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                            <i data-lucide="plus" class="me-1.5 size-4"></i>
                                            Nuevo proveedor
                                        </a>
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$proveedores" label="proveedores" />
        </div>
    </div>
@endsection
