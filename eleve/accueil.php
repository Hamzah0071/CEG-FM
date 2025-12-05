<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/admin/bulletin.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>Document</title>
</head>
<body>
   <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
   <div class="parent">
        <?php
        require_once('../include/header.php'); // chemin relatif selon le dossier
        ?>
        <div class="div3">

<style>
/* ---------- Général ---------- */
.ma-classe {
    font-family: 'Arial', sans-serif;
    max-width: 1150px;
    margin: 0 auto;
    padding: 20px;
    background: #f4f7fb;
}

/* ---------- Cards ---------- */
.card {
    background: #fff;
    border-left: 6px solid #3498db;
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}

/* ---------- Titres ---------- */
.card h2, .card h3 {
    color: #3498db;
    margin-bottom: 12px;
}
.card h2 {
    font-size: 28px;
}
.card h3 {
    font-size: 20px;
}

/* ---------- Grid ---------- */
.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* ---------- Tableau ---------- */
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
}
.table th, .table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}
.table th {
    background: #3498db;
    color: white;
    font-weight: bold;
}
.table tr:nth-child(even) {
    background: #f0f8ff;
}

/* ---------- Liste ---------- */
ul {
    list-style-type: disc;
    padding-left: 20px;
}
ul li {
    margin-bottom: 6px;
    padding: 4px 0;
}

/* ---------- Placeholders ---------- */
.missing {
    color: #999;
    font-style: italic;
}

/* ---------- Responsive ---------- */
@media (max-width: 800px) {
    .grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="ma-classe">
    <?php
        require_once('../include/liste-des eleve-par-classe.php'); // chemin relatif selon le dossier
        require_once('../include/liste-des-prof.php');
        require_once('../include/variable.php');
    ?>

    <div class="card">
        <h2>Ma classe — 6ème A</h2>
        <p><strong>Professeur principal :</strong> <span class="missing">Mme Rabenja</span> &nbsp; | &nbsp;
        <strong>Effectif :</strong> <span class="missing">10 élèves</span></p>
    </div>

    <div class="grid">
        
        <!-- Carte élève -->
        <div class="card">
            <h3>Élève sélectionné</h3>
            <p><strong>Nom :</strong> <span class="missing"><?php echo $etudiants [0]['nom'].""; ?></span></p>
            <p><strong>Sexe :</strong> <span class="missing"><?php echo $etudiants [0]['sexe'].""; ?></span></p>
            <p><strong>Age :</strong> <span class="missing"><?php echo $etudiants [0]['age'].""; ?></span></p>
            <p><strong>Date de naissance :</strong> <span class="missing"><?php echo $etudiants [0]['date_naissance'].""; ?></span></p>
            <p><strong>Matricule :</strong> <span class="missing"><?php echo $etudiants [0]['classe'].""; ?></span></p>
            <p><strong>Statut actif :</strong> <span class="missing">Oui</span></p>
            
        </div>

        <!-- Profs de la classe -->
        <div class="card">
            <h3>Professeurs de la classe</h3>
            <table class="table">
                <thead>
                    <tr><th>Matière</th><th>Professeur</th></tr>
                </thead>
                <tbody>
                    <tr>
                        
<?php foreach ($professeurs as $professeur): ?>
    <?php if (in_array('6ème A', $professeur['classes'])): ?>
        <tr>
            <td><?php echo $professeur['matiere']; ?></td>
            <td><?php echo $professeur['nom']; ?></td>
        </tr>
    <?php endif; ?>
<?php endforeach; ?>




                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Liste rapide des camarades -->
    <div class="card">
        <h3>Camarades de la classe</h3>
        <ul>
            <?php
            for ($i = 1; $i <= 10; $i++) {
                echo "<li>" . $etudiants[$i]['nom'] . "</li>";
            }
            ?>
        </ul>
    </div>

</div>
</div>

