<?php

$league_name = $_POST['name'];
$league_password = $_POST['pass'];
$teamID = $_POST['teamID'];
$league_deadline = $_POST['deadline'];

$deadline_timestamp = strtotime("+$league_deadline days");
$deadline_date = date('Y-m-d', $deadline_timestamp);

sleep(1);

if (!session_id()) session_start();

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	die("Connection to database failed: " . $conn->connect_error);
}


$ret = "2"; //server internal error as default
$sql = "SELECT COUNT(LeagueID) as NameCount " .
	"FROM League " .
	"WHERE LeagueName='$league_name'";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
if (intval($row['NameCount']) > 0) { //league name taken
	$ret = "1";
}
else {
	$conn->autocommit(FALSE);
	$sql = "INSERT INTO League (LeagueName, LeaguePassword, Deadline) " .
		 "VALUES ('$league_name', '$league_pass', '$deadline_date')";
	if ($conn->query($sql) === FALSE) {
		$conn->rollback();
		$ret = "2";
	}
	else {
		$leagueID = $conn->insert_id;
		$sql = "INSERT INTO TeamLeague (TeamID, LeagueID, ScoreOnLeague) " .
			"VALUES ($teamID, $leagueID, 0)";
		if ($conn->query($sql) === FALSE) {
			$conn->rollback();
			$ret = "2";
		}
		else {
			$conn->commit();
			$ret = "3-$leagueID";
		}
	}
}

$conn->close();
echo $ret;

?>
