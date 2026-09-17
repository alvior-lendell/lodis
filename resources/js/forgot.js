document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('forgot-password-form');
    const authContainer = document.getElementById('auth-container');
    const submitBtn = document.getElementById('submit-btn');
    const btnSpinner = document.getElementById('btn-spinner');
    const btnLabel = document.getElementById('btn-label');

    form?.addEventListener('submit', () => {
        submitBtn.disabled = true;
        btnSpinner?.classList.remove('hidden');

        const selectedMethod = form.querySelector('input[name="method"]:checked')?.value;

        if (selectedMethod === 'sms') {
            btnLabel.textContent = 'Verifying Phone Number...';
        } else if (selectedMethod === 'authenticator') {
            btnLabel.textContent = 'Checking Authenticator...';
        } else {
            btnLabel.textContent = 'Sending Reset Link...';
        }

        authContainer?.classList.add('pointer-events-none');
        form.querySelectorAll('input').forEach(input => {
            if (input.type !== 'radio') {
                input.readOnly = true;
            }
        });
        form.classList.add('opacity-85');
    });
});