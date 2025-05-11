<?php

class PageController extends Controller {


	function __construct() {
		$this->add_constraint('about', ['admin']);
	}


	function index(App $app) {
		return $this->view('index');
	}


	function about(App $app) {
		$data = array();
		return $this->view('about', $data);
	}
}