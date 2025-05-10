<?php


// load the bootstrap.
define('__APP__', __DIR__);
require_once __APP__ . '/system/bootstrap.php';


// load the engine.
$app = new App;


// run setup if site is new.
if ($app->is_new) return $app->run_setup();


// load twig.
$loader = new \Twig\Loader\FilesystemLoader(__APP__ . '/templates');
$twig = new \Twig\Environment($loader);
$twig->addGlobal('app', $app);
$twig->addExtension(new MosTwigExtensions());


// init the database & globals.
$db = new MosDatabase;
$twig->addGlobal('SITE_URL', SITE_URL);
$twig->addGlobal('SITE_NAME', SITE_NAME);
$twig->addGlobal('LOGIN_ENABLED', LOGIN_ENABLED);


// init any plugins.
$app->add_plugin('test', 'TestTool');


// add the routes used by the index endpoint.
$app->route('/', 'PageController');
$app->route('/about', 'PageController', 'about');


// tell the app to run.
$app->run();