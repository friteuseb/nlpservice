<?php
require_once 'config.php';
require_once 'nlp_service.php';

// Définir un chemin de log relatif
define('LOG_FILE', __DIR__ . '/nlp_service.log');

// S'assurer que le fichier de log existe
if (!file_exists(LOG_FILE)) {
    touch(LOG_FILE);
    chmod(LOG_FILE, 0666);
}

function testSimilarity($text1, $text2) {
    echo "Testing similarity between:\n";
    echo "Text 1: $text1\n";
    echo "Text 2: $text2\n\n";

    try {
        $result = NLPService::calculateSimilarity($text1, $text2);
        echo "Result:\n";
        print_r($result);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n\n";
}

// Test cases
testSimilarity("Le chat est sur le tapis.", "Un chat dort sur le canapé.");
testSimilarity("J'aime le café.", "J'adore le thé.");
testSimilarity("Paris est la capitale de la France.", "Londres est la capitale de l'Angleterre.");

// Afficher le contenu du fichier de log
echo "Log contents:\n";
if (file_exists(LOG_FILE)) {
    echo file_get_contents(LOG_FILE);
} else {
    echo "Log file not found.\n";
}
?>