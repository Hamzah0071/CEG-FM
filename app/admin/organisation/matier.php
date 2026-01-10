<?php
/**
 * Page : Liste des Matières
 * Rôle requis : Admin
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

$pageTitle = 'Gestion des Matières';

// Récupérer toutes les matières avec statistiques
try {
    $sql = "
        SELECT 
            m.*,
            COUNT(DISTINCT e.professeur_id) as nombre_professeurs,
            COUNT(DISTINCT e.classe_id) as nombre_classes,
            GROUP_CONCAT(DISTINCT p.nom ORDER BY p.nom SEPARATOR ', ') as professeurs
        FROM matieres m
        LEFT JOIN enseignements e ON m.id = e.matiere_id AND e.deleted_at IS NULL
        LEFT JOIN professeurs prof ON e.professeur_id = prof.id
        LEFT JOIN personnes p ON prof.personne_id = p.id
        WHERE m.deleted_at IS NULL
        GROUP BY m.id
        ORDER BY m.categorie, m.nom
    ";
    
    $stmt = $pdo->query($sql);
    $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Grouper par catégorie
    $matieres_par_categorie = [];
    foreach ($matieres as $matiere) {
        $categorie = $matiere['categorie'] ?? 'Autres';
        $matieres_par_categorie[$categorie][] = $matiere;
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    $matieres = [];
}

require_once __DIR__ . '/../../include/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Matières - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="parent">
        <div class="div3">
            <!-- En-tête -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-book"></i> Gestion des Matières</h1>
                    <p class="text-muted">Programme scolaire et disciplines enseignées</p>
                </div>
                <div class="header-actions">
                    <button onclick="exporterCSV()" class="btn btn-success">
                        <i class="fa-solid fa-file-excel"></i> Export CSV
                    </button>
                    <a href="ajouter-matiere.php" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Nouvelle matière
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
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count($matieres) ?></h3>
                        <p>Total Matières</p>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count($matieres_par_categorie) ?></h3>
                        <p>Catégories</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= array_sum(array_column($matieres, 'nombre_professeurs')) ?></h3>
                        <p>Affectations Profs</p>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= number_format(array_sum(array_column($matieres, 'coefficient')), 1) ?></h3>
                        <p>Total Coefficients</p>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card filter-card">
                <div class="card-body">
                    <div class="filter-grid">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" 
                                   id="searchMatieres" 
                                   class="form-control" 
                                   placeholder="Rechercher une matière...">
                        </div>
                        
                        <select id="filterCategorie" class="form-select">
                            <option value="">📚 Toutes les catégories</option>
                            <?php foreach (array_keys($matieres_par_categorie) as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select id="filterCoefficient" class="form-select">
                            <option value="">🎯 Coefficient</option>
                            <option value="1">Coef. 1</option>
                            <option value="2">Coef. 2</option>
                            <option value="3">Coef. 3</option>
                            <option value="4">Coef. 4+</option>
                        </select>
                        
                        <button onclick="reinitialiserFiltres()" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-rotate-right"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Matières groupées par catégorie -->
            <?php foreach ($matieres_par_categorie as $categorie => $matieres_cat): ?>
                <div class="categorie-section" data-categorie="<?= htmlspecialchars($categorie) ?>">
                    <div class="categorie-header">
                        <h2>
                            <i class="fa-solid fa-folder"></i>
                            <?= htmlspecialchars($categorie) ?>
                            <span class="badge badge-info"><?= count($matieres_cat) ?> matière(s)</span>
                        </h2>
                    </div>

                    <div class="matieres-grid">
                        <?php foreach ($matieres_cat as $matiere): ?>
                            <div class="matiere-card" 
                                 data-categorie="<?= htmlspecialchars($matiere['categorie'] ?? 'Autres') ?>"
                                 data-coefficient="<?= $matiere['coefficient'] ?>">
                                
                                <div class="matiere-header">
                                    <div class="matiere-icon">
                                        <i class="fa-solid fa-book-open"></i>
                                    </div>
                                    <div class="matiere-title">
                                        <h3><?= htmlspecialchars($matiere['nom']) ?></h3>
                                        <span class="matiere-code"><?= htmlspecialchars($matiere['code']) ?></span>
                                    </div>
                                    <div class="coefficient-badge">
                                        <span class="coef-label">Coef.</span>
                                        <span class="coef-value"><?= $matiere['coefficient'] ?></span>
                                    </div>
                                </div>

                                <div class="matiere-body">
                                    <div class="matiere-stats">
                                        <div class="stat-item">
                                            <i class="fa-solid fa-chalkboard-user"></i>
                                            <div>
                                                <strong><?= $matiere['nombre_professeurs'] ?></strong>
                                                <small>Professeur(s)</small>
                                            </div>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fa-solid fa-door-open"></i>
                                            <div>
                                                <strong><?= $matiere['nombre_classes'] ?></strong>
                                                <small>Classe(s)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($matiere['professeurs'])): ?>
                                        <div class="professeurs-list">
                                            <i class="fa-solid fa-users"></i>
                                            <span title="<?= htmlspecialchars($matiere['professeurs']) ?>">
                                                <?= htmlspecialchars(substr($matiere['professeurs'], 0, 40)) ?>
                                                <?= strlen($matiere['professeurs']) > 40 ? '...' : '' ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="matiere-footer">
                                    <a href="detail-matiere.php?id=<?= $matiere['id'] ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fa-solid fa-eye"></i> Détails
                                    </a>
                                    <a href="modifier-matiere.php?id=<?= $matiere['id'] ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </a>
                                    <button onclick="supprimerMatiere(<?= $matiere['id'] ?>, '<?= htmlspecialchars($matiere['nom']) ?>')" 
                                            class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($matieres)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <h3>Aucune matière enregistrée</h3>
                    <p>Commencez par ajouter les matières du programme scolaire</p>
                    <a href="ajouter-matiere.php" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-plus"></i> Ajouter une matière
                    </a>
                </div>
            <?php endif; ?>

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
        .filter-card {
            margin-bottom: 2rem;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
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

        /* Catégorie Section */
        .categorie-section {
            margin-bottom: 3rem;
        }

        .categorie-header {
            background: linear-gradient(135deg, var(--purple), #A78BFA);
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0;
            margin-bottom: 1.5rem;
        }

        .categorie-header h2 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .badge-info {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        /* Matières Grid */
        .matieres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .matiere-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s;
        }

        .matiere-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .matiere-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #F9FAFB, #F3F4F6);
            border-bottom: 2px solid #E5E7EB;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .matiere-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), #6366F1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .matiere-title {
            flex: 1;
        }

        .matiere-title h3 {
            margin: 0 0 0.25rem 0;
            font-size: 1.1rem;
            color: #111827;
        }

        .matiere-code {
            display: inline-block;
            background: #EEF2FF;
            color: var(--primary);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            font-family: monospace;
        }

        .coefficient-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(135deg, var(--warning), #FBBF24);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            min-width: 60px;
        }

        .coef-label {
            font-size: 0.7rem;
            font-weight: 600;
        }

        .coef-value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .matiere-body {
            padding: 1.25rem;
        }

        .matiere-stats {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .stat-item i {
            font-size: 1.5rem;
            color: var(--info);
        }

        .stat-item strong {
            display: block;
            font-size: 1.25rem;
            color: #111827;
        }

        .stat-item small {
            display: block;
            color: #6B7280;
            font-size: 0.75rem;
        }

        .professeurs-list {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: #F9FAFB;
            border-radius: 6px;
            color: #6B7280;
            font-size: 0.85rem;
        }

        .professeurs-list i {
            color: var(--success);
        }

        .matiere-footer {
            padding: 1rem 1.25rem;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
            display: flex;
            gap: 0.5rem;
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

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-warning { background: var(--warning); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-info { background: var(--info); color: white; }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .empty-icon {
            font-size: 4rem;
            color: #6B7280;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .matieres-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Filtrage
        const searchInput = document.getElementById('searchMatieres');
        const filterCategorie = document.getElementById('filterCategorie');
        const filterCoefficient = document.getElementById('filterCoefficient');

        function filtrerMatieres() {
            const recherche = searchInput.value.toLowerCase();
            const categorieSelectionnee = filterCategorie.value;
            const coefficientSelectionne = filterCoefficient.value;

            document.querySelectorAll('.matiere-card').forEach(card => {
                const texte = card.textContent.toLowerCase();
                const categorie = card.dataset.categorie;
                const coefficient = parseFloat(card.dataset.coefficient);

                let afficher = true;

                if (recherche && !texte.includes(recherche)) afficher = false;
                if (categorieSelectionnee && categorie !== categorieSelectionnee) afficher = false;
                
                if (coefficientSelectionne) {
                    if (coefficientSelectionne === '1' && coefficient !== 1) afficher = false;
                    if (coefficientSelectionne === '2' && coefficient !== 2) afficher = false;
                    if (coefficientSelectionne === '3' && coefficient !== 3) afficher = false;
                    if (coefficientSelectionne === '4' && coefficient < 4) afficher = false;
                }

                card.style.display = afficher ? '' : 'none';
            });

            // Masquer les sections vides
            document.querySelectorAll('.categorie-section').forEach(section => {
                const cardsVisibles = section.querySelectorAll('.matiere-card:not([style*="display: none"])');
                section.style.display = cardsVisibles.length > 0 ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filtrerMatieres);
        filterCategorie?.addEventListener('change', filtrerMatieres);
        filterCoefficient?.addEventListener('change', filtrerMatieres);

        function reinitialiserFiltres() {
            searchInput.value = '';
            filterCategorie.value = '';
            filterCoefficient.value = '';
            filtrerMatieres();
        }

        function supprimerMatiere(id, nom) {
            if (confirm(`⚠️ Êtes-vous sûr de vouloir supprimer la matière "${nom}" ?`)) {
                fetch('supprimer-matiere.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Matière supprimée');
                        location.reload();
                    } else {
                        alert('❌ Erreur : ' + data.message);
                    }
                });
            }
        }

        function exporterCSV() {
            let csv = '\uFEFF';
            csv += 'Code;Nom;Catégorie;Coefficient;Professeurs;Classes\n';

            document.querySelectorAll('.matiere-card').forEach(card => {
                if (card.style.display !== 'none') {
                    const code = card.querySelector('.matiere-code').textContent.trim();
                    const nom = card.querySelector('.matiere-title h3').textContent.trim();
                    const categorie = card.dataset.categorie;
                    const coefficient = card.dataset.coefficient;
                    const stats = card.querySelectorAll('.stat-item strong');
                    const nbProfs = stats[0]?.textContent.trim() || '0';
                    const nbClasses = stats[1]?.textContent.trim() || '0';
                    
                    csv += `${code};${nom};${categorie};${coefficient};${nbProfs};${nbClasses}\n`;
                }
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `matieres_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
        }
    </script>

    
</body>
</html>