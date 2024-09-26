<?php

class SimilarityAnalysis {
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

    public function compareTexts($text1, $text2, $method = 'cosine') {
        $encodedText1 = $this->encodeText($text1);
        $encodedText2 = $this->encodeText($text2);
        $data = json_encode([
            'text1' => $encodedText1,
            'text2' => $encodedText2,
            'method' => $method
        ]);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $data
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($this->apiUrl . '/similarity', false, $context);

        if ($result === FALSE) {
            return ['error' => 'Erreur lors de la comparaison des textes'];
        }

        return json_decode($result, true);
    }

    public function compareAllTexts($texts) {
        $results = [];
        $methods = ['cosine', 'euclidean', 'manhattan', 'jaccard', 'bleu'];

        for ($i = 0; $i < count($texts); $i++) {
            for ($j = $i + 1; $j < count($texts); $j++) {
                $methodResults = [];
                foreach ($methods as $method) {
                    $comparison = $this->compareTexts($texts[$i], $texts[$j], $method);
                    $methodResults[$method] = $comparison['similarity'] ?? null;
                }
                $results[] = [
                    'text1' => $texts[$i],
                    'text2' => $texts[$j],
                    'similarities' => $methodResults
                ];
            }
        }

        return $results;
    }


}
?>