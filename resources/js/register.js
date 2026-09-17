document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('register-form');
    if (!registerForm) return;

    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const capsWarningPwd = document.getElementById('caps-lock-warning-pwd');
    const capsWarningConfirm = document.getElementById('caps-lock-warning-confirm');
    const submitBtn = document.getElementById('submit-btn');
    const spinner = document.getElementById('email-spinner');
    const feedback = document.getElementById('email-feedback');
    const checklistContainer = document.getElementById('password-checklist-container');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Read security settings safely from DOM data attributes
    const minLength = parseInt(checklistContainer?.dataset.minLength || '8', 10);
    const reqUppercase = checklistContainer?.dataset.requireUppercase === 'true';
    const reqNumeric = checklistContainer?.dataset.requireNumeric === 'true';
    const reqSpecial = checklistContainer?.dataset.requireSpecial === 'true';

    let isEmailVerified = false;
    let lookupTimeout = null;

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

        const isPasswordValid = validLength && validUpper && validNumeric && validSpecial && validMatch;
        if (submitBtn) submitBtn.disabled = !(isEmailVerified && isPasswordValid);
    };

    const resetFormState = () => {
        isEmailVerified = false;
        if (passwordInput) { 
            passwordInput.value = ''; 
            passwordInput.type = 'password';
            passwordInput.disabled = true; 
        }
        if (confirmPasswordInput) { 
            confirmPasswordInput.value = ''; 
            confirmPasswordInput.type = 'password';
            confirmPasswordInput.disabled = true; 
        }

        const eyeIcon = document.getElementById('eye-icon');
        const eyeSlashIcon = document.getElementById('eye-slash-icon');
        const confirmEyeIcon = document.getElementById('confirm-eye-icon');
        const confirmEyeSlashIcon = document.getElementById('confirm-eye-slash-icon');

        eyeIcon?.classList.remove('hidden');
        eyeSlashIcon?.classList.add('hidden');
        confirmEyeIcon?.classList.remove('hidden');
        confirmEyeSlashIcon?.classList.add('hidden');

        if (submitBtn) submitBtn.disabled = true;
        capsWarningPwd?.classList.add('hidden');
        capsWarningConfirm?.classList.add('hidden');
        if (feedback) {
            feedback.classList.add('hidden');
            feedback.className = 'text-xs mt-1.5 hidden';
        }
        validatePasswordChecklist();
    };

    const performEmailCheck = async () => {
        const email = emailInput?.value.trim() || '';

        if (!email || !/\S+@\S+\.\S+/.test(email)) {
            resetFormState();
            return;
        }

        spinner?.classList.remove('hidden');
        feedback?.classList.add('hidden');

        try {
            const response = await fetch('/register/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email })
            });

            if (!response.ok) throw new Error(`Server returned status ${response.status}`);

            const data = await response.json();
            spinner?.classList.add('hidden');

            if (data.found && !data.has_account) {
                isEmailVerified = true;
                if (passwordInput) passwordInput.disabled = false;
                if (confirmPasswordInput) confirmPasswordInput.disabled = false;

                if (feedback) {
                    feedback.textContent = '✓ Verified employee email. Please set your password.';
                    feedback.className = 'text-xs mt-1.5 text-emerald-600 font-medium block';
                }
                validatePasswordChecklist();
            } else {
                resetFormState();
                if (feedback) {
                    feedback.textContent = data.message || 'Email lookup failed.';
                    feedback.className = 'text-xs mt-1.5 text-rose-600 font-medium block';
                }
            }
        } catch (error) {
            spinner?.classList.add('hidden');
            resetFormState();
            if (feedback) {
                feedback.textContent = 'Unable to verify email address. Please check connection.';
                feedback.className = 'text-xs mt-1.5 text-rose-600 font-medium block';
            }
        }
    };

    passwordInput?.addEventListener('input', validatePasswordChecklist);
    confirmPasswordInput?.addEventListener('input', validatePasswordChecklist);

    emailInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performEmailCheck();
        }
    });

    emailInput?.addEventListener('input', () => {
        clearTimeout(lookupTimeout);
        lookupTimeout = setTimeout(performEmailCheck, 500);
    });

    emailInput?.addEventListener('blur', performEmailCheck);
});