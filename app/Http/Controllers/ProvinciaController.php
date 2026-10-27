<?php

namespace App\Http\Controllers;

use App\Models\Provincia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProvinciaController extends Controller
{
    /**
     * Mostrar las provincias.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $provincias = Provincia::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('nomencladores.index', [
            'items' => $provincias,
            'titulo' => 'Provincias',
            'descripcion' => 'Listado de provincias registradas',
            'ruta' => 'provincias.index',
            'tipo' => 'provincias',
            'search' => $search,
        ]);
    }
}
