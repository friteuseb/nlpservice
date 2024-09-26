<?php
require_once 'nlp_service.php';
require_once 'data_provider.php';

class Analyzer {
    public static function analyzeAllTexts() {
        $texts = DataProvider::getTexts();
        $analyses = [];
        foreach ($texts as $title => $text) {
            $analyses[$title] = NLPService::analyzeText($text);
        }
        return $analyses;
    }

    public static function calculateAllSimilarities() {
        error_log("Début du calcul de toutes les similarités");
        $texts = DataProvider::getTexts();
        $similarities = [];
        $titles = array_keys($texts);
        for ($i = 0; $i < count($titles); $i++) {
            for ($j = $i + 1; $j < count($titles); $j++) {
                $text1 = $texts[$titles[$i]];
                $text2 = $texts[$titles[$j]];
                error_log("Calcul de similarité entre '{$titles[$i]}' et '{$titles[$j]}'");
                $result = NLPService::calculateSimilarity($text1, $text2);
                
                error_log("Résultat pour {$titles[$i]} vs {$titles[$j]}: " . json_encode($result));
                
                if (isset($result['similarity'])) {
                    $similarity = $result['similarity'];
                    error_log("Similarité calculée : $similarity");
                } else {
                    $similarity = 0;
                    $errorMessage = $result['error'] ?? 'Raison inconnue';
                    error_log("Erreur lors du calcul de similarité : $errorMessage");
                }
                
                $similarities[$titles[$i] . " vs " . $titles[$j]] = $similarity;
            }
        }
        error_log("Fin du calcul de toutes les similarités. Résultat : " . json_encode($similarities));
        return $similarities;
    }

}
?>