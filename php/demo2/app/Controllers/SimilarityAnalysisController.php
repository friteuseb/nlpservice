<?php

require_once 'app/Models/SimilarityAnalysis.php';
require_once 'app/Views/SimilarityAnalysisView.php';

class SimilarityAnalysisController {
    private $model;
    private $view;

    public function __construct() {
        $this->model = new SimilarityAnalysis();
        $this->view = new SimilarityAnalysisView();
    }

    public function compare() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $text1 = $_POST['text1'] ?? '';
            $text2 = $_POST['text2'] ?? '';
            $method = $_POST['method'] ?? 'cosine';
            $result = $this->model->compareTexts($text1, $text2, $method);
            $this->view->renderComparisonResult($result);
        } else {
            $sampleTexts = $this->model->getSampleTexts();
            $this->view->renderComparisonForm($sampleTexts);
        }
    }

    public function multiCompare() {
        $sampleTexts = $this->model->getSampleTexts();
        $results = $this->model->compareMultipleTexts($sampleTexts);
        $this->view->renderMultiComparisonResult($results, $sampleTexts);
    }

    public function analyzeAllTexts() {
        $startTime = microtime(true);
        $memoryStart = memory_get_usage();
        
        $sampleTexts = $this->model->getSampleTexts();
        $results = $this->model->compareAllTexts($sampleTexts);
        
        $endTime = microtime(true);
        $memoryEnd = memory_get_usage();
        
        $metrics = [
            'executionTime' => $endTime - $startTime,
            'memoryUsage' => $memoryEnd - $memoryStart,
            'textCount' => count($sampleTexts),
            'comparisonCount' => count($results)
        ];
        
        // Trouver les couples les plus similaires
        usort($results, function($a, $b) {
            return $b['similarities']['cosine'] <=> $a['similarities']['cosine'];
        });
        $topSimilar = array_slice($results, 0, 5);
        
        $this->view->renderAllTextsAnalysis($results, $sampleTexts, $metrics, $topSimilar);
    }
}
?>