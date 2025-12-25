<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Notes - CEG Rue François de Mahy</title>
    <style>
        
        .page { 
            width: 210mm; 
            min-height: 297mm; 
            margin: 0 auto; 
            background: white; 
            padding: 10mm; 
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
        }
        .header { 
            text-align: center; 
            font-size: 13px; 
            margin-bottom: 8px; 
        }
        .header h1 { 
            font-size: 19px; 
            margin: 4px 0; 
            font-weight: bold; 
        }
        .columns { 
            display: flex; 
            justify-content: space-between; 
            gap: 10px; 
        }
        .trimestre { 
            width: 33%; 
            border: 2px solid black; 
            padding: 8px; 
            font-size: 11.5px; 
            line-height: 1.3; 
            box-sizing: border-box; 
        }
        .trimestre h3 { 
            text-align: center; 
            text-decoration: underline; 
            font-size: 13px; 
            margin: 0 0 8px 0; 
        }
        .info { 
            margin-bottom: 5px; 
        }
        .info span { 
            font-weight: bold; 
            display: inline-block; 
            width: 90px; 
        }
        .checkboxes { 
            margin: 5px 0; 
            font-size: 10.5px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 6px 0; 
            font-size: 10.8px; 
        }
        th, td { 
            border: 1px solid black; 
            padding: 3px 2px; 
            text-align: center; 
        }
        th { 
            background: #e0e0e0; 
            font-weight: bold; 
        }
        .total { 
            font-weight: bold; 
        }
        .bottom { 
            margin-top: 6px; 
            font-weight: bold; 
            font-size: 11px; 
        }
        .decision { 
            margin-top: 8px; 
            font-size: 9.5px; 
            line-height: 1.3; 
        }
        .signature { 
            margin-top: 15px; 
            text-align: right; 
            font-size: 10.5px; 
        }
        @media print {
            body { background: white; padding: 0; }
            .page { box-shadow: none; padding: 8mm; }
        }
    </style>
</head>
<body>
   <div class="parent">
        <?php
        require_once('../include/header.php'); // chemin relatif selon le dossier
        ?>
        <div class="div3">
            <div class="page">
                <div class="header">
                    <div><strong>COLLÈGE D'ENSEIGNEMENT GÉNÉRAL RUE FRANÇOIS DE MAHY</strong></div>
                    <div>Code : 201 010 001</div>
                    <h1>BULLETIN DE NOTES</h1>
                    <div>Année Scolaire 20__ / 20__</div>
                </div>

                <div class="columns">
                    <!-- 1er TRIMESTRE -->
                    <div class="trimestre">
                        <h3>1<sup>er</sup> TRIMESTRE</h3>
                        <div class="info"><span>NOM ET PRÉNOMS</span> : _________________________</div>
                        <div class="info"><span>Date de Naissance</span> : _____________________</div>
                        <div class="checkboxes">
                            <input type="checkbox"> Fille <input type="checkbox"> Garçon 
                            <input type="checkbox"> Passant(e) <input type="checkbox"> Redoublant(e)
                            &nbsp;&nbsp; Classe : __________
                        </div>

                        <table>
                            <thead>
                                <tr><th>Matières</th><th>NI</th><th>NC</th><th>Coef</th><th>ND</th><th>Appréciation<br>et signatures</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Malagasy</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>Français</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>Anglais</td><td></td><td></td><td>02</td><td></td><td></td></tr>
                                <tr><td>HIST-GEO</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>MATH</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>PC</td><td></td><td></td><td>02</td><td></td><td></td></tr>
                                <tr><td>SVT</td><td></td><td></td><td>02</td><td></td><td></td></tr>
                                <tr><td>TIC</td><td></td><td></td><td>01</td><td></td><td></td></tr>
                                <tr><td>EPS</td><td></td><td></td><td>01</td><td></td><td></td></tr>
                                <tr class="total"><td>TOTAL</td><td></td><td></td><td>20</td><td></td><td></td></tr>
                            </tbody>
                        </table>

                        <div class="bottom">
                            Moyenne ........ /20<br>
                            Moyenne annuelle ........ /20<br>
                            Rang : ........ sur ........ élèves
                        </div>

                        <div class="decision">
                            <strong>Décision du conseil</strong><br>
                            Félicitations - Tableau d’honneur - Passable<br>
                            Avertissement conduite - Avertissement travail<br>
                            Exclu pour ...
                        </div>
                        <div class="signature">
                            Antsiranana, le ....................<br><br><br><br>
                            Le Directeur du CEG
                        </div>
                    </div>

                    <!-- 2ème TRIMESTRE -->
                    <div class="trimestre">
                        <h3>2<sup>ème</sup> TRIMESTRE</h3>
                        <div class="info"><span>NOM ET PRÉNOMS</span> : _________________________</div>
                        <div class="info"><span>Date de Naissance</span> : _____________________</div>
                        <div class="checkboxes">
                            <input type="checkbox"> Fille <input type="checkbox"> Garçon 
                            <input type="checkbox"> Passant(e) <input type="checkbox"> Redoublant(e)
                            &nbsp;&nbsp; Classe : __________
                        </div>

                        <table>
                            <thead>
                                <tr><th>Matières</th><th>NI</th><th>NC</th><th>Coef</th><th>ND</th><th>Appréciation<br>et signatures</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Malagasy</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>Français</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>Anglais</td><td></td><td></td><td>02</td><td></td><td></td></tr>
                                <tr><td>HIST-GEO</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>MATH</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>PC</td><td></td><td></td><td>02</td><td></td><td></td></tr>
                                <tr><td>SVT</td><td></td><td></td><td>02</td><td></td><td></td></tr>
                                <tr><td>TIC</td><td></td><td></td><td>01</td><td></td><td></td></tr>
                                <tr><td>EPS</td><td></td><td></td><td>01</td><td></td><td></td></tr>
                                <tr class="total"><td>TOTAL</td><td></td><td></td><td>20</td><td></td><td></td></tr>
                            </tbody>
                        </table>

                        <div class="bottom">
                            Moyenne ........ /20<br>
                            Moyenne annuelle ........ /20<br>
                            Rang : ........ sur ........ élèves
                        </div>

                        <div class="decision">
                            <strong>Décision du conseil</strong><br>
                            Félicitations - Tableau d’honneur - Passable<br>
                            Avertissement conduite - Avertissement travail<br>
                            Exclu pour ...
                        </div>
                        <div class="signature">
                            Antsiranana, le ....................<br><br><br><br>
                            Le Directeur du CEG
                        </div>
                    </div>

                    <!-- 3ème TRIMESTRE -->
                    <div class="trimestre">
                        <h3>3<sup>ème</sup> TRIMESTRE</h3>
                        <div class="info"><span>NOM ET PRÉNOMS</span> : _________________________</div>
                        <div class="info"><span>Date de Naissance</span> : _____________________</div>
                        <div class="checkboxes">
                            <input type="checkbox"> Fille <input type="checkbox"> Garçon 
                            <input type="checkbox"> Passant(e) <input type="checkbox"> Redoublant(e)
                            &nbsp;&nbsp; Classe : __________
                        </div>

                        <table>
                            <thead>
                                <tr><th>Matières</th><th>NI</th><th>NC</th><th>Coef</th><th>ND</th><th>Appréciation<br>et signatures</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Malagasy</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>Français</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>Anglais</td><td></td><td></td><td>02</td><td></td><td></td></tr>
                                <tr><td>HIST-GEO</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>MATH</td><td></td><td></td><td>03</td><td></td><td></td></tr>
                                <tr><td>PC</td><td></td><td></td><td>02</td><td></td><td></td></tr>
                                <tr><td>SVT</td><td></td><td></td><td>02</td><td></td><td></td></tr>
                                <tr><td>TIC</td><td></td><td></td><td>01</td><td></td><td></td></tr>
                                <tr><td>EPS</td><td></td><td></td><td>01</td><td></td><td></td></tr>
                                <tr class="total"><td>TOTAL</td><td></td><td></td><td>20</td><td></td><td></td></tr>
                            </tbody>
                        </table>

                        <div class="bottom">
                            Moyenne ........ /20<br>
                            Moyenne annuelle ........ /20<br>
                            Rang : ........ sur ........ élèves
                        </div>

                        <div class="decision">
                            <strong>Décision du conseil</strong><br>
                            Félicitations - Tableau d’honneur - Passable<br>
                            Avertissement conduite - Avertissement travail<br>
                            Exclu pour ...
                        </div>
                        <div class="signature">
                            Antsiranana, le ....................<br><br><br><br>
                            Le Directeur du CEG
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>