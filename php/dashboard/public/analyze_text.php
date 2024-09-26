<?php
require_once __DIR__ . '/../src/Config/config.php';
require_once __DIR__ . '/../src/Controllers/AnalyzeTextController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$controller = new AnalyzeTextController();
$controller->handle();