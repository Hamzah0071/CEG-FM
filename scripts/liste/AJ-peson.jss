    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.querySelector('#studentTableBody');
        const addForm = document.querySelector('#addForm');
        const confirmAddBtn = document.querySelector('#confirmAddBtn');

        // 🔹 Fonction pour afficher / masquer le formulaire
        window.toggleAddForm = function () {
            addForm.style.display = addForm.style.display === 'block' ? 'none' : 'block';
            document.querySelector('#nameInput').value = '';
            document.querySelector('#classInput').value = '';
            document.querySelector('#dateInput').value = '';
        };

        // 🔹 Fonction pour trier le tableau par ordre alphabétique (colonne "Nom et prénom")
        function sortTable() {
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                const nameA = a.children[1].textContent.trim().toLowerCase();
                const nameB = b.children[1].textContent.trim().toLowerCase();
                return nameA.localeCompare(nameB, 'fr', { sensitivity: 'base' });
            });

            // Réinjecter les lignes triées et renuméroter
            tbody.innerHTML = '';
            rows.forEach((row, i) => {
                row.children[0].textContent = (i + 1).toString().padStart(2, '0');
                tbody.appendChild(row);
            });
        }

        // 🔹 Ajouter un élève
        confirmAddBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const name = document.querySelector('#nameInput').value.trim();
            const className = document.querySelector('#classInput').value.trim();
            const date = document.querySelector('#dateInput').value.trim();

            console.log(date);

            if (!name || !className || !date) {
                alert('Veuillez remplir tous les champs.');
                return;
            }

            if (!/^\d{4}\-\d{2}\-\d{2}$/.test(date)) {
                alert('Veuillez entrer une date au format /DD/MM/YYYY.');
                return;
            }

            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td></td>
                <td>${name}</td>
                <td>${className}</td>
                <td>${date}</td>
                <td>
                    <img src="../images/icone/icons8-crayon-50.png" alt="Modifier" class="edit-icon">
                    <img src="../images/icone/icons8-gomme-50.png" alt="Supprimer" class="delete-icon">
                </td>
            `;
            tbody.appendChild(newRow);

            // Trier après ajout
            sortTable();
            toggleAddForm();
        });

        // 🔹 Gérer les clics (édition, validation, suppression)
        tbody.addEventListener('click', (e) => {
            const target = e.target;

            // ✏️ Modifier
            if (target.classList.contains('edit-icon')) {
                const row = target.closest('tr');
                const cells = row.querySelectorAll('td:not(:last-child)');
                cells.forEach((cell, i) => {
                    if (i === 0) return;
                    const value = cell.textContent.trim();
                    cell.innerHTML = `<input type="text" value="${value}">`;
                });
                row.querySelector('td:last-child').innerHTML = `
                    <button class="save-btn">Valider</button>
                    <img src="../images/icone/icons8-gomme-50.png" alt="Supprimer" class="delete-icon">
                `;
            }

            // ✅ Valider modification
            else if (target.classList.contains('save-btn')) {
                const row = target.closest('tr');
                const inputs = row.querySelectorAll('input');
                const [nameInput, classInput, dateInput] = inputs;

                if (!nameInput.value.trim() || !classInput.value.trim() || !dateInput.value.trim()) {
                    alert('Veuillez remplir tous les champs.');
                    return;
                }

                if (!/^\d{4}\/\d{2}\/\d{2}$/.test(dateInput.value.trim())) {
                    alert('Veuillez entrer une date au format YYYY/MM/DD.');
                    return;
                }

                const cells = row.querySelectorAll('td:not(:last-child)');
                cells[1].textContent = nameInput.value.trim();
                cells[2].textContent = classInput.value.trim();
                cells[3].textContent = dateInput.value.trim();

                row.querySelector('td:last-child').innerHTML = `
                    <img src="../images/icone/icons8-crayon-50.png" alt="Modifier" class="edit-icon">
                    <img src="../images/icone/icons8-gomme-50.png" alt="Supprimer" class="delete-icon">
                `;

                // Trier après modification
                sortTable();
            }

            // 🗑️ Supprimer
            else if (target.classList.contains('delete-icon')) {
                if (confirm("Voulez-vous vraiment supprimer cet élève ?")) {
                    const row = target.closest('tr');
                    row.remove();
                    sortTable(); // Trier + renuméroter après suppression
                }
            }
        });

        // Tri initial si besoin
        sortTable();
    });