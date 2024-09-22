<?php
function displayAnalysis($title, $text, $analysis) {
    echo "<div class='card mb-4'>";
    echo "<div class='card-header'><h3>" . htmlspecialchars($title) . "</h3></div>";
    echo "<div class='card-body'>";
    echo "<p><strong>Extrait :</strong> " . htmlspecialchars($text) . "</p>";
    echo "<h4>Résultats de l'analyse</h4>";
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<h5>Statistiques générales</h5>";
    echo "<ul class='list-group'>";
    echo "<li class='list-group-item'>Nombre de mots : " . ($analysis['word_count'] ?? 'N/A') . "</li>";
    echo "<li class='list-group-item'>Nombre de phrases : " . ($analysis['sentence_count'] ?? 'N/A') . "</li>";
    echo "<li class='list-group-item'>Longueur moyenne des phrases : " . ($analysis['average_sentence_length'] ?? 'N/A') . "</li>";
    echo "<li class='list-group-item'>Score de lisibilité : " . ($analysis['readability_score'] ?? 'N/A') . "</li>";
    echo "<li class='list-group-item'>Diversité lexicale : " . ($analysis['lexical_diversity'] ?? 'N/A') . "</li>";
    echo "<li class='list-group-item'>Cohérence sémantique : " . ($analysis['semantic_coherence'] ?? 'N/A') . "</li>";
    echo "<li class='list-group-item'>Catégorie : " . ($analysis['category'] ?? 'N/A') . "</li>";
    echo "<li class='list-group-item'>Langue : " . ($analysis['language'] ?? 'N/A') . "</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div class='col-md-6'>";
    echo "<h5>Analyse du sentiment</h5>";
    echo "<p>Sentiment dominant : <strong>" . ($analysis['sentiment'] ?? 'N/A') . "</strong></p>";
    echo "<canvas id='sentimentChart" . str_replace(' ', '', $title) . "'></canvas>";
    echo "</div>";
    echo "</div>";
    echo "<div class='row mt-3'>";
    echo "<div class='col-md-4'>";
    echo "<h5>Mots-clés (hors mots vides)</h5>";
    echo "<ul class='list-group'>";
    if (isset($analysis['keyphrases']) && is_array($analysis['keyphrases'])) {
        foreach ($analysis['keyphrases'] as $keyphrase) {
            echo "<li class='list-group-item'>" . htmlspecialchars($keyphrase) . "</li>";
        }
    } else {
        echo "<li class='list-group-item'>Aucun mot-clé significatif disponible</li>";
    }
    echo "</ul>";
    echo "</div>";
    echo "<div class='col-md-4'>";
    echo "<h5>Entités nommées</h5>";
    echo "<ul class='list-group'>";
    if (isset($analysis['named_entities']) && is_array($analysis['named_entities']) && !empty($analysis['named_entities'])) {
        foreach ($analysis['named_entities'] as $entity) {
            if (is_array($entity)) {
                // Si l'entité est un tableau, supposons qu'il contient le texte et le type
                $entityText = $entity['text'] ?? $entity[0] ?? '';
                $entityType = $entity['type'] ?? $entity[1] ?? '';
                echo "<li class='list-group-item'>" . htmlspecialchars($entityText) . " (" . htmlspecialchars($entityType) . ")</li>";
            } elseif (is_string($entity)) {
                // Si l'entité est une chaîne, affichez-la simplement
                echo "<li class='list-group-item'>" . htmlspecialchars($entity) . "</li>";
            }
        }
    } else {
        echo "<li class='list-group-item'>Aucune entité nommée détectée</li>";
    }
    echo "</ul>";
    echo "</div>";
    echo "<div class='col-md-4'>";
    echo "<h5>Top N-grams (hors mots vides)</h5>";
    echo "<ul class='list-group'>";
    if (isset($analysis['top_n_grams']) && is_array($analysis['top_n_grams'])) {
        foreach ($analysis['top_n_grams'] as $ngram) {
            if (is_array($ngram[0])) {
                $ngramText = implode(' ', $ngram[0]);
            } else {
                $ngramText = $ngram[0];
            }
            echo "<li class='list-group-item'>" . htmlspecialchars($ngramText) . " (" . $ngram[1] . ")</li>";
        }
    } else {
        echo "<li class='list-group-item'>Aucun N-gram significatif disponible</li>";
    }
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

function displaySimilarities($similarities) {
    echo "<div class='card mb-4'>";
    echo "<div class='card-header'><h3>Similarités entre les textes</h3></div>";
    echo "<div class='card-body'>";
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>Paire de textes</th><th>Score de similarité</th></tr></thead>";
    echo "<tbody>";
    foreach ($similarities as $pair => $score) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($pair) . "</td>";
        echo "<td>" . number_format($score, 4) . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
}

function renderSentimentChart($title, $analysis) {
    if (isset($analysis['sentiment_distribution']) && is_array($analysis['sentiment_distribution'])) {
        $chartId = 'sentimentChart' . str_replace(' ', '', $title);
        echo "new Chart(document.getElementById('" . $chartId . "').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Positif', 'Neutre', 'Négatif'],
                datasets: [{
                    data: [
                        " . ($analysis['sentiment_distribution']['POSITIVE'] ?? 0) . ",
                        " . ($analysis['sentiment_distribution']['NEUTRAL'] ?? 0) . ",
                        " . ($analysis['sentiment_distribution']['NEGATIVE'] ?? 0) . "
                    ],
                    backgroundColor: ['#4CAF50', '#FFC107', '#F44336']
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Distribution du sentiment'
                }
            }
        });";
    }
}
?>