@php
    $breadcrumbs = $breadcrumbs ?? [];
    $accent = $accent ?? 'nova';

    $accentClasses = match ($accent) {
        'green' => 'bg-gradient-to-br from-emerald-100 to-emerald-50 text-emerald-700',
        'blue' => 'bg-gradient-to-br from-sky-100 to-sky-50 text-sky-700',
        'amber' => 'bg-gradient-to-br from-amber-100 to-amber-50 text-amber-700',
        default => 'bg-gradient-to-br from-nova-100 to-nova-50 text-nova-700',
    };
@endphp

<div class="nova-hero mb-5 print:hidden">
    <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
            <div class="nova-hero-icon {{ $accentClasses }}">
                <i data-lucide="{{ $icon }}" class="size-6"></i>
            </div>

            <div class="min-w-0">
                @if ($breadcrumbs)
                    <nav class="nova-breadcrumb mb-1.5" aria-label="Ruta de navegación">
                        @foreach ($breadcrumbs as $breadcrumb)
                            @if (! $loop->last)
                                @if (isset($breadcrumb['url']))
                                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                                @else
                                    <span class="text-slate-400 dark:text-zink-400">
                                        {{ $breadcrumb['label'] }}
                                    </span>
                                @endif

                                <i data-lucide="chevron-right" class="size-3 text-slate-300 dark:text-zink-500"></i>
                            @else
                                <span class="font-medium text-slate-500 dark:text-zink-300">
                                    {{ $breadcrumb['label'] }}
                                </span>
                            @endif
                        @endforeach
                    </nav>
                @endif

                <h1 class="text-xl font-semibold text-slate-800 sm:text-2xl dark:text-zink-50">
                    {{ $title }}
                </h1>

                @if ($description)
                    <p class="mt-1 text-sm text-slate-500 dark:text-zink-300">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
