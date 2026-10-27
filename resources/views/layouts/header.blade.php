@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))
        ->filter()
        ->map(fn (string $word): string => mb_substr($word, 0, 1))
        ->take(2)
        ->implode('');
    $pageTitle = trim(strip_tags($__env->yieldContent('titulo')));
@endphp

<header id="page-topbar"
    class="ltr:md:left-vertical-menu rtl:md:right-vertical-menu group-data-[sidebar-size=md]:ltr:md:left-vertical-menu-md group-data-[sidebar-size=md]:rtl:md:right-vertical-menu-md group-data-[sidebar-size=sm]:ltr:md:left-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:md:right-vertical-menu-sm group-data-[layout=horizontal]:ltr:left-0 group-data-[layout=horizontal]:rtl:right-0 fixed right-0 z-[1000] left-0 print:hidden group-data-[navbar=bordered]:m-4 group-data-[navbar=bordered]:[&.is-sticky]:mt-0 transition-all ease-linear duration-300 group-data-[navbar=hidden]:hidden group-data-[navbar=scroll]:absolute group/topbar group-data-[layout=horizontal]:z-[1004]">
    <div class="layout-width">
        <div
            class="mx-auto flex h-header items-center justify-between gap-3 border-b border-slate-200/70 bg-topbar px-4 shadow-sm shadow-slate-200/50 dark:border-zink-600 dark:bg-zink-700 dark:shadow-none">
            {{-- Control de navegación --}}
            <div class="flex min-w-0 items-center gap-2">
                <button type="button" id="topnav-hamburger-icon"
                    class="hamburger-icon inline-flex size-[37.5px] shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-nova-600 dark:text-zink-200 dark:hover:bg-zink-600 dark:hover:text-zink-50"
                    aria-label="Abrir o cerrar la navegación" aria-controls="navbar-nav">
                    <i data-lucide="panel-left" class="size-5"></i>
                </button>

                <span class="hidden h-5 w-px bg-slate-200 sm:block dark:bg-zink-600" aria-hidden="true"></span>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-700 dark:text-zink-100">
                        {{ $pageTitle !== '' ? $pageTitle : 'Panel de control' }}
                    </p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        TechNova Warehouse · Almacén principal
                    </p>
                </div>
            </div>

            {{-- Acciones y usuario --}}
            <div class="ms-auto flex shrink-0 items-center gap-2 sm:gap-3">
                <span
                    class="hidden items-center gap-1.5 rounded-full border border-emerald-200/70 bg-emerald-50/80 px-2.5 py-1 text-[11px] font-semibold text-emerald-800 lg:inline-flex">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    Almacén operativo
                </span>

                <a href="{{ route('dashboard') }}#alertas"
                    class="relative inline-flex size-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-nova-700 dark:text-zink-200 dark:hover:bg-zink-600"
                    aria-label="Ver alertas de stock y facturación">
                    <i data-lucide="bell" class="size-5"></i>
                    <span
                        class="absolute right-1.5 top-1.5 flex size-4 items-center justify-center rounded-full bg-amber-500 text-[9px] font-bold text-white ring-2 ring-white dark:ring-zink-700"
                        aria-hidden="true">2</span>
                </a>

                <span class="hidden h-6 w-px bg-slate-200 sm:block dark:bg-zink-600" aria-hidden="true"></span>

                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open"
                        class="flex items-center gap-2 rounded-xl border border-transparent px-1.5 py-1.5 text-left transition hover:border-slate-200 hover:bg-slate-50 sm:pr-2.5 dark:hover:border-zink-500 dark:hover:bg-zink-600"
                        :aria-expanded="open.toString()" aria-haspopup="menu" aria-label="Menú del usuario">
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-sky-600 text-sm font-semibold text-white shadow-sm">
                            {{ strtoupper($initials) }}
                        </span>

                        <span class="nova-user-meta min-w-0">
                            <span class="block truncate text-sm font-semibold text-slate-700 dark:text-zink-100">
                                {{ $user->name }}
                            </span>
                            <span class="block truncate text-xs text-slate-400 dark:text-zink-400">
                                {{ $user->roleLabel() }}
                            </span>
                        </span>

                        <i data-lucide="chevron-down" class="nova-user-chevron size-4 text-slate-400 transition"
                            :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open" x-cloak
                        class="absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-zink-500 dark:bg-zink-700"
                        role="menu">
                        <div class="border-b border-slate-100 px-4 py-3 dark:border-zink-500">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-sky-600 text-sm font-semibold text-white">
                                    {{ strtoupper($initials) }}
                                </span>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-700 dark:text-zink-100">
                                        {{ $user->name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500 dark:text-zink-300">
                                        {{ $user->email }}
                                    </p>
                                </div>
                            </div>

                            <span
                                class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-nova-50 px-2.5 py-1 text-xs font-medium text-nova-700 dark:bg-nova-500/15 dark:text-nova-300">
                                <span class="size-1.5 rounded-full bg-nova-500"></span>
                                {{ $user->roleLabel() }}
                            </span>
                        </div>

                        <div class="p-2">
                            <a href="{{ route('customers.index') }}" role="menuitem"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-nova-700 dark:text-zink-200 dark:hover:bg-zink-600">
                                <i data-lucide="users" class="size-4"></i>
                                Directorio de clientes
                            </a>

                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1 dark:border-zink-500">
                                @csrf

                                <button type="submit" role="menuitem"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-600 dark:text-zink-200 dark:hover:bg-rose-500/10 dark:hover:text-rose-300">
                                    <i data-lucide="log-out" class="size-4"></i>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
