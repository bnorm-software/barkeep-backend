<?php

/*
 * All this crap switches the environment to run from the testing database and session pool.
 * Pass in /reset, and the database and sessions will be cleared, and a default user will be created.
 * 
 * Magic!
 */

$dbCredentials = 'UnitTestDBCredentials';

if (!file_exists(UNITTESTSESSIONPATH)) mkdir(UNITTESTSESSIONPATH);
session_save_path(UNITTESTSESSIONPATH);

if (isset($Path[0]) && $Path[0] == 'reset') {
	$rm = "rm " . UNITTESTSESSIONPATH . "*";
	$rm = `$rm`;

	$db = new MySQLDatabase('UnitTestDBCredentials');

	$tables = $db->Query("
		SELECT table_name AS `table`
		FROM information_schema.tables
		WHERE table_schema = " . $db->Quote($UnitTestDBCredentials['name']) . ";
	");

	singleLog($tables);
	if ($tables) {
		$db->Query("SET FOREIGN_KEY_CHECKS = 0;");

		while ($table = $tables->Fetch()) {
			singleLog($db->Query("DROP TABLE IF EXISTS ".$table['table'].";"));
		}

		$db->Query("SET FOREIGN_KEY_CHECKS = 1;");
	}

	$dump = "mysqldump -u " . $DevDBCredentials['user'] . " -p" . $DevDBCredentials['pass'] . " -d " . $DevDBCredentials['name'] . " | mysql -u " . $UnitTestDBCredentials['user'] . " -p" . $UnitTestDBCredentials['pass'] . " -D" . $UnitTestDBCredentials['name'] . "";
	$dump = `$dump`;

	$makeUser = $db->Query("INSERT INTO tblUsers (username, password, accountType, displayName, email) VALUES ('joe', 'nohomohug', 'Standard', 'Joe Testmoore', 'joe@testmoore.com');");

	APIResponse(RESPONSE_200, "Cleared the database and sessions.");
	exit;
}