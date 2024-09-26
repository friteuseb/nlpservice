<?php

require_once 'app/Models/TextAnalysis.php';
require_once 'app/Views/TextAnalysisView.php';

class TextAnalysisController {
    private $model;
    private $view;

    public function __construct() {
        $this->model = new TextAnalysis();
        $this->view = new TextAnalysisView();
    }

    public function index() {
        $sampleTexts = $this->model->getSampleTexts();
        $this->view->renderIndex($sampleTexts);
    }

    public function analyze() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $text = $_POST['text'] ?? '';
            $result = $this->model->analyzeText($text);
            $this->view->renderAnalysisResult($result);
        } else {
            $this->view->renderAnalysisForm();
        }
    }
}
?>