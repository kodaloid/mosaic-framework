<?php

class FormErrors {
	private $errors;

	function __construct()
	{
		$this->errors = array();
	}

	function __get(string $name) {
		if ($name == 'count') return count($this->errors);
		return null;
	}

	function add(string $what, string $message) {
		$this->errors[] = (object)array(
			'what' => $what,
			'message' => $message
		);
	}

	function toArray() :array {
		return $this->errors;
	}
}