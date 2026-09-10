/** Interacciones del listado de proyectos. */
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('projectTable');
    if (!table) return;
    const search = document.getElementById('searchProyectos');
    const cards = document.getElementById('projectCards');
    const empty = document.getElementById('projectEmpty');
    const dialog = document.getElementById('projectPreview');
    const states = ['', 'En Proceso', 'Finalizado', 'Incompleto'];
    const params = new URLSearchParams(location.search);
    let state = states.includes(params.get('estado')) ? params.get('estado') : '';
    let view = 'table';
    try { view = localStorage.getItem('topsa-project-view') === 'cards' ? 'cards' : 'table'; } catch (_) { /* Storage is optional. */ }
    let projects = Array.from(table.querySelectorAll('tr[data-project-id]')).map(row => ({
        row, id: row.dataset.projectId, state: row.dataset.state,
        name: row.cells[1].textContent.trim(), client: row.cells[2].textContent.trim(),
        date: row.cells[3].textContent.trim(), budget: row.cells[4].textContent.trim(),
        address: row.cells[5].textContent.trim(), detail: row.querySelector('.act-view').href,
    }));
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    let trigger;
    const closePreview = () => { dialog.close(); if (trigger?.isConnected) trigger.focus(); };
    document.getElementById('closeProjectPreview').addEventListener('click', closePreview);
    dialog.addEventListener('click', event => { if (event.target === dialog && event.clientX < dialog.getBoundingClientRect().left) closePreview(); });
    function preview(project, button) {
        trigger = button;
        document.getElementById('previewTitle').textContent = project.name;
        const body = document.getElementById('previewBody');
        body.replaceChildren();
        const list = element('dl', 'preview-data');
        [['Cliente', project.client], ['Estado', project.state], ['Fecha de inicio', project.date], ['Presupuesto', project.budget], ['Inmueble', project.address]].forEach(([label, value]) => {
            list.append(element('dt', '', label), element('dd', '', value));
        });
        body.append(list);
        document.getElementById('previewDetail').href = project.detail;
        dialog.showModal();
    }
    projects.forEach(project => {
        const button = element('button', 'btn-action act-view', '');
        button.type = 'button';
        button.innerHTML = '<i class="fas fa-eye" aria-hidden="true"></i>';
        button.setAttribute('aria-label', `Consulta rápida: ${project.name}`);
        button.title = 'Consulta rápida';
        button.addEventListener('click', () => preview(project, button));
        project.row.querySelector('.act-view').replaceWith(button);
        const card = element('article', 'project-tile');
        const badge = project.row.querySelector('.badge-dot').cloneNode(true);
        card.append(badge, element('h3', '', project.name), element('p', 'text-muted', project.client));
        const meta = element('dl', 'tile-data');
        meta.append(element('dt', '', 'Inicio'), element('dd', '', project.date), element('dt', '', 'Presupuesto'), element('dd', 'cell-money', project.budget));
        card.append(meta);
        const open = element('button', 'btn btn-outline', 'Consultar proyecto →');
        open.type = 'button';
        open.addEventListener('click', () => preview(project, open));
        card.append(open);
        cards.append(card);
        project.card = card;
    });
    const chips = Array.from(document.querySelectorAll('.summary-chip')).map((chip, index) => {
        const button = element('button', chip.className);
        button.type = 'button';
        button.append(...chip.childNodes);
        button.dataset.state = states[index];
        button.addEventListener('click', () => { state = states[index]; refresh(); });
        chip.replaceWith(button);
        return button;
    });
    function refresh() {
        const term = normalize(search.value.trim());
        let count = 0;
        projects.forEach(project => {
            const visible = (!state || project.state === state) && normalize(`${project.name} ${project.client}`).includes(term);
            project.row.hidden = project.card.hidden = !visible;
            if (visible) count++;
        });
        chips.forEach(chip => {
            chip.setAttribute('aria-pressed', String(chip.dataset.state === state));
            chip.querySelector('.chip-value').textContent = projects.filter(project => !chip.dataset.state || project.state === chip.dataset.state).length;
        });
        table.hidden = view !== 'table' || count === 0;
        cards.hidden = view !== 'cards' || count === 0;
        empty.hidden = count !== 0;
        document.getElementById('projectResults').textContent = `${count} de ${projects.length} proyectos${state ? ` · ${state}` : ''}`;
        document.querySelectorAll('[data-project-view]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.projectView === view)));
        const url = new URL(location.href);
        if (state) url.searchParams.set('estado', state); else url.searchParams.delete('estado');
        history.replaceState(null, '', url);
    }
    search.addEventListener('input', refresh);
    document.getElementById('clearProjectFilters').addEventListener('click', () => { search.value = ''; state = ''; refresh(); search.focus(); });
    document.querySelectorAll('[data-project-view]').forEach(button => button.addEventListener('click', () => {
        view = button.dataset.projectView;
        try { localStorage.setItem('topsa-project-view', view); } catch (_) { /* Storage is optional. */ }
        refresh();
    }));
    table.querySelectorAll('.btn-delete').forEach(button => button.addEventListener('click', () => {
        const project = projects.find(item => item.id === button.dataset.id);
        const safeName = element('span', '', project.name).innerHTML;
        Modal.confirm('Eliminar Proyecto', `¿Está seguro que desea eliminar el proyecto <strong>${safeName}</strong>?`, async () => {
            button.disabled = true;
            try {
                const result = await App.post(`${App.baseUrl.replace(/\/$/, '')}/proyecto/delete/${project.id}`, new FormData());
                if (!result.success) throw new Error(result.message || 'No se pudo eliminar');
                project.row.remove(); project.card.remove();
                projects = projects.filter(item => item !== project);
                refresh();
                Toast.success(result.message || 'Proyecto eliminado');
            } catch (error) { Toast.error(error.message || 'Error al eliminar'); }
            finally { button.disabled = false; }
        }, 'Eliminar');
    }));
    refresh();
});
