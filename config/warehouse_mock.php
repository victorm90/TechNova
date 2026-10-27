<?php

/*
|--------------------------------------------------------------------------
| Datos de demostración del dashboard
|--------------------------------------------------------------------------
|
| Estas métricas representan inventario, ventas y facturación. Se mantienen
| fuera de la base de datos para no alterar la lógica actual del sistema.
| Reemplazar cada arreglo por consultas reales cuando existan los módulos.
|
*/

return [
    'period' => 'Últimos 12 meses',

    'kpis' => [
        [
            'label' => 'Valor del inventario',
            'value' => 128450,
            'format' => 'currency',
            'variation' => '+4.8%',
            'tone' => 'positive',
            'icon' => 'package',
            'accent' => 'green',
            'hint' => 'Costo total del stock disponible',
            'spark' => [42, 48, 45, 53, 58, 55, 62, 68, 66, 72, 76, 81],
        ],
        [
            'label' => 'Productos con stock bajo',
            'value' => 18,
            'format' => 'number',
            'variation' => '+3',
            'tone' => 'negative',
            'icon' => 'alert-triangle',
            'accent' => 'amber',
            'hint' => 'Requieren reposición',
            'spark' => [10, 11, 12, 12, 14, 13, 15, 16, 15, 17, 18, 18],
        ],
        [
            'label' => 'Ventas del periodo',
            'value' => 86240,
            'format' => 'currency',
            'variation' => '+12.5%',
            'tone' => 'positive',
            'icon' => 'trending-up',
            'accent' => 'blue',
            'hint' => 'Facturación acumulada',
            'spark' => [52, 58, 55, 64, 62, 70, 75, 72, 80, 78, 88, 94],
        ],
        [
            'label' => 'Facturas pendientes',
            'value' => 14320,
            'format' => 'currency',
            'variation' => '+2',
            'tone' => 'negative',
            'icon' => 'file-text',
            'accent' => 'teal',
            'hint' => '7 facturas por cobrar',
            'spark' => [38, 42, 40, 46, 44, 49, 47, 52, 55, 53, 58, 61],
        ],
    ],

    'activity' => [
        ['label' => 'Rotación de inventario', 'value' => '4.2x', 'hint' => 'Promedio del periodo', 'icon' => 'refresh-cw'],
        ['label' => 'Órdenes de compra', 'value' => '12', 'hint' => '3 por recibir', 'icon' => 'clipboard-list'],
        ['label' => 'Tiempo de despacho', 'value' => '1.8 días', 'hint' => 'Promedio por pedido', 'icon' => 'truck'],
    ],

    'sales_chart' => [
        'categories' => ['Nov', 'Dic', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
        'current' => [5200, 6100, 5800, 7200, 6800, 7900, 8400, 7600, 9100, 8800, 9600, 10200],
        'previous' => [4600, 5200, 5400, 6100, 6300, 6900, 7100, 7200, 7800, 8100, 8500, 8900],
    ],

    'inventory_chart' => [
        'labels' => ['Herramientas', 'Empaque', 'Insumos', 'Equipos', 'Otros'],
        'series' => [38, 24, 18, 12, 8],
    ],

    'billing_chart' => [
        'categories' => ['May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
        'paid' => [14200, 16800, 15900, 18400, 20100, 22300],
        'pending' => [3200, 2900, 4100, 3600, 3900, 4300],
        'overdue' => [1200, 900, 1500, 1100, 1700, 1400],
    ],

    'low_stock' => [
        ['product' => 'Caja corrugada 40×30', 'sku' => 'CAJ-4030', 'stock' => 4, 'min' => 20, 'status' => 'low'],
        ['product' => 'Cinta de embalaje 50 m', 'sku' => 'CIN-50', 'stock' => 6, 'min' => 25, 'status' => 'low'],
        ['product' => 'Guantes de manipulación', 'sku' => 'GUA-M', 'stock' => 9, 'min' => 30, 'status' => 'low'],
        ['product' => 'Etiqueta térmica 100×150', 'sku' => 'ETI-100', 'stock' => 12, 'min' => 40, 'status' => 'low'],
    ],

    'pending_invoices' => [
        ['invoice' => 'FAC-2026-0184', 'client' => 'Distribuidora del Norte', 'amount' => 2450.00, 'due' => '2026-10-18', 'status' => 'overdue'],
        ['invoice' => 'FAC-2026-0191', 'client' => 'Comercial La Habana', 'amount' => 1890.50, 'due' => '2026-10-24', 'status' => 'overdue'],
        ['invoice' => 'FAC-2026-0203', 'client' => 'Almacenes del Centro', 'amount' => 3120.00, 'due' => '2026-11-02', 'status' => 'pending'],
        ['invoice' => 'FAC-2026-0208', 'client' => 'Suministros Occidente', 'amount' => 980.00, 'due' => '2026-11-05', 'status' => 'pending'],
    ],

    'recent_sales' => [
        ['date' => '2026-10-26', 'client' => 'Comercial La Habana', 'reference' => 'VEN-1042', 'items' => 6, 'total' => 1280.50, 'status' => 'paid'],
        ['date' => '2026-10-26', 'client' => 'Distribuidora del Norte', 'reference' => 'VEN-1041', 'items' => 3, 'total' => 845.00, 'status' => 'pending'],
        ['date' => '2026-10-25', 'client' => 'Almacenes del Centro', 'reference' => 'VEN-1040', 'items' => 9, 'total' => 2210.75, 'status' => 'paid'],
        ['date' => '2026-10-25', 'client' => 'Suministros Occidente', 'reference' => 'VEN-1039', 'items' => 2, 'total' => 390.00, 'status' => 'overdue'],
        ['date' => '2026-10-24', 'client' => 'Comercial La Habana', 'reference' => 'VEN-1038', 'items' => 4, 'total' => 675.25, 'status' => 'paid'],
        ['date' => '2026-10-24', 'client' => 'Grupo Logístico Este', 'reference' => 'VEN-1037', 'items' => 7, 'total' => 1540.00, 'status' => 'pending'],
    ],

    'top_products' => [
        ['product' => 'Caja corrugada 40×30', 'sku' => 'CAJ-4030', 'units' => 320, 'revenue' => 4820.00, 'trend' => '+18.4%', 'tone' => 'positive'],
        ['product' => 'Cinta de embalaje 50 m', 'sku' => 'CIN-50', 'units' => 286, 'revenue' => 3140.50, 'trend' => '+11.2%', 'tone' => 'positive'],
        ['product' => 'Guantes de manipulación', 'sku' => 'GUA-M', 'units' => 198, 'revenue' => 2475.00, 'trend' => '-2.1%', 'tone' => 'negative'],
        ['product' => 'Etiqueta térmica 100×150', 'sku' => 'ETI-100', 'units' => 154, 'revenue' => 1890.75, 'trend' => '+6.8%', 'tone' => 'positive'],
    ],

    'warehouse_health' => [
        ['label' => 'Ocupación del almacén', 'value' => 72, 'suffix' => '%', 'hint' => '48 de 66 ubicaciones'],
        ['label' => 'Pedidos despachados hoy', 'value' => 23, 'suffix' => '', 'hint' => 'Meta diaria: 30'],
        ['label' => 'Precisión de inventario', 'value' => 98.4, 'suffix' => '%', 'hint' => 'Conteo cíclico'],
    ],
];
