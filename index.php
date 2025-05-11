<?php

// Load the bootstrap.
define('__APP__', __DIR__);
require_once __APP__ . '/system/bootstrap.php';

// Load the engine.
$app = App::get_instance();

// Initialize this app (Setup Twig & Database).
if ($app->initialize()) {

	// Init any plugins.
	$app->add_plugin('test', 'TestTool');

	// Add the routes used by the index endpoint.
	$app->route('/', 'PageController');
	$app->route('/about', 'PageController', 'about');

	// Run the app!
	$app->run();
}