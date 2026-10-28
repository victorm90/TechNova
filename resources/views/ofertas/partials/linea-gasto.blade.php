@props(['indice', 'linea' => []])

@php
    $gastoId = $linea['gasto_id'] ?? '';
    $cantidad = $linea['cantidad'] ?? '';
    $precio = $linea['precio_unitario'] ?? '';
@endphp

<div class="rounded-xl border border-slate-200 p-3 dark:border-zink-500" data-linea="gasto">
    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_110px_140px_120px_36px] md:items-end">
        <div>
            <label for="gasto-{{ $indice }}" class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-zink-300">
                Gasto
            </label>
            <select id="gasto-{{ $indice }}" name="gastos[{{ $indice }}][gasto_id]" data-selector
                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
                <option value="">Seleccione un gasto</option>
                @foreach ($gastos as $gasto)
                    <option value="{{ $gasto->id }}" data-precio="{{ $gasto->precio_unitario }}"
                        @selected((string) $gastoId === (string) $gasto->id)>
                        {{ $gasto->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="gasto-cantidad-{{ $indice }}" class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-zink-300">
                Cantidad
            </label>
            <input id="gasto-cantidad-{{ $indice }}" name="gastos[{{ $indice }}][cantidad]" type="number"
                min="0.01" step="0.01" value="{{ $cantidad }}" data-cantidad
                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
        </div>

        <div>
            <label for="gasto-precio-{{ $indice }}" class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-zink-300">
                Precio unitario
            </label>
            <input id="gasto-precio-{{ $indice }}" name="gastos[{{ $indice }}][precio_unitario]" type="number"
                min="0" step="0.01" value="{{ $precio }}" data-precio
                class="form-input w-full border-slate-200 dark:border-zink-500 dark:bg-zink-700">
        </div>

        <div>
            <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-zink-300">Total</span>
            <span data-linea-total
                class="flex h-9 items-center justify-end whitespace-nowrap text-sm font-semibold text-slate-700 dark:text-zink-100">
                $ 0,00
            </span>
        </div>

        <button type="button" data-eliminar-linea title="Quitar línea"
            class="inline-flex size-9 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100">
            <i data-lucide="trash-2" class="size-4"></i>
        </button>
    </div>
</div>
