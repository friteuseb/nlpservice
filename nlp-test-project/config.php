<?php
// Fonction pour obtenir l'adresse IP publique
function get_public_ip() {
    $ip = file_get_contents('https://api.ipify.org');
    return $ip !== false ? trim($ip) : '';
}

// Vérifiez si l'environnement est en développement local
function is_local_dev() {
    return in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || 
           (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost');
}

// Votre adresse IP publique à domicile
$home_ip = "86.208.219.79"; // Remplacez par votre IP publique réelle

$current_ip = get_public_ip();

if (is_local_dev()) {
    define('NLP_SERVICE_URL', 'http://localhost:5000/api');
    define('ENVIRONMENT', 'development');
} elseif ($current_ip === $home_ip) {
    define('NLP_SERVICE_URL', 'https://nlpservice.semantic-suggestion.com/api');
    define('ENVIRONMENT', 'home');
} else {
    define('NLP_SERVICE_URL', 'https://nlpservice.semantic-suggestion.com/api');
    define('ENVIRONMENT', 'production');
}

define('DEBUG_MODE', ENVIRONMENT !== 'production');

function debug_log($message) {
    if (DEBUG_MODE) {
        error_log("[" . ENVIRONMENT . "] " . $message);
    }
}

// Log pour le débogage
debug_log("Utilisation de l'URL du service : " . NLP_SERVICE_URL);