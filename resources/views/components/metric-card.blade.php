@props([
    'label',
    'value',
    'format' => 'number',
    'variation' => null,
    'tone' => 'neutral',
    'icon' => 'activity',
    'accent' => 'green',
    'hint' => null,
    'spark' => [],
])

@php
    $formattedValue = match ($format) {
        'currency' => '$'.number_format((float) $value, 0, ',', '.'),
        'percent' => number_format((float) $value, 1, ',', '.').'%',
        default => number_format((int) $value, 0, ',', '.'),
    };

    $iconClasses = match ($accent) {
        'green' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'blue' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        default => 'bg-nova-50 text-nova-700 dark:bg-nova-500/15 dark:text-nova-300',
    };

    $toneClasses = match ($tone) {
        'positive' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
        'negative' => 'bg-rose-50 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
        default => 'bg-slate-100 text-slate-700 dark:bg-zink-600 dark:text-zink-200',
    };

    $toneIcon = match ($tone) {
        'positive' => 'arrow-up-right',
        'negative' => 'arrow-down-right',
        default => 'minus',
    };

    $spark = array_values(array_filter((array) $spark, fn ($point) => is_numeric($point)));
    $sparkMax = $spark !== [] ? max($spark) : 0;
    $variationTone = in_array($tone, ['positive', 'negative'], true) ? $tone : 'neutral';
@endphp

<div class="nova-kpi nova-kpi--{{ $accent }} card">
    <div class="card-body">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-zink-300">
                    {{ $label }}
                </p>

                <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-zink-50">
                    {{ $formattedValue }}
                </p>

                @if ($hint)
                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-zink-300">
                        {{ $hint }}
                    </p>
                @endif
            </div>

            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $iconClasses }}"
                aria-hidden="true">
                <i data-lucide="{{ $icon }}" class="size-5"></i>
            </div>
        </div>

        @if ($variation || $spark !== [])
            <div class="mt-4 flex items-end justify-between gap-3 border-t border-slate-100 pt-3 dark:border-zink-500">
                <div class="min-w-0">
                    @if ($variation)
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $toneClasses }}">
                            <i data-lucide="{{ $toneIcon }}" class="size-3.5" aria-hidden="true"></i>
                            {{ $variation }}
                        </span>
                        <span class="mt-1 block text-[11px] text-slate-400">vs. periodo anterior</span>
                    @else
                        <span class="text-[11px] text-slate-400">Sin comparativa disponible</span>
                    @endif
                </div>

                @if ($spark !== [])
                    <div class="nova-kpi__spark shrink-0" aria-hidden="true" title="Tendencia del periodo">
                        @foreach ($spark as $point)
                            <span
                                style="height: {{ $sparkMax > 0 ? max(14, round(((float) $point / $sparkMax) * 100)) : 14 }}%"></span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
