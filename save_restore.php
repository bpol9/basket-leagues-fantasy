<?php

if (!session_id()) session_start();
$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	die("Connection to database failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['restore'])) {
	for ($i = 0; $i < $_SESSION['rc']; $i++) {
		$pos = $_SESSION['removed'][$i]['pos'];
		$index = $_SESSION['removed'][$i]['index'];
		$playerId = $_SESSION['removed'][$i]['playerId'];
		echo $_SESSION['rc'] . "<br>$pos $index $playerId<br>";

		$sql = "SELECT * FROM Player WHERE PlayerID=" . $playerId;
		$res = $conn->query($sql);
		$row = $res->fetch_assoc();
		$_SESSION[$pos][$index] = $row;
		$_SESSION[$pos][$index]['PurchasePrice'] = $_SESSION['removed'][$i]['purchasePrice'];
	}
}

$conn->close();

$_SESSION['rc'] = 0;
$_SESSION['removed']['pg'] = array();
$_SESSION['removed']['sg'] = array();
$_SESSION['removed']['pf'] = array();

header("Location: team.php?team=PAOK");

?>
