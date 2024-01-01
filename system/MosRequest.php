<?php

/**
 * Handles information to do with the request made.
 */
class MosRequest {
	private $_info;


	function __construct() {
		// deduce some information about the request.
		$method = $_SERVER['REQUEST_METHOD'];
		if (defined('SITE_URL')) {
			$site_url = parse_url(SITE_URL);
			$base_url = rtrim($site_url['path'], '/');
		}
		else {
			$base_url = '';
		}
		$r_url 	 = $_SERVER['REQUEST_URI'];

		if (str_starts_with($r_url, $base_url)) {
			$r_url = substr($r_url, strlen($base_url));
		}

		if (strlen($r_url) > 1) {
			$r_url = rtrim($r_url, '/');
		}

		$this->_info = array(
			'method'  		=> $method,
			'route'	 		=> '',
			'is_get'  		=> $method == 'GET',
			'is_post' 		=> $method == 'POST',
			'relative_url' => $r_url
		);
	}


	function __get($name) {
		if (isset($this->_info[$name])) {
			return $this->_info[$name];
		}
		return null;
	}


	function __set($name, $value) {
		switch ($name) {
			case 'route': 
				$this->_info['route'] = $value;
				break;
		}
	}


	/**
	 * Get specific request info.
	 */
	function info($name) {
		return $this->$name;
	}


	/**
	 * Get a specific query argument.
	 */
	function query($name, $default = null) {
		return isset($_GET[$name]) ? $_GET[$name] : $default;
	}


	/**
	 * Get a specific post argument.
	 */
	function post($name, $default = null) {
		return isset($_POST[$name]) ? $_POST[$name] : $default;
	}
}