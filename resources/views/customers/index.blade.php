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
        {{-- HEADER --}}
        <div class="flex flex-col gap-3 py-4 md:flex-row md:items-center print:hidden">
            <div class="grow">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center rounded-lg size-11 bg-custom-500/10">
                        <i data-lucide="users" class="size-6 text-custom-500"></i>
                    </div>
                    <div>
                        <h5 class="text-lg font-semibold text-slate-800 dark:text-zink-100">
                            Clientes
                        </h5>
                        <p class="text-sm text-slate-500 dark:text-zink-300">
                            Administración del directorio de clientes
                        </p>
                    </div>
                </div>
            </div>
            <ul class="flex items-center gap-2 text-sm font-normal shrink-0">
                <li class="relative pr-4 text-slate-400
                       before:content-['\\ea54']
                       before:font-remix
                       before:absolute
                       before:right-0
                       before:text-[16px]
                       before:top-0">
                    <a href="{{ route('dashboard') }}" class="hover:text-custom-500">
                        Dashboard
                    </a>
                </li>
                <li class="font-medium text-slate-700 dark:text-zink-100">
                    Clientes
                </li>
            </ul>
        </div>

        {{-- MENSAJE --}}
        @if (session('success'))
            <div
                class="flex items-center gap-3 px-4 py-3 mb-5
                    text-green-700 bg-green-50
                    border border-green-200 rounded-lg
                    dark:bg-green-500/10
                    dark:text-green-300
                    dark:border-green-500/20">

                <i data-lucide="check-circle" class="size-5"></i>

                <span class="text-sm font-medium">
                    {{ session('success') }}
                </span>

            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700 shadow-sm dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300"
                role="alert">
                <i data-lucide="circle-alert" class="mt-0.5 size-5 shrink-0"></i>
                <div>
                    <p class="font-semibold">No se pudo completar la operación</p>
                    <p class="mt-1 text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200"
                role="alert">
                <div class="flex items-start gap-3">
                    <i data-lucide="triangle-alert" class="mt-0.5 size-5 shrink-0"></i>
                    <div>
                        <p class="font-semibold">Revisa los datos del formulario</p>
                        <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- CARD --}}
        <div class="card overflow-hidden bg-white dark:bg-zink-700" id="customerList">
            {{-- CABECERA --}}
            <div class="px-5 py-4 border-b border-slate-200 dark:border-zink-500">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    {{-- BUSCADOR + CREAR --}}
                    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">

                        {{-- BUSCADOR --}}
                        <form method="GET" action="{{ route('customers.index') }}" class="relative w-full sm:w-auto">

                            <input type="text" name="search" value="{{ $search }}"
                                class="w-full sm:w-72 ltr:pl-10 rtl:pr-10
                                   ltr:pr-9 rtl:pl-9
                                   form-input
                                   border-slate-200
                                   dark:border-zink-500
                                   dark:bg-zink-700
                                   focus:border-custom-500
                                   focus:ring-0"
                                placeholder="Buscar cliente..." autocomplete="off">

                            <i data-lucide="search"
                                class="absolute size-4 text-slate-400
                                  ltr:left-3 rtl:right-3
                                  top-1/2 -translate-y-1/2">
                            </i>


                            @if ($search)
                                <a href="{{ route('customers.index') }}" title="Limpiar búsqueda"
                                    class="absolute flex items-center justify-center
                                       size-6 rounded
                                       text-slate-400
                                       ltr:right-2 rtl:left-2
                                       top-1/2 -translate-y-1/2
                                       hover:bg-slate-100
                                       hover:text-slate-600
                                       dark:hover:bg-zink-600">

                                    <i data-lucide="x" class="size-4"></i>

                                </a>
                            @endif

                        </form>


                        {{-- NUEVO --}}
                           <button type="button" @click="openCreate()"
                            class="inline-flex items-center justify-center
                               whitespace-nowrap
                               text-white btn
                               bg-custom-500
                               border-custom-500
                               hover:bg-custom-600 sm:shrink-0">
                            <i data-lucide="plus" class="size-4 me-1"></i>
                            Nuevo cliente
                        </button>
                    </div>
                </div>
            </div>

            {{-- TABLA --}}
            <div class="card-body">
                <div class="w-full overflow-x-auto">
                    <table class="w-full table-fixed">
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
                                    class="px-3 py-3 text-xs font-semibold
                                       text-left uppercase
                                       text-slate-500
                                       border-b border-slate-200
                                       dark:border-zink-500
                                       dark:text-zink-200">
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
                                                    rounded-full size-9 shrink-0
                                                    bg-custom-500/10
                                                    text-custom-500
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
                                               bg-slate-100
                                               text-slate-600
                                               dark:bg-zink-600
                                               dark:text-zink-200"
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
                                                   hover:text-custom-500
                                                   hover:border-custom-500
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
                                                                            border border-blue-200
                                                        bg-blue-50 text-blue-600
                                                        transition hover:bg-blue-100 hover:text-blue-700
                                                        dark:border-blue-900/50
                                                        dark:bg-blue-900/20 dark:text-blue-400
                                                                dark:hover:bg-blue-900/40">
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
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="flex items-center justify-center
                                                    size-14 rounded-full
                                                    bg-slate-100
                                                    dark:bg-zink-600">

                                                <i data-lucide="users" class="size-7 text-slate-400">
                                                </i>
                                            </div>
                                            <h6
                                                class="mt-4 font-semibold
                                                   text-slate-700
                                                   dark:text-zink-100">

                                                {{ $search ? 'No se encontraron clientes' : 'No hay clientes registrados' }}
                                            </h6>
                                            <p
                                                class="mt-1 text-sm text-slate-500
                                                  dark:text-zink-300">

                                                @if ($search)
                                                    No encontramos resultados para
                                                    "{{ $search }}".
                                                @else
                                                    Comienza agregando tu primer cliente.
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINACION --}}
                @if ($customers->total() > 0)
                    <div
                        class="flex flex-col gap-3 pt-5 mt-5
                            border-t border-slate-200
                            dark:border-zink-500
                            sm:flex-row sm:items-center
                            sm:justify-between">

                        <div class="text-sm text-slate-500 dark:text-zink-300">

                            Mostrando

                            <span class="font-medium text-slate-700 dark:text-zink-100">
                                {{ $customers->firstItem() }}
                            </span>

                            -

                            <span class="font-medium text-slate-700 dark:text-zink-100">
                                {{ $customers->lastItem() }}
                            </span>

                            de

                            <span class="font-medium text-slate-700 dark:text-zink-100">
                                {{ $customers->total() }}
                            </span>

                        </div>

                        <div>
                            {{ $customers->appends(request()->query())->links('pagination.custom') }}
                        </div>

                    </div>
                @endif
            </div>
        </div>


        {{-- ================================================= --}}
        {{-- MODAL CREAR --}}
        {{-- ================================================= --}}

        <div x-show="modal === 'create'" x-cloak class="fixed inset-0 overflow-y-auto" style="z-index:1050"
            role="dialog" aria-modal="true">

            <div class="flex items-start justify-center min-h-full
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
                                    size-10 rounded-lg bg-custom-500/10">

                                <i data-lucide="user-plus" class="size-5 text-custom-500">
                                </i>

                            </div>

                            <div>

                                <p
                                    class="text-xs font-semibold tracking-wider
                                      text-custom-500 uppercase">
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
                                class="text-white btn bg-custom-500
                                   border-custom-500 hover:bg-custom-600">

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

            <div class="flex items-start justify-center min-h-full
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
                                    size-10 rounded-lg bg-blue-500/10">

                                <i data-lucide="user-cog" class="size-5 text-blue-500">
                                </i>

                            </div>

                            <div>

                                <p
                                    class="text-xs font-semibold tracking-wider
                                      text-blue-500 uppercase">
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
                                       focus:border-custom-500"
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
                                       focus:border-custom-500"
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
                                       focus:border-custom-500 resize-none"
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
                                       focus:border-custom-500"
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
                                class="text-white btn bg-custom-500
                                   border-custom-500 hover:bg-custom-600">

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

                        <button type="button" @click="deleteCustomerConfirmed()"
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
                /* ELIMINAR */
                /* ================================================= */

                deleteCustomerConfirmed() {

                    if (!this.deleteCustomer.url) {
                        return;
                    }

                    const form = document.createElement('form');

                    form.method = 'POST';
                    form.action = this.deleteCustomer.url;

                    const csrf = document.createElement('input');

                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value =
                        document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                    const method = document.createElement('input');

                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';

                    form.appendChild(csrf);
                    form.appendChild(method);

                    document.body.appendChild(form);

                    form.submit();
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
