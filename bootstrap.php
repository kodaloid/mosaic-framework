<?php


// get the config file.
include 'config.php';


// load composer.
require_once __APP__ . "/vendor/autoload.php";


// add autoload handler.
spl_autoload_register(function ($class) {
	$file = __APP__ . "/controllers/{$class}.php";
	if (file_exists($file)) {
		require_once $file;
		return;
	}

	$file = __APP__ . "/plugins/{$class}.php";
	if (file_exists($file)) {
		require_once $file;
		return;
	}
});


// load system includes.
require_once __APP__ . '/system/App.php';
require_once __APP__ . '/system/Controller.php';
require_once __APP__ . '/system/Database.php';
require_once __APP__ . '/system/Polyfill.php';
require_once __APP__ . '/system/RequestHandler.php';
require_once __APP__ . '/system/Session.php';


// init sessions (ran after spl_autoload as session_start can use autoload).
session_start();