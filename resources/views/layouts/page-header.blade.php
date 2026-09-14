@php
    $breadcrumbs = $breadcrumbs ?? [];
@endphp

<div class="flex flex-col gap-3 py-4 md:flex-row md:items-center print:hidden">
    <div class="grow">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center rounded-lg size-11 bg-custom-500/10">
                <i data-lucide="{{ $icon }}" class="size-6 text-custom-500"></i>
            </div>
            <div>
                <h5 class="text-lg font-semibold text-slate-800 dark:text-zink-100">
                    {{ $title }}
                </h5>
                @if ($description)
                    <p class="text-sm text-slate-500 dark:text-zink-300">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    @if ($breadcrumbs)
        <ul class="flex items-center gap-2 text-sm font-normal shrink-0">
            @foreach ($breadcrumbs as $breadcrumb)
                <li @class([
                    'font-medium text-slate-700 dark:text-zink-100' => $loop->last,
                    'relative pr-4 text-slate-400 before:content-[\'\\ea54\'] before:font-remix before:absolute before:right-0 before:text-[16px] before:top-0' => !$loop->last,
                ])>
                    @if (! $loop->last && isset($breadcrumb['url']))
                        <a href="{{ $breadcrumb['url'] }}" class="hover:text-custom-500">
                            {{ $breadcrumb['label'] }}
                        </a>
                    @else
                        {{ $breadcrumb['label'] }}
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>