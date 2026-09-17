document.addEventListener('DOMContentLoaded', () => {
    const dispatchForm = document.getElementById('dispatch-form');
    const dispatchBtn = document.getElementById('dispatch-btn');
    const dispatchSpinner = document.getElementById('dispatch-spinner');
    const dispatchLabel = document.getElementById('dispatch-label');
    const authContainer = document.getElementById('auth-container');

    dispatchForm?.addEventListener('submit', () => {
        dispatchBtn.disabled = true;
        dispatchSpinner?.classList.remove('hidden');
        dispatchLabel.textContent = 'Processing...';
        authContainer?.classList.add('pointer-events-none');
    });
});