<!DOCTYPE html>
<html lang="es" class="light scroll-smooth group" data-layout="vertical" data-sidebar="light" data-sidebar-size="lg"
    data-mode="light" data-topbar="light" data-skin="default" data-navbar="sticky" data-content="fluid" dir="ltr">

<head>
    <meta charset="utf-8">
    <title> @yield('tutulo')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Minimal Admin & Dashboard Template" name="description">
    <meta content="StarCode Kh" name="author">
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Layout config Js -->
    <script src="{{ asset('assets/js/layout.js') }}"></script>
    <!-- Icons CSS -->

    <!-- StarCode CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/starcode2.css') }}">
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
   
</head>

<body
    class="text-base bg-body-bg text-body font-public dark:text-zink-100 dark:bg-zink-800 group-data-[skin=bordered]:bg-body-bordered group-data-[skin=bordered]:dark:bg-zink-700">
    <div class="group-data-[sidebar-size=sm]:min-h-sm group-data-[sidebar-size=sm]:relative">

        @include('layouts.sidebar')

        <!-- Left Sidebar End -->
        <div id="sidebar-overlay" class="absolute inset-0 z-[1002] bg-slate-500/30 hidden"></div>

        @include('layouts.header')

        @include('layouts.shoping')        

        <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>

        <div class="relative min-h-screen group-data-[sidebar-size=sm]:min-h-sm">

            <div
                class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
                <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">
                    @yield('contenido')
                    <!-- End Page-content -->
                </div>
            </div>

            @include('layouts.footer')
        </div>

    </div>
    <!-- end main content -->

    <!--customizer-->
    @include('layouts.customiser')

    <script src='{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}'></script>
    <script src="{{ asset('assets/libs/%40popperjs/core/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/libs/tippy.js/tippy-bundle.umd.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/prismjs/prism.js') }}"></script>
    <script src="{{ asset('assets/libs/lucide/umd/lucide.js') }}"></script>
    <script src="{{ asset('assets/js/starcode.bundle.js') }}"></script>
    <!--apexchart js-->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <!--dashboard ecommerce init js-->
    <script src="{{ asset('assets/js/pages/dashboards-ecommerce.init.js') }}"></script>
    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <!-- vanila calendar -->
    <script src="assets/libs/vanilla-calendar-pro/build/vanilla-calendar.min.js"></script>

    <script src="assets/js/pages/dashboards-hr.init.js"></script>
  
</body>

</html>
