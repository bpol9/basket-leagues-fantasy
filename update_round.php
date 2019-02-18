<?php

function exec_query($sql) {
	if ($GLOBALS['conn']->query($sql) === FALSE) {
		abort("Database query '$sql' failed.", TRUE);
	}
}

function abort($msg, $reset=FALSE) {
	if ($reset) {
		$GLOBALS['conn']->rollback();
		$GLOBALS['conn']->close();
	}
	echo "$msg\n\n";
	exit(1);
}

echo "\n";

$MATCH_NR = 7;
$tmp = ['Κολοσσός',
	'Ρέθυμνο',
	'Ολυμπιακός',
	'Κύμη',
	'ΑΕΚ',
	'Λαύριο',
	'Περιστέρι',
	'Πανιώνιος',
	'Προμηθέας',
	'Άρης',
	'Χολαργός',
	'Ήφαιστος',
	'ΠΑΟΚ',
	'Παναθηναικός'];
foreach($tmp as $t) {
	$teams[$t] = TRUE;
}

for ($i=0; $i<$MATCH_NR; $i++) {
	$match = readline("#" . ($i+1) . " match: ");
	$duo = explode('-', $match);
	if (count($duo) != 2) {
		abort("Wrong matchup. Correct matchup is '<host>-<onRoad>'.");
	}
	if (!isset($teams[$duo[0]])) {
		abort("There is no such team as '" . $duo[0] . "'.");
	}
	if (!isset($teams[$duo[1]])) {
		abort("There is no such team as '" . $duo[1] . "'.");
	}
	$matches[$i][0] = $duo[0];
	$matches[$i][1] = $duo[1];
}


$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	abort("Connection to database failed.");
}

$conn->autocommit(FALSE);
exec_query("TRUNCATE TABLE Round");

for ($i=0; $i<$MATCH_NR; $i++) {
	$sql = "INSERT INTO Round " .
		"(Host, OnRoad) " .
		"Values ('" . $matches[$i][0] . "','" . $matches[$i][1] . "')";
	exec_query($sql);
}

$conn->commit();
$conn->close();

echo "\nUpdated Round table successfully.\n\n";

?>
