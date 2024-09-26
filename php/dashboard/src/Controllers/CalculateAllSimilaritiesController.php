<?php
require_once __DIR__ . '/../Services/APIClient.php';

class CalculateAllSimilaritiesController {
    public function handle() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Méthode non autorisée']);
            return;
        }

        $method = $_POST['method'] ?? 'cosine';

        $apiClient = new APIClient();

        // Charger les textes
        $texts = [];
        $files = glob(TEXTS_DIRECTORY . '/*.txt');
        foreach ($files as $file) {
            $texts[basename($file, '.txt')] = file_get_contents($file);
        }

        $similarities = [];
        $start = microtime(true);
        $totalSimilarity = 0;
        $minSimilarity = 1;
        $maxSimilarity = 0;
        $comparisonCount = 0;

        try {
            $textNames = array_keys($texts);
            for ($i = 0; $i < count($textNames); $i++) {
                for ($j = $i + 1; $j < count($textNames); $j++) {
                    $result = $apiClient->calculateSimilarity($texts[$textNames[$i]], $texts[$textNames[$j]], $method);
                    $similarity = $result['similarity'];
                    $similarities[$textNames[$i] . ' vs ' . $textNames[$j]] = $similarity;
                    
                    $totalSimilarity += $similarity;
                    $minSimilarity = min($minSimilarity, $similarity);
                    $maxSimilarity = max($maxSimilarity, $similarity);
                    $comparisonCount++;
                }
            }

            $end = microtime(true);
            $executionTime = $end - $start;
            $averageSimilarity = $comparisonCount > 0 ? $totalSimilarity / $comparisonCount : 0;

            echo json_encode([
                'similarities' => $similarities,
                'executionTime' => $executionTime,
                'memoryUsage' => memory_get_peak_usage(true),
                'averageSimilarity' => $averageSimilarity,
                'minSimilarity' => $minSimilarity,
                'maxSimilarity' => $maxSimilarity,
                'comparisonCount' => $comparisonCount
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors du calcul de toutes les similarités : " . $e->getMessage());
            echo json_encode(['error' => "Erreur lors du calcul : " . $e->getMessage()]);
        }
    }
}