<?php
/*
Path: /
Name: Index
Description: Retourne tous les biens de l\'agence
*/

require_once __DIR__ . '/../bootstrap.php';

$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "Other";
header("Content-type: application/json;");
header("User-agent: " . $userAgent . ";");

/**
 * @var xml SimplexmlElement
 */
$xml = @simplexml_load_file(DATA_PATH, 'SimpleXMLElement', LIBXML_NOCDATA);

if ($xml === false) {
  http_response_code(502);
  echo json_encode(['status' => 502, 'data' => []]);
  exit();
}

echo (json_encode(
  [
    'status' => 200,
    'path' => '/',
    'name' => 'index',
    'description' => 'Retourne tous les biens de l\'agence',
    'data' => $xml->xpath('//BIEN'),
  ],
  JSON_UNESCAPED_UNICODE
));