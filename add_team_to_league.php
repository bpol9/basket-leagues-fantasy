<?php

function insert_team_to_league($tid, $lid) {
	$sql = "INSERT INTO TeamLeague (TeamID, LeagueID, ScoreOnLeague) " .
		"VALUES ($tid, $lid, 0)";
	if ($GLOBALS['conn']->query($sql) === FALSE) {
		return "4";
	} else {
		return "5";
	}
}

sleep(1);

$leagueName = $_POST['leagueName'];
$leaguePass = $_POST['leaguePass'];
$teamID = $_POST['teamID'];

if (!session_id()) session_start();

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	die("Connection to database failed: " . $conn->connect_error);
}

$ret = "4"; //internal server error
$sql = "SELECT LeagueID, LeaguePassword,Deadline " .
	"FROM League ".
	"WHERE LeagueName='$leagueName'";
$res = $conn->query($sql);
if ($res->num_rows == 0) { //no such league name
	$ret = "1";
}
else { //num_rows == 1. We are sure num_rows is not greater than one, from league creation rules
	$row = $res->fetch_assoc();
	$leagueID = $row['LeagueID'];
	$sql = "SELECT count(LeagueID) as inLeague " .
		"FROM " .
		   "(SELECT TeamID FROM Team " .
		   "WHERE UserID=" . $_SESSION['userID'] . ") t " .
		"JOIN TeamLeague USING (TeamID) " .
		"WHERE LeagueID=$leagueID";
	$check_participant = $conn->query($sql);
	$row2 = $check_participant->fetch_assoc();
	if (intval($row2['inLeague']) > 0) { //it should be exactly 1
		$ret = "6";
	}
	else if ($row['LeaguePassword'] != $leaguePass) { //incorrect password
		$ret = "2";
	}
	else { //correct password
		if ($row['Deadline'] != NULL) { //there is a deadline
			$deadline_time = strtotime($row['Deadline']);
			$now_date = date('Y-m-d');
			$now_time = strtotime($now_date);
			if ($now_time > $deadline_time) { //the deadline is passed
				$ret = "3";
			} else { //in time
				$ret = insert_team_to_league($teamID, $leagueID);
			}
		}
		else { //there is no deadline
			$ret = insert_team_to_league($teamID, $leagueID);
		}
	}
}

$conn->close();

if ($ret === "5") { //Use dash after server code to send extra info associated with the code. Used only for code 5(success).
	$ret .= "-$leagueID";
}
echo $ret;

?>
