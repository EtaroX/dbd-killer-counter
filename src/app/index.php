<?php
session_start();

if(isset($_POST['logout']) && $_POST['logout'] == 'logout'){
  purgeAll();
  undersite("index.html?status=logout");
}

if($_SESSION['user']->isAuth()){
  purgeAll();
  undersite("index.html?status=timeout");
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DBD Killer Counter</title>
</head>
<body>

</body>
</html>