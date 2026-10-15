// Sitio público: integración progresiva. Sin JS o con "reducir movimiento" todo se ve estático.
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const finePointer = window.matchMedia('(pointer: fine)').matches;

// Scroll narrativo con IntersectionObserver (sustituible por GSAP ScrollTrigger en una fase posterior).
if (!reduceMotion && 'IntersectionObserver' in window) {
    document.documentElement.classList.add('js-motion');
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.1 });
    document.querySelectorAll('[data-reveal]').forEach((el, i) => {
        el.style.transitionDelay = `${(Number(el.dataset.reveal) || 0) * 90}ms`;
        io.observe(el);
    });
}

// Objeto "R": rota sutilmente según el cursor; en táctil/reducido queda con una inclinación fija.
const stage = document.querySelector('[data-r-stage]');
const object = document.querySelector('[data-r-object]');
if (stage && object && !reduceMotion) {
    let frame = null;
    const tilt = (x, y) => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => {
            object.style.transform = `rotateY(${x * 22 - 14}deg) rotateX(${-y * 16 + 6}deg)`;
        });
    };
    if (finePointer) {
        window.addEventListener('pointermove', (e) => tilt(e.clientX / window.innerWidth, e.clientY / window.innerHeight), { passive: true });
    } else {
        let t = 0;
        const idle = () => { t += 0.01; tilt(0.5 + Math.sin(t) * 0.25, 0.5 + Math.cos(t * 0.8) * 0.15); requestAnimationFrame(idle); };
        idle();
    }
}

// Cabecera con fondo al hacer scroll.
const header = document.querySelector('[data-site-header]');
if (header) {
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY > 24;
        header.classList.toggle('bg-ink/80', scrolled);
        header.classList.toggle('backdrop-blur-xl', scrolled);
        header.classList.toggle('border-white/10', scrolled);
    }, { passive: true });
}

// Menú móvil.
document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => {
    document.querySelector('[data-menu]')?.classList.toggle('hidden');
});

// Formulario: el plan de interés solo aplica al sistema para talleres.
const serviceSelect = document.querySelector('#service_type');
const planField = document.querySelector('[data-plan-field]');
if (serviceSelect && planField) {
    const toggle = () => planField.classList.toggle('hidden', serviceSelect.value !== 'taller');
    serviceSelect.addEventListener('change', toggle);
    toggle();
}
