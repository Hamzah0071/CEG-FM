<?php
// Démarrer la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer les infos utilisateur
$role = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'Invité';
$user_id = $_SESSION['user_id'] ?? null;

// Labels des rôles
$roleLabels = [
    'admin' => 'Administrateur',
    'prof'  => 'Professeur',
    'eleve' => 'Élève',
    'guest' => 'Invité'
];
$roleLabel = $roleLabels[$role] ?? 'Invité';

// Chemins absolus (à adapter une seule fois si tu changes le nom du projet ou le domaine)
define('BASE_URL', '/social-prof-comunication/');                    // Racine du site
define('PUBLIC_URL', BASE_URL . 'public/');                          // Pour les assets (CSS, JS, images)
define('APP_URL', BASE_URL . 'app/');                                // Pour toutes les pages PHP

// Photo de profil
$profileImage = PUBLIC_URL . 'assets/images/profile/default.jpg';
if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $_SESSION['profile_image'])) {
    $profileImage = BASE_URL . $_SESSION['profile_image'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($pageTitle ?? 'CEG FM - Gestion Scolaire') ?></title>

    <!-- Styles et icône -->
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>assets/css/style.css">
    <link rel="icon" type="image/png" href="<?= PUBLIC_URL ?>assets/images/icone/CEG-fm.png">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>assets/icon/fontAwesome/all.min.css">

    <!-- bootrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <img src="<?= PUBLIC_URL ?>assets/images/icone/CEG-fm.png" alt="Logo CEG FM">
            <h2>CEG FM</h2>
        </div>

        <nav class="menu">
            <?php if ($role === 'admin'): ?>
                <!-- Gestion Élèves -->
                <div class="menu-section">
                    <span class="menu-title"><i class="fa-solid fa-users"></i> Gestion Élèves</span>
                    <ul>
                        <a href="<?= APP_URL ?>admin/eleves/liste-eleve.php" class="menu-item">
                            <i class="fa-solid fa-list"></i> Liste des élèves</a>
                        <a href="<?= APP_URL ?>admin/eleves/inscription-eleve.php" class="menu-item">
                            <i class="fa-solid fa-user-plus"></i> Inscription</a>
                        <a href="<?= APP_URL ?>admin/eleves/certificat-scolarite.php" class="menu-item">
                            <i class="fa-solid fa-graduation-cap"></i> Certificats</a>
                    </ul>
                </div>

                <!-- Gestion Professeurs -->
                <div class="menu-section">
                    <span class="menu-title"><i class="fa-solid fa-chalkboard-user"></i> Gestion Professeurs</span>
                    <ul>
                         <a href="<?= APP_URL ?>admin/professeurs/liste-professeurs.php" class="menu-item">
                            <i class="fa-solid fa-list-check"></i> Liste des profs</a> 
                         <a href="<?= APP_URL ?>admin/professeurs/recrutement.php" class="menu-item">
                            <i class="fa-solid fa-user-tie"></i> Recrutement</a> 
                         <a href="<?= APP_URL ?>admin/professeurs/liste_affectations.php" class="menu-item">
                            <i class="fa-solid fa-list-ol"></i> Liste d'affectations</a> 
                         <a href="<?= APP_URL ?>admin/professeurs/affectation-prof.php" class="menu-item">
                            <i class="fa-solid fa-scroll"></i> Affectations</a> 
                    </ul>
                </div>

                <!-- Organisation -->
                <div class="menu-section">
                    <span class="menu-title"><i class="fa-solid fa-gear"></i> Organisation</span>
                    <ul>
                         <a href="<?= APP_URL ?>admin/organisation/classe.php" class="menu-item">
                            <i class="fa-solid fa-door-open"></i> Classes</a> 
                         <a href="<?= APP_URL ?>admin/organisation/matier.php" class="menu-item">
                            <i class="fa-solid fa-book"></i> Matières</a> 
                         <a href="<?= APP_URL ?>admin/organisation/calendrier.php" class="menu-item">
                            <i class="fa-solid fa-calendar-days"></i> Calendrier</a> 
                         <a href="<?= APP_URL ?>admin/organisation/utilisateur.php" class="menu-item">
                            <i class="fa-solid fa-user-gear"></i> Comptes utilisateurs</a> 
                    </ul>
                </div>

                <!-- Notes & Bulletins -->
                <div class="menu-section">
                    <span class="menu-title"><i class="fa-solid fa-clipboard"></i> Notes & Bulletins</span>
                    <ul>
                         <a href="<?= APP_URL ?>admin/notes/note.php" class="menu-item">
                            <i class="fa-solid fa-pen-fancy"></i> Saisie notes</a> 
                         <a href="<?= APP_URL ?>admin/notes/bulletin.php" class="menu-item">
                            <i class="fa-solid fa-file-lines"></i> Bulletins</a> 
                         <a href="<?= APP_URL ?>admin/notes/statistiques.php" class="menu-item">
                            <i class="fa-solid fa-chart-line"></i> Statistiques</a> 
                    </ul>
                </div>

            <?php elseif ($role === 'prof'): ?>
                <ul>
                     <a href="<?= APP_URL ?>prof/mes-classes.php" class="menu-item"><i class="fa-solid fa-door-open"></i> Mes classes</a> 
                     <a href="<?= APP_URL ?>prof/saisie-notes.php" class="menu-item"><i class="fa-solid fa-pen-to-square"></i> Saisie des notes</a> 
                     <a href="<?= APP_URL ?>prof/bulletins.php" class="menu-item"><i class="fa-solid fa-file-pen"></i> Bulletins</a> 
                     <a href="<?= APP_URL ?>prof/appel.php" class="menu-item"><i class="fa-solid fa-clipboard-user"></i> Faire l'appel</a> 
                     <a href="<?= APP_URL ?>prof/cahier-de-texte.php" class="menu-item"><i class="fa-solid fa-book-open"></i> Cahier de texte</a> 
                </ul>

            <?php elseif ($role === 'eleve'): ?>
                <ul>
                     <a href="<?= APP_URL ?>eleve/accueil.php" class="menu-item"><i class="fa-solid fa-door-open"></i> Ma classe</a> 
                     <a href="<?= APP_URL ?>eleve/mes-notes.php" class="menu-item"><i class="fa-solid fa-chart-simple"></i> Mes notes & bulletins</a> 
                     <a href="<?= APP_URL ?>eleve/emploi-du-temps.php" class="menu-item"><i class="fa-solid fa-calendar-days"></i> Emploi du temps</a> 
                     <a href="<?= APP_URL ?>eleve/absences.php" class="menu-item"><i class="fa-solid fa-clock"></i> Mes absences</a> 
                </ul>

            <?php else: ?>
                <ul>
                     <a href="<?= PUBLIC_URL ?>index.php" class="menu-item"><i class="fa-solid fa-house"></i> Accueil</a> 
                     <a href="<?= PUBLIC_URL ?>emploi-du-temps.php" class="menu-item"><i class="fa-solid fa-calendar-days"></i> Emploi du temps</a> 
                     <a href="<?= PUBLIC_URL ?>calendrier.php" class="menu-item"><i class="fa-regular fa-calendar"></i> Calendrier scolaire</a> 
                     <a href="<?= PUBLIC_URL ?>actualites.php" class="menu-item"><i class="fa-solid fa-newspaper"></i> Actualités</a> 
                </ul>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button id="toggleSidebar" class="toggle-btn" aria-label="Toggle menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="searchBar" class="search-bar" placeholder="Rechercher..." aria-label="Rechercher">
            </div>
        </div>

        <div class="topbar-right">
            <div class="notification-icon">
                <i class="fa-solid fa-bell"></i>
                <span class="badge">33</span>
            </div>

            <div class="user-profile dropdown">  <!-- Ajoute class="dropdown" ici -->
                <img src="../../public/assets/images/CEG-fm.png" alt="Photo de profil" class="profile-img">    
                <img src="<?= htmlspecialchars($profileImage) ?>" alt="Photo de profil" class="profile-img">
                
                <button class="dropdown-toggle btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <!-- btn pour style Bootstrap, type="button" obligatoire -->
                    <span class="username"><?= htmlspecialchars($username) ?></span>
                    <small class="user-role"><?= htmlspecialchars($roleLabel) ?></small>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                
                <ul class="dropdown-menu">  <!-- Change div en ul/li pour vrai Bootstrap -->
                    <li><a href="<?= APP_URL ?>profil.php" class="dropdown-item">
                        <i class="fa-solid fa-user"></i> Mon profil</a></li>
                    <li><a href="<?= APP_URL ?>parametres.php" class="dropdown-item">
                        <i class="fa-solid fa-gear"></i> Paramètres</a></li>
                    <li><hr class="dropdown-divider"></li>  <!-- Divider Bootstrap -->
                    <li><a href="<?= APP_URL ?>auth/logout.php" class="dropdown-item logout">
                        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="main-content">
        <!-- Messages flash -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Le contenu spécifique de chaque page viendra ici -->