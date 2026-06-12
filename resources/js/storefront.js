/*
| RodSant Store — interacciones ligeras del frontend público.
| Sin dependencias: Alpine.js se carga vía Livewire en el layout.
*/

/**
 * Reveal on scroll: añade `.is-visible` a los elementos `.reveal`
 * cuando entran en el viewport. Respeta `prefers-reduced-motion`.
 */
function initReveal() {
    const simple = document.querySelectorAll('.reveal');
    const groups = document.querySelectorAll('[data-reveal-group]');
    if (simple.length === 0 && groups.length === 0) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || !('IntersectionObserver' in window)) {
        simple.forEach((el) => el.classList.add('is-visible'));
        groups.forEach((g) => Array.from(g.children).forEach((c) => c.classList.add('is-visible')));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;

                if (el.hasAttribute('data-reveal-group')) {
                    // Cascada: cada hijo aparece con un pequeño retardo incremental.
                    Array.from(el.children).forEach((child, i) => {
                        setTimeout(() => child.classList.add('is-visible'), i * 90);
                    });
                } else {
                    el.classList.add('is-visible');
                }

                observer.unobserve(el);
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -8% 0px' },
    );

    simple.forEach((el) => observer.observe(el));
    groups.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', initReveal);
// Re-ejecutar tras navegaciones / actualizaciones de Livewire.
document.addEventListener('livewire:navigated', initReveal);
