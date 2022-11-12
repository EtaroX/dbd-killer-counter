<?php
define("HOST", $_SERVER['HTTP_HOST']);
define("PODFOLDER", "/technoblade/dbd-killer-counter/src");


//*Database connection data
define('DB_TYPE', 'mysql'); //TODO: add support for other databases types
if (DB_TYPE == 'mysql') {
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('DB_NAME', 'technoblade'); //why technoblade? because he was a god
}


//*Cookies params and shit
define('COOKIE_DOMAIN', HOST);
define('COOKIE_PATH', PODFOLDER);
define('COOKIE_SECURE', 'false');
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Lax');



define('EMAIL_DB_JSON', false); //TODO: implement json file for email validation
define('EMAIL_DB_JSON_DIR', PODFOLDER . '/dlc/jsondb'); //!My dude, remeber to block access to this folder
define('EMAIL_DELETE_AFTER_VALID', true); //TODO: impelent not deleting email after validation

//*Email connection data
define('EMAIL_HOST', 'web6.aftermarket.hosting');
define('EMAIL_AUTH', true);
define('EMAIL_USER', 'test@dbd.etaro.pl');
define('EMAIL_PASS', 'test123'); //totaly real data :D
define('EMAIL_PORT', 587);
define('EMAIL_FROM', 'noreply@dbd.etaro.pl');
define('EMAIL_FROM_NAME', 'DBD Killer Counter');
