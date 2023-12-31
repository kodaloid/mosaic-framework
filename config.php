<?php


// Localization.
date_default_timezone_set('Europe/London');


// PHP Settings (comment these out to disable verbose errors).
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Global Variables.
define('SITE_URL', 'http://localhost/mosaic-cms/');
define('SITE_NAME', 'Mosaic CMS');


// Database.
define('DB_HOST', 'localhost');
define('DB_NAME', 'mosaic_cms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_DATE_FORMAT', 'Y-m-d H:i:s');
define('ARRAY_A', 0);
define('OBJECT', 1);


// Email credentials.
define('EMAIL_HOST', 'localhost');
define('EMAIL_USER', 'someone@somewhere.com');
define('EMAIL_NAME', 'Mosaic CMS');
define('EMAIL_PASS', '');
define('EMAIL_PORT', 587);


// Login System
define('LOGIN_ENABLED', true);
define('PASSWORD_SALT', 'c1isvFdxMDdmjOlvxpecFw');


// Timing.
define('ONE_DAY', 86400);