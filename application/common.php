<?php

const REQUEST_TYPE_AJAX = 'Do';
const REQUEST_TYPE_MODAL = 'Get';
const REQUEST_TYPE_PAGE = false;

function microtime_float() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function maxLength($string, $length) //Returns a string truncated to a given length
{
	return (strlen($string) > $length - 3) ? substr($string, 0, $length - 3).'...' : $string;
}

function mathMin($input, $min) //Returns the greater of two numbers
{
	return ($input < $min) ? $input : $min;
}

function mathMax($input, $max) //Returns the lesser of two numbers
{
	return ($input > $max) ? $max : $input;
}

function mathClamp($input, $min, $max) {
	if($min < $max) return mathMax(mathMin($input, $min), $max);
	else return false;
}

function timeIn() //Marks the moment in microseconds, for processing by 'timeOut'
{
	return microtime_float();
}

function timeOut($time_start, $round = 2) //Returns the time passed in microseconds since 'time_start', which should have been captured using 'timeIn'
{
	$time_end = microtime_float();
	return round($time_end - $time_start, $round);
}

function stringClamp($string, $length) {
	return (strlen($string) > $length) ? substr($string, 0, $length - 2).'...' : $string;
}

function getFileSize($sizeInBytes, $round = 2) //Converts a given number of bytes into an easily identifiable string
{
	$counter = 0;
	while($sizeInBytes >= 1024) {
		$sizeInBytes /= 1024;
		$counter++;
	}

	$result = round($sizeInBytes, $round);

	switch($counter) {
		case 0:
			$result .= 'B';
			break;
		case 1:
			$result .= 'KB';
			break;
		case 2:
			$result .= 'MB';
			break;
		case 3:
			$result .= 'GB';
			break;
		case 4:
			$result .= 'TB (seriously?)';
			break;
		default:
			break;
	}

	return $result;
}

function getParam($paramName, $array = false) { //EVEN MOAR NEW AND IMPROOVD!
	if($array === false) {
		if(isset($_POST[$paramName])) return $_POST[$paramName];
		else if(isset($_GET[$paramName])) return $_GET[$paramName];
	} else if(is_array($array)) return (array_key_exists($paramName, $array)) ? $array[$paramName] : false;
	else return false;
}

function getIP($ip = false) {
	if(!$ip) $ip = explode(':', $_SERVER['REMOTE_ADDR']);
	else $ip = explode(':', $ip);
	return $ip[0];
}

if(!function_exists('xmlentities')) {
	function xmlentities($string) {
		$not_in_list = "A-Z0-9a-z\s_-";
		return preg_replace_callback("/[^{$not_in_list}]/", 'get_xml_entity_at_index_0', $string);
	}

	function get_xml_entity_at_index_0($CHAR) {
		if(!is_string($CHAR[0]) || (strlen($CHAR[0]) > 1)) {
			die("function: 'get_xml_entity_at_index_0' requires data type: 'char' (single character). '{$CHAR[0]}' does not match this type.");
		}

		switch($CHAR[0]) {
			case "'":
			case '"':
			case '&':
			case '<':
			case '>':
				return htmlspecialchars($CHAR[0], ENT_QUOTES);
				break;

			default:
				return numeric_entity_4_char($CHAR[0]);
				break;
		}
	}

	function numeric_entity_4_char($char) {
		return "&#".str_pad(ord($char), 3, '0', STR_PAD_LEFT).";";
	}
}

function buildJSONResponse($array) {
	foreach($array as $key=>$item) {
		if(gettype($item) == 'string') $array[$key] = utf8_encode($item);
	}
	header('Content-type: application/json; charset=utf-8');
	echo json_encode($array);
}

function invertArray($array) {
	if(is_array($array)) {
		$result = array();
		foreach($array as $id => $item) $result[$item] = $id;
		return $result;
	} else return false;
}

function percent($total, $dividend, $round = 2, $padRound = true) {
	if($total != 0) {
		$result = round($dividend / $total * 100, $round);
		if($padRound) $result = number_format($result, $round, '.', '');
		return $result;
	} else return false; //You are not Chuck Norris.
}

function toBool($value) {
	return ($value) ? true : false;
}

function boolString($value) {
	return ($value) ? 'true' : 'false';
}

function keyHasValue($key, $array) {
	return (isset($array) && array_key_exists($key, $array) && $array[$key]);
}

function getArrayKeys(&$array) //Returns an array[] of the array keys passed
{
	if(is_array($array) && count($array) > 0) {
		$result = array();
		foreach($array as $key => $disposable) $result[] = $key;
		return $result;
	} else return false;
}

function prePrint($item, $asString = false) //Handy debugging function
{
	if($asString) {
		return "<pre>".print_r($item, true)."</pre>";
	} else {
		echo "<pre>".print_r($item, true)."</pre>";
	}
}

function buildKeyCSV($array) {
	$notFirst = false;
	$result = '';
	if(is_array($array)) {
		foreach($array as $key => $throwAway) {
			$result .= ($notFirst) ? ', '.$key : $key;
			$notFirst = true;
		}
		return $result;
	} else return $array;
}

function consolidateArray($array, $maxVals = 10, $cullSmallest = true) {
	$sortedArray = $array;

	if(count($array) > $maxVals) {
		$sortSuccess = ($cullSmallest) ? arsort($sortedArray) : asort($sortedArray);

		if($sortSuccess) {
			$array['consolidated'] = 0;
			$counter = 0;
			foreach($sortedArray as $id => $value) {
				$counter++;
				if($counter >= $maxVals) {
					$array['consolidated'] += $value;
					unset($array[$id]);
				}
			}
		}
	}
	return $array;
}

function clearLog($logFile = 'logs/log.txt') {
	$file = fopen($logFile, 'w');
	fwrite($file, '');
	fclose($file);
}

function singleLog($data, $logFile = 'logs/log.txt') {
	$file = fopen($logFile, 'a');

	if(is_array($data) || gettype($data) == 'object') $data = print_r($data, true);

	fwrite($file, $data."\r\n");
	fclose($file);
}

function columnToAlpha($num) //Converts a column number to a spreadsheet alphanumeric designation
{
	$dividend = $num;
	$result = '';

	while($dividend > 0) {
		$digit = ($dividend - 1) % 26;
		$result = chr(65 + $digit).$result;
		$dividend = (int)(($dividend - $digit) / 26);
	}

	return $result;
}

function convertSmartQuotes($string) { //converts smart quotes to normal quotes.
	$WTF = array(chr(145), chr(146), chr(147), chr(148), chr(151));
	$thatsBetter = array("'", "'", '"', '"', '-');
	return str_replace($WTF, $thatsBetter, $string);
}

function build404($request = false, $message = false, $exit = true) {
	$htmlMessage = ($message) ? "<br /><br /><pre>".htmlspecialchars($message, ENT_QUOTES)."</pre>" : false;
	if($request === false) {
		global $url;
		if(!empty($url)) $request = explode('/', $url);
	}
	if(is_array($request)) {
		foreach($request as $item) {
			switch($item) {
				case REQUEST_TYPE_AJAX:
					buildJSONResponse(array('success' => false, 'message' => 'Error: Could not find the function '.implode('/', $request)));
					exit;
				case REQUEST_TYPE_MODAL:
					include('pages/components/modalHeader.php');
					echo '<h1>Not Found</h1><p>The requested URL /'.implode('/', $request).' was not found on this server.</p><hr><address>Apache/2.2.14 (Ubuntu) Server at customintercept.com Port 80</address>'.$htmlMessage;
					include('pages/components/modalFooter.php');
					exit;
			}
		}
		header("HTTP/1.0 404 Not Found");
		echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL /'.implode('/', $request).' was not found on this server.</p><hr><address>Apache/2.2.14 (Ubuntu) Server at customintercept.com Port 80</address>'.$htmlMessage.'</body></html>';
		if($exit) exit;
	} else {
		header("HTTP/1.0 404 Not Found");
		echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL /'.$request.' was not found on this server.</p><hr><address>Apache/2.2.14 (Ubuntu) Server at customintercept.com Port 80</address>'.$htmlMessage.'</body></html>';
		if($exit) exit;
	}
}

function csvField($value, $textDelimiter = '"') {
	if(strstr($value, $textDelimiter) || strstr($value, ",")) $value = $textDelimiter.str_replace($textDelimiter, $textDelimiter.$textDelimiter, $value).$textDelimiter;
	return $value;
}

function buildFileOption($id, $name, $path, $fileExtension = 'color', $selected = false, $default = '[none/default]') {
	$result = "<select id='".$id."' name='".$name."'>\r\n";

	$result .= "\t<option>".htmlentities($default, ENT_QUOTES)."</option>\r\n";

	if($handle = opendir($path)) {
		while(false !== ($entry = readdir($handle))) {
			$extension = explode('.', $entry);
			$extension = (count($extension)) ? $extension[count($extension) - 1] : false;
			if($extension == $fileExtension) {
				$entry = str_replace('.color', '', $entry);
				$result .= ($entry == $selected) ? "\t<option selected='true'>".htmlentities($entry, ENT_QUOTES)."</option>\r\n" : "\t<option>".htmlentities($entry, ENT_QUOTES)."</option>\r\n";
			}
		}
	}

	$result .= "</select>\r\n";

	return $result;
}

//Accepts single value or an array
function toByte($value) {
	if(is_array($value)) {
		foreach($value as &$val) $val = toByte($val);
		return $value;
	} else {
		$value = (int)$value;
		$value = ($value > 255) ? 255 : $value;
		return ($value < 0) ? 0 : $value;
	}
}

function getFileExtension($fileName) {
	return substr(strrchr($fileName, '.'), 1);
}

function upDownBox($id, $class = false, $startVal = 2, $interval = 1, $min = 2, $max = 50) {
	$interval = abs($interval); //Make sure we're headed in the right direction
	$wrapperID = 'upDown-'.$id;
	$result = "
				<div id='".$wrapperID."' class='".$class." upDownBox'>
					<input type='hidden' value='".$startVal."' id='".$id."' name='".$id."' />
					<div class='valueBox left'>".$startVal."</div>
					<div class='upDownSelector'>
						<div class='upButton'></div>
						<div class='downButton'></div>
					</div>
				</div>
				<script type='text/javascript'>

					$('#".$wrapperID." .upButton').click(function()
					{
						var max = ".$max.";
						var value = Number($('#".$id."').val());
						var interval = ".$interval.";

						if(value + interval <= max)
						{
							$('#".$wrapperID." input').val(value + interval).trigger('change');
							$('#".$wrapperID." .valueBox').html(value + interval);
						}
					});

					$('#".$wrapperID." .downButton').click(function()
					{
						var min = ".$min.";
						var value = Number($('#".$id."').val());
						var interval = ".$interval.";

						if(value - interval >= min)
						{
							$('#".$wrapperID." input').val(value - interval).trigger('change');
							$('#".$wrapperID." .valueBox').html(value - interval);
						}
					});

				</script>
				";

	return $result;

}

//POST data to another script using only PHP
function postRequest($url, $data, $optional_headers = null) {
	if(is_array($data)) $data = http_build_query($data);

	$parsed = false;

	parse_str($data, $parsed); //Check to see if the data parses, in case a bad string was passed.

	if(is_string($data) && $parsed && is_array($parsed) && count($parsed)) {
		$params = array('http' => array(
			'method' => 'POST',
			'content' => $data
		));

		if($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}

		$ctx = stream_context_create($params);

		$fp = @fopen($url, 'rb', false, $ctx);

		if(!$fp) {
			throw new Exception("Problem with ".$url.", ".$php_errormsg);
		}

		$response = @stream_get_contents($fp);

		if($response === false) {
			throw new Exception("Problem reading data from ".$url.", ".$php_errormsg);
		}
		return $response;
	} else return false;
}

function ipIntersects($ip_one, $ip_two = false) {
	if($ip_two === false) {
		if($ip_one == $_SERVER['REMOTE_ADDR']) $ip = true;
		else $ip = false;
	} else {
		if(ip2long($ip_one) <= ip2long($_SERVER['REMOTE_ADDR']) && ip2long($ip_two) >= ip2long($_SERVER['REMOTE_ADDR'])) $ip = true;
		else $ip = false;
	}
	return $ip;
}

function folderTreeArray($path) {
	$root = scandir($path);
	$result = array();

	foreach($root as $value) {
		if($value === '.' || $value === '..') {
			continue;
		}
		if(is_file($path.'/'.$value)) {
			$result[] = $path.'/'.$value;
			continue;
		}

		foreach(folderTreeArray($path.'/'.$value) as $folder) {
			$result[] = $folder;
		}
	}

	return $result;
}

// Creates an array (or single entry) like $result['fileName']=>'relativeFilePath'
// Be careful with allowDupes.  Make sure if it is set to false(default), that there are no duplicate file names in the requested folder
function folderTreeFiles($path, $file = false, $allowDupes = false) {
	$list = folderTreeArray($path);
	$result = array();

	$fileName = false;

	foreach($list as $item) {
		$fileName = explode('/', $item);
		$fileName = $fileName[count($fileName) - 1];

		if($allowDupes) $result[$fileName][] = $item;
		else $result[$fileName] = $item;
	}

	if($fileName && $file) $result = (array_key_exists($file, $result)) ? $result[$file] : false;

	return $result;
}

function verifyArrayPattern($pattern, $test) {
	if($pattern & is_array($pattern) && $test && is_array($test)) {
		foreach($pattern as $key => $check) {
			if(array_key_exists($key, $test) != $check) return false;
		}
		return true;
	} else return false;
}

function pathNavigationUp($pathArray) {
	$lastItem = 0;
	end($pathArray);
	unset($pathArray[key($pathArray)]);
	foreach($pathArray as &$pathItem) $pathItem = urlencode($pathItem);
	return LINKROOT.implode('/', $pathArray);
}

function inline404($path) {
	echo "<div class='bodyLabel'><h1 class='red'>404: Could not find ".htmlentities($path, ENT_QUOTES)."</h1></div>";
}

//Requires APC: apt-get install php-apc
function refreshMime($path) {
	if(file_exists($path)) {
		if(!apc_exists('MimeRefresh') || (int)apc_fetch('MimeRefresh') < (int)filemtime($path) || !apc_exists('MimeTypes')) {
			$types = array();
			$file = fopen($path, 'r');
			while(($line = fgets($file)) !== false) {
				$line = trim(preg_replace('/#.*/', '', $line));
				if(!$line) continue;
				$parts = preg_split('/\s+/', $line);
				if(count($parts) == 1) continue;
				$type = array_shift($parts);
				foreach($parts as $part) $types[$part] = $type;
			}
			fclose($file);
			apc_store('MimeRefresh', filemtime($path));
			apc_store('MimeTypes', $types);
			return true;
		}
	} else return false;
}

//Requires APC: apt-get install php-apc
//TODO: make this accept any string, not necessarily a path to a valid file.  VERIFY THIS
function getMimeType($fileName, $path = false) {
	if(apc_exists('MimeTypes')) {
		$types = apc_fetch('MimeTypes');
		$ext = pathinfo($fileName, PATHINFO_EXTENSION);
		if(!$ext) $ext = $fileName;
		$ext = strtolower($ext);
		return array_key_exists($ext, $types) ? $types[$ext] : null;
	} else if($path) { //Force refresh and try again
		refreshMime($path);
		return getMimeType($fileName);
	} else return false;
}

function NameToURL($name, $db = false) {
	$clear = strip_tags($name);
	// Clean up things like &amp;
	$clear = html_entity_decode($clear);
	// Strip out any url-encoded stuff
	$clear = urldecode($clear);
	// Replace non-AlNum characters with space
	$clear = preg_replace('/[^A-Za-z0-9\-\/]/', ' ', $clear);
	// Replace Multiple spaces with single space
	$clear = preg_replace('/ +/', ' ', $clear);
	// Trim the string of leading/trailing space
	$clear = trim($clear);
	$clear = str_replace(' ', '', $clear);
	$clear = str_replace('/', '-', $clear);

	return ($db) ? $db->Quote($clear) : $clear;
}

function relevantDate($unixTime, $time = true, $seconds = true) {
	if($unixTime) {
		$seconds = ($time) ? $seconds : false;

		$today = date('Y m W d');
		$todaySplit = explode(' ', $today);

		$day = date('Y m W d', $unixTime);
		$daySplit = explode(' ', $day);

		$seconds = ($seconds) ? ':s' : '';

		$time = ($time) ? ' g:i'.$seconds.' a' : '';

		//Today
		if ($day == $today) $result = ($time) ? date('g:i'.$seconds.' a', $unixTime) : 'Today';

		//Yesterday
		else if (mktime(0, 0, 0, date('m'), date('d') - 1, date(date('y'))) == mktime(0, 0, 0, date('m', $unixTime), date('d', $unixTime), date(date('y', $unixTime))))
			$result = "Yesterday".date($time, $unixTime);

		//This week
		else if (mktime(0, 0, 0, date('m'), date('d') - 6, date(date('y'))) < $unixTime && microtime(true) > $unixTime)
			$result = ($time) ? date('D'.$time, $unixTime) : date('L'.$time, $unixTime);

		//This month
		//else if($daySplit[0] == $todaySplit[0] && $daySplit[1] == $todaySplit[1]) $result = date('D j g:i'.$seconds.' a', $unixTime);

		//This year
		else if ($daySplit[0] == $todaySplit[0]) $result = date('D M jS g:i'.$seconds.' a', $unixTime);
		else $result = date('Y M jS'.$time, $unixTime);

		return $result;
	}
	else return "Never";
}

function delTree($dir) {
	$files = array_diff(scandir($dir), array('.', '..'));
	foreach($files as $file) {
		(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
	}
	return rmdir($dir);
}

function trueInt($value) { return (bool)preg_match('/^-?([0-9])+$/', str_replace(" ", "", trim($value))); }
function trueFloat($value) { return (bool)preg_match('/^-?([0-9])+([\.|,]([0-9])*)?$/', str_replace(" ", "", trim($value))); }

function array_hierarchy_value($array, $keys, $boolFail = false) {
	$item = $array;
	foreach($keys as $key) {
		if(!array_key_exists($key, $item)) return ($boolFail) ? false : null;
		$item = $item[$key];
	}
	return $item;
}

function preg_match_array($regex, $string) {
	$result = array();
	$success = preg_match_all($regex, $string, $result);
	return ($success) ? $result[0] : array();
}

function Display($text, $return = false) {
	$text = htmlspecialchars($text, ENT_QUOTES);
	if($return) return $text;
	else echo $text;
}

function CharLimit($string, $limit = 20) {
	if(strlen($string) > $limit) {
		return substr($string, 0, $limit - 3).'...';
	} else return $string;
}


//New StringCompare() functions using Levenshtein Distance.  Probably faster and more accurate.
//$ratio is the length difference consideration between the two strings
function StringiCompare($string1, $string2, $ratio = 0.2) {
	return StringCompare(
		strtolower($string1)
		, strtolower($string2)
		, $ratio
	);
}

function StringCompare($string1, $string2, $ratio = 0.2) {
	$string1Length = strlen($string1);
	$string2Length = strlen($string2);
	$minLength = round(min($string1Length, $string2Length));
	$maxLength = max($string1Length, $string2Length);

	$compareLength = ($maxLength - $minLength < 4) ? $maxLength : (int)min($minLength + ($ratio * $maxLength), $maxLength);

	if($string1Length > $compareLength) $string1 = substr($string1, 0, $compareLength);
	if($string2Length > $compareLength) $string2 = substr($string2, 0, $compareLength);

	$levenshtein = ($string1Length > $string2Length) ? levenshtein($string2, $string1) : levenshtein($string1, $string2);

	return 1 - min($levenshtein / $compareLength, 1);
}

/*
function StringiCompare($string1, $string2, $chunkLength = 4, $step = 2, $log = false) {
	//if($log || true) { singleLog($string1); singleLog($string2); singleLog(''); }
	if(is_array($string1) && false) {
		return false;
	}
	return StringCompare(
		strtolower($string1)
		, strtolower($string2)
		, $chunkLength
		, $step
		, $log
	);
}

function StringCompare($string1, $string2, $chunkLength = 4, $step = 2, $log = false) {
	$string1 = trim($string1);
	$string2 = trim($string2);
	$step = max(1, $step);
	$longer = $string1;
	$shorter = $string2;
	if (strlen($string1) < strlen($string2)) {
		$longer = $string2;
		$shorter = $string1;
	}
	$longerLength = strlen($longer);
	$shorterLength = strlen($shorter);

	if ("$string1" === "$string2") {
		if ($log) singleLog($string1."\r\n".$string2."\r\n1\r\n");
		return 1;
	} else if ($longerLength > $chunkLength) { //If they are both too short, strict matching
		if ($longerLength && $shorterLength) {
			$matches = 0;
			$steps = 0;

			for ($i = 0; $i <= $shorterLength - $step; $i += $step) {
				$chunkLength = min($chunkLength, $shorterLength - $i);
				if ($chunkLength) {
					$matches += (bool)strstr($longer, substr($shorter, $i, $chunkLength));
					if ($log) singleLog(str_repeat(' ', $i).substr($shorter, $i, $chunkLength));
					$steps++;
				}
			}

			$value = ($steps) ? ($matches / $steps) * max($shorterLength / $longerLength, 0.75) : 0;
			if ($log) singleLog($shorter."\r\n".$longer."\r\n".$value."\r\n");
			return $value;
		} else {
			if ($log) singleLog($shorter."\r\n".$longer."\r\n0\r\n");
			return 0;
		}
	} else {
		if ($log) singleLog($string1."\r\n".$string2."\r\n0\r\n");
		return 0;
	}
}
*/ //Old StringCompare() functions

function ValueFromChoiceText($choices) {
	if(!is_array($choices)) $choices = array($choices);
	$value = false;
	foreach($choices as $choice) {
		$match = array();
		if(preg_match('/\A[^a-zA-Z0-9]*([0-9,]+[\.|,]?[0-9]*)/', $choice, $match) !== false) {
			if(count($match)) {
				if($value !== false && $value != $match[0]) return false;
				else $value = $match[0];
			}
		}
	}
	if($value !== false) $value = str_replace(',', '', $value); //rip out commas if it's imperial numerics
	return $value;
}

function LastArrayKey($array) {
	$keys = array_keys($array);
	return end($keys);
}

function arraykeyskeys($array) {
	$result = array();
	if(is_array($array)) {
		foreach($array as $key=>$item) $result[$key] = true;
	}
	return $result;
}

function indent($count) { return str_repeat("\t", (int)$count); }
function NiceDate($unixTime) { return date('Y M jS g:i:s a', $unixTime); }

class ListOfThings {

	private $things;
	private $singular = false;
	private $plural = false;
	private $empty = 'Nothing';

	public function __construct($things = array(), $singular = false, $plural = false, $empty = false) {
		$this->Add($things);
	}

	public function Add($things) {
		if(is_array($things)) {
			//dissociate the key
			foreach($things as $thing) $this->Add($thing);
		}
		else if($things !== false) $this->things[] = $things; //Just 1 item
		return $this;
	}

	public function IsPlural() {
		return count($this->things) > 1;
	}

	public function HasThings() {
		return (bool)count($this->things);
	}

	public function SetPredicate($singular, $plural) {
		$this->singular = ($singular) ? " ".trim($singular) : false;
		$this->plural = ($plural) ? " ".trim($plural) : false;
		return $this;
	}

	public function SetEmpty($empty) {
		$this->empty = $empty;
		return $this;
	}

	public function Clear() {
		$this->things = array();
		return $this;
	}

	public function Get($things = false) { //Overrides $things member if passed
		if($things) {
			$clone = clone $this;
			return $clone->Clear()->Add($things)->Get();
		}
		else return "$this";
	}

	public function Draw($things = false) {
		echo $this->Get($things);
	}

	public function __toString() {
		$things = $this->things; //disposable
		switch(count($this->things)) {
			case 0:
				return $this->empty;

			case 1:
				return array_pop($things).$this->singular;

			case 2:
				return implode(' and ', $things).$this->plural;

			default:
				$result = '';
				while($thing = array_shift($things)) {
					$result .= $thing;
					$count = count($things); //Instead of counting for each if()
					if($count == 1) $result .= ', and '; //OXFORD FTW!
					else if($count > 1) $result .= ', ';
				}
				return $result.$this->plural;
		}
	}
}