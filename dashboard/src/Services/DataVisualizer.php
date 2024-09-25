<?php
class DataVisualizer {
    public function prepareSimilarityGraph($similarities) {
        $nodes = [];
        $links = [];
        $textIds = [];

        foreach ($similarities as $pair => $score) {
            list($text1, $text2) = explode('_', $pair);
            if (!in_array($text1, $textIds)) {
                $textIds[] = $text1;
                $nodes[] = ['id' => $text1, 'name' => $text1];
            }
            if (!in_array($text2, $textIds)) {
                $textIds[] = $text2;
                $nodes[] = ['id' => $text2, 'name' => $text2];
            }
            $links[] = ['source' => $text1, 'target' => $text2, 'value' => floatval($score)];
        }

        return [
            'chart' => ['type' => 'networkgraph'],
            'title' => ['text' => 'Graphe de Similarité des Textes'],
            'plotOptions' => [
                'networkgraph' => [
                    'keys' => ['from', 'to', 'value']
                ]
            ],
            'series' => [
                [
                    'dataLabels' => ['enabled' => true],
                    'data' => $links
                ]
            ]
        ];
    }

    public function prepareThemeClusterGraph($themes) {
        $series = [
            [
                'name' => 'Thèmes',
                'data' => []
            ]
        ];

        foreach ($themes as $theme => $weight) {
            if (is_string($theme) && is_numeric($weight)) {
                $series[0]['data'][] = [
                    'name' => $theme,
                    'value' => floatval($weight)
                ];
            }
        }

        return [
            'chart' => [
                'type' => 'packedbubble',
                'height' => '100%'
            ],
            'title' => [
                'text' => 'Thèmes Dominants'
            ],
            'tooltip' => [
                'useHTML' => true,
                'pointFormat' => '<b>{point.name}:</b> {point.value}'
            ],
            'plotOptions' => [
                'packedbubble' => [
                    'minSize' => '30%',
                    'maxSize' => '120%',
                    'zMin' => 0,
                    'zMax' => 1000,
                    'layoutAlgorithm' => [
                        'splitSeries' => false,
                        'gravitationalConstant' => 0.02
                    ],
                    'dataLabels' => [
                        'enabled' => true,
                        'format' => '{point.name}',
                        'filter' => [
                            'property' => 'y',
                            'operator' => '>',
                            'value' => 250
                        ],
                        'style' => [
                            'color' => 'black',
                            'textOutline' => 'none',
                            'fontWeight' => 'normal'
                        ]
                    ]
                ]
            ],
            'series' => $series
        ];
    }

    public function prepareKPIData($analyses) {
        $totalTexts = count($analyses);
        $totalWords = 0;
        $totalSentiments = ['positive' => 0, 'neutral' => 0, 'negative' => 0];
        $uniqueEntities = [];

        foreach ($analyses as $analysis) {
            $totalWords += $analysis['word_count'] ?? 0;
            $sentiment = $analysis['sentiment_analysis']['overall_sentiment'] ?? 'neutral';
            $totalSentiments[$sentiment] = ($totalSentiments[$sentiment] ?? 0) + 1;
            if (isset($analysis['named_entities']) && is_array($analysis['named_entities'])) {
                foreach ($analysis['named_entities'] as $entity) {
                    // Assurez-vous que l'entité est une chaîne de caractères
                    $uniqueEntities[] = is_array($entity) ? $entity['text'] : $entity;
                }
            }
        }

        $uniqueEntities = array_unique($uniqueEntities);

        $averageWords = $totalTexts > 0 ? round($totalWords / $totalTexts) : 0;

        $globalSentiment = 'Aucun';
        if (!empty($totalSentiments)) {
            $maxSentiment = max($totalSentiments);
            $globalSentiment = array_search($maxSentiment, $totalSentiments);
        }

        return [
            'Nombre total de textes' => $totalTexts,
            'Nombre moyen de mots par texte' => $averageWords,
            'Sentiment global' => $globalSentiment,
            'Nombre d\'entités uniques' => count($uniqueEntities)
        ];
    }
}