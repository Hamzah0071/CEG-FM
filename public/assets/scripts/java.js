// =========================
// VARIABLES GLOBALES
// =========================
const sidebar = document.querySelector('.div1');
const parent = document.querySelector('.parent');
const toggleBtn = document.getElementById('toggleSidebar');
const menuItems = document.querySelectorAll('.menu-item');

// =========================
// TOGGLE SIDEBAR
// =========================
if (toggleBtn) {
  toggleBtn.addEventListener('click', (e) => {
    e.preventDefault();
    
    // Desktop : minimiser la sidebar
    if (window.innerWidth > 768) {
      parent.classList.toggle('minimized');
      sidebar.classList.toggle('minimized');
      
      // Sauvegarder l'état dans localStorage
      const isMinimized = parent.classList.contains('minimized');
      localStorage.setItem('sidebarMinimized', isMinimized);
    } 
    // Mobile : afficher/masquer la sidebar
    else {
      sidebar.classList.toggle('show');
      
      // Créer/retirer l'overlay
      let overlay = document.querySelector('.overlay');
      if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'overlay';
        document.body.appendChild(overlay);
        
        // Fermer au clic sur l'overlay
        overlay.addEventListener('click', () => {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        });
      }
      
      overlay.classList.toggle('show');
    }
  });
}

// =========================
// RESTAURER L'ÉTAT DE LA SIDEBAR
// =========================
window.addEventListener('DOMContentLoaded', () => {
  if (window.innerWidth > 768) {
    const isMinimized = localStorage.getItem('sidebarMinimized') === 'true';
    if (isMinimized) {
      parent.classList.add('minimized');
      sidebar.classList.add('minimized');
    }
  }
});

// =========================
// MENU ACTIF
// =========================
// Marquer l'élément de menu actif selon l'URL actuelle
const currentPath = window.location.pathname;

menuItems.forEach(item => {
  const href = item.getAttribute('href');
  
  if (href && currentPath.includes(href)) {
    // Retirer la classe active de tous les items
    menuItems.forEach(i => i.classList.remove('active'));
    
    // Ajouter la classe active à l'item courant
    item.classList.add('active');
  }
});

// =========================
// DROPDOWN (si pas géré par CSS :hover)
// =========================
const dropdownBtn = document.querySelector('.dropbtn');
const dropdown = document.querySelector('.dropdown');

if (dropdownBtn && dropdown) {
  // Pour mobile : toggle au clic
  if (window.innerWidth <= 768) {
    dropdownBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdown.classList.toggle('active');
    });
    
    // Fermer si clic ailleurs
    document.addEventListener('click', (e) => {
      if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('active');
      }
    });
  }
}

// =========================
// RECHERCHE (optionnel)
// =========================
const searchBar = document.getElementById('search');

if (searchBar) {
  searchBar.addEventListener('input', (e) => {
    const searchTerm = e.target.value.toLowerCase();
    
    // Si la recherche est vide, ne rien faire
    if (!searchTerm) return;
    
    // Filtrer les éléments du menu (exemple)
    menuItems.forEach(item => {
      const text = item.textContent.toLowerCase();
      if (text.includes(searchTerm)) {
        item.style.display = 'flex';
      } else {
        item.style.display = 'none';
      }
    });
    
    // Réinitialiser si vide
    if (searchTerm === '') {
      menuItems.forEach(item => {
        item.style.display = 'flex';
      });
    }
  });
}

// =========================
// TOOLTIPS POUR SIDEBAR MINIMISÉE
// =========================
// Ajouter l'attribut data-tooltip aux menu-items
menuItems.forEach(item => {
  const span = item.querySelector('span');
  if (span) {
    item.setAttribute('data-tooltip', span.textContent);
  }
});

// =========================
// RESPONSIVE : ADAPTER AU REDIMENSIONNEMENT
// =========================
let resizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    
    // Si on passe en desktop, retirer la classe show
    if (window.innerWidth > 768) {
      sidebar.classList.remove('show');
      const overlay = document.querySelector('.overlay');
      if (overlay) {
        overlay.classList.remove('show');
      }
    }
    
    // Si on passe en mobile, retirer minimized
    else {
      parent.classList.remove('minimized');
      sidebar.classList.remove('minimized');
    }
    
  }, 250);
});

// =========================
// ANIMATIONS AU SCROLL (optionnel)
// =========================
const div3 = document.querySelector('.div3');

if (div3) {
  div3.addEventListener('scroll', () => {
    const header = document.querySelector('.div2');
    if (div3.scrollTop > 50) {
      header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
    } else {
      header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)';
    }
  });
}

// =========================
// CONFIRMATION DE SUPPRESSION
// =========================
const deleteButtons = document.querySelectorAll('[data-confirm]');

deleteButtons.forEach(btn => {
  btn.addEventListener('click', (e) => {
    const message = btn.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
    
    if (!confirm(message)) {
      e.preventDefault();
    }
  });
});

// =========================
// AUTO-SUBMIT POUR FILTRES
// =========================
const autoSubmitSelects = document.querySelectorAll('[data-auto-submit]');

autoSubmitSelects.forEach(select => {
  select.addEventListener('change', () => {
    select.closest('form').submit();
  });
});

// =========================
// MESSAGES FLASH (disparition auto)
// =========================
const flashMessages = document.querySelectorAll('.alert, .message');

flashMessages.forEach(msg => {
  if (msg.getAttribute('data-auto-dismiss') !== 'false') {
    setTimeout(() => {
      msg.style.opacity = '0';
      msg.style.transform = 'translateY(-10px)';
      msg.style.transition = 'all 0.3s ease';
      
      setTimeout(() => {
        msg.remove();
      }, 300);
    }, 5000); // Disparaît après 5 secondes
  }
});

// =========================
// COPIER DANS LE PRESSE-PAPIERS
// =========================
const copyButtons = document.querySelectorAll('[data-copy]');

copyButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    const text = btn.getAttribute('data-copy');
    
    navigator.clipboard.writeText(text).then(() => {
      // Afficher un feedback
      const originalText = btn.textContent;
      btn.textContent = '✓ Copié !';
      btn.style.color = '#28a745';
      
      setTimeout(() => {
        btn.textContent = originalText;
        btn.style.color = '';
      }, 2000);
    });
  });
});

// =========================
// CONSOLE INFO
// =========================
console.log('%c🎓 CEG FM - Système de Gestion Scolaire', 'color: #667eea; font-size: 16px; font-weight: bold;');
console.log('%cDéveloppé avec <i class="fa-solid fa-heart"></i>', 'color: #764ba2; font-size: 12px;');