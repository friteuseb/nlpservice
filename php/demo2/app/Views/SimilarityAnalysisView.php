<?php

class SimilarityAnalysisView {
    public function renderComparisonForm($sampleTexts) {
        include 'app/Views/templates/header.php';
        ?>
        <h1>Comparaison de Textes</h1>
        <form action="index.php?action=compare" method="post">
            <label for="text1">Texte 1:</label>
            <textarea name="text1" id="text1" rows="5" cols="50" required></textarea>
            
            <label for="text2">Texte 2:</label>
            <textarea name="text2" id="text2" rows="5" cols="50" required></textarea>
            
            <label for="method">Méthode de comparaison:</label>
            <select name="method" id="method">
                <option value="cosine">Cosinus</option>
                <option value="euclidean">Euclidienne</option>
                <option value="manhattan">Manhattan</option>
                <option value="jaccard">Jaccard</option>
                <option value="bleu">BLEU</option>
            </select>
            
            <button type="submit">Comparer</button>
            <a href="index.php?action=analyzeAllTexts" class="button">Analyser tous les textes</a>
        </form>
        
        <h2>Textes d'exemple</h2>
        <ul>
            <?php foreach ($sampleTexts as $index => $text): ?>
                <li><?php echo htmlspecialchars($text); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php
        include 'app/Views/templates/footer.php';
    }

    public function renderComparisonResult($result) {
        include 'app/Views/templates/header.php';
        ?>
        <h1>Résultat de la Comparaison</h1>
        <div id="similarity-chart"></div>
        <h2>Détails de la Comparaison</h2>
        <pre><?php print_r($result); ?></pre>

        <script src="../Statics/highcharts.js"></script>
        <script>
            Highcharts.chart('similarity-chart', {
                chart: { type: 'bar' },
                title: { text: 'Score de Similarité' },
                xAxis: { categories: ['Score'] },
                yAxis: { title: { text: 'Similarité' }, max: 1 },
                series: [{
                    name: '<?php echo $result['method'] ?? 'Méthode'; ?>',
                    data: [<?php echo $result['similarity'] ?? 0; ?>]
                }]
            });
        </script>
        <?php
        include 'app/Views/templates/footer.php';
    }

    public function renderMultiComparisonResult($results, $sampleTexts) {
        include 'app/Views/templates/header.php';
        ?>
        <h1>Comparaison Multiple de Textes</h1>
        <div id="multi-similarity-chart"></div>
        <h2>Détails des Comparaisons</h2>
        <table>
            <tr>
                <th>Texte 1</th>
                <th>Texte 2</th>
                <th>Cosinus</th>
                <th>Euclidienne</th>
                <th>Manhattan</th>
                <th>Jaccard</th>
                <th>BLEU</th>
            </tr>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars(substr($result['text1'], 0, 30)) . '...'; ?></td>
                    <td><?php echo htmlspecialchars(substr($result['text2'], 0, 30)) . '...'; ?></td>
                    <td><?php echo number_format($result['similarities']['cosine'], 4); ?></td>
                    <td><?php echo number_format($result['similarities']['euclidean'], 4); ?></td>
                    <td><?php echo number_format($result['similarities']['manhattan'], 4); ?></td>
                    <td><?php echo number_format($result['similarities']['jaccard'], 4); ?></td>
                    <td><?php echo number_format($result['similarities']['bleu'], 4); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <script src="../Statics/highcharts.js"></script>
        <script>
            Highcharts.chart('multi-similarity-chart', {
                chart: { type: 'heatmap' },
                title: { text: 'Matrice de Similarité (Cosinus)' },
                xAxis: { categories: <?php echo json_encode(array_map(function($text) { return substr($text, 0, 20) . '...'; }, $sampleTexts)); ?> },
                yAxis: { categories: <?php echo json_encode(array_map(function($text) { return substr($text, 0, 20) . '...'; }, $sampleTexts)); ?> },
                colorAxis: {
                    min: 0,
                    max: 1,
                    stops: [
                        [0, '#3060cf'],
                        [0.5, '#fffbbc'],
                        [1, '#c4463a']
                    ]
                },
                series: [{
                    name: 'Similarité',
                    data: <?php
                        $heatmapData = [];
                        foreach ($results as $index => $result) {
                            $i = array_search($result['text1'], $sampleTexts);
                            $j = array_search($result['text2'], $sampleTexts);
                            $heatmapData[] = [$i, $j, $result['similarities']['cosine']];
                            $heatmapData[] = [$j, $i, $result['similarities']['cosine']];
                        }
                        echo json_encode($heatmapData);
                    ?>
                }]
            });
        </script>
        <?php
        include 'app/Views/templates/footer.php';
    }


    public function renderAllTextsAnalysis($results, $sampleTexts, $metrics, $topSimilar) {
        include 'app/Views/templates/header.php';
        ?>
        <h1>Analyse de Similarité de Tous les Textes</h1>
        
        <h2>Métriques d'Exécution et KPI</h2>
        <div class="kpi-container">
            <div class="kpi-box">
                <div class="kpi-title">Temps d'exécution</div>
                <div class="kpi-value"><?php echo number_format($metrics['executionTime'], 4); ?> s</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Utilisation mémoire</div>
                <div class="kpi-value"><?php echo number_format($metrics['memoryUsage'] / 1024 / 1024, 2); ?> MB</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Nombre de textes</div>
                <div class="kpi-value"><?php echo $metrics['textCount']; ?></div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Nombre de comparaisons</div>
                <div class="kpi-value"><?php echo $metrics['comparisonCount']; ?></div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Similarité moyenne (Cosinus)</div>
                <div class="kpi-value"><?php echo number_format($this->calculateAverageSimilarity($results, 'cosine'), 4); ?></div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Écart-type similarité (Cosinus)</div>
                <div class="kpi-value"><?php echo number_format($this->calculateStandardDeviation($results, 'cosine'), 4); ?></div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Similarité max (Cosinus)</div>
                <div class="kpi-value"><?php echo number_format($this->getMaxSimilarity($results, 'cosine'), 4); ?></div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Similarité min (Cosinus)</div>
                <div class="kpi-value"><?php echo number_format($this->getMinSimilarity($results, 'cosine'), 4); ?></div>
            </div>
        </div>

        <h2>Top 5 des Couples les Plus Similaires</h2>
        <table>
            <tr>
                <th>Texte 1</th>
                <th>Texte 2</th>
                <th>Similarité (Cosinus)</th>
                <th>Similarité (Euclidienne)</th>
                <th>Similarité (Manhattan)</th>
                <th>Similarité (Jaccard)</th>
                <th>Similarité (BLEU)</th>
            </tr>
            <?php foreach ($topSimilar as $pair): ?>
                <tr>
                    <td><?php echo htmlspecialchars(substr($pair['text1'], 0, 30)) . '...'; ?></td>
                    <td><?php echo htmlspecialchars(substr($pair['text2'], 0, 30)) . '...'; ?></td>
                    <td><?php echo number_format($pair['similarities']['cosine'], 4); ?></td>
                    <td><?php echo number_format($pair['similarities']['euclidean'], 4); ?></td>
                    <td><?php echo number_format($pair['similarities']['manhattan'], 4); ?></td>
                    <td><?php echo number_format($pair['similarities']['jaccard'], 4); ?></td>
                    <td><?php echo number_format($pair['similarities']['bleu'], 4); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Matrice de Similarité (Cosinus)</h2>
        <div id="similarity-heatmap" style="width:100%; height:400px;"></div>

        <h2>Distribution des Similarités</h2>
        <div id="similarity-distribution" style="width:100%; height:400px;"></div>

        <h2>Comparaison des Méthodes de Similarité</h2>
        <div id="similarity-methods-comparison" style="width:100%; height:400px;"></div>

        <script>
            // Heatmap de similarité
            Highcharts.chart('similarity-heatmap', {
                chart: { type: 'heatmap' },
                title: { text: 'Matrice de Similarité (Cosinus)' },
                xAxis: { categories: <?php echo json_encode(array_map(function($text) { return substr($text, 0, 20) . '...'; }, $sampleTexts)); ?> },
                yAxis: { categories: <?php echo json_encode(array_map(function($text) { return substr($text, 0, 20) . '...'; }, $sampleTexts)); ?> },
                colorAxis: {
                    min: 0,
                    max: 1,
                    stops: [
                        [0, '#3060cf'],
                        [0.5, '#fffbbc'],
                        [1, '#c4463a']
                    ]
                },
                series: [{
                    name: 'Similarité',
                    data: <?php
                        $heatmapData = [];
                        foreach ($results as $result) {
                            $i = array_search($result['text1'], $sampleTexts);
                            $j = array_search($result['text2'], $sampleTexts);
                            $heatmapData[] = [$i, $j, $result['similarities']['cosine']];
                            $heatmapData[] = [$j, $i, $result['similarities']['cosine']];
                        }
                        echo json_encode($heatmapData);
                    ?>
                }]
            });

            // Distribution des similarités
            Highcharts.chart('similarity-distribution', {
                chart: { type: 'scatter' },
                title: { text: 'Distribution des Similarités' },
                xAxis: { title: { text: 'Méthode de Similarité' } },
                yAxis: { title: { text: 'Score de Similarité' }, min: 0, max: 1 },
                series: [
                    <?php
                    $methods = ['cosine', 'euclidean', 'manhattan', 'jaccard', 'bleu'];
                    foreach ($methods as $index => $method) {
                        echo "{
                            name: '" . ucfirst($method) . "',
                            data: " . json_encode(array_map(function($result) use ($method, $index) {
                                return [$index, $result['similarities'][$method]];
                            }, $results)) . "
                        },";
                    }
                    ?>
                ]
            });

            // Comparaison des méthodes de similarité
            Highcharts.chart('similarity-methods-comparison', {
                chart: { type: 'boxplot' },
                title: { text: 'Comparaison des Méthodes de Similarité' },
                xAxis: { 
                    categories: ['Cosinus', 'Euclidienne', 'Manhattan', 'Jaccard', 'BLEU'],
                    title: { text: 'Méthode de Similarité' }
                },
                yAxis: { 
                    title: { text: 'Score de Similarité' },
                    min: 0,
                    max: 1
                },
                series: [{
                    name: 'Observations',
                    data: [
                        <?php
                        $methods = ['cosine', 'euclidean', 'manhattan', 'jaccard', 'bleu'];
                        foreach ($methods as $method) {
                            $values = array_column(array_column($results, 'similarities'), $method);
                            echo "[" . min($values) . ", " . $this->calculateQuartile($values, 0.25) . ", " . $this->calculateMedian($values) . ", " . $this->calculateQuartile($values, 0.75) . ", " . max($values) . "],";
                        }
                        ?>
                    ],
                    tooltip: {
                        headerFormat: '<em>Méthode de similarité {point.key}</em><br/>'
                    }
                }]
            });
        </script>
        <?php
        include 'app/Views/templates/footer.php';
    }

    private function calculateAverageSimilarity($results, $method) {
        $sum = array_sum(array_column(array_column($results, 'similarities'), $method));
        return $sum / count($results);
    }

    private function calculateStandardDeviation($results, $method) {
        $avg = $this->calculateAverageSimilarity($results, $method);
        $variance = array_sum(array_map(function($x) use ($avg, $method) {
            return pow($x['similarities'][$method] - $avg, 2);
        }, $results)) / count($results);
        return sqrt($variance);
    }

    private function getMaxSimilarity($results, $method) {
        return max(array_column(array_column($results, 'similarities'), $method));
    }

    private function getMinSimilarity($results, $method) {
        return min(array_column(array_column($results, 'similarities'), $method));
    }

    private function calculateMedian($arr) {
        sort($arr);
        $count = count($arr);
        $middleval = floor(($count-1)/2);
        if($count % 2) {
            $median = $arr[$middleval];
        } else {
            $low = $arr[$middleval];
            $high = $arr[$middleval+1];
            $median = (($low+$high)/2);
        }
        return $median;
    }

    private function calculateQuartile($arr, $quartile) {
        sort($arr);
        $pos = (count($arr) - 1) * $quartile;
        $base = floor($pos);
        $rest = $pos - $base;
        if( isset($arr[$base+1]) ) {
            return $arr[$base] + $rest * ($arr[$base+1] - $arr[$base]);
        } else {
            return $arr[$base];
        }
    }
}
    ?>

