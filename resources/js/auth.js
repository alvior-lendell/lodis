document.addEventListener('DOMContentLoaded', () => {
    // 1. Universal Password Visibility Toggle (Event Delegation)
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-toggle-password]');
        if (!toggleBtn) return;

        e.preventDefault();
        const targetId = toggleBtn.getAttribute('data-toggle-password');
        const input = document.getElementById(targetId);
        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        const eyeIcon = toggleBtn.querySelector('#eye-icon, [id*="eye-icon"]');
        const eyeSlashIcon = toggleBtn.querySelector('#eye-slash-icon, [id*="eye-slash-icon"]');

        eyeIcon?.classList.toggle('hidden', isPassword);
        eyeSlashIcon?.classList.toggle('hidden', !isPassword);
    });

    // 2. Universal Caps Lock Detection (Event Delegation)
    const handleCapsLock = (e) => {
        const warningId = e.target.getAttribute('data-caps-warning');
        if (!warningId) return;

        const warningEl = document.getElementById(warningId);
        if (!warningEl) return;

        if (e.type === 'blur' || e.type === 'focusout') {
            warningEl.classList.add('hidden');
            return;
        }

        if (e.getModifierState && e.getModifierState('CapsLock')) {
            warningEl.classList.remove('hidden');
        } else {
            warningEl.classList.add('hidden');
        }
    };

    document.addEventListener('keyup', handleCapsLock);
    document.addEventListener('keydown', handleCapsLock);
    document.addEventListener('focusout', handleCapsLock);

    // 3. Global Form Submit Loading Handler
    document.addEventListener('submit', (e) => {
        const form = e.target;
        const submitBtn = form.querySelector('#submit-btn, button[type="submit"]');
        const btnSpinner = form.querySelector('#btn-spinner');
        const btnLabel = form.querySelector('#btn-label');
        const authContainer = document.getElementById('auth-container');

        if (submitBtn) submitBtn.disabled = true;
        btnSpinner?.classList.remove('hidden');
        if (btnLabel && form.id === 'login-form') {
            btnLabel.textContent = 'Authenticating...';
        }
        authContainer?.classList.add('pointer-events-none');

        form.querySelectorAll('input').forEach(input => {
            if (input.type !== 'checkbox') {
                input.readOnly = true;
            }
        });

        form.classList.add('opacity-85');
    });

    // 4. PWA Registration & In-Page Install Prompt Handler
    let deferredPrompt;
    const installContainer = document.getElementById('pwa-install-container');
    const installBtn = document.getElementById('pwa-install-btn');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        installContainer?.classList.remove('hidden');
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        installContainer?.classList.add('hidden');
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        installContainer?.classList.add('hidden');
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
});