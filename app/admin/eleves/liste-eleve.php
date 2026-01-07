<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>
<body>
    <div class="parent">
        <?php
/**
 * Page : Liste des Élèves
 * Rôle requis : Admin
 */

// Configuration et protection
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

$pageTitle = 'Liste des Élèves';

// Récupérer les élèves avec toutes leurs informations
try {
    $sql = "
        SELECT 
            e.id as eleve_id,
            e.matricule,
            p.nom,
            p.prenom,
            p.date_naissance,
            p.sexe,
            p.telephone,
            p.adresse,
            e.nom_parent,
            e.telephone_parent,
            e.email_parent,
            CONCAT(c.niveau, ' ', c.nom) as classe,
            u.statut as statut_compte,
            i.statut as statut_inscription,
            e.date_inscription,
            e.created_at
        FROM eleves e
        JOIN personnes p ON e.personne_id = p.id
        JOIN utilisateurs u ON e.utilisateur_id = u.id
        LEFT JOIN inscriptions i ON e.id = i.eleve_id
        LEFT JOIN classes c ON i.classe_id = c.id
        LEFT JOIN annee_scolaire a ON i.annee_scolaire_id = a.id AND a.actif = 1
        WHERE e.deleted_at IS NULL
        ORDER BY p.nom, p.prenom
    ";
    
    $stmt = $pdo->query($sql);
    $eleves = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors du chargement des élèves : " . $e->getMessage();
    $eleves = [];
}

// Inclure le header
require_once __DIR__ . '/../../include/header.php';
// show_test_warning();
?>
<div class="div3">
    <h1><i class="fa-solid fa-users"></i> LISTE DES ÉLÈVES</h1>
    
    <!-- Bouton d'ajout -->
    <div style="margin-bottom: 1.5rem;">
        <a href="<?= APP_URL ?>admin/eleves/inscription-eleve.php" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Nouvel élève
        </a>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h2><?= count($eleves) ?></h2>
                    <p>Total élèves</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h2><?= count(array_filter($eleves, fn($e) => ($e['statut_inscription'] ?? '') === 'actif')) ?></h2>
                    <p>Actifs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h2><?= count(array_filter($eleves, fn($e) => ($e['statut_compte'] ?? '') === 'en_attente')) ?></h2>
                    <p>En attente</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h2><?= count(array_filter($eleves, fn($e) => !empty($e['classe']))) ?></h2>
                    <p>Avec classe</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" 
                               id="searchEleves" 
                               class="form-control" 
                               placeholder="Rechercher par nom, prénom, matricule...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="filterClasse" class="form-select">
                        <option value="">Toutes les classes</option>
                        <?php
                        $classes = array_unique(array_filter(array_column($eleves, 'classe')));
                        sort($classes);
                        foreach ($classes as $classe) {
                            echo "<option value='" . htmlspecialchars($classe) . "'>" . htmlspecialchars($classe) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filterStatut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif">Actif</option>
                        <option value="en_attente">En attente</option>
                        <option value="redouble">Redoublant</option>
                        <option value="abandonne">Abandonné</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tableau des élèves -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-table"></i> Tableau des élèves</h3>
        </div>
        <div class="card-body">
            <?php if (empty($eleves)): ?>
                <div class="alert alert-warning text-center">
                    <i class="fa-solid fa-info-circle"></i>
                    <strong>Aucun élève enregistré</strong>
                    <p>Commencez par ajouter un nouvel élève.</p>
                    <a href="<?= APP_URL ?>admin/eleves/inscription-eleve.php" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Ajouter un élève
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Matricule</th>
                                <th>Nom & Prénom</th>
                                <th>Sexe</th>
                                <th>Date naissance</th>
                                <th>Classe</th>
                                <th>Parent/Tuteur</th>
                                <th>Contact</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="elevesTableBody">
                            <?php foreach ($eleves as $eleve): ?>
                                <tr data-classe="<?= htmlspecialchars($eleve['classe'] ?? '') ?>" 
                                    data-statut="<?= htmlspecialchars($eleve['statut_inscription'] ?? $eleve['statut_compte'] ?? '') ?>">
                                    
                                    <!-- Matricule -->
                                    <td>
                                        <strong class="text-primary">
                                            <?= htmlspecialchars($eleve['matricule'] ?? 'N/A') ?>
                                        </strong>
                                    </td>
                                    
                                    <!-- Nom complet -->
                                    <td>
                                        <strong><?= htmlspecialchars($eleve['nom']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($eleve['prenom']) ?></small>
                                    </td>
                                    
                                    <!-- Sexe -->
                                    <td class="text-center">
                                        <?php if ($eleve['sexe'] === 'M'): ?>
                                            <span class="badge bg-primary">
                                                <i class="fa-solid fa-mars"></i> M
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fa-solid fa-venus"></i> F
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Date de naissance -->
                                    <td>
                                        <?php
                                        $date = new DateTime($eleve['date_naissance']);
                                        $age = $date->diff(new DateTime())->y;
                                        ?>
                                        <?= $date->format('d/m/Y') ?>
                                        <br>
                                        <small class="text-muted">(<?= $age ?> ans)</small>
                                    </td>
                                    
                                    <!-- Classe -->
                                    <td>
                                        <?php if (!empty($eleve['classe'])): ?>
                                            <span class="badge bg-info">
                                                <i class="fa-solid fa-door-open"></i>
                                                <?= htmlspecialchars($eleve['classe']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fa-solid fa-question-circle"></i>
                                                Non assigné
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Parent -->
                                    <td>
                                        <?php if (!empty($eleve['nom_parent'])): ?>
                                            <i class="fa-solid fa-user"></i>
                                            <?= htmlspecialchars($eleve['nom_parent']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non renseigné</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Contact -->
                                    <td>
                                        <?php if (!empty($eleve['telephone_parent'])): ?>
                                            <i class="fa-solid fa-phone"></i>
                                            <a href="tel:<?= htmlspecialchars($eleve['telephone_parent']) ?>">
                                                <?= htmlspecialchars($eleve['telephone_parent']) ?>
                                            </a>
                                        <?php elseif (!empty($eleve['telephone'])): ?>
                                            <i class="fa-solid fa-phone"></i>
                                            <a href="tel:<?= htmlspecialchars($eleve['telephone']) ?>">
                                                <?= htmlspecialchars($eleve['telephone']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Statut -->
                                    <td>
                                        <?php
                                        $statut = $eleve['statut_inscription'] ?? $eleve['statut_compte'] ?? 'inconnu';
                                        $badges = [
                                            'actif' => 'success',
                                            'en_attente' => 'warning',
                                            'redouble' => 'info',
                                            'abandonne' => 'danger',
                                            'transfere' => 'secondary'
                                        ];
                                        $badgeClass = $badges[$statut] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $statut)) ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="detail-eleve.php?id=<?= $eleve['eleve_id'] ?>" 
                                               class="btn btn-sm btn-info" 
                                               title="Voir les détails">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="modifier-eleve.php?id=<?= $eleve['eleve_id'] ?>" 
                                               class="btn btn-sm btn-warning" 
                                               title="Modifier">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <button onclick="confirmerSuppression(<?= $eleve['eleve_id'] ?>, '<?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']) ?>')" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Supprimer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination (à implémenter plus tard si nécessaire) -->
                <div class="mt-3 text-muted">
                    <small>Affichage de <?= count($eleves) ?> élève(s)</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Recherche et filtres en temps réel
    const searchInput = document.getElementById('searchEleves');
    const filterClasse = document.getElementById('filterClasse');
    const filterStatut = document.getElementById('filterStatut');
    
    if (searchInput) {
        searchInput.addEventListener('input', filtrerEleves);
    }
    if (filterClasse) {
        filterClasse.addEventListener('change', filtrerEleves);
    }
    if (filterStatut) {
        filterStatut.addEventListener('change', filtrerEleves);
    }
    
    function filtrerEleves() {
        const recherche = searchInput.value.toLowerCase();
        const classeSelectionnee = filterClasse.value;
        const statutSelectionne = filterStatut.value;
        
        const lignes = document.querySelectorAll('#elevesTableBody tr');
        let compteur = 0;
        
        lignes.forEach(ligne => {
            const texte = ligne.textContent.toLowerCase();
            const classe = ligne.dataset.classe || '';
            const statut = ligne.dataset.statut || '';
            
            const correspondRecherche = texte.includes(recherche);
            const correspondClasse = !classeSelectionnee || classe === classeSelectionnee;
            const correspondStatut = !statutSelectionne || statut === statutSelectionne;
            
            if (correspondRecherche && correspondClasse && correspondStatut) {
                ligne.style.display = '';
                compteur++;
            } else {
                ligne.style.display = 'none';
            }
        });
        
        console.log(`${compteur} élève(s) trouvé(s)`);
    }
    
    // Fonction de confirmation de suppression
    function confirmerSuppression(id, nom) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'élève "${nom}" ?\n\nCette action est irréversible.`)) {
            // TODO: Implémenter la suppression via AJAX
            fetch('supprimer-eleve.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Élève supprimé avec succès');
                    location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la suppression');
            });
        }
    }
    
    // Message de bienvenue (optionnel)
    console.log('Page liste élèves chargée - <?= count($eleves) ?> élève(s) au total');
</script>

<?php
// Fermer la page
// require_once __DIR__ . '/../../include/footer.php';
?>
</body>
</html>