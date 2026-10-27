@props(['paginator', 'label' => 'registros'])

@if ($paginator->total() > 0)
    <div class="nova-table-foot">
        <p aria-live="polite">
            Mostrando
            <span class="font-semibold text-slate-700 dark:text-zink-100">{{ $paginator->firstItem() }}</span>
            –
            <span class="font-semibold text-slate-700 dark:text-zink-100">{{ $paginator->lastItem() }}</span>
            de
            <span class="font-semibold text-slate-700 dark:text-zink-100">{{ $paginator->total() }}</span>
            {{ $label }}
        </p>

        <div>
            {{ $paginator->appends(request()->query())->links('pagination.custom') }}
        </div>
    </div>
@endif
