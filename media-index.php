<?php

declare(strict_types=1);

$baseDir = __DIR__ . '/data';
$mediaFiles = [];

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webm', 'mp4', 'mov'];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $ext = strtolower($file->getExtension());
        if (in_array($ext, $allowedExtensions)) {
            $relativePath = str_replace($baseDir . '/', '', $file->getPathname());
            $mediaFiles[] = $relativePath;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($mediaFiles, JSON_PRETTY_PRINT);
