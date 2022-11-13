<?php
require_once "consts.php";
session_start();

//* localy purge everything
function purgeAll() {
  if (isset($_SESSION["user"]) && get_class($_SESSION["user"]) == "User") {
    $_SESSION["user"]->logout();
  }
  $_SESSION = array();

  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params["path"],
      $params["domain"],
      $params["secure"],
      $params["httponly"]
    );
  }

  session_destroy();
}




//* technical stuff


function getIP() {
  $ip = $_SERVER['REMOTE_ADDR'];
  if ($ip == "::1") {
    $ip = "127.0.0.1";
  }
  if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    return false;
  }
  return $ip;
}
function undersite($cd) {
  $host = HOST;
  header("Location: http://$host" . PODFOLDER . "/$cd");
  exit;
}
function random_str(
  int $length = 64,
  string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
  if ($length < 1) {
    throw new \RangeException("Length must be a positive integer");
  }
  $pieces = [];
  $max = mb_strlen($keyspace, '8bit') - 1;
  for ($i = 0; $i < $length; ++$i) {
    $pieces[] = $keyspace[random_int(0, $max)];
  }
  return implode('', $pieces);
}
function console_log($msg) {
  echo "<script>console.log('$msg');</script>";
}
