<?php
session_start();

$role = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'Invité';

$dirName = basename(dirname($_SERVER['SCRIPT_FILENAME']));
$basePath = ($dirName === 'admin' || $dirName === 'prof' || $dirName === 'eleve') ? '../' : './';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= $basePath ?>styles/style.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <!-- ON PASSE basePath AU JAVASCRIPT UNE SEULE FOIS ICI -->
    <script>
        const basePath = <?= json_encode($basePath) ?>;
    </script>
    <script defer src="<?= $basePath ?>scripts/java.js"></script>

    <!-- Tu peux laisser les CSS spécifiques dans la page qui inclut le header -->
</head>
<body>

    <div class="div1">
        <div class="logo">
            <img src="<?= $basePath ?>images/icone/CEG-fm.png" alt="logo">
            <h2>CEG FM</h2>
        </div>

        <ul class="menu">
            <li class="titre-menu"><p>Menu</p></li>

        <?php if ($role === 'admin'): ?>

        <!-- ÉLÈVES -->
        <li class="submenu-item">
            <a href="#" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-étudiant-homme-64.png" alt="">
                <span>Élèves</span>
                <img src="<?= $basePath ?>images/icone/fleche-bas.png" class="arrow-icon">
            </a>

            <!-- <ul class="submenu"> -->
                <li>
                    <a href="<?= $basePath ?>admin/liste-eleve.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/liste.png" alt=""> Liste des élèves
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/inscription-eleve.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/inscription.png" alt=""> Inscription
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/certificat-scolarite.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/certificat.png" alt=""> Certificats
                    </a>
                </li>
            <!-- </ul> -->
        </li>


        <!-- PROFESSEURS -->
        <li class="submenu-item">
            <a href="#" class="menu-item">
                <img src="<?= $basePath ?>images/icone/professeur.png" alt="">
                <span>Professeurs</span>
                <img src="<?= $basePath ?>images/icone/fleche-bas.png" class="arrow-icon">
            </a>

            <!-- <ul class="submenu"> -->
                <li>
                    <a href="<?= $basePath ?>admin/liste-prof.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/professeur.png" alt=""> Liste des profs
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/affectation-prof.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/icons8-affectation-50.png" alt=""> Affectations
                    </a>
                </li>
            <!-- </ul> -->
        </li>


        <!-- ORGANISATION -->
        <li class="submenu-item">
            <a href="#" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-classe-50.png" alt="">
                <span>Organisation</span>
                <img src="<?= $basePath ?>images/icone/fleche-bas.png" class="arrow-icon">
            </a>

            <!-- <ul class="submenu"> -->
                <li>
                    <a href="<?= $basePath ?>admin/liste-classe.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/icons8-classe-50.png" alt=""> Classes
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/liste-matier.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/icons8-cahier-80.png" alt=""> Matières
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/calendrier.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/calendrier.png" alt=""> Calendrier
                    </a>
                </li>
            <!-- </ul> -->
        </li>


        <!-- NOTES / BULLETINS -->
        <li class="submenu-item">
            <a href="#" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-stylo-64.png" alt="">
                <span>Notes & bulletins</span>
                <img src="<?= $basePath ?>images/icone/fleche-bas.png" class="arrow-icon">
            </a>

            <!-- <ul class="submenu"> -->
                <li>
                    <a href="<?= $basePath ?>admin/notes.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/icons8-vérification-du-mot-de-passe-50.png" alt=""> Saisie notes
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/bulletins.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/icons8-stylo-64.png" alt=""> Bulletins
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>admin/statistiques.php" class="menu-item">
                        <img src="<?= $basePath ?>images/icone/icons8-graphiques-64.png" alt=""> Statistiques
                    </a>
                </li>
            <!-- </ul> -->
        </li>

            <?php elseif($role === 'prof'): ?>
        <!-- PROFESSEUR -->
        <li>
            <a href="<?= $basePath ?>prof/mes-classes.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-classe-50.png" alt="">
                <span>Mes classes</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>prof/saisie-notes.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-stylo-64.png" alt="">
                <span>Saisie des notes</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>prof/bulletins.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-stylo-64.png" alt="">
                <span>Bulletins</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>prof/appel.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/appel.png" alt="">
                <span>Faire l'appel</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>prof/cahier-de-texte.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-cahier-80.png" alt="">
                <span>Cahier de texte</span>
            </a>
        </li>


    <?php elseif($role === 'eleve'): ?>
        <!-- ÉLÈVE -->
        <li>
            <a href="<?= $basePath ?>eleve/accueil.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-classe-50.png" alt="">
                <span>Ma classe</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>eleve/mes-notes.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-note-50.png" alt="">
                <span>Mes notes & bulletins</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>eleve/emploi-du-temps.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/calendrier.png" alt="">
                <span>Emploi du temps</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>eleve/absences.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-vide-50.png" alt="">
                <span>Mes absences</span>
            </a>
        </li>


    <?php else: ?>
        <!-- INVITÉ / NON CONNECTÉ -->
        <li>
            <a href="<?= $basePath ?>accueil.php" class="menu-item">
                <img src="<?= $basePath ?>images/icone/accueil.png" alt="">
                <span>Accueil</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>emploi-du-temps/" class="menu-item">
                <img src="<?= $basePath ?>images/icone/icons8-cahier-80.png" alt="">
                <span>Emploi du temps</span>
            </a>
        </li>

        <li>
            <a href="<?= $basePath ?>calendrier/" class="menu-item">
                <img src="<?= $basePath ?>images/icone/calendrier.png" alt="">
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
            <a href="#">
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
            </a>
        </div>
    </div>
