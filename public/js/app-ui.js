document.addEventListener('click', event => { const button = event.target.closest('[data-confirm]'); if (button && !confirm(button.dataset.confirm)) event.preventDefault(); });
