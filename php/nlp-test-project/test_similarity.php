<?php

// Configuration de l'API
$API_URL = "https://nlpservice.semantic-suggestion.com/api";

/**
 * Encode le texte en base64
 * @param string $text Le texte à encoder
 * @return string Le texte encodé en base64
 */
function encodeText($text) {
    return base64_encode($text);
}

/**
 * Envoie une requête POST à l'API
 * @param string $endpoint L'endpoint de l'API
 * @param array $data Les données à envoyer
 * @return array La réponse de l'API décodée
 */
function sendRequest($endpoint, $data) {
    global $API_URL;
    $url = $API_URL . $endpoint;

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return ['error' => 'Erreur lors de la requête à l\'API'];
    }

    return json_decode($result, true);
}

/**
 * Analyse un texte
 * @param string $text Le texte à analyser
 * @return array Le résultat de l'analyse
 */
function analyzeText($text) {
    $encodedText = encodeText($text);
    return sendRequest("/analyze", ['content' => $encodedText]);
}

/**
 * Compare deux textes
 * @param string $text1 Le premier texte
 * @param string $text2 Le deuxième texte
 * @param string $method La méthode de comparaison (optionnel)
 * @return array Le résultat de la comparaison
 */
function compareTexts($text1, $text2, $method = 'cosine') {
    $encodedText1 = encodeText($text1);
    $encodedText2 = encodeText($text2);
    return sendRequest("/similarity", [
        'text1' => $encodedText1,
        'text2' => $encodedText2,
        'method' => $method
    ]);
}

// Exemple d'utilisation

// Test d'analyse de texte
$textToAnalyze = "L'intelligence artificielle transforme rapidement notre monde.";
$analysisResult = analyzeText($textToAnalyze);
echo "Résultat de l'analyse :\n";
print_r($analysisResult);

// Test de comparaison de textes
$text1 = "Les chats sont des animaux indépendants.";
$text2 = "Les chiens sont des animaux fidèles.";
$similarityResult = compareTexts($text1, $text2);
echo "\nRésultat de la comparaison :\n";
print_r($similarityResult);

// Test avec différentes méthodes de similarité
$methods = ['cosine', 'euclidean', 'manhattan', 'jaccard', 'bleu'];
foreach ($methods as $method) {
    $result = compareTexts($text1, $text2, $method);
    echo "\nSimilarité avec la méthode $method :\n";
    print_r($result);
}
?>