<?php
require_once __DIR__ . '/../src/Config/config.php';

// Routeur simple
$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '/':
        require __DIR__ . '/home.php';
        break;
    case '/analyze':
        require __DIR__ . '/analyze_text.php';
        break;
    case '/similarity':
        require __DIR__ . '/similarity_test.php';
        break;
    case '/batch':
        require __DIR__ . '/batch_analysis.php';
        break;
    case '/dashboard/public/index.php':
        // Inclure le contenu de ../index.php
        require __DIR__ . '/../index.php';
        break;
    default:
        http_response_code(404);
        require __DIR__ . '/404.php';
        break;
}
