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
		border: 1px solid #f1f1f1;
		border-radius: .4em;
		margin-top: 10px;
	}

	ul.menu li {
		float: left;
		border-right: 1px solid #555;
	}

	ul.menu li a {
		display: block;
		color: #38b0de;
		font-weight: bold;
		font-size: 15px;
		text-align: center;
		padding: 10px 12px;
		text-decoration: none;
	}

	ul.menu li:last-child {
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

	.team-list {
		border: 0px solid black;
		width: 40%;
	}

	.team-score {
	}

	tr.team-row>td {
		padding-top: 5px;
	}

	.button {
		background-color: orange;
		border: none;
		color: white;
		padding: 15px 20px;
		text-align: center;
		text-decoration: none;
		display: inline-block;
		font-weight: bold;
		font-size: 11px;
		margin: 4px 2px;
		cursor: pointer;
	}
	#teams-table>tbody>tr:last-child>td {padding-bottom: 5px;}
	.top-banner {
		width:100%;
		line-height:0px;
		background-color:white;
		text-align:center;
		border:1px solid lightblue;
		border-radius:.4em;
	}
	.top-banner img {
		vertical-align:middle;
		display:inline-block;
		width:120px;
		height:120px;
	}
	.top-banner p {
		vertical-align:middle;
		display:inline-block;
		font-size:40px;
		font-weight:bold;
		color:#38b0de;
	}
	.no_teams_message {
		color: #555;
		font-family: 'Lato Bold',sans-serif;
		font-size: 1.05em;
		line-height: 1.3;
		font-weight: normal;
		font-style: italic;
		margin-left:30px;
		margin-top:30px;
	}		
</style>
</head>

<body>
	<div class="top-banner">
		<p>Basket League Fantasy 2018-19</p>
		<img src="./images/hoops2.jpg">
	</div>
	<ul class="menu">
		<li><a class="active" href="./teams.php">Ομάδες</a></li>
		<li><a href="./leagues.php">Λίγκες</a></li>
		<li><a href="./guides.php">Οδηγίες</a></li>
		<li style="float:right"><a href="./logout.php">Αποσύνδεση</a></li>
	</ul>

	<div class="team-list">
		<h3 class="heading">Οι ομάδες μου</h3>
		<?php if (!$has_team) { ?>
		<p class="no_teams_message">Καμία ομάδα ακόμα</p>
		<?php } else { ?>
			<table id="teams-table" style="border-bottom:1px solid lightgrey; width:100%">
			<tbody>
			<?php for ($i=0; $i<$teamsNo; $i++) { ?>
			<tr class="team-row"><td>&#x1f3c0 <a href="team.php?team=<?php echo $teams[$i] ?>"><?php echo $teams[$i] ?></a></td><td style="float:right">509.05</td></tr>
			<?php } ?>
			</tbody>
			</table>
		<?php } ?>
	</div>
	<div style="<?php echo ($has_team)?'text-align:center;':''?> width:40%; margin-top:15px">
		<a href="create_team.php" class="button" style="<?php echo ($has_team)?'':'margin-left:20px;' ?>">ΔΗΜΙΟΥΡΓΙΑ ΟΜΑΔΑΣ</a>
	</div>
				
</body>
</html>
