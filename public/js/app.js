/**
 * Sistema TOPSA — JavaScript Global (app.js)
 * Helpers globales: Fetch API, modales, toasts, sidebar, utilidades
 */

// ─── Configuración Base ─────────────────────────────────────
const App = {
    baseUrl: document.querySelector('meta[name="base-url"]')?.content || window.location.origin,

    /**
     * Realizar petición AJAX con Fetch API
     */
    async fetch(url, options = {}) {
        const defaults = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        // Si es FormData, no establecer Content-Type (el navegador lo hace)
        if (!(options.body instanceof FormData)) {
            defaults.headers['Content-Type'] = 'application/json';
        }

        const config = { ...defaults, ...options };
        config.headers = { ...defaults.headers, ...options.headers };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `Error ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    },

    /**
     * GET request
     */
    async get(url) {
        return this.fetch(url);
    },

    /**
     * POST request con FormData
     */
    async post(url, formData) {
        return this.fetch(url, {
            method: 'POST',
            body: formData,
        });
    },

    /**
     * POST request con JSON
     */
    async postJson(url, data) {
        return this.fetch(url, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    },
};

// ─── Toast Notifications ────────────────────────────────────
const Toast = {
    container: null,

    init() {
        this.container = document.getElementById('toastContainer');
    },

    show(message, type = 'info', duration = 4000) {
        if (!this.container) this.init();

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle',
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="${icons[type] || icons.info}"></i>
            <span>${message}</span>
        `;

        this.container.appendChild(toast);

        // Auto-remover
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    success(msg) { this.show(msg, 'success'); },
    error(msg)   { this.show(msg, 'error'); },
    warning(msg) { this.show(msg, 'warning'); },
    info(msg)    { this.show(msg, 'info'); },
};

// ─── Modal Manager ──────────────────────────────────────────
const Modal = {
    backdrop: null,
    modal: null,
    titleEl: null,
    bodyEl: null,
    footerEl: null,

    init() {
        this.backdrop = document.getElementById('modalBackdrop');
        this.modal = document.getElementById('globalModal');
        this.titleEl = document.getElementById('modalTitle');
        this.bodyEl = document.getElementById('modalBody');
        this.footerEl = document.getElementById('modalFooter');

        // Close handlers
        document.getElementById('modalClose')?.addEventListener('click', () => this.close());
        this.backdrop?.addEventListener('click', (e) => {
            if (e.target === this.backdrop) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.backdrop?.style.display !== 'none') {
                this.close();
            }
        });
    },

    open(title, bodyHtml, footerHtml = '') {
        if (!this.backdrop) this.init();

        this.titleEl.textContent = title;
        this.bodyEl.innerHTML = bodyHtml;
        this.footerEl.innerHTML = footerHtml;
        this.backdrop.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    },

    close() {
        if (this.backdrop) {
            this.backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }
    },

    /**
     * Modal de confirmación
     */
    confirm(title, message, onConfirm, confirmText = 'Confirmar') {
        const body = `<p style="font-size: 0.9375rem; color: #4a5568;">${message}</p>`;
        const footer = `
            <button class="btn btn-outline" onclick="Modal.close()">Cancelar</button>
            <button class="btn btn-danger" id="modalConfirmBtn">${confirmText}</button>
        `;

        this.open(title, body, footer);

        document.getElementById('modalConfirmBtn')?.addEventListener('click', () => {
            this.close();
            if (typeof onConfirm === 'function') onConfirm();
        });
    },
};

// ─── Sidebar Logic ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const overlay = document.getElementById('sidebarOverlay');

    // Desktop: Toggle collapsed state
    sidebarToggle?.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
    });

    // Restore sidebar state
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        sidebar?.classList.add('collapsed');
    }

    // Mobile: Toggle open state
    mobileMenuToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('mobile-open');
        overlay?.classList.toggle('active');
    });

    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('mobile-open');
        overlay?.classList.remove('active');
    });

    // Initialize modules
    Toast.init();
    Modal.init();

    // Auto-dismiss flash alerts
    const flashAlert = document.getElementById('flashAlert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.opacity = '0';
            flashAlert.style.transform = 'translateY(-10px)';
            flashAlert.style.transition = 'all 0.3s ease';
            setTimeout(() => flashAlert.remove(), 300);
        }, 5000);
    }
});

// ─── Utility Functions ──────────────────────────────────────

/**
 * Formatear monto como moneda
 */
function formatMoney(amount) {
    return '$' + parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Formatear fecha ISO a dd/mm/yyyy
 */
function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
}

/**
 * Debounce function para búsquedas
 */
function debounce(fn, delay = 300) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

/**
 * Serializar un formulario a FormData
 */
function serializeForm(formId) {
    const form = document.getElementById(formId);
    return form ? new FormData(form) : new FormData();
}

/**
 * Inhabilitar/habilitar botón con spinner
 */
function toggleButtonLoading(btn, loading) {
    if (loading) {
        btn.dataset.originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
    }
}
