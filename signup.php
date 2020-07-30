<?php
//this is the backend file of user register
require 'base.php';
header("Content-Type: application/json");
$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "http://localhost:3000" || $http_origin == "http://3.89.103.49") {
    header("Access-Control-Allow-Origin: $http_origin");
    header("Access-Control-Allow-Credentials: true");
}
function bail_out() {
  respond_json([ 'isSuccessful' => false ]);
  exit;
}

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

$trial_username = (string)$json_obj['username'];
$trial_password = (string)$json_obj['password'];

$user_query = new User();
$result = $user_query->query(['username' => $trial_username]);

if (count($result)) {
  $user_query->cleanup();
  bail_out();
}

$is_successful = $user_query->add([
  'username' => $trial_username,
  'password' => password_hash($trial_password, PASSWORD_BCRYPT)
]);

if (!$is_successful) {
  bail_out();
}

respond_json([ 'isSuccessful' => true ]);

exit;
