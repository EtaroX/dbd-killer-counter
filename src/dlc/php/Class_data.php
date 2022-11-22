<?php

class DataHandler {
  private $connection;
  private $connectionfunctions;
  private function __construct() {
    $this->getConnectionFunctions();
  }
  private function __destruct() {
    $this->closeConnection();
  }
  public static function getUserAuth($username) {
    $instance = new self();
    $instance->openConnection();
    switch (DB_TYPE) {
      case "mysql":
        $result = $instance->priv_MYSQL_getUser($username);
        break;
      case "json":
        $result = $instance->priv_JSON_getUser($username);
        break;
    }
    $instance->__destruct();
    return $result;
  }

  private function priv_MYSQL_getUser($username) {
    $user =  $this->connection->real_escape_string($username);
    $sql = "SELECT * FROM `users` WHERE `USERNAME` = '$user' AND `ACTIVE` != 2";
    $result = $this->connection->query($sql);
    if ($result->num_rows == 0) {
      return "invalid";
    }
    $row = $result->fetch_assoc();
    $result->free();
    return $row;
  }
  private function priv_JSON_getUser($username) {
  }

  public static function insertLoggedData($ip, $id) {
    $instance = new self();
    $instance->openConnection();
    switch (DB_TYPE) {
      case "mysql":
        $result = $instance->priv_MYSQL_insertLoggedData($ip, $id);
        break;
      case "json":
        $result = $instance->priv_JSON_insertLoggedData($ip, $id);
        break;
    }
    $instance->__destruct();
    return $result;
  }
  private function priv_MYSQL_insertLoggedData($ip, $id) {
    $ip = $this->connection->real_escape_string(getIP());
    $id = $this->connection->real_escape_string($this->id);;
    $sql = "INSERT INTO `log` (`IP`, `ID_USER`) VALUES ('$ip', '$id')";
    if (!$this->connection->query($sql)) {
      return false;
    }
    return true;
  }
  private function priv_JSON_insertLoggedData($ip, $id) {
  }

  public static function insertCookie($cookie, $id){
    $instance = new self();
    $instance->openConnection();
    switch (DB_TYPE) {
      case "mysql":
        $result = $instance->priv_MYSQL_insertCookie($cookie, $id);
        break;
      case "json":
        $result = $instance->priv_JSON_insertCookie($cookie, $id);
        break;
    }
    $instance->__destruct();
    return $result;
  }
  private function priv_MYSQL_insertCookie($cookie, $id){
    $cookie = $this->connection->real_escape_string($cookie);
    $id = $this->connection->real_escape_string($this->id);
    $query = "INSERT INTO `cookies` (`ID_USER`, `COOKIE`, `EXPIRE_DATE`) VALUES ('$id', '$cookie', DATE_ADD(NOW(), INTERVAL 1 DAY))";
    $this->connection->query($query);
  }
  private function priv_JSON_insertCookie($cookie, $id) {
  }

  public static function deleteCookie($cookie){
    $instance = new self();
    $instance->openConnection();
    switch (DB_TYPE) {
      case "mysql":
        $result = $instance->priv_MYSQL_deleteCookie($cookie);
        break;
      case "json":
        $result = $instance->priv_JSON_deleteCookie($cookie);
        break;
    }
    $instance->__destruct();
    return $result;
  }
  private function priv_MYSQL_deleteCookie($cookie){
    $cookie = $this->connection->real_escape_string($cookie);
    if(COOKIE_REMOVE == true)
      $query = "DELETE FROM `cookies` WHERE `COOKIE` = '$cookie'";
    else $query = "UPDATE `cookies` SET active = 0 WHERE `COOKIE` = '$cookie'";
    $this->connection->query($query);
  }
  
  private function priv_JSON_deleteCookie($cookie) {
  }

  //* techincal functions
  private function getConnectionFunctions() {
    switch (DB_TYPE) {
      case "mysql":
        $this->connectionfunctions = [
          'openConnection' => function () {
            return new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
          },
          'closeConnection' => function () {
            $this->connection->close();
          }
        ];
        break;
      case "json": //TODO: add json support
        $this->connectionfunctions = [
          'openConnection' => function () {
          },
          'closeConnection' => function () {
          }
        ];
        break;
    }
  }
  private function openConnection() {
    $this->connection = $this->connectionfunctions['openConnection']();
  }
  private function closeConnection() {
    $this->connectionfunctions['openConnection']();
  }
}
