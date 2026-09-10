<?php

namespace App\Models;

use InvalidArgumentException;

/** Definición compartida por captura, validación y reporte. */
final class ValuoFormulario
{
    public const GENERALES = [
        'Identificación y encargo' => [
            'numero_informe' => 'Número de avalúo', 'destinatario' => 'Dirigido a', 'proposito' => 'Propósito del informe',
            'perito' => 'Perito valuador', 'registro_perito' => 'Registro del perito', 'propietarios' => 'Propietarios',
            'demandado' => 'Demandado / titular del derecho', 'matricula' => 'Matrícula', 'direccion_registral' => 'Dirección según documento legal',
            'direccion_actual' => 'Dirección actual', 'fecha_inspeccion' => 'Fecha y hora de inspección',
            'tipo_inmueble' => 'Tipo de inmueble', 'zona' => 'Zona', 'uso_actual' => 'Uso actual', 'uso_potencial' => 'Uso potencial', 'ocupante' => 'Utilizado por',
        ],
        'Entorno y terreno' => [
            'servicios_entorno' => 'Servicios públicos y privados', 'equipamiento' => 'Equipamiento urbano', 'otros_servicios' => 'Otros servicios',
            'uso_suelo' => 'Uso de suelo predominante', 'densidad' => 'Densidad poblacional', 'construcciones_entorno' => 'Construcciones predominantes',
            'nivel_socioeconomico' => 'Nivel socioeconómico', 'contaminacion' => 'Contaminación ambiental', 'distancia_urbana' => 'Distancia a centros urbanos',
            'distancia_comercio' => 'Distancia a centros de comercio', 'acceso' => 'Tipo de vía y acceso', 'observaciones_entorno' => 'Observaciones del entorno',
            'forma' => 'Forma del terreno', 'relacion_frente_fondo' => 'Relación frente / fondo', 'servidumbres' => 'Servidumbres',
            'vegetacion' => 'Vegetación', 'servicios_inmueble' => 'Servicios del inmueble', 'linderos' => 'Linderos y medidas perimétricas',
            'observaciones_area' => 'Observaciones sobre diferencias de área', 'coordenadas' => 'Coordenadas', 'mapa_parcela' => 'Mapa / parcela',
        ],
        'Descripción de la construcción' => [
            'sistema_constructivo' => 'Sistema constructivo', 'niveles' => 'Niveles', 'paredes' => 'Paredes', 'cielo' => 'Cielo raso',
            'techo' => 'Techo', 'puertas' => 'Puertas', 'ventanas' => 'Ventanas', 'agua' => 'Agua potable', 'aguas_servidas' => 'Aguas servidas',
            'aguas_lluvias' => 'Descarga de aguas pluviales', 'electricidad' => 'Instalaciones eléctricas', 'no_valuadas' => 'Construcciones no valuadas',
        ],
    ];
    // Campo => [etiqueta, tipo, valor inicial]
    public const CONSTRUCCION = [
        'descripcion' => ['Componente', 'text', ''], 'area' => ['Área (m²)', 'number', ''],
        'edad' => ['Edad (años)', 'number', ''], 'vida' => ['Vida útil (años)', 'number', ''],
        'estado' => ['Conservación', 'text', ''], 'k2' => ['K2 decimal (0.009191 = 0.9191%)', 'number', ''],
        'valor_nuevo' => ['Reposición total ($), no precio por m²', 'number', ''],
    ];
    public const SUJETO = [
        'frente' => ['Frente (m)', 'number', ''], 'fondo' => ['Fondo (m)', 'number', ''],
        'area_construida' => ['Área construida (m²)', 'number', ''], 'edad' => ['Edad (años)', 'number', ''],
        'vida' => ['Vida útil (años)', 'number', ''], 'estado' => ['Conservación', 'text', ''], 'q' => ['Factor Q (0 a 1)', 'number', ''],
    ];
    public const COMPARABLE = [
        'direccion' => ['Ubicación', 'text', ''], 'propietario' => ['Propietario', 'text', ''], 'matricula' => ['Matrícula', 'text', ''],
        'tipo' => ['Tipo de inmueble', 'text', ''], 'zona' => ['Zona', 'text', ''], 'fuente' => ['Fuente / respaldo', 'text', ''],
        'fecha' => ['Fecha de operación', 'date', ''], 'condicion' => ['Condición (venta, hipoteca...)', 'text', ''],
        'precio_original' => ['Precio original ($)', 'number', ''], 'precio_actual' => ['Precio actual ($)', 'number', ''],
        'justificacion_precio' => ['Justificación del precio actualizado', 'text', ''], 'area_terreno' => ['Terreno (m²)', 'number', ''],
        'frente' => ['Frente (m)', 'number', ''], 'fondo' => ['Fondo (m)', 'number', ''],
        'area_construida' => ['Construcción (m²)', 'number', ''], 'edad' => ['Edad (años)', 'number', ''],
        'vida' => ['Vida útil (años)', 'number', ''], 'estado' => ['Conservación', 'text', ''], 'q' => ['Factor Q (0 a 1)', 'number', ''],
        'funcionalidad' => ['Funcionalidad', 'number', 1], 'ubicacion' => ['Factor ubicación', 'number', 1],
        'topografia' => ['Topografía', 'number', 1], 'accesibilidad' => ['Accesibilidad', 'number', 1],
    ];

    public static function texto(mixed $value, int $max = 10000): string
    {
        if (!is_scalar($value) && $value !== null) {
            throw new InvalidArgumentException('Se recibió un campo con formato inválido.');
        }
        $v = trim((string) $value);
        if (strlen($v) > $max) {
            throw new InvalidArgumentException("Un campo supera el máximo de {$max} caracteres.");
        }
        return $v;
    }

    public static function normalizar(array $input): array
    {
        $d = [];
        foreach (['referencia', 'fecha_del_valuo', 'id_proyecto', 'estado', 'revision', 'area_escritura', 'area_inspeccion', 'area_valuada', 'valor_vara',
            'metodo', 'valor_adoptado', 'porcentaje_derecho', 'conclusion', 'situacion_juridica', 'revision_perito'] as $key) {
            $d[$key] = self::texto($input[$key] ?? '');
        }
        foreach (self::GENERALES as $fields) {
            foreach ($fields as $key => $label) {
                $d[$key] = self::texto($input[$key] ?? '');
            }
        }
        foreach (['factores', 'sujeto', 'construcciones', 'comparables'] as $key) {
            if (!is_array($input[$key] ?? [])) {
                throw new InvalidArgumentException("Formato inválido de {$key}.");
            }
        }
        foreach (ValuoCalculo::FACTORES as $key) {
            $d['factores'][$key] = self::texto($input['factores'][$key] ?? '');
        }
        foreach (self::SUJETO as $key => $spec) {
            $d['sujeto'][$key] = self::texto($input['sujeto'][$key] ?? '');
        }
        foreach (['construcciones' => self::CONSTRUCCION, 'comparables' => self::COMPARABLE] as $collection => $fields) {
            $rows = $input[$collection] ?? [];
            if (count($rows) > ($collection === 'comparables' ? 3 : 20)) {
                throw new InvalidArgumentException('Máximo 20 componentes y 3 comparables.');
            }
            $d[$collection] = [];
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    throw new InvalidArgumentException('Detalle inválido.');
                }
                $item = [];
                foreach ($fields as $key => $spec) {
                    $item[$key] = self::texto($row[$key] ?? '');
                }
                $d[$collection][] = $item;
            }
        }
        return $d;
    }

    public static function validarNumeros(array $d): void
    {
        foreach (['construcciones' => self::CONSTRUCCION, 'comparables' => self::COMPARABLE] as $collection => $fields) {
            foreach ($d[$collection] as $item) {
                foreach ($fields as $key => $spec) {
                    if ($spec[1] === 'number' && $item[$key] !== '') {
                        ValuoCalculo::numero($item[$key], $spec[0]);
                    }
                }
            }
        }
        foreach (['area_escritura', 'area_inspeccion'] as $key) {
            if ($d[$key] !== '') {
                ValuoCalculo::numero($d[$key], $key, 0.000001);
            }
        }
    }

    public static function nuevo(): array
    {
        return ['fecha_del_valuo' => date('Y-m-d'), 'estado' => 'Borrador', 'metodo' => 'costo', 'porcentaje_derecho' => 100,
            'factores' => array_fill_keys(ValuoCalculo::FACTORES, 1), 'construcciones' => [[]], 'comparables' => []];
    }
}
