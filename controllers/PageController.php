<?php

class PageController extends Controller {

	function index(App $app) {
		return $this->view('index');
	}


	function about(App $app) {
		$data = array();
		return $this->view('about', $data);
	}


	function test() {
		global $db;

		$db->exec($db->prepare("INSERT INTO people (`name`, `age`, `date_created`) VALUES (?, ?, ?)", "sis", array(
			"dave",
			"202",
			$db->now()
		)));

		$people = $db->select('SELECT * FROM people');

		return $this->view('test', ['people' => $people]);
	}
}