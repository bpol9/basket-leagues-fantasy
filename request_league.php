<?php

$leagueID = $_GET['id'];

if (!session_id()) session_start();

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	//die("Connection to database failed: " . $conn->connect_error);
	echo "500";
	exit(1);
}

$sql = "SELECT LeagueName " .
	"FROM League " .
	"WHERE LeagueID=$leagueID";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$league_name = $row['LeagueName'];

$sql = "SELECT TeamName, ScoreOnLeague, LastScore " .
	"FROM " .
	   "(SELECT TeamID, ScoreOnLeague " .
	   "FROM TeamLeague " .
	   "WHERE LeagueID=$leagueID) t " .
	   "JOIN Team USING (TeamID)";

$res = $conn->query($sql);
$conn->close();

if ($res->num_rows == 0) {
	echo "500";
	exit(1);
}

$row = $res->fetch_assoc();
$i=0;
while ($row != NULL) {
	$league['names'][$i] = $row['TeamName'];
	$league['scores'][0][$i] = $row['ScoreOnLeague'];
	$league['scores'][1][$i] = $row['LastScore'];
	$i++;
	$row = $res->fetch_assoc();
}

$league['name'] = $league_name;
$league['teamsNo'] = $i;
echo json_encode($league);
?>
