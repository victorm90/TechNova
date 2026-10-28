<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Gasto;
use App\Models\Oferta;
use App\Models\OfertaGasto;
use App\Models\OfertaServicio;
use App\Models\OfertaServicioProducto;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfertaService
{
    public function __construct(
        private readonly SecuenciaService $secuencias,
        private readonly StockOfertaService $stock,
    ) {}

    /**
     * Crear una oferta con sus servicios, productos y gastos.
     *
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos, ?User $usuario = null): Oferta
    {
        return DB::transaction(function () use ($datos, $usuario): Oferta {
            $almacenId = $this->resolverAlmacen($datos['almacen_id'] ?? null);

            $this->stock->validar(
                $this->requerimientosDesdeLineas($datos['servicios'] ?? []),
                $almacenId,
            );

            $oferta = Oferta::create([
                ...$this->datosCabecera($datos),
                'codigo' => $this->secuencias->siguiente('ofertas', 'OF-'),
                'almacen_id' => $almacenId,
            ]);

            $this->sincronizarServicios($oferta, $datos['servicios'] ?? [], $usuario);
            $this->sincronizarGastos($oferta, $datos['gastos'] ?? [], $usuario);

            return $this->cargar($oferta);
        });
    }

    /**
     * Crear un borrador rápido: sólo requiere cliente y fecha.
     */
    public function crearBorrador(Customer $customer, ?int $almacenId = null, ?string $fecha = null): Oferta
    {
        return DB::transaction(function () use ($customer, $almacenId, $fecha): Oferta {
            return Oferta::create([
                'codigo' => $this->secuencias->siguiente('ofertas', 'OF-'),
                'customer_id' => $customer->id,
                'fecha' => $fecha ?? now()->toDateString(),
                'estado' => Oferta::ESTADO_BORRADOR,
                'vigencia_dias' => 15,
                'almacen_id' => $almacenId ?? $this->stock->almacenPredeterminadoId(),
            ]);
        });
    }

    /**
     * Actualizar la cabecera y sincronizar las líneas de una oferta editable.
     *
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Oferta $oferta, array $datos, ?User $usuario = null): Oferta
    {
        $this->validarEditable($oferta);

        return DB::transaction(function () use ($oferta, $datos, $usuario): Oferta {
            $almacenId = $this->resolverAlmacen($datos['almacen_id'] ?? $oferta->almacen_id);

            $this->stock->validar(
                $this->requerimientosDesdeLineas($datos['servicios'] ?? []),
                $almacenId,
            );

            $oferta->update([
                ...$this->datosCabecera($datos),
                'almacen_id' => $almacenId,
            ]);

            $this->sincronizarServicios($oferta, $datos['servicios'] ?? [], $usuario);
            $this->sincronizarGastos($oferta, $datos['gastos'] ?? [], $usuario);

            return $this->cargar($oferta);
        });
    }

    /**
     * Añadir un servicio a una oferta existente desde el detalle.
     *
     * @param  array{servicio_id: int|string, cantidad: numeric|string, precio_unitario: numeric|string}  $datos
     */
    public function agregarServicio(Oferta $oferta, array $datos, ?User $usuario = null): OfertaServicio
    {
        $this->validarEditable($oferta);

        return DB::transaction(function () use ($oferta, $datos, $usuario): OfertaServicio {
            $servicioId = (int) $datos['servicio_id'];

            if ($oferta->servicios()->where('servicio_id', $servicioId)->exists()) {
                throw ValidationException::withMessages([
                    'servicio_id' => 'Ese servicio ya está incluido en la oferta.',
                ]);
            }

            $base = (float) Servicio::query()->whereKey($servicioId)->value('precio_unitario');
            $precio = isset($datos['precio_unitario'])
                ? round((float) $datos['precio_unitario'], 2)
                : $base;

            $linea = $oferta->servicios()->create([
                'servicio_id' => $servicioId,
                'cantidad' => round((float) $datos['cantidad'], 2),
                'precio_unitario' => $precio,
            ]);

            $linea->registrarCambioPrecio($base, $precio, $usuario);

            return $linea;
        });
    }

    /**
     * Añadir un gasto a una oferta existente desde el detalle.
     *
     * @param  array{gasto_id: int|string, cantidad: numeric|string, precio_unitario: numeric|string}  $datos
     */
    public function agregarGasto(Oferta $oferta, array $datos, ?User $usuario = null): OfertaGasto
    {
        $this->validarEditable($oferta);

        return DB::transaction(function () use ($oferta, $datos, $usuario): OfertaGasto {
            $gastoId = (int) $datos['gasto_id'];

            if ($oferta->gastos()->where('gasto_id', $gastoId)->exists()) {
                throw ValidationException::withMessages([
                    'gasto_id' => 'Ese gasto ya está incluido en la oferta.',
                ]);
            }

            $base = (float) Gasto::query()->whereKey($gastoId)->value('precio_unitario');
            $precio = isset($datos['precio_unitario'])
                ? round((float) $datos['precio_unitario'], 2)
                : $base;

            $linea = $oferta->gastos()->create([
                'gasto_id' => $gastoId,
                'cantidad' => round((float) $datos['cantidad'], 2),
                'precio_unitario' => $precio,
            ]);

            $linea->registrarCambioPrecio($base, $precio, $usuario);

            return $linea;
        });
    }

    /**
     * Añadir un producto de almacén a una línea de servicio.
     *
     * @param  array{producto_id: int|string, cantidad: int|string, precio_venta: numeric|string}  $datos
     */
    public function agregarProducto(
        Oferta $oferta,
        OfertaServicio $linea,
        array $datos,
        ?User $usuario = null,
    ): OfertaServicioProducto {
        $this->validarEditable($oferta);

        if ((int) $linea->oferta_id !== (int) $oferta->id) {
            throw ValidationException::withMessages([
                'producto_id' => 'La línea de servicio no pertenece a esta oferta.',
            ]);
        }

        return DB::transaction(function () use ($oferta, $linea, $datos, $usuario): OfertaServicioProducto {
            $productoId = (int) $datos['producto_id'];

            if ($linea->productos()->where('producto_id', $productoId)->exists()) {
                throw ValidationException::withMessages([
                    'producto_id' => 'Ese producto ya está asociado al servicio.',
                ]);
            }

            $cantidad = (int) $datos['cantidad'];

            $this->stock->validar(
                $this->requerimientosExistentes($oferta, $linea, $productoId, $cantidad),
                $this->resolverAlmacen($oferta->almacen_id),
            );

            $base = (float) Producto::query()->whereKey($productoId)->value('precio_venta');
            $precio = isset($datos['precio_venta'])
                ? round((float) $datos['precio_venta'], 2)
                : $base;

            $productoLinea = $linea->productos()->create([
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'precio_venta' => $precio,
            ]);

            $productoLinea->registrarCambioPrecio($base, $precio, $usuario);

            return $productoLinea;
        });
    }

    /**
     * Aplicar los ajustes reales confirmados antes de facturar.
     *
     * @param  array<string, mixed>  $datos
     */
    public function aplicarAjustes(Oferta $oferta, array $datos, ?User $usuario = null): void
    {
        $servicios = $oferta->servicios()->get()->keyBy('id');
        $gastos = $oferta->gastos()->get()->keyBy('id');
        $productos = $servicios->flatMap(fn (OfertaServicio $linea) => $linea->productos()->get())
            ->keyBy('id');

        foreach ($datos['servicios'] ?? [] as $ajuste) {
            /** @var OfertaServicio|null $linea */
            $linea = $servicios->get((int) $ajuste['id']);

            if (! $linea) {
                throw ValidationException::withMessages([
                    'servicios' => 'Una de las líneas de servicio ya no existe en la oferta.',
                ]);
            }

            $this->actualizarPrecioCantidad(
                $linea,
                (float) $ajuste['cantidad'],
                (float) $ajuste['precio_unitario'],
                'precio_unitario',
                $usuario,
            );
        }

        foreach ($datos['gastos'] ?? [] as $ajuste) {
            /** @var OfertaGasto|null $linea */
            $linea = $gastos->get((int) $ajuste['id']);

            if (! $linea) {
                throw ValidationException::withMessages([
                    'gastos' => 'Una de las líneas de gasto ya no existe en la oferta.',
                ]);
            }

            $this->actualizarPrecioCantidad(
                $linea,
                (float) $ajuste['cantidad'],
                (float) $ajuste['precio_unitario'],
                'precio_unitario',
                $usuario,
            );
        }

        foreach ($datos['productos'] ?? [] as $ajuste) {
            /** @var OfertaServicioProducto|null $linea */
            $linea = $productos->get((int) $ajuste['id']);

            if (! $linea) {
                throw ValidationException::withMessages([
                    'productos' => 'Una de las líneas de producto ya no existe en la oferta.',
                ]);
            }

            $this->actualizarPrecioCantidad(
                $linea,
                (float) $ajuste['cantidad'],
                (float) $ajuste['precio_venta'],
                'precio_venta',
                $usuario,
            );
        }
    }

    /**
     * Cantidades de productos de una oferta lista para facturar.
     *
     * @return array<int, array{producto_id: int, cantidad: int}>
     */
    public function requerimientosDeOferta(Oferta $oferta): array
    {
        return $oferta->servicios()
            ->with('productos')
            ->get()
            ->flatMap(fn (OfertaServicio $linea) => $linea->productos)
            ->map(fn (OfertaServicioProducto $producto): array => [
                'producto_id' => (int) $producto->producto_id,
                'cantidad' => (int) $producto->cantidad,
            ])
            ->all();
    }

    /**
     * Cargar las relaciones necesarias para vistas y respuestas.
     */
    public function cargar(Oferta $oferta): Oferta
    {
        return $oferta->refresh()->load([
            'customer',
            'almacen',
            'factura',
            'servicios.servicio.unidadMedida',
            'servicios.productos.producto.unidadMedida',
            'gastos.gasto',
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function validarEditable(Oferta $oferta): void
    {
        if ($oferta->estaFacturada() || $oferta->factura()->exists()) {
            throw ValidationException::withMessages([
                'oferta' => 'La oferta ya fue facturada y no puede modificarse.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function datosCabecera(array $datos): array
    {
        return [
            'customer_id' => (int) $datos['customer_id'],
            'fecha' => $datos['fecha'],
            'estado' => $datos['estado'],
            'nota' => $datos['nota'] ?? null,
            'forma_pago' => $datos['forma_pago'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null,
            'firma_pie' => $datos['firma_pie'] ?? null,
            'vigencia_dias' => (int) ($datos['vigencia_dias'] ?? 15),
        ];
    }

    private function resolverAlmacen(mixed $almacenId): ?int
    {
        return $almacenId !== null && $almacenId !== ''
            ? (int) $almacenId
            : ($this->stock->almacenPredeterminadoId());
    }

    /**
     * @param  array<int, array<string, mixed>>  $servicios
     */
    private function sincronizarServicios(Oferta $oferta, array $servicios, ?User $usuario): void
    {
        $existentes = $oferta->servicios()->get()->keyBy('id');
        $procesados = [];

        foreach (array_values($servicios) as $linea) {
            $modelo = $this->buscarServicioExistente($existentes, $linea);

            if ($modelo) {
                $precioAnterior = (float) $modelo->precio_unitario;

                $modelo->update([
                    'servicio_id' => (int) $linea['servicio_id'],
                    'cantidad' => round((float) $linea['cantidad'], 2),
                    'precio_unitario' => round((float) $linea['precio_unitario'], 2),
                ]);

                $modelo->registrarCambioPrecio(
                    $precioAnterior,
                    (float) $modelo->precio_unitario,
                    $usuario,
                );
            } else {
                $modelo = $oferta->servicios()->create([
                    'servicio_id' => (int) $linea['servicio_id'],
                    'cantidad' => round((float) $linea['cantidad'], 2),
                    'precio_unitario' => round((float) $linea['precio_unitario'], 2),
                ]);

                $base = (float) Servicio::query()
                    ->whereKey($linea['servicio_id'])
                    ->value('precio_unitario');

                $modelo->registrarCambioPrecio($base, (float) $modelo->precio_unitario, $usuario);
            }

            $procesados[] = $modelo->id;

            $this->sincronizarProductos($modelo, $linea['productos'] ?? [], $usuario);
        }

        $this->eliminarLineasNoEnviadas($oferta->servicios(), $procesados);
    }

    /**
     * @param  array<int, array<string, mixed>>  $gastos
     */
    private function sincronizarGastos(Oferta $oferta, array $gastos, ?User $usuario): void
    {
        $existentes = $oferta->gastos()->get()->keyBy('id');
        $procesados = [];

        foreach (array_values($gastos) as $linea) {
            $modelo = $this->buscarGastoExistente($existentes, $linea);

            if ($modelo) {
                $precioAnterior = (float) $modelo->precio_unitario;

                $modelo->update([
                    'gasto_id' => (int) $linea['gasto_id'],
                    'cantidad' => round((float) $linea['cantidad'], 2),
                    'precio_unitario' => round((float) $linea['precio_unitario'], 2),
                ]);

                $modelo->registrarCambioPrecio(
                    $precioAnterior,
                    (float) $modelo->precio_unitario,
                    $usuario,
                );
            } else {
                $modelo = $oferta->gastos()->create([
                    'gasto_id' => (int) $linea['gasto_id'],
                    'cantidad' => round((float) $linea['cantidad'], 2),
                    'precio_unitario' => round((float) $linea['precio_unitario'], 2),
                ]);

                $base = (float) Gasto::query()
                    ->whereKey($linea['gasto_id'])
                    ->value('precio_unitario');

                $modelo->registrarCambioPrecio($base, (float) $modelo->precio_unitario, $usuario);
            }

            $procesados[] = $modelo->id;
        }

        $this->eliminarLineasNoEnviadas($oferta->gastos(), $procesados);
    }

    /**
     * @param  array<int, array<string, mixed>>  $productos
     */
    private function sincronizarProductos(
        OfertaServicio $linea,
        array $productos,
        ?User $usuario,
    ): void {
        $existentes = $linea->productos()->get()->keyBy('id');
        $procesados = [];

        foreach (array_values($productos) as $producto) {
            $modelo = $this->buscarProductoExistente($existentes, $producto);

            if ($modelo) {
                $precioAnterior = (float) $modelo->precio_venta;

                $modelo->update([
                    'producto_id' => (int) $producto['producto_id'],
                    'cantidad' => (int) $producto['cantidad'],
                    'precio_venta' => round((float) $producto['precio_venta'], 2),
                ]);

                $modelo->registrarCambioPrecio(
                    $precioAnterior,
                    (float) $modelo->precio_venta,
                    $usuario,
                );
            } else {
                $modelo = $linea->productos()->create([
                    'producto_id' => (int) $producto['producto_id'],
                    'cantidad' => (int) $producto['cantidad'],
                    'precio_venta' => round((float) $producto['precio_venta'], 2),
                ]);

                $base = (float) Producto::query()
                    ->whereKey($producto['producto_id'])
                    ->value('precio_venta');

                $modelo->registrarCambioPrecio($base, (float) $modelo->precio_venta, $usuario);
            }

            $procesados[] = $modelo->id;
        }

        $this->eliminarLineasNoEnviadas($linea->productos(), $procesados);
    }

    /**
     * @param  HasMany<OfertaServicio>|HasMany<OfertaGasto>|HasMany<OfertaServicioProducto>  $relacion
     * @param  array<int, int>  $procesados
     */
    private function eliminarLineasNoEnviadas($relacion, array $procesados): void
    {
        if ($procesados === []) {
            $relacion->delete();

            return;
        }

        $relacion->whereNotIn('id', $procesados)->delete();
    }

    /**
     * @param  Collection<int, OfertaServicio>  $existentes
     * @param  array<string, mixed>  $linea
     */
    private function buscarServicioExistente($existentes, array $linea): ?OfertaServicio
    {
        $id = (int) ($linea['id'] ?? 0);

        if ($id > 0) {
            $lineaExistente = $existentes->get($id);

            if (! $lineaExistente) {
                throw ValidationException::withMessages([
                    'servicios' => 'Una de las líneas de servicio no pertenece a esta oferta.',
                ]);
            }

            return $lineaExistente;
        }

        return $existentes->firstWhere('servicio_id', (int) $linea['servicio_id']);
    }

    /**
     * @param  Collection<int, OfertaGasto>  $existentes
     * @param  array<string, mixed>  $linea
     */
    private function buscarGastoExistente($existentes, array $linea): ?OfertaGasto
    {
        $id = (int) ($linea['id'] ?? 0);

        if ($id > 0) {
            $lineaExistente = $existentes->get($id);

            if (! $lineaExistente) {
                throw ValidationException::withMessages([
                    'gastos' => 'Una de las líneas de gasto no pertenece a esta oferta.',
                ]);
            }

            return $lineaExistente;
        }

        return $existentes->firstWhere('gasto_id', (int) $linea['gasto_id']);
    }

    /**
     * @param  Collection<int, OfertaServicioProducto>  $existentes
     * @param  array<string, mixed>  $producto
     */
    private function buscarProductoExistente($existentes, array $producto): ?OfertaServicioProducto
    {
        $id = (int) ($producto['id'] ?? 0);

        if ($id > 0) {
            $productoExistente = $existentes->get($id);

            if (! $productoExistente) {
                throw ValidationException::withMessages([
                    'productos' => 'Uno de los productos no pertenece a esta línea de servicio.',
                ]);
            }

            return $productoExistente;
        }

        return $existentes->firstWhere('producto_id', (int) $producto['producto_id']);
    }

    private function actualizarPrecioCantidad(
        OfertaServicio|OfertaGasto|OfertaServicioProducto $linea,
        float $cantidad,
        float $precio,
        string $campoPrecio,
        ?User $usuario,
    ): void {
        $precioAnterior = (float) $linea->{$campoPrecio};

        $linea->update([
            'cantidad' => $linea instanceof OfertaServicioProducto
                ? (int) $cantidad
                : round($cantidad, 2),
            $campoPrecio => round($precio, 2),
        ]);

        $linea->registrarCambioPrecio($precioAnterior, round($precio, 2), $usuario);
    }

    /**
     * @param  array<int, array<string, mixed>>  $servicios
     * @return array<int, array{producto_id: int, cantidad: int}>
     */
    private function requerimientosDesdeLineas(array $servicios): array
    {
        $requerimientos = [];

        foreach ($servicios as $linea) {
            foreach (($linea['productos'] ?? []) as $producto) {
                $requerimientos[] = [
                    'producto_id' => (int) ($producto['producto_id'] ?? 0),
                    'cantidad' => (int) ($producto['cantidad'] ?? 0),
                ];
            }
        }

        return $requerimientos;
    }

    /**
     * Requerimientos existentes de la oferta más la línea que se quiere añadir.
     *
     * @return array<int, array{producto_id: int, cantidad: int}>
     */
    private function requerimientosExistentes(
        Oferta $oferta,
        OfertaServicio $linea,
        int $productoId,
        int $cantidad,
    ): array {
        $requerimientos = $oferta->servicios()
            ->with('productos')
            ->get()
            ->flatMap(fn (OfertaServicio $servicio) => $servicio->productos)
            ->map(fn (OfertaServicioProducto $producto): array => [
                'producto_id' => (int) $producto->producto_id,
                'cantidad' => (int) $producto->cantidad,
            ])
            ->all();

        $requerimientos[] = [
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
        ];

        return $requerimientos;
    }
}
