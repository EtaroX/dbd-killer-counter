<?php
require_once "../../dlc/php/consts.php";
require_once "../../dlc/php/functions.php";
require_once "../../dlc/php/Class_usr.php";
require_once "../../dlc/php/Class_val.php";
session_start();

header('Content-Type: application/json');

$input = filter_input_array(INPUT_POST);

if(!(isset($input['username']) && isset($input['password']) && isset($input['email']))) {
    echo json_encode(array("status" => "error", "message" => "Invalid input"));
    exit();
}
if($register = User::register($input)) {
    echo json_encode(array("status" => "success"));
    exit();
} else {
    echo json_encode(array("status" => "error", "message" => "Invalid input"));
    exit();
}
