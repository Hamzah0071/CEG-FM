<?php
/**
 * Page : Liste des Classes
 * Rôle requis : Admin
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

$pageTitle = 'Gestion des Classes';

// Récupérer l'année scolaire active
$stmtAnnee = $pdo->query("SELECT * FROM annee_scolaire WHERE actif = 1 LIMIT 1");
$annee_active = $stmtAnnee->fetch(PDO::FETCH_ASSOC);

// Récupérer toutes les classes avec statistiques
try {
    $sql = "
        SELECT 
            c.id,
            c.nom,
            c.niveau,
            c.effectif_max,
            c.salle_principale,
            a.libelle as annee_scolaire,
            COUNT(DISTINCT i.id) as effectif_actuel,
            COUNT(DISTINCT e.professeur_id) as nombre_professeurs,
            GROUP_CONCAT(DISTINCT 
                CONCAT(p.nom, ' ', p.prenom) 
                SEPARATOR ', '
            ) as professeurs_titulaires
        FROM classes c
        LEFT JOIN annee_scolaire a ON c.annee_scolaire_id = a.id
        LEFT JOIN inscriptions i ON c.id = i.classe_id 
            AND i.statut = 'actif' 
            AND i.deleted_at IS NULL
        LEFT JOIN enseignements e ON c.id = e.classe_id 
            AND e.est_titulaire = 1 
            AND e.deleted_at IS NULL
        LEFT JOIN professeurs prof ON e.professeur_id = prof.id
        LEFT JOIN personnes p ON prof.personne_id = p.id
        WHERE c.deleted_at IS NULL
        GROUP BY c.id, c.nom, c.niveau, c.effectif_max, c.salle_principale, a.libelle
        ORDER BY 
            FIELD(c.niveau, '6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Tle'),
            c.nom
    ";
    
    $stmt = $pdo->query($sql);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors du chargement des classes : " . $e->getMessage();
    $classes = [];
}

require_once __DIR__ . '/../../include/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Classes - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="parent">
        <div class="div3">
            <!-- En-tête -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-door-open"></i> Gestion des Classes</h1>
                    <p class="text-muted">Organisation des classes par niveau</p>
                </div>
                <div class="header-actions">
                    <button onclick="exporterPDF()" class="btn btn-danger">
                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                    </button>
                    <a href="ajouter-classe.php" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Nouvelle classe
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
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count($classes) ?></h3>
                        <p>Total Classes</p>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= array_sum(array_column($classes, 'effectif_actuel')) ?></h3>
                        <p>Élèves Inscrits</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div class="stat-content">
                        <?php 
                        $total_capacite = array_sum(array_column($classes, 'effectif_max'));
                        $total_inscrits = array_sum(array_column($classes, 'effectif_actuel'));
                        $taux = $total_capacite > 0 ? round(($total_inscrits / $total_capacite) * 100) : 0;
                        ?>
                        <h3><?= $taux ?>%</h3>
                        <p>Taux de Remplissage</p>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fa-solid fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $annee_active['libelle'] ?? 'N/A' ?></h3>
                        <p>Année Scolaire</p>
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
                                   id="searchClasses" 
                                   class="form-control" 
                                   placeholder="Rechercher une classe...">
                        </div>
                        
                        <select id="filterNiveau" class="form-select">
                            <option value="">📚 Tous les niveaux</option>
                            <option value="6ème">6ème</option>
                            <option value="5ème">5ème</option>
                            <option value="4ème">4ème</option>
                            <option value="3ème">3ème</option>
                            <option value="2nde">2nde</option>
                            <option value="1ère">1ère</option>
                            <option value="Tle">Terminale</option>
                        </select>
                        
                        <select id="filterCapacite" class="form-select">
                            <option value="">🎯 Capacité</option>
                            <option value="vide">Classes vides</option>
                            <option value="faible">&lt; 50%</option>
                            <option value="moyen">50% - 80%</option>
                            <option value="plein">&gt; 80%</option>
                            <option value="complet">Complètes</option>
                        </select>
                        
                        <button onclick="reinitialiserFiltres()" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-rotate-right"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Liste des classes groupées par niveau -->
            <?php
            $classes_par_niveau = [];
            foreach ($classes as $classe) {
                $classes_par_niveau[$classe['niveau']][] = $classe;
            }
            
            $niveaux_ordre = ['6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Tle'];
            ?>

            <?php foreach ($niveaux_ordre as $niveau): ?>
                <?php if (isset($classes_par_niveau[$niveau])): ?>
                    <div class="niveau-section" data-niveau="<?= $niveau ?>">
                        <div class="niveau-header">
                            <h2>
                                <i class="fa-solid fa-layer-group"></i>
                                <?= $niveau ?>
                                <span class="badge badge-info"><?= count($classes_par_niveau[$niveau]) ?> classe(s)</span>
                            </h2>
                        </div>

                        <div class="classes-grid">
                            <?php foreach ($classes_par_niveau[$niveau] as $classe): ?>
                                <?php
                                $taux_remplissage = $classe['effectif_max'] > 0 
                                    ? ($classe['effectif_actuel'] / $classe['effectif_max']) * 100 
                                    : 0;
                                    
                                $badge_class = 'secondary';
                                if ($taux_remplissage >= 100) $badge_class = 'danger';
                                elseif ($taux_remplissage >= 80) $badge_class = 'warning';
                                elseif ($taux_remplissage >= 50) $badge_class = 'success';
                                elseif ($taux_remplissage > 0) $badge_class = 'info';
                                ?>
                                
                                <div class="classe-card" 
                                     data-niveau="<?= htmlspecialchars($classe['niveau']) ?>"
                                     data-taux="<?= round($taux_remplissage) ?>">
                                    <div class="classe-card-header">
                                        <div class="classe-title">
                                            <h3><?= htmlspecialchars($classe['niveau'] . ' ' . $classe['nom']) ?></h3>
                                            <span class="classe-badge badge-<?= $badge_class ?>">
                                                <?= $classe['effectif_actuel'] ?>/<?= $classe['effectif_max'] ?>
                                            </span>
                                        </div>
                                        <div class="classe-progress">
                                            <div class="progress-bar">
                                                <div class="progress-fill progress-<?= $badge_class ?>" 
                                                     style="width: <?= min($taux_remplissage, 100) ?>%"></div>
                                            </div>
                                            <small><?= round($taux_remplissage) ?>% remplie</small>
                                        </div>
                                    </div>

                                    <div class="classe-card-body">
                                        <div class="classe-info">
                                            <div class="info-item">
                                                <i class="fa-solid fa-chair"></i>
                                                <span>
                                                    <strong>Salle:</strong> 
                                                    <?= htmlspecialchars($classe['salle_principale'] ?? 'Non assignée') ?>
                                                </span>
                                            </div>
                                            <div class="info-item">
                                                <i class="fa-solid fa-chalkboard-user"></i>
                                                <span>
                                                    <strong>Professeurs:</strong> 
                                                    <?= $classe['nombre_professeurs'] ?? 0 ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($classe['professeurs_titulaires'])): ?>
                                                <div class="info-item">
                                                    <i class="fa-solid fa-crown"></i>
                                                    <span>
                                                        <strong>Titulaire:</strong> 
                                                        <?= htmlspecialchars($classe['professeurs_titulaires']) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="classe-card-footer">
                                        <a href="detail-classe.php?id=<?= $classe['id'] ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Détails">
                                            <i class="fa-solid fa-eye"></i> Voir
                                        </a>
                                        <a href="../../admin/eleves/"></a>
                                        <a href="../../admin/eleves/liste-eleve.php?classe_id=<?= $classe['id'] ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Élèves">
                                            <i class="fa-solid fa-users"></i> Élèves
                                        </a>
                                        <a href="emploi-du-temps.php?classe_id=<?= $classe['id'] ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Emploi du temps">
                                            <i class="fa-solid fa-calendar"></i> EDT
                                        </a>
                                        <a href="modifier-classe.php?id=<?= $classe['id'] ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Modifier">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button onclick="supprimerClasse(<?= $classe['id'] ?>, '<?= htmlspecialchars($classe['niveau'] . ' ' . $classe['nom']) ?>')" 
                                                class="btn btn-sm btn-danger" 
                                                title="Supprimer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (empty($classes)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <h3>Aucune classe enregistrée</h3>
                    <p>Commencez par créer votre première classe</p>
                    <a href="ajouter-classe.php" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-plus"></i> Créer une classe
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
            color: #111827;
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
            color: #111827;
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

        /* Niveau Section */
        .niveau-section {
            margin-bottom: 3rem;
        }

        .niveau-header {
            background: linear-gradient(135deg, var(--primary), #6366F1);
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0;
            margin-bottom: 1.5rem;
        }

        .niveau-header h2 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .niveau-header .badge {
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 0.9rem;
        }

        /* Classes Grid */
        .classes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .classe-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s;
        }

        .classe-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .classe-card-header {
            padding: 1.25rem;
            background: #F9FAFB;
            border-bottom: 2px solid #E5E7EB;
        }

        .classe-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .classe-title h3 {
            margin: 0;
            font-size: 1.25rem;
            color: #111827;
        }

        .classe-badge {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .badge-danger { background: #FEE2E2; color: var(--danger); }
        .badge-warning { background: #FEF3C7; color: #D97706; }
        .badge-success { background: #D1FAE5; color: var(--success); }
        .badge-info { background: #DBEAFE; color: var(--info); }
        .badge-secondary { background: #F3F4F6; color: var(--secondary); }

        .classe-progress {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .progress-bar {
            flex: 1;
            height: 8px;
            background: #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            transition: width 0.3s;
            border-radius: 4px;
        }

        .progress-danger { background: var(--danger); }
        .progress-warning { background: var(--warning); }
        .progress-success { background: var(--success); }
        .progress-info { background: var(--info); }
        .progress-secondary { background: var(--secondary); }

        .classe-card-body {
            padding: 1.25rem;
        }

        .classe-info {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #6B7280;
            font-size: 0.9rem;
        }

        .info-item i {
            color: var(--primary);
            width: 20px;
        }

        .classe-card-footer {
            padding: 1rem 1.25rem;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
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

        .empty-state h3 {
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #6B7280;
            margin-bottom: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .classes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Filtrage
        const searchInput = document.getElementById('searchClasses');
        const filterNiveau = document.getElementById('filterNiveau');
        const filterCapacite = document.getElementById('filterCapacite');

        function filtrerClasses() {
            const recherche = searchInput.value.toLowerCase();
            const niveauSelectionne = filterNiveau.value;
            const capaciteSelectionnee = filterCapacite.value;

            document.querySelectorAll('.classe-card').forEach(card => {
                const texte = card.textContent.toLowerCase();
                const niveau = card.dataset.niveau;
                const taux = parseInt(card.dataset.taux);

                let afficher = true;

                // Filtre recherche
                if (recherche && !texte.includes(recherche)) {
                    afficher = false;
                }

                // Filtre niveau
                if (niveauSelectionne && niveau !== niveauSelectionne) {
                    afficher = false;
                }

                // Filtre capacité
                if (capaciteSelectionnee) {
                    if (capaciteSelectionnee === 'vide' && taux > 0) afficher = false;
                    if (capaciteSelectionnee === 'faible' && (taux < 0 || taux >= 50)) afficher = false;
                    if (capaciteSelectionnee === 'moyen' && (taux < 50 || taux >= 80)) afficher = false;
                    if (capaciteSelectionnee === 'plein' && (taux < 80 || taux >= 100)) afficher = false;
                    if (capaciteSelectionnee === 'complet' && taux < 100) afficher = false;
                }

                card.style.display = afficher ? '' : 'none';
            });

            // Masquer les sections de niveau vides
            document.querySelectorAll('.niveau-section').forEach(section => {
                const cardsVisibles = section.querySelectorAll('.classe-card:not([style*="display: none"])');
                section.style.display = cardsVisibles.length > 0 ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filtrerClasses);
        filterNiveau?.addEventListener('change', filtrerClasses);
        filterCapacite?.addEventListener('change', filtrerClasses);

        function reinitialiserFiltres() {
            searchInput.value = '';
            filterNiveau.value = '';
            filterCapacite.value = '';
            filtrerClasses();
        }

        function supprimerClasse(id, nom) {
            if (confirm(`⚠️ Êtes-vous sûr de vouloir supprimer la classe "${nom}" ?\n\nCette action supprimera également :\n• Les inscriptions des élèves\n• Les enseignements associés\n• L'emploi du temps`)) {
                fetch('supprimer-classe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Classe supprimée');
                        location.reload();
                    } else {
                        alert('❌ Erreur : ' + data.message);
                    }
                });
            }
        }

        function exporterPDF() {
            window.print();
        }
    </script>

    
</body>
</html>