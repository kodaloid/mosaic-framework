<?php


// load the bootstrap.
define('__APP__', __DIR__);
require_once __APP__ . '/bootstrap.php';


// load the engine.
$app = new App;


// load twig.
$loader = new \Twig\Loader\FilesystemLoader(__APP__ . '/templates');
$twig = new \Twig\Environment($loader);
$twig->addGlobal('app', $app);
$twig->addGlobal('SITE_URL', SITE_URL);
$twig->addGlobal('SITE_NAME', SITE_NAME);


// init the database.
$db = new Database;


// init any plugins.
$app->add_plugin('test', 'TestTool');


// add the routes used by the index endpoint.
$app->route('/', 'PageController', 'index');
$app->route('/about', 'PageController', 'about');


// tell the app to run.
$app->run();