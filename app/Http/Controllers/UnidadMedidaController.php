<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnidadMedidaController extends Controller
{
    /**
     * Mostrar las unidades de medida.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $unidades = UnidadMedida::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('abreviatura', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('nomencladores.index', [
            'items' => $unidades,
            'titulo' => 'Unidades de medida',
            'descripcion' => 'Listado de unidades de medida registradas',
            'ruta' => 'unidades-medida.index',
            'tipo' => 'unidades',
            'search' => $search,
        ]);
    }
}
