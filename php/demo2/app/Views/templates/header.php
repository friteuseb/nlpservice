<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse NLP</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/heatmap.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #2c3e50;
        }
        form {
            margin-bottom: 20px;
        }
        textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }
        button {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            margin-left: 10px;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .kpi-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .kpi-box {
            background-color: #f0f0f0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            width: calc(25% - 10px);
            box-sizing: border-box;
        }
        .kpi-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 1.2em;
            color: #3498db;
        }
    </style>
</head>
<body>
    <header>
        <h1>Analyse NLP</h1>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="index.php?action=analyze">Analyse de Texte</a></li>
                <li><a href="index.php?action=compare">Comparaison de Textes</a></li>
            </ul>
        </nav>
    </header>
    <main>