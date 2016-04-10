<?php

//error_reporting(-1);
//ini_set('display_errors: E_ALL');

const DEBUG = true;

const LINKROOT = '/'; // The base web directory where the application is installed
const SESSION_NAME = 'barkeepdev';

const LOGPERFORMANCE = true;

const SQLLOGFILE = 'logs/sql.txt';

////No longer using old SQL manager  It's bad, mmkay?
//$credentials[] = new dbCredentials('localhost', 'V4', '8oN5w2MoSiFab7zuLeqa423oKavE5o', 'V4'); //primary database

$DBCredentials = array(
	'host' => 'localhost',
	'name' => 'barkeep',
	'user' => 'barkeep',
	'pass' => 'muwiLe6EN6QA56RupELI8Il72aN8b2'
);

const LOGQUERIES = true;
const QUERYLOG = 'logs/queries.txt';

//Cannot be used for Client names
$reservedNames = array(
	'Manage' => true,
	'Get' => true,
	'Login' => true,
	'Logout' => true,
	'Do' => true,
	'images' => true,
	'logs' => true,
	'pages' => true,
	'thirdParty' => true,
	'css' => true,
	'js' => true,
	'application' => true
);