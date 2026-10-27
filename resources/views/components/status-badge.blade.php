@props(['status' => 'neutral', 'label' => null, 'dot' => true])

@php
    $labels = [
        'paid' => 'Pagado',
        'pending' => 'Pendiente',
        'overdue' => 'Vencido',
        'low' => 'Stock bajo',
        'ok' => 'Disponible',
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'critical' => 'Crítico',
        'out' => 'Sin stock',
        'draft' => 'Borrador',
        'cancelled' => 'Cancelado',
    ];

    $styles = match ($status) {
        'paid', 'active', 'ok' => 'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/20',
        'pending', 'low' => 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/20',
        'overdue', 'critical', 'out', 'cancelled' => 'bg-rose-50 text-rose-800 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-500/20',
        'draft' => 'bg-sky-50 text-sky-800 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-zink-600 dark:text-zink-200 dark:ring-zink-500',
    };
@endphp

<span
    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $styles }}">
    @if ($dot)
        <span class="size-1.5 rounded-full bg-current opacity-80" aria-hidden="true"></span>
    @endif
    {{ $label ?? ($labels[$status] ?? ucfirst($status)) }}
</span>
