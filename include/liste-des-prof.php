<?php
$professeurs = [
    // Mathématiques
    ['nom' => 'Mr RAKOTONDRABE Tianà',       'sexe' => 'M', 'age' => '34', 'date_naissance' => '1991-06-15', 'matiere' => 'Mathématiques', 'classes' => ['6ème A', '6ème B', '5ème C'], 'is_enabled' => true],
    ['nom' => 'Mme RANDRIANASOLO Fitia',     'sexe' => 'F', 'age' => '29', 'date_naissance' => '1996-03-22', 'matiere' => 'Mathématiques', 'classes' => ['6ème C', '6ème D', '5ème A'], 'is_enabled' => true],
    ['nom' => 'Mr ANDRIAMIHAJA Toky',        'sexe' => 'M', 'age' => '38', 'date_naissance' => '1987-11-08', 'matiere' => 'Mathématiques', 'classes' => ['5ème B', '4ème A', '4ème B'], 'is_enabled' => true],

    // SVT (Sciences de la Vie et de la Terre)
    ['nom' => 'Mme RAZAFINDRAKOTO Nirina',   'sexe' => 'F', 'age' => '31', 'date_naissance' => '1994-09-12', 'matiere' => 'SVT',            'classes' => ['6ème A', '6ème E', '5ème D'], 'is_enabled' => true],
    ['nom' => 'Mr MOHAMED Ali',              'sexe' => 'M', 'age' => '36', 'date_naissance' => '1989-02-27', 'matiere' => 'SVT',            'classes' => ['6ème F', '5ème A', '5ème E'], 'is_enabled' => true],

    // Physique-Chimie
    ['nom' => 'Mr RAHARISON Kanto',          'sexe' => 'M', 'age' => '42', 'date_naissance' => '1983-07-19', 'matiere' => 'Physique-Chimie', 'classes' => ['5ème A', '5ème B', '4ème C'], 'is_enabled' => true],
    ['nom' => 'Mme SOILIHI Fatima',          'sexe' => 'F', 'age' => '28', 'date_naissance' => '1997-04-05', 'matiere' => 'Physique-Chimie', 'classes' => ['4ème A', '4ème B', '3ème D'], 'is_enabled' => true],

    // Français
    ['nom' => 'Mme ANDRIANARIVELO Lalaina',  'sexe' => 'F', 'age' => '35', 'date_naissance' => '1990-10-30', 'matiere' => 'Français',       'classes' => ['6ème A', '6ème B', '6ème C'], 'is_enabled' => true],
    ['nom' => 'Mr RAZAFIMAHATRATRA Rivo',    'sexe' => 'M', 'age' => '39', 'date_naissance' => '1986-01-14', 'matiere' => 'Français',       'classes' => ['6ème D', '6ème E', '6ème F'], 'is_enabled' => true],
    ['nom' => 'Mme RAJAONARISON Hasina',     'sexe' => 'F', 'age' => '33', 'date_naissance' => '1992-08-21', 'matiere' => 'Français',       'classes' => ['5ème A', '5ème B', '5ème C'], 'is_enabled' => true],

    // Histoire-Géographie
    ['nom' => 'Mr IBRAHIM Saïd',             'sexe' => 'M', 'age' => '45', 'date_naissance' => '1980-05-03', 'matiere' => 'Histoire-Géo',    'classes' => ['5ème D', '5ème E', '4ème A'], 'is_enabled' => true],
    ['nom' => 'Mme COMBO Aïcha',             'sexe' => 'F', 'age' => '30', 'date_naissance' => '1995-12-17', 'matiere' => 'Histoire-Géo',    'classes' => ['4ème B', '4ème C', '3ème A'], 'is_enabled' => true],

    // Anglais
    ['nom' => 'Mme RAZAFINDRAIBE Mirantsoa', 'sexe' => 'F', 'age' => '27', 'date_naissance' => '1998-07-09', 'matiere' => 'Anglais',        'classes' => ['6ème A', '6ème F', '5ème E'], 'is_enabled' => true],
    ['nom' => 'Mr AHMED Nasser',             'sexe' => 'M', 'age' => '32', 'date_naissance' => '1993-11-25', 'matiere' => 'Anglais',        'classes' => ['5ème A', '4ème D', '3ème B'], 'is_enabled' => true],

    // Education Physique et Sportive
    ['nom' => 'Mr RAKOTOMALALA Tianà',       'sexe' => 'M', 'age' => '37', 'date_naissance' => '1988-04-18', 'matiere' => 'EPS',            'classes' => ['6ème A', '6ème B', '6ème C', '6ème D'], 'is_enabled' => true],
    ['nom' => 'Mme ANDRIAMBOAVONJY Soa',     'sexe' => 'F', 'age' => '29', 'date_naissance' => '1996-09-06', 'matiere' => 'EPS',            'classes' => ['6ème E', '6ème F', '5ème A', '5ème B'], 'is_enabled' => true],

    // Informatique / Technologie
    ['nom' => 'Mr BEN ALI Omar',             'sexe' => 'M', 'age' => '31', 'date_naissance' => '1994-02-28', 'matiere' => 'Informatique',   'classes' => ['5ème C', '4ème A', '3ème C'], 'is_enabled' => true],

    // Education Civique et Morale
    ['nom' => 'Mme RAHARIVELO Fanantenana',  'sexe' => 'F', 'age' => '40', 'date_naissance' => '1985-10-10', 'matiere' => 'ECM',            'classes' => ['3ème A', '3ème B', '3ème C', '3ème D'], 'is_enabled' => true],
];

// Pour tester rapidement
// echo "<pre>"; print_r($professeurs); echo "</pre>";

$selected_prof = $_POST['professeur'] ?? '';
?>