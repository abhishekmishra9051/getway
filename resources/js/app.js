import './bootstrap';

/**
 * data-copy="<text>" — copies the text to the clipboard and briefly shows "Copied!".
 * Used on payment link copy buttons throughout the dashboard.
 */
document.addEventListener('click', async (e) => {
    const el = e.target.closest('[data-copy]');
    if (!el) return;
    e.preventDefault();
    try {
        await navigator.clipboard.writeText(el.dataset.copy);
    } catch (_) {
        return;
    }
    const label = el.querySelector('[data-copy-label]') || el;
    const original = label.textContent;
    label.textContent = 'Copied!';
    setTimeout(() => label.textContent = original, 1500);
});

document.addEventListener('click', (event) => {
    const confirmButton = event.target.closest('[data-confirm]');
    if (confirmButton && !window.confirm(confirmButton.dataset.confirm)) {
        event.preventDefault();
        return;
    }

    const navigationTarget = event.target.closest('[data-href]');
    if (navigationTarget && !event.target.closest('a, button, input, select, textarea')) {
        window.location.assign(navigationTarget.dataset.href);
    }

    if (event.target.closest('[data-sidebar-toggle]')) {
        document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
        document.getElementById('backdrop')?.classList.toggle('hidden');
    }

    if (event.target.closest('[data-sidebar-close]')) {
        document.getElementById('backdrop')?.classList.add('hidden');
        document.getElementById('sidebar')?.classList.add('-translate-x-full');
    }

    if (event.target.closest('[data-toggle-secret]')) {
        const secret = document.getElementById('secret');
        if (secret) secret.type = secret.type === 'password' ? 'text' : 'password';
    }

    if (event.target.closest('[data-reload-page]')) {
        window.location.reload();
    }
});

document.addEventListener('change', (event) => {
    if (event.target.matches('[data-submit-on-change]')) {
        event.target.form?.requestSubmit();
    }
});

document.addEventListener('input', (event) => {
    if (!event.target.matches('[data-amount-preview]')) return;
    const preview = document.getElementById('preview-amount');
    const amount = Number.parseFloat(event.target.value || 0);
    if (preview) {
        preview.textContent = `₹${amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
});
