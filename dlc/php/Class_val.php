<?php
require_once("functions.php");

class Validate{
    public static function email($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public static function username($username){
        return preg_match("/^[a-zA-Z0-9]+$/", $username);
    }
    public static function password($password){
        return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/", $password);
    }
    public static function fullname($fullname){
        return preg_match("/^[a-zA-Z0-9 ]+$/", $fullname);
    }

    
}