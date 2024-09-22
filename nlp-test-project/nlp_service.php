<?php
require_once 'config.php';

class NLPService {
    private static function callService($endpoint, $data) {
        $url = NLP_SERVICE_URL . "/" . $endpoint;
        echo "Calling API: $url\n";
        echo "Data: " . json_encode($data) . "\n";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data)))
        );

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if(curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch) . "\n";
            throw new Exception('Erreur cURL : ' . curl_error($ch));
        }
        
        echo "API Response Code: $httpCode\n";
        echo "API Raw Response: $result\n";

        curl_close($ch);

        $decodedResult = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON Decode Error: " . json_last_error_msg() . "\n";
            echo "Failed to decode: $result\n";
            throw new Exception('Erreur de décodage JSON : ' . json_last_error_msg());
        }

        return $decodedResult;
    }

    public static function analyzeText($text) {
        $encodedText = base64_encode($text);
        try {
            return self::callService("analyze", ["content" => $encodedText]);
        } catch (Exception $e) {
            error_log("Analyze Text Error: " . $e->getMessage());
            return ['error' => 'Une erreur est survenue lors de l\'analyse du texte: ' . $e->getMessage()];
        }
    }



    public static function calculateSimilarity($text1, $text2, $method = 'cosine', $ngramRange = [1, 2]) {
        self::log("Début du calcul de similarité");
        self::log("Text 1: " . substr($text1, 0, 50) . "...");
        self::log("Text 2: " . substr($text2, 0, 50) . "...");
    
        // Encodage des textes
        $encodedText1 = base64_encode($text1);
        $encodedText2 = base64_encode($text2);
    
        // Préparation des paramètres de l'API
        $apiParams = [
            "text1" => $encodedText1,
            "text2" => $encodedText2,
            "method" => $method,
            "ngram_range" => $ngramRange
        ];
    
        try {
            // Appel à l'API
            self::log("Appel à l'API avec les paramètres : " . json_encode($apiParams));
            $result = self::callService("similarity", $apiParams);
            self::log("Réponse brute de l'API : " . json_encode($result));
    
            // Vérification et traitement du résultat
            if (isset($result['similarity'])) {
                $similarity = $result['similarity'];
                self::log("Similarité calculée: $similarity");
                return ['similarity' => $similarity];
            } else {
                self::log("Réponse de l'API inattendue: " . json_encode($result));
                return ['error' => "Réponse inattendue de l'API"];
            }
        } catch (Exception $e) {
            self::log("Erreur lors du calcul de similarité: " . $e->getMessage());
            return ['error' => 'Une erreur est survenue lors du calcul de similarité: ' . $e->getMessage()];
        }
    }
    
    private static function log($message) {
        error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'nlp_service.log');
    }
}
?>