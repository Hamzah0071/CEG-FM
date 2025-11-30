// basePath est défini dans header.php → toujours disponible
const basePath = window.basePath || '';

document.addEventListener('DOMContentLoaded', () => {
    // Cache des sélecteurs fréquents
    const sidebar       = document.querySelector('.div1');
    const parent        = document.querySelector('.parent');
    const toggleBtn     = document.getElementById('toggleSidebar');

    // ========================
    // 1. TOGGLE SIDEBAR (minimize / expand)
    // ========================
    if (toggleBtn && parent) {
    toggleBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        parent.classList.toggle('expanded');   // ← "expanded" au lieu de "minimized"
        // sidebar.classList.toggle('minimized'); ← supprimé, géré par CSS
    });
}

    // ========================
    // 2. SOUS-MENUS SIDEBAR (.div1)
    // ========================
    document.querySelectorAll('.div1 .submenu-item > .menu-item').forEach(menuItem => {
        menuItem.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Si sidebar réduite → on bloque l'ouverture (sauf si tu veux autoriser)
            if (sidebar.classList.contains('minimized')) return;

            const submenuItem = this.parentElement;
            const isActive    = submenuItem.classList.toggle('active'); // toggle + retour état
            const arrow       = this.querySelector('.arrow-icon');

            // Change la flèche
            if (arrow) {
                arrow.src = isActive
                    ? `${basePath}images/icone/fleche-haut.png`
                    : `${basePath}images/icone/fleche-bas.png`;
            }

            // Ferme tous les autres sous-menus (accordéon)
            document.querySelectorAll('.div1 .submenu-item').forEach(sibling => {
                if (sibling !== submenuItem) {
                    sibling.classList.remove('active');
                    const sibArrow = sibling.querySelector('.arrow-icon');
                    if (sibArrow) sibArrow.src = `${basePath}images/icone/fleche-bas.png`;
                }
            });
        });
    });

    // ========================
    // 3. DROPDOWN PROFIL (.div2 .droite)
    // ========================
    const profileDropdowns = document.querySelectorAll('.div2 .droite .dropdown');

    profileDropdowns.forEach(dropdown => {
        const btn   = dropdown.querySelector('.dropbtn');
        const arrow = btn?.querySelector('.arrow-icon');

        if (!btn) return;

        const toggleDropdown = (e) => {
            e?.preventDefault();
            e?.stopPropagation();

            const isActive = dropdown.classList.toggle('active');

            // Change la flèche
            if (arrow) {
                arrow.src = isActive
                    ? `${basePath}images/icone/fleche-haut.png`
                    : `${basePath}images/icone/fleche-bas.png`;
            }

            // Ferme les autres dropdowns (au cas où il y en aurait plusieurs)
            profileDropdowns.forEach(other => {
                if (other !== dropdown) {
                    other.classList.remove('active');
                    const otherArrow = other.querySelector('.arrow-icon');
                    if (otherArrow) otherArrow.src = `${basePath}images/icone/fleche-bas.png`;
                }
            });
        };

        btn.addEventListener('click', toggleDropdown);

        // Accessibilité clavier
        btn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleDropdown(e);
            }
        });
    });

    // ========================
    // 4. FERMER LE DROPDOWN SI CLIC AILLEURS
    // ========================
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown') && !e.target.closest('.dropbtn')) {
            profileDropdowns.forEach(dropdown => {
                if (dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                    const arrow = dropdown.querySelector('.arrow-icon');
                    if (arrow) arrow.src = `${basePath}images/icone/fleche-bas.png`;
                }
            });
        }
    });

    // Bonus : fermer avec Échap
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            profileDropdowns.forEach(dropdown => {
                if (dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                    const arrow = dropdown.querySelector('.arrow-icon');
                    if (arrow) arrow.src = `${basePath}images/icone/fleche-bas.png`;
                }
            });
        }
    });
});