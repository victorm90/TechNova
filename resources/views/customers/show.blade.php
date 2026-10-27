@extends('layouts.main')

@section('titulo', 'Detalle del cliente')

@section('contenido')

    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">

        {{-- HEADER --}}
        <div class="nova-hero mb-5">
            <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <nav class="nova-breadcrumb mb-1.5" aria-label="Ruta de navegación">
                        <a href="{{ route('customers.index') }}">Clientes</a>

                        <i data-lucide="chevron-right" class="size-3 text-slate-300 dark:text-zink-500"></i>

                        <span class="font-medium text-slate-500 dark:text-zink-300">Detalle</span>
                    </nav>

                    <h1 class="text-xl font-semibold text-slate-800 sm:text-2xl dark:text-zink-100">
                        Información del cliente
                    </h1>

                    <p class="mt-1 text-sm text-slate-500 dark:text-zink-300">
                        Datos de contacto y perfil del cliente
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- VOLVER --}}
                    <a href="{{ route('customers.index') }}"
                        class="inline-flex items-center gap-2 btn border-slate-200 bg-white text-slate-600 shadow-sm
                               hover:border-slate-300 hover:text-nova-500
                               dark:border-zink-500 dark:bg-zink-700 dark:text-zink-200">
                        <i data-lucide="arrow-left" class="size-4"></i>
                        <span>Volver</span>
                    </a>

                    {{-- EDITAR --}}
                    <a href="{{ route('customers.edit', $customer) }}"
                        class="inline-flex items-center gap-2 btn bg-nova-500 border-nova-500 text-white shadow-sm
                               hover:bg-nova-600 hover:border-nova-600">
                        <i data-lucide="pencil" class="size-4"></i>
                        <span>Editar cliente</span>
                    </a>
                </div>
            </div>
        </div>
        {{-- PERFIL DEL CLIENTE --}}
        <div class="mb-5 card border-0 shadow-sm dark:border-zink-600">

            <div class="card-body p-0">

                <div class="relative overflow-hidden rounded-xl">

                    {{-- DECORACIÓN --}}
                    <div class="absolute inset-x-0 top-0 h-28 bg-nova-500/10 dark:bg-nova-500/5"></div>

                    <div class="relative px-5 pb-5 pt-7 sm:px-7">

                        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

                            {{-- IDENTIDAD --}}
                            <div class="flex items-center gap-4">

                                {{-- AVATAR --}}
                                <div class="flex size-20 shrink-0 items-center justify-center rounded-2xl
                                            bg-gradient-to-br from-emerald-400 via-nova-400 to-sky-400 text-2xl font-bold text-white shadow-lg
                                            ring-4 ring-white dark:ring-zink-700">

                                    {{ strtoupper(substr($customer->name, 0, 1)) }}

                                </div>

                                <div class="min-w-0">

                                    <div class="flex flex-wrap items-center gap-2">

                                        <h3 class="text-xl font-bold text-slate-800 dark:text-zink-100">
                                            {{ $customer->name }}
                                        </h3>

                                        <span class="inline-flex items-center gap-1 rounded-full
                                                     bg-emerald-50 px-2.5 py-1 text-xs font-medium
                                                     text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Cliente
                                        </span>

                                    </div>

                                    <p class="mt-1 text-sm text-slate-500 dark:text-zink-300">
                                        Información general y datos de contacto
                                    </p>

                                </div>

                            </div>

                            {{-- ID CLIENTE --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3
                                        dark:border-zink-500 dark:bg-zink-800">

                                <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zink-400">
                                    ID del cliente
                                </p>

                                <p class="mt-1 font-semibold text-slate-700 dark:text-zink-100">
                                    #{{ $customer->id }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>


        {{-- INFORMACIÓN RÁPIDA --}}
        <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            {{-- TELÉFONO --}}
            <div class="card border-0 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="card-body">

                    <div class="flex items-center gap-4">

                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl
                                    bg-sky-50 text-sky-600">
                            <i data-lucide="phone" class="size-6"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-zink-400">
                                Teléfono
                            </p>

                            <p class="mt-1 truncate text-sm font-semibold text-slate-700 dark:text-zink-100">
                                {{ $customer->phone ?: 'No registrado' }}
                            </p>
                        </div>

                    </div>

                </div>
            </div>


            {{-- DIRECCIÓN --}}
            <div class="card border-0 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="card-body">

                    <div class="flex items-center gap-4">

                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl
                                    bg-amber-50 text-amber-600">
                            <i data-lucide="map-pin" class="size-6"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-zink-400">
                                Dirección
                            </p>

                            <p class="mt-1 truncate text-sm font-semibold text-slate-700 dark:text-zink-100">
                                {{ $customer->address ?: 'No registrada' }}
                            </p>
                        </div>

                    </div>

                </div>
            </div>


            {{-- IDENTIFICACIÓN --}}
            <div class="card border-0 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="card-body">

                    <div class="flex items-center gap-4">

                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl
                                    bg-nova-50 text-nova-600">
                            <i data-lucide="badge-check" class="size-6"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-zink-400">
                                Identificación
                            </p>

                            <p class="mt-1 truncate text-sm font-semibold text-slate-700 dark:text-zink-100">
                                {{ $customer->identity_card ?: 'No registrada' }}
                            </p>
                        </div>

                    </div>

                </div>
            </div>

        </div>


        {{-- CONTENIDO PRINCIPAL --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

            {{-- DATOS DEL CLIENTE --}}
            <div class="xl:col-span-2">

                <div class="card border-0 shadow-sm dark:border-zink-600">

                    {{-- CARD HEADER --}}
                    <div class="card-header border-b border-slate-200 dark:border-zink-500">

                        <div class="flex items-center gap-3">

                            <div class="flex size-10 items-center justify-center rounded-lg
                                        bg-nova-500/10 text-nova-500">
                                <i data-lucide="user-round" class="size-5"></i>
                            </div>

                            <div>
                                <h6 class="font-semibold text-slate-800 dark:text-zink-100">
                                    Datos personales
                                </h6>

                                <p class="mt-0.5 text-xs text-slate-400 dark:text-zink-400">
                                    Información registrada del cliente
                                </p>
                            </div>

                        </div>

                    </div>


                    {{-- CARD BODY --}}
                    <div class="card-body">

                        <div class="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">

                            {{-- NOMBRE --}}
                            <div>
                                <div class="mb-2 flex items-center gap-2">

                                    <i data-lucide="user" class="size-4 text-nova-500"></i>

                                    <span class="text-xs font-medium uppercase tracking-wide
                                                 text-slate-400 dark:text-zink-400">
                                        Nombre completo
                                    </span>

                                </div>

                                <p class="text-sm font-semibold text-slate-700 dark:text-zink-100">
                                    {{ $customer->name }}
                                </p>
                            </div>


                            {{-- TELÉFONO --}}
                            <div>
                                <div class="mb-2 flex items-center gap-2">

                                    <i data-lucide="phone" class="size-4 text-nova-500"></i>

                                    <span class="text-xs font-medium uppercase tracking-wide
                                                 text-slate-400 dark:text-zink-400">
                                        Número de teléfono
                                    </span>

                                </div>

                                <p class="text-sm font-semibold text-slate-700 dark:text-zink-100">
                                    {{ $customer->phone ?: 'No registrado' }}
                                </p>
                            </div>


                            {{-- IDENTIDAD --}}
                            <div>
                                <div class="mb-2 flex items-center gap-2">

                                    <i data-lucide="credit-card" class="size-4 text-nova-500"></i>

                                    <span class="text-xs font-medium uppercase tracking-wide
                                                 text-slate-400 dark:text-zink-400">
                                        Carnet de identidad
                                    </span>

                                </div>

                                <p class="text-sm font-semibold text-slate-700 dark:text-zink-100">
                                    {{ $customer->identity_card ?: 'No registrado' }}
                                </p>
                            </div>


                            {{-- DIRECCIÓN --}}
                            <div class="md:col-span-2">

                                <div class="mb-2 flex items-center gap-2">

                                    <i data-lucide="map-pin" class="size-4 text-nova-500"></i>

                                    <span class="text-xs font-medium uppercase tracking-wide
                                                 text-slate-400 dark:text-zink-400">
                                        Dirección particular
                                    </span>

                                </div>

                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3
                                            dark:border-zink-500 dark:bg-zink-800">

                                    <p class="text-sm font-medium text-slate-700 dark:text-zink-100">
                                        {{ $customer->address ?: 'No registrada' }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- PANEL LATERAL --}}
            <div>

                <div class="card border-0 shadow-sm dark:border-zink-600">

                    <div class="card-header border-b border-slate-200 dark:border-zink-500">

                        <div class="flex items-center gap-3">

                            <div class="flex size-10 items-center justify-center rounded-lg
                                        bg-slate-100 text-slate-600
                                        dark:bg-zink-600 dark:text-zink-200">

                                <i data-lucide="settings-2" class="size-5"></i>

                            </div>

                            <div>

                                <h6 class="font-semibold text-slate-800 dark:text-zink-100">
                                    Acciones
                                </h6>

                                <p class="mt-0.5 text-xs text-slate-400 dark:text-zink-400">
                                    Gestionar cliente
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="space-y-3">

                            {{-- EDITAR --}}
                            <a href="{{ route('customers.edit', $customer) }}"
                                class="flex w-full items-center justify-between rounded-lg border
                                       border-slate-200 px-4 py-3 transition-all
                                       hover:border-nova-300 hover:bg-nova-50
                                       dark:border-zink-500 dark:hover:bg-nova-500/10">

                                <div class="flex items-center gap-3">

                                    <div class="flex size-9 items-center justify-center rounded-lg
                                                bg-nova-500/10 text-nova-500">

                                        <i data-lucide="pencil" class="size-4"></i>

                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-slate-700 dark:text-zink-100">
                                            Editar cliente
                                        </p>

                                        <p class="text-xs text-slate-400 dark:text-zink-400">
                                            Modificar información
                                        </p>
                                    </div>

                                </div>

                                <i data-lucide="chevron-right"
                                    class="size-4 text-slate-400"></i>

                            </a>


                            {{-- VOLVER --}}
                            <a href="{{ route('customers.index') }}"
                                class="flex w-full items-center justify-between rounded-lg border
                                       border-slate-200 px-4 py-3 transition-all
                                       hover:border-slate-300 hover:bg-slate-50
                                       dark:border-zink-500 dark:hover:bg-zink-600">

                                <div class="flex items-center gap-3">

                                    <div class="flex size-9 items-center justify-center rounded-lg
                                                bg-slate-100 text-slate-500
                                                dark:bg-zink-600 dark:text-zink-300">

                                        <i data-lucide="arrow-left" class="size-4"></i>

                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-slate-700 dark:text-zink-100">
                                            Lista de clientes
                                        </p>

                                        <p class="text-xs text-slate-400 dark:text-zink-400">
                                            Volver al directorio
                                        </p>
                                    </div>

                                </div>

                                <i data-lucide="chevron-right"
                                    class="size-4 text-slate-400"></i>

                            </a>

                        </div>

                    </div>

                </div>


                {{-- ESTADO --}}
                <div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4
                            dark:border-green-500/20 dark:bg-green-500/5">

                    <div class="flex items-start gap-3">

                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full
                                    bg-green-500/10 text-green-500">

                            <i data-lucide="circle-check" class="size-5"></i>

                        </div>

                        <div>

                            <p class="text-sm font-semibold text-green-700 dark:text-green-400">
                                Cliente registrado
                            </p>

                            <p class="mt-1 text-xs leading-5 text-green-600/80 dark:text-green-400/70">
                                La información de este cliente se encuentra registrada
                                correctamente en el sistema.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- FUTURO: PROYECTOS / OFERTAS / FACTURAS --}}
        <div class="mt-5 card border-0 shadow-sm dark:border-zink-600">

            <div class="card-body">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-3">

                        <div class="flex size-10 items-center justify-center rounded-lg
                                    bg-sky-50 text-sky-600">

                            <i data-lucide="briefcase" class="size-5"></i>

                        </div>

                        <div>

                            <h6 class="font-semibold text-slate-800 dark:text-zink-100">
                                Gestión del cliente
                            </h6>

                            <p class="mt-0.5 text-xs text-slate-400 dark:text-zink-400">
                                Próximamente podrás consultar aquí sus ofertas,
                                proyectos y facturas.
                            </p>

                        </div>

                    </div>

                    <span class="inline-flex w-fit items-center rounded-full
                                 bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500
                                 dark:bg-zink-600 dark:text-zink-300">
                        <i data-lucide="clock-3" class="mr-1.5 size-3.5"></i>
                        Próximamente
                    </span>

                </div>

            </div>

        </div>

    </div>
@endsection
