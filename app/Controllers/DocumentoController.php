<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Documento;
use App\Models\Proyecto;

/**
 * Controlador de Documentos
 */
class DocumentoController extends Controller
{
    private Documento $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Documento();
    }

    public function index(): void
    {
        $idProyecto = $_GET['id_proyecto'] ?? '';

        if (\Core\Router::isAjax() && !empty($idProyecto)) {
            $documentos = $this->model->getByProyecto((int) $idProyecto);
            $this->json(['success' => true, 'data' => $documentos]);
            return;
        }

        $documentos = $this->model->allWithProyecto();
        $proyectos  = (new Proyecto())->allWithRelations();

        $this->view('documentos/index', [
            'pageTitle'  => 'Gestión Documental',
            'pageScript' => 'documentos',
            'documentos' => $documentos,
            'proyectos'  => $proyectos,
        ]);
    }

    /**
     * Subir un documento
     */
    public function upload(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('documento/index');
            return;
        }

        $idProyecto      = (int) $this->input('id_proyecto', 0);
        $tipoDocumento   = $this->input('tipo_de_documento', '');

        if ($idProyecto === 0 || empty($tipoDocumento)) {
            Session::flash('error', 'Datos incompletos.');
            $this->redirect('documento/index');
            return;
        }

        // Validar archivo
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Error al subir el archivo.');
            $this->redirect('documento/index');
            return;
        }

        $file = $_FILES['archivo'];
        $config = $this->appConfig;

        // Validar tamaño
        if ($file['size'] > $config['uploads']['max_size']) {
            Session::flash('error', 'El archivo excede el tamaño máximo permitido (10 MB).');
            $this->redirect('documento/index');
            return;
        }

        // Validar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $config['uploads']['allowed_types'])) {
            Session::flash('error', 'Tipo de archivo no permitido.');
            $this->redirect('documento/index');
            return;
        }

        // Crear directorio si no existe
        $uploadDir = $config['paths']['uploads'] . "/proyectos/{$idProyecto}";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generar nombre único
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $filePath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->model->create([
                'id_proyecto'       => $idProyecto,
                'nombre_del_archivo' => $file['name'],
                'tipo_de_documento' => $tipoDocumento,
                'fecha_de_subida'   => date('Y-m-d'),
                'ruta_del_archivo'  => "uploads/proyectos/{$idProyecto}/{$fileName}",
            ]);

            $this->logActivity('Subió documento: ' . $file['name'], 'documentos');
            Session::flash('success', 'Documento subido exitosamente.');
        } else {
            Session::flash('error', 'Error al guardar el archivo.');
        }

        $this->redirect('documento/index');
    }

    /**
     * Descargar documento
     */
    public function download(int $id = 0): void
    {
        $documento = $this->model->find($id);
        if (!$documento) {
            Session::flash('error', 'Documento no encontrado.');
            $this->redirect('documento/index');
            return;
        }

        $filePath = $this->appConfig['paths']['public'] . '/' . $documento['ruta_del_archivo'];
        if (!file_exists($filePath)) {
            Session::flash('error', 'El archivo no existe en el servidor.');
            $this->redirect('documento/index');
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $documento['nombre_del_archivo'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Eliminar documento (AJAX)
     */
    public function delete(int $id = 0): void
    {
        if (!\Core\Router::isAjax()) {
            $this->redirect('documento/index');
            return;
        }

        $documento = $this->model->find($id);
        if (!$documento) {
            $this->json(['success' => false, 'message' => 'Documento no encontrado.'], 404);
            return;
        }

        // Eliminar archivo físico
        $filePath = $this->appConfig['paths']['public'] . '/' . $documento['ruta_del_archivo'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->model->delete($id);
        $this->logActivity('Eliminó documento: ' . $documento['nombre_del_archivo'], 'documentos', $id);
        $this->json(['success' => true, 'message' => 'Documento eliminado exitosamente.']);
    }
}
