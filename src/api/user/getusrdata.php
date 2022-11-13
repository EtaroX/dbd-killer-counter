<?php
require_once "../../dlc/php/consts.php";
require_once "../../dlc/php/functions.php";
require_once "../../dlc/php/Class_usr.php";
session_start();

header('Content-Type: application/json');

if(!isset($_SESSION["user"])){
    $response = array(
        "status" => "error",
        "message" => "You are not logged in",
    );
    echo json_encode($response);
    exit;
}
if(!$_SESSION['user']->isAuth()){
    $response = array(
        "status" => "error",
        "message" => "You are not logged in",
    );
    echo json_encode($response);
    exit;
}
