<?php

$playerId = $_GET['playerid'];

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	die("Connection to database failed: " . $conn->connect_error);
}

$sql = "SELECT FirstName, LastName, Price, TeamName FROM Player WHERE PlayerID=" . $playerId;
$res = $conn->query($sql);
$conn->close();
$row = $res->fetch_assoc();

$tr = "<td width=\"15%\" style=\"border-left:0px\" class=\"player_column\">" . $row['LastName'] . "</td><td width=\"15%\">" . $row['TeamName'] . "</td><td class=\"right\" width=\"12%\" style=\"border-right:0px; padding:2px\"></td><td class=\"right\" width=\"12%\">" . $row['Price'] . "</td><td class=\"right\" width=\"12%\">" . $row['Price'] . "</td><td class=\"right\" width=\"14%\">-</td><td class=\"right\" width=\"15%\">AEK</td>";

echo $tr;

?>
