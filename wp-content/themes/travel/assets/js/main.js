(() => {
    const body = document.body;
    if (!body) {
        return;
    }

    window.requestAnimationFrame(() => {
        body.classList.add('travel-loaded');
    });

    const toggle = document.querySelector('.menu-toggle');
    const headerLinks = document.querySelector('.header-links');

    if (toggle && headerLinks) {
        toggle.addEventListener('click', () => {
            const isOpen = body.classList.toggle('nav-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        headerLinks.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (body.classList.contains('nav-open')) {
                    body.classList.remove('nav-open');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    document.querySelectorAll('[data-search-chip]').forEach((chip) => {
        chip.addEventListener('click', () => {
            const card = chip.closest('.search-card');
            const form = card ? card.querySelector('form') : document.querySelector('.travel-search');
            const input = form ? form.querySelector('.search-field') : null;
            const value = chip.getAttribute('data-search-chip');

            if (!input || !value) {
                return;
            }

            input.value = value;
            input.focus();
        });
    });
})();
