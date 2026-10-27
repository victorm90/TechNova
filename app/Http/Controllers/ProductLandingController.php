<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Servicio;
use Illuminate\View\View;

class ProductLandingController extends Controller
{
    /**
     * Mostrar la página de presentación.
     */
    public function index(): View
    {
        return view('admin.product-landing', [
            'totalCustomers' => Customer::query()->count(),
            'totalServicios' => Servicio::query()->count(),
            'totalProvincias' => Provincia::query()->count(),
            'totalMunicipios' => Municipio::query()->count(),
        ]);
    }
}
