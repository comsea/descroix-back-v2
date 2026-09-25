<?php
/*
Path: /contact
Name: Contact
Description: Traitement du formulaire de contact
*/
header("Content-type: application/json;");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "Other";
header("User-agent: " . $userAgent . ";");

function slugify($text)
{
  // replace ()
  $text = str_replace('(', '', $text);
  $text = str_replace(')', '', $text);

  // replace .
  $text = str_replace('.', '', $text);

  // replace accent
  $text = str_replace('é', 'e', $text);
  $text = str_replace('è', 'e', $text);
  $text = str_replace('ê', 'e', $text);
  $text = str_replace('à', 'a', $text);
  $text = str_replace('â', 'a', $text);
  $text = str_replace('ô', 'o', $text);
  $text = str_replace('î', 'i', $text);
  $text = str_replace('ï', 'i', $text);
  $text = str_replace('ç', 'c', $text);
  $text = str_replace('ù', 'u', $text);
  $text = str_replace('û', 'u', $text);
  $text = str_replace('É', 'E', $text);
  $text = str_replace('È', 'E', $text);
  $text = str_replace('Ê', 'E', $text);
  $text = str_replace('À', 'A', $text);
  $text = str_replace('Â', 'A', $text);
  $text = str_replace('Ô', 'O', $text);
  $text = str_replace('Î', 'I', $text);
  $text = str_replace('Ï', 'I', $text);
  $text = str_replace('Ç', 'C', $text);
  $text = str_replace('Ù', 'U', $text);
  $text = str_replace('Û', 'U', $text);

  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
  http_response_code(405);
  echo json_encode([
    'status' => '405',
    'error' => 'Vous devez utiliser le protocole POST'
  ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  echo json_encode([
    'status' => '200',
    'error' => 'Cors test passed'
  ]);
  exit();
}

$fields = [
  'slug' => array(
    'value' => false,
  )
];

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

foreach ($fields as $name => $value) {
  $fields[$name]['value'] =  isset($input[$name]) ? strip_tags(htmlspecialchars($input[$name])) : false;
}

/**
 * @var xml SimplexmlElement
 */
$xml = @simplexml_load_file('/var/www/data/dataset.xml', 'SimpleXMLElement', LIBXML_NOCDATA);

if ($xml === false) {
  http_response_code(502);
  echo json_encode(['status' => 502, 'error' => 'Service temporairement indisponible']);
  exit();
}

foreach ($xml->xpath('//BIEN') as $child) {
  $slug = slugify($child->INFO_GENERALES->AFF_ID);
  if ($slug === $fields['slug']['value']) {
    echo json_encode([
      'status' => '200',
      'data' => $child
    ]);
    exit();
  }
}

http_response_code(404);
echo json_encode([
  'status' => 404,
  'error' => 'Bien introuvable'
]);