<?php 

if (!session_id()) session_start();

$servername="localhost";
$uname="spanoulis";
$pass="calathes";
$db="basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT UserID FROM Account WHERE Username='" . $_POST['username'] . "' AND Password='" . $_POST['password'] . "'";
$res = $conn->query($sql);
if ($res->num_rows == 0) {
	//handle wrong username-password comb here
}
else {
	$row = $res->fetch_assoc();
	$_SESSION['userID'] = $row['UserID'];
	$_SESSION['username'] = $_POST['username'];
	$conn->close();
	header("Location: teams.php");
}

?>
