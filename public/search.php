<?php
/*
Path: /search
Name: Search
Description: Recherche les appartements de l'agence
*/

require_once __DIR__ . '/../bootstrap.php';

header("Content-type: application/json;");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "Other";
header("User-agent: " . $userAgent . ";");

if ($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
  http_response_code(405);
  echo json_encode([
    'status' => '405',
    'error' => 'Vous devez utiliser le protocole POST'
  ]);
}

$fields = [
  'operation' => array(
    'label' => 'Type d\'opération',
    'value' => false,
  ),
  'type' => array(
    'label' => 'Type de bien',
    'value' => false,
  ),
  'bedrooms' => array(
    'label' => 'Nombre de chambre(s)',
    'value' => false,
  ),
  'area' => array(
    'label' => 'Surface minimum',
    'value' => false,
  ),
  'budget' => array(
    'label' => 'Budget maximum',
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
$xml = @simplexml_load_file(DATA_PATH, 'SimpleXMLElement', LIBXML_NOCDATA);

if ($xml === false) {
  http_response_code(502);
  echo json_encode(['status' => 502, 'data' => []]);
  exit();
}

$biens = $xml->xpath('//BIEN');

$result = [];

const operations = [
  'LOCATION',
  'VENTE'
];

foreach (operations as $operation) {
  if ($fields['operation']['value'] === $operation || !in_array($fields['operation']['value'], operations)) {
    foreach ($biens as $bien) {
      if (isset($bien->$operation)) {
        array_push($result, $bien);
      }
    }
  }
}

const types = [
  'APPARTEMENT',
  'MAISON',
  'DEMEURE',
  'FORET',
  'IMMEUBLE',
  'PARKING',
  'TERRAIN'
];

foreach (types as $type) {
  if ($fields['type']['value'] === $type) {
    if ($result !== []) {
      $result = array_filter($result, function ($bien) use ($type) {
        return isset($bien->$type);
      });
    } else {
      foreach ($biens as $bien) {
        if (isset($bien->$type)) {
          array_push($result, $bien);
        }
      }
    }
  }
}

function filter_bedrooms($bien, $fields)
{
  // Si il y a un type de bien sélectionné
  if ($fields['type']['value'] !== false) {
    $type = $fields['type']['value'];
    return $bien->$type->NBRE_CHAMBRES >= intval($fields['bedrooms']['value']);
  } else {
    // Sinon on boucle sur les types de bien
    foreach (types as $type) {
      if (isset($bien->$type->NBRE_CHAMBRES)) {
        return $bien->$type->NBRE_CHAMBRES >= intval($fields['bedrooms']['value']);
      }
    }
  }
}

if ($fields['bedrooms']['value'] !== false) {
  // Si il y a déjà un résultat
  if ($result !== []) {
    // On filtre le tableau
    $result = array_filter($result, function ($bien) use ($fields) {
      return filter_bedrooms($bien, $fields);
    });
  } else {
    // On boucle sur les biens
    foreach ($biens as $bien) {
      if (filter_bedrooms($bien, $fields) === true) {
        array_push($result, $bien);
      }
    }
  }
}

function filter_surface($bien, $fields)
{
  // Si il y a un type de bien sélectionné
  if ($fields['type']['value'] !== false) {
    $type = $fields['type']['value'];
    return $bien->$type->SURFACE_HABITABLE >= intval($fields['area']['value']);
  } else {
    // Sinon on boucle sur les types de bien
    foreach (types as $type) {
      if (isset($bien->$type->SURFACE_HABITABLE)) {
        return $bien->$type->SURFACE_HABITABLE >= intval($fields['area']['value']);
      }
    }
  }
}


if ($fields['area']['value'] !== false) {
  // Si il y a déjà un résultat
  if ($result !== []) {
    // On filtre le tableau
    $result = array_filter($result, function ($bien) use ($fields) {
      return filter_surface($bien, $fields);
    });
  } else {
    // On boucle sur les biens
    foreach ($biens as $bien) {
      if (filter_surface($bien, $fields) === true) {
        array_push($result, $bien);
      }
    }
  }
}

function filter_budgets($bien, $fields)
{
  // Si il y a un type d'opération sélectionné
  if (in_array($fields['operation']['value'], operations)) {
    $operation = $fields['operation']['value'];
    if ($operation === 'VENTE') {
      // Si on cherche une vente
      return $bien->$operation->PRIX <= intval($fields['budget']['value']);
    } else {
      // Sinon on cherche une location
      return (intval($bien->$operation->LOYER) + intval($bien->$operation->PROVISION_SUR_CHARGES)) <= intval($fields['budget']['value']);
    }
  } else {
    // Sinon on boucle sur les types de bien
    foreach (operations as $operation) {
      if (isset($bien->$operation)) {
        if ($operation === 'VENTE') {
          // Si on cherche une vente
          return $bien->$operation->PRIX <= intval($fields['budget']['value']);
        } else {
          // Sinon on cherche une location
          return (intval($bien->$operation->LOYER) + intval($bien->$operation->PROVISION_SUR_CHARGES)) <= intval($fields['budget']['value']);
        }
      }
    }
  }
}

if ($fields['budget']['value'] !== false) {
  // Si il y a déjà un résultat
  if ($result !== []) {
    // On filtre le tableau
    $result = array_filter($result, function ($bien) use ($fields) {
      return filter_budgets($bien, $fields);
    });
  } else {
    // On boucle sur les biens
    foreach ($biens as $bien) {
      if (filter_budgets($bien, $fields) === true) {
        array_push($result, $bien);
      }
    }
  }
}


echo json_encode(
  [
    'status' => 200,
    'data' => array_values($result)
  ]
);