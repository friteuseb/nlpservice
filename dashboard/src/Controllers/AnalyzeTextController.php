<?php
require_once __DIR__ . '/../Services/APIClient.php';

class AnalyzeTextController {
    public function handle() {
        $textName = $_POST['text'] ?? '';

        if (empty($textName)) {
            echo json_encode(['error' => 'Nom du texte manquant']);
            return;
        }

        $filePath = TEXTS_DIRECTORY . '/' . $textName . '.txt';

        if (!file_exists($filePath)) {
            echo json_encode(['error' => 'Fichier texte non trouvé']);
            return;
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            echo json_encode(['error' => 'Impossible de lire le contenu du fichier']);
            return;
        }

        $apiClient = new APIClient();

        try {
            $result = $apiClient->analyzeText($content);
            $analysisResult = json_encode($result, JSON_PRETTY_PRINT);
            $analysisPath = ANALYSES_DIRECTORY . '/' . $textName . '_analysis.json';
            $bytesWritten = file_put_contents($analysisPath, $analysisResult);
            if ($bytesWritten === false) {
                error_log("Erreur lors de l'écriture du fichier: $analysisPath");
            } else {
                error_log("Fichier d'analyse sauvegardé avec succès: $analysisPath ($bytesWritten bytes)");
            }
            echo json_encode(['success' => true, 'message' => 'Analyse terminée et sauvegardée']);
        } catch (Exception $e) {
            error_log("Erreur lors de l'analyse de $textName: " . $e->getMessage());
            echo json_encode(['error' => "Erreur lors de l'analyse : " . $e->getMessage()]);
        }
    }
}