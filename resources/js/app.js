import './bootstrap';

const header = document.querySelector('.site-header');
const menuToggle = document.querySelector('[data-site-menu-toggle]');
const siteMenu = document.querySelector('[data-site-menu]');

if (header && menuToggle && siteMenu) {
    const setMenuOpen = (isOpen) => {
        header.classList.toggle('site-header--open', isOpen);
        menuToggle.setAttribute('aria-expanded', String(isOpen));
        menuToggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');

        const icon = menuToggle.querySelector('.site-header__toggle-icon');
        if (icon) {
            icon.textContent = isOpen ? 'close' : 'menu';
        }
    };

    header.classList.add('site-header--js');
    setMenuOpen(false);

    menuToggle.addEventListener('click', () => {
        setMenuOpen(!header.classList.contains('site-header--open'));
    });

    siteMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMenuOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuOpen(false);
        }
    });
}
