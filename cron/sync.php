<?php

require_once __DIR__ . '/../bootstrap.php';

$source = $_ENV['XML_SOURCE_URL'];

function log_message($msg) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(LOG_PATH, "[$timestamp] $msg\n", FILE_APPEND);
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

    if (file_put_contents(DATA_PATH, $content) === false) {
        throw new Exception("Impossible d'écrire dans " . DATA_PATH . " - permissions insuffisantes?");
    }

    restore_error_handler();
    log_message("✓ Synchronisation réussie. Fichier sauvegardé: " . DATA_PATH);

} catch (Exception $e) {
    log_message("✗ Erreur: " . $e->getMessage());
    exit(1);
}