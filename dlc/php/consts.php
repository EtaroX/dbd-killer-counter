<?php
define("HOST", $_SERVER['HTTP_HOST']);
define("PODFOLDER", "/technoblade/dbd-killer-counter/");
define("SECUREONLY",false);


//*Database connection data
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'technoblade'); //why technoblade? because he was a god

define('EMAIL_DB_JSON', false); //TODO: implement json file for email validation
define('EMAIL_DB_JSON_FILE', 'email.json'); 

//*Email connection data
define('EMAIL_HOST', 'web6.aftermarket.hosting');
define('EMAIL_AUTH', true);
define('EMAIL_USER', 'test@dbd.etaro.pl');
define('EMAIL_PASS', 'test123'); //totaly real data :D
define('EMAIL_PORT', 587);
define('EMAIL_FROM', 'noreply@dbd.etaro.pl');
define('EMAIL_FROM_NAME', 'DBD Killer Counter');
