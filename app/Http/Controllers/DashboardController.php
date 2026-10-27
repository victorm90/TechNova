<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Entidad;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Servicio;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Mostrar el panel operativo de inventario, ventas y facturación.
     */
    public function index(Request $request): View
    {
        $canManageNomencladores = $request->user()->can('manage-nomencladores');

        $mock = config('warehouse_mock');

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
                    'accent' => 'teal',
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

        return view('admin.dashboard', compact(
            'titulo',
            'mock',
            'fechaHoy',
            'recentCustomers',
            'totalCustomers',
            'customersThisMonth',
            'realSummary',
            'canManageNomencladores',
        ));
    }
}
