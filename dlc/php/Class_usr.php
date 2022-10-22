<?php
require_once("consts.php");
require_once("functions.php");
require_once("Class_val.php");

class User{
  private $connection;
  private $id;
  private $username;
  private $fullname;
  private $email;
  private $cookie;

  public function __construct(){
  }
  public function __destruct(){
    $this->closeConnection();
  }
  
  /*
  *   @param $input array
  *   @return boolean
  */
  public static function register(array $array){
    $instance = new self();
    $instance->openConnection();
    if(!(Validate::username($array["username"]) && Validate::password($array["password"]) && Validate::email($array["email"]))){ 
      $instance->__destruct();
      return false;
    }
    if(isset($array["fullname"])){
      if(!Validate::fullname($array["fullname"])){
        $instance->__destruct();
        return false;
      }
    }
    if(!$instance->priv_register($array)){
      $instance->__destruct();
      return false;
    }
    if(!$instance->sendemail($array["email"])){
      $instance->__destruct();
      return false;
    }
    $instance->__destruct();
    return true;
  }
  /*
  *   @param $username string, $password string
  *   @return Class, "no-conn", "invalid", "not-activated"
  */

  public static function login($username, $password){
    $instance = new self();
    if($temp = $instance->priv_login($username, $password) == true)
    return $instance;
    else return $temp;
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

  private function priv_register(array $array){
    if(!$this->openConnection()){
      return false;
    }
    $username = $this->connection->real_escape_string($array["username"]);
    $email = $this->connection->real_escape_string($array["email"]);
    if(!$this->connection->querry("SELECT * FROM users WHERE username = '$username' OR `email` = '$email';")){
      return false;
    }
    if($this->connection->affected_rows > 0){
      return false;
    }

    $fullname = $this->connection->real_escape_string($array["fullname"]);
    $password = $this->connection->real_escape_string(password_hash($array["password"], PASSWORD_DEFAULT));
    if(!$this->connection->query("INSERT INTO `users` (`username`, `fullname`, `email`, `password`) VALUES ('$username', '$fullname', '$email', '$password');")){
      return false;
    }
    return true;
  }
  private function sendemail($email){
     //function to send mail with link to api/login/ValidEmail.php
  }
  
  private function priv_login($username, $password){
    $this->username = $username;
    if(($temp = $this->authenticate($password)) == true){
      $this->logData();
      $this->generateToken();
      $this->closeConnection();
      return true;
    }
    else{
      return $temp;
    }
  }
  
  private function logData(){
    if (!$this->openConnection()) return "no-conn";
    $ip = $this->connection->real_escape_string(getIP());
    $id = $this->connection->real_escape_string($this->id);;
    $sql = "INSERT INTO `log` (`IP`, `ID_USER`) VALUES ('$ip', '$id')"; 
    $this->connection->query($sql);
  }
  private function generateToken(){
    if (!$this->openConnection()) return "no-conn";
    $cookie = random_str(64);
    $this->cookie = $cookie;
    $id = $this->connection->real_escape_string($this->id);;
    $query = "INSERT INTO `cookies` (`ID_USER`, `COOKIE`, `EXPIRE_DATE`) VALUES ('$id', '$cookie', DATE_ADD(NOW(), INTERVAL 1 DAY))";
    $this->connection->query($query);
    setcookie("aToken", $cookie, time() + (86400 * 30), "/", HOST, SECUREONLY);
  }

  private function authenticate($password){
    if ($password == null) $password = "";
    if (!$this->openConnection()) return "no-conn";
    $user =  $this->connection->real_escape_string($this->username);
    $sql = "SELECT * FROM `users` WHERE `USERNAME` = '$user' AND `ACTIVE` != 2";
    $result = $this->connection->query($sql);
    if ($result->num_rows == 0) {
      return "invalid";
    }
    $row = $result->fetch_assoc();
    $result->free();
    if (!password_verify($password, $row['PASSWORD'])) return "invalid";
      if($row['ACTIVE'] == 0) return "not-activated";
      $this->id = $row['ID_USER'];
      $this->fullname = $row['FULLNAME'];
      $this->email = $row['EMAIL'];
      return true;
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
