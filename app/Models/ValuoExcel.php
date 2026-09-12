<?php
namespace App\Models;

/** Exportación documental OOXML, sin recalcular ni alterar la instantánea guardada. */
final class ValuoExcel
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    public static function generar(array $v, array $anexos = []): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('La exportación Excel requiere la extensión ZIP de PHP.');
        }
        $d = $v['datos']; $r = $v['resultados'];
        $sheets = [];
        $rows = [['TOPSA | INFORME DE AVALÚO'], ['Resumen del expediente'],
            ['Referencia', $d['referencia']], ['Fecha de valuación', $d['fecha_del_valuo']],
            ['Estado', $d['estado']], ['Revisión', (int) $v['revision']],
            ['Ubicación', $d['direccion_actual']], ['Propietarios', $d['propietarios']],
            ['Perito', $d['perito']], ['Registro del perito', $d['registro_perito']],
            ['Método seleccionado', $d['metodo']], ['Resultados | USD']];
        foreach (['terreno' => 'Terreno', 'construccion' => 'Construcción', 'costo' => 'Método del costo',
            'mercado' => 'Método comparativo', 'adoptado' => 'Valor adoptado', 'valor_derecho' => 'Valor del derecho'] as $key => $label) {
            $rows[] = [$label, self::numero($r[$key], 3)];
        }
        $rows[] = ['Derecho valuado (%)', self::numero($d['porcentaje_derecho'])];
        foreach (['conclusion' => 'Conclusión', 'situacion_juridica' => 'Situación jurídica', 'revision_perito' => 'Revisión del perito'] as $key => $label) {
            $rows[] = [$label, $d[$key]];
        }
        $rows[] = ['Versión del cálculo', $r['version']];
        $rows[] = ['Alcance', 'Instantánea de datos y resultados guardados. Los valores exportados no se recalculan al editar este archivo. Importes en dólares estadounidenses (USD).'];
        $sheets['Resumen'] = $rows;
        $rows = [['TOPSA | EXPEDIENTE'], ['Referencia', $d['referencia']]];
        foreach (ValuoFormulario::GENERALES as $title => $fields) {
            $rows[] = [$title];
            foreach ($fields as $key => $label) { $rows[] = [$label, $d[$key]]; }
        }
        $rows[] = ['Anexos del expediente'];
        foreach ($anexos as $a) { $rows[] = [$a['nombre'], $a['descripcion'] ?: 'Sin descripción']; }
        $rows[] = ['Nota sobre anexos', 'Los archivos adjuntos se consultan en el sistema; no están incrustados en este libro.'];
        $sheets['Expediente'] = $rows;
        $rows = [['TOPSA | MÉTODO DEL COSTO'], ['Referencia', $d['referencia']], ['Terreno']];
        foreach (['area_escritura' => 'Área según escritura (m²)', 'area_inspeccion' => 'Área de inspección (m²)',
            'area_valuada' => 'Área valuada (m²)', 'valor_vara' => 'Valor base (USD/v²)'] as $key => $label) {
            $rows[] = [$label, self::numero($d[$key], $key === 'valor_vara' ? 3 : 2)];
        }
        foreach ($d['factores'] as $key => $value) { $rows[] = ['Factor: ' . ucfirst($key), self::numero($value)]; }
        foreach (['area_varas' => 'Área valuada (v²)', 'factor_terreno' => 'Factor total de terreno',
            'unitario_terreno' => 'Valor corregido (USD/v²)', 'terreno' => 'Valor del terreno (USD)'] as $key => $label) {
            $rows[] = [$label, self::numero($r[$key], in_array($key, ['terreno', 'unitario_terreno']) ? 3 : 2)];
        }
        foreach ($d['construcciones'] as $i => $c) {
            $rows[] = ['Construcción ' . ($i + 1)];
            foreach (ValuoFormulario::CONSTRUCCION as $key => [$label, $type]) {
                $rows[] = [$label, $type === 'number' ? self::numero($c[$key], $key === 'valor_nuevo' ? 3 : 2) : $c[$key]];
            }
            foreach (['k1' => 'K1', 'k' => 'K de depreciación', 'residual' => 'Valor residual (USD)', 'actual' => 'Valor actual (USD)'] as $key => $label) {
                $rows[] = [$label, self::numero($r['construcciones'][$i][$key], in_array($key, ['residual', 'actual']) ? 3 : 2)];
            }
        }
        $rows[] = ['Resultado del costo'];
        foreach (['construccion' => 'Construcción (USD)', 'costo' => 'Valor por costo (USD)',
            'peso_construccion' => 'Participación construcción', 'peso_terreno' => 'Participación terreno'] as $key => $label) {
            $rows[] = [$label, self::numero($r[$key], str_starts_with($key, 'peso_') ? 4 : 3)];
        }
        $sheets['Costo'] = $rows;
        $rows = [['TOPSA | COMPARABLES DE MERCADO'], ['Referencia', $d['referencia']], ['Inmueble sujeto']];
        foreach (ValuoFormulario::SUJETO as $key => [$label, $type]) {
            $rows[] = [$label, $type === 'number' ? self::numero($d['sujeto'][$key]) : $d['sujeto'][$key]];
        }
        if ($d['comparables']) {
            $rows[] = ['Datos de mercado', 'Comparable 1', 'Comparable 2', 'Comparable 3'];
            foreach (ValuoFormulario::COMPARABLE as $key => [$label, $type]) {
                $row = [$label];
                foreach ($d['comparables'] as $c) { $row[] = $type === 'number' ? self::numero($c[$key], str_starts_with($key, 'precio_') ? 3 : 2) : $c[$key]; }
                $rows[] = $row;
            }
            $rows[] = ['Homologación', 'Comparable 1', 'Comparable 2', 'Comparable 3'];
            foreach (['perimetro' => 'Perímetro (m)', 'cus' => 'CUS', 'depreciacion' => 'Depreciación',
                'global' => 'Factor global', 'unitario' => 'Precio actual / m² (USD)', 'homologado' => 'Valor homologado / m² (USD)'] as $key => $label) {
                $row = [$label];
                foreach ($r['comparables'] as $c) { $row[] = self::numero($c[$key], in_array($key, ['unitario', 'homologado']) ? 3 : 2); }
                $rows[] = $row;
            }
            foreach (array_keys($r['comparables'][0]['factores']) as $key) {
                $row = ['Factor: ' . ucfirst($key)];
                foreach ($r['comparables'] as $c) { $row[] = self::numero($c['factores'][$key]); }
                $rows[] = $row;
            }
        } else { $rows[] = ['Comparables', 'No se registraron comparables de mercado.']; }
        $rows[] = ['Promedio homologado (USD/m²)', self::numero($r['promedio_homologado'], 3)];
        $rows[] = ['Valor de mercado (USD)', self::numero($r['mercado'], 3)];
        $rows[] = ['Criterios', 'Comparaciones encadenadas 1/sujeto, 2/1 y 3/2. K2 y Q independientes. Residual del 10%. Perímetro = 2 × (frente + fondo).'];
        $sheets['Mercado'] = $rows;
        return self::empaquetar($sheets);
    }

    private static function numero(mixed $value, int $style = 2): array|string
    {
        return $value === null || $value === '' ? 'Pendiente / no aplica' : ['value' => (float) $value, 'style' => $style];
    }

    private static function xml(string $text): string
    {
        $text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text) ?? '';
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function hoja(array $rows, bool $market): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="' . self::NS . '"><sheetPr><pageSetUpPr fitToPage="1"/></sheetPr><sheetViews><sheetView workbookViewId="0" showGridLines="0"><pane ySplit="2" topLeftCell="A3" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="1" width="44" customWidth="1"/><col min="2" max="' . ($market ? '4' : '2') . '" width="' . ($market ? '30' : '85') . '" customWidth="1"/></cols><sheetData>';
        $merges = [];
        foreach ($rows as $i => $row) {
            $n = $i + 1; $heading = count($row) === 1 || count($row) === 4 && in_array($row[0], ['Datos de mercado', 'Homologación']);
            $height = 28;
            foreach ($row as $j => $value) {
                if (is_array($value)) continue;
                $width = $j === 0 ? 40 : ($market ? 27 : 78);
                $lines = 0;
                foreach (explode("\n", (string) $value) as $line) { $lines += max(1, (int) ceil(mb_strlen($line) / $width)); }
                $height = max($height, min(409, $lines * 16 + 12));
            }
            $xml .= '<row r="' . $n . '" ht="' . $height . '" customHeight="1">';
            foreach ($row as $j => $value) {
                $ref = chr(65 + $j) . $n;
                if (is_array($value) || is_int($value)) {
                    $number = is_array($value) ? $value['value'] : $value;
                    $style = is_array($value) ? $value['style'] : 2;
                    $xml .= '<c r="' . $ref . '" s="' . $style . '"><v>' . json_encode($number, JSON_THROW_ON_ERROR) . '</v></c>';
                } else {
                    // Explicit strings preserve identifiers and prevent spreadsheet formula injection.
                    $xml .= '<c r="' . $ref . '" s="' . ($heading ? 1 : 0) . '" t="inlineStr"><is><t xml:space="preserve">' . self::xml((string) $value) . '</t></is></c>';
                }
            }
            $xml .= '</row>';
            if (count($row) === 1) { $merges[] = '<mergeCell ref="A' . $n . ':' . ($market ? 'D' : 'B') . $n . '"/>'; }
        }
        $xml .= '</sheetData><mergeCells count="' . count($merges) . '">' . implode('', $merges) . '</mergeCells>';
        return $xml . '<pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/><pageSetup paperSize="9" orientation="' . ($market ? 'landscape' : 'portrait') . '" fitToWidth="1" fitToHeight="0"/><headerFooter><oddFooter>&amp;LTOPSA&amp;RPágina &amp;P de &amp;N</oddFooter></headerFooter></worksheet>';
    }

    private static function empaquetar(array $sheets): string
    {
        $path = tempnam(sys_get_temp_dir(), 'topsa-xlsx-');
        if ($path === false) throw new \RuntimeException('No se pudo crear el archivo temporal.');
        $zip = new \ZipArchive(); $opened = false;
        try {
            if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) throw new \RuntimeException('No se pudo generar Excel.');
            $opened = true;
            $add = static function (string $name, string $content) use ($zip): void {
                if (!$zip->addFromString($name, $content)) throw new \RuntimeException('No se pudo escribir Excel.');
            };
            $relNs = 'http://schemas.openxmlformats.org/package/2006/relationships';
            $docRel = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/';
            $add('_rels/.rels', '<Relationships xmlns="' . $relNs . '"><Relationship Id="rId1" Type="' . $docRel . 'officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $workbook = '<workbook xmlns="' . self::NS . '" xmlns:r="' . rtrim($docRel, '/') . '"><sheets>';
            $rels = '<Relationships xmlns="' . $relNs . '">';
            $types = '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
            $i = 0;
            foreach ($sheets as $name => $rows) {
                ++$i;
                $workbook .= '<sheet name="' . self::xml($name) . '" sheetId="' . $i . '" r:id="rId' . $i . '"/>';
                $rels .= '<Relationship Id="rId' . $i . '" Type="' . $docRel . 'worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
                $types .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
                $add('xl/worksheets/sheet' . $i . '.xml', self::hoja($rows, $name === 'Mercado'));
            }
            $add('xl/workbook.xml', $workbook . '</sheets></workbook>');
            $add('xl/_rels/workbook.xml.rels', $rels . '<Relationship Id="styles" Type="' . $docRel . 'styles" Target="styles.xml"/></Relationships>');
            $add('[Content_Types].xml', $types . '</Types>');
            $add('xl/styles.xml', '<styleSheet xmlns="' . self::NS . '"><numFmts count="2"><numFmt numFmtId="164" formatCode="#,##0.00########"/><numFmt numFmtId="165" formatCode="&quot;$&quot;#,##0.00"/></numFmts><fonts count="2"><font><sz val="11"/><color rgb="FF24342C"/><name val="Calibri"/></font><font><b/><sz val="12"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF244C34"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="5"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf><xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/><xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/><xf numFmtId="10" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');
            if (!$zip->close()) throw new \RuntimeException('No se pudo finalizar Excel.');
            $opened = false;
            $bytes = file_get_contents($path);
            if ($bytes === false) throw new \RuntimeException('No se pudo leer Excel.');
            return $bytes;
        } finally {
            if ($opened) $zip->close();
            unlink($path);
        }
    }
}
