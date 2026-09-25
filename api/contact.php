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

// Create the email and send the message
$to = 'contact@cabinetdescroix.fr'; // Add your email address inbetween the '' replacing yourname@yourdomain.com - This is where the form will send a message to.
$email_subject = "Message depuis le formulaire de contact :" . $fields['sujet']['value'];
$email_body = "Vous avez reçu un email provenant de votre formulaire de contact en ligne.\n\n" . "\nNom: " . $fields['nom']['value'] . "\n\nPrénom: " . $fields['prenom']['value'] . "\n\nEmail: " . $fields['email']['value'] . "\n\nTéléphone:" . $fields['phone']['value'] . "\n\nObjet: " . $fields['sujet']['value'] . "\n\nMessage:\n" . $fields['message']['value'];
$headers = "From: noreply@descroixapi.comsea.fr"; // This is the email address the generated message will be from. We recommend using something like noreply@yourdomain.com.
mail($to, $email_subject, $email_body, $headers);

echo json_encode([
  'status' => 200,
  'message' => 'Votre message a bien été envoyé'
]);
