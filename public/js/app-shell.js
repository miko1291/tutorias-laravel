(() => {
    const logoutForm = document.getElementById('logout-form');
    let lastActivity = Date.now();

    if (logoutForm) {
        ['pointerdown', 'pointermove', 'keydown', 'scroll', 'touchstart'].forEach(type => {
            document.addEventListener(type, () => { lastActivity = Date.now(); }, {passive: true});
        });
        window.setInterval(() => {
            if (Date.now() - lastActivity >= 10 * 60 * 1000) {
                logoutForm.requestSubmit();
            }
        }, 30 * 1000);
    }

    const resetForms = () => {
        document.querySelectorAll('form[data-submitting]').forEach(form => {
            delete form.dataset.submitting;
            form.querySelectorAll('[data-loading-label]').forEach(button => {
                if (button.tagName === 'INPUT') button.value = button.dataset.loadingLabel;
                else button.innerHTML = button.dataset.loadingLabel;
                button.disabled = false;
                button.classList.remove('is-loading');
                delete button.dataset.loadingLabel;
            });
        });
    };

    document.addEventListener('submit', event => {
        const form = event.target;
        if (event.defaultPrevented || form.matches('[data-no-loading]') || form.method.toLowerCase() === 'get') return;
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = 'true';
        // Espera a que el navegador capture el valor del botón antes de desactivarlo.
        window.setTimeout(() => {
            if (event.defaultPrevented) {
                resetForms();
                return;
            }
            form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach(button => {
                if (button.disabled) return;
                button.dataset.loadingLabel = button.tagName === 'INPUT' ? button.value : button.innerHTML;
                button.disabled = true;
                button.classList.add('is-loading');
                if (button.tagName === 'INPUT') button.value = 'Procesando…';
                else button.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" aria-hidden="true"></span> Procesando…';
            });
        }, 0);
    });
    window.addEventListener('pageshow', resetForms);

    const nav = document.querySelector('.pit-navbar');
    if (!nav) return;
    const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
    nav.querySelectorAll('a[href]').forEach(link => {
        if (link.matches('[data-toggle]')) return;
        const target = new URL(link.href);
        if (target.origin === window.location.origin && target.pathname === currentPath) {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
            const dropdown = link.closest('.dropdown');
            if (dropdown) dropdown.querySelector('.dropdown-toggle')?.classList.add('active');
        }
    });
})();
