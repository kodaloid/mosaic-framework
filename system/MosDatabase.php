<?php


/**
 * The database engine.
 */
class MosDatabase {
	private ?PDO $conn;
	public readonly bool $disabled;

	
	/**
	 * Database constructor.
	 */
	function __construct() {
		switch (DB_TYPE) {
			case 'mysql':
				$this->conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET, DB_USER, DB_PASS); 
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
				$this->disabled = false;
				break;
			case 'sqlite':
				$file = str_starts_with(DB_HOST, '/') ? __APP__ . DB_HOST : DB_HOST;
				$this->conn = new PDO('sqlite:' . $file);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
				$this->disabled = false;
				break;
			// case 'firebird':
			// case 'odbc':
			default:
				$this->disabled = true;
				break;
		}
	}


	/**
	 * Create a prepared statement.
	 */
	function prepare(string $query, ?string $types, array $data) : ?PDOStatement {
		if ($this->disabled) return null;
		$stmt = $this->conn->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
		if ($stmt == false) {
			trigger_error(implode("\n", $this->conn->errorInfo()), E_USER_ERROR);
		}
		if ($stmt) {
			$i=0;
			foreach ($data as $key => &$value) {
				$type = PDO::PARAM_STR;
				if (!is_null($types)) {
					switch ($types[$i++]) {
						case 'i': $type = PDO::PARAM_INT; break;
						case 'b': $type = PDO::PARAM_BOOL; break;
					}
				}
				$key = is_numeric($key) ? $key + 1 : $key;
				$stmt->bindParam($key, $value, $type);
			}
			return $stmt;
		}
		return null;
	}


	/**
	 * Get the current date/time in MySQL accepted format.
	 */
	function now() {
		return date(DB_DATE_FORMAT);
	}


	/**
	 * Check to see if the connection has been established (does not guarantee
	 * that it is still available).
	 */
	function is_established() {
		return $this->conn;
	}


	/**
	 * @param string|PDOStatement $query;
	 */
	function select($query, $type = OBJECT) : ?array {
		if ($this->disabled) return null;

		$stmt = is_string($query) ? $this->conn->prepare($query) : $query;
		
		# execute stmt or reports errors
		$stmt->execute() or trigger_error(implode("\n", $stmt->errorInfo()), E_USER_ERROR);

		# save data or reports errors
		($stmt_result = $stmt->fetchAll()) or trigger_error(implode("\n", $stmt->errorInfo()), E_USER_ERROR);

		$result = [];
		foreach ($stmt_result as $row) {
			$result[] = ($type == OBJECT) ? (object)$row : $row;
		}

		return $result;
	}


	/**
	 * @param string|\mysqli_stmt $query;
	 */
	function select_row($query, $type = OBJECT) : ?object {
		if ($this->disabled) return null;

		$rows = $this->select($query, $type);
		return $rows[0] ?? null;
	}


	/**
	 * Execute a query (insert, update etc...)
	 */
	function exec($query) {
		$stmt = is_string($query) ? $this->conn->prepare($query) : $query;
		
		# execute stmt or reports errors
		$stmt->execute() or trigger_error(implode("\n", $stmt->errorInfo()), E_USER_ERROR);
	}


	/**
	 * Get the last inserted row id.
	 */
	function insert_id() : ?int {
		return $this->disabled ? null : $this->conn->lastInsertId();
	}
}