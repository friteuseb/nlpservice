<?php
require_once __DIR__ . '/../src/Config/config.php';
require_once __DIR__ . '/../src/Services/APIClient.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Charger les textes
$texts = [];
$files = glob(TEXTS_DIRECTORY . '/*.txt');
foreach ($files as $file) {
    $texts[basename($file, '.txt')] = $file;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse par Lot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Analyse par Lot des Textes</h1>
        <p>Nombre total de textes : <?php echo count($texts); ?></p>
        <button id="startAnalysis" class="btn btn-primary">Lancer l'analyse</button>
        <div id="progressContainer" class="mt-3" style="display: none;">
            <div class="progress">
                <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>
        <div id="result" class="mt-3"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('startAnalysis').addEventListener('click', function() {
        const texts = <?php echo json_encode(array_keys($texts)); ?>;
        let processed = 0;
        
        document.getElementById('progressContainer').style.display = 'block';
        document.getElementById('result').innerHTML = '';
        this.disabled = true;

        function updateProgress() {
            const progress = Math.round((processed / texts.length) * 100);
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('progressBar').setAttribute('aria-valuenow', progress);
            document.getElementById('progressBar').textContent = progress + '%';
        }

        function analyzeNext() {
            if (processed < texts.length) {
                const textName = texts[processed];
                fetch('/dashboard/public/analyze_text.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `text=${encodeURIComponent(textName)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    if (!data.success) {
                        throw new Error("L'analyse n'a pas réussi à sauvegarder le fichier");
                    }
                    processed++;
                    updateProgress();
                    document.getElementById('result').innerHTML += `<p>Analyse de "${textName}" terminée et sauvegardée.</p>`;
                    analyzeNext();
                })
                .catch(error => {
                    document.getElementById('result').innerHTML += `<p class="text-danger">Erreur lors de l'analyse de "${textName}": ${error.message}</p>`;
                    processed++;
                    updateProgress();
                    analyzeNext();
                });
            } else {
                document.getElementById('result').innerHTML += '<p class="text-success"><strong>Toutes les analyses sont terminées!</strong></p>';
                document.getElementById('startAnalysis').disabled = false;
            }
        }

        analyzeNext();
    });
    </script>
</body>
</html>