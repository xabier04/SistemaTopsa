/**
 * Módulo JS: Usuarios
 */
document.addEventListener('DOMContentLoaded', () => {
    // Toggle estado de cuenta
    document.querySelectorAll('.btn-toggle-estado').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            try {
                const result = await App.post(`${window.location.origin}/usuario/toggleEstado/${id}`, new FormData());
                Toast.success(result.message);
                
                const badge = document.getElementById(`estado-${id}`);
                if (badge) {
                    badge.textContent = result.estado;
                    badge.className = 'badge ' + (result.estado === 'Activo' ? 'badge-success' : 'badge-danger');
                }
            } catch (error) {
                Toast.error(error.message || 'Error al cambiar estado');
            }
        });
    });
});
