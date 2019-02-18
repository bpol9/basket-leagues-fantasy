
<?php

function abort_transaction($msg="No debug message available.\n\n", $conn) {
	$conn->rollback();
	$conn->query("UNLOCK TABLES");
	echo $msg;
	$conn->close();
	exit(1);
}

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$conn->autocommit(FALSE);
$sql = "LOCK TABLES MostPopular WRITE, LessPopular WRITE, Player READ";
if ($conn->query($sql) === FALSE) {
	abort_transaction("Update failed. Can't lock tables.\n\n", $conn);
}

if ($conn->query("TRUNCATE TABLE MostPopular") === FALSE) {
	abort_transaction("Update failed. Can't truncate MostPopular table.\n\n", $conn);
}
if ($conn->query("TRUNCATE TABLE LessPopular") === FALSE) {
	abort_transaction("Update failed. Can't truncate LessPopular table.\n\n", $conn);
}

$sql = "INSERT INTO MostPopular (FirstName, LastName, Score) " . 
	"SELECT FirstName, LastName, Popularity FROM Player " .
	"ORDER BY Popularity DESC " .
	"LIMIT 5 OFFSET 0";
if ($conn->query($sql) === FALSE) {
	abort_transaction("Update failed. Can't insert into MostPopular table.\n\n", $conn);
}

$sql = "INSERT INTO LessPopular (FirstName, LastName, Score) " . 
	"SELECT FirstName, LastName, Popularity FROM Player " .
	"ORDER BY Popularity ASC " .
	"LIMIT 5 OFFSET 0";
if ($conn->query($sql) === FALSE) {
	abort_transaction("Update failed. Can't insert into LessPopular table.\n\n", $conn);
}

$conn->commit();
$conn->query("UNLOCK TABLES");
$conn->close();

echo "Updated MostPopular and LessPopular tables successfully.\n\n";

?>
