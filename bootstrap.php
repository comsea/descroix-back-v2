<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define('DATA_PATH', __DIR__ . '/data/dataset.xml');
define('LOG_PATH', __DIR__ . '/data/sync.log');
