@php
    $usuario = auth()->user();
    $nomencladorActive = request()->routeIs('provincias.*', 'municipios.*', 'unidades-medida.*');

    $grupos = [
        'Principal' => [
            [
                'label' => 'Dashboard',
                'icon' => 'layout-dashboard',
                'route' => 'dashboard',
                'active' => request()->routeIs('dashboard'),
            ],
        ],
        'Operación' => array_values(array_filter([
            [
                'label' => 'Clientes',
                'icon' => 'users',
                'route' => 'customers.index',
                'active' => request()->routeIs('customers.*'),
            ],
            $usuario?->can('manage-ofertas') ? [
                'label' => 'Ofertas',
                'icon' => 'file-text',
                'route' => 'ofertas.index',
                'active' => request()->routeIs('ofertas.*'),
            ] : null,
            [
                'label' => 'Productos',
                'icon' => 'package',
                'route' => 'productos.index',
                'active' => request()->routeIs('productos.*'),
            ],
        ])),
        'Inventario' => [
            [
                'label' => 'Existencias',
                'icon' => 'warehouse',
                'route' => 'inventario.index',
                'active' => request()->routeIs('inventario.index'),
            ],
            [
                'label' => 'Entradas',
                'icon' => 'arrow-down-to-line',
                'route' => 'entradas.index',
                'active' => request()->routeIs('entradas.*'),
            ],
            [
                'label' => 'Salidas',
                'icon' => 'arrow-up-from-line',
                'route' => 'salidas.index',
                'active' => request()->routeIs('salidas.*'),
            ],
            [
                'label' => 'Ajustes',
                'icon' => 'sliders-horizontal',
                'route' => 'ajustes.index',
                'active' => request()->routeIs('ajustes.*'),
            ],
        ],
        'Abastecimiento' => array_values(array_filter([
            $usuario?->can('manage-proveedores') ? [
                'label' => 'Proveedores',
                'icon' => 'truck',
                'route' => 'proveedores.index',
                'active' => request()->routeIs('proveedores.*'),
            ] : null,
            $usuario?->can('manage-productos') ? [
                'label' => 'Categorías',
                'icon' => 'tags',
                'route' => 'categorias.index',
                'active' => request()->routeIs('categorias.*'),
            ] : null,
            $usuario?->can('manage-productos') ? [
                'label' => 'Almacenes',
                'icon' => 'building-2',
                'route' => 'almacenes.index',
                'active' => request()->routeIs('almacenes.*'),
            ] : null,
        ])),
        'Administración' => array_values(array_filter([
            $usuario?->can('manage-nomencladores') ? [
                'label' => 'Servicios',
                'icon' => 'briefcase',
                'route' => 'servicios.index',
                'active' => request()->routeIs('servicios.*'),
            ] : null,
            $usuario?->can('manage-nomencladores') ? [
                'label' => 'Gastos',
                'icon' => 'receipt',
                'route' => 'gastos.index',
                'active' => request()->routeIs('gastos.*'),
            ] : null,
            $usuario?->can('manage-nomencladores') ? [
                'label' => 'Nomencladores',
                'icon' => 'list-tree',
                'active' => $nomencladorActive,
                'children' => [
                    [
                        'label' => 'Provincias',
                        'icon' => 'map',
                        'route' => 'provincias.index',
                        'active' => request()->routeIs('provincias.*'),
                    ],
                    [
                        'label' => 'Municipios',
                        'icon' => 'map-pin',
                        'route' => 'municipios.index',
                        'active' => request()->routeIs('municipios.*'),
                    ],
                    [
                        'label' => 'Unidades de medida',
                        'icon' => 'ruler',
                        'route' => 'unidades-medida.index',
                        'active' => request()->routeIs('unidades-medida.*'),
                    ],
                ],
            ] : null,
        ])),
    ];

    $grupos = array_filter($grupos);
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
            @foreach ($grupos as $titulo => $items)
                <p class="nova-nav-section">{{ $titulo }}</p>

                @foreach ($items as $item)
                    @if (isset($item['children']))
                        <div x-data="{ open: {{ $item['active'] ? 'true' : 'false' }} }">
                            <button type="button" @click="open = !open"
                                @class([
                                    'nova-nav-link nova-nav-link--button',
                                    'active' => $item['active'],
                                ])
                                :aria-expanded="open.toString()" aria-controls="nomencladores-menu">
                                <span class="nova-nav-icon">
                                    <i data-lucide="{{ $item['icon'] }}" class="size-[1.05rem]"></i>
                                </span>
                                <span class="nova-nav-label">{{ $item['label'] }}</span>
                                <i data-lucide="chevron-down" class="nova-nav-chevron" :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div id="nomencladores-menu" x-show="open" x-cloak class="nova-nav-sub">
                                @foreach ($item['children'] as $child)
                                    <a href="{{ route($child['route']) }}"
                                        @class(['nova-nav-sublink', 'active' => $child['active']])
                                        @if ($child['active']) aria-current="page" @endif>
                                        <i data-lucide="{{ $child['icon'] }}" class="size-3.5 shrink-0"></i>
                                        <span>{{ $child['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}" @class(['nova-nav-link', 'active' => $item['active']])
                            @if ($item['active']) aria-current="page" @endif>
                            <span class="nova-nav-icon">
                                <i data-lucide="{{ $item['icon'] }}" class="size-[1.05rem]"></i>
                            </span>
                            <span class="nova-nav-label">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            @endforeach
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
