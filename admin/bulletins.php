<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/admin/bulletin.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>Bulletin</title>
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
            <!-- ==== tableaux ==== -->
            
            <div class="top">
                <h2>COLLEGE D4ENSEIGNENT GENERALE </h2>
                <h4>Rue Françoi de mahy</h4>
                <p>Code :100 010 100 001</p>
                <h1>BULLETIN DES NOTES</h1>
            </div>

            <div class="info-eleve">
                <table>
                    <tr>
                        <td><strong>Nom de l'élève :</strong></td>
                        <td><input type="text" class="input-large" placeholder="Ex: B.M Hamzah"></td>
                        <td><strong>Classe :</strong></td>
                        <td><input type="text" class="input-medium" placeholder="Ex: 3ème A"></td>
                    </tr>
                    <tr>
                        <td><strong>Année scolaire :</strong></td>
                        <td><input type="text" class="input-medium" value="2024 - 2025" readonly></td>
                        <tr>
                        <tr>
                        <td><strong>Sexe :</strong></td>
                        <td class="radio-group">
                            <label><input type="radio" name="sexe_eleve" value="M"> Garçon</label>
                            <label><input type="radio" name="sexe_eleve" value="F"> Fille</label>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>Actualité :</strong></td>
                        <td class="radio-group">
                            <label><input type="radio" name="statut_eleve" value="P"> Passant</label>
                            <label><input type="radio" name="statut_eleve" value="R"> Redoublant</label>
                            <label><input type="radio" name="statut_eleve" value="E"> Exclu</label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ==== SECTION 2 : NOTES ==== -->
            <h3>Notes de l'élève</h3>
            <?php 
                $matieres = [
                    ["Malagasy", 3],
                    ["Français", 2],
                    ["Anglais", 2],
                    ["HIST-GEO", 3],
                    ["MATH", 3],
                    ["PC", 2],
                    ["SVT", 3],
                    ["TICE", 1],
                    ["EPS", 1],
                ];
            ?>
            <table  class="table-notes"> 
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th>Note J</th>
                        <th>Note C</th>
                        <th>Coefficient</th>
                        <th>Moyenne</th>
                        <th>signiature</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($matieres as $m) : ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($m[0]) ?></strong></td>
                            
                            <!-- Note 1 -->
                            <td>
                                <input type="number" 
                                    name="note1[<?= $m[0] ?>]" 
                                    min="0" max="20" step="0.25" 
                                    class="input-note" 
                                    placeholder="0">
                            </td>
                            
                            <!-- Note 2 -->
                            <td>
                                <input type="number" 
                                    name="note2[<?= $m[0] ?>]" 
                                    min="0" max="20" step="0.25" 
                                    class="input-note" 
                                    placeholder="0">
                            </td>
                            
                            <!-- Coefficient (affiché, non modifiable) -->
                            <td class="coeff">
                                <strong><?= $m[1] ?></strong>
                            </td>
                            
                            <!-- Moyenne (calculée automatiquement) -->
                            <td class="moyenne">
                                <input type="number" 
                                    name="note2[<?= $m[0] ?>]" 
                                    min="0" max="20" step="0.25" 
                                    class="input-note" 
                                    placeholder="0">
                            </td>
                            
                            <!-- Appréciation -->
                            <td>
                                <input type="text" 
                                    name="appreciation[<?= $m[0] ?>]" 
                                    class="input-appreciation" 
                                    placeholder="">
                            </td>
                        </tr>
                        <?php endforeach; ?>

                    <tr class="total">
                        <td colspan="4"><strong>Moyenne générale</strong></td>
                        <td id="moyenne-generale">
                            <input type="number" 
                                    name="note2[<?= $m[0] ?>]" 
                                    min="0" max="20" step="0.25" 
                                    class="input-note" 
                                    placeholder="0">
                        </td>
                        <td id="mention-generale"></td>
                    </tr>

                </tbody>
            </table>


            <!-- ==== COMPORTEMENT ==== -->

        </div>
         
    </div>
</body>


