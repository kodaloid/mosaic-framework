<?php

/**
 * The base app for Mosaic CMS
 */
class App {
	private static App $instance; // singleton instance.
	private array $routes; // an array of mapped routes.
	private array $plugins; // an array of loaded plugins.

	/**
	 * Information about the request.
	 */
	public readonly MosRequest $request;

	/**
	 * Information about the current session (login system).
	 */
	public readonly MosSession $session;

	/**
	 * Indicates whether a config.php file exists.
	 */
	public readonly bool $is_new;

	/**
	 * Various built-in tools.
	 */
	public readonly MosTools $tools;

	
	/**
	 * Constructor called by get_instance();
	 */
	private function __construct() {
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
	 * Get the app instance.
	 */
	static function get_instance() : App {
		if (!isset(self::$instance)) {
			self::$instance = new self;
		}
      return self::$instance;
	}


	/**
	 * Initialize this app (Setup Twig & Database).
	 */
	function initialize() :bool {
		// handle missing setup.
		if ($this->is_new) {
			$this->run_setup();
			return false;
		}

		// load twig.
		global $twig;
		$loader = new \Twig\Loader\FilesystemLoader(__APP__ . '/templates');
		$twig = new \Twig\Environment($loader);
		$twig->addExtension(new MosTwigExtensions());

		// init the database & globals.
		global $db;
		$db = new MosDatabase;
		$twig->addGlobal('app', $this);
		$twig->addGlobal('SITE_URL', SITE_URL);
		$twig->addGlobal('SITE_NAME', SITE_NAME);
		$twig->addGlobal('OTP_ENABLED', OTP_ENABLED);
		$twig->addGlobal('LOGIN_ENABLED', LOGIN_ENABLED);
		return true;
	}


	/**
	 * Route a specific URL scheme to a controller.
	 * @param $url The scheme, for example /products/test
	 * @param $controller The name of a controller class to instantiate.
	 * @param $method The name of the static controller method to call (leave empty for index).
	 */
	//function route(string $url, string $controller, string $method = 'index', array $http_methods = ['GET']) {
	function route(string $url, string $class, string $func = 'index', array $http_methods = ['GET']) {
		if (strlen($url) > 1) $url = rtrim($url, '/');
		$this->routes[$url] = (object)array(
			'controller' => $class,
			'method' => $func,
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
		// if the url is for an asset file, serve it.
		if (str_starts_with($this->request->relative_url, '/assets') && file_exists(__APP__ . $this->request->relative_url)) {
			$mime = mime_content_type(__APP__ . $this->request->relative_url);
			header('Content-Type: ' . $mime);
			readfile(__APP__ . $this->request->relative_url);
			return;
		}
				
		// get the route.
		$url = $this->is_new ? '/' : $this->request->relative_url;
		$route = isset($this->routes[$url]) ? $this->routes[$url] : null;

		// nullify if route does not support the request method.
		if (!is_null($route) && !in_array($_SERVER['REQUEST_METHOD'], $route->http_methods)) {
			$route = null;
		}

		// if not nullified, execute route.
		if (!is_null($route)) {
			$this->request->route = $route->scheme;
			$method = $route->method;
			$class_instance = new $route->controller();

			$roles = $this->session->current_user_roles();
			if ($class_instance->any_role_is_within_constraints($method, $roles)) {
				echo $class_instance->$method($this);
			}
			else {
				// nothing found so throw a 404.
				http_response_code(403);
				header('Content-Type: text/plain');
				echo "403 - Access Denied.";
			}
			exit;
		}
		
		// nothing found so throw a 404.
		http_response_code(404);
		header('Content-Type: text/plain');
		echo "404 - Error page $url not found.";
	}


	/**
	 * Called when the setup needs to be displayed, see index.php
	 */
	function run_setup() {
		// load twig.
		$loader = new \Twig\Loader\FilesystemLoader(__APP__ . '/system/templates');
		global $twig;
		$twig = new \Twig\Environment($loader);
		$twig->addGlobal('app', $this);
		$twig->addExtension(new MosTwigExtensions());

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