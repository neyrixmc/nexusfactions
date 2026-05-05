<?php

/**
 * Script de build pour NexusFactions
 * Génère un fichier .phar prêt à l'emploi
 */

// Configuration
$pharName = "NexusFactions.phar";
$sourceDir = __DIR__;
$outputPath = __DIR__ . "/" . $pharName;

// Supprimer l'ancien .phar s'il existe
if (file_exists($outputPath)) {
    echo "Suppression de l'ancien .phar...\n";
    unlink($outputPath);
}

// Créer le .phar
echo "Création du .phar...\n";
$phar = new Phar($outputPath);
$phar->startBuffering();

// Définir le stub
$phar->setStub('<?php __HALT_COMPILER();');

// Ajouter les fichiers
$filesToAdd = [
    'plugin.yml',
    'resources/config.yml',
    'resources/messages.yml'
];

// Ajouter plugin.yml
echo "Ajout de plugin.yml...\n";
$phar->addFile($sourceDir . '/plugin.yml', 'plugin.yml');

// Ajouter les resources
echo "Ajout des resources...\n";
$phar->addFile($sourceDir . '/resources/config.yml', 'resources/config.yml');
$phar->addFile($sourceDir . '/resources/messages.yml', 'resources/messages.yml');

// Ajouter tous les fichiers PHP du dossier src
echo "Ajout des fichiers source...\n";
$srcDir = $sourceDir . '/src';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $relativePath = 'src/' . substr($file->getPathname(), strlen($srcDir) + 1);
        $relativePath = str_replace('\\', '/', $relativePath);
        echo "  - " . $relativePath . "\n";
        $phar->addFile($file->getPathname(), $relativePath);
    }
}

// Finaliser
$phar->stopBuffering();

echo "\n✓ Build terminé avec succès!\n";
echo "Fichier créé: " . $outputPath . "\n";
echo "Taille: " . round(filesize($outputPath) / 1024, 2) . " KB\n";
