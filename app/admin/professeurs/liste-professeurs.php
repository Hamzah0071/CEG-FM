<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Professeurs - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="parent">
        <?php
        /**
         * Page : Liste des Professeurs
         * Rôle requis : Admin
         */

        // Configuration et protection
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../include/auth_check.php';
        require_role('admin');

        $pageTitle = 'Liste des Professeurs';

        // Récupérer les professeurs avec toutes leurs informations
        try {
            $sql = "
                SELECT 
                    prof.id as professeur_id,
                    prof.matricule,
                    p.nom,
                    p.prenom,
                    p.date_naissance,
                    p.sexe,
                    p.telephone,
                    p.adresse,
                    u.email,
                    prof.specialite,
                    prof.date_recrutement,
                    u.statut as statut_compte,
                    prof.created_at,
                    GROUP_CONCAT(DISTINCT CONCAT(c.niveau, ' ', c.nom) SEPARATOR ', ') as classes,
                    GROUP_CONCAT(DISTINCT m.nom SEPARATOR ', ') as matieres,
                    COUNT(DISTINCT c.id) as nombre_classes
                FROM professeurs prof
                JOIN personnes p ON prof.personne_id = p.id
                JOIN utilisateurs u ON prof.utilisateur_id = u.id
                LEFT JOIN enseignements e ON prof.id = e.professeur_id
                LEFT JOIN classes c ON e.classe_id = c.id
                LEFT JOIN matieres m ON e.matiere_id = m.id
                WHERE prof.deleted_at IS NULL
                GROUP BY prof.id, prof.matricule, p.nom, p.prenom, p.date_naissance, 
                         p.sexe, p.telephone, p.adresse, u.email, prof.specialite, 
                         prof.date_recrutement, u.statut, prof.created_at
                ORDER BY p.nom, p.prenom
            ";
            
            $stmt = $pdo->query($sql);
            $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Erreur lors du chargement des professeurs : " . $e->getMessage();
            $professeurs = [];
        }

        // Inclure le header
        require_once __DIR__ . '/../../include/header.php';
        ?>
        
        <div class="div3">
            <!-- En-tête de la page -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-chalkboard-user"></i> Gestion des Professeurs</h1>
                    <p class="text-muted">Gérez et consultez l'ensemble du corps enseignant</p>
                </div>
                <div>
                    <a href="<?= APP_URL ?>admin/professeurs/recrutement.php" class="btn btn-primary">
                        <i class="fa-solid fa-user-plus"></i> Recruter un professeur
                    </a>
                    <button onclick="exporterCSV()" class="btn btn-success">
                        <i class="fa-solid fa-file-excel"></i> Exporter CSV
                    </button>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa-solid fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count($professeurs) ?></h3>
                        <p>Total Professeurs</p>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($professeurs, fn($p) => ($p['statut_compte'] ?? '') === 'actif')) ?></h3>
                        <p>Professeurs Actifs</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($professeurs, fn($p) => ($p['statut_compte'] ?? '') === 'en_attente')) ?></h3>
                        <p>En Attente</p>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($professeurs, fn($p) => !empty($p['classes']))) ?></h3>
                        <p>Avec Classes Assignées</p>
                    </div>
                </div>
            </div>

            <!-- Filtres et recherche -->
            <div class="card filter-card">
                <div class="card-body">
                    <div class="filter-grid">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" 
                                   id="searchProfesseurs" 
                                   class="form-control" 
                                   placeholder="Rechercher par nom, matricule, spécialité...">
                        </div>
                        
                        <select id="filterSpecialite" class="form-select">
                            <option value="">
                                <i class="fa-solid fa-book"></i>
                             Toutes les spécialités
                            </option>
                            <?php
                            $specialites = array_unique(array_filter(array_column($professeurs, 'specialite')));
                            sort($specialites);
                            foreach ($specialites as $spec) {
                                echo "<option value='" . htmlspecialchars($spec) . "'>" . htmlspecialchars($spec) . "</option>";
                            }
                            ?>
                        </select>
                        
                        <select id="filterStatut" class="form-select">
                            <option value="">
                                <i class="fa-solid fa-book"></i>
                                Tous les statuts
                            </option>
                            <option value="actif">
                                <i class="fa-regular fa-circle-check"></i>
                                Actif
                            </option>
                            <option value="en_attente">
                                <i class="fa-solid fa-hourglass-end"></i>
                                 En attente
                                </option>
                            <option value="inactif">
                                <i class="fa-regular fa-circle"></i>
                                 Inactif
                            </option>
                            <option value="suspendu">
                                <i class="fa-solid fa-ban">

                                </i>Suspendu
                            </option>
                        </select>
                        
                        <button onclick="reinitialiserFiltres()" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-rotate-right"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tableau des professeurs -->
            <div class="card table-card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-table"></i> Liste Complète des Professeurs</h3>
                    <div class="table-info">
                        <span id="countResults"><?= count($professeurs) ?> professeur(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($professeurs)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fa-solid fa-users-slash"></i>
                            </div>
                            <h3>Aucun professeur enregistré</h3>
                            <p>Commencez par recruter votre premier professeur</p>
                            <a href="<?= APP_URL ?>admin/professeurs/recrutement.php" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-user-plus"></i> Recruter un professeur
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-modern">
                                <thead>
                                    <tr>
                                        <th><i class="fa-solid fa-hashtag"></i> Matricule</th>
                                        <th><i class="fa-solid fa-user"></i> Identité</th>
                                        <th class="text-center"><i class="fa-solid fa-venus-mars"></i> Sexe</th>
                                        <th><i class="fa-solid fa-address-book"></i> Contact</th>
                                        <th><i class="fa-solid fa-graduation-cap"></i> Spécialité</th>
                                        <th><i class="fa-solid fa-door-open"></i> Classes</th>
                                        <th><i class="fa-solid fa-book"></i> Matières</th>
                                        <th><i class="fa-solid fa-calendar-check"></i> Recrutement</th>
                                        <th class="text-center"><i class="fa-solid fa-signal"></i> Statut</th>
                                        <th class="text-center"><i class="fa-solid fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="professeursTableBody">
                                    <?php foreach ($professeurs as $prof): ?>
                                        <tr data-specialite="<?= htmlspecialchars($prof['specialite'] ?? '') ?>" 
                                            data-statut="<?= htmlspecialchars($prof['statut_compte'] ?? '') ?>"
                                            class="table-row-hover">
                                            
                                            <!-- Matricule -->
                                            <td>
                                                <span class="badge badge-matricule">
                                                    <?= htmlspecialchars($prof['matricule'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            
                                            <!-- Identité -->
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($prof['prenom'], 0, 1) . substr($prof['nom'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong class="user-name"><?= htmlspecialchars($prof['nom']) ?></strong>
                                                        <small class="user-subtext"><?= htmlspecialchars($prof['prenom']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Sexe -->
                                            <td class="text-center">
                                                <?php if ($prof['sexe'] === 'M'): ?>
                                                    <span class="badge badge-male">
                                                        <i class="fa-solid fa-mars"></i> M
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-female">
                                                        <i class="fa-solid fa-venus"></i> F
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- Contact -->
                                            <td>
                                                <div class="contact-info">
                                                    <?php if (!empty($prof['telephone'])): ?>
                                                        <div class="contact-item">
                                                            <i class="fa-solid fa-phone"></i>
                                                            <a href="tel:<?= htmlspecialchars($prof['telephone']) ?>">
                                                                <?= htmlspecialchars($prof['telephone']) ?>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($prof['email'])): ?>
                                                        <div class="contact-item">
                                                            <i class="fa-solid fa-envelope"></i>
                                                            <a href="mailto:<?= htmlspecialchars($prof['email']) ?>">
                                                                <?= htmlspecialchars($prof['email']) ?>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            
                                            <!-- Spécialité -->
                                            <td>
                                                <?php if (!empty($prof['specialite'])): ?>
                                                    <span class="badge badge-specialite">
                                                        <?= htmlspecialchars($prof['specialite']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted-small">Non renseigné</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- Classes -->
                                            <td>
                                                <?php if (!empty($prof['classes'])): ?>
                                                    <div class="info-badge">
                                                        <span class="badge badge-info">
                                                            <?= $prof['nombre_classes'] ?> classe(s)
                                                        </span>
                                                        <small class="info-detail" title="<?= htmlspecialchars($prof['classes']) ?>">
                                                            <?= htmlspecialchars(substr($prof['classes'], 0, 30)) ?>
                                                            <?= strlen($prof['classes']) > 30 ? '...' : '' ?>
                                                        </small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted-small">
                                                        <i class="fa-solid fa-minus-circle"></i> Aucune
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- Matières -->
                                            <td>
                                                <?php if (!empty($prof['matieres'])): ?>
                                                    <small class="info-detail" title="<?= htmlspecialchars($prof['matieres']) ?>">
                                                        <?= htmlspecialchars(substr($prof['matieres'], 0, 25)) ?>
                                                        <?= strlen($prof['matieres']) > 25 ? '...' : '' ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted-small">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- Date recrutement -->
                                            <td>
                                                <?php if (!empty($prof['date_recrutement'])): ?>
                                                    <?php
                                                    $dateEmbauche = new DateTime($prof['date_recrutement']);
                                                    $anciennete = $dateEmbauche->diff(new DateTime());
                                                    ?>
                                                    <div class="date-info">
                                                        <strong><?= $dateEmbauche->format('d/m/Y') ?></strong>
                                                        <small class="text-muted-small">
                                                            <?= $anciennete->y ?> an(s) <?= $anciennete->m ?> mois
                                                        </small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted-small">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- Statut -->
                                            <td class="text-center">
                                                <?php
                                                $statut = $prof['statut_compte'] ?? 'inconnu';
                                                $badges = [
                                                    'actif' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Actif'],
                                                    'en_attente' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'En attente'],
                                                    'inactif' => ['class' => 'secondary', 'icon' => 'pause-circle', 'text' => 'Inactif'],
                                                    'suspendu' => ['class' => 'danger', 'icon' => 'ban', 'text' => 'Suspendu']
                                                ];
                                                $badgeInfo = $badges[$statut] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Inconnu'];
                                                ?>
                                                <span class="badge badge-<?= $badgeInfo['class'] ?>">
                                                    <i class="fa-solid fa-<?= $badgeInfo['icon'] ?>"></i>
                                                    <?= $badgeInfo['text'] ?>
                                                </span>
                                            </td>
                                            
                                            <!-- Actions -->
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="detail-professeur.php?id=<?= $prof['professeur_id'] ?>" 
                                                       class="btn-action btn-action-info" 
                                                       title="Voir détails">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <a href="modifier-professeur.php?id=<?= $prof['professeur_id'] ?>" 
                                                       class="btn-action btn-action-warning" 
                                                       title="Modifier">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <a href="emploi-du-temps-prof.php?id=<?= $prof['professeur_id'] ?>" 
                                                       class="btn-action btn-action-success" 
                                                       title="Emploi du temps">
                                                        <i class="fa-solid fa-calendar"></i>
                                                    </a>
                                                    <button onclick="confirmerSuppression(<?= $prof['professeur_id'] ?>, '<?= htmlspecialchars($prof['nom'] . ' ' . $prof['prenom'], ENT_QUOTES) ?>')" 
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

        <style>
            :root {
                --primary-color: #4F46E5;
                --success-color: #10B981;
                --warning-color: #F59E0B;
                --danger-color: #EF4444;
                --info-color: #3B82F6;
                --secondary-color: #6B7280;
                --purple-color: #8B5CF6;
                --bg-light: #F9FAFB;
                --border-color: #E5E7EB;
                --text-dark: #111827;
                --text-muted: #6B7280;
            }

            .page-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid var(--border-color);
            }

            .page-header h1 {
                font-size: 2rem;
                color: var(--text-dark);
                margin: 0;
            }

            .page-header p {
                margin: 0.5rem 0 0 0;
                font-size: 0.95rem;
            }

            .page-header > div:last-child {
                display: flex;
                gap: 0.75rem;
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
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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

            .stat-primary .stat-icon { background: linear-gradient(135deg, var(--primary-color), #6366F1); }
            .stat-success .stat-icon { background: linear-gradient(135deg, var(--success-color), #34D399); }
            .stat-warning .stat-icon { background: linear-gradient(135deg, var(--warning-color), #FBBF24); }
            .stat-info .stat-icon { background: linear-gradient(135deg, var(--info-color), #60A5FA); }

            .stat-content h3 {
                font-size: 2rem;
                margin: 0;
                color: var(--text-dark);
            }

            .stat-content p {
                margin: 0.25rem 0 0 0;
                color: var(--text-muted);
                font-size: 0.9rem;
            }

            /* Filter Card */
            .filter-card {
                margin-bottom: 1.5rem;
                border: none;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }

            .filter-grid {
                display: grid;
                grid-template-columns: 2fr 1fr 1fr auto;
                gap: 1rem;
                align-items: center;
            }

            .search-box {
                position: relative;
            }

            .search-box i {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--text-muted);
            }

            .search-box input {
                padding-left: 2.75rem;
            }

            /* Table Card */
            .table-card {
                border: none;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }

            .table-card .card-header {
                background: linear-gradient(135deg, var(--primary-color), #6366F1);
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1.25rem 1.5rem;
            }

            .table-card .card-header h3 {
                margin: 0;
                font-size: 1.25rem;
            }

            .table-info {
                background: rgba(255,255,255,0.2);
                padding: 0.5rem 1rem;
                border-radius: 6px;
                font-weight: 500;
            }

            /* Modern Table */
            .table-modern {
                margin: 0;
            }

            .table-modern thead {
                background: var(--bg-light);
            }

            .table-modern thead th {
                font-weight: 600;
                color: var(--text-dark);
                border-bottom: 2px solid var(--border-color);
                padding: 1rem 0.75rem;
                font-size: 0.875rem;
            }

            .table-modern tbody td {
                padding: 1rem 0.75rem;
                vertical-align: middle;
                border-bottom: 1px solid var(--border-color);
            }

            .table-row-hover:hover {
                background: var(--bg-light);
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
                background: linear-gradient(135deg, var(--primary-color), #6366F1);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 0.875rem;
            }

            .user-name {
                display: block;
                color: var(--text-dark);
                font-size: 0.95rem;
            }

            .user-subtext {
                display: block;
                color: var(--text-muted);
                font-size: 0.85rem;
            }

            /* Contact Info */
            .contact-info {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .contact-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
            }

            .contact-item i {
                color: var(--text-muted);
                width: 16px;
            }

            .contact-item a {
                color: var(--primary-color);
                text-decoration: none;
            }

            .contact-item a:hover {
                text-decoration: underline;
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

            .badge-matricule {
                background: #EEF2FF;
                color: var(--primary-color);
                font-family: monospace;
            }

            .badge-male {
                background: #DBEAFE;
                color: var(--info-color);
            }

            .badge-female {
                background: #FCE7F3;
                color: #EC4899;
            }

            .badge-specialite {
                background: #F3E8FF;
                color: var(--purple-color);
            }

            .badge-info {
                background: #DBEAFE;
                color: var(--info-color);
            }

            .badge-success {
                background: #D1FAE5;
                color: var(--success-color);
            }

            .badge-warning {
                background: #FEF3C7;
                color: #D97706;
            }

            .badge-danger {
                background: #FEE2E2;
                color: var(--danger-color);
            }

            .badge-secondary {
                background: #F3F4F6;
                color: var(--secondary-color);
            }

            /* Info Badges */
            .info-badge {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .info-detail {
                color: var(--text-muted);
                font-size: 0.8rem;
            }

            .date-info {
                display: flex;
                flex-direction: column;
                gap: 0.125rem;
            }

            .date-info strong {
                font-size: 0.9rem;
            }

            .text-muted-small {
                color: var(--text-muted);
                font-size: 0.85rem;
            }

            /* Action Buttons */
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
                font-size: 0.875rem;
            }

            .btn-action:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            }

            .btn-action-info {
                background: #DBEAFE;
                color: var(--info-color);
            }

            .btn-action-warning {
                background: #FEF3C7;
                color: #D97706;
            }

            .btn-action-success {
                background: #D1FAE5;
                color: var(--success-color)
            }
        </style>