document.addEventListener('DOMContentLoaded', () => {
    const verifyForm = document.getElementById('verify-otp-form');
    const resendForm = document.getElementById('resend-otp-form');
    const authContainer = document.getElementById('auth-container');
    const otpInput = document.getElementById('otp');
    const verifyBtn = document.getElementById('verify-btn');
    const btnSpinner = document.getElementById('btn-spinner');
    const btnLabel = document.getElementById('btn-label');
    const resendBtn = document.getElementById('resend-btn');
    const resendSpinner = document.getElementById('resend-spinner');
    const resendLabel = document.getElementById('resend-label');
    const timerLabel = document.getElementById('timer-label');

    let remainingSeconds = parseInt("{{ $cooldownSeconds ?? 60 }}", 10);

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
        verifyBtn.disabled = true;
        btnSpinner?.classList.remove('hidden');
        btnLabel.textContent = 'Verifying...';
        authContainer?.classList.add('pointer-events-none');
        if (otpInput) otpInput.readOnly = true;
        verifyForm.classList.add('opacity-85');
    });

    resendForm?.addEventListener('submit', () => {
        resendBtn.disabled = true;
        resendSpinner?.classList.remove('hidden');
        if (resendLabel) resendLabel.childNodes[0].nodeValue = 'Sending... ';
        authContainer?.classList.add('pointer-events-none');
        verifyForm?.classList.add('opacity-85');
    });
});