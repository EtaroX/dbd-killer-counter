<?php
require_once "../../dlc/php/consts.php";
require_once "../../dlc/php/functions.php";
require_once "../../dlc/php/Class_usr.php";
session_start();

header('Content-Type: application/json');

$input = filter_input_array(INPUT_POST);

if (!isset($input['username']) && !isset($input['password'])) {
    $response = array(
        "status" => "error",
        "message" => "Invalid request",
    );
    echo json_encode($response);
    exit;
}
$user = User::login($input['username'], $input['password']);
if (gettype($user) != "object") {
    $response = array(
        "status" => "error",
        "message" => $user,
    );
    echo json_encode($response);
    exit;
}

$response = array(
    "status" => "success",
);
echo json_encode($response);
$_SESSION["user"] = $user;
exit;
