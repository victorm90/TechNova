<!DOCTYPE html>
<html lang="es" class="light scroll-smooth">

<head>
    <meta charset="utf-8">
    <title>TechNova Warehouse | Sistema de almacén</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="TechNova Warehouse: inventario, ventas y facturación en un solo panel para tu almacén.">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/starcode2.css') }}">
    @vite(['resources/css/app.css'])
</head>

<body class="nova-product-bg font-public text-slate-700 antialiased">
    <a href="#producto" class="nova-skip-link">Saltar al producto</a>

    @php
        /*
         | Catálogo de demostración. Sustituir por el modelo de productos
         | cuando exista el módulo comercial en la base de datos.
         */
        $featuredProducts = [
            [
                'name' => 'TechNova Warehouse',
                'category' => 'Gestión de almacén',
                'description' => 'Inventario, ventas y facturación en un solo panel.',
                'price' => 149,
                'image' => 'warehouse-dashboard.png',
                'status' => 'ok',
                'status_label' => 'Disponible',
            ],
            [
                'name' => 'TechNova POS',
                'category' => 'Punto de venta',
                'description' => 'Cobro rápido con control de caja y turnos.',
                'price' => 89,
                'image' => 'product-home.png',
                'status' => 'ok',
                'status_label' => 'Disponible',
            ],
            [
                'name' => 'TechNova Facturación',
                'category' => 'Facturación',
                'description' => 'Facturas, cobros y estados de cuenta automatizados.',
                'price' => 119,
                'image' => 'order-overview.jpg',
                'status' => 'low',
                'status_label' => 'Últimas licencias',
            ],
            [
                'name' => 'TechNova Reportes',
                'category' => 'Analítica',
                'description' => 'Indicadores de rotación, margen y actividad.',
                'price' => 69,
                'image' => 'dashboard.png',
                'status' => 'ok',
                'status_label' => 'Disponible',
            ],
            [
                'name' => 'TechNova Compras',
                'category' => 'Abastecimiento',
                'description' => 'Órdenes de compra y seguimiento de proveedores.',
                'price' => 79,
                'image' => 'img-01.png',
                'status' => 'ok',
                'status_label' => 'Disponible',
            ],
            [
                'name' => 'TechNova CRM',
                'category' => 'Clientes',
                'description' => 'Directorio, historial y seguimiento comercial.',
                'price' => 59,
                'image' => 'img-02.png',
                'status' => 'out',
                'status_label' => 'Sin stock',
            ],
        ];
    @endphp

    {{-- ============================================================
         NAVEGACIÓN
         ============================================================ --}}
    <header class="sticky top-0 z-50 border-b border-white/70 bg-white/90 backdrop-blur-xl">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-5">
            <a href="{{ route('product.landing') }}" class="flex items-center gap-3" aria-label="TechNova, inicio">
                <span class="inline-flex rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-slate-200/70">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="TechNova" class="h-9 w-auto object-contain">
                </span>
                <span class="hidden leading-tight sm:block">
                    <span class="block text-sm font-bold tracking-tight text-slate-900">TechNova</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                        Warehouse
                    </span>
                </span>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 md:flex"
                aria-label="Navegación del producto">
                <a href="#producto" class="transition hover:text-nova-700">Producto</a>
                <a href="#caracteristicas" class="transition hover:text-nova-700">Características</a>
                <a href="#especificaciones" class="transition hover:text-nova-700">Especificaciones</a>
                <a href="#destacados" class="transition hover:text-nova-700">Destacados</a>
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}"
                    class="btn hidden border-slate-200 bg-white px-4 py-2 text-slate-700 hover:border-nova-200 hover:text-nova-700 sm:inline-flex">
                    Iniciar sesión
                </a>

                <a href="mailto:soporte@technova.test?subject=Solicitud%20de%20demo"
                    class="btn inline-flex items-center border-nova-500 bg-nova-500 px-4 py-2 text-white shadow-sm hover:border-nova-600 hover:bg-nova-600">
                    <i data-lucide="calendar-check" class="me-1.5 size-4" aria-hidden="true"></i>
                    Solicitar demo
                </a>

                <button type="button" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav"
                    class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 md:hidden">
                    <span class="sr-only">Abrir menú</span>
                    <i data-lucide="menu" class="size-5" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <nav id="mobile-nav" data-mobile-nav
            class="hidden border-t border-slate-200/70 bg-white px-5 py-4 md:hidden" aria-label="Navegación móvil">
            <div class="flex flex-col gap-1 text-sm font-medium text-slate-700">
                <a href="#producto" class="rounded-lg px-3 py-2 hover:bg-slate-50">Producto</a>
                <a href="#caracteristicas" class="rounded-lg px-3 py-2 hover:bg-slate-50">Características</a>
                <a href="#especificaciones" class="rounded-lg px-3 py-2 hover:bg-slate-50">Especificaciones</a>
                <a href="#destacados" class="rounded-lg px-3 py-2 hover:bg-slate-50">Productos destacados</a>
                <a href="{{ route('login') }}" class="mt-2 rounded-lg px-3 py-2 font-semibold text-nova-700 hover:bg-nova-50">
                    Iniciar sesión
                </a>
            </div>
        </nav>
    </header>

    <main>
        <div class="mx-auto max-w-7xl px-5 pt-6">

            {{-- MIGAS --}}
            <nav class="flex flex-wrap items-center gap-2 text-xs text-slate-500" aria-label="Ruta de navegación">
                <a href="{{ route('product.landing') }}" class="transition hover:text-nova-700">Inicio</a>
                <i data-lucide="chevron-right" class="size-3" aria-hidden="true"></i>
                <span>Software</span>
                <i data-lucide="chevron-right" class="size-3" aria-hidden="true"></i>
                <span class="font-semibold text-slate-700">TechNova Warehouse</span>
            </nav>

            {{-- ====================================================
                 PRODUCTO PRINCIPAL
                 ==================================================== --}}
            <section id="producto" class="mt-6 grid gap-8 lg:grid-cols-12 lg:gap-10" aria-labelledby="producto-title">
                <div class="lg:col-span-7">
                    <div class="nova-product-media">
                        <img id="productImage" src="{{ asset('assets/images/warehouse-dashboard.png') }}"
                            alt="Vista del panel de TechNova Warehouse con indicadores de inventario y ventas"
                            width="1200" height="900">

                        <span
                            class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 shadow-sm ring-1 ring-inset ring-amber-200">
                            <i data-lucide="star" class="size-3.5 fill-amber-400 text-amber-500" aria-hidden="true"></i>
                            Más vendido
                        </span>

                        <span
                            class="absolute right-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-600 shadow-sm ring-1 ring-inset ring-slate-200">
                            <i data-lucide="image" class="size-3" aria-hidden="true"></i>
                            Vista de demostración
                        </span>
                    </div>

                    <div class="nova-product-thumbs mt-3" role="group" aria-label="Miniaturas del producto">
                        <button type="button" class="nova-thumb is-active" aria-pressed="true"
                            data-gallery-src="{{ asset('assets/images/warehouse-dashboard.png') }}"
                            data-gallery-alt="Vista general del panel de TechNova Warehouse">
                            <img src="{{ asset('assets/images/warehouse-dashboard.png') }}" alt="" loading="lazy">
                        </button>

                        <button type="button" class="nova-thumb" aria-pressed="false"
                            data-gallery-src="{{ asset('assets/images/dashboard.png') }}"
                            data-gallery-alt="Panel de indicadores y reportes de TechNova Warehouse">
                            <img src="{{ asset('assets/images/dashboard.png') }}" alt="" loading="lazy">
                        </button>

                        <button type="button" class="nova-thumb" aria-pressed="false"
                            data-gallery-src="{{ asset('assets/images/order-overview.jpg') }}"
                            data-gallery-alt="Vista de facturación y pedidos de TechNova Warehouse">
                            <img src="{{ asset('assets/images/order-overview.jpg') }}" alt="" loading="lazy">
                        </button>

                        <button type="button" class="nova-thumb" aria-pressed="false"
                            data-gallery-src="{{ asset('assets/images/product-home.png') }}"
                            data-gallery-alt="Vista del catálogo de productos del almacén">
                            <img src="{{ asset('assets/images/product-home.png') }}" alt="" loading="lazy">
                        </button>
                    </div>

                    <p class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                        <i data-lucide="mouse-pointer-click" class="size-4" aria-hidden="true"></i>
                        Selecciona una miniatura para ampliar la vista del panel.
                    </p>
                </div>

                <div class="lg:col-span-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-nova-700">
                        Software de gestión de almacén
                    </p>

                    <h1 id="producto-title" class="mt-2 text-3xl font-semibold leading-tight text-slate-900 sm:text-4xl">
                        TechNova Warehouse
                    </h1>

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <span class="inline-flex items-center gap-1">
                            <i data-lucide="star" class="size-4 fill-amber-400 text-amber-500" aria-hidden="true"></i>
                            <span class="font-semibold text-slate-800">4,9</span>
                        </span>
                        <a href="#destacados" class="underline-offset-2 hover:underline">128 valoraciones</a>
                        <span class="text-slate-300" aria-hidden="true">·</span>
                        <span>SKU TN-WH-01</span>
                    </div>

                    <p class="mt-4 text-sm leading-7 text-slate-600">
                        Controla existencias, ventas y facturación desde un panel claro y centralizado.
                        Diseñado para equipos de almacén que necesitan visibilidad inmediata de su
                        operación y alertas antes de que un problema afecte al negocio.
                    </p>

                    <ul class="mt-4 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" aria-hidden="true"></i>
                            Alertas de stock y cobros
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" aria-hidden="true"></i>
                            Comparativas por periodo
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" aria-hidden="true"></i>
                            Facturación en un clic
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" aria-hidden="true"></i>
                            Multiusuario con roles
                        </li>
                    </ul>

                    {{-- PRECIO Y DISPONIBILIDAD --}}
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-end gap-1.5">
                                    <p class="nova-product-price">
                                        <span id="productPrice" data-monthly="$149" data-annual="$1.490">$149</span>
                                        <small id="productPeriod">/ mes</small>
                                    </p>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    Precio de demostración · impuestos no incluidos.
                                </p>
                            </div>

                            <x-status-badge status="ok" label="24 licencias disponibles" />
                        </div>

                        {{-- DISPONIBILIDAD --}}
                        <div class="mt-4 border-t border-slate-100 pt-4">
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span class="font-semibold text-slate-700">Disponibilidad de stock</span>
                                <span class="text-slate-500">24 de 40 licencias</span>
                            </div>

                            <div class="nova-stock-track mt-2" role="img" aria-label="60 por ciento de licencias disponibles">
                                <span style="width: 60%"></span>
                            </div>

                            <p class="mt-1.5 text-[11px] text-slate-500">
                                Activación inmediata · reposición estimada en 3 días.
                            </p>
                        </div>

                        {{-- PLANES --}}
                        <fieldset class="mt-4 border-t border-slate-100 pt-4">
                            <legend class="mb-2 text-xs font-semibold text-slate-700">Modalidad de licencia</legend>

                            <div class="grid gap-2 sm:grid-cols-2">
                                <label class="nova-option">
                                    <input type="radio" name="plan" value="monthly" data-plan-price="$149"
                                        data-plan-period="/ mes" checked>
                                    <span>
                                        <span class="block text-sm font-semibold text-slate-800">Mensual</span>
                                        <span class="block text-[11px] text-slate-500">Cancela cuando quieras</span>
                                    </span>
                                </label>

                                <label class="nova-option">
                                    <input type="radio" name="plan" value="annual" data-plan-price="$1.490"
                                        data-plan-period="/ año">
                                    <span>
                                        <span class="block text-sm font-semibold text-slate-800">Anual</span>
                                        <span class="block text-[11px] text-emerald-700">Ahorra 2 meses</span>
                                    </span>
                                </label>
                            </div>
                        </fieldset>

                        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                            <a href="{{ route('login') }}"
                                class="btn inline-flex flex-1 items-center justify-center border-nova-500 bg-nova-500 px-5 py-3 text-center text-white shadow-sm hover:border-nova-600 hover:bg-nova-600">
                                <i data-lucide="arrow-right" class="me-1.5 size-4" aria-hidden="true"></i>
                                Comenzar ahora
                            </a>

                            <a href="mailto:soporte@technova.test?subject=Solicitud%20de%20demo"
                                class="btn inline-flex flex-1 items-center justify-center border-slate-200 bg-white px-5 py-3 text-center text-slate-700 shadow-sm hover:border-nova-200 hover:text-nova-700">
                                <i data-lucide="calendar-check" class="me-1.5 size-4" aria-hidden="true"></i>
                                Solicitar demo
                            </a>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] text-slate-600">
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="size-3.5 text-emerald-600" aria-hidden="true"></i>
                                Soporte incluido
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="refresh-cw" class="size-3.5 text-sky-700" aria-hidden="true"></i>
                                Actualizaciones
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="clock-3" class="size-3.5 text-amber-700" aria-hidden="true"></i>
                                Implementación guiada
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="users" class="size-3.5 text-nova-700" aria-hidden="true"></i>
                                Multiusuario
                            </span>
                        </div>
                    </div>

                    {{-- METADATOS --}}
                    <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-200/70 py-1.5">
                            <dt class="text-slate-500">Categoría</dt>
                            <dd class="font-semibold text-slate-700">Almacén</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-b border-slate-200/70 py-1.5">
                            <dt class="text-slate-500">Idioma</dt>
                            <dd class="font-semibold text-slate-700">Español</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-b border-slate-200/70 py-1.5">
                            <dt class="text-slate-500">Despliegue</dt>
                            <dd class="font-semibold text-slate-700">Nube</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-b border-slate-200/70 py-1.5">
                            <dt class="text-slate-500">Actualización</dt>
                            <dd class="font-semibold text-slate-700">Automática</dd>
                        </div>
                    </dl>
                </div>
            </section>

            {{-- ====================================================
                 ESTADÍSTICAS REALES
                 ==================================================== --}}
            <section class="mt-8 grid grid-cols-2 gap-3 rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm backdrop-blur sm:grid-cols-4"
                aria-label="Datos del sistema">
                @foreach ([
                    ['label' => 'Clientes registrados', 'value' => $totalCustomers, 'icon' => 'users'],
                    ['label' => 'Servicios activos', 'value' => $totalServicios, 'icon' => 'briefcase'],
                    ['label' => 'Provincias', 'value' => $totalProvincias, 'icon' => 'map'],
                    ['label' => 'Municipios', 'value' => $totalMunicipios, 'icon' => 'map-pin'],
                ] as $stat)
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-nova-50 text-nova-700"
                            aria-hidden="true">
                            <i data-lucide="{{ $stat['icon'] }}" class="size-4"></i>
                        </span>
                        <div>
                            <p class="text-lg font-semibold text-slate-900">{{ number_format($stat['value']) }}</p>
                            <p class="text-[11px] text-slate-500">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </section>

            {{-- ====================================================
                 CARACTERÍSTICAS
                 ==================================================== --}}
            <section id="caracteristicas" class="border-t border-slate-200/70 py-14" aria-labelledby="caracteristicas-title">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-nova-700">Características</p>
                    <h2 id="caracteristicas-title" class="mt-2 text-2xl font-semibold text-slate-900">
                        Todo lo que necesitas para operar tu almacén
                    </h2>
                    <p class="mt-2 text-sm leading-7 text-slate-600">
                        Una base ordenada para inventario, ventas y facturación, con la información
                        crítica siempre a la vista.
                    </p>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="nova-feature-card">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                            <i data-lucide="package" class="size-5" aria-hidden="true"></i>
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-800">Inventario</h3>
                        <p class="mt-1.5 text-sm leading-6 text-slate-600">
                            Existencias, mínimos, categorías y valor del stock en tiempo real.
                        </p>
                    </div>

                    <div class="nova-feature-card">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                            <i data-lucide="shopping-cart" class="size-5" aria-hidden="true"></i>
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-800">Ventas</h3>
                        <p class="mt-1.5 text-sm leading-6 text-slate-600">
                            Registro de pedidos, clientes y comparativos por periodo.
                        </p>
                    </div>

                    <div class="nova-feature-card">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-800">
                            <i data-lucide="file-text" class="size-5" aria-hidden="true"></i>
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-800">Facturación</h3>
                        <p class="mt-1.5 text-sm leading-6 text-slate-600">
                            Facturas pagadas, pendientes y vencidas con alertas visibles.
                        </p>
                    </div>

                    <div class="nova-feature-card">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                            <i data-lucide="bar-chart" class="size-5" aria-hidden="true"></i>
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-800">Reportes</h3>
                        <p class="mt-1.5 text-sm leading-6 text-slate-600">
                            Indicadores de rotación, actividad y evolución del negocio.
                        </p>
                    </div>
                </div>
            </section>

            {{-- ====================================================
                 DESCRIPCIÓN Y ESPECIFICACIONES
                 ==================================================== --}}
            <section class="grid gap-5 border-t border-slate-200/70 py-14 lg:grid-cols-2" aria-label="Detalle del producto">
                <div id="descripcion" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-semibold text-slate-900">Descripción del producto</h2>

                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        TechNova Warehouse centraliza la información operativa de tu almacén.
                        Sustituye hojas de cálculo y registros dispersos por una vista única con
                        inventario, ventas y facturación.
                    </p>

                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        El panel está pensado para supervisores y equipos administrativos que
                        necesitan detectar stock bajo, cobrar facturas pendientes y revisar
                        el comportamiento del negocio sin cambiar de herramienta.
                    </p>

                    <h3 class="mt-5 text-sm font-semibold text-slate-800">Incluye</h3>
                    <ul class="mt-3 space-y-2.5 text-sm text-slate-600">
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" aria-hidden="true"></i>
                            Panel con KPIs y comparativas contra el periodo anterior.
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" aria-hidden="true"></i>
                            Alertas de stock bajo y facturas pendientes o vencidas.
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" aria-hidden="true"></i>
                            Tablas con búsqueda, paginación y estados claros.
                        </li>
                    </ul>

                    <p class="mt-5 text-xs leading-5 text-slate-500">
                        ¿Necesitas recuperar tu acceso?
                        <a href="mailto:soporte@technova.test?subject=Recuperaci%C3%B3n%20de%20acceso"
                            class="font-semibold text-nova-700 underline-offset-2 hover:underline">
                            Contacta a soporte
                        </a>.
                    </p>
                </div>

                <div id="especificaciones" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-semibold text-slate-900">Especificaciones</h2>

                    <dl class="mt-4 divide-y divide-slate-100 text-sm">
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-slate-500">Licencia</dt>
                            <dd class="font-semibold text-slate-700">SaaS · 24 disponibles</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-slate-500">Modalidad</dt>
                            <dd class="font-semibold text-slate-700">Suscripción mensual o anual</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-slate-500">Usuarios</dt>
                            <dd class="font-semibold text-slate-700">Multiusuario con roles</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-slate-500">Soporte</dt>
                            <dd class="font-semibold text-slate-700">Incluido</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-slate-500">Actualizaciones</dt>
                            <dd class="font-semibold text-slate-700">Automáticas</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-slate-500">Despliegue</dt>
                            <dd class="font-semibold text-slate-700">En la nube</dd>
                        </div>
                    </dl>

                    <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-800 ring-1 ring-inset ring-amber-200/60">
                        Precio y disponibilidad de demostración. Ajustar con la información comercial definitiva.
                    </p>
                </div>
            </section>

            {{-- ====================================================
                 PRODUCTOS DESTACADOS (SLIDER)
                 ==================================================== --}}
            <section id="destacados" class="border-t border-slate-200/70 py-14" aria-labelledby="destacados-title">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-nova-700">Catálogo</p>
                            <span class="nova-demo-badge">Datos de demostración</span>
                        </div>

                        <h2 id="destacados-title" class="mt-2 text-2xl font-semibold text-slate-900">
                            Productos destacados
                        </h2>
                        <p class="mt-2 text-sm leading-7 text-slate-600">
                            Complementa tu operación con módulos que se integran al mismo panel.
                        </p>
                    </div>

                    <div class="flex items-center gap-2" aria-label="Controles del carrusel">
                        <button type="button" class="nova-slider__nav" data-slider-prev aria-label="Producto anterior">
                            <i data-lucide="chevron-left" class="size-4" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="nova-slider__nav" data-slider-next aria-label="Producto siguiente">
                            <i data-lucide="chevron-right" class="size-4" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="nova-slider mt-6">
                    <div class="nova-slider__track" id="featuredSlider" tabindex="0"
                        aria-label="Carrusel de productos destacados">
                        @foreach ($featuredProducts as $index => $product)
                            <article class="nova-slider__item" data-slide="{{ $index }}">
                                <div
                                    class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                    <div class="relative overflow-hidden bg-slate-100">
                                        <img src="{{ asset('assets/images/'.$product['image']) }}"
                                            alt="Vista del producto {{ $product['name'] }}"
                                            class="aspect-[16/10] w-full object-cover object-top transition duration-300 group-hover:scale-[1.03]"
                                            loading="lazy">

                                        @if ($index === 0)
                                            <span
                                                class="absolute left-3 top-3 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-amber-800 ring-1 ring-inset ring-amber-200">
                                                Más vendido
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex grow flex-col p-4">
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-nova-700">
                                            {{ $product['category'] }}
                                        </p>

                                        <h3 class="mt-1 text-sm font-semibold text-slate-900">
                                            {{ $product['name'] }}
                                        </h3>

                                        <p class="mt-1 text-xs leading-5 text-slate-600">
                                            {{ $product['description'] }}
                                        </p>

                                        <div class="mt-3 flex items-center justify-between gap-2">
                                            <p class="text-base font-semibold text-slate-900">
                                                ${{ number_format($product['price'], 0, ',', '.') }}
                                                <span class="text-[11px] font-medium text-slate-500">/ mes</span>
                                            </p>

                                            <x-status-badge :status="$product['status']" :label="$product['status_label']" />
                                        </div>

                                        <a href="#producto"
                                            class="btn mt-3 inline-flex w-full items-center justify-center border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:border-nova-300 hover:bg-nova-50 hover:text-nova-800">
                                            Ver detalle
                                            <i data-lucide="arrow-right" class="ms-1.5 size-3.5" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="nova-slider__dots" data-slider-dots aria-label="Seleccionar producto visible">
                        @foreach ($featuredProducts as $index => $product)
                            <button type="button" data-slide-dot="{{ $index }}"
                                aria-label="Ir al producto {{ $index + 1 }}: {{ $product['name'] }}"
                                @if ($index === 0) aria-current="true" @endif></button>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ====================================================
                 CTA FINAL
                 ==================================================== --}}
            <section class="pb-16">
                <div class="nova-hero-dashboard flex flex-col items-start justify-between gap-5 md:flex-row md:items-center">
                    <div>
                        <p class="relative text-xs font-semibold uppercase tracking-[0.18em] text-nova-800">Empieza hoy</p>
                        <h2 class="relative mt-2 text-2xl font-semibold text-slate-900 sm:text-3xl">
                            Ordena tu almacén en una sola vista
                        </h2>
                        <p class="relative mt-2 max-w-xl text-sm text-slate-600">
                            Accede al panel y revisa inventario, ventas y facturación desde el primer día.
                        </p>
                    </div>

                    <a href="{{ route('login') }}"
                        class="btn relative inline-flex shrink-0 items-center border-nova-500 bg-nova-500 px-5 py-3 text-white shadow-sm hover:border-nova-600 hover:bg-nova-600">
                        Iniciar sesión
                        <i data-lucide="arrow-right" class="ms-1.5 size-4" aria-hidden="true"></i>
                    </a>
                </div>
            </section>
        </div>
    </main>

    {{-- ============================================================
         PIE
         ============================================================ --}}
    <footer class="border-t border-slate-200/70 bg-white/70 py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-slate-200/70">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="TechNova" class="h-8 w-auto">
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">TechNova Warehouse</p>
                        <p class="text-xs text-slate-500">Inventario · Ventas · Facturación</p>
                    </div>
                </div>

                <nav class="flex flex-wrap gap-4 text-sm text-slate-600" aria-label="Enlaces del pie">
                    <a href="#producto" class="hover:text-nova-700">Producto</a>
                    <a href="#caracteristicas" class="hover:text-nova-700">Características</a>
                    <a href="#destacados" class="hover:text-nova-700">Destacados</a>
                    <a href="{{ route('login') }}" class="font-semibold text-nova-700 hover:text-nova-900">Acceder</a>
                </nav>
            </div>

            <div class="flex flex-col gap-2 border-t border-slate-200/70 pt-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ date('Y') }} TechNova · Sistema de Almacén</p>
                <p class="text-slate-500">Producto de demostración para gestión de almacenes</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('assets/libs/lucide/umd/lucide.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                lucide.createIcons();
            }

            /* Menú móvil */
            const navToggle = document.querySelector('[data-nav-toggle]');
            const mobileNav = document.querySelector('[data-mobile-nav]');

            if (navToggle && mobileNav) {
                navToggle.addEventListener('click', function () {
                    const isOpen = !mobileNav.classList.contains('hidden');
                    mobileNav.classList.toggle('hidden', isOpen);
                    navToggle.setAttribute('aria-expanded', String(!isOpen));
                });

                mobileNav.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        mobileNav.classList.add('hidden');
                        navToggle.setAttribute('aria-expanded', 'false');
                    });
                });
            }

            /* Galería del producto */
            const mainImage = document.getElementById('productImage');
            const thumbs = document.querySelectorAll('[data-gallery-src]');

            thumbs.forEach(function (thumb) {
                thumb.addEventListener('click', function () {
                    if (!mainImage) {
                        return;
                    }

                    mainImage.src = thumb.dataset.gallerySrc;
                    mainImage.alt = thumb.dataset.galleryAlt || mainImage.alt;

                    thumbs.forEach(function (item) {
                        item.classList.toggle('is-active', item === thumb);
                        item.setAttribute('aria-pressed', String(item === thumb));
                    });
                });
            });

            /* Planes y precio */
            const price = document.getElementById('productPrice');
            const period = document.getElementById('productPeriod');

            document.querySelectorAll('input[name="plan"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    if (!price || !period) {
                        return;
                    }

                    price.textContent = input.dataset.planPrice;
                    period.textContent = input.dataset.planPeriod;
                });
            });

            /* Carrusel de productos destacados */
            const slider = document.getElementById('featuredSlider');
            const prev = document.querySelector('[data-slider-prev]');
            const next = document.querySelector('[data-slider-next]');
            const dots = [...document.querySelectorAll('[data-slide-dot]')];

            if (!slider || !prev || !next) {
                return;
            }

            const step = function () {
                const first = slider.querySelector('[data-slide]');
                return first ? first.getBoundingClientRect().width + 16 : 320;
            };

            const currentIndex = function () {
                return Math.round(slider.scrollLeft / step());
            };

            const updateControls = function () {
                prev.disabled = slider.scrollLeft <= 4;
                next.disabled = slider.scrollLeft >= slider.scrollWidth - slider.clientWidth - 4;

                const index = Math.min(dots.length - 1, currentIndex());
                dots.forEach(function (dot, i) {
                    if (i === index) {
                        dot.setAttribute('aria-current', 'true');
                    } else {
                        dot.removeAttribute('aria-current');
                    }
                });
            };

            prev.addEventListener('click', function () {
                slider.scrollBy({ left: -step(), behavior: 'smooth' });
            });

            next.addEventListener('click', function () {
                slider.scrollBy({ left: step(), behavior: 'smooth' });
            });

            dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    slider.scrollTo({ left: Number(dot.dataset.slideDot) * step(), behavior: 'smooth' });
                });
            });

            slider.addEventListener('scroll', updateControls, { passive: true });
            window.addEventListener('resize', updateControls);
            updateControls();
        });
    </script>
</body>

</html>
