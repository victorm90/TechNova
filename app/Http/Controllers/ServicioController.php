<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicioController extends Controller
{
    /**
     * Mostrar los servicios.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $servicios = Servicio::query()
            ->with('unidadMedida')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhereHas('unidadMedida', function (Builder $unitQuery) use ($search) {
                            $unitQuery->where('nombre', 'like', "%{$search}%")
                                ->orWhere('abreviatura', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('nomencladores.index', [
            'items' => $servicios,
            'titulo' => 'Servicios',
            'descripcion' => 'Listado de servicios registrados',
            'ruta' => 'servicios.index',
            'tipo' => 'servicios',
            'search' => $search,
        ]);
    }
}
