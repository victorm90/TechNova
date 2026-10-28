<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Datos de la entidad emisora (DEMO)
    |--------------------------------------------------------------------------
    |
    | Todos los valores por defecto son datos de ejemplo. Reemplácelos mediante
    | variables de entorno antes de usar los documentos en producción.
    |
    */

    'nombre' => env('EMPRESA_NOMBRE', 'TechNova Servicios Integrales (Datos de ejemplo)'),

    'direccion' => env('EMPRESA_DIRECCION', 'Calle Ejemplo #123, Reparto Demo, La Habana (Dato de ejemplo)'),

    'telefono' => env('EMPRESA_TELEFONO', '+53 5555 0101 (Dato de ejemplo)'),

    'correo' => env('EMPRESA_CORREO', 'contacto@ejemplo.test'),

    'identificacion_fiscal' => env('EMPRESA_NIT', 'NIT-DEMO-000000 (Dato de ejemplo)'),

    'sitio_web' => env('EMPRESA_WEB', 'https://ejemplo.test'),

    /*
    |--------------------------------------------------------------------------
    | Logotipo
    |--------------------------------------------------------------------------
    |
    | Ruta relativa al directorio public/ o ruta absoluta del sistema. Si el
    | archivo no existe, el documento se genera sin logotipo.
    |
    */

    'logo' => env('EMPRESA_LOGO', 'assets/images/logo.png'),

    /*
    |--------------------------------------------------------------------------
    | Documentos
    |--------------------------------------------------------------------------
    */

    'moneda' => 'MN',

    'firma_pie' => env('EMPRESA_FIRMA_PIE', 'Departamento Comercial · TechNova'),

    'vigencia_dias' => 15,

];
