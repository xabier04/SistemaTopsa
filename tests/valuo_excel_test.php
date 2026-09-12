<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
spl_autoload_register(static function ($class) {
    if (str_starts_with($class, 'App\\Models\\')) require __DIR__ . '/../app/Models/' . substr($class, 11) . '.php';
});
use App\Models\ValuoFormulario;
use App\Models\ValuoCalculo;
use App\Models\ValuoExcel;

$d = ValuoFormulario::normalizar(array_merge(require __DIR__ . '/fixtures/apaneca.php', [
    'referencia' => '=HYPERLINK("https://example.com")', 'matricula' => '000123',
    'conclusion' => "Texto <formal> & revisión\x01", 'estado' => 'Borrador', 'fecha_del_valuo' => '2026-05-28',
]));
foreach ([false, true] as $landOnly) {
    if ($landOnly) { $d['construcciones'] = []; $d['comparables'] = []; $d['metodo'] = 'costo'; $d['valor_adoptado'] = ''; }
    $r = ValuoCalculo::calcular($d);
    $bytes = ValuoExcel::generar(['datos' => $d, 'resultados' => $r, 'revision' => 1]);
    $path = tempnam(sys_get_temp_dir(), 'xlsx-test-');
    try {
        file_put_contents($path, $bytes);
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw new RuntimeException('XLSX inválido.');
        for ($i = 0; $i < $zip->numFiles; ++$i) {
            if (simplexml_load_string($zip->getFromIndex($i)) === false) throw new RuntimeException('XML inválido.');
        }
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (str_contains($xml, '<f>') || !str_contains($xml, '=HYPERLINK') || !str_contains($xml, '&lt;formal&gt; &amp; revisión')) throw new RuntimeException('Texto inseguro o alterado.');
        if (!str_contains($zip->getFromName('xl/worksheets/sheet2.xml'), '>000123<')) throw new RuntimeException('Matrícula alterada.');
        if ($landOnly && (!str_contains($xml, 'Pendiente / no aplica') || !str_contains($xml, '<v>0</v>'))) throw new RuntimeException('No diferencia cero de pendiente.');
        if (!$landOnly && !str_contains($xml, '<v>60000</v>')) throw new RuntimeException('Valor adoptado incorrecto.');
        $zip->close();
    } finally { unlink($path); }
}
echo "OK: XLSX válido, precisión, texto seguro, identificadores y terreno sin construcción con valores pendientes.\n";
