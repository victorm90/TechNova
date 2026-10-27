@extends('layouts.main')

@section('titulo', $titulo)

@php($search = $search ?? '')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => match ($tipo) {
            'unidades' => 'ruler',
            'servicios' => 'briefcase',
            default => 'map',
        },
        'title' => $titulo,
        'description' => $descripcion,
        'accent' => in_array($tipo, ['unidades', 'servicios'], true) ? 'blue' : 'amber',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Nomencladores'],
            ['label' => $titulo],
        ],
    ])

    <div class="nova-table-card card overflow-hidden bg-white dark:bg-zink-700" data-table-card>
        <div class="nova-progress-bar h-1 w-full"></div>

        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zink-500">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">Registros</span>
                <span aria-live="polite"
                    class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                    {{ $items->total() }}
                </span>
            </div>

            <x-table-search :action="route($ruta)" :search="$search"
                :placeholder="'Buscar '.strtolower($titulo).'...'" :id="'search-'.$tipo" />
        </div>

        <x-table-loading />

        <div class="card-body">
            <div class="nova-table-scroll w-full">
                <table class="nova-table w-full table-fixed">
                    @if ($tipo === 'provincias')
                        <colgroup>
                            <col style="width: 18%">
                            <col style="width: 52%">
                            <col style="width: 30%">
                        </colgroup>
                    @elseif ($tipo === 'municipios')
                        <colgroup>
                            <col style="width: 18%">
                            <col style="width: 42%">
                            <col style="width: 40%">
                        </colgroup>
                    @elseif ($tipo === 'unidades')
                        <colgroup>
                            <col style="width: 25%">
                            <col style="width: 50%">
                            <col style="width: 25%">
                        </colgroup>
                    @else
                        <colgroup>
                            <col style="width: 12%">
                            <col style="width: 38%">
                            <col style="width: 25%">
                            <col style="width: 25%">
                        </colgroup>
                    @endif

                    <thead>
                        <tr>
                            <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                                Código
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                                Nombre
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                                {{ $tipo === 'municipios' ? 'Provincia' : ($tipo === 'unidades' ? 'Abreviatura' : ($tipo === 'servicios' ? 'Unidad de medida' : 'Identificador')) }}
                            </th>
                            @if ($tipo === 'servicios')
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                                    Estado
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="px-3 py-3">
                                    <span class="text-sm text-slate-500 dark:text-zink-300">
                                        {{ in_array($tipo, ['unidades', 'servicios'], true) ? $item->id : ($item->codigo ?: $item->id) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-100 to-sky-100 font-semibold text-nova-700">
                                            {{ strtoupper(mb_substr($item->nombre, 0, 1)) }}
                                        </div>
                                        <span class="truncate font-medium text-slate-700 dark:text-zink-100" title="{{ $item->nombre }}">
                                            {{ $item->nombre }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($tipo === 'municipios')
                                        <span class="text-sm text-slate-600 dark:text-zink-200">
                                            {{ $item->provincia->nombre }}
                                        </span>
                                    @elseif ($tipo === 'unidades')
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 dark:bg-zink-600 dark:text-zink-200">
                                            {{ $item->abreviatura ?: 'Sin abreviatura' }}
                                        </span>
                                    @elseif ($tipo === 'servicios')
                                        <span class="text-sm text-slate-600 dark:text-zink-200">
                                            {{ $item->unidadMedida->nombre }}
                                            @if ($item->unidadMedida->abreviatura)
                                                ({{ $item->unidadMedida->abreviatura }})
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-sm text-slate-400 dark:text-zink-300">
                                            Provincia registrada
                                        </span>
                                    @endif
                                </td>
                                @if ($tipo === 'servicios')
                                    <td class="px-3 py-3">
                                        <x-status-badge :status="$item->activo ? 'active' : 'inactive'" />
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $tipo === 'servicios' ? 4 : 3 }}">
                                    <x-table-empty icon="inbox"
                                        :title="$search !== '' ? 'Sin resultados' : 'No hay registros para mostrar'"
                                        :message="$search !== '' ? 'No encontramos coincidencias para “'.$search.'”.' : 'Los registros aparecerán aquí cuando estén disponibles.'">
                                        @if ($search !== '')
                                            <a href="{{ route($ruta) }}"
                                                class="text-sm font-medium text-nova-600 hover:text-nova-700">
                                                Limpiar búsqueda
                                            </a>
                                        @endif
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->total() > 0)
                <x-table-pagination :paginator="$items" label="registros" />
            @endif
        </div>
    </div>
@endsection
