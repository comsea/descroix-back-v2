<?php
/*
Path: /getNew
Name: getNew
Description: Retourne les trois appartements de l\'agence
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
    'data' => array_slice($xml->xpath('//BIEN'), 0, 3),
  ]
));