<?php
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Model.php';
require __DIR__ . '/../core/Validator.php';
require __DIR__ . '/../core/FormState.php';
require __DIR__ . '/../app/Models/Cliente.php';
require __DIR__ . '/../app/Models/Inmueble.php';
require __DIR__ . '/../app/Models/Ubicacion.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$catalogo = App\Models\Ubicacion::catalogo();
check(count($catalogo) === 14, 'Departamentos incompletos');
$municipios = array_merge(...array_values($catalogo));
check(count($municipios) === 44, 'Municipios incompletos');
check(count(array_merge(...array_values($municipios))) === 262, 'Distritos incompletos');
$ubicacion = ['departamento' => 'La Libertad', 'municipio' => 'La Libertad Sur', 'distrito' => 'Santa Tecla'];
check(App\Models\Ubicacion::errores($ubicacion) === [], 'Ubicación válida rechazada');
check(isset(App\Models\Ubicacion::errores(array_replace($ubicacion, ['distrito' => 'Apaneca']))['distrito']), 'Aceptó distrito incompatible');
$validator = new Core\Validator(['telefono' => '', 'correo_electronico' => '']);
check($validator->validate(['telefono' => 'phone', 'correo_electronico' => 'email|max:50']), 'Contactos vacíos rechazados');
$validator = new Core\Validator(['telefono' => '123', 'correo_electronico' => 'incorrecto']);
check(!$validator->validate(['telefono' => 'phone', 'correo_electronico' => 'email|max:50']), 'Formatos inválidos aceptados');
Core\FormState::save('cliente/update/1', ['nombre' => 'Nombre conservado', 'telefono' => '123'], $validator->getErrors());
check(Core\FormState::take('cliente/store') === [], 'Se mezclaron formularios');
$state = Core\FormState::take('cliente/update/1');
check($state['values']['nombre'] === 'Nombre conservado' && $state['values']['telefono'] === '', 'No conservó los campos correctos');
check(Core\FormState::take('cliente/update/1') === [], 'Estado no consumido');
$db = Core\Database::getInstance();
$db->beginTransaction();
try {
    $cliente = new App\Models\Cliente();
    $id = $cliente->create(['nombre' => 'Prueba temporal', 'dui' => sprintf('%08d-0', random_int(10000000, 99999999)), 'telefono' => '', 'correo_electronico' => '', 'direccion' => 'Dirección conservada']);
    check($cliente->find($id)['telefono'] === '', 'No guardó teléfono opcional');
    $inmueble = new App\Models\Inmueble();
    $iid = $inmueble->create($ubicacion + ['id_cliente' => $id, 'matricula' => (string) random_int(10000000, 99999999), 'area' => 100, 'tipo_inmueble' => 'Casa', 'direccion' => 'Calle y casa']);
    $saved = $inmueble->find($iid);
    check($saved['distrito'] === 'Santa Tecla' && $saved['direccion'] === 'Calle y casa', 'No guardó ubicación y detalle');
    $inmueble->update($iid, ['distrito' => 'Comasagua']);
    check($inmueble->find($iid)['distrito'] === 'Comasagua', 'No actualizó ubicación');
} finally {
    $db->rollBack();
}
echo "OK: catálogo, jerarquía, contactos opcionales, recuperación y persistencia (sin dejar registros).\n";
