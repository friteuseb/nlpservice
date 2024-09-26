<?php

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

require_once __DIR__ . '/../Config/config.php';

class APIClient {
    private $apiUrl;

    public function __construct() {
        $this->apiUrl = API_URL;
    }
    public function analyzeText($text) {
        $response = $this->callApi('analyze', ['content' => base64_encode($text)]);
        
        // Log de la réponse brute
        error_log("Réponse brute de l'API pour analyzeText : " . substr($response, 0, 1000));
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Réponse invalide de l'API. Réponse brute : " . substr($response, 0, 1000));
        }
        return $decodedResponse;
    }
    

    public function calculateSimilarity($text1, $text2, $method = 'cosine') {
        $response = $this->callApi('similarity', [
            'text1' => base64_encode($text1),
            'text2' => base64_encode($text2),
            'method' => $method
        ]);
        
        error_log("Réponse brute de l'API pour calculateSimilarity : " . substr($response, 0, 1000));
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Réponse invalide de l'API pour la similarité. Réponse brute : " . substr($response, 0, 1000));
        }
        return $decodedResponse;
    }

    public function extractTopics($texts) {
        return $this->callApi('extract_topics', [
            'texts' => array_map('base64_encode', $texts),
            'num_topics' => 10
        ]);
    }

    private function callApi($endpoint, $data) {
        $url = API_URL . '/' . $endpoint;
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            ]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        // Log de l'URL et de la réponse
        error_log("URL de l'API appelée : " . $url);
        error_log("Réponse brute de callApi : " . substr($result, 0, 1000));
        
        if ($result === FALSE) {
            throw new Exception("Erreur lors de l'appel à l'API");
        }
        return $result;
    }
}