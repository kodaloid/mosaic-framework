<?php


abstract class Controller {
	protected function view($view_name, $args = []) {
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