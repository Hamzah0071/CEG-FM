<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Notes - CEG Rue François de Mahy</title>
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f4f4f4;
        }
        .parent { width: 100%; }
        .div3 {
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 1100px;
        }

        .top {
            text-align: center;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .top h2 { font-size: 24px; color: #1e3d8e; margin: 15px 0; }
        .top h4 { font-weight: bold; }

        /* Zone de contrôle : matricule + trimestre */
        .controls {
            text-align: center;
            margin: 30px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        #matriculeInput {
            padding: 16px 25px;
            width: 420px;
            max-width: 90%;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            border: 3px solid #333;
            border-radius: 12px;
            letter-spacing: 3px;
        }
        #matriculeInput:focus { border-color: #0066cc; box-shadow: 0 0 15px rgba(0,102,204,0.4); }

        select#trimestreSelect {
            padding: 14px 20px;
            font-size: 18px;
            border: 3px solid #333;
            border-radius: 10px;
            background: white;
            cursor: pointer;
        }

        .no-result {
            text-align: center;
            color: #d00;
            font-size: 22px;
            margin: 80px 0;
            font-weight: bold;
        }

        /* Un seul trimestre visible à la fois */
        .trimestre {
            border: 3px solid black;
            padding: 20px;
            background: white;
            display: none;
            page-break-after: always; /* pour impression propre */
        }
        .trimestre.visible { display: block; }

        .info { font-size: 14px; margin-bottom: 10px; line-height: 1.6; }
        .info span { display: inline-block; width: 170px; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 15px; }
        th, td { border: 1px solid black; padding: 6px; text-align: center; }
        th { background: #e0e0e0; font-weight: bold; }
        input[type="number"], input[type="text"] {
            border: none;
            border-bottom: 1px dotted #000;
            width: 100%;
            text-align: center;
            padding: 3px;
        }
        .total td { font-weight: bold; background: #f0f0f0; }
        .bottom { margin-top: 15px; font-size: 14px; font-weight: bold; }
        .signature { margin-top: 50px; text-align: right; font-size: 13px; }
    </style>
</head>
<body>

<div class="parent">
    <?php require_once('../include/header.php'); ?>
    <div class="div3">

        <div class="top">
            <h3>COLLÈGE D'ENSEIGNEMENT GÉNÉRAL</h3>
            <h4>RUE FRANÇOIS DE MAHY</h4>
            <p>Code : 201 010 001</p>
            <h2>BULLETIN DE NOTES</h2>
            <p>Année Scolaire 2025 / 2026</p>
        </div>

        <!-- Contrôles : matricule + choix du trimestre -->
        <div class="controls">
            <input type="text" id="matriculeInput" placeholder="Matricule (ex: 20256A001)" autocomplete="off" autofocus>
            <select id="trimestreSelect">
                <option value="1">1er Trimestre</option>
                <option value="2">2ème Trimestre</option>
                <option value="3">3ème Trimestre</option>
            </select>
        </div>

        <div id="noResult" class="no-result" style="display:none;">
            Matricule introuvable. Vérifiez la saisie.
        </div>

        <!-- Conteneur du bulletin -->
        <div id="bulletinContainer"></div>

    </div>
</div>

<?php 
require_once(__DIR__ . '/../include/liste-des eleve-par-classe.php');
require_once(__DIR__ . '/../include/variable.php'); 
?>

<script>
// Données
const eleves = <?php echo json_encode(array_values($etudiants)); ?>;
const matieres = <?php echo json_encode($liste_matieres); ?>;

const inputMatricule = document.getElementById('matriculeInput');
const selectTrimestre = document.getElementById('trimestreSelect');
const container = document.getElementById('bulletinContainer');
const noResult = document.getElementById('noResult');

let eleveActuel = null;

function genererUnTrimestre(eleve, numero) {
    const suffixe = ["", "1er", "2ème", "3ème"];
    const titre = suffixe[numero] + " TRIMESTRE";

    let html = `
    <div class="trimestre" data-trimestre="${numero}">
        <h3 style="text-align:center; text-decoration:underline; margin-bottom:20px; font-size:18px;">
            ${titre}
        </h3>

        <div class="info"><span>NOM & PRÉNOMS</span>: ${eleve.nom}</div>
        <div class="info"><span>MATRICULE</span>: <strong>${eleve.matricule}</strong></div>
        <div class="info"><span>Date de naissance</span>: ${eleve.date_naissance}</div>
        <div class="info">
            Sexe : 
            <input type="radio" ${eleve.sexe === 'F' ? 'checked' : ''} disabled> Fille
            <input type="radio" ${eleve.sexe === 'M' ? 'checked' : ''} disabled> Garçon
            &nbsp;&nbsp;&nbsp; Classe : <strong>${eleve.classe}</strong>
        </div>

        <table>
            <thead>
                <tr><th>Matières</th><th>NI</th><th>NC</th><th>Coef</th><th>ND</th><th>Appréciation</th></tr>
            </thead>
            <tbody>`;

    matieres.forEach(m => {
        html += `
            <tr>
                <td>${m[0]}</td>
                <td><input class="ni" type="number" min="0" max="20" step="0.5"></td>
                <td><input class="nc" type="number" min="0" max="20" step="0.5"></td>
                <td class="coef">${m[1]}</td>
                <td><input class="nd" type="number" min="0" max="20" step="0.5" readonly></td>
                <td><input type="text"></td>
            </tr>`;
    });

    html += `
            </tbody>
        </table>

        <div class="bottom">
            Moyenne du trimestre : <span class="moyenne">......</span> / 20<br>
            Moyenne annuelle : ...... / 20<br>
            Rang : ...... sur ...... élèves
        </div>

        <div class="signature">
            Antsiranana, le ....................................<br><br><br><br>
            Le Directeur
        </div>
    </div>`;

    return html;
}

function afficherBulletin(eleve) {
    eleveActuel = eleve;
    container.innerHTML = genererUnTrimestre(eleve, 1) + genererUnTrimestre(eleve, 2) + genererUnTrimestre(eleve, 3);
    noResult.style.display = 'none';

    // Afficher uniquement le trimestre sélectionné
    changerTrimestre();

    // Activer les calculs
    setTimeout(activerCalculs, 100);
}

function changerTrimestre() {
    const choix = selectTrimestre.value;
    document.querySelectorAll('.trimestre').forEach(t => {
        t.classList.toggle('visible', t.dataset.trimestre === choix);
    });
}

// Recherche par matricule
inputMatricule.addEventListener('input', function() {
    const recherche = this.value.trim().toUpperCase();
    if (recherche === '') {
        container.innerHTML = '';
        noResult.style.display = 'none';
        return;
    }

    const eleve = eleves.find(e => e.matricule && e.matricule.toUpperCase().includes(recherche));
    if (eleve && eleve.is_enabled !== false) {
        afficherBulletin(eleve);
    } else {
        container.innerHTML = '';
        noResult.style.display = 'block';
    }
});

// Changer de trimestre
selectTrimestre.addEventListener('change', changerTrimestre);

/* Calcul automatique */
function activerCalculs() {
    document.querySelectorAll('.trimestre.visible tbody tr').forEach(row => {
        const ni = row.querySelector('.ni');
        const nc = row.querySelector('.nc');
        const nd = row.querySelector('.nd');
        const coefCell = row.querySelector('.coef');

        if (!ni || !nc || !nd) return;

        const update = () => {
            const n1 = parseFloat(ni.value) || 0;
            const n2 = parseFloat(nc.value) || 0;
            nd.value = ((n1 + n2) / 2).toFixed(2);
            calculerMoyenneTrimestre();
        };

        ni.addEventListener('input', update);
        nc.addEventListener('input', update);
    });

    calculerMoyenneTrimestre();
}

function calculerMoyenneTrimestre() {
    const trimestre = document.querySelector('.trimestre.visible');
    if (!trimestre) return;

    let totalPoints = 0;
    let totalCoef = 0;

    trimestre.querySelectorAll('tbody tr').forEach(row => {
        const coef = parseFloat(row.querySelector('.coef')?.textContent) || 0;
        const nd = parseFloat(row.querySelector('.nd')?.value) || 0;
        totalPoints += nd * coef;
        totalCoef += coef;
    });

    const moyenne = totalCoef > 0 ? (totalPoints / totalCoef).toFixed(2) : '0.00';
    trimestre.querySelector('.moyenne').textContent = moyenne;
}

// Focus au démarrage
window.addEventListener('load', () => inputMatricule.focus());
</script>

</body>
</html>