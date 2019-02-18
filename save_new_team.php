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

$ids_prices = explode('-', $_GET['ids']);
$team_name = $_GET['teamName'];
$rem_money = $_GET['remMoney'];

//echo "ids: " . $_GET['ids'] . " teamName: " . $team_name . " remMoney: " . $rem_money;


$sql = "INSERT INTO Team (TeamName,UserID,RemainingMoney,State,Sell_Buy_IDs) Values ('" . $team_name . "'," . $_SESSION['userID'] . "," . $rem_money . ",'u',NULL)"; 
if ($conn->query($sql) === FALSE) {
	abort_transaction("Failed to insert new team into database with sql query: " . $sql, $conn);
}
$team_id = $conn->insert_id;

for ($i=0; $i<count($ids_prices); $i++) {
	$id_price = explode('_', $ids_prices[$i]);
	$sql = "INSERT INTO TeamPlayer (TeamID, PlayerID, PurchasePrice) Values (" . $team_id . "," . $id_price[0] . "," . $id_price[1] . ")";
	if ($conn->query($sql) === FALSE) {
		abort_transaction("Failed to insert player into team.", $conn);
	}
}

$conn->commit();
$conn->close();

echo "OK";

?>
