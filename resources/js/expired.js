document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('expired-password-form');
    if (!form) return;

    const authContainer = document.getElementById('auth-container');
    const currentPasswordInput = document.getElementById('current_password');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const submitBtn = document.getElementById('submit-btn');
    const btnSpinner = document.getElementById('btn-spinner');
    const btnLabel = document.getElementById('btn-label');
    const capsLockWarning = document.getElementById('caps-lock-warning');
    const checklistContainer = document.getElementById('password-checklist-container');

    // Read policy properties safely from dataset attributes
    const minLength = parseInt(checklistContainer?.dataset.minLength || '8', 10);
    const reqUppercase = checklistContainer?.dataset.requireUppercase === 'true';
    const reqNumeric = checklistContainer?.dataset.requireNumeric === 'true';
    const reqSpecial = checklistContainer?.dataset.requireSpecial === 'true';

    // Current Password Visibility Toggle
    const toggleCurrentPasswordBtn = document.getElementById('toggle-current-password-btn');
    const currentEyeIcon = document.getElementById('current-eye-icon');
    const currentEyeSlashIcon = document.getElementById('current-eye-slash-icon');

    toggleCurrentPasswordBtn?.addEventListener('click', () => {
        const isPassword = currentPasswordInput.type === 'password';
        currentPasswordInput.type = isPassword ? 'text' : 'password';
        currentEyeIcon?.classList.toggle('hidden', isPassword);
        currentEyeSlashIcon?.classList.toggle('hidden', !isPassword);
    });

    // Password Visibility Toggle
    const togglePasswordBtn = document.getElementById('toggle-password-btn');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeSlashIcon = document.getElementById('eye-slash-icon');

    togglePasswordBtn?.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        eyeIcon?.classList.toggle('hidden', isPassword);
        eyeSlashIcon?.classList.toggle('hidden', !isPassword);
    });

    // Confirm Password Visibility Toggle
    const toggleConfirmPasswordBtn = document.getElementById('toggle-confirm-password-btn');
    const confirmEyeIcon = document.getElementById('confirm-eye-icon');
    const confirmEyeSlashIcon = document.getElementById('confirm-eye-slash-icon');

    toggleConfirmPasswordBtn?.addEventListener('click', () => {
        const isPassword = confirmPasswordInput.type === 'password';
        confirmPasswordInput.type = isPassword ? 'text' : 'password';
        confirmEyeIcon?.classList.toggle('hidden', isPassword);
        confirmEyeSlashIcon?.classList.toggle('hidden', !isPassword);
    });

    // Real-Time Caps Lock Detection
    const checkCapsLock = (event) => {
        if (event.getModifierState && event.getModifierState('CapsLock')) {
            capsLockWarning?.classList.remove('hidden');
        } else {
            capsLockWarning?.classList.add('hidden');
        }
    };

    [currentPasswordInput, passwordInput, confirmPasswordInput].forEach(input => {
        input?.addEventListener('keyup', checkCapsLock);
        input?.addEventListener('keydown', checkCapsLock);
        input?.addEventListener('blur', () => {
            capsLockWarning?.classList.add('hidden');
        });
    });

    const updateRuleStatus = (elementId, isValid) => {
        const el = document.getElementById(elementId);
        if (!el) return;
        el.className = isValid 
            ? 'flex items-center gap-2 text-emerald-600 font-medium transition' 
            : 'flex items-center gap-2 text-slate-400 transition';
    };

    const validatePasswordChecklist = () => {
        const pwd = passwordInput?.value || '';
        const confirmPwd = confirmPasswordInput?.value || '';
        const currentPwd = currentPasswordInput?.value || '';

        const validLength = pwd.length >= minLength;
        const validUpper = !reqUppercase || /[A-Z]/.test(pwd);
        const validNumeric = !reqNumeric || /[0-9]/.test(pwd);
        const validSpecial = !reqSpecial || /[^A-Za-z0-9]/.test(pwd);
        const validMatch = pwd.length > 0 && pwd === confirmPwd;

        updateRuleStatus('rule-length', validLength);
        if (reqUppercase) updateRuleStatus('rule-uppercase', validUpper);
        if (reqNumeric) updateRuleStatus('rule-numeric', validNumeric);
        if (reqSpecial) updateRuleStatus('rule-special', validSpecial);
        updateRuleStatus('rule-match', validMatch);

        if (submitBtn) {
            submitBtn.disabled = !(validLength && validUpper && validNumeric && validSpecial && validMatch && currentPwd.length > 0);
        }
    };

    currentPasswordInput?.addEventListener('input', validatePasswordChecklist);
    passwordInput?.addEventListener('input', validatePasswordChecklist);
    confirmPasswordInput?.addEventListener('input', validatePasswordChecklist);

    form?.addEventListener('submit', () => {
        if (submitBtn) submitBtn.disabled = true;
        btnSpinner?.classList.remove('hidden');
        if (btnLabel) btnLabel.textContent = 'Updating Credentials...';

        authContainer?.classList.add('pointer-events-none');
        form.querySelectorAll('input').forEach(input => {
            if (input.type !== 'hidden') {
                input.readOnly = true;
            }
        });
        form.classList.add('opacity-85');
    });
});