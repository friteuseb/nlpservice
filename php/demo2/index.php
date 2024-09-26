<?php
require_once 'app/Controllers/TextAnalysisController.php';
require_once 'app/Controllers/SimilarityAnalysisController.php';

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'analyze':
        $controller = new TextAnalysisController();
        $controller->analyze();
        break;
    case 'compare':
        $controller = new SimilarityAnalysisController();
        $controller->compare();
        break;
    case 'analyzeAllTexts':
        $controller = new SimilarityAnalysisController();
        $controller->analyzeAllTexts();
        break;
    default:
        $controller = new TextAnalysisController();
        $controller->index();
        break;

}
?>