<?php

// Localization.
date_default_timezone_set('Europe/London');

// PHP Settings (comment these out to disable verbose errors).
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Global Variables.
define('SITE_URL', 'http://localhost:8080/');
define('SITE_NAME', 'Test Site');

// Database.
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'mosaic_cms');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_DATE_FORMAT', 'Y-m-d H:i:s');
define('ARRAY_A', 0);
define('OBJECT', 1);

// Login System
define('LOGIN_ENABLED', true);
define('PASSWORD_SALT', '[salt-goes-here]');

// Timing.
define('ONE_DAY', 86400);