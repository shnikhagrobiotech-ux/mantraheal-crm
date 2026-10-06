// MantraHeal Core JavaScript Helpers
document.addEventListener('DOMContentLoaded', () => {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

function confirmAction(message, formId) {
    if (confirm(message || 'Are you sure you want to proceed with this action?')) {
        document.getElementById(formId).submit();
    }
}

function copyToClipboard(text, elementId = null) {
    navigator.clipboard.writeText(text).then(() => {
        if (elementId) {
            const el = document.getElementById(elementId);
            const original = el.innerText;
            el.innerText = 'Copied!';
            setTimeout(() => el.innerText = original, 2000);
        } else {
            alert('Copied to clipboard: ' + text);
        }
    });
}
