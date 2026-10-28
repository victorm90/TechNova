<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Services\DocumentoService;
use App\Services\PdfService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FacturaController extends Controller
{
    public function __construct(
        private readonly DocumentoService $documentos,
        private readonly PdfService $pdf,
    ) {}

    /**
     * Detalle de una factura emitida.
     */
    public function show(Factura $factura): View
    {
        $factura->load(['oferta', 'customer', 'almacen', 'user', 'lineas']);

        return view('facturas.show', compact('factura'));
    }

    /**
     * Vista imprimible de la factura.
     */
    public function imprimir(Factura $factura): View
    {
        return view('documentos.print', [
            'documento' => $this->documentos->factura($factura),
        ]);
    }

    /**
     * Descargar la factura final en PDF.
     */
    public function pdf(Factura $factura): Response
    {
        return $this->pdf->descargar(
            'documentos.pdf',
            ['documento' => $this->documentos->factura($factura)],
            'Factura-'.$factura->codigo.'.pdf',
        );
    }
}
