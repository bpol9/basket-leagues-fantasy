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

if ($pos == "pg") {
	$sql = "SELECT PlayerID, FirstName, LastName, Price FROM Player WHERE Position='PG'";
}
else if ($pos == "sg" || $pos == "sf" || $pos == "sg/sf") {
	$sql = "SELECT PlayerID, FirstName, LastName, Price FROM Player WHERE Position='SG' OR Position='SF' OR Position='SG/SF'";
}
else if ($pos == "pf" || $pos == "c" || $pos == "pf/c") {
	$sql = "SELECT PlayerID, FirstName, LastName, Price FROM Player WHERE Position='PF' OR Position='C' OR Position='PF/C'";
}

$res = $conn->query($sql);
$conn->close();

$output = "<tr><th>First Name</th><th>Last Name</th><th>Price</th></tr>";
$row = $res->fetch_assoc();
while ($row != NULL) {
	$output .= "<tr><td>" . $row['FirstName'] . "</td><td>" . $row['LastName'] . "</td><td>" . $row['Price'] . "</td></tr>";
	$row = $res->fetch_assoc();
}

echo $output;

?>
