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


$sql = "SELECT Host, OnRoad FROM Round";
$sched = $conn->query($sql);
$row1 = $sched->fetch_assoc();
while ($row1 != NULL) {
	$host = $row1['Host'];
	$on_road = $row1['OnRoad'];
	$opponents[$host] = $on_road;
	$opponents[$on_road] = '@' . $host;
	$row1 = $sched->fetch_assoc();
}

if ($pos == 0) {
	$sql = "SELECT PlayerID, FirstName, LastName, Price, TeamName, MeanScore FROM Player WHERE Position='PG'";
}
else if ($pos == 1) {
	$sql = "SELECT PlayerID, FirstName, LastName, Price, TeamName, MeanScore FROM Player WHERE Position='SG' OR Position='SF' OR Position='SG/SF'";
}
else if ($pos == 2) {
	$sql = "SELECT PlayerID, FirstName, LastName, Price, TeamName, MeanScore FROM Player WHERE Position='PF' OR Position='C' OR Position='PF/C'";
}

$res = $conn->query($sql);
$conn->close();

$ret_str = "";
$row = $res->fetch_assoc();
while ($row != NULL) {
	$ret_str .= $row['LastName'] . " ." . substr($row['FirstName'],0,1) .
			"#" . $row['TeamName'] .
			"#" . $opponents[$row['TeamName']] .
			"#" . $row['MeanScore'] .
			"#" . $row['Price'] .
			"#" . $row['PlayerID'] .
			"%";
	$row = $res->fetch_assoc();
}

$ret_str = rtrim($ret_str,'%');
echo $ret_str;

?>
