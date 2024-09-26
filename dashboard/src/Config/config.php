<?php

define('TEXTS_DIRECTORY', __DIR__ . '/../../texts');
define('ANALYSES_DIRECTORY', __DIR__ . '/../../data/analyses');
define('SIMILARITIES_DIRECTORY', __DIR__ . '/../../data/similarities');
define('MAX_TEXTS', 100);

// Créer les répertoires s'ils n'existent pas
if (!file_exists(TEXTS_DIRECTORY)) {
    mkdir(TEXTS_DIRECTORY, 0755, true);
}
if (!file_exists(ANALYSES_DIRECTORY)) {
    mkdir(ANALYSES_DIRECTORY, 0755, true);
}
if (!file_exists(SIMILARITIES_DIRECTORY)) {
    mkdir(SIMILARITIES_DIRECTORY, 0755, true);
}

// Déterminez si vous êtes en environnement local
function isLocalEnvironment() {
    $localDomains = ['localhost', '127.0.0.1', 'nlpservice.ddev.site'];
           strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
           strpos($_SERVER['SERVER_NAME'], '.test') !== false;
}

// Définissez l'URL de l'API en fonction de l'environnement
if (isLocalEnvironment()) {
    define('API_URL', '127.0.0.1:5000/api');  // Utilisez l'adresse IP locale et le port approprié
} else {
    define('API_URL', 'https://nlpservice.semantic-suggestion.com/api');
}
