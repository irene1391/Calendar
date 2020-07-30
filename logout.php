<?php
//this is the backend file of user logout
header("Content-Type: application/json");
$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "http://localhost:3000" || $http_origin == "http://3.89.103.49") {
    header("Access-Control-Allow-Origin: $http_origin");
    header("Access-Control-Allow-Credentials: true");
}
session_name("MOD5ID");
ini_set("session.cookie_httponly", 1);
session_start();

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

if (!$_SESSION['user_id'] || !$_SESSION['token'] || !$json_obj['token'] || $_SESSION['token'] != $json_obj['token']) {
  echo json_encode([ 'isSuccessful' => false ]);
  exit;
}

session_destroy();

echo json_encode([ 'isSuccessful' => true ]);
exit;
