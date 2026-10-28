@extends('layouts.main')

@section('titulo', 'Servicios')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'briefcase',
        'title' => 'Servicios',
        'description' => 'Catálogo de servicios reutilizables en las ofertas',
        'accent' => 'blue',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Servicios'],
        ],
    ])

    <div class="nova-table-card card overflow-hidden bg-white dark:bg-zink-700">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zink-500">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">Servicios</span>
                <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700">
                    {{ $servicios->total() }}
                </span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <x-table-search :action="route('servicios.index')" :search="$search"
                    placeholder="Buscar servicio..." id="servicio-search" />

                <a href="{{ route('servicios.create') }}"
                    class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                    Nuevo servicio
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="nova-table-scroll w-full">
                <table class="nova-table w-full">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Servicio</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Unidad de medida</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Precio unitario</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Estado</th>
                            <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($servicios as $servicio)
                            <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                                            <i data-lucide="briefcase" class="size-4"></i>
                                        </span>
                                        <span class="font-medium text-slate-700 dark:text-zink-100">{{ $servicio->nombre }}</span>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $servicio->unidadMedida?->nombre ?? '—' }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                    <x-money :amount="$servicio->precio_unitario" />
                                </td>

                                <td class="px-3 py-3">
                                    <x-status-badge :status="$servicio->activo ? 'active' : 'inactive'" :dot="false" />
                                </td>

                                <td class="px-2 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('servicios.edit', $servicio) }}" title="Editar servicio"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-600 hover:bg-sky-100">
                                            <i data-lucide="pencil" class="size-4"></i>
                                        </a>

                                        <form method="POST" action="{{ route('servicios.destroy', $servicio) }}"
                                            onsubmit="return confirm('¿Eliminar este servicio del catálogo?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" title="Eliminar servicio"
                                                class="inline-flex size-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100">
                                                <i data-lucide="trash-2" class="size-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-table-empty icon="briefcase"
                                        :title="$search !== '' ? 'Sin resultados' : 'No hay servicios registrados'"
                                        :message="$search !== ''
                                            ? 'No encontramos coincidencias para la búsqueda.'
                                            : 'Crea un servicio para reutilizarlo en las ofertas.'">
                                        <a href="{{ route('servicios.create') }}"
                                            class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                            Nuevo servicio
                                        </a>
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$servicios" label="servicios" />
        </div>
    </div>
@endsection
