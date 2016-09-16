<?php

/** @return string[] */
function ParseHeader() {
	$result = array();
	prePrint(getallheaders());
	exit;
	$headers = explode(",", getallheaders());
	if ($headers) {
		foreach ($headers as $header) {
			$header = explode(":", $header);
			if ($header && count($header) == 2) $result[$header[0]] = $header[1];
		}
	}
	return $result;
}

const RESPONSE_200 = '200 OK';
const RESPONSE_404 = '404 Not Found';
const RESPONSE_401 = '401 Not Authorized';
const RESPONSE_400 = '400 Bad Request';
const RESPONSE_500 = '500 Internal Error';
/**
 * @param string $type
 * @param string $message
 */
function APIResponse($type = RESPONSE_404, $message = null) {
	switch ($type) {
		case RESPONSE_200:
		case RESPONSE_404:
		case RESPONSE_401:
		case RESPONSE_400:
		case RESPONSE_500:
			header("Status: $type");
			header('Cache-Control: max-age=30');
			if (!is_null($message)) {
				if (!is_array($message)) $message = array('message' => $message);
				buildJSONResponse($message);
			}
			break;
		default:
			APIResponse(RESPONSE_500);
			break;
	}
	exit;
}

function Nullable($value) { return ($value) ? $value : null; }