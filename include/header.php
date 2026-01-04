<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'Invité';

$roleLabel = [
    'admin' => 'Administrateur',
    'prof'  => 'Professeur',
    'eleve' => 'Élève'
][$role] ?? 'Invité';

$dirName = basename(dirname($_SERVER['SCRIPT_FILENAME']));
$basePath = in_array($dirName, ['admin', 'prof', 'eleve']) ? '../' : './';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= $basePath ?>styles/style.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <link rel="stylesheet" href="../assets/icon/fontAwesome/all.min.css">
</head>
<body>

    <div class="div1">
        <div class="logo">
            <img src="<?= $basePath ?>images/icone/CEG-fm.png" alt="logo">
            <h2>CEG FM</h2>

        </div>

        <ul class="menu">

        <?php if ($role === 'admin'): ?>

        <!-- ÉLÈVES -->
        <span>
            <i>Menus Élèves</i>
        </span>

            <!-- <ul class="submenu"> -->
                <li>
                    <a href="<?= $basePath ?>admin/liste-eleve.php" class="menu-item">
                        <i class="fa-solid fa-list"></i>
                        Liste des élèves
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/inscription-eleve.php" class="menu-item">
                        <i class="fa-regular fa-pen-to-square"></i>
                        Inscription
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/certificat-scolarite.php" class="menu-item">
                        <i class="fa-solid fa-graduation-cap"></i>
                         Certificats
                    </a>
                </li>
            <!-- </ul> -->
        </li>


        <!-- PROFESSEURS -->
        <span>
            <i>Menus Professeurs</i>
        </span>

            <!-- <ul class="submenu"> -->
                <li>
                    <a href="<?= $basePath ?>admin/liste-prof.php" class="menu-item">
                        <i class="fa-solid fa-list-check"></i>
                        Liste des profs
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/recrutement.php" class="menu-item">
                        <i class="fa-regular fa-paste"></i>
                         Recrutement
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/liste_affectations.php" class="menu-item">
                        <i class="fa-solid fa-list-ol"></i>
                        liste d'affectations
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/affectation-prof.php" class="menu-item">
                        <i class="fa-solid fa-scroll"></i>
                         Affectations
                    </a>
                </li>
            <!-- </ul> -->
        </li>


        <!-- ORGANISATION -->
        <span>
            <i>Menus Professeurs</i>
        </span>

            <!-- <ul class="submenu"> -->
                <li>
                    <a href="<?= $basePath ?>admin/liste-classe.php" class="menu-item">
                        <i class="fa-solid fa-laptop"></i>
                         Classes
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/liste-matier.php" class="menu-item">
                        <i class="fa-solid fa-book"></i>
                        Matières
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/calendrier.php" class="menu-item">
                        <i class="fa-solid fa-calendar-days"></i>
                        Calendrier
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/utilisateurs.php" class="menu-item">
                        <i class="fa-solid fa-user-gear"></i>
                        Comptes utilisateurs
                    </a>
                </li>
            <!-- </ul> -->
        </li>


        <!-- NOTES / BULLETINS -->
        <span>
            <i>Menus Notes & bulletins</i>
        </span>

            <!-- <ul class="submenu"> -->
                <li>
                    <a href="<?= $basePath ?>admin/notes.php" class="menu-item">
                        <i class="fa-solid fa-pen-fancy"></i>
                         Saisie notes
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/bulletins.php" class="menu-item">
                    <i class="fa-solid fa-calculator"></i>
                    Bulletins
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/statistiques.php" class="menu-item">
                    <i class="fa-solid fa-chart-line"></i>
                    Statistiques
                    </a>
                </li>
            <!-- </ul> -->
        </li>

            <?php elseif($role === 'prof'): ?>
        <!-- PROFESSEUR -->
        <li>
            <a href="<?= $basePath ?>prof/mes-classes.php" class="menu-item">
                <i class="fa-solid fa-laptop"></i>
                <span>Mes classes</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>prof/saisie-notes.php" class="menu-item">
                <i class="fa-solid fa-calculator"></i>
                <span>Saisie des notes</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>prof/bulletins.php" class="menu-item">
                <i class="fa-solid fa-file-pen"></i>
                <span>Bulletins</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>prof/appel.php" class="menu-item">
                <i class="fa-solid fa-users"></i>
                <span>Faire l'appel</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>prof/cahier-de-texte.php" class="menu-item">
                <i class="fa-solid fa-book"></i>
                <span>Cahier de texte</span>
            </a>
        </li>


    <?php elseif($role === 'eleve'): ?>
        <!-- ÉLÈVE -->
        <li>
            <a href="<?= $basePath ?>eleve/ma-classe.php" class="menu-item">
                <i class="fa-solid fa-laptop"></i>
                <span>Ma classe</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>eleve/mes-notes.php" class="menu-item">
                <i class="fa-solid fa-calculator"></i>
                <span>Mes notes & bulletins</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>eleve/emploi-du-temps.php" class="menu-item">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Emploi du temps</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>eleve/absences.php" class="menu-item">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Mes absences</span>
            </a>
        </li>


    <?php else: ?>
        <!-- INVITÉ / NON CONNECTÉ -->
        <li>
            <a href="<?= $basePath ?>accueil.php" class="menu-item">
                <i class="fa-regular fa-house"></i>
                <span>Accueil</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>emploi-du-temps/" class="menu-item">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Emploi du temps</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>calendrier/" class="menu-item">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Calendrier scolaire</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>messages/" class="menu-item">
                <img src="<?= $basePath ?>images/icone/message.png" alt="">
                <span>Messagerie interne</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>documents/" class="menu-item">
                <img src="<?= $basePath ?>images/icone/documents.png" alt="">
                <span>Ressources</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>actualites/" class="menu-item">
                <img src="<?= $basePath ?>images/icone/actualites.png" alt="">
                <span>Actualités</span>
            </a>
        </li>

    <?php endif; ?>

        </ul>
    </div>

    <!-- Barre du haut (recherche + profil) -->
    <div class="div2">
        <div class="gauche">
            <a href="#" id="toggleSidebar">
                <img src="<?= $basePath ?>images/icone/justifier.png" alt="Menu">
            </a>
            <div class="recherche">
                <img src="<?= $basePath ?>images/icone/loupe.png" alt="" class="search-icon">
                <input type="text" id="search" class="search-bar" placeholder="Rechercher...">
            </div>
        </div>

        <div class="droite">
            
                    <img src="<?= $basePath ?>images/profile/Andoarano.jpg" alt="User" class="profile">
                    <div class="dropdown">
                        <button class="dropbtn">
                            <span><?= htmlspecialchars($username) ?></span>
                        </button>
                        <div class="dropdown-content">
                            <a href="#">Paramètres</a>
                            <a href="#">Éditer profil</a>
                            <a href="<?= $basePath ?>auth/logout.php">Déconnexion</a>
                        </div>
                    </div>
            
        </div>
    </div>
