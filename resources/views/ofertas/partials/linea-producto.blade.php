@props(['servicioIndice', 'indice', 'linea' => []])

@php
    $productoId = $linea['producto_id'] ?? '';
    $cantidad = $linea['cantidad'] ?? '';
    $precio = $linea['precio_venta'] ?? '';
    $existencias = $stockPorAlmacen[$productoId] ?? collect();
    $stockActual = (int) ($existencias[(string) $almacenActual] ?? $existencias[$almacenActual] ?? 0);
    $productoSeleccionado = $productos->firstWhere('id', (int) $productoId);
    $unidad = $productoSeleccionado?->unidadMedida?->abreviatura ?? 'ud';
@endphp

<div class="rounded-lg border border-slate-200 bg-slate-50/60 p-2.5 dark:border-zink-500 dark:bg-zink-600/30"
    data-producto>
    <input type="hidden" name="servicios[{{ $servicioIndice }}][productos][{{ $indice }}][id]"
        value="{{ $linea['id'] ?? '' }}">

    <div class="grid gap-2 md:grid-cols-[minmax(0,1fr)_76px_110px_100px_32px] md:items-start">
        <div>
            <label for="producto-{{ $servicioIndice }}-{{ $indice }}"
                class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-zink-300">Producto</label>
            <select id="producto-{{ $servicioIndice }}-{{ $indice }}"
                name="servicios[{{ $servicioIndice }}][productos][{{ $indice }}][producto_id]"
                data-selector-producto
                class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">
                <option value="">Seleccione un producto</option>
                @foreach ($productos as $producto)
                    <option value="{{ $producto->id }}" data-precio="{{ $producto->precio_venta }}"
                        data-unidad="{{ $producto->unidadMedida?->abreviatura ?? 'ud' }}"
                        data-stock="{{ ($stockPorAlmacen[$producto->id] ?? collect())->toJson() }}"
                        @selected((string) $productoId === (string) $producto->id)>
                        {{ $producto->nombre }} ({{ $producto->sku }})
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-[10px] text-slate-400" data-stock-hint>
                Stock: <span>{{ number_format($stockActual, 0, ',', '.') }}</span>
                {{ $unidad }}
            </p>
        </div>

        <div>
            <label for="producto-cantidad-{{ $servicioIndice }}-{{ $indice }}"
                class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-zink-300">Cantidad</label>
            <input id="producto-cantidad-{{ $servicioIndice }}-{{ $indice }}"
                name="servicios[{{ $servicioIndice }}][productos][{{ $indice }}][cantidad]" type="number" min="1"
                step="1" value="{{ $cantidad }}" data-cantidad-producto
                class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">
        </div>

        <div>
            <label for="producto-precio-{{ $servicioIndice }}-{{ $indice }}"
                class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-zink-300">Precio venta</label>
            <input id="producto-precio-{{ $servicioIndice }}-{{ $indice }}"
                name="servicios[{{ $servicioIndice }}][productos][{{ $indice }}][precio_venta]" type="number"
                min="0" step="0.01" value="{{ $precio }}" data-precio-producto
                class="form-input w-full border-slate-200 text-sm dark:border-zink-500 dark:bg-zink-700">
        </div>

        <div>
            <span class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-zink-300">Subtotal</span>
            <span data-total-producto
                class="flex h-9 items-center justify-end whitespace-nowrap text-sm font-semibold text-slate-700 dark:text-zink-100">
                $ 0,00
            </span>
        </div>

        <button type="button" data-eliminar-producto title="Quitar producto"
            class="mt-5 inline-flex size-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100">
            <i data-lucide="trash-2" class="size-3.5"></i>
        </button>
    </div>
</div>
