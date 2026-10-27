@extends('layouts.main')

@section('titulo', 'Almacenes')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'building-2',
        'title' => 'Almacenes',
        'description' => 'Ubicaciones físicas donde se controla el inventario',
        'accent' => 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Almacenes'],
        ],
    ])

    <div class="nova-table-card card overflow-hidden bg-white dark:bg-zink-700">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zink-500">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">Almacenes</span>
                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                    {{ $almacenes->total() }}
                </span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <x-table-search :action="route('almacenes.index')" :search="$search"
                    placeholder="Buscar almacén..." id="almacen-search" />

                <a href="{{ route('almacenes.create') }}"
                    class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                    Nuevo almacén
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="nova-table-scroll w-full">
                <table class="nova-table w-full">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Almacén</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Dirección</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Teléfono</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Productos</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Unidades</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Estado</th>
                            <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($almacenes as $almacen)
                            <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                                            <i data-lucide="warehouse" class="size-4"></i>
                                        </span>
                                        <span class="font-medium text-slate-700 dark:text-zink-100">{{ $almacen->nombre }}</span>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">{{ $almacen->direccion ?: '—' }}</td>
                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">{{ $almacen->telefono ?: '—' }}</td>

                                <td class="px-3 py-3 text-right text-sm text-slate-600 is-numeric">
                                    {{ number_format((int) $almacen->inventarios_count, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-semibold text-slate-700 is-numeric dark:text-zink-100">
                                    {{ number_format((int) ($almacen->stock_total ?? 0), 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-3">
                                    <x-status-badge :status="$almacen->activo ? 'active' : 'inactive'" :dot="false" />
                                </td>

                                <td class="px-2 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('almacenes.edit', $almacen) }}" title="Editar almacén"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-600 hover:bg-sky-100">
                                            <i data-lucide="pencil" class="size-4"></i>
                                        </a>

                                        <form method="POST" action="{{ route('almacenes.destroy', $almacen) }}"
                                            onsubmit="return confirm('¿Eliminar el almacén {{ $almacen->nombre }}?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" title="Eliminar almacén"
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
                                    <x-table-empty icon="building-2"
                                        :title="$search !== '' ? 'Sin resultados' : 'No hay almacenes registrados'"
                                        :message="$search !== ''
                                            ? 'No encontramos coincidencias para la búsqueda.'
                                            : 'Crea un almacén para asignar existencias a tus productos.'">
                                        <a href="{{ route('almacenes.create') }}"
                                            class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                            Nuevo almacén
                                        </a>
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$almacenes" label="almacenes" />
        </div>
    </div>
@endsection
