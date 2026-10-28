@extends('layouts.main')

@section('titulo', 'Ofertas')

@section('contenido')
    @include('layouts.page-header', [
        'icon' => 'file-text',
        'title' => 'Ofertas',
        'description' => 'Servicios y gastos agrupados para cada cliente',
        'accent' => 'green',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Ofertas'],
        ],
    ])

    <div class="nova-table-card card overflow-hidden bg-white dark:bg-zink-700">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 xl:flex-row xl:items-center xl:justify-between dark:border-zink-500">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-zink-100">Ofertas</span>
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                    {{ $ofertas->total() }}
                </span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('ofertas.index') }}" role="search" data-table-search
                    class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative w-64 max-w-full">
                        <label for="oferta-search" class="sr-only">Buscar oferta</label>
                        <input id="oferta-search" type="search" name="search" value="{{ $search }}"
                            class="form-input nova-input-icon w-full border-slate-200 pe-9 focus:border-nova-500 focus:ring-0 dark:border-zink-500 dark:bg-zink-700"
                            placeholder="Buscar por código o cliente..." autocomplete="off">
                        <i data-lucide="search"
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"></i>
                    </div>

                    <label for="oferta-estado" class="sr-only">Estado</label>
                    <select id="oferta-estado" name="estado" onchange="this.form.submit()"
                        class="form-input border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                        <option value="">Todos los estados</option>
                        @foreach (\App\Models\Oferta::estados() as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected($estado === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="btn border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-zink-500 dark:text-zink-200 dark:hover:bg-zink-600">
                        Filtrar
                    </button>

                    @if ($search !== '' || $estado !== '')
                        <a href="{{ route('ofertas.index') }}" class="btn border-slate-200 text-slate-500">
                            Limpiar
                        </a>
                    @endif
                </form>

                <a href="{{ route('ofertas.create') }}"
                    class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                    Nueva oferta
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="nova-table-scroll w-full">
                <table class="nova-table w-full">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Oferta</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Cliente</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Fecha</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Líneas</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Total</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Estado</th>
                            <th class="px-2 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ofertas as $oferta)
                            <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                            <i data-lucide="file-text" class="size-4"></i>
                                        </span>
                                        <div>
                                            <a href="{{ route('ofertas.show', $oferta) }}"
                                                class="font-semibold text-slate-700 hover:text-nova-700 dark:text-zink-100 dark:hover:text-nova-300">
                                                {{ $oferta->codigo }}
                                            </a>
                                            <p class="text-xs text-slate-400">#{{ $oferta->id }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $oferta->customer?->name ?? '—' }}
                                </td>

                                <td class="px-3 py-3 text-sm text-slate-600 dark:text-zink-200">
                                    {{ $oferta->fecha?->format('d/m/Y') }}
                                </td>

                                <td class="px-3 py-3 text-right text-sm is-numeric text-slate-600 dark:text-zink-200">
                                    <span title="Servicios">{{ (int) $oferta->servicios_count }} serv.</span>
                                    <span class="text-slate-300">/</span>
                                    <span title="Gastos">{{ (int) $oferta->gastos_count }} gast.</span>
                                </td>

                                <td class="px-3 py-3 text-right text-sm font-semibold is-numeric text-slate-700 dark:text-zink-100">
                                    <x-money :amount="$oferta->total" />
                                </td>

                                <td class="px-3 py-3">
                                    <x-status-badge :status="$oferta->estadoBadge()" :label="$oferta->estadoLabel()" :dot="false" />
                                </td>

                                <td class="px-2 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('ofertas.show', $oferta) }}" title="Ver oferta"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100">
                                            <i data-lucide="eye" class="size-4"></i>
                                        </a>

                                        <a href="{{ route('ofertas.edit', $oferta) }}" title="Editar oferta"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-600 hover:bg-sky-100">
                                            <i data-lucide="pencil" class="size-4"></i>
                                        </a>

                                        <form method="POST" action="{{ route('ofertas.destroy', $oferta) }}"
                                            onsubmit="return confirm('¿Eliminar la oferta {{ $oferta->codigo }}?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" title="Eliminar oferta"
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
                                    <x-table-empty icon="file-text"
                                        :title="$search !== '' || $estado !== '' ? 'Sin resultados' : 'No hay ofertas registradas'"
                                        :message="$search !== '' || $estado !== ''
                                            ? 'No encontramos ofertas con los filtros seleccionados.'
                                            : 'Crea una oferta y añade servicios y gastos del catálogo.'">
                                        <a href="{{ route('ofertas.create') }}"
                                            class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                            Nueva oferta
                                        </a>
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$ofertas" label="ofertas" />
        </div>
    </div>
@endsection
