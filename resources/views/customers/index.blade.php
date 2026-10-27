@extends('layouts.main')

@section('titulo', 'Clientes')

@section('contenido')

    @php
        $oldCustomerId = old('customer_id');
        $hasValidationModal = $oldCustomerId !== null;
        $currentModal = $hasValidationModal
            ? ($oldCustomerId === 'new' ? 'create' : 'edit')
            : ($modal ?? null);
        $currentCustomer = $hasValidationModal && $oldCustomerId !== 'new'
            ? [
                'id' => (int) $oldCustomerId,
                'name' => old('name', ''),
                'phone' => old('phone', ''),
                'address' => old('address', ''),
                'identity_card' => old('identity_card', ''),
            ]
            : ($modalCustomer
                ? [
                    'id' => $modalCustomer->id,
                    'name' => $modalCustomer->name,
                    'phone' => $modalCustomer->phone,
                    'address' => $modalCustomer->address,
                    'identity_card' => $modalCustomer->identity_card,
                ]
                : null);
    @endphp

    <script>
        window.customerModalState = @json([
            'modal' => $currentModal,
            'customer' => $currentCustomer,
        ]);
    </script>

    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto" x-data="customerManager()">
        @include('layouts.page-header', [
            'icon' => 'users',
            'title' => 'Clientes',
            'description' => 'Administración del directorio de clientes',
            'accent' => 'green',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Clientes'],
            ],
        ])

        {{-- CARD --}}
        <div class="nova-table-card card overflow-hidden bg-white dark:bg-zink-700" id="customerList" data-table-card>
            <div class="nova-progress-bar h-1 w-full"></div>

            {{-- CABECERA --}}
            <div class="px-5 py-4 border-b border-slate-200 dark:border-zink-500">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <span class="whitespace-nowrap text-sm font-semibold text-slate-700 dark:text-zink-100">
                            Listado de clientes
                        </span>
                        <span aria-live="polite"
                            class="inline-flex items-center rounded-full bg-nova-50 px-2.5 py-0.5 text-xs font-medium text-nova-700 dark:bg-nova-500/15 dark:text-nova-300">
                            {{ $customers->total() }}
                        </span>
                    </div>

                    {{-- BUSCADOR + CREAR --}}
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end">
                        <x-table-search :action="route('customers.index')" :search="$search"
                            placeholder="Buscar cliente..." id="customer-search" />

                        {{-- NUEVO --}}
                        <button type="button" @click="openCreate()"
                            class="inline-flex items-center justify-center whitespace-nowrap text-white btn bg-nova-500 border-nova-500 hover:bg-nova-600 sm:shrink-0">
                            <i data-lucide="plus" class="size-4 me-1"></i>
                            Nuevo cliente
                        </button>
                    </div>
                </div>
            </div>

            <x-table-loading />

            {{-- TABLA --}}
            <div class="card-body">
                <div class="nova-table-scroll w-full">
                    <table class="nova-table w-full table-fixed">
                        {{-- ANCHOS FIJOS --}}
                        <colgroup>
                            <col style="width: 24%">
                            <col style="width: 30%">
                            <col style="width: 16%">
                            <col style="width: 18%">
                            <col style="width: 12%">
                        </colgroup>

                        <thead>
                            <tr class="bg-slate-50 dark:bg-zink-600">
                                <th
                                    class="px-3 py-3 text-xs font-semibold text-left uppercase text-slate-500 border-b border-slate-200 dark:border-zink-500 dark:text-zink-200">
                                    Cliente
                                </th>

                                <th
                                    class="px-3 py-3 text-xs font-semibold
                                       text-left uppercase
                                       text-slate-500
                                       border-b border-slate-200
                                       dark:border-zink-500
                                       dark:text-zink-200">
                                    Dirección
                                </th>

                                <th
                                    class="px-3 py-3 text-xs font-semibold
                                       text-left uppercase
                                       text-slate-500
                                       border-b border-slate-200
                                       dark:border-zink-500
                                       dark:text-zink-200">
                                    Teléfono
                                </th>

                                <th
                                    class="px-3 py-3 text-xs font-semibold
                                       text-left uppercase
                                       text-slate-500
                                       border-b border-slate-200
                                       dark:border-zink-500
                                       dark:text-zink-200">
                                    Identificación
                                </th>

                                <th
                                    class="px-2 py-3 text-xs font-semibold
                                       text-center uppercase
                                       text-slate-500
                                       border-b border-slate-200
                                       dark:border-zink-500
                                       dark:text-zink-200">
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($customers as $customer)
                                <tr class="hover:bg-slate-50 dark:hover:bg-zink-600/50">

                                    {{-- CLIENTE --}}
                                    <td
                                        class="px-3 py-3 border-b border-slate-200
                                           dark:border-zink-500 overflow-hidden">

                                        <div class="flex items-center gap-2 min-w-0">

                                            <div
                                                class="flex items-center justify-center
                                                    rounded-xl size-9 shrink-0
                                                    bg-gradient-to-br from-emerald-100 to-sky-100
                                                    text-nova-700
                                                    font-semibold">

                                                {{ strtoupper(mb_substr($customer->name, 0, 1)) }}

                                            </div>

                                            <div class="min-w-0">

                                                <div class="font-medium text-slate-700
                                                       dark:text-zink-100
                                                       truncate"
                                                    title="{{ $customer->name }}">
                                                    {{ $customer->name }}
                                                </div>

                                                <div class="text-xs text-slate-400 truncate">
                                                    Cliente #{{ $customer->id }}
                                                </div>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- DIRECCION --}}
                                    <td
                                        class="px-3 py-3 border-b border-slate-200
                                           dark:border-zink-500 overflow-hidden">

                                        <div class="flex items-center gap-2 min-w-0">

                                            <i data-lucide="map-pin" class="size-4 shrink-0 text-slate-400">
                                            </i>

                                            <span
                                                class="block min-w-0 truncate
                                                   text-sm text-slate-600
                                                   dark:text-zink-200"
                                                title="{{ $customer->address }}">
                                                {{ $customer->address }}
                                            </span>

                                        </div>

                                    </td>


                                    {{-- TELEFONO --}}
                                    <td
                                        class="px-3 py-3 border-b border-slate-200
                                           dark:border-zink-500 overflow-hidden">

                                        <div class="flex items-center gap-2 min-w-0">

                                            <i data-lucide="phone" class="size-4 shrink-0 text-slate-400">
                                            </i>

                                            <span
                                                class="truncate text-sm
                                                   text-slate-600
                                                   dark:text-zink-200"
                                                title="{{ $customer->phone }}">
                                                {{ $customer->phone }}
                                            </span>

                                        </div>

                                    </td>


                                    {{-- IDENTIFICACION --}}
                                    <td
                                        class="px-3 py-3 border-b border-slate-200
                                           dark:border-zink-500 overflow-hidden">

                                        <span
                                            class="inline-flex max-w-full items-center gap-1
                                               px-2 py-1
                                               text-xs font-medium
                                               rounded-md
                                               bg-amber-50
                                               text-amber-700
                                               dark:bg-amber-500/15
                                               dark:text-amber-300"
                                            title="{{ $customer->identity_card }}">

                                            <i data-lucide="contact" class="size-3.5 shrink-0">
                                            </i>

                                            <span class="truncate">
                                                {{ $customer->identity_card }}
                                            </span>

                                        </span>

                                    </td>


                                    {{-- ACCIONES --}}
                                    <td
                                        class="px-2 py-3 border-b border-slate-200
                                           dark:border-zink-500">

                                        <div class="flex items-center justify-center gap-1">

                                            {{-- VER --}}
                                            <a href="{{ route('customers.show', $customer) }}" title="Ver cliente"
                                                class="inline-flex items-center justify-center
                                                   size-8 shrink-0
                                                   rounded-lg
                                                   border border-slate-200
                                                   text-slate-500
                                                   hover:text-nova-500
                                                   hover:border-nova-500
                                                   dark:border-zink-500">

                                                <i data-lucide="eye" class="size-4"></i>

                                            </a>


                                            {{-- EDITAR --}}
                                            <button type="button"
                                                x-on:click="openEdit({{ \Illuminate\Support\Js::from([
                                                    'id' => $customer->id,
                                                    'name' => $customer->name,
                                                    'phone' => $customer->phone,
                                                    'address' => $customer->address,
                                                    'identity_card' => $customer->identity_card,
                                                ]) }})"
                                                title="Editar cliente"
                                                class="inline-flex items-center justify-center
                                                    size-8 shrink-0
                                                                                rounded-lg
                                                                            border border-sky-200
                                                        bg-sky-50 text-sky-600
                                                        transition hover:bg-sky-100 hover:text-sky-700
                                                        dark:border-sky-900/50
                                                        dark:bg-sky-900/20 dark:text-sky-400
                                                                dark:hover:bg-sky-900/40">
                                                <i data-lucide="pencil" class="size-4"></i>
                                            </button>


                                            {{-- ELIMINAR --}}
                                            <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                                                class="inline-flex shrink-0"
                                                @submit.prevent="confirmDelete({
                                                id: {{ $customer->id }},
                                                name: @js($customer->name),
                                                 url: @js(route('customers.destroy', $customer))
                                                                                            })">

                                                 @csrf
                                                @method('DELETE')

                                                <button type="submit" title="Eliminar cliente"
                                                    class="inline-flex items-center justify-center
                                                                    size-8
                                                                     rounded-lg
                                                                         border border-red-200
                                                                                                             bg-red-50
                                                                            text-red-500
                                                                                 transition-all
                                                                    hover:bg-red-100
                                                                hover:border-red-300
                                                                 hover:text-red-600
                                                             dark:border-red-500/30
                                                             dark:bg-red-500/10
                                                                 dark:text-red-400
                                                                         dark:hover:bg-red-500/20">

                                                    <i data-lucide="trash-2" class="size-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5">
                                        <x-table-empty icon="users"
                                            :title="$search ? 'Sin resultados' : 'No hay clientes registrados'"
                                            :message="$search ? 'No encontramos coincidencias para “'.$search.'”.' : 'Comienza agregando tu primer cliente al directorio.'">
                                            @if ($search)
                                                <a href="{{ route('customers.index') }}"
                                                    class="text-sm font-medium text-nova-600 hover:text-nova-700">
                                                    Limpiar búsqueda
                                                </a>
                                            @else
                                                <button type="button" @click="openCreate()"
                                                    class="btn border-nova-500 bg-nova-500 text-white hover:bg-nova-600">
                                                    <i data-lucide="user-plus" class="me-1.5 inline-block size-4"></i>
                                                    Nuevo cliente
                                                </button>
                                            @endif
                                        </x-table-empty>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINACION --}}
                <x-table-pagination :paginator="$customers" label="clientes" />
            </div>
        </div>


        {{-- ================================================= --}}
        {{-- MODAL CREAR --}}
        {{-- ================================================= --}}

        <div x-show="modal === 'create'" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1050"
            role="dialog" aria-modal="true">

            <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="close()" aria-hidden="true"></div>

            <div class="relative flex items-start justify-center min-h-full
                   px-4 pt-16 pb-8 md:pt-20">

                <div @click.outside="close()"
                    class="w-full max-w-2xl overflow-hidden
                       bg-white rounded-xl shadow-2xl
                       dark:bg-zink-700">

                    <div
                        class="flex items-center justify-between
                            px-6 py-4
                            border-b border-slate-200
                            dark:border-zink-500">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex items-center justify-center
                                    size-10 rounded-lg bg-nova-500/10">

                                <i data-lucide="user-plus" class="size-5 text-nova-500">
                                </i>

                            </div>

                            <div>

                                <p
                                    class="text-xs font-semibold tracking-wider
                                      text-nova-500 uppercase">
                                    Directorio
                                </p>

                                <h5
                                    class="text-lg font-semibold
                                       text-slate-800 dark:text-zink-100">
                                    Nuevo cliente
                                </h5>

                            </div>

                        </div>

                        <button type="button" @click="close()"
                            class="inline-flex items-center justify-center
                               size-9 rounded-lg
                               text-slate-500
                               hover:bg-slate-100
                               dark:hover:bg-zink-600">

                            <i data-lucide="x" class="size-5"></i>

                        </button>

                    </div>


                    <form method="POST" action="{{ route('customers.store') }}"
                        class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        @csrf

                        <input type="hidden" name="customer_id" value="new">

                        @php($customer = new \App\Models\Customer())
                        @php($fieldPrefix = 'create-')

                        @include('customers._fields')


                        <div
                            class="flex justify-end gap-2
                                pt-4 md:col-span-2
                                border-t border-slate-200
                                dark:border-zink-500">

                            <button type="button" @click="close()" class="btn border-slate-200 text-slate-500">
                                Cancelar
                            </button>

                            <button type="submit"
                                class="text-white btn bg-nova-500
                                   border-nova-500 hover:bg-nova-600">

                                <i data-lucide="save" class="size-4 me-1"></i>

                                Guardar cliente

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- MODAL EDITAR --}}
        {{-- ================================================= --}}

        <div x-show="modal === 'edit'" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1050" role="dialog"
            aria-modal="true">

            <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="close()" aria-hidden="true"></div>

            <div class="relative flex items-start justify-center min-h-full
                   px-4 pt-16 pb-8 md:pt-20">

                <div @click.outside="close()"
                    class="w-full max-w-2xl overflow-hidden
                       bg-white rounded-xl shadow-2xl
                       dark:bg-zink-700">

                    {{-- HEADER --}}
                    <div
                        class="flex items-center justify-between
                            px-6 py-4
                            border-b border-slate-200
                            dark:border-zink-500">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex items-center justify-center
                                    size-10 rounded-lg bg-sky-500/10">

                                <i data-lucide="user-cog" class="size-5 text-sky-500">
                                </i>

                            </div>

                            <div>

                                <p
                                    class="text-xs font-semibold tracking-wider
                                      text-sky-500 uppercase">
                                    Directorio
                                </p>

                                <h5
                                    class="text-lg font-semibold
                                       text-slate-800 dark:text-zink-100">

                                    Editar cliente

                                </h5>

                            </div>

                        </div>


                        <button type="button" @click="close()"
                            class="inline-flex items-center justify-center
                               size-9 rounded-lg
                               text-slate-500
                               hover:bg-slate-100
                               dark:hover:bg-zink-600">

                            <i data-lucide="x" class="size-5"></i>

                        </button>

                    </div>


                    {{-- FORMULARIO --}}
                    <form method="POST" :action="editUrl" class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        @csrf
                        @method('PUT')

                        <input type="hidden" name="customer_id" :value="customer.id">


                        {{-- NOMBRE --}}
                        <div>

                            <label class="inline-block mb-2 text-sm font-medium">
                                Nombre completo
                            </label>

                            <div class="relative">

                                <i data-lucide="user"
                                    class="absolute size-4 text-slate-400
                                      ltr:left-3 rtl:right-3
                                      top-1/2 -translate-y-1/2">
                                </i>

                                <input name="name" type="text" x-model="customer.name"
                                    class="ltr:pl-10 rtl:pr-10 form-input w-full
                                       border-slate-200
                                       dark:border-zink-500
                                       dark:bg-zink-700
                                       focus:border-nova-500"
                                                minlength="10" maxlength="50"
                                    required>

                            </div>

                            @error('name')
                                <p class="mt-1 text-sm text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- TELEFONO --}}
                        <div>

                            <label class="inline-block mb-2 text-sm font-medium">
                                Número de teléfono
                            </label>

                            <div class="relative">

                                <i data-lucide="phone"
                                    class="absolute size-4 text-slate-400
                                      ltr:left-3 rtl:right-3
                                      top-1/2 -translate-y-1/2">
                                </i>

                                <input name="phone" type="text" x-model="customer.phone"
                                    class="ltr:pl-10 rtl:pr-10 form-input w-full
                                       border-slate-200
                                       dark:border-zink-500
                                       dark:bg-zink-700
                                       focus:border-nova-500"
                                                inputmode="numeric" pattern="[0-9]{8,12}" maxlength="12"
                                    required>

                            </div>

                            @error('phone')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- DIRECCION --}}
                        <div class="md:col-span-2">

                            <label class="inline-block mb-2 text-sm font-medium">
                                Dirección particular
                            </label>

                            <div class="relative">

                                <i data-lucide="map-pin"
                                    class="absolute size-4 text-slate-400
                                      ltr:left-3 rtl:right-3 top-3">
                                </i>

                                <textarea name="address" rows="3" x-model="customer.address"
                                    class="ltr:pl-10 rtl:pr-10 form-input w-full
                                       border-slate-200
                                       dark:border-zink-500
                                       dark:bg-zink-700
                                       focus:border-nova-500 resize-none"
                                                maxlength="100"
                                    required></textarea>

                            </div>

                            @error('address')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- CARNET --}}
                        <div>

                            <label class="inline-block mb-2 text-sm font-medium">
                                Número de carnet de identidad
                            </label>

                            <div class="relative">

                                <i data-lucide="contact"
                                    class="absolute size-4 text-slate-400
                                      ltr:left-3 rtl:right-3
                                      top-1/2 -translate-y-1/2">
                                </i>

                                <input name="identity_card" type="text" x-model="customer.identity_card"
                                    class="ltr:pl-10 rtl:pr-10 form-input w-full
                                       border-slate-200
                                       dark:border-zink-500
                                       dark:bg-zink-700
                                       focus:border-nova-500"
                                                inputmode="numeric" pattern="[0-9]{11}" maxlength="11"
                                    required>

                            </div>

                            @error('identity_card')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- BOTONES --}}
                        <div
                            class="flex justify-end gap-2
                                pt-4 md:col-span-2
                                border-t border-slate-200
                                dark:border-zink-500">

                            <button type="button" @click="close()" class="btn border-slate-200 text-slate-500">
                                Cancelar
                            </button>

                            <button type="submit"
                                class="text-white btn bg-nova-500
                                   border-nova-500 hover:bg-nova-600">

                                <i data-lucide="save" class="size-4 me-1"></i>

                                Guardar cambios

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- MODAL CONFIRMAR ELIMINACION --}}
        {{-- ========================================================= --}}

        <div x-show="deleteModal" x-cloak x-transition.opacity class="fixed inset-0 overflow-y-auto"
            style="z-index: 1100" role="dialog" aria-modal="true" @keydown.escape.window="closeDeleteModal()">

            {{-- BACKDROP --}}
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeDeleteModal()">
            </div>

            {{-- CONTENEDOR --}}
            <div class="relative flex min-h-full items-center justify-center
               px-4 py-8">

                {{-- MODAL --}}
                <div x-show="deleteModal" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95" @click.stop
                    class="relative w-full max-w-md overflow-hidden
                   rounded-2xl bg-white shadow-2xl
                   dark:bg-zink-700">

                    {{-- ICONO --}}
                    <div class="flex justify-center pt-7">

                        <div
                            class="flex size-16 items-center justify-center
                           rounded-full
                           bg-red-100
                           ring-8 ring-red-50
                           dark:bg-red-500/10
                           dark:ring-red-500/5">

                            <i data-lucide="trash-2" class="size-7 text-red-500">
                            </i>

                        </div>

                    </div>

                    {{-- CONTENIDO --}}
                    <div class="px-6 pt-5 pb-6 text-center">

                        <h5 class="text-lg font-semibold text-slate-800 dark:text-zink-100">
                            ¿Eliminar cliente?
                        </h5>

                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-zink-300">

                            Estás a punto de eliminar a

                            <span class="font-semibold text-slate-700 dark:text-zink-100" x-text="deleteCustomer.name">
                            </span>.

                        </p>

                        <div
                            class="mt-4 rounded-lg
                           border border-red-200
                           bg-red-50
                           px-4 py-3
                           text-left
                           dark:border-red-500/20
                           dark:bg-red-500/10">

                            <div class="flex gap-3">

                                <i data-lucide="triangle-alert" class="mt-0.5 size-4 shrink-0 text-red-500">
                                </i>

                                <p class="text-xs leading-5 text-red-600 dark:text-red-300">

                                    Esta acción eliminará el registro del cliente
                                    y no podrá deshacerse.

                                </p>

                            </div>

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-2
                       border-t border-slate-200
                       bg-slate-50
                       px-6 py-4
                       dark:border-zink-500
                       dark:bg-zink-600">

                        <button type="button" @click="closeDeleteModal()"
                            class="btn
                           border-slate-200
                           bg-white
                           text-slate-600
                           hover:bg-slate-100
                           dark:border-zink-500
                           dark:bg-zink-700
                           dark:text-zink-200
                           dark:hover:bg-zink-600">

                            Cancelar

                        </button>

                        <form method="POST" :action="deleteCustomer.url">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="inline-flex items-center
                               btn
                               border-red-500
                               bg-red-500
                               text-white
                               shadow-sm
                               hover:bg-red-600">

                                <i data-lucide="trash-2" class="size-4 me-1.5"></i>

                                Eliminar cliente

                            </button>
                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ========================================================= --}}
    {{-- ALPINE --}}
    {{-- ========================================================= --}}

    <script>
        function customerManager() {

            const initialState = window.customerModalState ?? {};
            const initialCustomer = initialState.customer ?? {};

            return {

                /* ================================================= */
                /* MODALES */
                /* ================================================= */

                modal: initialState.modal ?? null,

                deleteModal: false,

                /* ================================================= */
                /* CLIENTE */
                /* ================================================= */

                customer: {
                    id: initialCustomer.id ?? null,
                    name: initialCustomer.name ?? '',
                    phone: initialCustomer.phone ?? '',
                    address: initialCustomer.address ?? '',
                    identity_card: initialCustomer.identity_card ?? '',
                },

                /* ================================================= */
                /* ELIMINACION */
                /* ================================================= */

                deleteCustomer: {
                    id: null,
                    name: '',
                    url: ''
                },

                /* ================================================= */
                /* EDITAR */
                /* ================================================= */

                editUrl: '',

                /* ================================================= */
                /* INIT */
                /* ================================================= */

                init() {

                    if (this.modal === 'edit' && this.customer.id) {
                        this.editUrl = `/clientes/${this.customer.id}`;
                    }

                    this.updateBodyScroll();

                    this.$nextTick(() => {
                        this.refreshIcons();
                    });
                },

                /* ================================================= */
                /* CREAR */
                /* ================================================= */

                openCreate() {

                    this.customer = {
                        id: null,
                        name: '',
                        phone: '',
                        address: '',
                        identity_card: ''
                    };

                    this.editUrl = '';

                    this.modal = 'create';

                    this.updateBodyScroll();

                    this.$nextTick(() => {
                        this.refreshIcons();
                    });
                },

                /* ================================================= */
                /* EDITAR */
                /* ================================================= */

                openEdit(customer) {

                    this.customer = {
                        id: customer.id,
                        name: customer.name ?? '',
                        phone: customer.phone ?? '',
                        address: customer.address ?? '',
                        identity_card: customer.identity_card ?? ''
                    };

                    this.editUrl = `/clientes/${customer.id}`;

                    this.modal = 'edit';

                    this.updateBodyScroll();

                    this.$nextTick(() => {
                        this.refreshIcons();
                    });
                },

                /* ================================================= */
                /* CERRAR MODAL */
                /* ================================================= */

                close() {

                    this.modal = null;

                    this.updateBodyScroll();
                },

                /* ================================================= */
                /* CONFIRMAR ELIMINACION */
                /* ================================================= */

                confirmDelete(customer) {

                    this.deleteCustomer = {
                        id: customer.id,
                        name: customer.name ?? '',
                        url: customer.url
                    };

                    this.deleteModal = true;

                    document.body.classList.add('overflow-hidden');

                    this.$nextTick(() => {
                        this.refreshIcons();
                    });
                },

                /* ================================================= */
                /* CERRAR ELIMINACION */
                /* ================================================= */

                closeDeleteModal() {

                    this.deleteModal = false;

                    this.deleteCustomer = {
                        id: null,
                        name: '',
                        url: ''
                    };

                    this.updateBodyScroll();
                },

                /* ================================================= */
                /* SCROLL */
                /* ================================================= */

                updateBodyScroll() {

                    if (this.modal || this.deleteModal) {

                        document.body.classList.add('overflow-hidden');

                    } else {

                        document.body.classList.remove('overflow-hidden');

                    }
                },

                /* ================================================= */
                /* LUCIDE */
                /* ================================================= */

                refreshIcons() {

                    if (window.lucide) {
                        lucide.createIcons();
                    }
                }

            };
        }
    </script>
@endsection
