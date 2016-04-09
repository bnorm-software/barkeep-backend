<?php

$postString = file_get_contents("php://input");
if ($postString) $_POST = json_decode($postString, true);

include_once('application/CLASSES.php');
include_once('application/config.php');

$Start = microtime(true);

$url = getParam('baseURL');
$perfLog = "\r\n" . date("Y-m-d H:i:s") . " - $url\r\n";

$pathComponents = explode('/', $url); //Reversed because array_pop() is faster than array_shift()  Not that it matters significantly on arrays with <10 items.
$Path = array();

foreach ($pathComponents as $pathItem) {
	if ($pathItem) $Path[] = urldecode($pathItem);
}

$requestProtocol = (!empty($Path)) ? array_shift($Path) : false;
$requestBase = (!empty($Path)) ? array_shift($Path) : false;
$requestVersion = (!empty($Path)) ? array_shift($Path) : false;

#region DEBUG LOGGER
if(DEBUG) {
	$tracePatterns = array(
		//'books'
		//,'test'
	);

	foreach ($tracePatterns as $tracePattern) {
		if (strstr($url, $tracePattern)) {
			xdebug_start_trace("logs/xdebug-$tracePattern");
			break;
		}
	}
}
#endregion

switch ($requestProtocol) {
	case "api":
		switch ($requestBase) {
			case "rest":
				switch ($requestVersion) {
					case "v1":
						session_name(SESSION_NAME);
						session_start();

						$SessionLoadTime = microtime(true) - $Start;

						if (!isset($_SESSION['session'])) $_SESSION['session'] = new Session(new MySQLDatabase('DBCredentials'));

						$Session =& $_SESSION['session'];

						$perfLog .= "Session ID:            " . session_id() . "\r\n";
						$perfLog .= "Session IP Address:    " . $_SERVER['REMOTE_ADDR'] . "\r\n";
						$perfLog .= "Session started in     " . number_format(microtime(true) - $Start, 4) . " seconds.\r\n";

						$processStart = microtime(true);

						$Session->Process($_SERVER, $Path);
						break;

					default:
						APIResponse(RESPONSE_400);
						break;
				}
				break;

			default:
				APIResponse(RESPONSE_400);
				break;
		}
		break;

	default:
		APIResponse(RESPONSE_400);
		break;
}


$perfLog .= "Command processed in   " . number_format(microtime(true) - $processStart, 4) . " seconds.\r\n";
$perfLog .= "Completed in           " . number_format(microtime(true) - $Start, 4) . " seconds.\r\n";

singleLog($perfLog, 'logs/PerfLog.txt');
