@extends('layouts.main')

@section('titulo', 'Gastos')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'receipt',
        'title' => 'Gastos',
        'description' => 'Catálogo de gastos reutilizables en las ofertas',
        'accent' => 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Gastos'],
        ],
    ])

    <div class="nova-table-card card overflow-hidden bg-white dark:bg-zink-700">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zink-500">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">Gastos</span>
                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                    {{ $gastos->total() }}
                </span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <x-table-search :action="route('gastos.index')" :search="$search"
                    placeholder="Buscar gasto..." id="gasto-search" />

                <a href="{{ route('gastos.create') }}"
                    class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                    Nuevo gasto
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="nova-table-scroll w-full">
                <table class="nova-table w-full">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Gasto</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Precio unitario</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Estado</th>
                            <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($gastos as $gasto)
                            <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                                            <i data-lucide="receipt" class="size-4"></i>
                                        </span>
                                        <span class="font-medium text-slate-700 dark:text-zink-100">{{ $gasto->nombre }}</span>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                    <x-money :amount="$gasto->precio_unitario" />
                                </td>

                                <td class="px-3 py-3">
                                    <x-status-badge :status="$gasto->activo ? 'active' : 'inactive'" :dot="false" />
                                </td>

                                <td class="px-2 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('gastos.edit', $gasto) }}" title="Editar gasto"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-600 hover:bg-sky-100">
                                            <i data-lucide="pencil" class="size-4"></i>
                                        </a>

                                        <form method="POST" action="{{ route('gastos.destroy', $gasto) }}"
                                            onsubmit="return confirm('¿Eliminar este gasto del catálogo?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" title="Eliminar gasto"
                                                class="inline-flex size-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100">
                                                <i data-lucide="trash-2" class="size-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-table-empty icon="receipt"
                                        :title="$search !== '' ? 'Sin resultados' : 'No hay gastos registrados'"
                                        :message="$search !== ''
                                            ? 'No encontramos coincidencias para la búsqueda.'
                                            : 'Crea un gasto para reutilizarlo en las ofertas.'">
                                        <a href="{{ route('gastos.create') }}"
                                            class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                            Nuevo gasto
                                        </a>
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$gastos" label="gastos" />
        </div>
    </div>
@endsection
