<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inventario;
use App\Models\Municipio;
use App\Models\Producto;
use App\Models\Provincia;
use App\Models\Servicio;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductLandingController extends Controller
{
    /**
     * Imágenes disponibles para el catálogo de la página de presentación.
     *
     * @var array<int, string>
     */
    private const IMAGENES = [
        'warehouse-dashboard.png',
        'product-home.png',
        'order-overview.jpg',
        'dashboard.png',
        'img-01.png',
        'img-02.png',
    ];

    /**
     * Mostrar la página de presentación.
     */
    public function index(): View
    {
        $productos = Producto::query()
            ->activos()
            ->with(['categoriaProducto', 'unidadMedida'])
            ->withSum('inventarios as stock_total', 'cantidad')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $usandoProductosReales = $productos->isNotEmpty();

        return view('admin.product-landing', [
            'totalCustomers' => Customer::query()->count(),
            'totalServicios' => Servicio::query()->count(),
            'totalProvincias' => Provincia::query()->count(),
            'totalMunicipios' => Municipio::query()->count(),
            'totalProductos' => Producto::query()->activos()->count(),
            'totalUnidades' => (float) Inventario::query()->sum('cantidad'),
            'featuredProducts' => $usandoProductosReales
                ? $this->productosDestacados($productos)
                : $this->productosDemostracion(),
            'usandoProductosReales' => $usandoProductosReales,
        ]);
    }

    /**
     * Transformar los productos reales al formato del carrusel.
     *
     * @param  Collection<int, Producto>  $productos
     * @return array<int, array<string, mixed>>
     */
    private function productosDestacados(Collection $productos): array
    {
        return $productos
            ->values()
            ->map(function (Producto $producto, int $indice): array {
                $stock = (float) ($producto->stock_total ?? 0);
                $minimo = (float) $producto->stock_minimo;

                [$estado, $etiqueta] = match (true) {
                    $stock <= 0 => ['out', 'Sin stock'],
                    $stock <= $minimo => ['low', 'Stock bajo'],
                    default => ['ok', 'Disponible'],
                };

                $unidad = $producto->unidadMedida?->abreviatura
                    ?? $producto->unidadMedida?->nombre
                    ?? 'unidades';

                return [
                    'name' => $producto->nombre,
                    'category' => $producto->categoriaProducto?->nombre ?? 'Sin categoría',
                    'description' => $producto->descripcion
                        ?: 'Producto disponible en el catálogo del almacén.',
                    'price' => (float) $producto->precio_venta,
                    'price_suffix' => '/ '.$unidad,
                    'image' => self::IMAGENES[$indice % count(self::IMAGENES)],
                    'status' => $estado,
                    'status_label' => $etiqueta,
                    'sku' => $producto->sku,
                    'stock' => $stock,
                    'unit' => $unidad,
                    'url' => auth()->check()
                        ? route('productos.show', $producto)
                        : route('login'),
                    'link_label' => 'Ver producto',
                ];
            })
            ->all();
    }

    /**
     * Catálogo de demostración usado mientras no existan productos.
     *
     * @return array<int, array<string, mixed>>
     */
    private function productosDemostracion(): array
    {
        return [
            [
                'name' => 'TechNova Warehouse',
                'category' => 'Gestión de almacén',
                'description' => 'Inventario, ventas y facturación en un solo panel.',
                'price' => 149,
                'price_suffix' => '/ mes',
                'image' => 'warehouse-dashboard.png',
                'status' => 'ok',
                'status_label' => 'Disponible',
                'sku' => null,
                'stock' => null,
                'unit' => null,
                'url' => '#producto',
                'link_label' => 'Ver detalle',
            ],
            [
                'name' => 'TechNova POS',
                'category' => 'Punto de venta',
                'description' => 'Cobro rápido con control de caja y turnos.',
                'price' => 89,
                'price_suffix' => '/ mes',
                'image' => 'product-home.png',
                'status' => 'ok',
                'status_label' => 'Disponible',
                'sku' => null,
                'stock' => null,
                'unit' => null,
                'url' => '#producto',
                'link_label' => 'Ver detalle',
            ],
            [
                'name' => 'TechNova Facturación',
                'category' => 'Facturación',
                'description' => 'Facturas, cobros y estados de cuenta automatizados.',
                'price' => 119,
                'price_suffix' => '/ mes',
                'image' => 'order-overview.jpg',
                'status' => 'low',
                'status_label' => 'Últimas licencias',
                'sku' => null,
                'stock' => null,
                'unit' => null,
                'url' => '#producto',
                'link_label' => 'Ver detalle',
            ],
            [
                'name' => 'TechNova Reportes',
                'category' => 'Analítica',
                'description' => 'Indicadores de rotación, margen y actividad.',
                'price' => 69,
                'price_suffix' => '/ mes',
                'image' => 'dashboard.png',
                'status' => 'ok',
                'status_label' => 'Disponible',
                'sku' => null,
                'stock' => null,
                'unit' => null,
                'url' => '#producto',
                'link_label' => 'Ver detalle',
            ],
            [
                'name' => 'TechNova Compras',
                'category' => 'Abastecimiento',
                'description' => 'Órdenes de compra y seguimiento de proveedores.',
                'price' => 79,
                'price_suffix' => '/ mes',
                'image' => 'img-01.png',
                'status' => 'ok',
                'status_label' => 'Disponible',
                'sku' => null,
                'stock' => null,
                'unit' => null,
                'url' => '#producto',
                'link_label' => 'Ver detalle',
            ],
            [
                'name' => 'TechNova CRM',
                'category' => 'Clientes',
                'description' => 'Directorio, historial y seguimiento comercial.',
                'price' => 59,
                'price_suffix' => '/ mes',
                'image' => 'img-02.png',
                'status' => 'out',
                'status_label' => 'Sin stock',
                'sku' => null,
                'stock' => null,
                'unit' => null,
                'url' => '#producto',
                'link_label' => 'Ver detalle',
            ],
        ];
    }
}
