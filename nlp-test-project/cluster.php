<?php
function analyzeTexts($texts) {
    $url = 'https://nlpservice.semantic-suggestion.com/api/analyze';
    $results = [];

    foreach ($texts as $index => $text) {
        $data = array(
            'content' => base64_encode($text),
            'generate_sentiment_graph' => true
        );
        
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );
        
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            $results[$index] = ["error" => "Analyse failed for text " . ($index + 1)];
        } else {
            $results[$index] = json_decode($result, true);
        }
    }

    return $results;
}

function extractTopics($texts, $numTopics = 5) {
    $url = 'https://nlpservice.semantic-suggestion.com/api/extract_topics';
    $data = array(
        'texts' => array_map('base64_encode', $texts),
        'num_topics' => $numTopics
    );
    
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        return ["error" => "Topic extraction failed"];
    } else {
        return json_decode($result, true);
    }
}

$texts = [
    "Quelle pitié que Bilbon ne l'ait pas tué quand il en a eu l'occasion !
    Gandalf : De la pitié ? Mais c'est la pitié qui a retenu la main de votre oncle. Nombreux sont les vivants qui mériteraient la mort et les morts qui mériteraient la vie. Pouvez vous leur rendre ? Frodon ? Alors ne soyez pas trop prompt à dispenser mort et jugement. Même les grands sages ne peuvent connaître toutes les fins. Mon cœur me dit que Gollum a encore un rôle à jouer. En bien ou en mal, avant que cette histoire se termine. De la pitié de Bilbon peu dépendre le sort de beaucoup.",
    
    "Frodon était à présent en sûreté dans la Dernière Maison Simple à l'Est de la Mer. C'était, comme Bilbon l'avait déclaré jadis, \"une maison parfaite, que l'on aime manger, dormir, raconter des histoires ou chanter, ou encore un agréable mélange de tout cela\". Le seul fait de se trouver là était un remède à la fatigue, à la peur ou à la tristesse.",
    
    "Il se jeta sur son lit et sombra aussitôt dans un long sommeil. Les autres ne tardèrent pas à faire de même, et aucun bruit ni rêve ne vint troubler leur repos. A leur réveil, ils virent que la lumière du jour se répandait à flots sur la pelouse devant la tente, et que la source jaillissait et tombait scintillante au soleil."
];

$analysisResults = analyzeTexts($texts);
$topics = extractTopics($texts);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse de Textes</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Analyse de Textes</h1>

    <?php foreach ($analysisResults as $index => $result): ?>
        <h2>Texte <?php echo $index + 1; ?></h2>
        <p><?php echo htmlspecialchars($texts[$index]); ?></p>
        
        <?php if (isset($result['error'])): ?>
            <p>Erreur: <?php echo $result['error']; ?></p>
        <?php else: ?>
            <h3>Analyse de Sentiment</h3>
            <p>Sentiment global: <?php echo $result['sentiment_analysis']['overall_sentiment']; ?></p>
            <p>Score: <?php echo $result['sentiment_analysis']['overall_score']; ?></p>
            
            <?php if (isset($result['sentiment_analysis']['sentiment_graph'])): ?>
                <h4>Graphique de Sentiment</h4>
                <img src="data:image/png;base64,<?php echo $result['sentiment_analysis']['sentiment_graph']; ?>" alt="Graphique de sentiment">
            <?php endif; ?>
            
            <h3>Mots-clés</h3>
            <ul>
                <?php foreach ($result['keyphrases'] as $keyphrase): ?>
                    <li><?php echo htmlspecialchars($keyphrase); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endforeach; ?>

    <h2>Thématiques Dominantes</h2>
    <?php if (isset($topics['error'])): ?>
        <p>Erreur: <?php echo $topics['error']; ?></p>
    <?php else: ?>
        <ul>
            <?php foreach ($topics as $topic): ?>
                <li>Thème <?php echo $topic['id'] + 1; ?>: <?php echo implode(', ', $topic['words']); ?></li>
            <?php endforeach; ?>
        </ul>

        <canvas id="topicsChart"></canvas>
        <script>
        var ctx = document.getElementById('topicsChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($topic) { return "Thème " . ($topic['id'] + 1); }, $topics)); ?>,
                datasets: [{
                    label: 'Importance des thèmes',
                    data: <?php echo json_encode(array_map(function($topic) { return count($topic['words']); }, $topics)); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        </script>
    <?php endif; ?>
</body>
</html>