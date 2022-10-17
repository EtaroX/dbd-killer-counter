<?php
require_once("consts.php");
require_once("functions.php");
class User{
  private $connection;
  private $id;
  private $username;
  private $fullname;
  private $email;
  private $cookie;
  private $tempauth;

  public function __construct($username, $password){
    $this->username = $username;
    if($this->authenticate($password) == true){
      $this->logData();
      $this->generateToken();
      $this->closeConnection();
    }
  }
  public function __call( $name, $arguments ) {
    $function = array(
      "getUsername" => function(){
        return $this->username;
      },
      "getFullname" => function(){
        return $this->fullname;
      },
      "getEmail" => function(){
        return $this->email;
      },
    );
    if(array_key_exists($name, $function)){
      return $function[$name]();
    }


  }
  public function isAuth(){
    if($this->tempauth == true){
    $this->tempauth = false;
      return true;
    }
    if($_COOKIE["aToken"] == $this->cookie)
    return true;
    else return false;
  }

  private function logData(){
    if (!$this->openConnection()) return "no-conn";
    $ip = $this->connection->real_escape_string(getIP());
    $id = $this->connection->real_escape_string($this->id);;
    $sql = "INSERT INTO `log` (`IP`, `ID_USER`) VALUES ('$ip', '$id')";
  }
  private function generateToken(){
    if (!$this->openConnection()) return "no-conn";
    $cookie = random_str(64);
    $this->cookie = $cookie;
    $id = $this->connection->real_escape_string($this->id);;
    $query = "INSERT INTO `cookies` (`ID_USER`, `COOKIE`, `EXPIRE_DATE`) VALUES ('$id', '$cookie', DATE_ADD(NOW(), INTERVAL 1 DAY))";
    $this->connection->query($query);
    setcookie("aToken", $cookie, time() + (86400 * 30), "/", HOST, SECUREONLY);
    $this->tempauth = true;
  }

  private function authenticate($password){
    if ($password == null) $password = "";
    if (!$this->openConnection()) return "no-conn";
    $user =  $this->connection->real_escape_string($this->username);
    $sql = "SELECT * FROM `users` WHERE `USERNAME` = '$user'";
    $result = $this->connection->query($sql);
    if ($result->num_rows == 0) {
      return "invalid";
    }
    $row = $result->fetch_assoc();
    $result->free();
    if (password_verify($password, $row['PASSWORD'])) {
      $this->id = $row['ID_USER'];
      $this->fullname = $row['FULLNAME'];
      $this->email = $row['EMAIL'];
      return true;
    } else return "invalid";
  }
  //* technical stuff
  private function openConnection()  {
    if ($this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME)) {$this->connection->set_charset("utf-8"); return true;}
    return false;
  }
  private function closeConnection(){
    if (get_class($this->connection) != "mysqli") {
      return true;
    }
    if (get_class($this->connection) == "mysqli") {
      if ($this->connection->ping()) {
        $this->connection->close();
        return true;
      } else return true;
    }
    return false;
  }

  public function jsonSerialize(){
    $vars = get_object_vars($this);
    return $vars;
  }
}
