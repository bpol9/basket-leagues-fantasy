<?php

$pos = $_GET['pos'];

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	die("Connection to database failed: " . $conn->connect_error);
}


if ($pos == 0) {
	$sql = "SELECT PlayerID, FirstName, LastName, Price FROM Player WHERE Position='PG'";
}
else if ($pos == 1) {
	$sql = "SELECT PlayerID, FirstName, LastName, Price FROM Player WHERE Position='SG' OR Position='SF' OR Position='SG/SF'";
}
else if ($pos == 2) {
	$sql = "SELECT PlayerID, FirstName, LastName, Price FROM Player WHERE Position='PF' OR Position='C' OR Position='PF/C'";
}

$res = $conn->query($sql);
$conn->close();

/*
$output = "<table id=\"modalBuyTable\"><tr><th>Παίκτης</th><th>Μέσος Όρος</th><th>Επόμενος Αντίπαλος</th><th>Τιμή</th><th>-</th></tr>";
$row = $res->fetch_assoc();

while ($row != NULL) {
	$output .= "<tr><td>" . $row['LastName'] . "</td><td>-</td><td>-</td><td>" . $row['Price'] . "</td><td><a href=\"#\" onclick=\"buyPlayer(" . $row['PlayerID'] . ")\">Buy</a></tr>";
	$row = $res->fetch_assoc();
}
$output .= "</table>";

echo $output;
 */

$ret_str = "";
$row = $res->fetch_assoc();
while ($row != NULL) {
	$ret_str .= $row['LastName'] . "#" . "-" . "#" . "-" . "#" . $row['Price'] . "#" . $row['PlayerID'] . "%";
	$row = $res->fetch_assoc();
}

$ret_str = rtrim($ret_str,'%');
echo $ret_str;

?>
