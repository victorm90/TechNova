<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <meta charset="utf-8">
    <title>Iniciar sesión | TechNova Warehouse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Accede al panel de gestión de almacén de TechNova.">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/starcode2.css') }}">
    @vite(['resources/css/app.css'])
</head>

<body class="nova-auth-bg font-public text-slate-700 antialiased">
    <a href="#login-form" class="nova-skip-link">Saltar al formulario</a>

    <main class="grid min-h-screen lg:grid-cols-[1.02fr_1fr]">

        {{-- ========================================================
             PANEL DE MARCA
             ======================================================== --}}
        <section class="nova-auth-brand relative hidden flex-col justify-between p-10 lg:flex xl:p-14"
            aria-label="Información de TechNova Warehouse">
            <div class="relative">
                <a href="{{ route('product.landing') }}" class="inline-flex items-center gap-3"
                    aria-label="TechNova, volver al inicio">
                    <span class="inline-flex rounded-2xl bg-white p-2.5 shadow-sm ring-1 ring-white/80">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="TechNova"
                            class="h-16 w-auto object-contain">
                    </span>
                    <span class="leading-tight">
                        <span class="block text-base font-bold tracking-tight text-slate-900">TechNova</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-600">
                            Warehouse
                        </span>
                    </span>
                </a>

                <span
                    class="mt-10 inline-flex items-center gap-2 rounded-full border border-white/80 bg-white/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-nova-800 shadow-sm">
                    <span class="size-1.5 rounded-full bg-emerald-600"></span>
                    Sistema de almacén
                </span>

                <h1 class="mt-4 max-w-md text-3xl font-semibold leading-tight text-slate-900 xl:text-4xl">
                    Todo tu almacén,
                    <span class="text-nova-700">en un solo lugar.</span>
                </h1>

                <p class="mt-4 max-w-md text-sm leading-7 text-slate-700">
                    Gestiona inventario, ventas y facturación desde un panel claro,
                    rápido y pensado para el trabajo diario de tu equipo.
                </p>

                <ul class="mt-8 max-w-md space-y-3 text-sm text-slate-700">
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-white/80 text-emerald-700 shadow-sm">
                            <i data-lucide="package" class="size-4" aria-hidden="true"></i>
                        </span>
                        Inventario y stock bajo bajo control
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-white/80 text-sky-700 shadow-sm">
                            <i data-lucide="shopping-cart" class="size-4" aria-hidden="true"></i>
                        </span>
                        Ventas y facturación centralizadas
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-white/80 text-amber-700 shadow-sm">
                            <i data-lucide="users" class="size-4" aria-hidden="true"></i>
                        </span>
                        Clientes y nomencladores ordenados
                    </li>
                </ul>

                <div class="mt-8 grid max-w-md grid-cols-3 gap-2.5">
                    <div class="nova-auth-stat">
                        <p class="text-xs font-semibold text-slate-800">Tiempo real</p>
                        <p class="mt-0.5 text-[11px] text-slate-600">Stock y ventas</p>
                    </div>
                    <div class="nova-auth-stat">
                        <p class="text-xs font-semibold text-slate-800">Con roles</p>
                        <p class="mt-0.5 text-[11px] text-slate-600">Accesos seguros</p>
                    </div>
                    <div class="nova-auth-stat">
                        <p class="text-xs font-semibold text-slate-800">En la nube</p>
                        <p class="mt-0.5 text-[11px] text-slate-600">Sin instalación</p>
                    </div>
                </div>
            </div>

            <div class="relative mt-10 flex items-center justify-between gap-4 text-xs text-slate-600">
                <p>© {{ date('Y') }} TechNova · Sistema de Almacén</p>
                <span class="inline-flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="size-3.5" aria-hidden="true"></i>
                    Conexión segura
                </span>
            </div>
        </section>

        {{-- ========================================================
             FORMULARIO
             ======================================================== --}}
        <section class="flex items-center justify-center px-4 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <a href="{{ route('product.landing') }}" class="mb-8 inline-flex lg:hidden"
                    aria-label="TechNova, volver al inicio">
                    <span class="inline-flex rounded-2xl bg-white p-2 shadow-sm ring-1 ring-slate-200/70">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="TechNova"
                            class="h-14 w-auto object-contain">
                    </span>
                </a>

                <div class="nova-auth-card p-6 sm:p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-nova-700">
                        Acceso al panel
                    </p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">
                        Bienvenido de nuevo
                    </h2>
                    <p class="mt-1.5 text-sm text-slate-600">
                        Ingresa tus credenciales para continuar.
                    </p>

                    @if (session('status'))
                        <div class="mt-5 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                            role="status">
                            <i data-lucide="circle-check" class="mt-0.5 size-4 shrink-0" aria-hidden="true"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3" role="alert">
                            <p class="flex items-center gap-2 text-sm font-semibold text-rose-800">
                                <i data-lucide="circle-alert" class="size-4 shrink-0" aria-hidden="true"></i>
                                Revisa los datos ingresados
                            </p>
                            <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-xs text-rose-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="login-form" method="POST" action="{{ route('login') }}" class="mt-6 space-y-5"
                        data-login-form>
                        @csrf

                        {{-- CORREO --}}
                        <div>
                            <label for="email" class="nova-label">Correo electrónico</label>

                            <div class="relative">
                                <i data-lucide="mail"
                                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"></i>

                                <input id="email" name="email" type="email" value="{{ old('email') }}"
                                    @class([
                                        'form-input nova-input-icon w-full focus:ring-0',
                                        'nova-field--invalid' => $errors->has('email'),
                                        'border-slate-200' => ! $errors->has('email'),
                                    ])
                                    placeholder="nombre@empresa.com" autocomplete="email" inputmode="email" autofocus
                                    required @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                            </div>

                            @error('email')
                                <p id="email-error" class="nova-error">
                                    <i data-lucide="circle-alert" class="mt-px size-3.5 shrink-0" aria-hidden="true"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- CONTRASEÑA --}}
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <label for="password" class="nova-label">Contraseña</label>

                                <a href="mailto:soporte@technova.test?subject=Recuperaci%C3%B3n%20de%20acceso"
                                    class="mb-1 text-xs font-semibold text-nova-700 transition hover:text-nova-900">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>

                            <div class="relative">
                                <i data-lucide="lock"
                                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"></i>

                                <input id="password" name="password" type="password"
                                    @class([
                                        'form-input nova-input-icon w-full pe-11 focus:ring-0',
                                        'nova-field--invalid' => $errors->has('password'),
                                        'border-slate-200' => ! $errors->has('password'),
                                    ])
                                    placeholder="Tu contraseña" autocomplete="current-password" required
                                    @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>

                                <button type="button" data-password-toggle
                                    class="absolute right-2 top-1/2 inline-flex size-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-nova-700"
                                    aria-label="Mostrar contraseña" aria-pressed="false">
                                    <i data-lucide="eye" class="size-4" aria-hidden="true"></i>
                                </button>
                            </div>

                            @error('password')
                                <p id="password-error" class="nova-error">
                                    <i data-lucide="circle-alert" class="mt-px size-3.5 shrink-0" aria-hidden="true"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-700">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember'))
                                    class="size-4 rounded border-slate-300 text-nova-600 focus:ring-nova-500">
                                Recordarme en este equipo
                            </label>
                        </div>

                        <button type="submit" data-login-submit
                            class="btn inline-flex w-full items-center justify-center border-nova-500 bg-nova-500 px-4 py-3 text-white shadow-sm hover:border-nova-600 hover:bg-nova-600 disabled:cursor-wait disabled:opacity-80">
                            <span data-login-label>Iniciar sesión</span>
                            <i data-lucide="arrow-right" class="ms-1.5 size-4" data-login-icon aria-hidden="true"></i>
                            <span data-login-spinner
                                class="ms-2 hidden size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                                aria-hidden="true"></span>
                        </button>
                    </form>

                    <p class="mt-5 flex items-start gap-2 rounded-xl bg-slate-50 px-3.5 py-3 text-xs leading-5 text-slate-600">
                        <i data-lucide="info" class="mt-px size-4 shrink-0 text-slate-500" aria-hidden="true"></i>
                        <span>
                            La recuperación de contraseña se gestiona con el equipo de soporte.
                            El acceso está protegido y cada usuario tiene permisos según su rol.
                        </span>
                    </p>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4 text-sm">
                        <a href="{{ route('product.landing') }}"
                            class="inline-flex items-center gap-1.5 text-slate-600 transition hover:text-nova-700">
                            <i data-lucide="arrow-left" class="size-4" aria-hidden="true"></i>
                            Volver al inicio
                        </a>

                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                            <i data-lucide="lock-keyhole" class="size-3.5" aria-hidden="true"></i>
                            Sesión protegida
                        </span>
                    </div>
                </div>

                <p class="mt-5 text-center text-xs text-slate-500">
                    TechNova Warehouse · Inventario, ventas y facturación
                </p>
            </div>
        </section>
    </main>

    <script src="{{ asset('assets/libs/lucide/umd/lucide.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                lucide.createIcons();
            }

            /* Mostrar u ocultar contraseña */
            const toggle = document.querySelector('[data-password-toggle]');
            const password = document.getElementById('password');

            if (toggle && password) {
                toggle.addEventListener('click', function () {
                    const isHidden = password.type === 'password';
                    password.type = isHidden ? 'text' : 'password';
                    toggle.setAttribute('aria-pressed', String(isHidden));
                    toggle.setAttribute('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
                });
            }

            /* Estado de envío */
            const form = document.querySelector('[data-login-form]');
            const submit = document.querySelector('[data-login-submit]');
            const label = document.querySelector('[data-login-label]');
            const icon = document.querySelector('[data-login-icon]');
            const spinner = document.querySelector('[data-login-spinner]');

            if (form && submit) {
                form.addEventListener('submit', function () {
                    if (form.checkValidity()) {
                        submit.disabled = true;
                        submit.setAttribute('aria-busy', 'true');

                        if (label) {
                            label.textContent = 'Verificando...';
                        }

                        if (icon) {
                            icon.classList.add('hidden');
                        }

                        if (spinner) {
                            spinner.classList.remove('hidden');
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>
