<?php

$file = '/var/www/data/dataset.xml';
$source = 'http://clients.ac3-distribution.com/office12/cabine_2102081130/cache/export.xml';
$logFile = '/var/log/descroix-sync.log';

function log_message($msg) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $msg\n", FILE_APPEND);
    echo $msg . "\n";
}

try {
    log_message("Début synchronisation XML depuis $source");

    set_error_handler(function($errno, $errstr) {
        throw new Exception("PHP Error: $errstr");
    });

    $xml = simplexml_load_file($source, 'SimpleXMLElement');

    if (!$xml) {
        throw new Exception("Impossible de parser le XML depuis la source");
    }

    $content = $xml->saveXML();

    if (!$content) {
        throw new Exception("saveXML() a retourné vide");
    }

    $content = iconv("ISO-8859-1//TRANSLIT", "UTF-8", $content);

    if ($content === false) {
        throw new Exception("Erreur de conversion ISO-8859-1 vers UTF-8");
    }

    $content = str_replace('encoding="ISO-8859-1"', 'encoding="UTF-8"', $content);

    if (file_put_contents($file, $content) === false) {
        throw new Exception("Impossible d'écrire dans $file - permissions insuffisantes?");
    }

    restore_error_handler();
    log_message("✓ Synchronisation réussie. Fichier sauvegardé: $file");

} catch (Exception $e) {
    log_message("✗ Erreur: " . $e->getMessage());
    exit(1);
}