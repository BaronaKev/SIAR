<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Impuesto aplicado en la facturación (HU024)
    |--------------------------------------------------------------------------
    | En Colombia los restaurantes normalmente liquidan el Impuesto Nacional
    | al Consumo (8 %). Se configura en el .env con SIAR_IMPUESTO_PORCENTAJE.
    */
    'impuesto_porcentaje' => (float) env('SIAR_IMPUESTO_PORCENTAJE', 8),

    /*
    |--------------------------------------------------------------------------
    | Segundos entre recargas automáticas de la vista de cocina (HU021)
    |--------------------------------------------------------------------------
    */
    'cocina_refresco_segundos' => (int) env('SIAR_COCINA_REFRESCO', 30),

];
