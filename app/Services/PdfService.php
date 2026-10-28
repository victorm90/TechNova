<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfService
{
    /**
     * Generar y descargar un PDF a partir de una vista Blade.
     *
     * @param  array<string, mixed>  $datos
     */
    public function descargar(string $vista, array $datos, string $archivo): Response
    {
        return Pdf::loadView($vista, $datos)
            ->setPaper('letter', 'portrait')
            ->setOption([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ])
            ->download($archivo);
    }
}
