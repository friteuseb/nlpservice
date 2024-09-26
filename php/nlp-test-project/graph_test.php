<?php
function analyzeSentiment($text) {
    $url = 'https://nlpservice.semantic-suggestion.com/api/analyze';
    /*fetch('https://cors-anywhere.herokuapp.com/https://nlpservice.semantic-suggestion.com/api/analyze', {*/
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
        return null;
    }
    
    return json_decode($result, true);
}

// HTML form
echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse de Sentiment</title>
</head>
<body>
    <h1>Analyse de Sentiment avec Graphique</h1>
    <form method="post">
        <textarea name="text" rows="4" cols="50" required></textarea><br>
        <input type="submit" value="Analyser">
    </form>
HTML;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['text'];
    $result = analyzeSentiment($text);

    if ($result !== null) {
        echo "<h2>Résultats de l'analyse :</h2>";
        
        // Affichage du sentiment global
        $sentiment = $result['sentiment_analysis']['overall_sentiment'];
        $score = $result['sentiment_analysis']['overall_score'];
        echo "<p>Sentiment global : $sentiment (Score : $score)</p>";
        
        // Affichage de l'émotion dominante
        $emotion = $result['sentiment_analysis']['dominant_emotion'];
        $emotion_score = $result['sentiment_analysis']['emotion_score'];
        echo "<p>Émotion dominante : $emotion (Score : $emotion_score)</p>";
        
        // Affichage du graphique de sentiment
        if (isset($result['sentiment_analysis']['sentiment_graph'])) {
            echo "<h3>Graphique de sentiment :</h3>";
            echo "<img src='data:image/png;base64," . $result['sentiment_analysis']['sentiment_graph'] . "' alt='Graphique de sentiment'>";
        }
        
        // Affichage des sentiments par phrase
        echo "<h3>Sentiment par phrase :</h3>";
        echo "<ul>";
        foreach ($result['sentiment_analysis']['sentence_sentiments'] as $sentence) {
            echo "<li>" . htmlspecialchars($sentence['text']) . " - Sentiment : " . $sentence['sentiment'] . " (Score : " . $sentence['score'] . ")</li>";
        }
        echo "</ul>";
        
        // Affichage du résultat complet pour le débogage
        echo "<h3>Résultat complet :</h3>";
        echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    } else {
        echo "<p>Erreur lors de l'analyse du texte.</p>";
    }
}

echo "</body></html>";
?>