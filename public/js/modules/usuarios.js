/**
 * Módulo JS: Usuarios
 */
document.addEventListener('DOMContentLoaded', () => {
    // ─── Acción Eliminar Usuario ────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const baseUrl = (typeof App !== 'undefined' && App.baseUrl) ? App.baseUrl.replace(/\/$/, '') : window.location.origin;

            const executeDelete = async () => {
                try {
                    const fd = new FormData();
                    const csrf = (typeof App !== 'undefined' && App.getCsrfToken) ? App.getCsrfToken() : null;
                    if (csrf) fd.append('_csrf_token', csrf);

                    const result = await App.post(`${baseUrl}/usuario/delete/${id}`, fd);
                    if (result && result.success) {
                        Toast.success(result.message || `Usuario "${name}" eliminado exitosamente.`);
                        const row = btn.closest('tr');
                        if (row) {
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            setTimeout(() => {
                                row.remove();
                                // Actualizar contador en el summary chip
                                const chipValue = document.querySelector('.summary-chip .chip-value');
                                if (chipValue) {
                                    const current = parseInt(chipValue.textContent, 10);
                                    if (!isNaN(current) && current > 0) {
                                        chipValue.textContent = current - 1;
                                    }
                                }
                            }, 300);
                        }
                    } else {
                        Toast.error((result && result.message) || 'No se pudo eliminar el usuario.');
                    }
                } catch (error) {
                    Toast.error(error.message || 'Error de conexión al eliminar usuario.');
                }
            };

            if (typeof Modal !== 'undefined' && Modal.confirm) {
                Modal.confirm(
                    'Eliminar Usuario',
                    `¿Está seguro que desea eliminar al usuario <strong>${name}</strong>? Esta acción no se puede deshacer.`,
                    executeDelete
                );
            } else if (confirm(`¿Está seguro que desea eliminar al usuario "${name}"? Esta acción no se puede deshacer.`)) {
                executeDelete();
            }
        });
    });
});
