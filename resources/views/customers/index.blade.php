@extends('layouts.main')

@section('tutulo', 'Clientes')

@section('contenido')

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

                <li
                    class="relative pr-4 text-slate-400
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


        {{-- CARD --}}
        <div class="card" id="customerList">

            {{-- CABECERA --}}
            <div class="px-5 py-4 border-b border-slate-200 dark:border-zink-500">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    {{-- TITULO --}}
                    <div class="shrink-0">

                        <h6 class="text-base font-semibold text-slate-800 dark:text-zink-100">
                            Directorio de clientes
                        </h6>

                        <p class="mt-1 text-sm text-slate-500 dark:text-zink-300">
                            {{ $customers->total() }}
                            {{ $customers->total() == 1 ? 'cliente registrado' : 'clientes registrados' }}
                        </p>

                    </div>


                    {{-- BUSCADOR + CREAR --}}
                    <div class="flex items-center gap-2">

                        {{-- BUSCADOR --}}
                        <form method="GET" action="{{ route('customers.index') }}" class="relative">

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
                               hover:bg-custom-600">

                            <i data-lucide="plus" class="size-4 me-1"></i>

                            Nuevo cliente

                        </button>

                    </div>

                </div>

            </div>


            {{-- TABLA --}}
            <div class="card-body">

                <div class="w-full overflow-hidden">

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

                                            <i data-lucide="id-card" class="size-3.5 shrink-0">
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
                                            <button type="button" x-on:click='openEdit(@js([
    'id' => $customer->id,
    'name' => $customer->name,
    'phone' => $customer->phone,
    'address' => $customer->address,
    'identity_card' => $customer->identity_card,
]))'
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
                                                onsubmit="return confirm('¿Deseas eliminar este cliente? Esta acción no se puede deshacer.');">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" title="Eliminar cliente"
                                                    class="inline-flex items-center justify-center
                                                       size-8
                                                       rounded-lg
                                                       border border-red-200
                                                       text-red-500
                                                       hover:bg-red-50
                                                       hover:border-red-500
                                                       dark:border-red-500/30">

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

                                <i data-lucide="user-pen" class="size-5 text-blue-500">
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
                                    required>

                            </div>

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
                                    required></textarea>

                            </div>

                        </div>


                        {{-- CARNET --}}
                        <div>

                            <label class="inline-block mb-2 text-sm font-medium">
                                Número de carnet de identidad
                            </label>

                            <div class="relative">

                                <i data-lucide="id-card"
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
                                    required>

                            </div>

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


    </div>


    {{-- ========================================================= --}}
    {{-- ALPINE --}}
    {{-- ========================================================= --}}

  <script>
    function customerManager() {

        return {

            modal: window.customerModalState?.modal ?? null,

            customer: {
                id: window.customerModalState?.customer?.id ?? null,
                name: window.customerModalState?.customer?.name ?? '',
                phone: window.customerModalState?.customer?.phone ?? '',
                address: window.customerModalState?.customer?.address ?? '',
                identity_card: window.customerModalState?.customer?.identity_card ?? '',
            },

            editUrl: '',

            init() {

                if (this.modal === 'edit' && this.customer.id) {
                    this.editUrl = `/customers/${this.customer.id}`;
                }

                if (this.modal) {
                    document.body.classList.add('overflow-hidden');
                }

                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },

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

                document.body.classList.add('overflow-hidden');

                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },

            openEdit(customer) {

                this.customer = {
                    id: customer.id,
                    name: customer.name ?? '',
                    phone: customer.phone ?? '',
                    address: customer.address ?? '',
                    identity_card: customer.identity_card ?? ''
                };

                this.editUrl = `/customers/${customer.id}`;

                this.modal = 'edit';

                document.body.classList.add('overflow-hidden');

                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },

            close() {

                this.modal = null;

                document.body.classList.remove('overflow-hidden');
            }
        };
    }
</script>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

@endsection
