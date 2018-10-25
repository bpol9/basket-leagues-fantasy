<!DOCTYPE html>

<?php

if (!session_id()) session_start();

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$team = $_GET['team'];

$sql = "SELECT TeamID FROM Team WHERE UserID=" . $_SESSION['userID'] . " AND TeamName='" . $team . "'";
echo $sql . "<br>";
$res1 = $conn->query($sql);
if ($res1->num_rows == 0) {
	die("Internal Server Error");
}

$row1 = $res1->fetch_assoc();
$teamId = $row1['TeamID'];
$sql = "SELECT PlayerID,PurchasePrice FROM TeamPlayer WHERE TeamID=" . $teamId;
$res1 = $conn->query($sql);
$pgi = $sgi = $pfi = 0;
$row1 = $res1->fetch_assoc();
while ($row1 != NULL) {
	$playerId = $row1['PlayerID'];
	$sql = "SELECT FirstName,LastName,Position,Price,TeamName,LastWeekScore FROM Player WHERE PlayerID=" . $playerId;
	$res2 = $conn->query($sql);
	$row2 = $res2->fetch_assoc();
	switch ($row2['Position']) {
	case "PG":
		$_SESSION['pgs'][$pgi] = $row2;
		$_SESSION['pgs'][$pgi]['PlayerID'] = $playerId;
		$_SESSION['pgs'][$pgi++]['PurchasePrice'] = $row1['PurchasePrice'];
		break;
	case "SG":
	case "SF":
	case "SG/SF":
		$_SESSION['sgs'][$sgi] = $row2;
		$_SESSION['sgs'][$sgi]['PlayerID'] = $playerId;
		$_SESSION['sgs'][$sgi++]['PurchasePrice'] = $row1['PurchasePrice'];
		break;
	case "PF":
	case "C":
	case "PF/C":
		$_SESSION['pfs'][$pfi] = $row2;
		$_SESSION['pfs'][$pfi]['PlayerID'] = $playerId;
		$_SESSION['pfs'][$pfi++]['PurchasePrice'] = $row1['PurchasePrice'];
		break;
	}
	$row1 = $res1->fetch_assoc();
}
$conn->close();
/*
for ($i=0; $i<2; $i++) {
	echo $pgs[$i]['PurchasePrice'];
	echo $sgs[$i]['PurchasePrice'];
	echo $pfs[$i]['LastName'];
}
 */
?>

<html>
<head>
<style>
	ul {
		list-style-type: none;
		margin: 0;
		padding: 0;
		overflow: hidden;
		background-color: #f1f1f1;
	}

	li {
		float: left;
		border-right: 1px solid #555;
	}

	li a {
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

	li a.active {
		background-color: #4CAF50;
		color: white;
	}

	li a:hover:not(.active) {
		background-color: #555;
		color: white;
	}

	td.player_column {
		text-align: left;
		border-bottom: 1px solid white;
		border-collapse: collapse;
		color: grey;
		font-weight: bold;
		font-size: 13px;
		padding:4px;
		padding-left: 12px;
	}

	table#teamTable {
		border: 1px solid white;
		border-collapse: collapse;
		border-right: 0px;
		border-left: 0px;
		background-color: white;
	}

	table#teamTable td:not(.player_column) {
		text-align: center;
		border-bottom: 1px solid white;
		border-collapse: collapse;
		color: grey;
		font-weight: bold;
		font-size: 13px;
		padding: 4px;
	}

	table#headTable {
		background-color: hotpink;
		border: 1px solid white;
		border-collapse: collapse;
		margin-top: 40px;
	}

	table#headTable td {
		font-size:15px;
		border-bottom: 1px solid white;
		border-collapse: collapse;
		color:white;
		font-weight: bold;
	}

	.right {
		float:right;
	}
	.pos_row {
		/*background-color: #73b1b7;*/
		background-color: #8db6cd;
	}

	.modal {
		display: none;
		position: fixed;
		z-index: 1;
		padding-top:50px;
		left:0;
		top: 0;
		width: 100%;
		height: 100%;
		overflow: auto;
		background-color: rgb(0,0,0); /*Fallback color */
		background-color: rgba(0,0,0,0.4);
	}

	.modal-content {
		background-color: #fefefe;
		margin: auto;
		padding: 20px;
		border: 1px solid #888;
		width: 40%;
		height: 60%;
	}

	td a {
		text-decoration: none;
		background-color: white;
		border: 1px solid green;
		color:black;
		padding: 1px 1px;
		padding-left:3px;
		padding-right:3px;
		text-align:center;
		display: inline-block;
	}
</style>
</head>
<body>
	<ul>
		<li><a class="active" href="./teams.php">Ομάδες</a></li>
		<li><a href="./leagues.php">Λίγκες</a></li>
		<li><a href="./guides.php">Οδηγίες</a></li>
		<li style="float:right"><a href="./help.php">Αποσύνδεση</a></li>
	</ul>
	
	<div>
	<table id="headTable" border="0px" width="65%">
		<tr>
		<td width="70px"><b>Ομάδα:</b></td><td width="120px"><input size="10" type="text" name="team" value="Θρύλος"></td>
		<td width="150px">Σκορ εβδομάδας:</td><td width="90px"><input size="6" type="text" name="week_score" value="200.05"></td>
		<td><form action="save_restore.php" method="post">
			<input style="float:right; width:110px" type="submit"  name="save" value="Αποθήκευση">
		</form></td>
		</tr>
		<tr>
		<td width="70px">Χρόνος:</td><td width="120px"><input size="10" type="text" name="time" value="01:45:11"></td>
		<td width="150px">Χρήματα:</td><td width="90px"><input size="6" type="text" name="money" value="90.04"></td>
		<td><form action="save_restore.php" method="post">
			<input style="float:right; width:110px" type="submit" name="restore" value="Αναίρεση">
		</form></td>
		</tr>
	</table>
	<table id="teamTable" width="65%">
		<tr>
		<td class="player_column" width="15%" style="border-left:0px">Παίκτης</td>
		<td width="15%">Ομάδα</td></td>
		<td class="right" width="12%" style="border-right:0px">Πώληση Αγορά</td>
		<td class="right" width="12%" style="border-right:0px">Τιμή αγοράς</td>
		<td class="right" width="12%">Τωρινή τιμή</td>
		<td class="right" width="14%">Πρόσφατο σκορ</td>
		<td class="right" width="15%">Επόμενος αντίπαλος</td>
		</tr>

		<tr>
		<td colspan="6" class="pos_row" style="border-right:0px"><font color="white">Point Guards</font></td>
		</tr>

		<?php for ($i=0; $i<2; $i++) {
		if (!in_array($i, $_SESSION['removed']['pg'])) { ?>
		<tr>
		<td width="15%" style="border-left:0px" class="player_column"><?php echo $_SESSION['pgs'][$i]['LastName'] ?></td>
		<td width="15%"><?php echo $_SESSION['pgs'][$i]['TeamName'] ?></td>
		<td class="right" width="12%" style="border-right:0px; padding:2px"><?php echo "<a href=\"remove.php?pos=pg&index=" . urlencode($i) . "&team=" . urlencode($team) . "&playerId=" . urlencode($_SESSION['pgs'][$i]['PlayerID']) . "\">Sell</a>" ?></td>
		<td class="right" width="12%" style="border-right:0px"><?php echo $_SESSION['pgs'][$i]['PurchasePrice'] ?></td>
		<td class="right" width="12%"><?php echo $_SESSION['pgs'][$i]['Price'] ?></td>
		<td class="right" width="14%"><?php echo $_SESSION['pgs'][$i]['LastWeekScore'] ?></td>
		<td class="right" width="15%">Trigger?</td>
		</tr>

		<?php }
		else { ?>
			<tr>
			<td width="15%" style="border-left:0px; padding-left:30px;" class="player_column">-</td>
			<td width="15%">-</td>
			<td class="right" width="12%" style="border-right:0px; padding:2px"><?php echo "<a class=\"button_link\" href=\"#\" onclick=\"displayModal(0,$i)\">Buy</a>"; ?></td>
			<td class="right" width="12%" style="border-right:0px">-</td>
			<td class="right" width="12%">-</td>
			<td class="right" width="14%">-</td>
			<td class="right" width="15%">-</td>
			</tr>
		<?php }
		} ?>


		<tr>
		<td colspan="6" class="pos_row" style="border-right:0px"><font color="white">Shooting Guards and Small Forwards</font></td>
		</tr>
		<?php for ($i=0; $i<4; $i++) { ?>
		<tr>
		<td width="15%" style="border-left:0px" class="player_column"><?php echo $_SESSION['sgs'][$i]['LastName'] ?></td>
		<td width="15%"><?php echo $_SESSION['sgs'][$i]['TeamName'] ?></td>
		<td class="right" width="12%" style="border-right:0px; padding:2px;"><?php echo "<a href=\"remove.php?pos=sg&index=" . urlencode($i) . "&team=" . urlencode($team) . "&playerId=" . urlencode($_SESSION['sgs'][$i]['PlayerID']) . "\">Sell</a>"; ?></td>
		<td class="right" width="12%" style="border-right:0px"><?php echo $_SESSION['sgs'][$i]['PurchasePrice'] ?></td>
		<td class="right" width="12%"><?php echo $_SESSION['sgs'][$i]['Price'] ?></td>
		<td class="right" width="14%"><?php echo $_SESSION['sgs'][$i]['LastWeekScore'] ?></td>
		<td class="right" width="15%">Trigger?</td>
		</tr>

		<?php } ?>
		<tr>
		<td colspan="6" class="pos_row" style="border-right:0px"><font color="white">Power Forwards and Centers</font></td>
		</tr>
		<?php for ($i=0; $i<4; $i++) { ?>
		<tr>
		<td width="15%" style="border-left:0px" class="player_column"><?php echo $_SESSION['pfs'][$i]['LastName'] ?></td>
		<td width="15%"><?php echo $_SESSION['pfs'][$i]['TeamName'] ?></td>
		<td class="right" width="12%" style="border-right:0px; padding:2px;"><?php echo "<a href=\"remove.php?pos=pf&index=" . urlencode($i) . "&team=" . urlencode($team) . "&playerId=" . urlencode($_SESSION['pfs'][$i]['PlayerID']) . "\">Sell</a>"; ?></td>
		<td class="right" width="12%" style="border-right:0px"><?php echo $_SESSION['pfs'][$i]['PurchasePrice'] ?></td>
		<td class="right" width="12%"><?php echo $_SESSION['pfs'][$i]['Price'] ?></td>
		<td class="right" width="14%"><?php echo $_SESSION['pfs'][$i]['LastWeekScore'] ?></td>
		<td class="right" width="15%">Trigger?</td>
		</tr>
		<?php } ?>


	</table>
	</div>

	<div id="buyModal" class="modal">
		<div class="modal-content">
			<table id="buyModalTable"></table>
		</div>
	</div>
	
</body>

<script>
var modal = document.getElementById("buyModal");
var modalTable = document.getElementById("buyModalTable");
var cached = [false, false, false];
var players = ["", "", ""];

function displayModal(pos,index) {
	if (cached[pos]) {
		modal.style.display = "block";
		modalTable.innerHTML = players[pos];
	}
	else {
		modal.style.display = "block";
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				console.log(this.responseText);
				cached[pos] = true;
				players[pos] = this.responseText;
				modalTable.innerHTML = players[pos];

			}
		};
		xhttp.open("GET", "player_list.php?pos=pg", true);
		xhttp.send();
	}
}

window.onclick = function(event) {
	if (event.target == modal) {
		modal.style.display = "none";
	}
}
</script>

</html>

		
