from flask import Blueprint, render_template_string

web_bp = Blueprint('web', __name__)

html_template = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NLP Service - Semantic Analysis Platform</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        code {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 2px 5px;
            font-family: monospace;
        }
        pre {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 10px;
            overflow-x: auto;
        }
        .endpoint {
            background-color: #e8f4f8;
            border-left: 5px solid #3498db;
            padding: 10px;
            margin-bottom: 20px;
        }
        .example {
            background-color: #f0f0f0;
            border-left: 5px solid #2ecc71;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>NLP Service - Semantic Analysis Platform</h1>
    <p>Welcome to our NLP service API. This platform offers two main functionalities: text analysis and text similarity comparison.</p>

    <h2>1. Text Analysis</h2>
    <div class="endpoint">
        <h3>Endpoint: <code>POST /api/analyze</code></h3>
        <p>This endpoint performs a comprehensive analysis of the provided text.</p>
    </div>

    <h3>Request Format:</h3>
    <pre>
{
    "content": "Your text here, encoded in base64"
}
    </pre>

    <h3>Example using cURL:</h3>
    <div class="example">
        <pre>
curl -X POST https://nlpservice.semantic-suggestion.com/api/analyze \
     -H "Content-Type: application/json" \
     -d '{"content": "Q2V0IGV4ZW1wbGUgZGUgdGV4dGUgZXN0IGVuIGZyYW7Dp2Fpcy4="}'
        </pre>
        <p>Note: The content "Cet exemple de texte est en français." is base64 encoded in the above example.</p>
    </div>

    <h3>Response Format:</h3>
    <pre>
{
    "sentiment": "NEUTRAL",
    "keyphrases": ["exemple", "texte", "français"],
    "category": "Non catégorisé",
    "named_entities": [],
    "readability_score": 83.5,
    "word_count": 7,
    "sentence_count": 1,
    "language": "fr",
    "lexical_diversity": 1.0,
    "top_n_grams": [
        [("cet", "exemple"), 1],
        [("exemple", "de"), 1],
        [("de", "texte"), 1]
    ],
    "semantic_coherence": 1.0,
    "sentiment_distribution": {
        "POSITIVE": 0.0,
        "NEGATIVE": 0.0,
        "NEUTRAL": 1.0
    },
    "average_sentence_length": 7.0
}
    </pre>


    <h3>Example using cURL:</h3>
    <div class="example">
        <pre>
    curl -X POST https://nlpservice.semantic-suggestion.com/api/analyze \
     -H "Content-Type: application/json" \
     -d '{
         "content": "RWxsZSBzb25nZWFpdCBxdWVscXVlZm9pcyBxdWUgY+KAmcOpdGFpZW50IGzDoCBwb3VydGFudCBsZXMgcGx1cyBiZWF1eCBqb3VycyBkZSBzYSB2aWUsIGxhIGx1bmUgZGUgbWllbCwgY29tbWUgb24gZGlzYWl0LiBQb3VyIGVuIGdvw7t0ZXIgbGEgZG91Y2V1ciwgaWwgZcO7dCBmYWxsdSwgc2FucyBkb3V0ZSwgc+KAmWVuIGFsbGVyIHZlcnMgZGVzIHBheXMgw6Agbm9tcyBzb25vcmVzIG/DuSBsZXMgbGVuZGVtYWlucyBkZSBtYXJpYWdlIG9udCBkZSBwbHVzIHN1YXZlcyBwYXJlc3NlcyAhIERhbnMgZGVzIGNoYWlzZXMgZGUgcG9zdGUsIHNvdXMgZGVzIHN0b3JlcyBkZSBzb2llIGJsZXVlLCBvbiBtb250ZSBhdSBwYXMgZGVzIHJvdXRlcyBlc2NhcnDDqWVzLCDDqWNvdXRhbnQgbGEgY2hhbnNvbiBkdSBwb3N0aWxsb24sIHF1aSBzZSByw6lwy6h0ZSBkYW5zIGxhIG1vbnRhZ25lIGF2ZWMgbGVzIGNsb2NoZXR0ZXMgZGVzIGNow6h2cmVzIGV0IGxlIGJydWl0IHNvdXJkIGRlIGxhIGNhc2NhZGUuIFF1YW5kIGxlIHNvbGVpbCBzZSBjb3VjaGUsIG9uIHJlc3BpcmUgYXUgYm9yZCBkZXMgZ29sZmVzIGxlIHBhcmZ1bSBkZXMgY2l0cm9ubmllcnMgOyBwdWlzLCBsZSBzb2lyLCBzdXIgbGEgdGVycmFzc2UgZGVzIHZpbGxhcywgc2V1bHMgZXQgbGVzIGRvaWd0cyBjb25mb25kdXMsIG9uIHJlZ2FyZGUgbGVzIMOpdG9pbGVzIGVuIGZhaXNhbnQgZGVzIHByb2pldHMuIElsIGx1aSBzZW1ibGFpdCBxdWUgY2VydGFpbnMgbGlldXggc3VyIGxhIHRlcnJlIGRldmFpZW50IHByb2R1aXJlIGR1IGJvbmhldXIsIGNvbW1lIHVuZSBwbGFudGUgcGFydGljdWxpw6hyZSBhdSBzb2wgZXQgcXVpIHBvdXNzZSBtYWwgdG91dCBhdXRyZSBwYXJ0LiBRdWUgbmUgcG91dmFpdC1lbGxlIHPigJlhY2NvdWRlciBzdXIgbGUgYmFsY29uIGRlcyBjaGFsZXRzIHN1aXNzZXMgb3UgZW5mZXJtZXIgc2EgdHJpc3Rlc3NlIGRhbnMgdW4gY290dGFnZSDDqWNvc3NhaXMsIGF2ZWMgdW4gbWFyaSB2w6p0dSBk4oCZdW4gaGFiaXQgZGUgdmVsb3VycyBub2lyIMOgIGxvbmd1ZXMgYmFzcXVlcywgZXQgcXVpIHBvcnRlIGRlcyBib3R0ZXMgbW9sbGVzLCB1biBjaGFwZWF1IHBvaW50dSBldCBkZXMgbWFuY2hldHRlcyAhCgpQZXV0LcOqdHJlIGF1cmFpdC1lbGxlIHNvdWhhaXTDqSBmYWlyZSDDoCBxdWVscXUndW4gbGEgY29uZmlkZW5jZSBkZSB0b3V0ZXMgY2VzIGNob3Nlcy4gTWFpcyBjb21tZW50IGRpcmUgdW4gaW5zYWlzaXNzYWJsZSBtYWxhaXNlLCBxdWkgY2hhbmdlIGQnYXNwZWN0IGNvbW1lIGxlcyBudcOpZXMsIHF1aSB0b3VyYmlsbG9ubmUgY29tbWUgbGUgdmVudCA/IExlcyBtb3RzIGx1aSBtYW5xdWFpZW50IGRvbmMsIGwnb2NjYXNpb24sIGxhIGhhcmRpZXNzZS4KU2kgQ2hhcmxlcyBs4oCZYXZhaXQgdm91bHUgY2VwZW5kYW50LCBzJ2lsIHMnZW4gZsO7dCBkb3V0w6ksIHNpIHNvbiByZWdhcmQsIHVuZSBzZXVsZSBmb2lzLCBmw7t0IHZlbnUgw6AgbGEgcmVuY29udHJlIGRlIHNhIHBlbnPDqWUsIGlsIGx1aSBzZW1ibGFpdCBxdSd1bmUgYWJvbmRhbmNlIHN1Yml0ZSBzZSBzZXJhaXQgZMOpdGFjaMOpZSBkZSBzb24gY8OcdXIsIGNvbW1lIHRvbWJlIGxhIHLDqWNvbHRlIGQndW4gZXNwYWxpZXIgcXVhbmQgb24geSBwb3J0ZSBsYSBtYWluLiBNYWlzLCDDoCBtZXN1cmUgcXVlIHNlIHNlcnJhaXQgZGF2YW50YWdlIGwnaW50aW1pdMOpIGRlIGxldXIgdmllLCB1biBkw6l0YWNoZW1lbnQgaW50w6lyaWV1ciBzZSBmYWlzYWl0IHF1aSBsYSBkw6lsaWFpdCBkZSBsdWkuCkxhIGNvbnZlcnNhdGlvbiBkZSBDaGFybGVzIMOpdGFpdCBwbGF0ZSBjb21tZSB1biB0cm90dG9pciBkZSBydWUsIGV0IGxlcyBpZMOpZXMgZGUgdG91dCBsZSBtb25kZSB5IGTDqWZpbGFpZW50IGRhbnMgbGV1ciBjb3N0dW1lIG9yZGluYWlyZSwgc2FucyBleGNpdGVyIGQnw6ltb3Rpb24sIGRlIHJpcmUgb3UgZGUgcsOqdmVyaWUuIElsIG4nYXZhaXQgamFtYWlzIMOpdMOpIGN1cmlldXgsIGRpc2FpdC1pbCwgcGVuZGFudCBxdSdpbCBoYWJpdGFpdCBSb3VlbiwgZCdhbGxlciB2b2lyIGF1IHRow6nDonRyZSBsZXMgYWN0ZXVycyBkZSBQYXJpcy4gSWwgbmUgc2F2YWl0IG5pIG5hZ2VyLCBuaSBmYWlyZSBkZXMgYXJtZXMsIG5pIHRpcmVyIGxlIHBpc3RvbGV0LCBldCBpbCBuZSBwdXQsIHVuIGpvdXIsIGx1aSBleHBsaXF1ZXIgdW4gdGVybWUgZCfDqXF1aXRhdGlvbiBxdSdlbGxlIGF2YWl0IHJlbmNvbnRyw6kgZGFucyB1biByb21hbi4="
     }'
        </pre>
        <p>Note: The content "Elle songeait quelquefois que c'étaient là pourtant les plus beaux jours de sa vie, la lune de miel, comme on disait. Pour en goûter la douceur, il eût fallu, sans doute, s'en aller vers des pays à noms sonores où les lendemains de mariage ont de plus suaves paresses ! Dans des chaises de poste, sous des stores de soie bleue, on monte au pas des routes escarpées, écoutant la chanson du postillon, qui se répète dans la montagne avec les clochettes des chèvres et le bruit sourd de la cascade. Quand le soleil se couche, on respire au bord des golfes le parfum des citronniers ; puis, le soir, sur la terrasse des villas, seuls et les doigts confondus, on regarde les étoiles en faisant des projets. Il lui semblait que certains lieux sur la terre devaient produire du bonheur, comme une plante particulière au sol et qui pousse mal tout autre part. Que ne pouvait-elle s'accouder sur le balcon des chalets suisses ou enfermer sa tristesse dans un cottage écossais, avec un mari vêtu d'un habit de velours noir à longues basques, et qui porte des bottes molles, un chapeau pointu et des manchettes !
Peut-être aurait-elle souhaité faire à quelqu'un la confidence de toutes ces choses. Mais comment dire un insaisissable malaise, qui change d'aspect comme les nuées, qui tourbillonne comme le vent ? Les mots lui manquaient donc, l’occasion, la hardiesse.
Si Charles l'avait voulu cependant, s'il s'en fût douté, si son regard, une seule fois, fût venu à la rencontre de sa pensée, il lui semblait qu'une abondance subite se serait détachée de son cœur, comme tombe la récolte d'un espalier quand on y porte la main. Mais, à mesure que se serrait davantage l'intimité de leur vie, un détachement intérieur se faisait qui la déliait de lui.
La conversation de Charles était plate comme un trottoir de rue, et les idées de tout le monde y défilaient dans leur costume ordinaire, sans exciter d'émotion, de rire ou de rêverie. Il n'avait jamais été curieux, disait-il, pendant qu'il habitait Rouen, d'aller voir au théâtre les acteurs de Paris. Il ne savait ni nager, ni faire des armes, ni tirer le pistolet, et il ne put, un jour, lui expliquer un terme d'équitation qu'elle avait rencontré dans un roman." is base64 encoded in the above example.</p>
    </div>

    <h3>Response Format:</h3>
    <pre>
{
    "sentiment": "NEUTRAL",
    "keyphrases": ["exemple", "texte", "français"],
    "category": "Non catégorisé",
    "named_entities": [],
    "readability_score": 83.5,
    "word_count": 7,
    "sentence_count": 1,
    "language": "fr",
    "lexical_diversity": 1.0,
    "top_n_grams": [
        [("cet", "exemple"), 1],
        [("exemple", "de"), 1],
        [("de", "texte"), 1]
    ],
    "semantic_coherence": 1.0,
    "sentiment_distribution": {
        "POSITIVE": 0.0,
        "NEGATIVE": 0.0,
        "NEUTRAL": 1.0
    },
    "average_sentence_length": 7.0
}
    </pre>







    <h2>2. Text Similarity Comparison</h2>
    <div class="endpoint">
        <h3>Endpoint: <code>POST /api/similarity</code></h3>
        <p>This endpoint compares the similarity between two provided texts.</p>
    </div>

    <h3>Request Format:</h3>
    <pre>
{
    "text1": "Your first text here, encoded in base64",
    "text2": "Your second text here, encoded in base64"
}
    </pre>

    <h3>Example using cURL:</h3>
    <div class="example">
        <pre>
curl -X POST https://nlpservice.semantic-suggestion.com/api/similarity \
     -H "Content-Type: application/json" \
     -d '{
         "text1": "TGUgY2hhdCBlc3Qgc3VyIGxlIHRhcGlzLg==",
         "text2": "VW4gY2hhdCBkb3J0IHN1ciBsZSBjYW5hcOku"
     }'
        </pre>
        <p>Note: The contents "Le chat est sur le tapis." and "Un chat dort sur le canapé." are base64 encoded in the above example.</p>
    </div>

    <h3>Response Format:</h3>
    <pre>
{
    "similarity": 0.75
}
    </pre>

    <h2>PHP Example</h2>
    <p>For a practical implementation example, you can check our PHP test script:</p>
    <p><a href="https://nlpservice.semantic-suggestion.com/test.php">PHP Test Script</a></p>

    <h2>Rate Limiting</h2>
    <p>Please note that our API is rate-limited to ensure fair usage:</p>
    <ul>
        <li>200 requests per day</li>
        <li>50 requests per hour</li>
        <li>10 requests per minute</li>
    </ul>

    <h2>Contact</h2>
    <p>If you have any questions or need further assistance, please contact our support team.</p>
</body>
</html>
"""

@web_bp.route('/')
def home():
    return render_template_string(html_template)