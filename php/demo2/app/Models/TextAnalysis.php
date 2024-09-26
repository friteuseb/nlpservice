<?php

class TextAnalysis {
    private $apiUrl = "https://nlpservice.semantic-suggestion.com/api";
    /*  private $apiUrl = "http://0.0.0.0:8000/api";*/

    public function getSampleTexts() {
        return [
            "L'anneau unique fut forgé dans les flammes de la Montagne du Destin, un pouvoir pour les gouverner tous.",
            "En un trou vivait un hobbit. Ce n'était pas un trou déplaisant, sale et humide, rempli de bouts de vers et d'une atmosphère suintante.",
            "Le courage n'est pas l'absence de peur, mais la capacité de vaincre ce qui fait peur.",
            "La vie, c'est comme une bicyclette, il faut avancer pour ne pas perdre l'équilibre.",
            "Être ou ne pas être, telle est la question.",
            "Le petit prince s'assit sur une pierre et leva les yeux vers le ciel.",
            "Il n'y a pas de hasard, il n'y a que des rendez-vous.",
            "La vie est un mystère qu'il faut vivre, et non un problème à résoudre.",
            "La liberté commence où l'ignorance finit.",
            "L'important n'est pas la chute, mais l'atterrissage."
        ];
    }

    private function encodeText($text) {
        return base64_encode($text);
    }

    public function analyzeText($text) {
        $encodedText = $this->encodeText($text);
        $data = json_encode(['content' => $encodedText]);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $data
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($this->apiUrl . '/analyze', false, $context);

        if ($result === FALSE) {
            return ['error' => 'Erreur lors de l\'analyse du texte'];
        }

        return json_decode($result, true);
    }
}
?>