<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\CategoriaProducto;
use App\Models\Customer;
use App\Models\Entidad;
use App\Models\Inventario;
use App\Models\Municipio;
use App\Models\Producto;
use App\Models\Provincia;
use App\Models\Servicio;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Mostrar el panel operativo de inventario, ventas y facturación.
     */
    public function index(Request $request): View
    {
        $canManageNomencladores = $request->user()->can('manage-nomencladores');
        $hasInventoryData = Producto::query()->exists();

        $mock = $this->dashboardData(config('warehouse_mock'), $hasInventoryData);

        $totalCustomers = Customer::query()->count();
        $customersThisMonth = Customer::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $recentCustomers = Customer::query()
            ->latest('id')
            ->limit(5)
            ->get();

        $fechaHoy = ucfirst(now()->locale('es')->translatedFormat('l, j \d\e F \d\e Y'));

        $realSummary = [
            [
                'label' => 'Clientes',
                'value' => $totalCustomers,
                'hint' => "{$customersThisMonth} nuevos este mes",
                'icon' => 'users',
                'accent' => 'green',
                'url' => route('customers.index'),
            ],
        ];

        if ($canManageNomencladores) {
            $realSummary = array_merge($realSummary, [
                [
                    'label' => 'Productos',
                    'value' => Producto::query()->count(),
                    'hint' => 'Catálogo registrado',
                    'icon' => 'package',
                    'accent' => 'teal',
                    'url' => route('productos.index'),
                ],
                [
                    'label' => 'Servicios',
                    'value' => Servicio::query()->count(),
                    'hint' => 'Catálogo registrado',
                    'icon' => 'briefcase',
                    'accent' => 'blue',
                    'url' => route('servicios.index'),
                ],
                [
                    'label' => 'Provincias',
                    'value' => Provincia::query()->count(),
                    'hint' => 'Cobertura territorial',
                    'icon' => 'map',
                    'accent' => 'amber',
                    'url' => route('provincias.index'),
                ],
                [
                    'label' => 'Municipios',
                    'value' => Municipio::query()->count(),
                    'hint' => 'Registrados',
                    'icon' => 'map-pin',
                    'accent' => 'amber',
                    'url' => route('municipios.index'),
                ],
                [
                    'label' => 'Unidades de medida',
                    'value' => UnidadMedida::query()->count(),
                    'hint' => 'Para el catálogo',
                    'icon' => 'ruler',
                    'accent' => 'blue',
                    'url' => route('unidades-medida.index'),
                ],
                [
                    'label' => 'Entidades',
                    'value' => Entidad::query()->count(),
                    'hint' => 'Registradas',
                    'icon' => 'building-2',
                    'accent' => 'green',
                    'url' => null,
                ],
            ]);
        }

        $titulo = 'Dashboard | TechNova Warehouse';

        $totalAlmacenes = Almacen::query()->where('activo', true)->count();

        return view('admin.dashboard', compact(
            'titulo',
            'mock',
            'fechaHoy',
            'recentCustomers',
            'totalCustomers',
            'customersThisMonth',
            'realSummary',
            'canManageNomencladores',
            'hasInventoryData',
            'totalAlmacenes',
        ));
    }

    /**
     * Combinar los datos de demostración con las métricas reales del inventario.
     *
     * @param  array<string, mixed>  $mock
     * @return array<string, mixed>
     */
    private function dashboardData(array $mock, bool $hasInventoryData): array
    {
        if (! $hasInventoryData) {
            return $mock;
        }

        $valorInventario = (float) Inventario::query()
            ->selectRaw('COALESCE(SUM(cantidad * costo_promedio), 0) as valor')
            ->value('valor');

        $stockBajo = Producto::query()
            ->activos()
            ->withSum('inventarios as stock_total', 'cantidad')
            ->get()
            ->filter(fn (Producto $producto): bool => (float) ($producto->stock_total ?? 0) <= (float) $producto->stock_minimo)
            ->sortBy(fn (Producto $producto): float => (float) ($producto->stock_total ?? 0))
            ->values();

        $mock['kpis'][0] = [
            'label' => 'Valor del inventario',
            'value' => $valorInventario,
            'format' => 'currency',
            'variation' => null,
            'tone' => 'neutral',
            'icon' => 'package',
            'accent' => 'green',
            'hint' => 'Costo total del stock disponible',
            'spark' => [],
        ];

        $mock['kpis'][1] = [
            'label' => 'Productos con stock bajo',
            'value' => $stockBajo->count(),
            'format' => 'number',
            'variation' => null,
            'tone' => 'negative',
            'icon' => 'alert-triangle',
            'accent' => 'amber',
            'hint' => 'Requieren reposición',
            'spark' => [],
        ];

        $mock['inventory_chart'] = $this->inventoryChart() ?? $mock['inventory_chart'];

        $mock['low_stock'] = $stockBajo
            ->take(4)
            ->map(fn (Producto $producto): array => [
                'product' => $producto->nombre,
                'sku' => $producto->sku,
                'stock' => (float) ($producto->stock_total ?? 0),
                'min' => (float) $producto->stock_minimo,
                'status' => (float) ($producto->stock_total ?? 0) <= 0 ? 'critical' : 'low',
            ])
            ->values()
            ->all();

        $topProducts = $this->topProducts();
        $mock['top_products'] = $topProducts ?? $mock['top_products'];
        $mock['top_products_real'] = $topProducts !== null;

        return $mock;
    }

    /**
     * Distribución de unidades disponibles por categoría.
     *
     * @return array{labels: array<int, string>, series: array<int, float>, total_label: string}|null
     */
    private function inventoryChart(): ?array
    {
        if ((float) Inventario::query()->sum('cantidad') <= 0) {
            return null;
        }

        $filas = CategoriaProducto::query()
            ->leftJoin('productos', 'productos.categoria_producto_id', '=', 'categorias_productos.id')
            ->leftJoin('inventarios', 'inventarios.producto_id', '=', 'productos.id')
            ->selectRaw('categorias_productos.nombre as categoria, COALESCE(SUM(inventarios.cantidad), 0) as cantidad')
            ->groupBy('categorias_productos.id', 'categorias_productos.nombre')
            ->orderByDesc('cantidad')
            ->get()
            ->filter(fn (CategoriaProducto $categoria): bool => (float) $categoria->cantidad > 0)
            ->values();

        if ($filas->isEmpty()) {
            return null;
        }

        $principales = $filas->take(5);
        $resto = (float) $filas->slice(5)->sum(fn (CategoriaProducto $categoria): float => (float) $categoria->cantidad);

        $labels = $principales
            ->map(fn (CategoriaProducto $categoria): string => $categoria->categoria)
            ->all();

        $series = $principales
            ->map(fn (CategoriaProducto $categoria): float => (float) $categoria->cantidad)
            ->all();

        if ($resto > 0) {
            $labels[] = 'Otras categorías';
            $series[] = $resto;
        }

        return [
            'labels' => $labels,
            'series' => $series,
            'total_label' => 'Unidades',
        ];
    }

    /**
     * Productos con más unidades despachadas.
     *
     * @return array<int, array{product: string, sku: string, units: float, revenue: float, trend: string, tone: string}>|null
     */
    private function topProducts(): ?array
    {
        $filas = DB::table('operacion_inventario_lineas as lineas')
            ->join('operaciones_inventario as operaciones', 'operaciones.id', '=', 'lineas.operacion_inventario_id')
            ->join('productos', 'productos.id', '=', 'lineas.producto_id')
            ->where('operaciones.tipo', 'salida')
            ->selectRaw('productos.nombre as product, productos.sku, SUM(lineas.cantidad) as units, SUM(lineas.cantidad * productos.precio_venta) as revenue')
            ->groupBy('productos.id', 'productos.nombre', 'productos.sku')
            ->orderByDesc('units')
            ->limit(4)
            ->get();

        if ($filas->isEmpty()) {
            return null;
        }

        return $filas
            ->map(fn (object $fila): array => [
                'product' => $fila->product,
                'sku' => $fila->sku,
                'units' => (float) $fila->units,
                'revenue' => (float) $fila->revenue,
                'trend' => '—',
                'tone' => 'positive',
            ])
            ->all();
    }
}
