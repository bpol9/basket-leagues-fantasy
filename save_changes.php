<?php

function abort_transaction($msg="No debug message available.", $conn) {
	$conn->rollback();
	echo $msg;
	$conn->close();
	exit(1);
}

sleep(1);

if (!session_id()) session_start();

$c=0;
if (isset($_GET['sid1'])) { $sold[$c] = $_GET['sid1']; $bought[$c++] = $_GET['bid1']; }
if (isset($_GET['sid2'])) { $sold[$c] = $_GET['sid2']; $bought[$c++] = $_GET['bid2']; }
if (isset($_GET['sid3'])) { $sold[$c] = $_GET['sid3']; $bought[$c++] = $_GET['bid3']; }

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
	$sell_buy_IDs .= $sold[$i] . "#" . $bought[$i];
	if ($i < ($c-1)) $sell_buy_IDs .= "%";
}

$sql = "UPDATE Team SET Sell_Buy_IDs='" . $sell_buy_IDs . "' WHERE TeamID=" . $_SESSION['TeamID'];
$conn->query($sql);

$sql = "UPDATE Team SET State='s' WHERE TeamID=" . $_SESSION['TeamID'];
$conn->query($sql);

$conn->commit();
$conn->close();

echo "OK";

?>
