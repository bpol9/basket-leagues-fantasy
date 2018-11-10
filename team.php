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

$sql = "SELECT TeamID,State,RemainingMoney FROM Team WHERE UserID=" . $_SESSION['userID'] . " AND TeamName='" . $team . "'";

$res1 = $conn->query($sql);
if ($res1->num_rows == 0) {
	die("Internal Server Error");
}

$row1 = $res1->fetch_assoc();
$teamId = $row1['TeamID'];
$state = $row1['State'];
$_SESSION['TeamID'] = $teamId;
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
		$team_members['pgs'][$pgi] = $row2;
		$team_members['pgs'][$pgi]['PlayerID'] = $playerId;
		$team_members['pgs'][$pgi++]['PurchasePrice'] = $row1['PurchasePrice'];
		break;
	case "SG":
	case "SF":
	case "SG/SF":
		$team_members['sgs'][$sgi] = $row2;
		$team_members['sgs'][$sgi]['PlayerID'] = $playerId;
		$team_members['sgs'][$sgi++]['PurchasePrice'] = $row1['PurchasePrice'];
		break;
	case "PF":
	case "C":
	case "PF/C":
		$team_members['pfs'][$pfi] = $row2;
		$team_members['pfs'][$pfi]['PlayerID'] = $playerId;
		$team_members['pfs'][$pfi++]['PurchasePrice'] = $row1['PurchasePrice'];
		break;
	}
	$row1 = $res1->fetch_assoc();
}
$conn->close();

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
	td a:hover {
		cursor:pointer;
	}
	td a:active {
		/*background-color: yellow;*/
		border: 1px dashed grey;
	}
	.buy-sell-column {
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
		<td><a id="save-changes-button" style="float:right" onclick="saveChanges()">Αποθήκευση</a></td>
		</tr>
		<tr>
		<td width="70px">Χρόνος:</td><td width="120px"><input size="10" type="text" name="time" value="01:45:11"></td>
		<td width="150px">Χρήματα:</td><td width="90px"><input size="6" type="text" name="money" value="90.04"></td>
		<td><a id="reset-changes-button" style="float:right;" onclick="resetChanges()">Αναίρεση</a></td>
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

		<?php for ($i=0; $i<2; $i++) { ?>
		<tr id="<?php echo "pg" . $i ?>">
		<td id="<?php echo $team_members['pgs'][$i]['PlayerID'] ?>" width="15%" style="border-left:0px" class="player_column"><?php echo $team_members['pgs'][$i]['LastName'] ?></td>
		<td width="15%"><?php echo $team_members['pgs'][$i]['TeamName'] ?></td>
		<td class="right" width="12%" style="border-right:0px; padding:2px"><?php echo "<a class=\"buy-sell-column\" href=\"#\" onclick=\"sellPlayer('pg" .$i . "'," . $team_members['pgs'][$i]['PlayerID'] . ")\">Sell</a>" ?></td>
		<td class="right" width="12%" style="border-right:0px"><?php echo $team_members['pgs'][$i]['PurchasePrice'] ?></td>
		<td class="right" width="12%"><?php echo $team_members['pgs'][$i]['Price'] ?></td>
		<td class="right" width="14%"><?php echo $team_members['pgs'][$i]['LastWeekScore'] ?></td>
		<td class="right" width="15%">Trigger?</td>
		</tr>

		<?php } ?>


		<tr>
		<td colspan="6" class="pos_row" style="border-right:0px"><font color="white">Shooting Guards and Small Forwards</font></td>
		</tr>

		<?php for ($i=0; $i<4; $i++) { ?>
		<tr id="<?php echo "sg" . $i ?>">
		<td id="<?php echo $team_members['sgs'][$i]['PlayerID'] ?>" width="15%" style="border-left:0px" class="player_column"><?php echo $team_members['sgs'][$i]['LastName'] ?></td>
		<td width="15%"><?php echo $team_members['sgs'][$i]['TeamName'] ?></td>
		<td class="right" width="12%" style="border-right:0px; padding:2px"><?php echo "<a class=\"buy-sell-column\" href=\"#\" onclick=\"sellPlayer('sg" . $i . "'," . $team_members['sgs'][$i]['PlayerID'] . ")\">Sell</a>" ?></td>
		<td class="right" width="12%" style="border-right:0px"><?php echo $team_members['sgs'][$i]['PurchasePrice'] ?></td>
		<td class="right" width="12%"><?php echo $team_members['sgs'][$i]['Price'] ?></td>
		<td class="right" width="14%"><?php echo $team_members['sgs'][$i]['LastWeekScore'] ?></td>
		<td class="right" width="15%">Trigger?</td>
		</tr>

		<?php } ?>


		<tr>
		<td colspan="6" class="pos_row" style="border-right:0px"><font color="white">Power Forwards and Centers</font></td>
		</tr>

		<?php for ($i=0; $i<4; $i++) { ?>
		<tr id="<?php echo "pf" . $i ?>">
		<td id="<?php echo $team_members['pfs'][$i]['PlayerID'] ?>" width="15%" style="border-left:0px" class="player_column"><?php echo $team_members['pfs'][$i]['LastName'] ?></td>
		<td width="15%"><?php echo $team_members['pfs'][$i]['TeamName'] ?></td>
		<td class="right" width="12%" style="border-right:0px; padding:2px"><?php echo "<a class=\"buy-sell-column\" href=\"#\" onclick=\"sellPlayer('pf" . $i . "'," . $team_members['pfs'][$i]['PlayerID'] . ")\">Sell</a>" ?></td>
		<td class="right" width="12%" style="border-right:0px"><?php echo $team_members['pfs'][$i]['PurchasePrice'] ?></td>
		<td class="right" width="12%"><?php echo $team_members['pfs'][$i]['Price'] ?></td>
		<td class="right" width="14%"><?php echo $team_members['pfs'][$i]['LastWeekScore'] ?></td>
		<td class="right" width="15%">Trigger?</td>
		</tr>
		<?php } ?>


	</table>
	</div>

	<div id="modal" class="modal">
		<div id = "modalContent" class="modal-content"></div>
	</div>
	
</body>

<script>
/*TODO remove sold[] and bought[] because the ids are contained also in sold_info[][0] and sold_info[][1] respectively*/
var empty_row1 = "<tr id=\"";
var empty_row2 = "\"><td width=\"15%\" style=\"border-left:0px\" class=\"player_column\">-</td><td width=\"15%\">-</td><td class=\"right\" width=\"12%\" style=\"border-right:0px; padding:2px\"><a class=\"button_link\" href=\"#\" onclick=\"displayModal(";
var empty_row3 = ")\">Buy</a></td><td class=\"right\" width=\"12%\" style=\"border-right:0px\">-</td><td class=\"right\" width=\"12%\">-</td><td class=\"right\" width=\"14%\">-</td><td class=\"right\" width=\"15%\">-</td></tr>";

var modal = document.getElementById("modal");
var modalContent = document.getElementById("modalContent");
var MAX_CHANGES = 3;
var BUTTON_INDEX = 2;
var cached = [false, false, false];
var players = ["", "", ""];
var bi = 0;
var si = 0;
var sold_info = create_2D_array(MAX_CHANGES);
var pos_sold_bought = create_2D_array(MAX_CHANGES); //pos_sold_bought[] = [<pos>,<sold_id>,<bought_id>]
var pending_id; //row id for which buy button has been pressed.
var saved; //indicates whether the team has been changed and saved.

<?php if ($state == 's') { ?>
	saved = true;
	disableTeamChanges();
<?php } else { ?>
	saved = false;
<?php } ?>

function create_2D_array(rows) {
	var arr = [];
	for (var i=0; i<rows; i++) {
		arr[i] = [];
	}
	return arr;
}

function makePositionTable(list, pos) {
	var ret = '<table id="modalBuyTable"><tr><th>Παίκτης</th><th>Μέσος Όρος</th><th>Επόμενος Αντίπαλος</th><th>Τιμή</th><th>-</th></tr>';
	var players = list.split("%");
	var player;
	for (var i=0; i<players.length; i++) {
		player = players[i].split("#");
		ret += "<tr>";
		for (var j=0; j<player.length; j++) {
			if (j == player.length-1) ret += '<td><a href="#" onclick="buyPlayer(' + player[j] + ')">Buy</a></td>';
			else ret += "<td>" + player[j] + "</td>";
		}
		ret += "</tr>";
	}
	ret += "</table>";
	return ret;
}

function displayModal(pos, player_id) {
	pending_id = pos;
	var pos_id;
	switch (pos.substring(0,2)) {
	case "pg":
		pos_id = 0;
		break;
	case "sg":
		pos_id = 1;
		break;
	case "pf":
		pos_id = 2;
		break;
	}

	if (cached[pos_id]) {
		modal.style.display = "block";
		modalContent.innerHTML = players[pos_id];
	}
	else {
		modal.style.display = "block";
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				console.log("player_list.php response for position id " + pos_id + ": " + this.responseText);
				players[pos_id] = makePositionTable(this.responseText, pos);
				cached[pos_id] = true;
				modalContent.innerHTML = players[pos_id];

			}
		};
		xhttp.open("GET", "player_list.php?pos=" + pos_id, true);
		xhttp.send();
	}
}

window.onclick = function(event) {
	if (event.target == modal) {
		modal.style.display = "none";
	}
}

function sellPlayer(pos, player_id) {
	var row;
	var columns;
	pos_sold_bought[si][0] = pos;
	pos_sold_bought[si][1] = player_id;

	console.log("[sellPlayer] pos argument: " + pos);
	row = document.getElementById(pos);

	sold_info[si][0] = pos;
	sold_info[si][1] = player_id;
	columns = row.children;
	for (var j=0; j<columns.length; j++) {
		sold_info[si][j+2] = columns[j].innerHTML;
	}
	++si;
	if (si == MAX_CHANGES) disableSellButtons();

	row.innerHTML = empty_row1 + pos + empty_row2 + "'" + pos + "'" + "," + player_id + empty_row3;
	return;
}

/*
function update_sold_info(old_id, new_id) {
	var done = false;
	var i = 0;
	while (!done && i < MAX_CHANGES) {
		if (sold_info[i][0] == old_id) {
			sold_info[i][1] = new_id;
			done = true;
		}
		else ++i;
	}
	if (i == MAX_CHANGES) {
		console.log("Sold id was not found in sold_info.");
		//TODO Reset changes + alert
	}
}
*/

function saveBuyId(id, pos) {
	var done = false;
	var i = 0;
	while (!done && (i < MAX_CHANGES)) {
		if (pos_sold_bought[i][0] == pos) { done = true; pos_sold_bought[i][2] = id; }
		else ++i;
	}
	if (i == MAX_CHANGES) {
		console.log("[updateBuyId] did not found pos " + pos + " in pos_sold_bought array");
		resetChanges();
		alert("Some error occured and changes are lost. Please try again.");
	}
}

function buyPlayer(id) {
	++bi;
	saveBuyId(id, pending_id);

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			var row = document.getElementById(pending_id);
			console.log("player_row.php response for player id " + id + ": " + this.responseText);
			//TODO Update the columns of the row locally.
			row.innerHTML = this.responseText;
			row.children[0].id = id;
			modal.style.display = "none";
		}
	};
	xhttp.open("GET", "player_row.php?playerid=" + id, true);
	xhttp.send();
}

function saveChanges() {
	if (si != bi) { //Put any other locally-detectable error condition here.
		alert("Some positions are empty!");
		//resetChanges();
	}
	else {
		modalContent.innerHTML = "<p>Saving your changes...</p>";
		modal.style.display = "block";
		
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				modal.style.display = "none";
				if (this.responseText != "OK") {
					resetChanges();
					alert("An error occured and changes were not saved!\nPlease try again.");
				}
				else { 
					disableTeamChanges();
					saved = true;
				}
			}
		};
		
		var sold_ids = "";
		var bought_ids = "";
		for (var i=1; i<=bi; i++) {
			sold_ids += (i==1)?("?sid1=" + pos_sold_bought[i-1][1]):("&sid" + i + "=" + pos_sold_bought[i-1][1]);
			bought_ids += "&bid" + i + "=" + pos_sold_bought[i-1][2];
		}
		var ids = sold_ids + bought_ids;
		console.log("[saveChanges] Ready to send ids list '" + ids + "' to save_changes.php");

		xhttp.open("GET", "save_changes.php" + ids, true);
		xhttp.send();
	}
}

function resetRow(row) {
	var values = row.split("#");
	var id = values[0]; //values[0] is the id of the player that was bought. The LastName column has this id.
	var row = document.getElementById(id).parentElement;
	var pos = row.id;
	var columns = row.children;

	for (var i=0; i<columns.length; i++) {
		if (i == BUTTON_INDEX) {
			columns[i].innerHTML = "<a class=\"buy-sell-column\" href=\"#\" onclick=\"sellPlayer('" + pos + "'," + values[i+1] + ")\">Sell</a>";
		}
		else {
			columns[i].innerHTML = values[i+1];
		}
	}
}

function localReset() {
	var row;
	var columns;
	for (var i=0; i<si; i++) {
		for (var j=0; j<9; j++) {
			console.log(sold_info[i][j] + ":");
		}
	}

	for (var i=0; i<si; i++) { //Handles automatically the case where si=0, where no changes have been made anyway.
		row = document.getElementById(sold_info[i][0]);
		columns = row.children;
		columns[0].id = sold_info[i][1];
		for (j=0; j<columns.length; j++) {
			columns[j].innerHTML = sold_info[i][j+2];
		}
	}
	si = 0;
	bi = 0;
}


function resetActions() {
	saved = false;
	si = bi = 0;
	for (var i=0; i<MAX_CHANGES; i++) pos_sold_bought[i][0] = "";
	enableTeamChanges();
}

function resetChanges() {
	if (saved) {
		modalContent.innerHTML = "<p>Reseting your changes...</p>";
		modal.style.display = "block";

		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				console.log("reset_team.php response: " + this.responseText);
				var rows = this.responseText.split("%");
				for (var i=0; i<rows.length; i++) {
					resetRow(rows[i]);
				}
				resetActions();
				modal.style.display = "none";
			}
		};
		xhttp.open("GET", "reset_team.php", true);
		xhttp.send();
	}
	else {
		localReset();
		resetActions();
	}
}
				 


function disableSellButtons() {
	var button_cols = document.getElementsByClassName("buy-sell-column");
	for (var i=0; i<button_cols.length; i++) {
		if (button_cols[i].innerHTML == "Sell") button_cols[i].style.display = "none";
	}
}

//TODO Make one parameterized function with "none"/"block" as argument.
function disableTeamChanges() {
	var button_cols = document.getElementsByClassName("buy-sell-column");
	for (var i=0; i<button_cols.length; i++) {
		button_cols[i].style.display = "none";
	}
	document.getElementById("save-changes-button").style.display = "none";
	//document.getElementById("reset-changes-button").style.display = "none";
}

function enableTeamChanges() {
	var button_cols = document.getElementsByClassName("buy-sell-column");
	for (var i=0; i<button_cols.length; i++) {
		button_cols[i].style.display = "inline-block";
	}
	document.getElementById("save-changes-button").style.display = "block";
	document.getElementById("reset-changes-button").style.display = "block";
}
	
</script>

</html>

		
