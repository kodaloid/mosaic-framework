<?php


/**
 * Describes a controller component.
 */
abstract class Controller {
	protected array $_constraints = [];


	/**
	 * Add a role constraint to one of the functions on this controller.
	 */
	protected function add_constraint(string $func, array $roles) {
		$existing = isset($this->_constraints[$func]) ? $this->_constraints[$func] : [];
		$this->_constraints[$func] = array_merge($existing, $roles);
	}


	/**
	 * Get all role restraints for one of the functions on this controller.
	 */
	function get_constraints(string $func) :array {
		return isset($this->_constraints[$func]) ? $this->_constraints[$func] : [];
	}


	/**
	 * Check to see if any role in an array is allowed for a specific function.
	 */
	function any_role_is_within_constraints(string $func, array $roles) :bool {
		$constraints = $this->get_constraints($func);
		if (sizeof($constraints) == 0) return true;
		
		$matches = array_intersect($roles, $constraints);
		return sizeof($matches) > 0;
	}


	/**
	 * Return a Twig template for rendering.
	 */
	protected function view(string $view_name, array $args = []) {
		global $twig;
		global $app;
		if (!str_contains($view_name, '.twig')) {
			$view_name .= '.twig';
		}
		$template = $twig->load($view_name);
		$args['app'] = $app;
		return $template->render($args);
	}
}