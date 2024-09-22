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