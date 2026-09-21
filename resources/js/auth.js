document.addEventListener('DOMContentLoaded', () => {
    // =========================================================================
    // 1. Password Visibility Toggle (Unified Delegated Handler)
    // =========================================================================
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-toggle-password], #toggle-password-btn, #toggle-current-password-btn, #toggle-confirm-password-btn');
        if (!toggleBtn) return;

        e.preventDefault();

        // Resolve target input ID from attribute or button ID
        let targetId = toggleBtn.getAttribute('data-toggle-password');
        if (!targetId) {
            if (toggleBtn.id === 'toggle-current-password-btn') targetId = 'current_password';
            else if (toggleBtn.id === 'toggle-password-btn') targetId = 'password';
            else if (toggleBtn.id === 'toggle-confirm-password-btn') targetId = 'password_confirmation';
        }

        const input = document.getElementById(targetId);
        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        // Precise attribute selectors matching IDs ending with "eye-icon" or "eye-slash-icon"
        const eyeIcon = toggleBtn.querySelector('[id$="eye-icon"]');
        const eyeSlashIcon = toggleBtn.querySelector('[id$="eye-slash-icon"]');

        if (eyeIcon) eyeIcon.classList.toggle('hidden', isPassword);
        if (eyeSlashIcon) eyeSlashIcon.classList.toggle('hidden', !isPassword);
    });

    // =========================================================================
    // 2. Universal Caps Lock Detection
    // =========================================================================
    const handleCapsLock = (e) => {
        const target = e.target;
        if (!target || !target.getAttribute) return;

        const warningId = target.getAttribute('data-caps-warning') || 'caps-lock-warning';
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

    // =========================================================================
    // 3. Forgot Password Dynamic Method Selection
    // =========================================================================
    const forgotForm = document.getElementById('forgot-password-form');
    if (forgotForm) {
        const forgotBtnLabel = forgotForm.querySelector('#btn-label');
        const methodInputs = forgotForm.querySelectorAll('input[name="method"]');

        const updateButtonLabel = () => {
            if (!forgotBtnLabel) return;
            const selectedMethod = forgotForm.querySelector('input[name="method"]:checked')?.value;

            if (selectedMethod === 'authenticator') {
                forgotBtnLabel.textContent = 'Verify via Authenticator App';
            } else {
                forgotBtnLabel.textContent = 'Send Email Reset Link';
            }
        };

        methodInputs.forEach((input) => {
            input.addEventListener('change', updateButtonLabel);
        });

        updateButtonLabel();
    }

    // =========================================================================
    // 4. Registration Verification & Password Checklist
    // =========================================================================
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
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
    }

    // =========================================================================
    // 5. Expired / Reset Real-Time Password Policy Checklist
    // =========================================================================
    const expiredForm = document.getElementById('expired-password-form');
    const resetForm = document.getElementById('reset-password-form');
    const checklistContainer = document.getElementById('password-checklist-container');

    if ((expiredForm || resetForm || checklistContainer) && !registerForm) {
        const currentPasswordInput = document.getElementById('current_password');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const submitBtn = document.getElementById('submit-btn');

        const minLength = parseInt(checklistContainer?.dataset.minLength || '8', 10);
        const reqUppercase = checklistContainer?.dataset.requireUppercase === 'true';
        const reqNumeric = checklistContainer?.dataset.requireNumeric === 'true';
        const reqSpecial = checklistContainer?.dataset.requireSpecial === 'true';

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
            const validCurrent = !currentPasswordInput || currentPwd.length > 0;

            updateRuleStatus('rule-length', validLength);
            if (reqUppercase) updateRuleStatus('rule-uppercase', validUpper);
            if (reqNumeric) updateRuleStatus('rule-numeric', validNumeric);
            if (reqSpecial) updateRuleStatus('rule-special', validSpecial);
            updateRuleStatus('rule-match', validMatch);

            if (submitBtn) {
                submitBtn.disabled = !(validLength && validUpper && validNumeric && validSpecial && validMatch && validCurrent);
            }
        };

        currentPasswordInput?.addEventListener('input', validatePasswordChecklist);
        passwordInput?.addEventListener('input', validatePasswordChecklist);
        confirmPasswordInput?.addEventListener('input', validatePasswordChecklist);

        validatePasswordChecklist();
    }

    // =========================================================================
    // 6. OTP Verification & Resend Cooldown Manager
    // =========================================================================
    const verifyOtpForm = document.getElementById('verify-otp-form');
    const resendOtpForm = document.getElementById('resend-otp-form');

    if (verifyOtpForm || resendOtpForm) {
        const resendBtn = document.getElementById('resend-btn');
        const timerLabel = document.getElementById('timer-label');

        let remainingSeconds = parseInt(resendBtn?.dataset.cooldown || '60', 10);

        const startCountdown = () => {
            if (!resendBtn || !timerLabel) return;

            if (remainingSeconds > 0) {
                resendBtn.disabled = true;
                resendBtn.className = 'font-bold text-slate-400 cursor-not-allowed transition disabled:opacity-60 flex items-center gap-1.5';
                timerLabel.textContent = `(${remainingSeconds}s)`;

                const interval = setInterval(() => {
                    remainingSeconds--;
                    if (remainingSeconds > 0) {
                        timerLabel.textContent = `(${remainingSeconds}s)`;
                    } else {
                        clearInterval(interval);
                        resendBtn.disabled = false;
                        resendBtn.className = 'font-bold text-brand hover:text-brand-hover transition cursor-pointer flex items-center gap-1.5';
                        timerLabel.textContent = '';
                    }
                }, 1000);
            } else {
                resendBtn.disabled = false;
                resendBtn.className = 'font-bold text-brand hover:text-brand-hover transition cursor-pointer flex items-center gap-1.5';
                timerLabel.textContent = '';
            }
        };

        startCountdown();
    }

    // =========================================================================
    // 7. Global Form Submit Loading & UI Lock Handler
    // =========================================================================
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form || !(form instanceof HTMLFormElement)) return;

        const submitBtn = form.querySelector('#dispatch-btn, #verify-btn, #resend-btn, #submit-btn, button[type="submit"]');
        const btnSpinner = form.querySelector('#dispatch-spinner, #resend-spinner, #btn-spinner');
        const btnLabel = form.querySelector('#dispatch-label, #resend-label, #btn-label');
        const btnIcon = form.querySelector('#btn-icon');
        const authContainer = document.getElementById('auth-container');

        if (submitBtn) submitBtn.disabled = true;
        btnSpinner?.classList.remove('hidden');
        btnIcon?.classList.add('hidden');

        if (btnLabel) {
            if (form.id === 'login-form') {
                btnLabel.textContent = 'Authenticating...';
            } else if (form.id === 'register-form') {
                btnLabel.textContent = 'Creating Account...';
            } else if (form.id === 'expired-password-form') {
                btnLabel.textContent = 'Updating Credentials...';
            } else if (form.id === 'reset-password-form') {
                btnLabel.textContent = 'Updating Password...';
            } else if (form.id === 'forgot-password-form') {
                const selectedMethod = form.querySelector('input[name="method"]:checked')?.value;
                btnLabel.textContent = selectedMethod === 'authenticator'
                    ? 'Redirecting to Authenticator...'
                    : 'Sending Reset Link...';
            } else if (form.id === 'dispatch-form') {
                btnLabel.textContent = 'Processing...';
            } else if (form.id === 'verify-otp-form') {
                btnLabel.textContent = 'Verifying...';
            } else if (form.id === 'resend-otp-form') {
                if (btnLabel.childNodes.length > 0 && btnLabel.childNodes[0].nodeType === Node.TEXT_NODE) {
                    btnLabel.childNodes[0].nodeValue = 'Sending... ';
                } else {
                    btnLabel.textContent = 'Sending... ';
                }
                document.getElementById('verify-otp-form')?.classList.add('opacity-85');
            }
        }

        authContainer?.classList.add('pointer-events-none');

        form.querySelectorAll('input').forEach((input) => {
            if (input.type !== 'checkbox' && input.type !== 'radio' && input.type !== 'hidden') {
                input.readOnly = true;
            }
        });

        form.classList.add('opacity-85');
    });

    // =========================================================================
    // 8. PWA Registration & In-Page Install Prompt Handler
    // =========================================================================
    let deferredPrompt = null;
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