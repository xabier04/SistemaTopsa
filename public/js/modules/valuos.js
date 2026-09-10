document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-print-report]').forEach(button => button.addEventListener('click', () => window.print()));
    const form = document.getElementById('avaluo-form');
    if (!form) return;
    const components = document.getElementById('avaluo-construcciones');
    const comparables = document.getElementById('avaluo-comparables');
    const preview = document.getElementById('avaluo-preview');
    let generation = 0;
    const dirty = () => { generation++; preview.textContent = 'Hay datos nuevos. Pulse Calcular y verificar para actualizar los resultados.'; };
    form.addEventListener('input', dirty); form.addEventListener('change', dirty);
    const reindex = () => components.querySelectorAll('[data-component]').forEach((row, index) => {
        row.querySelectorAll('input').forEach(input => {
            const old = input.id;
            input.name = input.name.replace(/construcciones\[\d+\]/, `construcciones[${index}]`); input.id = input.name;
            row.querySelectorAll('label').forEach(label => { if (label.htmlFor === old) label.htmlFor = input.id; });
        });
    });
    document.getElementById('avaluo-add-construccion').addEventListener('click', () => {
        if (components.children.length >= 20) return;
        components.append(document.getElementById('avaluo-construccion-template').content.cloneNode(true)); reindex(); dirty();
    });
    components.addEventListener('click', event => {
        if (event.target.closest('[data-remove-component]')) { event.target.closest('[data-component]').remove(); reindex(); dirty(); }
    });
    document.getElementById('avaluo-enable-comparables').addEventListener('click', () => {
        if (comparables.children.length) return;
        comparables.append(document.getElementById('avaluo-comparables-template').content.cloneNode(true)); dirty();
    });
    document.getElementById('avaluo-clear-comparables').addEventListener('click', () => {
        if (comparables.children.length && window.confirm('¿Quitar los tres comparables del formulario? Los cambios se aplican al guardar.')) {
            comparables.replaceChildren(); document.getElementById('metodo').value = 'costo'; dirty();
        }
    });
    form.addEventListener('invalid', event => { const details = event.target.closest('details'); if (details) details.open = true; }, true);
    document.getElementById('avaluo-calculate').addEventListener('click', async event => {
        const requestGeneration = generation; const button = event.currentTarget;
        button.disabled = true; preview.textContent = 'Calculando…';
        try {
            const response = await fetch(form.dataset.calculateUrl, {method:'POST', body:new FormData(form), headers:{'Accept':'application/json'}});
            const body = await response.json();
            if (requestGeneration !== generation) return;
            if (!response.ok) throw new Error(body.error || 'No se pudo calcular.');
            const r = body.resultados;
            const money = value => value === null ? 'Sin definir' : new Intl.NumberFormat('es-SV', {style:'currency', currency:'USD'}).format(value);
            preview.replaceChildren(); const grid = document.createElement('div'); grid.className = 'avaluo-results';
            for (const [label,key] of [['Terreno','terreno'],['Construcción','construccion'],['Método del costo','costo'],['Método comparativo','mercado'],['Valor adoptado','adoptado'],['Valor del derecho','valor_derecho']]) {
                const card = document.createElement('div'); card.textContent = label;
                const value = document.createElement('strong'); value.textContent = money(r[key]); card.append(value); grid.append(card);
            }
            preview.append(grid); const p = document.createElement('p'); p.textContent = 'Cálculo actualizado. El detalle de homologación se guarda con el expediente.'; preview.append(p);
        } catch (error) { if (requestGeneration === generation) preview.textContent = error.message; }
        finally { button.disabled = false; }
    });
});
