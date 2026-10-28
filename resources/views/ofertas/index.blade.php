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

    <div class="nova-table-card card overflow-hidden bg-white dark:bg-zink-700"
        x-data="{ nuevaOferta: {{ $errors->any() ? 'true' : 'false' }} }">
        <datalist id="clientes-oferta">
            @foreach ($clientes as $cliente)
                <option value="{{ $cliente->name }}"></option>
            @endforeach
        </datalist>
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

                <button type="button" @click="nuevaOferta = true"
                    class="btn inline-flex items-center justify-center border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                    <i data-lucide="plus" class="me-1.5 size-4"></i>
                    Nueva oferta
                </button>
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
                                        <button type="button" @click="nuevaOferta = true"
                                            class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                            Nueva oferta
                                        </button>
                                    </x-table-empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$ofertas" label="ofertas" />
        </div>

        {{-- Alta rápida de oferta --}}
        <div x-show="nuevaOferta" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1050" role="dialog"
            aria-modal="true" aria-labelledby="nueva-oferta-titulo">
            <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="nuevaOferta = false" aria-hidden="true"></div>

            <div class="relative flex min-h-full items-start justify-center px-4 pb-8 pt-16 md:pt-20">
                <div @click.outside="nuevaOferta = false"
                    class="w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-zink-700">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-zink-500">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-lg bg-nova-500/10">
                                <i data-lucide="file-plus" class="size-5 text-nova-500"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-nova-500">Ofertas</p>
                                <h5 id="nueva-oferta-titulo" class="text-lg font-semibold text-slate-800 dark:text-zink-100">
                                    Nueva oferta
                                </h5>
                            </div>
                        </div>

                        <button type="button" @click="nuevaOferta = false"
                            class="inline-flex size-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-zink-600">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('ofertas.storeRapida') }}" class="space-y-4 p-6">
                        @csrf

                        @if ($errors->any())
                            <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                <ul class="list-inside list-disc space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <label for="cliente_nombre" class="mb-2 inline-block text-sm font-medium">
                                Nombre del cliente <span class="text-rose-500">*</span>
                            </label>
                            <input id="cliente_nombre" name="cliente_nombre" type="text" list="clientes-oferta"
                                value="{{ old('cliente_nombre') }}" required maxlength="50" autocomplete="off"
                                placeholder="Escriba el nombre del cliente"
                                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                            <p class="mt-1 text-xs text-slate-400">
                                Si el nombre coincide con un cliente existente se reutilizará. Si es nuevo, se creará con ese nombre y podrá completar sus datos después.
                            </p>
                        </div>

                        <div>
                            <label for="fecha_rapida" class="mb-2 inline-block text-sm font-medium">Fecha</label>
                            <input id="fecha_rapida" name="fecha" type="date"
                                value="{{ old('fecha', now()->toDateString()) }}"
                                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                        </div>

                        <p class="rounded-lg border border-sky-100 bg-sky-50 px-3 py-2 text-xs text-sky-700">
                            Después de guardar podrá añadir servicios, gastos y productos, ajustar precios y completar los datos de impresión.
                        </p>

                        <div class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-zink-500">
                            <button type="button" @click="nuevaOferta = false" class="btn border-slate-200 text-slate-500">
                                Cancelar
                            </button>
                            <button type="submit" class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                <i data-lucide="save" class="me-1.5 size-4"></i>
                                Crear y editar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
