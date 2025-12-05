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
        .absences-container { font-family: 'Times New Roman', serif; max-width: 1000px; margin: 0 auto; }
        .header-absences { text-align: center; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border: 2px solid #333; }
        .header-absences h2 { margin: 10px 0; font-size: 24px; color: #1e3d8e; }
        .info-eleve { background: #e9f7ff; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 15px; }
        .info-eleve strong { color: #333; }

        .table-absences { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        .table-absences th { background: #d0e6ff; padding: 12px 8px; border: 2px solid #333; text-align: center; font-weight: bold; }
        .table-absences td { border: 1px solid #333; padding: 12px 8px; vertical-align: top; }
        .matiere { background: #f0f8ff; font-weight: bold; width: 180px; }
        .dates-absences { min-height: 60px; line-height: 1.6; }
        .total-row { background: #fff3cd !important; font-weight: bold; font-size: 16px; }
        .total-row td { text-align: center; color: #d00; }

        .no-absence { color: #28a745; font-style: italic; }
        .footer-date { text-align: center; margin-top: 40px; font-size: 14px; color: #555; }
    </style>

    <div class="absences-container">

        <!-- En-tête -->
        <div class="header-absences">
            <h2>FICHE INDIVIDUELLE DES ABSENCES</h2>
            <p>Année scolaire 2025 / 2026</p>
        </div>

        <!-- Infos élève -->
        <div class="info-eleve">
            <strong>Nom et prénoms :</strong> ANDRIANASOLO Tianà<br>
            <strong>Matricule :</strong> 20256A001<br>
            <strong>Classe :</strong> 6ème A<br>
            <strong>Date de naissance :</strong> 12-05-2014
        </div>

        <!-- Tableau des absences par matière -->
        <table class="table-absences">
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Dates et motifs d'absence</th>
                    <th>Total<br>(jours)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="matiere">Malagasy</td>
                    <td class="dates-absences"><span class="no-absence">Aucune absence</span></td>
                    <td>0</td>
                </tr>
                <tr>
                    <td class="matiere">Français</td>
                    <td class="dates-absences">
                        15/10/2025 – Maladie<br>
                        20/11/2025 – Motif familial
                    </td>
                    <td>2</td>
                </tr>
                <tr>
                    <td class="matiere">Anglais</td>
                    <td class="dates-absences"><span class="no-absence">Aucune absence</span></td>
                    <td>0</td>
                </tr>
                <tr>
                    <td class="matiere">HISTO-GEO</td>
                    <td class="dates-absences"><span class="no-absence">Aucune absence</span></td>
                    <td>0</td>
                </tr>
                <tr>
                    <td class="matiere">MATH</td>
                    <td class="dates-absences">03/12/2025 – Raison médicale</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td class="matiere">PC</td>
                    <td class="dates-absences"><span class="no-absence">Aucune absence</span></td>
                    <td>0</td>
                </tr>
                <tr>
                    <td class="matiere">SVT</td>
                    <td class="dates-absences"><span class="no-absence">Aucune absence</span></td>
                    <td>0</td>
                </tr>
                <tr>
                    <td class="matiere">TICE</td>
                    <td class="dates-absences"><span class="no-absence">Aucune absence</span></td>
                    <td>0</td>
                </tr>
                <tr>
                    <td class="matiere">EPS</td>
                    <td class="dates-absences"><span class="no-absence">Aucune absence</span></td>
                    <td>0</td>
                </tr>

                <!-- Ligne totale -->
                <tr class="total-row">
                    <td colspan="2">TOTAL GÉNÉRAL DES ABSENCES</td>
                    <td>3 jours</td>
                </tr>
            </tbody>
        </table>

        <div class="footer-date">
            Fiche mise à jour le 04 décembre 2025<br>
            <em>Imprimée par le secrétariat du CEG Rue François de Mahy</em>
        </div>
    </div>
</div>