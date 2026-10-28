@php
    $fmt = fn (mixed $valor): string => number_format((float) $valor, 2, ',', '.');
    $moneda = $documento['moneda'];
    $esFactura = $documento['tipo'] === 'FACTURA';
@endphp

<div class="doc">
    <table class="doc-header">
        <tr>
            <td class="doc-header-logo">
                @if ($documento['logo'])
                    <img src="{{ $documento['logo'] }}" alt="Logotipo de la empresa">
                @else
                    <div class="doc-logo-placeholder">{{ $documento['empresa']['nombre'] }}</div>
                @endif
            </td>
            <td class="doc-header-empresa">
                <div class="doc-empresa-nombre">{{ $documento['empresa']['nombre'] }}</div>
                <div>{{ $documento['empresa']['direccion'] }}</div>
                <div>Tel.: {{ $documento['empresa']['telefono'] }} · {{ $documento['empresa']['correo'] }}</div>
                <div>ID fiscal: {{ $documento['empresa']['identificacion_fiscal'] }}</div>
                <div class="doc-demo">DATOS DE EJEMPLO · REEMPLAZAR ANTES DE USAR EN PRODUCCIÓN</div>
            </td>
            <td class="doc-header-titulo">
                <div class="doc-tipo">{{ $documento['tipo'] }}</div>
                <div class="doc-numero">{{ $documento['numero'] }}</div>
                <div>Fecha: {{ $documento['fecha']?->format('d/m/Y') }}</div>
                @if ($esFactura && ! empty($documento['oferta_codigo']))
                    <div>Oferta: {{ $documento['oferta_codigo'] }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="doc-cliente">
        <tr>
            <td class="doc-cliente-titulo">Cliente</td>
            <td>
                <strong>{{ $documento['cliente']['nombre'] }}</strong>
                @if ($documento['cliente']['direccion'])
                    <div>{{ $documento['cliente']['direccion'] }}</div>
                @endif
                <div>
                    @if ($documento['cliente']['telefono'])
                        Tel.: {{ $documento['cliente']['telefono'] }}
                    @endif
                    @if ($documento['cliente']['identificacion'])
                        · CI: {{ $documento['cliente']['identificacion'] }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if (count($documento['servicios']) > 0)
        <h2 class="doc-seccion">Servicios</h2>
        <table class="doc-tabla">
            <thead>
                <tr>
                    <th class="doc-col-num">No.</th>
                    <th>Descripción</th>
                    <th class="doc-col-um">U/M</th>
                    <th class="doc-col-cant">Cantidad</th>
                    <th class="doc-col-precio">Precio</th>
                    <th class="doc-col-subtotal">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documento['servicios'] as $linea)
                    <tr>
                        <td class="doc-center">{{ $linea['numero'] }}</td>
                        <td>{{ $linea['descripcion'] }}</td>
                        <td class="doc-center">{{ $linea['unidad'] }}</td>
                        <td class="doc-right">{{ $fmt($linea['cantidad']) }}</td>
                        <td class="doc-right">{{ $fmt($linea['precio']) }}</td>
                        <td class="doc-right">{{ $fmt($linea['subtotal']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="doc-right doc-bold">Subtotal servicios</td>
                    <td class="doc-right doc-bold">{{ $fmt($documento['subtotales']['servicios']) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    @if (count($documento['productos']) > 0)
        <h2 class="doc-seccion">Recursos y materiales</h2>
        <table class="doc-tabla">
            <thead>
                <tr>
                    <th>Producto / material</th>
                    @unless ($esFactura)
                        <th>Servicio asociado</th>
                    @endunless
                    <th class="doc-col-um">U/M</th>
                    <th class="doc-col-cant">Cantidad</th>
                    <th class="doc-col-precio">Precio venta</th>
                    <th class="doc-col-subtotal">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documento['productos'] as $linea)
                    <tr>
                        <td>{{ $linea['descripcion'] }}</td>
                        @unless ($esFactura)
                            <td>{{ $linea['servicio'] }}</td>
                        @endunless
                        <td class="doc-center">{{ $linea['unidad'] }}</td>
                        <td class="doc-right">{{ $fmt($linea['cantidad']) }}</td>
                        <td class="doc-right">{{ $fmt($linea['precio']) }}</td>
                        <td class="doc-right">{{ $fmt($linea['subtotal']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $esFactura ? 4 : 5 }}" class="doc-right doc-bold">Subtotal materiales</td>
                    <td class="doc-right doc-bold">{{ $fmt($documento['subtotales']['productos']) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    @if (count($documento['gastos']) > 0)
        <h2 class="doc-seccion">Gastos</h2>
        <table class="doc-tabla">
            <thead>
                <tr>
                    <th class="doc-col-num">No.</th>
                    <th>Descripción</th>
                    <th class="doc-col-cant">Cantidad</th>
                    <th class="doc-col-precio">Precio</th>
                    <th class="doc-col-subtotal">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documento['gastos'] as $linea)
                    <tr>
                        <td class="doc-center">{{ $linea['numero'] }}</td>
                        <td>{{ $linea['descripcion'] }}</td>
                        <td class="doc-right">{{ $fmt($linea['cantidad']) }}</td>
                        <td class="doc-right">{{ $fmt($linea['precio']) }}</td>
                        <td class="doc-right">{{ $fmt($linea['subtotal']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="doc-right doc-bold">Subtotal gastos</td>
                    <td class="doc-right doc-bold">{{ $fmt($documento['subtotales']['gastos']) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <table class="doc-totales">
        <tr>
            <td class="doc-totales-etiqueta">Subtotal servicios</td>
            <td class="doc-totales-valor">{{ $fmt($documento['subtotales']['servicios']) }}</td>
        </tr>
        <tr>
            <td class="doc-totales-etiqueta">Materiales</td>
            <td class="doc-totales-valor">{{ $fmt($documento['subtotales']['productos']) }}</td>
        </tr>
        <tr>
            <td class="doc-totales-etiqueta">Gastos</td>
            <td class="doc-totales-valor">{{ $fmt($documento['subtotales']['gastos']) }}</td>
        </tr>
        <tr class="doc-total-final">
            <td class="doc-totales-etiqueta">TOTAL {{ $moneda }}</td>
            <td class="doc-totales-valor">{{ $fmt($documento['subtotales']['total']) }}</td>
        </tr>
    </table>

    <table class="doc-condiciones">
        <tr>
            <td class="doc-condiciones-titulo">Forma de pago</td>
            <td>{{ $documento['forma_pago'] ?: 'No especificada' }}</td>
        </tr>
        @if ($documento['observaciones'])
            <tr>
                <td class="doc-condiciones-titulo">Observaciones</td>
                <td>{{ $documento['observaciones'] }}</td>
            </tr>
        @endif
        @if ($documento['nota'])
            <tr>
                <td class="doc-condiciones-titulo">Nota</td>
                <td>{!! nl2br(e($documento['nota'])) !!}</td>
            </tr>
        @endif
        <tr>
            <td class="doc-condiciones-titulo">Condiciones</td>
            <td>
                Los precios pueden variar según los gastos reales y el costo de los materiales al momento
                de la ejecución. Esta {{ $esFactura ? 'factura' : 'oferta' }} tiene una vigencia de
                {{ $documento['vigencia'] }} días.
            </td>
        </tr>
    </table>

    <table class="doc-firmas">
        <tr>
            <td>
                <div class="doc-firma-linea"></div>
                <div class="doc-firma-titulo">Firma del proveedor</div>
                <div>{{ $documento['firma'] }}</div>
            </td>
            <td>
                <div class="doc-firma-linea"></div>
                <div class="doc-firma-titulo">Firma y aceptación del cliente</div>
                <div>{{ $documento['cliente']['nombre'] }}</div>
            </td>
        </tr>
    </table>

    <div class="doc-pie">
        Documento generado por el sistema. Los datos de la entidad emisora son de ejemplo y deben
        reemplazarse mediante las variables <strong>EMPRESA_*</strong> antes de su uso en producción.
    </div>
</div>
