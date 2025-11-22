document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript chargé'); // Debug

    const tbody = document.getElementById('studentTableBody');
    const addForm = document.getElementById('addForm');
    const confirmAddBtn = document.getElementById('confirmAddBtn');
    const searchInput = document.getElementById('searchInput');

    if (!tbody || !addForm || !confirmAddBtn) {
        console.error('Éléments manquants dans le DOM');
        return;
    }

    // 🔹 Recherche en temps réel
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = tbody.getElementsByTagName('tr');
            
            for (let row of rows) {
                const className = row.cells[1].textContent.toLowerCase();
                const initial = row.cells[2].textContent.toLowerCase();
                
                if (className.includes(searchTerm) || initial.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // 🔹 Ajouter une classe
    confirmAddBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const nameInput = document.getElementById('nameInput');
        const initialInput = document.getElementById('initialInput');
        
        const name = nameInput.value.trim();
        const initial = initialInput.value.trim();

        if (!name || !initial) {
            alert('Veuillez remplir tous les champs.');
            return;
        }

        try {
            const response = await fetch('../php/save_class.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `name=${encodeURIComponent(name)}&initial=${encodeURIComponent(initial)}`
            });

            const result = await response.json();
            
            if (result.success) {
                // Ajouter la nouvelle ligne au tableau
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-id', name);
                newRow.innerHTML = `
                    <td>${(tbody.children.length + 1).toString().padStart(2, '0')}</td>
                    <td class="editable">${name}</td>
                    <td class="editable">${initial}</td>
                    <td>
                        <img src="../images/icone/icons8-crayon-50.png" alt="Modifier" class="edit-icon" style="cursor:pointer; width:20px; margin-right:10px;">
                        <img src="../images/icone/icons8-gomme-50.png" alt="Supprimer" class="delete-icon" style="cursor:pointer; width:20px;">
                    </td>
                `;
                tbody.appendChild(newRow);
                
                // Réinitialiser le formulaire
                nameInput.value = '';
                initialInput.value = '';
                addForm.style.display = 'none';
                
                alert(result.message);
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'ajout de la classe');
        }
    });

    // 🔹 Gérer les actions (Modifier/Supprimer)
    tbody.addEventListener('click', function(e) {
        const target = e.target;
        const row = target.closest('tr');
        const className = row.cells[1].textContent;

        // ✏️ Modification
        if (target.classList.contains('edit-icon')) {
            const nameCell = row.cells[1];
            const initialCell = row.cells[2];
            
            const currentName = nameCell.textContent;
            const currentInitial = initialCell.textContent;
            
            nameCell.innerHTML = `<input type="text" value="${currentName}" class="edit-input">`;
            initialCell.innerHTML = `<input type="text" value="${currentInitial}" class="edit-input">`;
            
            row.cells[3].innerHTML = `
                <button class="save-btn" style="margin-right:5px;">💾</button>
                <button class="cancel-btn">❌</button>
            `;
        }
        
        // ✅ Sauvegarder modification
        else if (target.classList.contains('save-btn')) {
            const nameInput = row.cells[1].querySelector('input');
            const initialInput = row.cells[2].querySelector('input');
            
            const newName = nameInput.value.trim();
            const newInitial = initialInput.value.trim();
            
            if (!newName || !newInitial) {
                alert('Les champs ne peuvent pas être vides');
                return;
            }

            // Appel AJAX pour sauvegarder
            fetch('../php/edit_class.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `oldName=${encodeURIComponent(className)}&newName=${encodeURIComponent(newName)}&newInitial=${encodeURIComponent(newInitial)}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    row.cells[1].textContent = newName;
                    row.cells[2].textContent = newInitial;
                    row.setAttribute('data-id', newName);
                    row.cells[3].innerHTML = `
                        <img src="../images/icone/icons8-crayon-50.png" alt="Modifier" class="edit-icon" style="cursor:pointer; width:20px; margin-right:10px;">
                        <img src="../images/icone/icons8-gomme-50.png" alt="Supprimer" class="delete-icon" style="cursor:pointer; width:20px;">
                    `;
                    alert(result.message);
                } else {
                    alert(result.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la modification');
            });
        }
        
        // ❌ Annuler modification
        else if (target.classList.contains('cancel-btn')) {
            location.reload(); // Recharger la page pour réinitialiser
        }
        
        // 🗑️ Suppression
        else if (target.classList.contains('delete-icon')) {
            if (confirm(`Voulez-vous vraiment supprimer la classe "${className}" ?`)) {
                fetch('../php/delete_class.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `name=${encodeURIComponent(className)}`
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        row.remove();
                        alert(result.message);
                        // Re-numéroter les lignes
                        const rows = tbody.getElementsByTagName('tr');
                        for (let i = 0; i < rows.length; i++) {
                            rows[i].cells[0].textContent = (i + 1).toString().padStart(2, '0');
                        }
                    } else {
                        alert(result.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la suppression');
                });
            }
        }
    });
});