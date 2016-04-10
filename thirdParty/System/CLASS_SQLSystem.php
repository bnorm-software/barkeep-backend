<?php

/*
 * Jer's yet undetermined license goes here
 */

class SQLInsertBuilder { //This provides a streamlined method of inserting large volumes of data, and tracking its insert ids.
	private $statement = '';
	private $table;
	private $vars;
	private $values;
	public $successfulInserts = 0;
	public $failedInserts = 0;

	private $insertIndex = array(); //TODO: verify that this must be array on instantiation and not false.

	private $insertAlignment = 0;

	public $status = 'Waiting for Vars';

	public function __construct($table, $vars = false) {
		$this->table = $table;
		if($vars) $this->addVars($vars);
	}

	public function addVars($vars) {
		if(is_array($vars)) foreach($vars as $var) $this->vars[] = $var;
		else $this->vars[] = $vars;
		$this->status = 'Waiting for Values';
	}

	public function addValues($values) {
		if(is_array($values)) foreach($values as $value) $this->values[] = $value;
		else $this->values[] = $values;
		$this->status = ($this->ready()) ? 'Ready' : 'Unmatched Vars to Values';
	}

	public function ready() {
		return (is_array($this->vars) && is_array($this->values) && $this->numValues() % $this->numVars() == 0);
	}

	public function numVars() {
		return (is_array($this->vars)) ? count($this->vars) : false;
	}

	public function numValues() {
		return (is_array($this->values)) ? count($this->values) : false;
	}

	public function getInsertAlignment($originList) //Creates an array that aligns original keys with new keys.  ###### This needs output clarification
	{
		if(strpos($this->status, 'Sent') !== false && is_array($this->insertIndex)) {
			if(is_array($originList) && count($originList) == $this->countInsertIndex()) {
				$result = array();
				$counter = 0;
				foreach($this->insertIndex as $insertIndex => $insertCount) {
					for($i = $insertIndex; $i < $insertIndex + $insertCount; $i++) {
						$result[$originList[$counter]] = $i;
						$counter++;
					}
				}
				return $result;
			} else return false;
		} else return false;
	}

	public function countInsertIndex() {
		if(is_array($this->insertIndex) && count($this->insertIndex) > 0) {
			$result = 0;
			foreach($this->insertIndex as $insertCount) {
				$result += $insertCount;
			}
			return $result;
		} else return 0;
	}

	public function send(&$database, $rowsPerInsert = 500, $echoOutput = false) {
		if($this->ready()) {
			if(gettype($database) == 'object' && get_class($database) == 'MySQLDatabase') {
				$numRows = count($this->values) / count($this->vars);

				$queryTop = "INSERT INTO ".$this->table." (";
				foreach($this->vars as $id => $var) $queryTop .= ($id == 0) ? $var : ", ".$var;
				$queryTop .= ") VALUES \r\n";

				unset($this->insertIndex);
				$this->insertIndex = array();

				$totalNumInserts = $this->numValues() / $this->numVars();
				$totalInsertCounter = 0;
				$valuesPerRow = $this->numVars();
				$totalValueCounter = 0;
				while($totalInsertCounter < $totalNumInserts) {
					$query = $queryTop;

					$rowCounter = 0;
					$currentInsertCounter = 0;
					while($currentInsertCounter < $rowsPerInsert && $totalInsertCounter < $totalNumInserts) {
						$query .= ($currentInsertCounter == 0) ? "(" : ", (";

						for($currentValueCounter = 0; $currentValueCounter < $valuesPerRow; $currentValueCounter++) {
							$query .= ($currentValueCounter == 0) ? "" : ", ";
							$query .= (gettype($this->values[$totalValueCounter]) == 'string' && $this->values[$totalValueCounter] != 'NULL') ? $database->Quote($this->values[$totalValueCounter]) : $this->values[$totalValueCounter];
							$totalValueCounter++;
						}

						$query .= ")\r\n";

						$currentInsertCounter++;
						$totalInsertCounter++;
					}
					$query .= ';';

					$insertID = $database->Query($query, true);

					if($echoOutput) echo $query."\r\n\r\n";

					$this->successfulInserts += $database->RowsAffected();
					$this->insertIndex[$insertID] = $database->RowsAffected(); //Log the insertID and rowsAffected for insertAlignment function
					$this->failedInserts += ($currentInsertCounter - $database->RowsAffected());
				}
				$this->status = 'Sent: '.$this->successfulInserts.' successful inserts.';
				return ($this->failedInserts) ? false : true;
			} else {
				$this->status = "Failed. Sent a '".gettype($database)."'";
				$this->status .= (gettype($database) == 'object') ? " of type '".get_class($database)."'" : '.';
				return false;
			}
		} else {
			$this->status = (count($this->values) > 0) ? "Failed. Insert builder is not ready." : "No data present for insert.";
			return false;
		}
	}
}