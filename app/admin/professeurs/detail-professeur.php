<?php
/**
 * Page : Détail d'un Professeur
 * Rôle requis : Admin
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

$pageTitle = 'Détail Professeur';
$professeur_id = $_GET['id'] ?? null;

if (!$professeur_id) {
    $_SESSION['error_message'] = "Identifiant professeur manquant";
    header('Location: liste-professeurs.php');
    exit;
}

// Récupérer les informations complètes du professeur
try {
    // Informations de base
    $sql = "SELECT * FROM v_professeurs_complet WHERE professeur_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $professeur_id]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prof) {
        $_SESSION['error_message'] = "Professeur introuvable";
        header('Location: liste-professeurs.php');
        exit;
    }
    
    // Récupérer l'année scolaire active
    $stmtAnnee = $pdo->query("SELECT id FROM annee_scolaire WHERE actif = 1 LIMIT 1");
    $annee_active = $stmtAnnee->fetch(PDO::FETCH_ASSOC);
    $annee_id = $annee_active['id'] ?? null;
    
    // Enseignements du professeur
    $sqlEnseignements = "
        SELECT * FROM v_enseignements_professeurs 
        WHERE professeur_id = :id 
        AND annee_scolaire_id = :annee_id
        ORDER BY classe_nom, matiere_nom
    ";
    $stmtEns = $pdo->prepare($sqlEnseignements);
    $stmtEns->execute(['id' => $professeur_id, 'annee_id' => $annee_id]);
    $enseignements = $stmtEns->fetchAll(PDO::FETCH_ASSOC);
    
    // Statistiques
    $sqlStats = "
        SELECT 
            COUNT(DISTINCT e.classe_id) as nb_classes,
            COUNT(DISTINCT e.matiere_id) as nb_matieres,
            COALESCE(SUM(e.volume_horaire_hebdo), 0) as total_heures,
            COUNT(DISTINCT CASE WHEN e.est_titulaire = 1 THEN e.classe_id END) as nb_classes_titulaire
        FROM enseignements e
        WHERE e.professeur_id = :id
        AND e.annee_scolaire_id = :annee_id
        AND e.deleted_at IS NULL
    ";
    $stmtStats = $pdo->prepare($sqlStats);
    $stmtStats->execute(['id' => $professeur_id, 'annee_id' => $annee_id]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header('Location: liste-professeurs.php');
    exit;
}

require_once __DIR__ . '/../../include/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']) ?> - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="parent">
        <div class="div3">
            <!-- En-tête avec photo et nom -->
            <div class="profile-header">
                <div class="profile-photo-section">
                    <div class="profile-photo">
                        <?php if (!empty($prof['photo_path']) && file_exists($prof['photo_path'])): ?>
                            <img src="<?= htmlspecialchars($prof['photo_path']) ?>" alt="Photo">
                        <?php else: ?>
                            <div class="photo-placeholder">
                                <?= strtoupper(substr($prof['prenom'], 0, 1) . substr($prof['nom'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h1><?= htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']) ?></h1>
                        <p class="profile-subtitle"><?= htmlspecialchars($prof['specialite'] ?? 'Spécialité non renseignée') ?></p>
                        <div class="profile-badges">
                            <span class="badge badge-primary">
                                <i class="fa-solid fa-id-card"></i> <?= htmlspecialchars($prof['matricule']) ?>
                            </span>
                            <?php
                            $statut = $prof['statut_compte'] ?? 'inconnu';
                            $badges = [
                                'actif' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Actif'],
                                'en_attente' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'En attente'],
                                'inactif' => ['class' => 'secondary', 'icon' => 'pause-circle', 'text' => 'Inactif'],
                                'suspendu' => ['class' => 'danger', 'icon' => 'ban', 'text' => 'Suspendu']
                            ];
                            $badgeInfo = $badges[$statut] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => 'Inconnu'];
                            ?>
                            <span class="badge badge-<?= $badgeInfo['class'] ?>">
                                <i class="fa-solid fa-<?= $badgeInfo['icon'] ?>"></i> <?= $badgeInfo['text'] ?>
                            </span>
                            <span class="badge badge-info">
                                <i class="fa-solid fa-briefcase"></i> <?= ucfirst($prof['statut_emploi'] ?? 'N/A') ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="liste-professeurs.php" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Retour
                    </a>
                    <a href="modifier-professeur.php?id=<?= $professeur_id ?>" class="btn btn-warning">
                        <i class="fa-solid fa-pen"></i> Modifier
                    </a>
                    <a href="emploi-du-temps-prof.php?id=<?= $professeur_id ?>" class="btn btn-success">
                        <i class="fa-solid fa-calendar"></i> Emploi du temps
                    </a>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['nb_classes'] ?? 0 ?></h3>
                        <p>Classes assignées</p>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['nb_matieres'] ?? 0 ?></h3>
                        <p>Matières enseignées</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['total_heures'] ?? 0, 1) ?>h</h3>
                        <p>Volume horaire hebdo</p>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['nb_classes_titulaire'] ?? 0 ?></h3>
                        <p>Classe(s) dont titulaire</p>
                    </div>
                </div>
            </div>

            <!-- Informations détaillées -->
            <div class="details-grid">
                
                <!-- Informations personnelles -->
                <div class="detail-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-user"></i> Informations Personnelles</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-venus-mars"></i> Sexe</span>
                            <span class="info-value"><?= $prof['sexe'] === 'M' ? 'Masculin' : 'Féminin' ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-calendar"></i> Date de naissance</span>
                            <span class="info-value">
                                <?= date('d/m/Y', strtotime($prof['date_naissance'])) ?>
                                <small>(<?= floor((time() - strtotime($prof['date_naissance'])) / (365*24*60*60)) ?> ans)</small>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-map-marker-alt"></i> Lieu de naissance</span>
                            <span class="info-value"><?= htmlspecialchars($prof['lieu_naissance'] ?? 'Non renseigné') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-flag"></i> Nationalité</span>
                            <span class="info-value"><?= htmlspecialchars($prof['nationalite'] ?? 'Non renseigné') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-heart"></i> Situation familiale</span>
                            <span class="info-value"><?= htmlspecialchars($prof['situation_familiale'] ?? 'Non renseigné') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="detail-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-address-book"></i> Coordonnées</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-phone"></i> Téléphone</span>
                            <span class="info-value">
                                <a href="tel:<?= htmlspecialchars($prof['telephone']) ?>">
                                    <?= htmlspecialchars($prof['telephone'] ?? 'Non renseigné') ?>
                                </a>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-envelope"></i> Email</span>
                            <span class="info-value">
                                <a href="mailto:<?= htmlspecialchars($prof['email']) ?>">
                                    <?= htmlspecialchars($prof['email']) ?>
                                </a>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-home"></i> Adresse</span>
                            <span class="info-value"><?= nl2br(htmlspecialchars($prof['adresse'] ?? 'Non renseignée')) ?></span>
                        </div>
                        <hr>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-user-shield"></i> Contact d'urgence</span>
                            <span class="info-value">
                                <?php if (!empty($prof['personne_urgence_nom'])): ?>
                                    <strong><?= htmlspecialchars($prof['personne_urgence_nom']) ?></strong><br>
                                    <a href="tel:<?= htmlspecialchars($prof['personne_urgence_tel']) ?>">
                                        <?= htmlspecialchars($prof['personne_urgence_tel']) ?>
                                    </a>
                                <?php else: ?>
                                    Non renseigné
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Qualifications -->
                <div class="detail-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-graduation-cap"></i> Qualifications</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-book-open"></i> Spécialité</span>
                            <span class="info-value">
                                <span class="badge badge-purple"><?= htmlspecialchars($prof['specialite'] ?? 'Non renseignée') ?></span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-certificate"></i> Diplôme principal</span>
                            <span class="info-value"><?= htmlspecialchars($prof['diplome_principal'] ?? 'Non renseigné') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-award"></i> Autres diplômes</span>
                            <span class="info-value"><?= nl2br(htmlspecialchars($prof['autres_diplomes'] ?? 'Aucun')) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-briefcase"></i> Expérience</span>
                            <span class="info-value"><?= $prof['experience_annees'] ?? 0 ?> an(s)</span>
                        </div>
                    </div>
                </div>

                <!-- Informations administratives -->
                <div class="detail-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-file-contract"></i> Informations Administratives</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-calendar-check"></i> Date de recrutement</span>
                            <span class="info-value">
                                <?php if (!empty($prof['date_recrutement'])): ?>
                                    <?= date('d/m/Y', strtotime($prof['date_recrutement'])) ?>
                                    <?php
                                    $anciennete = floor((time() - strtotime($prof['date_recrutement'])) / (365*24*60*60));
                                    $mois = floor(((time() - strtotime($prof['date_recrutement'])) % (365*24*60*60)) / (30*24*60*60));
                                    ?>
                                    <small>(<?= $anciennete ?> an(s) <?= $mois ?> mois)</small>
                                <?php else: ?>
                                    Non renseignée
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-id-badge"></i> Type d'emploi</span>
                            <span class="info-value">
                                <span class="badge badge-<?= $prof['statut_emploi'] === 'permanent' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($prof['statut_emploi'] ?? 'N/A') ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-clock"></i> Dernière connexion</span>
                            <span class="info-value">
                                <?= !empty($prof['last_login']) ? date('d/m/Y à H:i', strtotime($prof['last_login'])) : 'Jamais' ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-calendar-plus"></i> Créé le</span>
                            <span class="info-value"><?= date('d/m/Y à H:i', strtotime($prof['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enseignements -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3><i class="fa-solid fa-chalkboard-teacher"></i> Enseignements pour l'année en cours</h3>
                    <a href="assigner-cours.php?prof_id=<?= $professeur_id ?>" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Assigner un cours
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($enseignements)): ?>
                        <div class="empty-state-small">
                            <i class="fa-solid fa-inbox"></i>
                            <p>Aucun enseignement assigné pour cette année</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Classe</th>
                                        <th>Matière</th>
                                        <th>Coefficient</th>
                                        <th>Volume horaire</th>
                                        <th>Professeur titulaire</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enseignements as $ens): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-info"><?= htmlspecialchars($ens['classe_nom']) ?></span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($ens['matiere_nom']) ?></strong>
                                                <small class="text-muted">(<?= htmlspecialchars($ens['matiere_code']) ?>)</small>
                                            </td>
                                            <td><?= $ens['coefficient'] ?></td>
                                            <td><?= number_format($ens['volume_horaire_hebdo'], 1) ?>h/semaine</td>
                                            <td class="text-center">
                                                <?php if ($ens['est_titulaire']): ?>
                                                    <span class="badge badge-success"><i class="fa-solid fa-crown"></i> Oui</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Non</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button onclick="retirerEnseignement(<?= $ens['enseignement_id'] ?>)" 
                                                        class="btn btn-sm btn-danger">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
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

        .profile-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .profile-photo-section {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), #6366F1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
        }

        .profile-info h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            color: #111827;
        }

        .profile-subtitle {
            color: #6B7280;
            margin: 0 0 1rem 0;
            font-size: 1.1rem;
        }

        .profile-badges {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .profile-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* Stats Grid */
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

        /* Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .detail-card .card-header {
            background: linear-gradient(135deg, var(--primary), #6366F1);
            color: white;
            padding: 1rem 1.5rem;
        }

        .detail-card .card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-card .card-body {
            padding: 1.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.75rem 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6B7280;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 0 0 40%;
        }

        .info-value {
            text-align: right;
            flex: 1;
            color: #111827;
        }

        .info-value a {
            color: var(--primary);
            text-decoration: none;
        }

        .info-value a:hover {
            text-decoration: underline;
        }

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
        .badge-warning { background: #FEF3C7; color: #D97706; }
        .badge-danger { background: #FEE2E2; color: var(--danger); }
        .badge-info { background: #DBEAFE; color: var(--info); }
        .badge-purple { background: #F3E8FF; color: var(--purple); }
        .badge-secondary { background: #F3F4F6; color: var(--secondary); }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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

        .card-body {
            padding: 1.5rem;
        }

        .empty-state-small {
            text-align: center;
            padding: 2rem;
            color: #6B7280;
        }

        .empty-state-small i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                gap: 1.5rem;
            }

            .profile-photo-section {
                flex-direction: column;
                text-align: center;
            }

            .profile-actions {
                width: 100%;
            }

            .profile-actions .btn {
                flex: 1;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .info-row {
                flex-direction: column;
                gap: 0.5rem;
            }

            .info-label, .info-value {
                text-align: left;
            }
        }
    </style>

    <script>
        function retirerEnseignement(enseignementId) {
            if (confirm('Êtes-vous sûr de vouloir retirer cet enseignement ?')) {
                fetch('retirer-enseignement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ enseignement_id: enseignementId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Enseignement retiré');
                        location.reload();
                    } else {
                        alert('❌ Erreur : ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('❌ Une erreur est survenue');
                });
            }
        }
    </script>
</body>
</html>