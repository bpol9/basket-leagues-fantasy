<!DOCTYPE html>

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

$sql = "SELECT TeamName, TeamID FROM Team WHERE UserID=" . $_SESSION['userID'];
$res = $conn->query($sql);
if ($res->num_rows == 0) {
	$has_team = FALSE;
}
else {
	$has_team = TRUE;
	$teamsNo = $res->num_rows;
	$row = $res->fetch_assoc();
	$i = 0;
	while ($row != NULL) {
		$teams[$i] = $row['TeamName'];
		$teamIDs[$i++] = $row['TeamID'];
		$row = $res->fetch_assoc();
	}
}
$conn->close();

?>

<html>
<head>
<style>
	ul.menu {
		list-style-type: none;
		margin: 0;
		padding: 0;
		overflow: hidden;
		background-color: #f1f1f1;
	}

	ul.menu li {
		float: left;
		border-right: 1px solid #555;
	}

	ul.menu li a {
		display: block;
		color: #38b0de;
		font-weight: bold;
		text-align: center;
		padding: 14px 16px;
		text-decoration: none;
	}

	li:last-child {
		border-right: none;
	}

	ul.menu li a.active {
		background-color: #4CAF50;
		/*background-color: black;*/
		color: white;
	}

	li a:hover:not(.active) {
		background-color: #555;
		color: white;
	}

	.heading {
		color: #555;
		font-family: 'Lato Bold',sans-serif;
		font-size: 1.25em;
		line-height: 1.3;
		font-weight: normal;
	}
</style>
</head>

<body>
	<ul class="menu">
		<li><a class="active" href="./teams.php">Ομάδες</a></li>
		<li><a href="./leagues.php">Λίγκες</a></li>
		<li><a href="./guides.php">Οδηγίες</a></li>
		<li style="float:right"><a href="./help.php">Αποσύνδεση</a></li>
	</ul>

	<div class="team-list">
		<h3 class="heading">Οι ομάδες μου</h3>
		<?php if (!$has_team) { ?>
		<p class="no_teams_message">Καμία ομάδα ακόμα</p>
		<?php } else { ?>
			<ul>
			<?php for ($i=0; $i<$teamsNo; $i++) { ?>
			<li><?php echo "<a href=\"team.php?team=" . $teams[$i] . "\">" . $teams[$i] . "</a>" ?></li>
			<?php } ?>
			</ul>
		<?php } ?>
	</div>
				
</body>
</html>
