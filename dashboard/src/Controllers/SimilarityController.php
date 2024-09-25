<?php

require_once __DIR__ . '/../Services/APIClient.php';

class SimilarityController {
    public function handle() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Méthode non autorisée']);
            return;
        }

        $text1 = $_POST['text1'] ?? '';
        $text2 = $_POST['text2'] ?? '';
        $method = $_POST['method'] ?? 'cosine';

        if (empty($text1) || empty($text2)) {
            echo json_encode(['error' => 'Les deux textes sont requis']);
            return;
        }

        $apiClient = new APIClient();

        try {
            $result = $apiClient->calculateSimilarity($text1, $text2, $method);
            echo json_encode($result);
        } catch (Exception $e) {
            error_log("Erreur lors du calcul de similarité : " . $e->getMessage());
            echo json_encode(['error' => "Erreur lors du calcul de similarité : " . $e->getMessage()]);
        }
    }
}