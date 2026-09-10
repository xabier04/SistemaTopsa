<?php if (!empty($formErrors)): ?>
<div class="alert alert-error" role="alert">
    <p>Corrija los campos indicados. Los demás datos se han conservado.</p>
    <ul>
        <?php foreach ($formErrors as $field => $message): ?>
        <li><a href="#<?= e($field) ?>"><?= e($message) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const fields = <?= json_encode(array_keys($formErrors), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    fields.forEach(name => {
        const input = document.getElementById(name);
        if (!input) return;
        input.classList.add('is-invalid');
        input.setAttribute('aria-invalid', 'true');
        input.addEventListener('input', () => input.removeAttribute('aria-invalid'), {once: true});
    });
    document.getElementById(fields[0])?.focus();
});
</script>
<?php endif; ?>
