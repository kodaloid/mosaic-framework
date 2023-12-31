<?php

class Database {
	private $conn;

	
	function __construct() {
		/* You should enable error reporting for mysqli before attempting to make a connection */
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	
		/* Set the desired charset after establishing a connection */
		$this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS,	DB_NAME);
		$this->conn->set_charset(DB_CHARSET);
	}


	function prepare(string $query, string $types, array $data) {
		$stmt = $this->conn->prepare($query);
		$params = [];

		$params[] = &$types;
		for ($i=0; $i<count($data); $i++) $params[] = &$data[$i];

		//$stmt->bind_param($types, ...$params);
		call_user_func_array(array($stmt, 'bind_param'), $params);

		return $stmt;
	}


	/**
	 * Get the current date/time in MySQL accepted format.
	 */
	function now() {
		return date(DB_DATE_FORMAT);
	}


	/**
	 * @param string|\mysqli_stmt $query;
	 */
	function select($query, $type = OBJECT) {
		$stmt = is_string($query) ? $this->conn->prepare($query) : $query;
		
		# execute stmt or reports errors
		$stmt->execute() or trigger_error($stmt->error, E_USER_ERROR);

		# save data or reports errors
		($stmt_result = $stmt->get_result()) or trigger_error($stmt->error, E_USER_ERROR);

		$result = [];
		while($row = $stmt_result->fetch_assoc()) {
			$result[] = ($type == OBJECT) ? (object)$row : $row;
		}

		return $result;
	}


	/**
	 * @param string|\mysqli_stmt $query;
	 */
	function select_row($query, $type = OBJECT) {
		$rows = $this->select($query, $type);
		return $rows[0] ?? null;
	}


	/**
	 * Execute a query (insert, update etc...)
	 */
	function exec($query) {
		$stmt = is_string($query) ? $this->conn->prepare($query) : $query;
		
		# execute stmt or reports errors
		$stmt->execute() or trigger_error($stmt->error, E_USER_ERROR);
	}


	function insert_id() {
		return $this->conn->insert_id;
	}
}