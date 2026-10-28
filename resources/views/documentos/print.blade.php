<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documento['tipo'] }} {{ $documento['numero'] }}</title>
    <style>
        :root {
            color-scheme: light;
        }

        body {
            margin: 0;
            background: #e2e8f0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1f2937;
        }

        .barra-impresion {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            background: #0f172a;
        }

        .barra-impresion button,
        .barra-impresion a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #059669;
            border-radius: 8px;
            background: #059669;
            color: #ffffff;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .barra-impresion a {
            border-color: #475569;
            background: #334155;
        }

        .hoja {
            width: 216mm;
            min-height: 279mm;
            margin: 16px auto;
            padding: 14mm 15mm;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
        }

        .doc {
            font-size: 9px;
        }

        .doc-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .doc-header td {
            vertical-align: top;
            padding: 0;
        }

        .doc-header-logo {
            width: 21%;
            padding-right: 10px !important;
        }

        .doc-header-logo img {
            max-width: 130px;
            max-height: 64px;
        }

        .doc-logo-placeholder {
            font-size: 11px;
            font-weight: bold;
            color: #047857;
        }

        .doc-header-empresa {
            width: 46%;
            font-size: 8px;
            line-height: 1.45;
            color: #475569;
            border-left: 2px solid #059669;
            padding-left: 10px !important;
        }

        .doc-empresa-nombre {
            font-size: 11px;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 2px;
        }

        .doc-demo {
            margin-top: 3px;
            font-size: 7px;
            font-weight: bold;
            color: #b45309;
        }

        .doc-header-titulo {
            width: 33%;
            text-align: right;
            font-size: 8px;
            line-height: 1.5;
        }

        .doc-tipo {
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #065f46;
        }

        .doc-numero {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .doc-cliente {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background: #f0fdf4;
        }

        .doc-cliente td {
            padding: 7px 9px;
            vertical-align: top;
            font-size: 8.5px;
            line-height: 1.45;
        }

        .doc-cliente-titulo {
            width: 15%;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-seccion {
            font-size: 9.5px;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin: 12px 0 4px;
            padding-bottom: 3px;
            border-bottom: 1px solid #a7f3d0;
        }

        .doc-tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .doc-tabla th {
            background: #065f46;
            color: #ffffff;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 5px 6px;
            text-align: left;
        }

        .doc-tabla td {
            padding: 4px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8.5px;
        }

        .doc-tabla tfoot td {
            background: #ecfdf5;
            border-bottom: none;
            padding: 5px 6px;
        }

        .doc-tabla tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .doc-col-num {
            width: 5%;
            text-align: center;
        }

        .doc-col-um {
            width: 8%;
            text-align: center;
        }

        .doc-col-cant {
            width: 11%;
            text-align: right;
        }

        .doc-col-precio {
            width: 14%;
            text-align: right;
        }

        .doc-col-subtotal {
            width: 15%;
            text-align: right;
        }

        .doc-right {
            text-align: right;
        }

        .doc-center {
            text-align: center;
        }

        .doc-bold {
            font-weight: bold;
        }

        .doc-totales {
            width: 45%;
            border-collapse: collapse;
            margin: 12px 0 0 auto;
        }

        .doc-totales td {
            padding: 4px 8px;
            font-size: 8.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        .doc-totales-etiqueta {
            text-align: right;
            color: #475569;
        }

        .doc-totales-valor {
            width: 38%;
            text-align: right;
            font-weight: bold;
        }

        .doc-total-final td {
            background: #065f46;
            color: #ffffff;
            font-size: 10px;
            border-bottom: none;
        }

        .doc-condiciones {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        .doc-condiciones td {
            padding: 4px 8px;
            vertical-align: top;
            font-size: 8px;
            line-height: 1.45;
            border: 1px solid #e2e8f0;
        }

        .doc-condiciones-titulo {
            width: 18%;
            font-weight: bold;
            color: #047857;
            background: #f0fdf4;
        }

        .doc-firmas {
            width: 100%;
            border-collapse: collapse;
            margin-top: 34px;
        }

        .doc-firmas td {
            width: 50%;
            padding: 0 26px;
            text-align: center;
            vertical-align: bottom;
            font-size: 8px;
        }

        .doc-firma-linea {
            border-top: 1px solid #334155;
            height: 1px;
            margin-bottom: 4px;
        }

        .doc-firma-titulo {
            font-weight: bold;
        }

        .doc-pie {
            margin-top: 22px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .barra-impresion {
                display: none;
            }

            .hoja {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            @page {
                margin: 14mm 15mm;
            }
        }
    </style>
</head>

<body>
    <div class="barra-impresion">
        <button type="button" onclick="window.print()">Imprimir / Guardar como PDF</button>
        <a href="{{ url()->previous() }}">Volver</a>
    </div>

    <div class="hoja">
        @include('documentos._cuerpo', ['documento' => $documento, 'esFactura' => $documento['tipo'] === 'FACTURA'])
    </div>

    <script>
        window.addEventListener('load', () => {
            window.setTimeout(() => window.print(), 350);
        });
    </script>
</body>

</html>
