/*
| RodSant Store — interacciones ligeras del frontend público.
| Sin dependencias: Alpine.js se carga vía Livewire en el layout.
*/

/**
 * Reveal on scroll: añade `.is-visible` a los elementos `.reveal`
 * cuando entran en el viewport. Respeta `prefers-reduced-motion`.
 */
function initReveal() {
    const elements = document.querySelectorAll('.reveal');
    if (elements.length === 0) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || !('IntersectionObserver' in window)) {
        elements.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -8% 0px' },
    );

    elements.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', initReveal);
// Re-ejecutar tras navegaciones / actualizaciones de Livewire.
document.addEventListener('livewire:navigated', initReveal);
