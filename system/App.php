<?php

/**
 * The base app for Mosaic CMS
 */
class App {
	// an array of mapped routes.
	private $routes;
	private $plugins;
	public $request;
	public $session;

	
	function __construct() {
		$this->routes = [];
		$this->plugins = [];
		$this->request = new RequestHandler();
		$this->session = new Session();

		if (LOGIN_ENABLED) {
			$this->route("/login/", 'LoginController', 'login');
			$this->route("/login/", 'LoginController', 'login', ['GET', 'POST']);
			$this->route("/logout", 'LoginController', 'logout');
			$this->route("/register", "LoginController", 'register');
		}
	}


	/**
	 * Route a specific URL scheme to a controller.
	 * @param $url The scheme, for example /products/test
	 * @param $controller The name of a controller class to instanciate.
	 * @param $method The name of the static controller method to call (leave empty for index).
	 */
	function route($url, $controller, $method = 'index', $http_methods = ['GET']) {
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
	function add_plugin($name, $className) {
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
		$url = $this->request->relative_url;

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
		var_dump($this->routes);
	}


	function plugin($name) {
		return isset($this->plugins[$name]) ? $this->plugins[$name] : null;
	}


	function redirect($url) {
		header("Location: $url");
	}


	function url($url) {
		return SITE_URL . ltrim($url, '/');
	}
}