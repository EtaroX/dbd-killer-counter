<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (DB_TYPE) require_once("../../dlc/vendor/JSONDB/Store.php");

require_once("../../dlc/vendor/PHPmail/autoload.php");
require_once("consts.php");
require_once("functions.php");
require_once("Class_val.php");

class User {
  private $connection;
  private $id;
  private $username;
  private $fullname;
  private $email;
  private $cookie;

  public function __construct() {
  }
  public function __destruct() {
    $this->closeConnection();
  }

  /*
  *   @param $input array
  *   @return boolean
  */
  public static function register(array $array) {
    $instance = new self();
    $instance->openConnection();
    if (!(Validate::username($array["username"]) && Validate::password($array["password"]) && Validate::email($array["email"]))) {
      $instance->__destruct();
      return false;
    }
    if (isset($array["fullname"])) {
      if (!Validate::fullname($array["fullname"])) {
        $instance->__destruct();
        return false;
      }
    }
    if (!$instance->priv_register($array)) {
      $instance->__destruct();
      return false;
    }
    if (!$instance->sendemail($array["email"], $array("username"))) {
      $instance->__destruct();
      return false;
    }
    $instance->__destruct();
    return true;
  }

  private function priv_register(array $array) {
    $array['password'] = password_hash($array['password'], PASSWORD_DEFAULT);
    return DataHandler::registerUser($array);
  }



  /*
  *   @param $username string, $password string
  *   @return Class, "no-conn", "invalid", "not-activated"
  */
  public static function login($username, $password) {
    $instance = new self();
    if ($temp = $instance->priv_login($username, $password) == true)
      return $instance;
    else return $temp;
  }
  public function __call($name, $arguments) {
    $function = array(
      "getUsername" => function () {
        return $this->username;
      },
      "getFullname" => function () {
        return $this->fullname;
      },
      "getEmail" => function () {
        return $this->email;
      },
    );
    if (array_key_exists($name, $function)) {
      return $function[$name]();
    }
  }

  public function isAuth() {
    if ($_COOKIE["aToken"] == $this->cookie)
      return true;
    else return false;
  }
  public function logout() {
    DataHandler::deleteCookie($this->cookie);
    setcookie("aToken", "", time() - 3600, "/");
    $this->cookie = "";
    $this->__destruct();
  }




  private function priv_login($username, $password) {
    $this->username = $username;
    if (($temp = $this->authenticate($password)) == true) {
      unset($password);
      $this->logData();
      $this->generateToken();
      $this->closeConnection();
      return true;
    } else {
      return $temp;
    }
  }

  private function logData() {
    DataHandler::insertLoggedData(getIP(), $this->id);
  }

  private function generateToken() {
    $cookie = random_str(64);
    $this->cookie = $cookie;
    DataHandler::insertCookie($this->id, $cookie);
    setcookie(COOKIE_TOKEN_NAME, $cookie, time() + (86400 * 30), COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
  }

  private function authenticate($password) {
    if ($password == null) $password = "";
    $row = DataHandler::getUserAuth($this->username);
    if (!password_verify($password, $row['PASSWORD'])) return "invalid";
    if ($row['ACTIVE'] == 0) return "not-activated";
    $this->id = $row['ID_USER'];
    $this->fullname = $row['FULLNAME'];
    $this->email = $row['EMAIL'];
    return true;
  }



  //* Email stuff
  private function sendemail($email, $username) {
    //function to send mail with link to api/login/ValidEmail.php
    $randstr = random_str(5);
    if (EMAIL_DB_TYPE == 'json') {
      $jsondb = new \SleekDB\Store("email", EMAIL_DB_JSON_DIR);
      $datetime = new DateTime();
      $datetime->add(new DateInterval('PT1H'));
      $jsondb->insert([
        "email" => $email,
        "username" => $username,
        "token" => $randstr,
        "expire" => $datetime->getTimestamp()
      ]);
      unset($jsondb);
      unset($datetime);
    } else if (EMAIL_DB_TYPE == 'mysql') {
      $this->openConnection();
      $this->connection->query("INSERT INTO `email` (`email`, `username`, `token`,`EXPIRE_DATE`) VALUES ('$email', '$username', '$randstr', DATE_ADD(NOW(), INTERVAL 1 HOUR));");
      $this->closeConnection();
    }
    $token = md5($username . $randstr . $email);
    $link = "http://" . HOST . PODFOLDER . "api/login/ValidEmail.php?token=" . $token;
    $mail = new PHPMailer();

    $mail->isSMTP();
    $mail->Host = EMAIL_HOST;
    $mail->SMTPAuth = EMAIL_AUTH;
    $mail->Username = EMAIL_USER;
    $mail->Password = EMAIL_PASS;
    $mail->SMTPSecure = 'tls';

    $mail->From = EMAIL_FROM;
    $mail->FromName = EMAIL_FROM_NAME;
    $mail->addAddress($email);

    $mail->isHTML(true);

    $mail->Subject = 'DBD counter validation';
    $mail->Body    = $link; //TODO: body of email
    $mail->AltBody = $link; //TODO: body of email

    if (!$mail->send()) {
      return false;
    } else {
      return true;
    }
  }

  static public function ValidEmail($token, $username, $email) {
    $instance = new self();
    if ($instance->priv_ValidEmail($token, $username, $email) == true) {
      $instance->__destruct();
      return true;
    }
    $instance->__destruct();
    return false;
  }
  private function priv_ValidEmail($token, $username, $email) {
    $this->openConnection();
    $array = [];
    if (EMAIL_DB_TYPE == 'json') {
      $jsondb = new \SleekDB\Store("email", EMAIL_DB_JSON_DIR);
      $result = $jsondb->findBy(["email" => $email, "username" => $username]);
      if ($result == null) return false;
      $result = $result[0];
      if ($result["expire"] < time()) {
        $jsondb->deleteBy(["email" => $email, "username" => $username]);
        return false;
      }
      $array = $result;
    } else if (EMAIL_DB_TYPE == 'mysql') {
      $username = $this->connection->real_escape_string($username);
      $email = $this->connection->real_escape_string($email);
      $sql = "SELECT * FROM `email` WHERE `username` = '$username' AND `email` = '$email'";
      $result = $this->connection->query($sql);
      if ($result->num_rows == 0) {
        return false;
      }
      $row = $result->fetch_assoc();
      $result->free();
      if ($row["EXPIRE_DATE"] < date("Y-m-d H:i:s")) {
        $this->connection->query("DELETE FROM `email` WHERE `username` = '$username' AND `email` = '$email'");
        return false;
      }
      $array = $row;
    }


    if (md5($array['username'] . $array["token"] . $array['email']) == $token) {
      if (EMAIL_DB_JSON) {
        $jsondb->deleteBy(["email" => $email, "username" => $username]);
      } else {
        $this->connection->query("DELETE FROM `email` WHERE `username` = '$username' AND `email` = '$email'");
      }
      $username = $this->connection->real_escape_string($username);
      $email = $this->connection->real_escape_string($email);
      $this->connection->query("UPDATE `users` SET `ACTIVE` = 1 WHERE `username` = '$username' AND `email` = '$email'");
      return true;
    } else return false;
  }



  //* technical stuff
  private function openConnection() {
    if ($this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
      $this->connection->set_charset("utf8");
      return true;
    }
    return false;
  }
  private function closeConnection() {
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

  public function jsonSerialize() {
    $vars = get_object_vars($this);
    return $vars;
  }
}
