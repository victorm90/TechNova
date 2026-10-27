@php
    $navItems = [
        [
            'label' => 'Dashboard',
            'icon' => 'layout-dashboard',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
        ],
        [
            'label' => 'Clientes',
            'icon' => 'users',
            'route' => 'customers.index',
            'active' => request()->routeIs('customers.*'),
        ],
    ];

    $catalogItems = [];

    if (auth()->user()?->can('manage-nomencladores')) {
        $catalogItems = [
            [
                'label' => 'Servicios',
                'icon' => 'briefcase',
                'route' => 'servicios.index',
                'active' => request()->routeIs('servicios.*'),
            ],
        ];
    }

    $nomencladorActive = request()->routeIs('provincias.*', 'municipios.*', 'unidades-medida.*');
@endphp

<aside class="app-menu bg-vertical-menu fixed bottom-0 top-0 z-[1003] hidden w-vertical-menu border-vertical-menu-border transition-all duration-150 ease-linear md:block print:hidden"
    aria-label="Navegación principal">
    <div class="flex h-full flex-col">

        {{-- MARCA --}}
        <a href="{{ route('dashboard') }}" class="nova-sidebar-brand" aria-label="TechNova Warehouse, ir al dashboard">
            <span class="nova-sidebar-brand__mark">
                <img src="{{ asset('assets/images/logo.png') }}" alt="" class="h-8 w-8 object-contain">
            </span>

            <span class="nova-sidebar-brand__text">
                <span class="nova-sidebar-brand__title">TechNova</span>
                <span class="nova-sidebar-brand__subtitle">Warehouse</span>
            </span>
        </a>

        {{-- NAVEGACIÓN --}}
        <nav id="navbar-nav" class="min-h-0 flex-1 overflow-y-auto pb-2" aria-label="Secciones del sistema">
            <p class="nova-nav-section">Operación</p>

            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" @class(['nova-nav-link', 'active' => $item['active']])
                    @if ($item['active']) aria-current="page" @endif>
                    <span class="nova-nav-icon">
                        <i data-lucide="{{ $item['icon'] }}" class="size-[1.05rem]"></i>
                    </span>
                    <span class="nova-nav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach

            @if ($catalogItems !== [] || auth()->user()?->can('manage-nomencladores'))
                <p class="nova-nav-section">Catálogo</p>
            @endif

            @foreach ($catalogItems as $item)
                <a href="{{ route($item['route']) }}" @class(['nova-nav-link', 'active' => $item['active']])
                    @if ($item['active']) aria-current="page" @endif>
                    <span class="nova-nav-icon">
                        <i data-lucide="{{ $item['icon'] }}" class="size-[1.05rem]"></i>
                    </span>
                    <span class="nova-nav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach

            @can('manage-nomencladores')
                <div x-data="{ open: {{ $nomencladorActive ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                        @class([
                            'nova-nav-link nova-nav-link--button',
                            'active' => $nomencladorActive,
                        ])
                        :aria-expanded="open.toString()" aria-controls="nomencladores-menu">
                        <span class="nova-nav-icon">
                            <i data-lucide="list-tree" class="size-[1.05rem]"></i>
                        </span>
                        <span class="nova-nav-label">Nomencladores</span>
                        <i data-lucide="chevron-down" class="nova-nav-chevron" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div id="nomencladores-menu" x-show="open" x-cloak class="nova-nav-sub">
                        <a href="{{ route('provincias.index') }}"
                            @class(['nova-nav-sublink', 'active' => request()->routeIs('provincias.*')])
                            @if (request()->routeIs('provincias.*')) aria-current="page" @endif>
                            <i data-lucide="map" class="size-3.5 shrink-0"></i>
                            <span>Provincias</span>
                        </a>

                        <a href="{{ route('municipios.index') }}"
                            @class(['nova-nav-sublink', 'active' => request()->routeIs('municipios.*')])
                            @if (request()->routeIs('municipios.*')) aria-current="page" @endif>
                            <i data-lucide="map-pin" class="size-3.5 shrink-0"></i>
                            <span>Municipios</span>
                        </a>

                        <a href="{{ route('unidades-medida.index') }}"
                            @class(['nova-nav-sublink', 'active' => request()->routeIs('unidades-medida.*')])
                            @if (request()->routeIs('unidades-medida.*')) aria-current="page" @endif>
                            <i data-lucide="ruler" class="size-3.5 shrink-0"></i>
                            <span>Unidades de medida</span>
                        </a>
                    </div>
                </div>
            @endcan
        </nav>

        {{-- ESTADO DEL ALMACÉN --}}
        <div class="nova-sidebar-footer">
            <p class="nova-sidebar-footer__label">
                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                Almacén principal
            </p>
            <p class="nova-sidebar-footer__value">Operativo · datos demo</p>

            <a href="{{ route('dashboard') }}#alertas"
                class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-nova-700 transition hover:text-nova-900 dark:text-nova-300">
                Ver alertas
                <i data-lucide="arrow-right" class="size-3"></i>
            </a>
        </div>
    </div>
</aside>
