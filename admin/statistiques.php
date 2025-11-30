<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Bulletins - CEG FM</title>
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            color: #333;
        }
        .parent { display: flex; min201min-height: 100vh; }
        .div3 {
            flex: 1;
            padding: 30px;
            background: #f4f6f9;
        }

        /* En-tête de la page */
        .page-title {
            font-size: 28px;
            margin-bottom: 10px;
            color: #1e40af;
            text-align: center;
        }
        .page-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        /* Filtres */
        .filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            align-items: center;
        }
        .filters select, .filters button {
            padding: 10px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }
        .filters button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .filters button:hover { background: #1d4ed8; }

        /* Cartes statistiques */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.07);
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-8px); }
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #1e40af;
        }
        .stat-card.success .value { color: #16a34a; }
        .stat-card.warning .value { color: #ca8a04; }
        .stat-card.danger .value { color: #dc2626; }

        /* Graphiques */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }
        .chart-box {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.07);
        }
        .chart-title {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            color: #1e40af;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .div3 { padding: 15px; }
            .filters { flex-direction: column; }
            .charts-container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="parent">
    <?php require_once('../include/header.php'); ?>

    <div class="div3">
        <h1 class="page-title">Statistiques des Bulletins</h1>
        <p class="page-subtitle">Suivi global des performances scolaires</p>

        <!-- Filtres -->
        <div class="filters">
            <select id="annee">
                <option>2024-2025</option>
                <option>2023-2024</option>
                <option>2022-2023</option>
            </select>
            <select id="trimestre">
                <option>Tous les trimestres</option>
                <option>1er Trimestre</option>
                <option>2ème Trimestre</option>
                <option>3ème Trimestre</option>
            </select>
            <select id="classe">
                <option>Toutes les classes</option>
                <option>6ème</option>
                <option>5ème</option>
                <option>4ème</option>
                <option>3ème</option>
            </select>
            <button onclick="filtrer()">Appliquer le filtre</button>
        </div>

        <!-- Cartes statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Élèves</h3>
                <div class="value">342</div>
            </div>
            <div class="stat-card success">
                <h3>Taux de Réussite</h3>
                <div class="value">78.4%</div>
            </div>
            <div class="stat-card warning">
                <h3>Moyenne Générale</h3>
                <div class="value">12.8</div>
            </div>
            <div class="stat-card danger">
                <h3>Échecs</h3>
                <div class="value">21.6%</div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="charts-container">
            <!-- Réussite par classe -->
            <div class="chart-box">
                <h3 class="chart-title">Taux de réussite par classe</h3>
                <canvas id="chartReussite"></canvas>
            </div>

            <!-- Répartition des moyennes -->
            <div class="chart-box">
                <h3 class="chart-title">Répartition des moyennes</h3>
                <canvas id="chartMoyennes"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Données statiques (à remplacer plus tard par du PHP réel)
const dataReussite = {
    labels: ['6ème', '5ème', '4ème', '3ème'],
    datasets: [{
        label: 'Taux de réussite (%)',
        data: [82, 76, 79, 74],
        backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

const dataMoyennes = {
    labels: ['<10', '10-12', '12-14', '14-16', '16-18', '≥18'],
    datasets: [{
        data: [45, 78, 95, 62, 38, 24],
        backgroundColor: [
            '#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e', '#10b981'
        ]
    }]
};

// Graphique Réussite par classe (barres)
new Chart(document.getElementById('chartReussite'), {
    type: 'bar',
    data: dataReussite,
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
        }
    }
});

// Graphique Répartition des moyennes (circulaire)
new Chart(document.getElementById('chartMoyennes'), {
    type: 'doughnut',
    data: dataMoyennes,
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.parsed} élèves` } }
        }
    }
});

// Fonction filtre (à connecter avec PHP plus tard)
function filtrer() {
    alert("Filtre appliqué ! (À connecter avec PHP pour données dynamiques)");
    // Ici tu pourras faire une requête AJAX ou recharger la page avec paramètres GET
}
</script>

</body>
</html>