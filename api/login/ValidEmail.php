<?php
require_once "../../dlc/php/consts.php";
require_once "../../dlc/php/functions.php";
require_once "../../dlc/php/Class_usr.php";
require_once "../../dlc/php/Class_val.php";
session_start();

$input = filter_input_array(INPUT_GET);

if(!(isset($input['username']) && isset($input['token']) && isset($input['email']))) {
    undersite("email.php?status=error&message=wtf");
    exit();
}
if(!Validate::email($input['email'])){
    undersite("email.php?status=error&message=invalid");
    exit();
}
undersite("email.php?status=success");
exit();