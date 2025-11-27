<?php
$etudiants = [

    // ==================== 6ème A ====================
    [
        'id'             => null,  // ← SERA LA CLÉ PRIMAIRE en BDD (AUTO_INCREMENT)
        'nom'            => 'ANDRIANASOLO',
        'sexe'           => 'M',
        'age'            => '11',
        'date_naissance' => '12-05-2014',
        'is_enabled'     => true,
        'classe'         => '6ème A'  // ← SERA LA CLÉ ÉTRANGÈRE → référence table "classes"
    ],
    [
        'id'             => null,
        'nom'            => 'RAKOTONDRABE Nirina',
        'sexe'           => 'F',
        'age'            => '12',
        'date_naissance' => '23-09-2013',
        'is_enabled'     => true,
        'classe'         => '6ème A'
    ],
    // ... (tous les autres élèves de 6ème A)

    // ==================== 6ème B ====================
    [
        'id'             => null,
        'nom'            => 'ANDRIAMBOAVONJY Rivo',
        'sexe'           => 'M',
        'age'            => '11',
        'date_naissance' => '27-12-2014',
        'is_enabled'     => true,
        'classe'         => '6ème B'  // ← même champ → même clé étrangère
    ],
    // ... etc.

    // Tous les autres élèves (6ème C, D, E, F, 5ème, 4ème, 3ème...)
    // → même structure, même logique

    // Exemple dernier élève
    [
        'id'             => null,
        'nom'            => 'RAKOTOBE Fanantenana',
        'sexe'           => 'F',
        'age'            => '12',
        'date_naissance' => '17-03-2013',
        'is_enabled'     => true,
        'classe'         => '3ème A'  // ← toujours lié à la table classes
    ],

];

$selected_nom = $_POST['eleve'] ?? '';
?>

<!-- ==================== -->
 <!-- Étape 1 : Créer un menu déroulant avec toutes les classes disponibles -->

<?// Récupérer toutes les classes uniques
 $classes_disponibles = array_unique(array_column($etudiants, 'classe'));
// Trier par ordre logique (facultatif mais joli)
sort($classes_disponibles);?>

<!-- Étape 2 : Récupérer la classe sélectionnée par l’utilisateur -->

<?$classe_selectionnee = $_POST['classe'] ?? '';  // ou $_GET si tu préfères ?>


<!-- Étape 3 : Filtrer les étudiants en fonction de la classe sélectionnée -->
<? 
$eleves_filtrés = [];

if ($classe_selectionnee === '' || $classe_selectionnee === 'toutes') {
    // Si rien n'est choisi ou "Toutes les classes" → on affiche tout
    $eleves_filtrés = $etudiants;
} else {
    foreach ($etudiants as $eleve) {
        if ($eleve['classe'] === $classe_selectionnee) {
            $eleves_filtrés[] = $eleve;
        }
    }
}
?>


<!-- ============= -->
 <?php
$matieres = ["Malagasy", "Français", "Anglais", "H-G", "MATH", "PC", "SVT", "TICE", "EPS"];
$classes  = ["6ème", "5ème", "4ème", "3ème"];
$initiales = ['A', 'B', 'C', 'D', 'E', 'F'];

$matieres = $_POST['matiere'] ?? '';
$classes  = $_POST['classe'] ?? '';
$initiales = $_POST['initiale'] ?? '';
?>

<!-- Select matière -->
<select name="matiere" required>
    <option value="">-- Choisir une matière --</option>
    <?php foreach ($matieres as $m): ?>
        <option value="<?= htmlspecialchars($m) ?>" 
            <?= (($_POST['matiere'] ?? '') === $m) ? 'selected' : '' ?>>
            <?= htmlspecialchars($m) ?>
        </option>
    <?php endforeach; ?>
</select>