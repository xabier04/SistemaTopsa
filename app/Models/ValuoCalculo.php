<?php

namespace App\Models;

use InvalidArgumentException;

/** Fórmulas de Hoja1 del archivo de Apaneca, sin corregir referencias encadenadas. */
final class ValuoCalculo
{
    public const VERSION = 'apaneca-excel-v1';
    public const VARAS_POR_METRO = 1.431024614;
    public const FACTORES = ['ubicacion', 'area', 'frente', 'fondo', 'forma', 'via', 'pendiente', 'servicios', 'riesgo'];

    public static function numero(mixed $value, string $label, float $min = 0, float $max = 999999999999): float
    {
        if (!is_scalar($value) || !is_numeric($value) || !is_finite((float) $value)
            || (float) $value < $min || (float) $value > $max) {
            throw new InvalidArgumentException("Revise {$label}: debe ser un número entre {$min} y {$max}.");
        }
        return (float) $value;
    }

    public static function calcular(array $d): array
    {
        $area = self::numero($d['area_valuada'] ?? null, 'área a valuar', 0.000001);
        $base = self::numero($d['valor_vara'] ?? null, 'valor base por v²', 0.000001);
        $factor = 1;
        foreach (self::FACTORES as $key) {
            $factor *= self::numero($d['factores'][$key] ?? null, "factor de terreno {$key}", 0.000001, 100);
        }
        $terreno = $area * self::VARAS_POR_METRO * $base * $factor;
        $construcciones = [];
        $construccion = 0;
        foreach ($d['construcciones'] ?? [] as $i => $c) {
            $edad = self::numero($c['edad'] ?? null, 'edad de construcción', 0, 1000);
            $vida = self::numero($c['vida'] ?? null, 'vida útil de construcción', 0.000001, 1000);
            if ($edad > $vida) {
                throw new InvalidArgumentException('La edad excede la vida útil. El perito debe revisar la vida útil antes de calcular.');
            }
            self::numero($c['area'] ?? null, 'área construida', 0.000001);
            $nuevo = self::numero($c['valor_nuevo'] ?? null, 'valor de reposición total', 0.000001);
            $residual = $nuevo * 0.1; // L128 = N128 * 0.1
            $k1 = $edad / $vida;
            $k2 = self::numero($c['k2'] ?? null, 'K2 (coeficiente decimal)', 0, 1);
            $k = $k1 + (1 - $k1) * $k2;
            $actual = $nuevo - ($nuevo - $residual) * $k;
            $construcciones[] = ['k1' => $k1, 'k' => $k, 'residual' => $residual, 'actual' => $actual, 'remanente' => $vida - $edad];
            $construccion += $actual;
        }
        $costo = $terreno + $construccion;
        if (!is_finite($costo) || $costo > 999999999999.99) {
            throw new InvalidArgumentException('El valor por costo excede el rango admitido. Revise áreas, precios y factores.');
        }
        $pc = $construccion / $costo;
        $pt = $terreno / $costo;
        $comparables = $d['comparables'] ?? [];
        $homologados = [];
        $mercado = null;
        $promedio = null;
        if ($comparables) {
            if (count($comparables) !== 3 || !$construcciones) {
                throw new InvalidArgumentException('La plantilla de mercado requiere construcción y exactamente tres comparables.');
            }
            $anterior = self::sujeto($d['sujeto'] ?? [], $area);
            $areaSujeto = $anterior['area_construida'];
            foreach ($comparables as $i => $c) {
                $actual = self::sujeto($c, self::numero($c['area_terreno'] ?? null, 'área de terreno del comparable', 0.000001));
                if ($actual['edad'] >= $actual['vida'] || $actual['depreciacion'] <= 0) {
                    throw new InvalidArgumentException('El comparable debe tener vida remanente y factor Q mayor que cero para evitar división entre cero.');
                }
                $precio = self::numero($c['precio_actual'] ?? null, 'precio actual del comparable', 0.000001);
                // K y N usan el comparable anterior en el Excel; el orden es significativo.
                $f = [
                    'terreno' => $pt * (($anterior['frente'] / $actual['frente']) * ($actual['fondo'] / $anterior['fondo'])
                        * ($actual['perimetro'] / $anterior['perimetro']) * ($actual['area_terreno'] / $anterior['area_terreno']) ** 0.5) ** (1 / 6) + $pc,
                    'edad' => $pt + (($anterior['vida'] ** 1.4 - $anterior['edad'] ** 1.4) / ($actual['vida'] ** 1.4 - $actual['edad'] ** 1.4)) * $pc,
                    'superficie' => $pt + ($anterior['area_construida'] / $actual['area_construida']) * $pc,
                    'conservacion' => $pt + ($anterior['depreciacion'] / $actual['depreciacion']) * $pc,
                    'funcionalidad' => self::numero($c['funcionalidad'] ?? null, 'funcionalidad', 0.000001, 100),
                    'cus' => $pt * ($actual['cus'] / $anterior['cus']) + $pc,
                    'ubicacion' => self::numero($c['ubicacion'] ?? null, 'ubicación del comparable', 0.000001, 100),
                    'topografia' => self::numero($c['topografia'] ?? null, 'topografía del comparable', 0.000001, 100),
                    'accesibilidad' => self::numero($c['accesibilidad'] ?? null, 'accesibilidad del comparable', 0.000001, 100),
                ];
                $global = round(array_product($f), 4, PHP_ROUND_HALF_UP);
                $unitario = $precio / $actual['area_construida'];
                $homologados[] = ['factores' => $f, 'global' => $global, 'unitario' => $unitario,
                    'homologado' => $global * $unitario, 'cus' => $actual['cus'], 'depreciacion' => $actual['depreciacion'], 'perimetro' => $actual['perimetro']];
                $anterior = $actual;
            }
            $promedio = array_sum(array_column($homologados, 'homologado')) / 3;
            $mercado = $promedio * $areaSujeto;
            if (!is_finite($mercado) || $mercado > 999999999999.99) {
                throw new InvalidArgumentException('El valor de mercado excede el rango admitido. Revise los comparables.');
            }
        }
        $metodo = $d['metodo'] ?? 'costo';
        if (!in_array($metodo, ['costo', 'comparativo'], true) || ($metodo === 'comparativo' && $mercado === null)) {
            throw new InvalidArgumentException('Complete los tres comparables para adoptar el método comparativo.');
        }
        $adoptado = ($d['valor_adoptado'] ?? '') === '' ? null : self::numero($d['valor_adoptado'], 'valor adoptado', 0, 999999999999.99);
        $derecho = self::numero($d['porcentaje_derecho'] ?? 100, 'porcentaje del derecho', 0, 100);
        return ['version' => self::VERSION, 'area_varas' => $area * self::VARAS_POR_METRO, 'factor_terreno' => $factor,
            'unitario_terreno' => $base * $factor, 'terreno' => $terreno, 'construccion' => $construccion,
            'construcciones' => $construcciones, 'costo' => $costo, 'peso_construccion' => $pc, 'peso_terreno' => $pt,
            'comparables' => $homologados, 'promedio_homologado' => $promedio, 'mercado' => $mercado,
            'calculado' => $metodo === 'costo' ? $costo : $mercado, 'adoptado' => $adoptado,
            'valor_derecho' => $adoptado === null ? null : $adoptado * $derecho / 100];
    }

    private static function sujeto(array $s, float $area): array
    {
        $s['area_terreno'] = $area;
        foreach (['frente', 'fondo', 'area_construida', 'vida'] as $key) {
            $s[$key] = self::numero($s[$key] ?? null, "{$key} del sujeto/comparable", 0.000001);
        }
        $s['edad'] = self::numero($s['edad'] ?? null, 'edad del sujeto/comparable', 0, $s['vida']);
        $s['q'] = self::numero($s['q'] ?? null, 'factor Q', 0, 1);
        $s['perimetro'] = 2 * ($s['frente'] + $s['fondo']);
        $s['cus'] = $s['area_construida'] / $area;
        $s['depreciacion'] = (1 - ($s['edad'] / $s['vida']) ** 1.4) * $s['q'];
        return $s;
    }
}
