@props(['icon' => 'inbox', 'title' => 'Sin registros', 'message' => null])

<div class="flex flex-col items-center px-6 py-12 text-center">
    <div class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-zink-600 dark:text-zink-300">
        <i data-lucide="{{ $icon }}" class="size-6"></i>
    </div>

    <p class="mt-3 font-medium text-slate-700 dark:text-zink-100">
        {{ $title }}
    </p>

    @if ($message)
        <p class="mt-1 max-w-md text-sm text-slate-400 dark:text-zink-400">
            {{ $message }}
        </p>
    @endif

    @if (trim(strip_tags($slot)) !== '')
        <div class="mt-3">
            {{ $slot }}
        </div>
    @endif
</div>
