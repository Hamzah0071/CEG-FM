<?php
/**
 * Page : Assigner/Changer la classe d'un élève
 * Rôle requis : Admin
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

$pageTitle = 'Assigner une Classe';
$eleve_id = $_GET['eleve_id'] ?? null;

if (!$eleve_id) {
    $_SESSION['error_message'] = "Identifiant élève manquant";
    header('Location: liste-eleve.php');
    exit;
}

// Récupérer l'année scolaire active
$stmtAnnee = $pdo->query("SELECT * FROM annee_scolaire WHERE actif = 1 LIMIT 1");
$annee_active = $stmtAnnee->fetch(PDO::FETCH_ASSOC);
$annee_id = $annee_active['id'] ?? null;

// Récupérer les informations de l'élève
try {
    $sql = "
        SELECT 
            e.id as eleve_id,
            e.matricule,
            CONCAT(p.nom, ' ', p.prenom) as nom_complet,
            p.nom,
            p.prenom,
            p.sexe,
            p.date_naissance,
            i.id as inscription_id,
            i.classe_id as classe_actuelle_id,
            CONCAT(c.niveau, ' ', c.nom) as classe_actuelle,
            i.statut as statut_actuel,
            a.libelle as annee_scolaire
        FROM eleves e
        JOIN personnes p ON e.personne_id = p.id
        LEFT JOIN inscriptions i ON e.id = i.eleve_id 
            AND i.annee_scolaire_id = :annee_id
            AND i.deleted_at IS NULL
        LEFT JOIN classes c ON i.classe_id = c.id
        LEFT JOIN annee_scolaire a ON i.annee_scolaire_id = a.id
        WHERE e.id = :eleve_id
        AND e.deleted_at IS NULL
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['eleve_id' => $eleve_id, 'annee_id' => $annee_id]);
    $eleve = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$eleve) {
        $_SESSION['error_message'] = "Élève introuvable";
        header('Location: liste-eleves.php');
        exit;
    }
    
    // Récupérer toutes les classes disponibles
    $stmtClasses = $pdo->prepare("
        SELECT 
            c.id,
            c.nom,
            c.niveau,
            c.effectif_max,
            c.salle_principale,
            CONCAT(c.niveau, ' ', c.nom) as nom_complet,
            COUNT(DISTINCT i.id) as effectif_actuel
        FROM classes c
        LEFT JOIN inscriptions i ON c.id = i.classe_id 
            AND i.statut = 'actif'
            AND i.deleted_at IS NULL
            AND i.annee_scolaire_id = :annee_id
        WHERE c.annee_scolaire_id = :annee_id
        AND c.deleted_at IS NULL
        GROUP BY c.id
        ORDER BY 
            FIELD(c.niveau, '6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Tle'),
            c.nom
    ");
    $stmtClasses->execute(['annee_id' => $annee_id]);
    $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
    
    // Grouper par niveau
    $classes_par_niveau = [];
    foreach ($classes as $classe) {
        $classes_par_niveau[$classe['niveau']][] = $classe;
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header('Location: liste-eleves.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $classe_id = $_POST['classe_id'];
        $statut = $_POST['statut'] ?? 'actif';
        $action = $_POST['action']; // 'create' ou 'update'
        
        if ($action === 'update' && !empty($eleve['inscription_id'])) {
            // Mise à jour de l'inscription existante
            $stmtUpdate = $pdo->prepare("
                UPDATE inscriptions 
                SET classe_id = :classe_id, 
                    statut = :statut,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :inscription_id
            ");
            
            $stmtUpdate->execute([
                'classe_id' => $classe_id,
                'statut' => $statut,
                'inscription_id' => $eleve['inscription_id']
            ]);
            
            $message = "Classe mise à jour avec succès";
            
        } else {
            // Création d'une nouvelle inscription
            $stmtCreate = $pdo->prepare("
                INSERT INTO inscriptions (
                    eleve_id, 
                    classe_id, 
                    annee_scolaire_id, 
                    date_inscription, 
                    statut
                )
                VALUES (:eleve_id, :classe_id, :annee_id, CURRENT_DATE, :statut)
            ");
            
            $stmtCreate->execute([
                'eleve_id' => $eleve_id,
                'classe_id' => $classe_id,
                'annee_id' => $annee_id,
                'statut' => $statut
            ]);
            
            $message = "Classe assignée avec succès";
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = $message;
        header('Location: liste-eleves.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigner une Classe - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="parent">
        <div class="div3">
            <!-- En-tête -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-door-open"></i> Assigner une Classe</h1>
                    <p class="text-muted">Année scolaire : <?= htmlspecialchars($annee_active['libelle'] ?? 'N/A') ?></p>
                </div>
                <a href="liste-eleves.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Retour à la liste
                </a>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Informations élève -->
            <div class="card info-card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-user-graduate"></i> Informations de l'Élève</h3>
                </div>
                <div class="card-body">
                    <div class="eleve-info-grid">
                        <div class="info-item">
                            <div class="eleve-avatar">
                                <?= strtoupper(substr($eleve['prenom'], 0, 1) . substr($eleve['nom'], 0, 1)) ?>
                            </div>
                            <div>
                                <h3><?= htmlspecialchars($eleve['nom_complet']) ?></h3>
                                <span class="badge badge-primary">
                                    <?= htmlspecialchars($eleve['matricule']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-details">
                            <div class="detail-row">
                                <span class="label"><i class="fa-solid fa-venus-mars"></i> Sexe</span>
                                <span class="value"><?= $eleve['sexe'] === 'M' ? 'Masculin' : 'Féminin' ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label"><i class="fa-solid fa-calendar"></i> Âge</span>
                                <span class="value">
                                    <?= floor((time() - strtotime($eleve['date_naissance'])) / (365*24*60*60)) ?> ans
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="label"><i class="fa-solid fa-door-open"></i> Classe actuelle</span>
                                <span class="value">
                                    <?php if (!empty($eleve['classe_actuelle'])): ?>
                                        <span class="badge badge-info"><?= htmlspecialchars($eleve['classe_actuelle']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Aucune classe assignée</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="label"><i class="fa-solid fa-signal"></i> Statut</span>
                                <span class="value">
                                    <span class="badge badge-<?= $eleve['statut_actuel'] === 'actif' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($eleve['statut_actuel'] ?? 'Non inscrit') ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire d'assignation -->
            <form method="POST" id="formAssignation">
                <input type="hidden" name="action" value="<?= !empty($eleve['inscription_id']) ? 'update' : 'create' ?>">
                
                <!-- Sélection du statut -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-toggle-on"></i> Statut de l'Inscription</h3>
                    </div>
                    <div class="card-body">
                        <div class="statut-options">
                            <label class="statut-option">
                                <input type="radio" name="statut" value="actif" 
                                       <?= ($eleve['statut_actuel'] ?? 'actif') === 'actif' ? 'checked' : '' ?> required>
                                <div class="statut-card statut-actif">
                                    <i class="fa-solid fa-check-circle"></i>
                                    <strong>Actif</strong>
                                    <small>Élève en cours de scolarité</small>
                                </div>
                            </label>
                            
                            <label class="statut-option">
                                <input type="radio" name="statut" value="redouble" 
                                       <?= ($eleve['statut_actuel'] ?? '') === 'redouble' ? 'checked' : '' ?>>
                                <div class="statut-card statut-redouble">
                                    <i class="fa-solid fa-rotate"></i>
                                    <strong>Redoublant</strong>
                                    <small>Élève qui redouble</small>
                                </div>
                            </label>
                            
                            <label class="statut-option">
                                <input type="radio" name="statut" value="transfere" 
                                       <?= ($eleve['statut_actuel'] ?? '') === 'transfere' ? 'checked' : '' ?>>
                                <div class="statut-card statut-transfere">
                                    <i class="fa-solid fa-arrow-right"></i>
                                    <strong>Transféré</strong>
                                    <small>Provient d'un autre établissement</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Sélection de la classe -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-door-open"></i> Choisir une Classe</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($classes)): ?>
                            <div class="alert alert-warning">
                                <i class="fa-solid fa-exclamation-triangle"></i>
                                Aucune classe disponible pour cette année scolaire.
                            </div>
                        <?php else: ?>
                            <?php foreach ($classes_par_niveau as $niveau => $classes_niveau): ?>
                                <div class="niveau-section">
                                    <h4 class="niveau-title">
                                        <i class="fa-solid fa-layer-group"></i> <?= htmlspecialchars($niveau) ?>
                                    </h4>
                                    
                                    <div class="classes-selection-grid">
                                        <?php foreach ($classes_niveau as $classe): ?>
                                            <?php
                                            $taux_remplissage = $classe['effectif_max'] > 0 
                                                ? ($classe['effectif_actuel'] / $classe['effectif_max']) * 100 
                                                : 0;
                                            
                                            $est_complete = $taux_remplissage >= 100;
                                            $est_actuelle = $classe['id'] == $eleve['classe_actuelle_id'];
                                            ?>
                                            
                                            <label class="classe-selection-card <?= $est_complete ? 'disabled' : '' ?> <?= $est_actuelle ? 'current' : '' ?>">
                                                <input type="radio" 
                                                       name="classe_id" 
                                                       value="<?= $classe['id'] ?>"
                                                       <?= $est_actuelle ? 'checked' : '' ?>
                                                       <?= $est_complete && !$est_actuelle ? 'disabled' : '' ?>
                                                       required>
                                                
                                                <div class="classe-card-content">
                                                    <div class="classe-header">
                                                        <h4><?= htmlspecialchars($classe['nom_complet']) ?></h4>
                                                        <?php if ($est_actuelle): ?>
                                                            <span class="badge badge-success">Classe actuelle</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="classe-details">
                                                        <div class="detail">
                                                            <i class="fa-solid fa-users"></i>
                                                            <span><?= $classe['effectif_actuel'] ?> / <?= $classe['effectif_max'] ?> élèves</span>
                                                        </div>
                                                        
                                                        <?php if (!empty($classe['salle_principale'])): ?>
                                                            <div class="detail">
                                                                <i class="fa-solid fa-location-dot"></i>
                                                                <span>Salle <?= htmlspecialchars($classe['salle_principale']) ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="progress-bar">
                                                        <div class="progress-fill" 
                                                             style="width: <?= min($taux_remplissage, 100) ?>%; 
                                                                    background: <?= $est_complete ? '#EF4444' : '#10B981' ?>">
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($est_complete && !$est_actuelle): ?>
                                                        <div class="classe-status complete">
                                                            <i class="fa-solid fa-ban"></i> Classe complète
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="classe-status available">
                                                            <i class="fa-solid fa-check"></i> 
                                                            <?= $classe['effectif_max'] - $classe['effectif_actuel'] ?> place(s) disponible(s)
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="form-actions">
                    <a href="liste-eleves.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fa-solid fa-times"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-check"></i>
                        <?= !empty($eleve['inscription_id']) ? 'Mettre à jour' : 'Assigner' ?> la classe
                    </button>
                </div>
            </form>

        </div>
    </div>

    <style>
        :root {
            --primary: #4F46E5;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
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

        /* Card Élève */
        .info-card {
            margin-bottom: 2rem;
        }

        .eleve-info-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 2rem;
            align-items: center;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .eleve-avatar {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), #6366F1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
        }

        .info-item h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
        }

        .info-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .detail-row {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-row .label {
            color: #6B7280;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-row .value {
            font-weight: 600;
            color: #111827;
        }

        /* Statut Options */
        .statut-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .statut-option {
            cursor: pointer;
        }

        .statut-option input[type="radio"] {
            display: none;
        }

        .statut-card {
            padding: 1.5rem;
            border: 3px solid #E5E7EB;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s;
        }

        .statut-card i {
            font-size: 2rem;
            display: block;
            margin-bottom: 0.75rem;
        }

        .statut-card strong {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .statut-card small {
            color: #6B7280;
        }

        .statut-option input[type="radio"]:checked + .statut-card {
            border-color: var(--primary);
            background: #EEF2FF;
        }

        .statut-actif i { color: var(--success); }
        .statut-redouble i { color: var(--warning); }
        .statut-transfere i { color: var(--info); }

        /* Niveau Section */
        .niveau-section {
            margin-bottom: 2rem;
        }

        .niveau-title {
            font-size: 1.25rem;
            color: #111827;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #E5E7EB;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Classes Grid */
        .classes-selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }

        .classe-selection-card {
            cursor: pointer;
        }

        .classe-selection-card input[type="radio"] {
            display: none;
        }

        .classe-card-content {
            padding: 1.25rem;
            border: 3px solid #E5E7EB;
            border-radius: 12px;
            transition: all 0.3s;
            background: white;
        }

        .classe-selection-card:not(.disabled):hover .classe-card-content {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        .classe-selection-card input[type="radio"]:checked + .classe-card-content {
            border-color: var(--primary);
            background: #EEF2FF;
        }

        .classe-selection-card.current .classe-card-content {
            border-color: var(--success);
            background: #ECFDF5;
        }

        .classe-selection-card.disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .classe-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .classe-header h4 {
            margin: 0;
            font-size: 1.1rem;
        }

        .classe-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .classe-details .detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6B7280;
            font-size: 0.875rem;
        }

        .classe-details i {
            color: var(--primary);
            width: 16px;
        }

        .progress-bar {
            height: 8px;
            background: #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .progress-fill {
            height: 100%;
            transition: width 0.3s;
        }

        .classe-status {
            text-align: center;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .classe-status.available {
            background: #D1FAE5;
            color: var(--success);
        }

        .classe-status.complete {
            background: #FEE2E2;
            color: var(--danger);
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

        .badge-primary { background: #EEF2FF; color: var(--primary); }
        .badge-success { background: #D1FAE5; color: var(--success); }
        .badge-info { background: #DBEAFE; color: var(--info); }
        .badge-warning { background: #FEF3C7; color: #D97706; }

        /* Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #E5E7EB;
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-lg {
            padding: 1rem 2.5rem;
            font-size: 1.05rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #6366F1);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .btn-outline-secondary {
            background: white;
            color: #6B7280;
            border: 2px solid #E5E7EB;
        }

        .btn-outline-secondary:hover {
            background: #F9FAFB;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), #6366F1);
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0;
        }

        .card-header h3 {
            margin: 0;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-danger {
            background: #FEE2E2;
            color: #991B1B;
            border-left: 4px solid var(--danger);
        }

        .alert-warning {
            background: #FEF3C7;
            color: #92400E;
            border-left: 4px solid var(--warning);
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .eleve-info-grid {
                grid-template-columns: 1fr;
            }

            .info-details {
                grid-template-columns: 1fr;
            }

            .classes-selection-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

   <script>
    // Validation avant soumission
    document.getElementById('formAssignation').addEventListener('submit', function(e) {
        const selectedClasse = document.querySelector('input[name="classe_id"]:checked');
        const selectedStatut = document.querySelector('input[name="statut"]:checked');
        
        if (!selectedClasse) {
            e.preventDefault();
            alert('⚠️ Veuillez sélectionner une classe');
            return false;
        }
        
        if (!selectedStatut) {
            e.preventDefault();
            alert('⚠️ Veuillez sélectionner un statut');
            return false;
        }
        
        // Récupération du nom de la classe et vérification si c'est la classe actuelle
        const classeCard = selectedClasse.closest('.classe-selection-card');
        const classeNom = classeCard.querySelector('h4').textContent.trim();
        const isCurrent = classeCard.classList.contains('current');
        
        // Demande de confirmation seulement si ce n'est PAS la classe actuelle
        if (!isCurrent) {
            const confirmation = window.confirm(
                `Confirmer l'assignation à la classe :\n${classeNom} ?`
            );
            
            if (!confirmation) {
                e.preventDefault(); // Annule la soumission si l'utilisateur clique sur "Annuler"
                return false;
            }
        }
        
        // Si tout est OK (classe et statut sélectionnés + confirmation si nécessaire), le formulaire se soumet normalement
    });
</script>

