#!/bin/bash

# Créer un point d'entrée principal dans public/
cat > public/index.php << EOL
<?php
require_once __DIR__ . '/../src/Config/config.php';

// Routeur simple
\$request = \$_SERVER['REQUEST_URI'];

switch (\$request) {
    case '/':
        require __DIR__ . '/home.php';
        break;
    case '/analyze':
        require __DIR__ . '/analyze_text.php';
        break;
    case '/similarity':
        require __DIR__ . '/similarity_test.php';
        break;
    case '/batch':
        require __DIR__ . '/batch_analysis.php';
        break;
    default:
        http_response_code(404);
        require __DIR__ . '/404.php';
        break;
}
EOL

# Convertir les fichiers de contrôleur
for file in src/Controllers/calculate_all_similarities.php src/Controllers/calculate_similarity.php src/Controllers/process_submission.php
do
    filename=$(basename "$file" .php)
    classname=$(echo "$filename" | sed -r 's/(^|_)([a-z])/\U\2/g')Controller
    
    mv "$file" "src/Controllers/${classname}.php"
    
    # Créer une structure de classe de base
    cat > "src/Controllers/${classname}.php" << EOL
<?php

class $classname {
    public function handle() {
        // Transférez le contenu existant ici et adaptez-le au format de classe
    }
}
EOL
done

# Supprimer le dossier Helpers s'il est vide
if [ -z "$(ls -A src/Helpers)" ]; then
    rm -r src/Helpers
fi

# Mettre à jour .htaccess
cat > public/.htaccess << EOL
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
EOL

# Créer un fichier 404.php de base
cat > public/404.php << EOL
<?php
http_response_code(404);
echo "404 - Page not found";
EOL

# Ajouter une gestion des erreurs de base dans APIClient.php
sed -i '1i<?php\n\nset_error_handler(function($errno, $errstr, $errfile, $errline) {\n    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);\n});\n' src/Services/APIClient.php

# Vérifier et ajuster les permissions
chmod 644 public/.htaccess
chmod 755 public
chmod 644 src/Config/config.php

echo "Recommendations applied. Please review the changes and adjust as necessary."
