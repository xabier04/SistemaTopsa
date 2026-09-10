/**
 * Módulo JS: Clientes
 */
document.addEventListener('DOMContentLoaded', () => {
    // Búsqueda con debounce
    const searchInput = document.getElementById('searchClientes');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            const term = e.target.value.trim();
            const rows = document.querySelectorAll('#tablaClientes tbody tr[data-id]');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term.toLowerCase()) ? '' : 'none';
            });
        }, 250));
    }

    // Eliminar cliente
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const baseUrl = App.baseUrl.replace(/\/$/, '');
            
            Modal.confirm(
                'Eliminar Cliente',
                `¿Está seguro que desea eliminar al cliente <strong>${name}</strong>? Esta acción no se puede deshacer.`,
                async () => {
                    try {
                        const result = await App.post(`${baseUrl}/cliente/delete/${id}`, new FormData());
                        Toast.success(result.message || 'Cliente eliminado');
                        const row = btn.closest('tr');
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.95)';
                        row.style.transition = 'all 0.3s ease';
                        setTimeout(() => row.remove(), 300);
                    } catch (error) {
                        Toast.error(error.message || 'Error al eliminar');
                    }
                },
                'Eliminar'
            );
        });
    });

    // ─── Drawer de Perfil de Cliente / Historial ───────────────
    const drawer = document.getElementById('clientDrawer');
    const overlay = document.getElementById('drawerOverlay');
    const closeBtn = document.getElementById('closeDrawer');
    const drawerBody = document.getElementById('drawerBody');
    const baseUrl = App.baseUrl.replace(/\/$/, '');

    const escapeHtml = (text) => {
        if (text === null || text === undefined) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    };

    const getInitials = (name) => {
        if (!name) return 'CL';
        const parts = name.trim().split(/\s+/).filter(Boolean);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }
        return name.slice(0, 2).toUpperCase();
    };

    const closeDrawer = () => {
        if (!drawer) return;
        drawer.classList.remove('active');
        overlay?.classList.remove('active');
        drawer.setAttribute('aria-hidden', 'true');
    };

    const openDrawer = () => {
        if (!drawer) return;
        drawer.classList.add('active');
        overlay?.classList.add('active');
        drawer.setAttribute('aria-hidden', 'false');
    };

    closeBtn?.addEventListener('click', closeDrawer);
    overlay?.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer?.classList.contains('active')) {
            closeDrawer();
        }
    });

    const renderDrawerContent = (cliente, proyectos) => {
        const proys = Array.isArray(proyectos) ? proyectos : [];
        const totalP = proys.length;
        let enProcesoP = 0;
        let finalizadosP = 0;
        let presupuestoTotal = 0;

        proys.forEach(p => {
            const st = (p.estado_del_proyecto || '').toLowerCase().trim();
            if (st.includes('proceso') || st === 'en proceso') enProcesoP++;
            else if (st.includes('finaliz') || st === 'finalizado') finalizadosP++;
            presupuestoTotal += parseFloat(p.presupuesto_inicial || 0);
        });

        const addressHtml = cliente.direccion ? `
            <div class="drawer-address-box">
                <div class="drawer-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="drawer-info-text">
                    <div class="drawer-info-value">${escapeHtml(cliente.direccion)}</div>
                </div>
            </div>
        ` : '';

        let projectsHtml = '';
        if (proys.length > 0) {
            projectsHtml = proys.map(p => {
                const st = p.estado_del_proyecto || 'En Proceso';
                let stClass = 'status-default';
                if (st === 'En Proceso') stClass = 'status-en-proceso';
                else if (st === 'Finalizado') stClass = 'status-finalizado';

                return `
                    <div class="drawer-project-card">
                        <div class="drawer-project-card-top">
                            <div class="drawer-project-icon"><i class="fas fa-drafting-compass"></i></div>
                            <div class="drawer-project-info">
                                <h5 class="drawer-project-name">${escapeHtml(p.nombre_del_proyecto || 'Sin Nombre')}</h5>
                                <span class="drawer-status-pill ${stClass}">
                                    <i class="fas fa-circle" style="font-size:0.5rem"></i> ${escapeHtml(st)}
                                </span>
                            </div>
                        </div>
                        <div class="drawer-project-details">
                            <div><i class="far fa-calendar-alt"></i> <strong>INICIO:</strong> ${formatDate(p.fecha_de_inicio)}</div>
                            <div><i class="fas fa-dollar-sign"></i> <strong>PRESUPUESTO:</strong> ${formatMoney(p.presupuesto_inicial)}</div>
                        </div>
                        <a href="${baseUrl}/proyecto/detalle/${p.id_proyecto}" class="btn-drawer-view-project">
                            Ver detalles del proyecto <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                `;
            }).join('');
        } else {
            projectsHtml = `
                <div class="empty-state p-4 text-center">
                    <i class="fas fa-folder-open text-muted" style="font-size:2rem"></i>
                    <p class="mt-2 text-muted" style="font-size:0.875rem">Este cliente no tiene proyectos vinculados aún.</p>
                </div>
            `;
        }

        drawerBody.innerHTML = `
            <div class="drawer-profile-card">
                <div class="drawer-avatar-circle">${getInitials(cliente.nombre)}</div>
                <h4 class="drawer-client-name">${escapeHtml(cliente.nombre || '—')}</h4>
                <div class="drawer-dui-badge">
                    <i class="far fa-id-card"></i> DUI: ${escapeHtml(cliente.dui || '—')}
                </div>

                <div class="drawer-contact-grid">
                    <div class="drawer-info-box">
                        <div class="drawer-info-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="drawer-info-text">
                            <div class="drawer-info-label">Teléfono</div>
                            <div class="drawer-info-value">${escapeHtml(cliente.telefono || '—')}</div>
                        </div>
                    </div>
                    <div class="drawer-info-box">
                        <div class="drawer-info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="drawer-info-text">
                            <div class="drawer-info-label">Correo</div>
                            <div class="drawer-info-value">${escapeHtml(cliente.correo_electronico || '—')}</div>
                        </div>
                    </div>
                </div>

                ${addressHtml}

                <a href="${baseUrl}/cliente/edit/${cliente.id_cliente}" class="btn-drawer-edit">
                    <i class="fas fa-user-edit"></i> Editar Información
                </a>
            </div>

            <div class="drawer-stats-row">
                <div class="drawer-stat-item">
                    <div class="drawer-stat-value">${totalP}</div>
                    <div class="drawer-stat-label">Total Proyectos</div>
                </div>
                <div class="drawer-stat-item">
                    <div class="drawer-stat-value en-proceso">${enProcesoP}</div>
                    <div class="drawer-stat-label">En Proceso</div>
                </div>
                <div class="drawer-stat-item">
                    <div class="drawer-stat-value finalizados">${finalizadosP}</div>
                    <div class="drawer-stat-label">Finalizados</div>
                </div>
            </div>

            <div class="drawer-budget-banner">
                <div class="drawer-budget-title">
                    <i class="fas fa-wallet"></i> Presupuesto Total Vinculado
                </div>
                <div class="drawer-budget-value">${formatMoney(presupuestoTotal)}</div>
            </div>

            <div class="drawer-projects-header">
                <div class="drawer-projects-title">
                    <i class="fas fa-drafting-compass"></i> Proyectos Vinculados
                </div>
                <span class="drawer-projects-count">${totalP} ${totalP === 1 ? 'proyecto' : 'proyectos'}</span>
            </div>

            ${projectsHtml}
        `;
    };

    const loadClientProfile = async (clientId) => {
        openDrawer();
        drawerBody.innerHTML = `
            <div class="drawer-loading text-center p-5">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="mt-2 text-muted">Cargando perfil del cliente...</p>
            </div>
        `;

        try {
            const data = await App.get(`${baseUrl}/cliente/historial/${clientId}`);
            if (data.success && data.cliente) {
                renderDrawerContent(data.cliente, data.proyectos || []);
            } else {
                throw new Error(data.message || 'No se pudo cargar la información del cliente.');
            }
        } catch (err) {
            drawerBody.innerHTML = `
                <div class="drawer-loading text-center p-5 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                    <p class="mt-2">${escapeHtml(err.message || 'Error al conectar con el servidor')}</p>
                    <button class="btn btn-outline btn-sm mt-3" id="btnDrawerErrorClose">Cerrar</button>
                </div>
            `;
            document.getElementById('btnDrawerErrorClose')?.addEventListener('click', closeDrawer);
        }
    };

    window.closeClientDrawer = closeDrawer;

    // Delegar clics en botones de historial
    document.getElementById('tablaClientes')?.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-historial');
        if (btn) {
            e.preventDefault();
            const clientId = btn.dataset.id;
            if (clientId) {
                loadClientProfile(clientId);
            }
        }
    });
});
