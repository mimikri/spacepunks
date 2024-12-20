<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Class to perform SQL (MySQLi) queries:
class AJAXChatMySQLiQuery {

	/**
  * @var string
  */
 public $_sql = '';
	public $_result = 0;
	public $_errno = 0;
	public $_error = '';

	// Constructor:
	function __construct($sql, public $_connectionID) {
		$this->_sql = trim((string) $sql);
		$this->_result = $this->_connectionID->query($this->_sql);
		if(!$this->_result) {
			$this->_errno = $this->_connectionID->errno;
			$this->_error = $this->_connectionID->error;
		}
	}

	// Returns true if an error occured:
	function error(): bool {
		// Returns true if the Result-ID is valid:
		return !(bool)($this->_result);
	}

	// Returns an Error-String:
	function getError(): string {
		if($this->error()) {
			$str  = 'Query: '	 .$this->_sql  ."\n";
			$str .= 'Error-Report: '	.$this->_error."\n";
			$str .= 'Error-Code: '.$this->_errno;
		} else {
			$str = "No errors.";
		}
		return $str;
	}

	// Returns the content:
	function fetch() {
		if($this->error()) {
			return null;
		} else {
			return $this->_result->fetch_assoc();
		}
	}

	// Returns the number of rows (SELECT or SHOW):
	function numRows() {
		if($this->error()) {
			return null;
		} else {
			return $this->_result->num_rows;
		}
	}

	// Returns the number of affected rows (INSERT, UPDATE, REPLACE or DELETE):
	function affectedRows() {
		if($this->error()) {
			return null;
		} else {
			return $this->_connectionID->affected_rows;
		}
	}

	// Frees the memory:
	function free(): void {
		$this->_result->free();
	}
	
}
?>