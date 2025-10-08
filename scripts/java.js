document.addEventListener('DOMContentLoaded', () => {
    // Gestion des sous-menus dans div1
    document.querySelectorAll('.div1 .submenu-item > .menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const submenuItem = this.parentElement;
            const submenu = submenuItem.querySelector(':scope > .submenu');
            const arrow = this.querySelector('.arrow-icon');
            const div1 = document.querySelector('.div1');

            // Vérifier si div1 est réduit (minimized), si oui, ne pas ouvrir le sous-menu
            if (div1.classList.contains('minimized')) {
                return; // Arrêter l'exécution si div1 est réduit
            }

            if (submenu && arrow) {
                submenuItem.classList.toggle('active');

                // Change l'icône de la flèche
                arrow.src = submenuItem.classList.contains('active')
                    ? '../images/icone/fleche-haut.png'
                    : '../images/icone/fleche-bas.png';

                // Ferme les autres sous-menus au même niveau
                const parentMenu = submenuItem.parentElement;
                const siblingSubmenuItems = parentMenu.querySelectorAll(':scope > .submenu-item');
                siblingSubmenuItems.forEach(sibling => {
                    if (sibling !== submenuItem && sibling.querySelector(':scope > .submenu')) {
                        sibling.classList.remove('active');
                        const siblingArrow = sibling.querySelector(':scope > .menu-item > .arrow-icon');
                        if (siblingArrow) {
                            siblingArrow.src = '../images/icone/fleche-bas.png';
                        }
                    }
                });
            }
        });
    });

    // Gestion des dropdowns dans .div2 .droite
    document.querySelectorAll('.div2 .droite .dropdown .dropbtn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Empêche tout comportement par défaut (ex. : navigation)
            e.stopPropagation(); // Empêche la propagation au parent <a> ou autres éléments

            const dropdown = this.closest('.dropdown');
            const arrow = this.querySelector('.arrow-icon');

        // Ferme tous les autres dropdowns
            document.querySelectorAll('.div2 .droite .dropdown').forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('active');
                    const otherArrow = otherDropdown.querySelector('.arrow-icon');
                    if (otherArrow) {
                        otherArrow.src = '../images/icone/fleche-bas.png';
                    }
                }
            });

        // Bascule l'état du dropdown cliqué
            dropdown.classList.toggle('active');
            if (arrow) {
                arrow.src = dropdown.classList.contains('active')
                    ? '../images/icone/fleche-haut.png'
                    : '../images/icone/fleche-bas.png';
            }
        });

        // Gestion de l'accessibilité (touches Entrée et Espace)
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click(); // Simule un clic sur le bouton
                }
            });
        });

        // Ferme les dropdowns si on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.div2 .droite .dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                    const arrow = dropdown.querySelector('.arrow-icon');
                    if (arrow) {
                        arrow.src = '../images/icone/fleche-bas.png';
                    }
                });
            }
        });

    // Gestion de la réduction/élargissement de div1
    const toggleSidebar = document.getElementById('toggleSidebar');
    const parent = document.querySelector('.parent');
    const div1 = document.querySelector('.div1');

    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function(e) {
            e.preventDefault();
            parent.classList.toggle('minimized');
            div1.classList.toggle('minimized');
        });
    }
});