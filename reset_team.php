<?php

function abort_transaction($msg="No debug message available.", $conn) {
	$conn->rollback();
	echo $msg;
	$conn->close();
	exit(1);
}

if (!session_id()) session_start();

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	die("Connection to database failed: " . $conn->connect_error);
}


$conn->autocommit(FALSE);
//$conn->begin_transaction(MYSQLI_TRANS_READ_WRITE);

$sql = "SELECT Sell_Buy_IDs FROM Team WHERE TeamID=" . $_SESSION['TeamID'];
$res = $conn->query($sql);
$row = $res->fetch_assoc();


$players = explode("%", $row['Sell_Buy_IDs']);
$ret_str = "";
$c = count($players);
for ($i=0; $i<$c; $i++) {
	$ids = explode("#", $players[$i]);

	$sql = "SELECT PurchasePrice FROM TeamPlayer WHERE TeamID=" . $_SESSION['TeamID'] . " AND PlayerID=" . $ids[0];
	$res = $conn->query($sql);
	$row = $res->fetch_assoc();
	if ($row == NULL) abort_transaction("Selecting from TeamPlayer with PlayerID,TeamID returned 0 results.", $conn);
	$pur_price = $row['PurchasePrice'];

	$sql = "SELECT FirstName, LastName, TeamName, Price, LastWeekScore FROM Player WHERE PlayerID=" . $ids[0];
	$res = $conn->query($sql);
	$row = $res->fetch_assoc();
	if ($row == NULL) abort_transaction("Selecting from Player with PlayerID returned 0 results.", $conn);

	$ret_str .= $ids[1] . "#" . $row['LastName'] . "#" . $row['TeamName'] . "#" . $ids[0] . "#" . $pur_price . "#" . $row['Price'] . "#" . $row['LastWeekScore'];
	if ($i < ($c-1)) $ret_str .= "%";
}

$sql = "UPDATE Team SET Sell_Buy_IDs=NULL WHERE TeamID=" . $_SESSION['TeamID'];
$conn->query($sql);
$sql = "UPDATE Team SET State='u' WHERE TeamID=" . $_SESSION['TeamID'];
$conn->query($sql);

$conn->commit();
$conn->close();

echo $ret_str;

?>
