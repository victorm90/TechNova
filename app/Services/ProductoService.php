<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoService
{
    public function __construct(private readonly SecuenciaService $secuencias) {}

    /**
     * Crear un producto con SKU consecutivo. Las existencias y los precios se
     * registran posteriormente mediante una entrada de inventario.
     *
     * @param  array{nombre: string, categoria_producto_id: int, unidad_medida_id: int, imagen?: UploadedFile|null}  $datos
     */
    public function crear(array $datos): Producto
    {
        return DB::transaction(function () use ($datos): Producto {
            $producto = Producto::create([
                'sku' => $this->secuencias->siguiente('productos', 'PROD-'),
                'nombre' => $datos['nombre'],
                'categoria_producto_id' => $datos['categoria_producto_id'],
                'unidad_medida_id' => $datos['unidad_medida_id'],
                'imagen' => $this->guardarImagen($datos['imagen'] ?? null),
                'activo' => true,
            ]);

            return $producto->refresh();
        });
    }

    /**
     * Actualizar los datos maestros de un producto sin alterar existencias ni precios.
     *
     * @param  array{nombre: string, categoria_producto_id: int, unidad_medida_id: int, imagen?: UploadedFile|null, activo?: bool}  $datos
     */
    public function actualizar(Producto $producto, array $datos): Producto
    {
        return DB::transaction(function () use ($producto, $datos): Producto {
            $imagen = $producto->imagen;

            if (($datos['imagen'] ?? null) instanceof UploadedFile) {
                $nuevaImagen = $this->guardarImagen($datos['imagen']);

                if ($nuevaImagen !== null && $imagen !== null) {
                    Storage::disk('public')->delete($imagen);
                }

                $imagen = $nuevaImagen;
            }

            $producto->update([
                'nombre' => $datos['nombre'],
                'categoria_producto_id' => $datos['categoria_producto_id'],
                'unidad_medida_id' => $datos['unidad_medida_id'],
                'imagen' => $imagen,
                'activo' => $datos['activo'] ?? $producto->activo,
            ]);

            return $producto->refresh();
        });
    }

    /**
     * Guardar la imagen en el disco público y devolver su ruta relativa.
     */
    private function guardarImagen(?UploadedFile $imagen): ?string
    {
        if (! $imagen instanceof UploadedFile) {
            return null;
        }

        return $imagen->store('productos', 'public');
    }
}
