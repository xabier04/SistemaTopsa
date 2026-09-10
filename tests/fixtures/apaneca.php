<?php
// Datos numéricos de Hoja1. Sin nombres, direcciones ni identificadores personales.
return [
    'area_valuada' => 132.251, 'valor_vara' => 185,
    'factores' => array_fill_keys(['ubicacion','area','frente','fondo','forma','via','pendiente','servicios','riesgo'], 1),
    'construcciones' => [['area' => 129.05, 'edad' => 30, 'vida' => 60, 'k2' => 0.009191, 'valor_nuevo' => 45800]],
    'sujeto' => ['frente' => 9.98, 'fondo' => 16, 'area_construida' => 129.05, 'edad' => 30, 'vida' => 60, 'q' => 0.9191],
    'comparables' => [
        ['area_terreno' => 187.68, 'frente' => 25.11, 'fondo' => 7.47, 'area_construida' => 172.66, 'edad' => 30, 'vida' => 60, 'q' => 0.9748,
            'precio_actual' => 57000, 'funcionalidad' => 1, 'ubicacion' => 1.2, 'topografia' => 1, 'accesibilidad' => 1.2],
        ['area_terreno' => 225.44, 'frente' => 14.4, 'fondo' => 19.25, 'area_construida' => 200.15, 'edad' => 30, 'vida' => 60, 'q' => 0.9191,
            'precio_actual' => 62000, 'funcionalidad' => 1, 'ubicacion' => 1, 'topografia' => 1, 'accesibilidad' => 1],
        ['area_terreno' => 134.12, 'frente' => 9.25, 'fondo' => 14, 'area_construida' => 120.36, 'edad' => 25, 'vida' => 60, 'q' => 0.9748,
            'precio_actual' => 78000, 'funcionalidad' => 1, 'ubicacion' => 1, 'topografia' => 1, 'accesibilidad' => 1],
    ], 'metodo' => 'comparativo', 'valor_adoptado' => 60000, 'porcentaje_derecho' => 50,
];
