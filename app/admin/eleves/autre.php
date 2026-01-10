<?php
/**
 * Page : Liste des Élèves
 * Rôle requis : Admin
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

$pageTitle = 'Gestion des Élèves';

// Filtres
$classe_filter = $_GET['classe_id'] ?? null;
$statut_filter = $_GET['statut'] ?? null;

// Récupérer l'année scolaire active
$stmtAnnee = $pdo->query("SELECT * FROM annee_scolaire WHERE actif = 1 LIMIT 1");
$annee_active = $stmtAnnee->fetch(PDO::FETCH_ASSOC);
$annee_id = $annee_active['id'] ?? null;

// Récupérer les élèves avec leurs informations
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
            u.email,
            e.nom_parent,
            e.telephone_parent,
            e.email_parent,
            e.date_inscription,
            i.id as inscription_id,
            CONCAT(c.niveau, ' ', c.nom) as classe_actuelle,
            c.id as classe_id,
            i.statut as statut_inscription,
            u.statut as statut_compte,
            a.libelle as annee_scolaire,
            e.created_at
        FROM eleves e
        JOIN personnes p ON e.personne_id = p.id
        JOIN utilisateurs u ON e.utilisateur_id = u.id
        LEFT JOIN inscriptions i ON e.id = i.eleve_id 
            AND i.annee_scolaire_id = :annee_id
            AND i.deleted_at IS NULL
        LEFT JOIN classes c ON i.classe_id = c.id
        LEFT JOIN annee_scolaire a ON i.annee_scolaire_id = a.id
        WHERE e.deleted_at IS NULL
    ";
    
    // Ajouter les filtres
    $params = ['annee_id' => $annee_id];
    
    if ($classe_filter) {
        $sql .= " AND c.id = :classe_id";
        $params['classe_id'] = $classe_filter;
    }
    
    if ($statut_filter) {
        $sql .= " AND i.statut = :statut";
        $params['statut'] = $statut_filter;
    }
    
    $sql .= " ORDER BY p.nom, p.prenom";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $eleves = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer la liste des classes pour le filtre
    $stmtClasses = $pdo->prepare("
        SELECT id, CONCAT(niveau, ' ', nom) as nom_complet 
        FROM classes 
        WHERE annee_scolaire_id = :annee_id 
        AND deleted_at IS NULL 
        ORDER BY 
            FIELD(niveau, '6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Tle'),
            nom
    ");
    $stmtClasses->execute(['annee_id' => $annee_id]);
    $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    $eleves = [];
    $classes = [];
}

require_once __DIR__ . '/../../include/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Élèves - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="parent">
        <div class="div3">
            <!-- En-tête -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-user-graduate"></i> Gestion des Élèves</h1>
                    <p class="text-muted">Effectif scolaire - Année <?= htmlspecialchars($annee_active['libelle'] ?? 'N/A') ?></p>
                </div>
                <div class="header-actions">
                    <button onclick="exporterExcel()" class="btn btn-success">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </button>
                    <a href="inscription-eleve.php" class="btn btn-primary">
                        <i class="fa-solid fa-user-plus"></i> Inscrire un élève
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count($eleves) ?></h3>
                        <p>Total Élèves</p>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($eleves, fn($e) => $e['statut_inscription'] === 'actif')) ?></h3>
                        <p>Actifs</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($eleves, fn($e) => empty($e['classe_actuelle']))) ?></h3>
                        <p>Sans Classe</p>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fa-solid fa-venus-mars"></i>
                    </div>
                    <div class="stat-content">
                        <?php 
                        $garcons = count(array_filter($eleves, fn($e) => $e['sexe'] === 'M'));
                        $filles = count($eleves) - $garcons;
                        ?>
                        <h3><?= $garcons ?> / <?= $filles ?></h3>
                        <p>Garçons / Filles</p>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card filter-card">
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="filter-grid">
                            <div class="search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" 
                                       id="searchEleves" 
                                       class="form-control" 
                                       placeholder="Rechercher un élève (nom, prénom, matricule)...">
                            </div>
                            
                            <select name="classe_id" id="filterClasse" class="form-select" onchange="this.form.submit()">
                                <option value="">🚪 Toutes les classes</option>
                                <option value="sans_classe" <?= $classe_filter === 'sans_classe' ? 'selected' : '' ?>>
                                    ⚠️ Sans classe assignée
                                </option>
                                <?php foreach ($classes as $classe): ?>
                                    <option value="<?= $classe['id'] ?>" 
                                            <?= $classe_filter == $classe['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($classe['nom_complet']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <select name="statut" id="filterStatut" class="form-select" onchange="this.form.submit()">
                                <option value="">📊 Tous les statuts</option>
                                <option value="actif" <?= $statut_filter === 'actif' ? 'selected' : '' ?>>✅ Actif</option>
                                <option value="redouble" <?= $statut_filter === 'redouble' ? 'selected' : '' ?>>🔄 Redoublant</option>
                                <option value="abandonne" <?= $statut_filter === 'abandonne' ? 'selected' : '' ?>>❌ Abandonné</option>
                                <option value="transfere" <?= $statut_filter === 'transfere' ? 'selected' : '' ?>>➡️ Transféré</option>
                                <option value="diplome" <?= $statut_filter === 'diplome' ? 'selected' : '' ?>>🎓 Diplômé</option>
                            </select>
                            
                            <select id="filterSexe" class="form-select">
                                <option value="">⚧️ Tous</option>
                                <option value="M">👨 Garçons</option>
                                <option value="F">👩 Filles</option>
                            </select>
                            
                            <a href="liste-eleves.php" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-rotate-right"></i> Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Actions groupées -->
            <div class="card actions-card">
                <div class="card-body">
                    <div class="actions-bar">
                        <div class="selection-info">
                            <input type="checkbox" id="selectAll" class="form-check">
                            <label for="selectAll">Sélectionner tout</label>
                            <span id="selectedCount" class="badge badge-primary">0 sélectionné(s)</span>
                        </div>
                        <div class="bulk-actions">
                            <button onclick="assignerClasseGroupe()" class="btn btn-sm btn-primary" disabled id="btnAssigner">
                                <i class="fa-solid fa-door-open"></i> Assigner à une classe
                            </button>
                            <button onclick="changerStatutGroupe()" class="btn btn-sm btn-warning" disabled id="btnStatut">
                                <i class="fa-solid fa-toggle-on"></i> Changer le statut
                            </button>
                            <button onclick="exporterSelection()" class="btn btn-sm btn-success" disabled id="btnExport">
                                <i class="fa-solid fa-download"></i> Exporter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des élèves -->
            <div class="card table-card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-table"></i> Liste des Élèves</h3>
                    <div class="table-info">
                        <span id="countResults"><?= count($eleves) ?> élève(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($eleves)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <h3>Aucun élève trouvé</h3>
                            <p>Commencez par inscrire votre premier élève</p>
                            <a href="inscription-eleve.php" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-user-plus"></i> Inscrire un élève
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAllTable" class="form-check">
                                        </th>
                                        <th>Matricule</th>
                                        <th>Nom & Prénom</th>
                                        <th>Sexe</th>
                                        <th>Âge</th>
                                        <th>Classe</th>
                                        <th>Contact Parent</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="elevesTableBody">
                                    <?php foreach ($eleves as $eleve): ?>
                                        <tr data-sexe="<?= htmlspecialchars($eleve['sexe']) ?>"
                                            data-classe="<?= htmlspecialchars($eleve['classe_id'] ?? '') ?>"
                                            data-statut="<?= htmlspecialchars($eleve['statut_inscription'] ?? '') ?>">
                                            <td>
                                                <input type="checkbox" 
                                                       class="form-check eleve-checkbox" 
                                                       value="<?= $eleve['eleve_id'] ?>"
                                                       data-inscription="<?= $eleve['inscription_id'] ?? '' ?>">
                                            </td>
                                            
                                            <td>
                                                <span class="badge badge-matricule">
                                                    <?= htmlspecialchars($eleve['matricule']) ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($eleve['prenom'], 0, 1) . substr($eleve['nom'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong class="user-name"><?= htmlspecialchars($eleve['nom']) ?></strong>
                                                        <small class="user-subtext"><?= htmlspecialchars($eleve['prenom']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="text-center">
                                                <?php if ($eleve['sexe'] === 'M'): ?>
                                                    <span class="badge badge-male">
                                                        <i class="fa-solid fa-mars"></i> M
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-female">
                                                        <i class="fa-solid fa-venus"></i> F
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td>
                                                <?php
                                                $age = floor((time() - strtotime($eleve['date_naissance'])) / (365*24*60*60));
                                                ?>
                                                <?= $age ?> ans
                                            </td>
                                            
                                            <td>
                                                <?php if (!empty($eleve['classe_actuelle'])): ?>
                                                    <span class="badge badge-info">
                                                        <i class="fa-solid fa-door-open"></i>
                                                        <?= htmlspecialchars($eleve['classe_actuelle']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fa-solid fa-exclamation-triangle"></i>
                                                        Non assigné
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td>
                                                <?php if (!empty($eleve['telephone_parent'])): ?>
                                                    <div class="contact-info-small">
                                                        <div>
                                                            <i class="fa-solid fa-user"></i>
                                                            <strong><?= htmlspecialchars($eleve['nom_parent'] ?? 'Parent') ?></strong>
                                                        </div>
                                                        <div>
                                                            <i class="fa-solid fa-phone"></i>
                                                            <a href="tel:<?= htmlspecialchars($eleve['telephone_parent']) ?>">
                                                                <?= htmlspecialchars($eleve['telephone_parent']) ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted-small">Non renseigné</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td>
                                                <?php
                                                $statut = $eleve['statut_inscription'] ?? 'non_inscrit';
                                                $badges = [
                                                    'actif' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Actif'],
                                                    'redouble' => ['class' => 'warning', 'icon' => 'rotate', 'text' => 'Redouble'],
                                                    'abandonne' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Abandonné'],
                                                    'transfere' => ['class' => 'info', 'icon' => 'arrow-right', 'text' => 'Transféré'],
                                                    'diplome' => ['class' => 'purple', 'icon' => 'graduation-cap', 'text' => 'Diplômé'],
                                                    'non_inscrit' => ['class' => 'secondary', 'icon' => 'question', 'text' => 'Non inscrit']
                                                ];
                                                $badgeInfo = $badges[$statut] ?? $badges['non_inscrit'];
                                                ?>
                                                <span class="badge badge-<?= $badgeInfo['class'] ?>">
                                                    <i class="fa-solid fa-<?= $badgeInfo['icon'] ?>"></i>
                                                    <?= $badgeInfo['text'] ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="detail-eleve.php?id=<?= $eleve['eleve_id'] ?>" 
                                                       class="btn-action btn-action-info" 
                                                       title="Voir détails">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <a href="modifier-eleve.php?id=<?= $eleve['eleve_id'] ?>" 
                                                       class="btn-action btn-action-warning" 
                                                       title="Modifier">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <a href="assigner-classe.php?eleve_id=<?= $eleve['eleve_id'] ?>" 
                                                       class="btn-action btn-action-primary" 
                                                       title="Assigner classe">
                                                        <i class="fa-solid fa-door-open"></i>
                                                    </a>
                                                    <a href="bulletin-eleve.php?id=<?= $eleve['eleve_id'] ?>" 
                                                       class="btn-action btn-action-success" 
                                                       title="Bulletin">
                                                        <i class="fa-solid fa-file-alt"></i>
                                                    </a>
                                                    <button onclick="supprimerEleve(<?= $eleve['eleve_id'] ?>, '<?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']) ?>')" 
                                                            class="btn-action btn-action-danger" 
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
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <style>
        :root {
            --primary: #4F46E5;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
            --purple: #8B5CF6;
            --secondary: #6B7280;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E5E7EB;
        }

        .page-header h1 {
            font-size: 2rem;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
        }

        .stat-primary .stat-icon { background: linear-gradient(135deg, var(--primary), #6366F1); }
        .stat-success .stat-icon { background: linear-gradient(135deg, var(--success), #34D399); }
        .stat-warning .stat-icon { background: linear-gradient(135deg, var(--warning), #FBBF24); }
        .stat-info .stat-icon { background: linear-gradient(135deg, var(--info), #60A5FA); }

        .stat-content h3 {
            font-size: 2rem;
            margin: 0;
        }

        .stat-content p {
            margin: 0.25rem 0 0 0;
            color: #6B7280;
            font-size: 0.9rem;
        }

        /* Filtres */
        .filter-card, .actions-card {
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr repeat(3, 1fr) auto;
            gap: 1rem;
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
        }

        .search-box input {
            padding-left: 3rem;
        }

        /* Actions groupées */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .selection-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .bulk-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), #6366F1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .user-name {
            display: block;
            color: #111827;
            font-size: 0.95rem;
        }

        .user-subtext {
            display: block;
            color: #6B7280;
            font-size: 0.85rem;
        }

        .contact-info-small {
            font-size: 0.85rem;
        }

        .contact-info-small div {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            margin-bottom: 0.25rem;
        }

        .contact-info-small i {
            color: #6B7280;
            width: 14px;
        }

        .contact-info-small a {
            color: var(--primary);
            text-decoration: none;
        }

        /* Badges */
        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .badge-matricule { background: #EEF2FF; color: var(--primary); font-family: monospace; }
        .badge-male { background: #DBEAFE; color: var(--info); }
        .badge-female { background: #FCE7F3; color: #EC4899; }
        .badge-info { background: #DBEAFE; color: var(--info); }
        .badge-success { background: #D1FAE5; color: var(--success); }
        .badge-warning { background: #FEF3C7; color: #D97706; }
        .badge-danger { background: #FEE2E2; color: var(--danger); }
        .badge-purple { background: #F3E8FF; color: var(--purple); }
        .badge-secondary { background: #F3F4F6; color: var(--secondary); }
        .badge-primary { background: #EEF2FF; color: var(--primary); }

        /* Actions */
        .action-buttons {
            display: flex;
            gap: 0.375rem;
            justify-content: center;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-action-info { background: #DBEAFE; color: var(--info); }
        .btn-action-warning { background: #FEF3C7; color: #D97706; }
        .btn-action-primary { background: #EEF2FF; color: var(--primary); }
        .btn-action-success { background: #D1FAE5; color: var(--success); }
        .btn-action-danger { background: #FEE2E2; color: var(--danger); }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.875rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-warning { background: var(--warning); color: white; }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>

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


</body>
</html>