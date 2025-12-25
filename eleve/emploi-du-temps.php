<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>Emploi du temps</title>

    <style>
        .edt-container { font-family: 'Times New Roman', serif; max-width: 1150px; margin: 0 auto; padding: 20px; }
        .edt-header { text-align: center; padding: 20px; background: #f0f8ff; border: 3px solid #1e3d8e; border-radius: 12px; margin-bottom: 20px; }
        .edt-header h2 { margin: 10px 0; font-size: 28px; color: #1e3d8e; }
        .edt-info { background: #e8f5e8; padding: 18px; border-radius: 10px; text-align: center; font-size: 17px; font-weight: bold; color: #155724; margin-bottom: 25px; }

        .table-edt { width: 100%; border-collapse: collapse; font-size: 15px; margin-bottom: 20px; }
        .table-edt th { background: #1e3d8e; color: white; padding: 15px; border: 2px solid #000; text-align: center; }
        .table-edt td { border: 2px solid #000; height: 95px; vertical-align: top; padding: 8px; background: #f8fdff; }

        .heure { background: #e0e0e0; font-weight: bold; text-align: center; width: 100px; }

        .cours { background: white; padding: 8px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); height: 78px; font-size: 13.5px; }
        .matiere { font-weight: bold; color: #1e3d8e; font-size: 14px; }
        .prof { font-style: italic; color: #444; font-size: 12px; margin-top: 3px; }

        .recreation { background: #4caf50 !important; color: white; font-weight: bold; text-align: center; font-size: 18px; }
        .pause { background: #ffeb3b; font-weight: bold; text-align: center; }
    </style>
</head>

<body>

<div class="parent">
<?php require_once('../include/header.php'); ?>

<?php
require_once('../include/liste-des-prof.php');
require_once(__DIR__.'/../include/variable.php');

// Classe sélectionnée
$classe_courante = $_GET['classe'] ?? $_SESSION['classe'] ?? '6ème A';

// Vérification : classe valide ?
$classe_valide = false;
foreach ($classes_existantes as $c) {
    if ($c['nom']." ".$c['initiale'] === $classe_courante) {
        $classe_valide = true;
        break;
    }
}
if (!$classe_valide) {
    $classe_courante = "6ème A";
}
?>
    <div class="div3">
        <div class="edt-container">

            <div class="edt-header">
                <h2>EMPLOI DU TEMPS</h2>
                <p>Année scolaire 2025 / 2026</p>
            </div>

            <div class="edt-info">
                CLASSE : <strong><?= htmlspecialchars($classe_courante) ?></strong>
                &nbsp;|&nbsp; Effectif : 38 élèves
                &nbsp;|&nbsp; Professeur principal :
                <strong>
                    <?php
                    foreach ($professeurs as $p) {
                        if ($p['matiere'] === 'Français' && in_array($classe_courante, $p['classes'])) {
                            echo htmlspecialchars($p['nom']);
                            break;
                        }
                    }
                    ?>
                </strong>
            </div>

            <table class="table-edt">
                <thead>
                    <tr>
                        <th class="heure">Heure</th>
                        <th>Lundi</th>
                        <th>Mardi</th>
                        <th>Mercredi</th>
                        <th>Jeudi</th>
                        <th>Vendredi</th>
                    </tr>
                </thead>

                <tbody>

                    <!-- Ligne 1 -->
                    <tr>
                        <td class="heure">06:00<br>08:00</td>
                        <td><?= blocCours("Malagasy", $classe_courante) ?></td>
                        <td><?= blocCours("Mathématiques", $classe_courante) ?></td>
                        <td><?= blocCours("Français", $classe_courante) ?></td>
                        <td><?= blocCours("Anglais", $classe_courante) ?></td>
                        <td><?= blocCours("HISTO-GEO", $classe_courante) ?></td>
                    </tr>

                    <!-- Ligne 2 -->
                    <tr>
                        <td class="heure">08:30<br>10:00</td>
                        <td><?= blocCours("Français", $classe_courante) ?></td>
                        <td><?= blocCours("SVT", $classe_courante) ?></td>
                        <td><?= blocCours("Mathématiques", $classe_courante) ?></td>
                        <td><?= blocCours("Malagasy", $classe_courante) ?></td>
                        <td><?= blocCours("Informatique", $classe_courante) ?></td>
                    </tr>

                    <!-- Pause -->
                    <tr>
                        <td class="heure">10:00<br>10:30</td>
                        <td colspan="5" class="recreation">PAUSE</td>
                    </tr>

                    <!-- Ligne 3 -->
                    <tr>
                        <td class="heure">10:30<br>12:00</td>
                        <td><?= blocCours("SVT", $classe_courante) ?></td>
                        <td><?= blocCours("Physique-Chimie", $classe_courante) ?></td>
                        <td><?= blocCours("EPS", $classe_courante) ?></td>
                        <td><?= blocCours("HISTO-GEO", $classe_courante) ?></td>
                        <td><?= blocCours("Anglais", $classe_courante) ?></td>
                    </tr>

                    <!-- Pause midi -->
                    <tr>
                        <td class="heure">12:00<br>15:00</td>
                        <td colspan="5" class="pause">PAUSE MIDI</td>
                    </tr>

                    <!-- Ligne 4 -->
                    <tr>
                        <td class="heure">15:00<br>17:00</td>
                        <td><?= blocCours("Mathématiques", $classe_courante) ?></td>
                        <td><?= blocCours("TICE", $classe_courante) ?></td>
                        <td><?= blocCours("Français", $classe_courante) ?></td>
                        <td><?= blocCours("Malagasy", $classe_courante) ?></td>
                        <td><?= blocCours("SVT", $classe_courante) ?></td>
                    </tr>

                    <!-- Ligne 5 -->
                    <tr>
                        <td class="heure">17:30<br>18:00</td>
                        <td><?= blocCours("EPS", $classe_courante) ?></td>
                        <td><?= blocCours("Anglais", $classe_courante) ?></td>
                        <td><?= blocCours("Informatique", $classe_courante) ?></td>
                        <td><?= blocCours("Français", $classe_courante) ?></td>
                        <td><?= blocCours("HISTO-GEO", $classe_courante) ?></td>
                    </tr>

                </tbody>
            </table>

        </div>

    </div>


<?php
function blocCours($matiere, $classe) {
    $prof = getProf($matiere, $classe);
    return "
        <div class='cours'>
            <span class='matiere'>$matiere</span><br>
            <span class='prof'>$prof</span>
        </div>";
}

function getProf($matiere, $classe) {
    global $professeurs;
    foreach ($professeurs as $p) {
        if ($p['matiere'] === $matiere && in_array($classe, $p['classes'])) {
            return htmlspecialchars($p['nom']);
        }
    }
    return "Non assigné";
}
?>

</div>
</body>
</html>
