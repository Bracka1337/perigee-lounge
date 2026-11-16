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

    // After a chip click, keep its highlight while the page scrolls there, so sections
    // passing by on the way don't take it over.
    let lockedUntil = 0;
    chips.forEach((chip) => chip.addEventListener('click', () => {
        setCurrent(chip.getAttribute('href'));
        lockedUntil = Date.now() + 1000;
    }));

    const observer = new IntersectionObserver((entries) => {
        if (Date.now() < lockedUntil) return;
        entries.forEach((entry) => {
            if (entry.isIntersecting) setCurrent(`#${entry.target.id}`);
        });
    }, { rootMargin: '-25% 0px -70% 0px' });

    sections.forEach((section) => observer.observe(section));

    const lastSection = sections[sections.length - 1];
    window.addEventListener('scroll', () => {
        if (Date.now() < lockedUntil) return;
        if (window.scrollY < sections[0].offsetTop - 200) {
            setCurrent('#menu-top');
        } else if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
            // A short last section never reaches the observer's band, so the page bottom selects it.
            setCurrent(`#${lastSection.id}`);
        }
    }, { passive: true });
}

// A photo whose file is missing shows the category placeholder instead of a broken image.
function setupPhotoFallback() {
    const showPlaceholder = (img) => {
        img.hidden = true;
        const placeholder = img.parentElement.querySelector('[data-menu-photo-placeholder]');
        if (placeholder) placeholder.hidden = false;
    };

    document.querySelectorAll('img[data-menu-photo]').forEach((img) => {
        if (img.complete && img.naturalWidth === 0 && img.currentSrc) {
            showPlaceholder(img);
        } else {
            img.addEventListener('error', () => showPlaceholder(img), { once: true });
        }
    });

    const modalImage = document.getElementById('menu-item-modal-image');
    modalImage?.addEventListener('error', () => modalImage.classList.add('hidden'));
}

// The available-only switch reloads the page; carry the highlighted category along so it stays in view.
function setupKeepCategory() {
    document.querySelectorAll('form[data-menu-keep-category]').forEach((form) => {
        form.addEventListener('submit', () => {
            const current = document.querySelector('[data-menu-chip][aria-current="true"]')?.getAttribute('href');
            const action = new URL(form.action, window.location.href);
            action.hash = current && current !== '#menu-top' ? current : '';
            form.action = action.toString();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    setupItemDialog();
    setupCategoryHighlight();
    setupPhotoFallback();
    setupKeepCategory();
});
