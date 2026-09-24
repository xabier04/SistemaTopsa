<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Valuo;
use App\Models\Proyecto;
use App\Models\ValuoCalculo;
use App\Models\ValuoFormulario;
use InvalidArgumentException;

class ValuoController extends Controller
{
    private Valuo $model;
    public function __construct() { parent::__construct(); $this->model = new Valuo(); }

    public function index(): void
    {
        $this->view('valuos/index', ['pageTitle' => 'Avalúos', 'pageScript' => 'valuos', 'valuos' => $this->model->allWithProyecto()]);
    }

    public function create(): void
    {
        $d = ValuoFormulario::nuevo();
        $p = (new Proyecto())->getDetail((int) ($_GET['proyecto'] ?? 0));
        if ($p) {
            $d = array_merge($d, ['id_proyecto' => $p['id_proyecto'], 'propietarios' => $p['nombre_cliente'],
                'matricula' => $p['matricula'] ?? '', 'direccion_actual' => $p['direccion_inmueble'] ?? '',
                'tipo_inmueble' => $p['tipo_inmueble'] ?? '', 'area_escritura' => $p['area'] ?? '']);
        }
        $this->formulario($d);
    }

    public function edit(int $id = 0): void
    {
        $v = $this->buscar($id);
        $d = $v['datos'] ?: array_merge(ValuoFormulario::nuevo(), ['id_proyecto' => $v['id_proyecto'],
            'fecha_del_valuo' => $v['fecha_del_valuo'], 'valor_adoptado' => $v['monto_estimado'], 'conclusion' => $v['observaciones']]);
        $d['revision'] = $v['revision'] ?? 0;
        $this->formulario($d, $id);
    }

    public function store(): void { $this->guardar(null); }
    public function update(int $id = 0): void { $this->guardar($id); }

    public function calcular(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) { $this->json(['error' => 'La sesión expiró. Recargue la página.'], 403); }
        try {
            $d = ValuoFormulario::normalizar($this->allInput());
            ValuoFormulario::validarNumeros($d);
            $this->json(['resultados' => ValuoCalculo::calcular($d)]);
        } catch (InvalidArgumentException $e) { $this->json(['error' => $e->getMessage()], 422); }
    }

    private function guardar(?int $id): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            Session::flash('error', 'La sesión expiró. Recargue el formulario.');
            $this->redirect('valuo/index');
        }
        if ($id !== null) { $this->buscar($id); }
        $d = [];
        try {
            $d = ValuoFormulario::normalizar($this->allInput());
            ValuoFormulario::validarNumeros($d);
            if ($d['referencia'] === '' || strlen($d['referencia']) > 100) { throw new InvalidArgumentException('Ingrese una referencia de hasta 100 caracteres.'); }
            if (!ctype_digit($d['id_proyecto']) || !(new Proyecto())->find((int) $d['id_proyecto'])) { throw new InvalidArgumentException('Seleccione un proyecto existente.'); }
            $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $d['fecha_del_valuo']);
            if (!$fecha || $fecha->format('Y-m-d') !== $d['fecha_del_valuo']) { throw new InvalidArgumentException('Ingrese una fecha válida de avalúo.'); }
            if (!in_array($d['estado'], ['Borrador', 'Revisado'], true)) { throw new InvalidArgumentException('Estado de expediente inválido.'); }
            foreach ($d['comparables'] as $c) {
                if ($c['fecha'] !== '') {
                    $f = \DateTimeImmutable::createFromFormat('!Y-m-d', $c['fecha']);
                    if (!$f || $f->format('Y-m-d') !== $c['fecha']) { throw new InvalidArgumentException('Revise la fecha de operación del comparable.'); }
                }
            }
            $r = ValuoCalculo::calcular($d);
            if ($d['estado'] === 'Revisado') {
                foreach (['perito', 'registro_perito', 'conclusion', 'revision_perito', 'valor_adoptado'] as $key) {
                    if ($d[$key] === '') { throw new InvalidArgumentException('Para marcar Revisado indique perito, registro, valor adoptado, conclusión y revisión de las fórmulas.'); }
                }
            }
            $saved = $this->model->guardarExpediente($d, $r, $id);
        } catch (InvalidArgumentException $e) {
            http_response_code(422); $this->formulario($d ?: ValuoFormulario::nuevo(), $id, $e->getMessage()); return;
        } catch (\Throwable $e) {
            error_log('Avalúo: ' . $e->getMessage()); http_response_code(500);
            $this->formulario($d ?: ValuoFormulario::nuevo(), $id, 'No se pudo guardar. Los datos siguen en el formulario.'); return;
        }
        try { $this->logActivity('Guardó expediente de avalúo #' . $saved, 'valuos', $saved); }
        catch (\Throwable $e) { error_log('Bitácora de avalúo: ' . $e->getMessage()); }
        Session::flash('success', 'Avalúo guardado con sus entradas y cálculos.');
        $this->redirect('valuo/show/' . $saved);
    }

    private function formulario(array $d, ?int $id = null, ?string $error = null): void
    {
        $this->view('valuos/form', ['pageTitle' => $id ? 'Editar avalúo' : 'Nuevo avalúo', 'pageScript' => 'valuos',
            'd' => $d, 'id' => $id, 'error' => $error, 'proyectos' => (new Proyecto())->allWithRelations(),
            'action' => url($id ? "valuo/update/{$id}" : 'valuo/store')]);
    }

    private function buscar(int $id): array
    {
        $v = $this->model->expediente($id);
        if (!$v) { Session::flash('error', 'Avalúo no encontrado.'); $this->redirect('valuo/index'); }
        return $v;
    }

    public function show(int $id = 0): void
    {
        $v = $this->buscar($id);
        $this->view('valuos/detail', ['pageTitle' => 'Expediente de avalúo', 'pageScript' => 'valuos', 'v' => $v,
            'd' => $v['datos'], 'r' => $v['resultados'], 'anexos' => $this->model->anexos($id)]);
    }

    public function report(int $id = 0): void
    {
        $v = $this->buscar($id);
        if (!$v['datos']) { $this->redirect('valuo/show/' . $id); }
        $d = $v['datos']; $r = $v['resultados']; $anexos = $this->model->anexos($id);
        require dirname(__DIR__) . '/Views/valuos/report.php';
    }

    public function excel(int $id = 0): void
    {
        $v = $this->buscar($id);
        if (!$v['datos']) { $this->redirect('valuo/show/' . $id); }
        try {
            $bytes = \App\Models\ValuoExcel::generar($v, $this->model->anexos($id));
        } catch (\Throwable $e) {
            error_log('Excel de avalúo: ' . $e->getMessage());
            Session::flash('error', 'No se pudo generar el Excel. Verifique que el servidor tenga habilitada la extensión ZIP de PHP.');
            $this->redirect('valuo/show/' . $id);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="avaluo-' . $id . '.xlsx"');
        header('Content-Length: ' . strlen($bytes));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        echo $bytes;
    }

    public function upload(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) { $this->redirect('valuo/show/' . $id); }
        $this->buscar($id); $path = null;
        try {
            $file = $_FILES['anexo'] ?? [];
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
                throw new InvalidArgumentException('Seleccione un archivo válido de hasta 10 MB (también sujeto al límite del servidor).');
            }
            if (filesize($file['tmp_name']) > 10 * 1024 * 1024) { throw new InvalidArgumentException('El anexo supera 10 MB.'); }
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            $types = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($types[$mime])) { throw new InvalidArgumentException('Solo se aceptan PDF, JPG, PNG o WebP.'); }
            $description = ValuoFormulario::texto($_POST['descripcion'] ?? '', 255);
            $name = ValuoFormulario::texto(basename($file['name']), 255);
            $filename = bin2hex(random_bytes(16)) . '.' . $types[$mime];
            $dir = dirname(__DIR__, 2) . '/storage/valuos';
            if (!is_dir($dir) && !mkdir($dir, 0770, true)) { throw new \RuntimeException('No se pudo crear el directorio de anexos.'); }
            $path = $dir . '/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $path)) { throw new \RuntimeException('No se pudo almacenar el anexo.'); }
            $this->model->guardarAnexo($id, $name, $filename, $mime, $description); $path = null;
            Session::flash('success', 'Anexo agregado al expediente.');
        } catch (\Throwable $e) {
            if ($path && is_file($path)) { unlink($path); }
            error_log('Anexo: ' . $e->getMessage());
            Session::flash('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'No se pudo guardar el anexo.');
        }
        $this->redirect('valuo/show/' . $id);
    }

    public function attachment(int $id = 0): void
    {
        $a = $this->model->anexo($id);
        $path = $a ? dirname(__DIR__, 2) . '/storage/valuos/' . basename($a['archivo']) : '';
        if (!$a || !is_file($path)) { http_response_code(404); echo 'Anexo no encontrado.'; return; }
        header('Content-Type: ' . $a['tipo']); header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, no-store');
        header("Content-Disposition: inline; filename*=UTF-8''" . rawurlencode($a['nombre'])); readfile($path);
    }
}
