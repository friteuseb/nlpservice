<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soumettre des Textes pour Analyse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Soumettre des Textes pour Analyse</h1>
        <form action="process_submission.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="textInput" class="form-label">Saisir un texte</label>
                <textarea class="form-control" id="textInput" name="textInput" rows="5"></textarea>
            </div>
            <div class="mb-3">
                <label for="fileInput" class="form-label">Ou uploader un fichier texte</label>
                <input class="form-control" type="file" id="fileInput" name="fileInput[]" multiple accept=".txt">
            </div>
            <button type="submit" class="btn btn-primary">Soumettre pour analyse</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>