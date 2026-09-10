<?php
$catalogo = \App\Models\Ubicacion::catalogo();
$departamento = $formValues['departamento'] ?? '';
$municipio = $formValues['municipio'] ?? '';
$opciones = [
    'departamento' => array_keys($catalogo),
    'municipio' => array_keys($catalogo[$departamento] ?? []),
    'distrito' => $catalogo[$departamento][$municipio] ?? [],
];
?>
<div class="form-row" id="ubicacionInmueble">
    <?php foreach ($opciones as $campo => $valores): ?>
    <div class="form-group">
        <label for="<?= $campo ?>"><?= ucfirst($campo) ?> <span class="required-mark">*</span></label>
        <select id="<?= $campo ?>" name="<?= $campo ?>" class="form-control" required>
            <option value="">Seleccione <?= $campo ?></option>
            <?php foreach ($valores as $valor): ?>
            <option value="<?= e($valor) ?>" <?= ($formValues[$campo] ?? '') === $valor ? 'selected' : '' ?>><?= e($valor) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endforeach; ?>
</div>
<script type="application/json" id="catalogoElSalvador"><?= json_encode($catalogo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const catalogo = JSON.parse(document.getElementById('catalogoElSalvador').textContent);
    const departamento = document.getElementById('departamento');
    const municipio = document.getElementById('municipio');
    const distrito = document.getElementById('distrito');
    const llenar = (select, valores) => {
        select.replaceChildren(new Option('Seleccione ' + select.name, ''));
        valores.forEach(valor => select.add(new Option(valor, valor)));
    };
    departamento.addEventListener('change', () => {
        llenar(municipio, Object.keys(catalogo[departamento.value] || {}));
        llenar(distrito, []);
    });
    municipio.addEventListener('change', () => {
        llenar(distrito, catalogo[departamento.value]?.[municipio.value] || []);
    });
});
</script>
