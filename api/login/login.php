<?php
require_once "consts.php";
require_once "functions.php";
require_once "usrClass.php";

header('Content-Type: application/json');

$input = filter_input_array(INPUT_POST);

if (!isset($input['username']) && !isset($input['password'])) {
    $response = array(
        "status" => "error",
        "message" => "Invalid request",
    );
    exit;
}
$user = new User($input['username'], $input['password']);
if (!$user->isAuth()) {
    $response = array(
        "status" => "error",
        "message" => "Invalid username or password",
    );
    exit;
}

$response = array(
    "status" => "success",
    "message" => "Logged in successfully",
    "username" => $user->getUsername(),
    "fullname" => $user->getFullname(),
    "email" => $user->getEmail(),
);
exit;
