<?php
/**
 * Page : Emploi du Temps
 * Rôle requis : Admin/Prof
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';

$pageTitle = 'Emploi du Temps';

// Déterminer le type de vue
$classe_id = $_GET['classe_id'] ?? null;
$prof_id = $_GET['prof_id'] ?? null;

// Récupérer l'année scolaire active
$stmtAnnee = $pdo->query("SELECT id FROM annee_scolaire WHERE actif = 1 LIMIT 1");
$annee_active = $stmtAnnee->fetch(PDO::FETCH_ASSOC);
$annee_id = $annee_active['id'] ?? null;

// Configuration des horaires
$heures = [
    ['debut' => '07:30', 'fin' => '08:25'],
    ['debut' => '08:30', 'fin' => '09:25'],
    ['debut' => '09:30', 'fin' => '10:25'],
    ['debut' => '10:45', 'fin' => '11:40'],
    ['debut' => '11:45', 'fin' => '12:40'],
    ['debut' => '14:00', 'fin' => '14:55'],
    ['debut' => '15:00', 'fin' => '15:55'],
    ['debut' => '16:00', 'fin' => '16:55']
];

$jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

try {
    if ($classe_id) {
        // Emploi du temps d'une classe
        $stmtClasse = $pdo->prepare("SELECT CONCAT(niveau, ' ', nom) as nom_complet FROM classes WHERE id = ?");
        $stmtClasse->execute([$classe_id]);
        $classe = $stmtClasse->fetch(PDO::FETCH_ASSOC);
        
        $sql = "
            SELECT 
                edt.*,
                m.nom as matiere_nom,
                m.code as matiere_code,
                CONCAT(p.nom, ' ', p.prenom) as professeur_nom
            FROM emploi_du_temps edt
            JOIN enseignements e ON edt.affectation_id = e.id
            JOIN matieres m ON e.matiere_id = m.id
            JOIN professeurs prof ON e.professeur_id = prof.id
            JOIN personnes p ON prof.personne_id = p.id
            WHERE edt.classe_id = :id 
            AND edt.annee_scolaire_id = :annee_id
            AND edt.actif = 1
            AND edt.deleted_at IS NULL
            ORDER BY FIELD(edt.jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'), edt.heure_debut
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $classe_id, 'annee_id' => $annee_id]);
        
    } else if ($prof_id) {
        // Emploi du temps d'un professeur
        $stmtProf = $pdo->prepare("SELECT CONCAT(p.prenom, ' ', p.nom) as nom_complet FROM professeurs prof JOIN personnes p ON prof.personne_id = p.id WHERE prof.id = ?");
        $stmtProf->execute([$prof_id]);
        $professeur = $stmtProf->fetch(PDO::FETCH_ASSOC);
        
        $sql = "
            SELECT 
                edt.*,
                m.nom as matiere_nom,
                m.code as matiere_code,
                CONCAT(c.niveau, ' ', c.nom) as classe_nom
            FROM emploi_du_temps edt
            JOIN enseignements e ON edt.affectation_id = e.id
            JOIN matieres m ON e.matiere_id = m.id
            JOIN classes c ON edt.classe_id = c.id
            WHERE e.professeur_id = :id
            AND edt.annee_scolaire_id = :annee_id
            AND edt.actif = 1
            AND edt.deleted_at IS NULL
            ORDER BY FIELD(edt.jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'), edt.heure_debut
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $prof_id, 'annee_id' => $annee_id]);
    }
    
    $emploi_du_temps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organiser l'emploi du temps par jour et heure
    $planning = [];
    foreach ($emploi_du_temps as $cours) {
        $planning[$cours['jour']][$cours['heure_debut']] = $cours;
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    $emploi_du_temps = [];
    $planning = [];
}

require_once __DIR__ . '/../../include/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emploi du Temps - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="parent">
        <div class="div3">
            <!-- En-tête -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-calendar-days"></i> Emploi du Temps</h1>
                    <?php if ($classe_id && isset($classe)): ?>
                        <p class="text-muted">Classe : <strong><?= htmlspecialchars($classe['nom_complet']) ?></strong></p>
                    <?php elseif ($prof_id && isset($professeur)): ?>
                        <p class="text-muted">Professeur : <strong><?= htmlspecialchars($professeur['nom_complet']) ?></strong></p>
                    <?php endif; ?>
                </div>
                <div class="header-actions">
                    <button onclick="imprimerEDT()" class="btn btn-secondary">
                        <i class="fa-solid fa-print"></i> Imprimer
                    </button>
                    <button onclick="exporterPDF()" class="btn btn-danger">
                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                    </button>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="modifier-emploi-du-temps.php?<?= $classe_id ? "classe_id=$classe_id" : "prof_id=$prof_id" ?>" 
                           class="btn btn-primary">
                            <i class="fa-solid fa-pen"></i> Modifier
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count($emploi_du_temps) ?></h3>
                        <p>Total cours/semaine</p>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count($emploi_du_temps) * 0.92 ?>h</h3>
                        <p>Volume horaire</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fa-solid fa-calendar-week"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_unique(array_column($emploi_du_temps, 'jour'))) ?></h3>
                        <p>Jours de cours</p>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_unique(array_column($emploi_du_temps, 'matiere_nom'))) ?></h3>
                        <p>Matières différentes</p>
                    </div>
                </div>
            </div>

            <!-- Grille de l'emploi du temps -->
            <div class="card edt-card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-table-cells"></i> Grille Hebdomadaire</h3>
                    <div class="legend">
                        <span class="legend-item">
                            <span class="dot dot-cours"></span> Cours
                        </span>
                        <span class="legend-item">
                            <span class="dot dot-pause"></span> Pause
                        </span>
                        <span class="legend-item">
                            <span class="dot dot-libre"></span> Libre
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="edt-container">
                        <table class="edt-table">
                            <thead>
                                <tr>
                                    <th class="heure-column">Horaires</th>
                                    <?php foreach ($jours as $jour): ?>
                                        <th><?= $jour ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($heures as $index => $heure): ?>
                                    <!-- Pause de 10h25 à 10h45 -->
                                    <?php if ($index === 3): ?>
                                        <tr class="pause-row">
                                            <td class="heure-cell">
                                                <div class="heure-info">
                                                    <strong>10:25 - 10:45</strong>
                                                    <small>Récréation</small>
                                                </div>
                                            </td>
                                            <?php foreach ($jours as $jour): ?>
                                                <td class="pause-cell">
                                                    <div class="pause-indicator">
                                                        <i class="fa-solid fa-mug-hot"></i>
                                                        <span>Pause</span>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endif; ?>
                                    
                                    <!-- Pause déjeuner 12h40 à 14h00 -->
                                    <?php if ($index === 5): ?>
                                        <tr class="pause-row">
                                            <td class="heure-cell">
                                                <div class="heure-info">
                                                    <strong>12:40 - 14:00</strong>
                                                    <small>Déjeuner</small>
                                                </div>
                                            </td>
                                            <?php foreach ($jours as $jour): ?>
                                                <td class="pause-cell dejeuner">
                                                    <div class="pause-indicator">
                                                        <i class="fa-solid fa-utensils"></i>
                                                        <span>Déjeuner</span>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endif; ?>
                                    
                                    <tr>
                                        <td class="heure-cell">
                                            <div class="heure-info">
                                                <strong><?= $heure['debut'] ?> - <?= $heure['fin'] ?></strong>
                                                <small>55 min</small>
                                            </div>
                                        </td>
                                        <?php foreach ($jours as $jour): ?>
                                            <td class="cours-cell">
                                                <?php if (isset($planning[$jour][$heure['debut']])): ?>
                                                    <?php $cours = $planning[$jour][$heure['debut']]; ?>
                                                    <div class="cours-item" style="background: <?= getCouleurMatiere($cours['matiere_code']) ?>">
                                                        <div class="cours-matiere">
                                                            <strong><?= htmlspecialchars($cours['matiere_nom']) ?></strong>
                                                            <span class="cours-code"><?= htmlspecialchars($cours['matiere_code']) ?></span>
                                                        </div>
                                                        <div class="cours-info">
                                                            <?php if ($classe_id): ?>
                                                                <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($cours['professeur_nom']) ?></span>
                                                            <?php else: ?>
                                                                <span><i class="fa-solid fa-door-open"></i> <?= htmlspecialchars($cours['classe_nom']) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (!empty($cours['salle'])): ?>
                                                            <div class="cours-salle">
                                                                <i class="fa-solid fa-location-dot"></i>
                                                                <?= htmlspecialchars($cours['salle']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="cours-vide">
                                                        <i class="fa-solid fa-circle-dot"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Résumé par matière -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3><i class="fa-solid fa-chart-bar"></i> Répartition par Matière</h3>
                </div>
                <div class="card-body">
                    <div class="matieres-resume">
                        <?php
                        $matieres_count = [];
                        foreach ($emploi_du_temps as $cours) {
                            $key = $cours['matiere_nom'];
                            if (!isset($matieres_count[$key])) {
                                $matieres_count[$key] = [
                                    'nom' => $cours['matiere_nom'],
                                    'code' => $cours['matiere_code'],
                                    'count' => 0
                                ];
                            }
                            $matieres_count[$key]['count']++;
                        }
                        
                        arsort($matieres_count);
                        ?>
                        
                        <?php foreach ($matieres_count as $matiere): ?>
                            <div class="matiere-resume-item">
                                <div class="matiere-resume-header">
                                    <span class="matiere-color" style="background: <?= getCouleurMatiere($matiere['code']) ?>"></span>
                                    <strong><?= htmlspecialchars($matiere['nom']) ?></strong>
                                    <span class="matiere-badge"><?= $matiere['count'] ?>h</span>
                                </div>
                                <div class="matiere-progress-bar">
                                    <div class="matiere-progress-fill" 
                                         style="width: <?= ($matiere['count'] / count($emploi_du_temps)) * 100 ?>%; 
                                                background: <?= getCouleurMatiere($matiere['code']) ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php
    // Fonction pour attribuer une couleur selon la matière
    function getCouleurMatiere($code) {
        $couleurs = [
            'MATH' => '#4F46E5',
            'PC' => '#10B981',
            'SVT' => '#22C55E',
            'FR' => '#F59E0B',
            'ANG' => '#EF4444',
            'HG' => '#8B5CF6',
            'EPS' => '#06B6D4',
            'INFO' => '#3B82F6',
            'PHILO' => '#EC4899',
            'ECO' => '#14B8A6',
            'MLG' => '#F97316',
            'ARTS' => '#A855F7',
            'MUS' => '#F43F5E',
            'TECH' => '#6366F1'
        ];
        return $couleurs[$code] ?? '#6B7280';
    }
    ?>

    <style>
        :root {
            --primary: #4F46E5;
            --success: #10B981;
            --warning: #F59E0B;
            --info: #3B82F6;
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

        /* Card */
        .edt-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), #6366F1);
            color: white;
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            margin: 0;
            font-size: 1.25rem;
        }

        .legend {
            display: flex;
            gap: 1.5rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .dot-cours { background: var(--primary); }
        .dot-pause { background: var(--warning); }
        .dot-libre { background: #E5E7EB; }

        /* Table EDT */
        .edt-container {
            overflow-x: auto;
        }

        .edt-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .edt-table thead {
            background: #F9FAFB;
        }

        .edt-table th {
            padding: 1rem;
            text-align: center;
            font-weight: 700;
            color: #111827;
            border: 2px solid #E5E7EB;
        }

        .heure-column {
            width: 120px;
        }

        .heure-cell {
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            padding: 0.75rem;
        }

        .heure-info {
            text-align: center;
        }

        .heure-info strong {
            display: block;
            font-size: 0.95rem;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .heure-info small {
            color: #6B7280;
            font-size: 0.75rem;
        }

        .cours-cell {
            border: 2px solid #E5E7EB;
            padding: 0.5rem;
            min-height: 80px;
            vertical-align: top;
        }

        .cours-item {
            border-radius: 8px;
            padding: 0.75rem;
            color: white;
            height: 100%;
            min-height: 70px;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            transition: all 0.2s;
            cursor: pointer;
        }

        .cours-item:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .cours-matiere {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .cours-matiere strong {
            font-size: 0.95rem;
        }

        .cours-code {
            font-size: 0.75rem;
            opacity: 0.9;
            font-weight: 600;
        }

        .cours-info, .cours-salle {
            font-size: 0.8rem;
            opacity: 0.95;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .cours-vide {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 70px;
            color: #D1D5DB;
            font-size: 1.5rem;
        }

        .pause-row {
            background: #FEF3C7;
        }

        .pause-cell {
            border: 2px solid #FDE68A;
            padding: 1rem;
            text-align: center;
        }

        .pause-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            color: #D97706;
        }

        .pause-indicator i {
            font-size: 1.5rem;
        }

        .pause-cell.dejeuner {
            background: #DBEAFE;
            border-color: #BFDBFE;
        }

        .pause-cell.dejeuner .pause-indicator {
            color: #1D4ED8;
        }

        /* Résumé matières */
        .matieres-resume {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .matiere-resume-item {
            padding: 1rem;
            background: #F9FAFB;
            border-radius: 8px;
        }

        .matiere-resume-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .matiere-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        .matiere-resume-header strong {
            flex: 1;
            color: #111827;
        }

        .matiere-badge {
            background: white;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.875rem;
            color: #4F46E5;
        }

        .matiere-progress-bar {
            height: 8px;
            background: #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
        }

        .matiere-progress-fill {
            height: 100%;
            transition: width 0.3s;
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

        .btn-primary { background: var(--primary); color: white; }
        .btn-danger { background: #EF4444; color: white; }
        .btn-secondary { background: #6B7280; color: white; }

        @media print {
            .header-actions, .stats-grid, .card-header .legend {
                display: none !important;
            }
            
            .edt-table {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .legend {
                flex-direction: column;
                gap: 0.5rem;
            }

            .edt-container {
                overflow-x: scroll;
            }
        }
    </style>

    <script>
        function imprimerEDT() {
            window.print();
        }

        function exporterPDF() {
            alert('Fonctionnalité d\'export PDF à implémenter avec une bibliothèque comme jsPDF ou en backend avec TCPDF/mPDF');
        }

        // Tooltip sur les cours
        document.querySelectorAll('.cours-item').forEach(item => {
            item.addEventListener('click', function() {
                const details = this.textContent;
                alert('Détails du cours:\n\n' + details);
            });
        });
    </script>

    
</body>
</html>