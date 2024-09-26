<?php

class TextAnalysisView {
    public function renderIndex($sampleTexts) {
        include 'app/Views/templates/header.php';
        ?>
        <h1>Analyse de Texte</h1>
        <form action="index.php?action=analyze" method="post">
            <textarea name="text" rows="10" cols="50" required></textarea>
            <button type="submit">Analyser</button>
        </form>
        <h2>Textes d'exemple</h2>
        <ul>
            <?php foreach ($sampleTexts as $text): ?>
                <li><?php echo htmlspecialchars($text); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php
        include 'app/Views/templates/footer.php';
    }

    public function renderAnalysisForm() {
        include 'app/Views/templates/header.php';
        ?>
        <h1>Analyse de Texte</h1>
        <form action="index.php?action=analyze" method="post">
            <textarea name="text" rows="10" cols="50" required></textarea>
            <button type="submit">Analyser</button>
        </form>
        <?php
        include 'app/Views/templates/footer.php';
    }

    public function renderAnalysisResult($result) {
        include 'app/Views/templates/header.php';
        ?>
        <h1>Résultats de l'Analyse de Texte</h1>

        <div class="kpi-container">
            <div class="kpi-box">
                <div class="kpi-title">Score de Sentiment</div>
                <div class="kpi-value"><?php echo number_format($result['sentiment_analysis']['score'], 2); ?></div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Sentiment Dominant</div>
                <div class="kpi-value"><?php echo ucfirst($result['sentiment_analysis']['label']); ?></div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Nombre d'Entités</div>
                <div class="kpi-value"><?php echo count($result['named_entities']); ?></div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Nombre de Mots-clés</div>
                <div class="kpi-value"><?php echo count($result['keyphrases']); ?></div>
            </div>
        </div>

        <h2>Analyse des Sentiments</h2>
        <div id="sentiment-chart" style="width:100%; height:400px;"></div>

        <h2>Entités Nommées</h2>
        <div id="entities-chart" style="width:100%; height:400px;"></div>
        <table>
            <tr>
                <th>Entité</th>
                <th>Type</th>
                <th>Lien Wikipedia</th>
            </tr>
            <?php foreach ($result['named_entities'] as $entity): ?>
                <tr>
                    <td><?php echo htmlspecialchars($entity['text']); ?></td>
                    <td><?php echo htmlspecialchars($entity['type']); ?></td>
                    <td>
                        <?php if (isset($entity['wikipedia_url'])): ?>
                            <a href="<?php echo htmlspecialchars($entity['wikipedia_url']); ?>" target="_blank">Voir sur Wikipedia</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Mots-clés</h2>
        <div id="keywords-chart" style="width:100%; height:400px;"></div>

        <h2>Texte Annoté</h2>
        <div class="annotated-text">
            <?php echo $this->renderAnnotatedText($result['text'], $result['named_entities']); ?>
        </div>

        <script>
            // Graphique des sentiments
            Highcharts.chart('sentiment-chart', {
                chart: { type: 'pie' },
                title: { text: 'Distribution des Sentiments' },
                series: [{
                    name: 'Sentiment',
                    data: [
                        ['Positif', <?php echo $result['sentiment_analysis']['sentiment_distribution']['POSITIVE'] ?? 0; ?>],
                        ['Négatif', <?php echo $result['sentiment_analysis']['sentiment_distribution']['NEGATIVE'] ?? 0; ?>],
                        ['Neutre', <?php echo $result['sentiment_analysis']['sentiment_distribution']['NEUTRAL'] ?? 0; ?>]
                    ]
                }]
            });

            // Graphique des entités nommées
            Highcharts.chart('entities-chart', {
                chart: { type: 'column' },
                title: { text: 'Types d\'Entités Nommées' },
                xAxis: { 
                    categories: <?php echo json_encode(array_unique(array_column($result['named_entities'], 'type'))); ?>,
                    title: { text: 'Type d\'Entité' }
                },
                yAxis: { 
                    title: { text: 'Nombre d\'Occurrences' }
                },
                series: [{
                    name: 'Occurrences',
                    data: <?php 
                        $entityCounts = array_count_values(array_column($result['named_entities'], 'type'));
                        echo json_encode(array_values($entityCounts));
                    ?>
                }]
            });

            // Graphique des mots-clés
            Highcharts.chart('keywords-chart', {
                chart: { type: 'bar' },
                title: { text: 'Mots-clés Principaux' },
                xAxis: { 
                    categories: <?php echo json_encode(array_column($result['keyphrases'], 'text')); ?>,
                    title: { text: 'Mot-clé' }
                },
                yAxis: { 
                    title: { text: 'Score' },
                    min: 0,
                    max: 1
                },
                series: [{
                    name: 'Score',
                    data: <?php echo json_encode(array_column($result['keyphrases'], 'score')); ?>
                }]
            });
        </script>
        <?php
        include 'app/Views/templates/footer.php';
    }

    private function renderAnnotatedText($text, $entities) {
        $annotatedText = $text;
        foreach ($entities as $entity) {
            $replacement = '<span class="entity" title="' . htmlspecialchars($entity['type']) . '">';
            if (isset($entity['wikipedia_url'])) {
                $replacement .= '<a href="' . htmlspecialchars($entity['wikipedia_url']) . '" target="_blank">';
            }
            $replacement .= htmlspecialchars($entity['text']);
            if (isset($entity['wikipedia_url'])) {
                $replacement .= '</a>';
            }
            $replacement .= '</span>';
            $annotatedText = str_replace($entity['text'], $replacement, $annotatedText);
        }
        return $annotatedText;
    }
}

?>