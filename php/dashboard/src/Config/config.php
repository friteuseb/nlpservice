<?php
define('API_URL', 'https://nlpservice.semantic-suggestion.com/api');
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