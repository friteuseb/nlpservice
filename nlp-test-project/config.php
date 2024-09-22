<?php
define('NLP_SERVICE_URL', 'https://nlpservice.semantic-suggestion.com/api');
define('DEBUG_MODE', true);

function debug_log($message) {
    if (DEBUG_MODE) {
        error_log($message);
    }
}
