@props(['rows' => 4])

<div class="nova-table-loading" role="status" aria-live="polite">
    <div class="absolute inset-x-0 top-0 flex items-center gap-2 border-b border-slate-200/70 px-5 py-4 dark:border-zink-500">
        <span class="nova-spinner" aria-hidden="true"></span>
        <p class="text-sm font-medium text-slate-600 dark:text-zink-200">Cargando registros...</p>
    </div>

    <div class="mt-14 space-y-3" aria-hidden="true">
        @for ($i = 0; $i < $rows; $i++)
            <div class="nova-skeleton-row">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
        @endfor
    </div>
</div>
