<?php
//created a class to control the different class
class DBManage {
	private $dbhost = 'localhost';
	private $databasename = 'calendar';
	private $username = '503user';
	private $password = '1rZFR13R7MqsGuiY';

	protected $connection;
	protected $tablename;
	protected $fields; // [fieldname:string => fieldtype:string]
//a function to get to username
	function __construct() {
		$this->connection = new mysqli($this->dbhost, $this->username, $this->password, $this->databasename);

		if($this->connection->connect_errno) {
			printf("Connection Failed: %s\n", $this->connection->connect_error);
			exit;
		}
	}
//afuntion about clean up
	function cleanup() {
		$this->connection->close();
	}

	// Sourced from https://www.php.net/manual/en/mysqli-stmt.bind-param.php
	function refValues($arr) {
    $refs = array();
    foreach($arr as $key => $value) {
    	$refs[$key] = &$arr[$key];
    }

    return $refs;
   }

	function query($condkv = []) {
		$condition = '1=1';
		$fieldtypes = [];
		$values = [];
		$results = [];

		if (count($condkv)) {
			$fields = array_keys($condkv);

			foreach ($fields as $field) {
				$condition .= " AND $field = ?";
				$fieldtypes[] = $this->fields[$field] ?? 's';
				$values[] = $condkv[$field];
			}
			$condition .= ';';
		}

		$params[] = implode('', $fieldtypes);
		$params = array_merge($params, $values);
		$query = "SELECT * FROM $this->tablename WHERE $condition";

		$stmt = $this->connection->prepare($query);

		if(!$stmt){
			printf("Query Prep Failed: %s\n", $this->connection->error);
			return FALSE;
		}

		if (count($condkv)) {
			call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));
		}

		$stmt->execute();

		// Sourced from https://www.php.net/manual/en/mysqli-stmt.bind-result.php
		$resultparam = [];
		$meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
       $resultparam[] = &$row[$field->name];
    }

    call_user_func_array(array($stmt, 'bind_result'), $this->refValues($resultparam));

    while ($stmt->fetch()) {
    	$c = [];
      foreach($row as $key => $val)
      {
				$c[$key] = $val;
      }
      $results[] = $c;
    }

    $result = $results;

    $stmt->close();

    return $result;
	}
//a funtion when you need to add elements
	function add($addkv) {
		$fields = array_keys($addkv);
		$values = [];
		$fieldtypes = [];
		$placeholder = [];

		foreach ($fields as $field) {
			$values[] = $addkv[$field];
			$fieldtypes[] = $this->fields[$field] ?? 's';
			$placeholder[] = '?';
		}

		$fields = implode(', ', $fields);
		$fieldtypes = implode('', $fieldtypes);
		$placeholder = implode(', ', $placeholder);
		$params[] = $fieldtypes;
		$params = array_merge($params, $values);

		$query = "INSERT INTO $this->tablename ($fields) values ($placeholder)";
		$stmt = $this->connection->prepare($query);

		if(!$stmt){
			printf("Query Prep Failed: %s\n", $this->connection->error);
			return FALSE;
		}

		call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));

		$stmt->execute();
		$newid = $stmt->insert_id;

		$stmt->close();

		return $newid; // Truthy
	}
//a funtion when you need to update 
	function update($updatekv, $condkv) {
		$updatefields = array_keys($updatekv);
		$condfields = array_keys($condkv);
		$values = [];
		$fieldtypes = [];
		$seter = [];
		$cond = [];

		foreach ($updatefields as $updatefield) {
			$seter[] = "$updatefield = ?";
			$fieldtypes[] = $this->fields[$updatefield] ?? 's';
			$values[] = $updatekv[$updatefield];
		}

		foreach ($condfields as $condfield) {
			$cond[] = "$condfield = ?";
			$fieldtypes[] = $this->fields[$condfield] ?? 's';
			$values[] = $condkv[$condfield];
		}

		$seter = implode(', ', $seter);
		$cond = implode(' AND ', $cond);
		$fieldtypes = implode('', $fieldtypes);

		$stmt = $this->connection->prepare("UPDATE $this->tablename SET $seter WHERE $cond");

		if(!$stmt){
			printf("Query Prep Failed: %s\n", $this->connection->error);
			return FALSE;
		}

		$stmt->bind_param($fieldtypes, ...$values);
		$stmt->execute();
		$stmt->close();

		return TRUE;
	}
//a funtion when you need to delete
	function delete($id) {
		$stmt = $this->connection->prepare("DELETE FROM $this->tablename WHERE id = ?"); // Make sure each tb is pk by id:int

		if(!$stmt){
			printf("Query Prep Failed: %s\n", $this->connection->error);
			return FALSE;
		}

		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->close();

		return TRUE;
	}
}

//a funtion when you need to extend database
//about the definition in the user table
class User extends DBManage {
	protected $tablename = 'user';
	protected $fields = [
		'id' => 'i',
		'username' => 's',
		'password' => 's'
	];
}

//definition in the event table
// we made the time relevant field into string, because it is easier to calculate
class Event extends DBManage {
	protected $tablename = 'event';
	protected $fields = [
		'id' => 'i',
		'user_id' => 'i',
		'content' => 's',
		'title' => 's',
    'year' => 's',
    'month' => 's',
    'day' => 's',
    'start_hr' => 's',
    'start_min' => 's',
    'end_hr' => 's',
    'end_min' => 's',
    'tag' => 's',
    'coord' => 's'
	];
}

function respond_json ($payload) {
	echo json_encode($payload);
	exit;
}
