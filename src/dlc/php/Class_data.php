<?php

class DataHandler{
    private $connection;
    private $connectionfunctions;
    private function __construct(){
        $this->getConnectionFunctions();
    }

    public static function getUserAuth($username){
      $instance = new self();
      $instance->openConnection();
      switch(DB_TYPE){
        case "mysql":
            $result = $instance->priv_MYSQL_getUser($username);
          break;
        case "json":
            $result = $instance->priv_JSON_getUser($username);
          break;
      }
      $instance->closeConnection();
      return $result;
  }

  private function priv_MYSQL_getUser($username){
  
  }
  private function priv_JSON_getUser($username){
  
  }
//* techincal functions
private function getConnectionFunctions(){
    switch(DB_TYPE){
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
  private function openConnection(){
    $this->connection = $this->connectionfunctions['openConnection']();
  }
  private function closeConnection(){
    $this->connectionfunctions['openConnection']();
  }

}