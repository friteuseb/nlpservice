<?php

function analyzePath($path, $baseDir = '') {
    $result = [];
    $entries = scandir($path);

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') continue;

        $fullPath = $path . DIRECTORY_SEPARATOR . $entry;
        $relativePath = $baseDir ? $baseDir . DIRECTORY_SEPARATOR . $entry : $entry;

        if (is_dir($fullPath)) {
            $result[$entry] = analyzePath($fullPath, $relativePath);
        } else {
            $result[$entry] = analyzeFile($fullPath, $relativePath);
        }
    }

    return $result;
}

function analyzeFile($path, $relativePath) {
    $content = file_get_contents($path);
    $issues = [];

    // Vérifier les inclusions
    preg_match_all('/require(_once)?\s*\(?\s*[\'"](.+?)[\'"]\s*\)?/', $content, $matches);
    foreach ($matches[2] as $include) {
        if (!file_exists(dirname($path) . DIRECTORY_SEPARATOR . $include)) {
            $issues[] = "Inclusion non trouvée: $include";
        }
    }

    // Vérifier les définitions de constantes
    preg_match_all('/define\s*\(\s*[\'"](.+?)[\'"]\s*,/', $content, $matches);
    $constants = $matches[1];

    // Vérifier l'utilisation de l'API
    if (strpos($content, 'API_URL') !== false) {
        $issues[] = "Utilise API_URL";
    }

    return [
        'path' => $relativePath,
        'size' => filesize($path),
        'issues' => $issues,
        'constants' => $constants
    ];
}

$projectRoot = __DIR__;
$structure = analyzePath($projectRoot);

$report = "Rapport d'analyse du projet\n\n";
$report .= "Structure du projet :\n";
$report .= json_encode($structure, JSON_PRETTY_PRINT) . "\n\n";

$report .= "Problèmes détectés :\n";
foreach ($structure as $key => $value) {
    $report .= analyzeStructure($key, $value);
}

function analyzeStructure($key, $value, $indent = '') {
    $report = '';
    if (is_array($value) && isset($value['path'])) {
        $report .= $indent . $value['path'] . "\n";
        if (!empty($value['issues'])) {
            foreach ($value['issues'] as $issue) {
                $report .= $indent . "  - " . $issue . "\n";
            }
        }
        if (!empty($value['constants'])) {
            $report .= $indent . "  Constants définies: " . implode(', ', $value['constants']) . "\n";
        }
    } else {
        $report .= $indent . $key . "/\n";
        foreach ($value as $subKey => $subValue) {
            $report .= analyzeStructure($subKey, $subValue, $indent . "  ");
        }
    }
    return $report;
}

file_put_contents('project_analysis_report.txt', $report);
echo "Rapport généré dans project_analysis_report.txt\n";