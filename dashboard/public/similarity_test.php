<?php
require_once __DIR__ . '/../src/Config/config.php';
require_once __DIR__ . '/../src/Controllers/SimilarityController.php';
require_once __DIR__ . '/../src/Controllers/CalculateAllSimilaritiesController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['allMethod'])) {
        $controller = new CalculateAllSimilaritiesController();
    } else {
        $controller = new SimilarityController();
    }
    $controller->handle();
    exit;
}


$apiClient = new APIClient();

// Charger les textes
$texts = [];
$files = glob(TEXTS_DIRECTORY . '/*.txt');
foreach ($files as $file) {
    $texts[basename($file, '.txt')] = file_get_contents($file);
}

$similarityMethods = ['cosine', 'euclidean', 'manhattan', 'jaccard', 'bleu'];

function calculateAllSimilarities($texts, $method) {
    global $apiClient;
    $start = microtime(true);
    $similarities = [];
    $textNames = array_keys($texts);
    $totalComparisons = (count($textNames) * (count($textNames) - 1)) / 2;
    $completedComparisons = 0;

    for ($i = 0; $i < count($textNames); $i++) {
        for ($j = $i + 1; $j < count($textNames); $j++) {
            $result = $apiClient->calculateSimilarity($texts[$textNames[$i]], $texts[$textNames[$j]], $method);
            $similarities[$textNames[$i] . ' vs ' . $textNames[$j]] = $result['similarity'];
            $completedComparisons++;
            
            // Mise à jour de la progression
            $progress = ($completedComparisons / $totalComparisons) * 100;
            echo "<script>updateProgress($progress);</script>";
            ob_flush();
            flush();
        }
    }

    $end = microtime(true);
    $executionTime = $end - $start;

    return [
        'similarities' => $similarities,
        'executionTime' => $executionTime,
        'memoryUsage' => memory_get_peak_usage(true)
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Similarité</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-3">
    <a href="index.php" class="btn btn-primary">Retour au Dashboard</a>
</div>
    <div class="container mt-5">
        <h1>Test de Similarité entre Textes</h1>
        <form id="similarityForm">
            <div class="mb-3">
                <label for="text1" class="form-label">Premier texte</label>
                <select class="form-select" id="text1" name="text1" required>
                    <option value="">Sélectionnez un texte</option>
                    <?php foreach ($texts as $name => $content): ?>
                        <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="text2" class="form-label">Deuxième texte</label>
                <select class="form-select" id="text2" name="text2" required>
                    <option value="">Sélectionnez un texte</option>
                    <?php foreach ($texts as $name => $content): ?>
                        <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="method" class="form-label">Méthode de similarité</label>
                <select class="form-select" id="method" name="method" required>
                    <?php foreach ($similarityMethods as $method): ?>
                        <option value="<?php echo $method; ?>"><?php echo ucfirst($method); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Calculer la similarité</button>
        </form>
        <div id="result" class="mt-4"></div>

        <h2 class="mt-5">Calcul de similarité pour tous les textes</h2>
        <form id="allSimilaritiesForm">
            <div class="mb-3">
                <label for="allMethod" class="form-label">Méthode de similarité</label>
                <select class="form-select" id="allMethod" name="allMethod" required>
                    <?php foreach ($similarityMethods as $method): ?>
                        <option value="<?php echo $method; ?>"><?php echo ucfirst($method); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Calculer toutes les similarités</button>
        </form>
        <div id="progressBar" class="progress mt-3" style="display:none;">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <div id="allResults" class="mt-4"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('similarityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const text1 = document.getElementById('text1').value;
        const text2 = document.getElementById('text2').value;
        const method = document.getElementById('method').value;
        
        if (text1 === text2) {
            alert("Veuillez sélectionner deux textes différents.");
            return;
        }

        document.getElementById('result').innerHTML = 'Calcul en cours...';

        fetch('similarity_test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `text1=${encodeURIComponent(text1)}&text2=${encodeURIComponent(text2)}&method=${encodeURIComponent(method)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            document.getElementById('result').innerHTML = `
                <div class="alert alert-success">
                    La similarité entre "${text1}" et "${text2}" est de : ${data.similarity.toFixed(4)}
                </div>`;
        })
        .catch(error => {
            console.error('Erreur détaillée:', error);
            document.getElementById('result').innerHTML = `<div class="alert alert-danger">Erreur : ${error.message}</div>`;
        });
    });

        document.getElementById('allSimilaritiesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const method = document.getElementById('allMethod').value;
        document.getElementById('progressBar').style.display = 'block';
        document.getElementById('allResults').innerHTML = 'Calcul en cours...';

        fetch('similarity_test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `allMethod=${encodeURIComponent(method)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        let resultHtml = `
            <h3>Résultats de similarité</h3>
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Temps d'exécution</h5>
                            <p class="card-text">${data.executionTime.toFixed(2)} secondes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Utilisation mémoire</h5>
                            <p class="card-text">${(data.memoryUsage / (1024 * 1024)).toFixed(2)} MB</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Similarité moyenne</h5>
                            <p class="card-text">${data.averageSimilarity.toFixed(4)}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Nombre de comparaisons</h5>
                            <p class="card-text">${data.comparisonCount}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Similarité minimale</h5>
                            <p class="card-text">${data.minSimilarity.toFixed(4)}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Similarité maximale</h5>
                            <p class="card-text">${data.maxSimilarity.toFixed(4)}</p>
                        </div>
                    </div>
                </div>
            </div>
            <h4 class="mt-4">Détails des similarités</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Textes comparés</th>
                        <th>Similarité</th>
                    </tr>
                </thead>
                <tbody>
        `;
        for (const [pair, similarity] of Object.entries(data.similarities)) {
            resultHtml += `
                <tr>
                    <td>${pair}</td>
                    <td>${similarity.toFixed(4)}</td>
                </tr>
            `;
        }
        resultHtml += '</tbody></table>';
        document.getElementById('allResults').innerHTML = resultHtml;
    })
        .catch(error => {
            console.error('Erreur détaillée:', error);
            document.getElementById('allResults').innerHTML = `<div class="alert alert-danger">Erreur : ${error.message}</div>`;
        });
    });

    function updateProgress(progress) {
        const progressBar = document.querySelector('#progressBar .progress-bar');
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
        progressBar.textContent = `${progress.toFixed(2)}%`;
    }
    </script>
</body>
</html>