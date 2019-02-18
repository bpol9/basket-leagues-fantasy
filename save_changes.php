<?php

function abort_transaction($msg="No debug message available.", $conn=NULL) {
	if ($conn != NULL) {
		$conn->rollback();
		$conn->close();
	}
	echo $msg;
	exit(1);
}

function exec_query($conn, $sql, $dmsg) {
	if ($conn->query($sql) === FALSE) {
		abort_transaction($dmsg, $conn);
	}
}

sleep(1);

if (!session_id()) session_start();

$c=0;
if (isset($_GET['sid1'])) {
	$sold[$c] = $_GET['sid1'];
	$bought[$c++] = $_GET['bid1'];
} else {
	abort_transaction("No player ids were received in the url.");
}
if (isset($_GET['sid2'])) {
	$sold[$c] = $_GET['sid2'];
	$bought[$c++] = $_GET['bid2'];
}
if (isset($_GET['sid3'])) {
	$sold[$c] = $_GET['sid3'];
	$bought[$c++] = $_GET['bid3'];
}

if (isset($_GET['remMoney'])) {
	$remaining_money = $_GET['remMoney'];
} else {
	abort_transaction("No remaining money value was received in the url.");
}

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	die("Connection to database failed: " . $conn->connect_error);
}

//Start a transaction
$conn->begin_transaction(MYSQLI_TRANS_READ_WRITE);
//$conn->autocommit(FALSE);

$sell_buy_IDs = "";

for ($i=0; $i<$c; $i++) {
	$sql = "UPDATE Player " .
		"SET Popularity = Popularity - 1 " .
		"WHERE PlayerID = " . $sold[$i];
	exec_query($conn, $sql, "Updating Popularity for PlayerID " . $sold[$i]);
	$sql = "UPDATE Player " .
		"SET Popularity = Popularity + 1 " .
		"WHERE PlayerID = " . $bought[$i];
	exec_query($conn, $sql, "Updating Popularity for PlayerID " . $bought[$i]);
	$sell_buy_IDs .= $sold[$i] . "#" . $bought[$i];
	if ($i < ($c-1)) $sell_buy_IDs .= "%";
}

$sql = "UPDATE Team SET Sell_Buy_IDs='" . $sell_buy_IDs . "' WHERE TeamID=" . $_SESSION['TeamID'];
exec_query($conn, $sql, "Updating Sell_Buy_IDS for TeamID " . $_SESSION['TeamID']);

$sql = "UPDATE Team SET State='s' WHERE TeamID=" . $_SESSION['TeamID'];
exec_query($conn, $sql, "Updating State for TeamID " . $_SESSION['TeamID']);

$sql = "UPDATE Team SET RemainingMoney=" . $remaining_money . " WHERE TeamID=" . $_SESSION['TeamID'];
exec_query($conn, $sql, "Updating RemainingMoney for TeamID " . $_SESSION['TeamID']);

$conn->commit();
$conn->close();

echo "OK";

?>
