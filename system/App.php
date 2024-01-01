<?php

/**
 * The base app for Mosaic CMS
 */
class App {
	// an array of mapped routes.
	private array $routes;
	private array $plugins;
	public MosRequest $request;
	public MosSession $session;
	public bool $is_new;
	public MosTools $tools;

	
	function __construct() {
		$this->routes = [];
		$this->plugins = [];
		$this->request = new MosRequest();
		$this->session = new MosSession();
		$this->is_new = !defined('SITE_URL');
		$this->tools = new MosTools();

		if (defined('LOGIN_ENABLED') && LOGIN_ENABLED) {
			$this->route("/login/", 'LoginController', 'login');
			$this->route("/login/", 'LoginController', 'login', ['GET', 'POST']);
			$this->route("/logout", 'LoginController', 'logout');
		}
	}


	/**
	 * Route a specific URL scheme to a controller.
	 * @param $url The scheme, for example /products/test
	 * @param $controller The name of a controller class to instantiate.
	 * @param $method The name of the static controller method to call (leave empty for index).
	 */
	function route(string $url, string $controller, string $method = 'index', array $http_methods = ['GET']) {
		if (strlen($url) > 1) $url = rtrim($url, '/');
		$this->routes[$url] = (object)array(
			'controller' => $controller,
			'method' => $method,
			'http_methods' => $http_methods,
			'scheme' => $url
		);
	}


	/**
	 * Add & initialize a plugin into the global plugin namespace.
	 */
	function add_plugin(string $name, string $className) {
		if (!class_exists($className)) {
			trigger_error("Plugin $className does not exist.", E_USER_ERROR);
			exit;
		}
		$this->plugins[$name] = new $className;
		return true;
	}

	
	/**
	 * Run the app.
	 */
	function run() {
		$url = $this->is_new ? '/' : $this->request->relative_url;

		// get the route.
		$route = isset($this->routes[$url]) ? $this->routes[$url] : null;

		// nullify if route does not support the request method.
		if (!is_null($route) && !in_array($_SERVER['REQUEST_METHOD'], $route->http_methods)) {
			$route = null;
		}

		// if not nullified, execute route.
		if (!is_null($route)) {
			$this->request->route = $route->scheme;
			$method = $route->method;
			echo (new $route->controller())->$method($this);
			exit;
		}
		
		http_response_code(404);
		header('Content-Type: text/plain');
		echo "404 - Error page $url not found.";
	}


	/**
	 * Called when the setup needs to be displayed, see index.php
	 */
	function run_setup() {
		global $twig;
		// add a setup controller.
		define('SITE_NAME', 'Mosaic CMS');
		$twig->addGlobal('SITE_NAME', SITE_NAME);
		$this->route('/', 'SetupController', 'index', ['GET', 'POST']);
		$this->run();
	}


	/**
	 * Retrieve a named plugin instance.
	 */
	function plugin(string $name) {
		return isset($this->plugins[$name]) ? $this->plugins[$name] : null;
	}


	/**
	 * Redirect the browser to another URL.
	 */
	function redirect(string $url) {
		header("Location: $url");
	}


	/**
	 * Get a fully qualified URL from a relative one.
	 */
	function url(string $relative_url) {
		return SITE_URL . ltrim($relative_url, '/');
	}
}