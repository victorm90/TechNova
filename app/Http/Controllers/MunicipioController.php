<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MunicipioController extends Controller
{
    /**
     * Mostrar los municipios.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $municipios = Municipio::query()
            ->with('provincia')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%")
                        ->orWhereHas('provincia', function (Builder $provinceQuery) use ($search) {
                            $provinceQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('nomencladores.index', [
            'items' => $municipios,
            'titulo' => 'Municipios',
            'descripcion' => 'Listado de municipios registrados',
            'ruta' => 'municipios.index',
            'tipo' => 'municipios',
            'search' => $search,
        ]);
    }
}
