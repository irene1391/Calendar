<?php
//this is the backend file of adding event
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
  respond_json([ 'isError' => true ]);
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

$new_event_id = $event_query->add([
  'user_id' => $_SESSION['user_id'],
  'title' => (string)$json_obj['title'],
  'content' => (string)$json_obj['content'],
  'year' => (string)$json_obj['year'],
  'month' => (string)$json_obj['month'],
  'day' => (string)$json_obj['day'],
  'start_hr' => (string)$json_obj['startHr'],
  'start_min' => (string)$json_obj['startMin'],
  'end_hr' => (string)$json_obj['endHr'],
  'end_min' => (string)$json_obj['endMin'],
  'tag' => (string)$json_obj['tag'],
  'coord' => (string)$json_obj['coord']
]);

$event_query->cleanup();

if (!$new_event_id) {
  bail_out();
}
//respod to front end and show this information in calendar
respond_json([
  'id' => $new_event_id,
  'title' => htmlentities($json_obj['title']),
  'content' => htmlentities($json_obj['content']),
  'year' => htmlentities($json_obj['year']),
  'month' => htmlentities($json_obj['month']),
  'day' => htmlentities($json_obj['day']),
  'startHr' => htmlentities($json_obj['startHr']),
  'startMin' => htmlentities($json_obj['startMin']),
  'endHr' => htmlentities($json_obj['endHr']),
  'endMin' => htmlentities($json_obj['endMin']),
  'tag' => htmlentities($json_obj['tag']),
  'coord' => htmlentities($json_obj['coord'])
]);

exit;
