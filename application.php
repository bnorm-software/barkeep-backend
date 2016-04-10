<?php

include_once('application/CLASSES.php');
include_once('application/config.php');

$url = getParam('baseURL');

$PerfLog = new PerfLog($url);

$pathComponents = explode('/', $url);
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
                        $headers = getallheaders();
                        if($headers && isset($headers['Content-Type'])) {
                            switch(strtolower($headers['Content-Type'])) {
                                case 'application/json':
                                    if ($postString = file_get_contents("php://input")) $_POST = json_decode($postString, true);
                                    break;

                                default:
                                    break;
                            }
                        }

						session_name(SESSION_NAME);
						session_start();

						if (!isset($_SESSION['session'])) $_SESSION['session'] = new Session(new MySQLDatabase('DBCredentials'));

						$Session =& $_SESSION['session'];

						$Session->Process($_SERVER, $Path, $PerfLog);

                        session_write_close();
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
