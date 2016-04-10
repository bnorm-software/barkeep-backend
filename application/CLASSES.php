<?php

include_once('application/common.php');
include_once('application/FUNCTIONS.php');
include_once('application/System/CLASS_System.php');

function __autoload($className) {
	switch ($className) {
		case 'MySQLDatabase':
			include_once('thirdParty/System/CLASS_MySQLDatabase.php');
			break;
		case 'SQLInsertBuilder':
			include_once('thirdParty/System/CLASS_SQLSystem.php');
			break;
		case 'Session':
			include_once('application/CLASS_Session.php');
			break;
		case 'Book':
			include_once('application/CLASS_Book.php');
			break;
		case 'SqlFormatter':
			include_once('thirdParty/SqlFormatter.php');
			break;
		case 'PerfLog':
			include_once('thirdParty/CLASS_PerfLog.php');
			break;
		default:
			$grenade = new Exception('Could not locate class: '.$className);
			throw $grenade;
	}
}