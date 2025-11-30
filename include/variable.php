<?php
// Tableaux de base (jamais écrasés)
$liste_matieres  = 
    [["Malagasy", 3],
    ["Français", 2],
    ["Anglais", 2],
    ["HISTO-GEO", 3],
    ["MATH", 3],
    ["PC", 2],
    ["SVT", 3],
    ["TICE", 1],
    ["EPS", 1],
];
$liste_classes   = ["6ème", "5ème", "4ème", "3ème"];
$liste_initiales = ['A', 'B', 'C', 'D', 'E', 'F'];

// Récupération POST sans écraser les tableaux
$matiere_post   = $_POST['matiere'] ?? '';
$classe_post    = $_POST['classe'] ?? '';
$initiale_post  = $_POST['initiale'] ?? '';


// boucle les classe a parire des variable 
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