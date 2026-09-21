document.addEventListener('DOMContentLoaded', () => {
    const verifyForm = document.getElementById('verify-otp-form');
    const resendForm = document.getElementById('resend-otp-form');
    
    // Guard clause: Exit if neither form exists on the current page
    if (!verifyForm && !resendForm) return;

    const authContainer = document.getElementById('auth-container');
    const otpInput = document.getElementById('otp');
    const verifyBtn = document.getElementById('verify-btn');
    const btnSpinner = document.getElementById('btn-spinner');
    const btnLabel = document.getElementById('btn-label');
    const resendBtn = document.getElementById('resend-btn');
    const resendSpinner = document.getElementById('resend-spinner');
    const resendLabel = document.getElementById('resend-label');
    const timerLabel = document.getElementById('timer-label');

    // Read initial cooldown from HTML data attribute instead of Blade interpolation
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

    verifyForm?.addEventListener('submit', () => {
        if (verifyBtn) verifyBtn.disabled = true;
        btnSpinner?.classList.remove('hidden');
        if (btnLabel) btnLabel.textContent = 'Verifying...';
        authContainer?.classList.add('pointer-events-none');
        if (otpInput) otpInput.readOnly = true;
        verifyForm.classList.add('opacity-85');
    });

    resendForm?.addEventListener('submit', () => {
        if (resendBtn) resendBtn.disabled = true;
        resendSpinner?.classList.remove('hidden');
        if (resendLabel) resendLabel.childNodes[0].nodeValue = 'Sending... ';
        authContainer?.classList.add('pointer-events-none');
        verifyForm?.classList.add('opacity-85');
    });
});