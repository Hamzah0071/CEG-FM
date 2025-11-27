<?php
// Tableaux de base (jamais écrasés)
$liste_matieres  = ["Malagasy", "Français", "Anglais", "H-G", "MATH", "PC", "SVT", "TICE", "EPS"];
$liste_classes   = ["6ème", "5ème", "4ème", "3ème"];
$liste_initiales = ['A', 'B', 'C', 'D', 'E', 'F'];

// Récupération POST sans écraser les tableaux
$matiere_post   = $_POST['matiere'] ?? '';
$classe_post    = $_POST['classe'] ?? '';
$initiale_post  = $_POST['initiale'] ?? '';

// Génération automatique de TOUTES les classes possibles
$classes_existantes = [];

foreach ($liste_classes as $niveau) {
    foreach ($liste_initiales as $initiale) {
        $classes_existantes[] = [
            "nom"       => $niveau,
            "initiale"  => $initiale
        ];
    }
}
?>