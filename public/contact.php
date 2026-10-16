<?php
/*
Path: /contact
Name: Contact
Description: Traitement du formulaire de contact
*/

require_once __DIR__ . '/../bootstrap.php';

use App\Mailer;

header("Content-type: application/json;");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  echo json_encode([
    'status' => 200,
    'message' => 'Cors test passed'
  ]);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode([
    'status' => 405,
    'error' => 'Vous devez utiliser le protocole POST'
  ]);
  exit();
}

$fields = [
  'prenom' => array(
    'value' => false,
  ),
  'nom' => array(
    'value' => false,
  ),
  'email' => array(
    'value' => false,
  ),
  'phone' => array(
    'value' => false,
  ),
  'sujet' => array(
    'value' => false,
  ),
  'message' => array(
    'value' => false,
  )
];

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

foreach ($fields as $name => $value) {
  $fields[$name]['value'] =  isset($input[$name]) ? strip_tags(htmlspecialchars($input[$name])) : false;
}

$subject = "Message depuis le formulaire de contact : " . $fields['sujet']['value'];
$body = "Vous avez reçu un message depuis le formulaire de contact en ligne.\n\n"
  . "Nom: " . $fields['nom']['value'] . "\n"
  . "Prénom: " . $fields['prenom']['value'] . "\n"
  . "Email: " . $fields['email']['value'] . "\n"
  . "Téléphone: " . $fields['phone']['value'] . "\n"
  . "Objet: " . $fields['sujet']['value'] . "\n\n"
  . "Message:\n" . $fields['message']['value'];

$sent = Mailer::send($subject, $body, $fields['email']['value'] ?: null);

if (!$sent) {
  http_response_code(502);
  echo json_encode([
    'status' => 502,
    'error' => "Erreur lors de l'envoi du message, réessayez plus tard"
  ]);
  exit();
}

echo json_encode([
  'status' => 200,
  'message' => 'Votre message a bien été envoyé'
]);
