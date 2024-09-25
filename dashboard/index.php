<?php
require_once __DIR__ . '/src/Config/config.php';
require_once __DIR__ . '/src/Services/DataVisualizer.php';

$visualizer = new DataVisualizer();

// Charger les analyses
$analyses = [];
$files = glob(ANALYSES_DIRECTORY . '/*_analysis.json');
foreach ($files as $file) {
    $textName = basename($file, '_analysis.json');
    $analysisContent = file_get_contents($file);
    if ($analysisContent !== false) {
        $analyses[$textName] = json_decode($analysisContent, true);
    }
}

// Vérifier s'il y a des analyses disponibles
$analysesAvailable = !empty($analyses);

if ($analysesAvailable) {
    // Calculer les similarités (si nécessaire et disponible dans les analyses)
    $similarities = [];
    $textNames = array_keys($analyses);
    for ($i = 0; $i < count($textNames); $i++) {
        for ($j = $i + 1; $j < count($textNames); $j++) {
            $text1 = $textNames[$i];
            $text2 = $textNames[$j];
            // Vérifier si la similarité est stockée dans les analyses
            if (isset($analyses[$text1]['similarities'][$text2])) {
                $similarities[$text1 . '_' . $text2] = $analyses[$text1]['similarities'][$text2];
            }
        }
    }

    // Extraire les thèmes dominants
    $themes = [];
    foreach ($analyses as $analysis) {
        if (isset($analysis['keyphrases'])) {
            foreach ($analysis['keyphrases'] as $keyphrase) {
                if (!isset($themes[$keyphrase])) {
                    $themes[$keyphrase] = 0;
                }
                $themes[$keyphrase]++;
            }
        }
    }
    arsort($themes);
    $themes = array_slice($themes, 0, 10); // Garder les 10 thèmes les plus fréquents

    // Préparer les données pour la visualisation
    $similarityGraph = $visualizer->prepareSimilarityGraph($similarities);
    $themeClusterGraph = $visualizer->prepareThemeClusterGraph($themes);
    $kpiData = $visualizer->prepareKPIData($analyses);

    // Calculs des KPI et graphiques
    $totalWords = 0;
    $totalSentences = 0;
    $sentiments = ['positive' => 0, 'neutral' => 0, 'negative' => 0];
    $entities = [];

    foreach ($analyses as $analysis) {
        $totalWords += $analysis['word_count'] ?? 0;
        $totalSentences += $analysis['sentence_count'] ?? 0;
        $sentiments[$analysis['sentiment_analysis']['overall_sentiment'] ?? 'neutral']++;
        if (isset($analysis['named_entities'])) {
            $entities = array_merge($entities, $analysis['named_entities']);
        }
    }

    $avgWordsPerSentence = $totalSentences > 0 ? $totalWords / $totalSentences : 0;
    $topEntities = array_slice(array_count_values($entities), 0, 5, true);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard d'Analyse de Texte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/networkgraph.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Dashboard d'Analyse de Texte</h1>
        
        <div class="mb-3">
            <a href="/dashboard/public/similarity_test.php" class="btn btn-secondary">Test de Similarité</a>
            <a href="/dashboard/public/batch_analysis.php" class="btn btn-primary">Analyse par Lot</a>
        </div>

        <?php if (!$analysesAvailable): ?>
            <div class="alert alert-warning">
                Aucune analyse n'est disponible. Veuillez lancer une analyse par lot pour voir les résultats.
            </div>
        <?php else: ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <h2>KPI Globaux</h2>
                    <div id="kpiContainer"></div>
                </div>
                <div class="col-md-6">
                    <h2>Graphe de Similarité</h2>
                    <div id="similarityGraph"></div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <h2>Thèmes Dominants</h2>
                    <div id="themeClusterGraph"></div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <h2>Répartition des sentiments</h2>
                    <div id="sentimentChart"></div>
                </div>
                <div class="col-md-6">
                    <h2>Top 5 des entités nommées</h2>
                    <div id="entityChart"></div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Nombre total de mots</h5>
                            <p class="card-text"><?php echo $totalWords; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Nombre total de phrases</h5>
                            <p class="card-text"><?php echo $totalSentences; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Moyenne de mots par phrase</h5>
                            <p class="card-text"><?php echo number_format($avgWordsPerSentence, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>


            <script>
            // Graphe de similarité
            Highcharts.chart('similarityGraph', <?php echo json_encode($similarityGraph); ?>);

            // Graphe de cluster des thèmes
            Highcharts.chart('themeClusterGraph', <?php echo json_encode($themeClusterGraph); ?>);

            // Affichage des KPI
            const kpiContainer = document.getElementById('kpiContainer');
            const kpiData = <?php echo json_encode($kpiData); ?>;
            
            for (const [kpi, value] of Object.entries(kpiData)) {
                const kpiElement = document.createElement('div');
                kpiElement.className = 'card mb-3';
                kpiElement.innerHTML = `
                    <div class="card-body">
                        <h5 class="card-title">${kpi}</h5>
                        <p class="card-text">${value}</p>
                    </div>
                `;
                kpiContainer.appendChild(kpiElement);
            }
            </script>
            <script>
                // Graphique de répartition des sentiments
                Highcharts.chart('sentimentChart', {
                    chart: { type: 'pie' },
                    title: { text: 'Répartition des sentiments' },
                    series: [{
                        name: 'Sentiments',
                        data: [
                            ['Positif', <?php echo $sentiments['positive']; ?>],
                            ['Neutre', <?php echo $sentiments['neutral']; ?>],
                            ['Négatif', <?php echo $sentiments['negative']; ?>]
                        ]
                    }]
                });

                // Graphique des top entités nommées
                Highcharts.chart('entityChart', {
                    chart: { type: 'bar' },
                    title: { text: 'Top 5 des entités nommées' },
                    xAxis: { categories: <?php echo json_encode(array_keys($topEntities)); ?> },
                    yAxis: { title: { text: 'Nombre d\'occurrences' } },
                    series: [{
                        name: 'Occurrences',
                        data: <?php echo json_encode(array_values($topEntities)); ?>
                    }]
                });
            </script>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>