<?php
//this is the backend file of delete event
require 'base.php';
header("Content-Type: application/json");
$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "http://localhost:3000" || $http_origin == "http://3.89.103.49") {
    header("Access-Control-Allow-Origin: $http_origin");
    header("Access-Control-Allow-Credentials: true");
}
session_name("MOD5ID");
ini_set("session.cookie_httponly", 1);
session_start();

function bail_out() {
  respond_json([ 'isSuccessful' => false ]);
  exit;
}

function security_check($trial_token) {
  if (!$_SESSION['user_id'] || !$_SESSION['token'] || !$trial_token || $_SESSION['token'] != $trial_token) {
    bail_out();
  }
}

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

security_check((string)$json_obj['token']);

$event_query = new Event();

$event = $event_query->query([
  'id' => (int)$json_obj['id'],
  'user_id' => $_SESSION['user_id']
]);

if (!count($event)) {
  bail_out();
}

$is_successful = $event_query->delete((int)$json_obj['id']);

$event_query->cleanup();

if (!$is_successful) {
  bail_out();
}

respond_json([ 'isSuccessful' => true ]);

exit;
