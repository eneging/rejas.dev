// Panel: tema claro/oscuro persistente y utilidades mínimas.
const root = document.documentElement;

document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const dark = root.classList.toggle('dark');
        try { localStorage.setItem('rejas-theme', dark ? 'dark' : 'light'); } catch {}
    });
});

// Menú lateral en móvil.
document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => document.querySelector('[data-sidebar]')?.classList.toggle('-translate-x-full'));
});

// Filas dinámicas (partidas de cotización, hitos de pago).
document.querySelectorAll('[data-repeater]').forEach((wrap) => {
    const tpl = wrap.querySelector('template');
    const list = wrap.querySelector('[data-repeater-list]');
    let index = list.children.length;

    const recalc = () => {
        const out = wrap.querySelector('[data-repeater-total]');
        if (!out) return;
        let total = 0;
        list.querySelectorAll('[data-row]').forEach((row) => {
            const q = parseFloat(row.querySelector('[data-qty]')?.value || 1);
            const p = parseFloat(row.querySelector('[data-price]')?.value || 0);
            total += q * p;
        });
        out.textContent = total.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    wrap.querySelector('[data-repeater-add]')?.addEventListener('click', () => {
        list.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__INDEX__', index++));
        recalc();
    });
    wrap.addEventListener('click', (e) => {
        const remove = e.target.closest('[data-repeater-remove]');
        if (remove && list.children.length > 1) { remove.closest('[data-row]').remove(); recalc(); }
    });
    wrap.addEventListener('input', recalc);
    recalc();
});

// Confirmaciones simples.
document.addEventListener('submit', (e) => {
    const msg = e.target.dataset.confirm;
    if (msg && !window.confirm(msg)) e.preventDefault();
});
