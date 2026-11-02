function setupItemDialog() {
    const dialog = document.getElementById('menu-item-modal');
    if (!dialog) return;

    const field = (name) => document.getElementById(`menu-item-modal-${name}`);
    const image = field('image');
    const allergensWrapper = field('allergens-wrapper');

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-menu-item]');

        if (trigger) {
            const { name, desc, price, image: src, category, allergens } = trigger.dataset;

            field('name').textContent = name;
            field('desc').textContent = desc;
            field('price').textContent = price;
            field('category').textContent = category;
            field('allergens').textContent = allergens;
            allergensWrapper.classList.toggle('hidden', !allergens);

            image.classList.toggle('hidden', !src);
            image.alt = src ? name : '';
            if (src) image.src = src;
            else image.removeAttribute('src');

            dialog.showModal();
        } else if (event.target === dialog) {
            dialog.close();
        }
    });
}

function setupCategoryHighlight() {
    const sections = document.querySelectorAll('[data-menu-section]');
    const chips = [...document.querySelectorAll('[data-menu-chip]')];
    if (!sections.length || !chips.length || !('IntersectionObserver' in window)) return;

    const setCurrent = (hash) => {
        chips.forEach((chip) => {
            const isCurrent = chip.getAttribute('href') === hash;

            chip.toggleAttribute('aria-current', isCurrent);
            if (isCurrent) {
                chip.setAttribute('aria-current', 'true');
                chip.parentElement.scrollTo({ left: chip.offsetLeft - 24 });
            }
        });
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) setCurrent(`#${entry.target.id}`);
        });
    }, { rootMargin: '-25% 0px -70% 0px' });

    sections.forEach((section) => observer.observe(section));

    window.addEventListener('scroll', () => {
        if (window.scrollY < sections[0].offsetTop - 200) setCurrent('#menu-top');
    }, { passive: true });
}

document.addEventListener('DOMContentLoaded', () => {
    setupItemDialog();
    setupCategoryHighlight();
});
