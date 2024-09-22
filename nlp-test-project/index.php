<?php
require_once 'config.php';
require_once 'analyzer.php';
require_once 'view_helpers.php';

$analyses = Analyzer::analyzeAllTexts();
$similarities = Analyzer::calculateAllSimilarities();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Démonstration du Service NLP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Démonstration du Service NLP avec des Extraits de Livres Classiques Français</h1>
        
        <div class="mb-4">
            <h2>Analyse de textes</h2>
            <?php
            $texts = DataProvider::getTexts();
            foreach ($analyses as $title => $analysis) {
                displayAnalysis($title, $texts[$title], $analysis);
            }
            ?>
        </div>

        <div class="mb-4">
            <h2>Comparaison de similarités</h2>
            <?php displaySimilarities($similarities); ?>
        </div>

        <div class="mb-4">
            <h2>Test en direct</h2>
            <form id="liveTestForm">
                <div class="mb-3">
                    <label for="textInput" class="form-label">Entrez un texte à analyser :</label>
                    <textarea class="form-control" id="textInput" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Analyser</button>
            </form>
            <div id="liveResult" class="mt-3"></div>
        </div>
    </div>

<script>
<?php
    foreach ($analyses as $title => $analysis) {
        renderSentimentChart($title, $analysis);
    }
    ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('liveTestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var text = document.getElementById('textInput').value;
        var resultDiv = document.getElementById('liveResult');
        resultDiv.innerHTML = '<p>Chargement en cours...</p>';
        
        console.log('Texte à analyser:', text);

        fetch('https://nlpservice.semantic-suggestion.com/api/analyze', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({content: btoa(unescape(encodeURIComponent(text)))}),
            mode: 'cors',
        })
        .then(response => {
            console.log('Statut de la réponse:', response.status);
            if (!response.ok) {
                throw new Error('Erreur réseau: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Données reçues:', data);
            if (data.error) {
                throw new Error(data.error);
            }
            resultDiv.innerHTML = '<h3>Résultat de l\'analyse</h3>';
            resultDiv.innerHTML += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch((error) => {
            console.error('Erreur:', error);
            resultDiv.innerHTML = '<h3>Erreur</h3><p>' + error.message + '</p>';
        });
    });
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>