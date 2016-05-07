<?php

/*
 * Jer's yet undetermined license goes here
 */

/*
	//Example connection array:
	$dbCreds = array(
		'host'=>'localhost',
		'name'=>'dbName',
		'user'=>'dbUser',
		'pass'=>'complexPassword'
	);

	//Example instantiation:
	$db = new MySQLDatabase('dbCreds'); // <--pass the name of the array variable which will be globalized into the object

	//This method of credential storage prevents credentials from needing to be serialized into the session,
	//or the need for explicitly re-instantiating the connection resource with each session start.
 */

const MYSQLDATABASERELATIVEPATHTOROOT = '/../../'; //Boooooo.  Because __sleep() is not called from apache context, apparently.
const MYSQLPDODEFAULTFETCH = PDO::FETCH_ASSOC;
const MYSQLVARPATH = '/tmp/MySQLVars/'; //Where to store any persistent declared values

const MYSQLVAREXPIREDAYS = 1; //0 for off.  Use 0 only for debugging.

const MYSQLLOGPRETTIFY = true;

class MySQLDatabase {

	/** @var bool|string */
	private $credsVar = false;
	/** @var bool|string */
	private $uniqueID;

	/** @var bool|PDO */
	public $Conn;
	/** @var bool|string|PDOStatement */
	public $LastQuery = false;
	/** @var bool|string */
	public $LastQueryString = false;

	/** @var bool */
	private $connected = false;
	/** @var int */
	public $HitCount = 0; //For counting the db hits during the current web request.
	/** @var int */
	public $QueryTime = 0; //Stores the total amount of time communicating with the db during the current web request.
	/** @var string */
	public $QueryLog = '';
	/** @var int */
	public $QueryCount = 0;

	/** @var string[] */
	private $warmVariables = array();
	/** @var string[] */
	private $coldVariables = array();

	/** @var bool */
	private $logQueries = false;

	public function __construct($credsVar) {
		$this->credsVar = $credsVar;
		$this->logQueries = defined('LOGQUERIES') && LOGQUERIES;
		$this->uniqueID = 'sqldb-' . session_id();
		if (!file_exists(MYSQLVARPATH)) mkdir(MYSQLVARPATH);

		MySQLDatabase::GC();
	}

	public function Connect() {
		//Minimal confusion.
		$credsVar = $this->credsVar;
		global $$credsVar;
		$creds = $$credsVar;
		//Confused yet?
		//This prevents serializing of database credentials while allowing for multiple databases
		//Only in PHP, eh?

		$dsn = "mysql:dbname=" . $creds['name'] . ";host=" . $creds['host'];
		try {
			$this->Conn = new PDO($dsn, $creds['user'], $creds['pass'],
				array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => MYSQLPDODEFAULTFETCH,
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
				)
			);
		} catch (PDOException $e) { //Because credentials, yo.
			$pomeranians = new Exception('Cannot connect to database.');
			throw $pomeranians;
		}
		if ($this->Conn) $this->connected = true;
		else {
			$dachshunds = new Exception('Cannot connect to database.');
			throw $dachshunds;
		}
	}

	/** @return bool|PDOStatement */
	public function Query($string, $returnID = false, $prettify = MYSQLLOGPRETTIFY) {
		if (!$this->connected) $this->Connect();
		$this->LastQueryString = $string;
		$start = microtime(true);
		$this->LastQuery = $this->Conn->query($string);
		$finish = microtime(true) - $start;
		$this->QueryTime += $finish;
		$this->HitCount++;
		if ($this->logQueries) {
			$this->QueryLog .= "\r\nQuery #" . $this->HitCount . "  Query duration: " . number_format($finish, 4);

			if ($this->LastQuery) $this->QueryLog .= "\r\n\tRows: " . $this->LastQuery->rowCount();
			else $this->QueryLog .= "\r\n\tError: " . implode(' - ', $this->Conn->errorInfo());

			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$function = (!empty($trace)) ? $trace[0] : array();
			foreach ($trace as $function) {
				if (array_key_exists('class', $function) && $function['class'] == 'MySQLDatabase') continue;
				else break;
			}
			$traceString = ($function && array_key_exists('class', $function)) ? $function['class'] . "::" : '';
			$traceString .= ($function && array_key_exists('function', $function)) ? $function['function'] . '()' : '';
			if (strlen($traceString)) $traceString .= "\r\n\t";
			$traceString .= ($function && array_key_exists('file', $function)) ? $function['file'] . ':' . $function['line'] : '';
			$this->QueryLog .= "\r\n\t$traceString";

			if ($prettify) {
				$formatter = new SqlFormatter();
				$string = $formatter->format($string, false);
			}

			$this->QueryLog .= "\r\n\t------\r\n\t" . str_replace("\n", "\n\t", trim(str_replace("\t", ' ', $string), "\r\n")) . "\r\n\t------";
			$this->QueryLog .= "\r\n";
		}
		return ($returnID) ? $this->Conn->lastInsertID() : $this->LastQuery;
	}

	/** @return bool|array */
	public function QueryToArray($query, $columnKey = false, $fetchStyle = MYSQLPDODEFAULTFETCH) {
		$query = $this->Query($query);
		if ($query) {
			if ($columnKey !== false) {
				$found = false;
				$result = array();
				while ($row = $query->fetch($fetchStyle)) {
					if ($found || array_key_exists($columnKey, $row)) {
						$result[$row[$columnKey]] = $row;
						$found = true;
					} else $result[] = $row;
				}
				return $result;
			} else return $query->fetchAll();
		} else return false;
	}

	/** @return array|bool */
	public function GetRow($string, $fetchStyle = MYSQLPDODEFAULTFETCH) {
		//LIMIT it if it isn't already
		if (!stristr($string, ' LIMIT ')) $string = trim($string, "\r\n\t ;") . " LIMIT 1;";
		$query = $this->Query($string);
		if ($query) return $query->fetch($fetchStyle);
		else return false;
	}

	/** @return mixed */
	public function GetValue($string, $columnKey = false) {
		$row = $this->GetRow($string);
		if ($row) {
			if ($columnKey !== false) {
				if (array_key_exists($columnKey, $row)) return $row[$columnKey];
				else throw new Exception("Requested column $columnKey was not found");
			} else return array_shift($row);
		} else return false;
	}

	/** @return string */
	public function Quote($string) {
		if (!$this->connected) $this->Connect();
		return $this->Conn->quote($string);
	}

	/** @return int */
	public function RowsAffected() {
		if (!$this->connected) {
			$this->Connect();
			return 0;
		} else if ($this->LastQuery) {
			return $this->LastQuery->rowCount();
		} else return 0;
	}

	/** @return string */
	public function BooleanNullable($value, $isCondition = false) {
		$true = ($isCondition) ? " = " . $this->Quote($value) : $this->Quote($value);
		$false = ($isCondition) ? ' IS NULL' : 'NULL';
		return (trim($value)) ? $true : $false;
	}

	/** @return bool */
	public function SetVariable($name, $value, $warm = false) {
		//TODO: Validate for acceptable MySLQ variable characters
		if (!file_exists(MYSQLVARPATH . $this->uniqueID)) mkdir(MYSQLVARPATH . $this->uniqueID);
		file_put_contents(MYSQLVARPATH . $this->uniqueID . "/$name", $value);
		$result = $this->Query("SET @" . $name . " := " . $this->Quote($value) . ";");
		if ($warm) $this->warmVariables[$name] = true;
		else $this->coldVariables[$name] = true;
		return (bool)$result;
	}

	/** @return string|bool */
	public function GetVariable($name) {
		return (file_exists(MYSQLVARPATH . $this->uniqueID . "/$name")) ? file_get_contents(MYSQLVARPATH . $this->uniqueID . "/$name") : false;
	}

	/** @return bool */
	public function VariableValid($name) {
		return file_exists(MYSQLVARPATH . $this->uniqueID . "/$name");
	}

	/** @return bool */
	public function WarmVariable($name) {
		if (array_key_exists($name, $this->coldVariables)) {
			if ($this->coldVariables[$name]) return true;
			else if (file_exists(MYSQLVARPATH . $this->uniqueID . "/$name")) {
				$start = microtime(true);
				$this->Query("SET @" . $name . " := " . $this->Quote(file_get_contents(MYSQLVARPATH . $this->uniqueID . "/$name")) . ";");
				$this->coldVariables[$name] = true;
				$this->QueryLog .= "\tWarmed $name in " . number_format(microtime(true) - $start, 4) . " seconds\r\n";
				return true;
			} else return false;
		} else if (array_key_exists($name, $this->warmVariables)) return $this->warmVariables[$name];
		else return false;
	}

	/** @return array */
	public function __sleep() {
		foreach ($this->coldVariables as $name => $val) $this->coldVariables[$name] = false;
		foreach ($this->warmVariables as $name => $val) $this->warmVariables[$name] = false;
		if ($this->logQueries && $this->HitCount) {
			singleLog(
				$this->QueryLog .
				"\r\nTotal Query Time: " . number_format($this->QueryTime, 4) .
				"\r\n############\r\n"
				, dirname(__FILE__) . MYSQLDATABASERELATIVEPATHTOROOT . QUERYLOG
			);
		}
		return array_diff(array_keys(get_object_vars($this)),
			array( //Members excluded from session serialization
			       'Conn'
			       , 'LastQuery'
			       , 'HitCount'
			       , 'QueryTime'
			       , 'QueryLog'
			       , 'logQueries'
			       , 'connected'
			));
	}

	public function __wakeup() {
		$this->logQueries = defined('LOGQUERIES') && LOGQUERIES;
		$this->QueryCount = 0;
		if ($this->logQueries) {
			$this->QueryLog = "\r\n############\r\n" . date("Y-m-d H:i:s") . " - PID #" . getmypid() . "\r\n" . $_SERVER['REQUEST_URI'] . "\r\n";
		}

		//Warm the appropriate variables on wakeup.
		$varQuery = '';
		$warmCount = 0;
		foreach ($this->warmVariables as $var => $value) {
			if (file_exists(MYSQLVARPATH . $this->uniqueID . "/$var")) {
				$this->warmVariables[$var] = true;
				$varQuery .= "SET @" . $var . " := " . $this->Quote(file_get_contents(MYSQLVARPATH . $this->uniqueID . "/$var")) . ";";
				$warmCount++;
			} else $this->warmVariables[$var] = false;
		}
		if ($varQuery) {
			$start = microtime(true);
			if (!$this->Query($varQuery)) {
				foreach ($this->warmVariables as $var => $val) $this->warmVariables[$var] = false;
				$this->QueryLog .= "\tFailed to warm variables.\r\n";
			} else $this->QueryLog .= "\tWarmed $warmCount variables in " . number_format(microtime(true) - $start, 4) . " seconds\r\n";
		}
	}

	public function Connected() {
		return (bool)$this->Conn;
	}

	//Remove filters for old-ass sessions
	public static function GC() {
		if (MYSQLVAREXPIREDAYS && file_exists(MYSQLVARPATH)) {
			$expireTime = time() - (MYSQLVAREXPIREDAYS * 86400);
			foreach (scandir(MYSQLVARPATH) as $sessionFolder) {
				if (stristr($sessionFolder, 'sqldb-') && filemtime(MYSQLVARPATH . $sessionFolder) < $expireTime) {
					$delete = "rm -r " . MYSQLVARPATH . $sessionFolder;
					$delete = `$delete`;
					singleLog("MySQLDatabase Garbage Collection for $sessionFolder");
				}
			}
		}
	}
}