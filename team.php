<!DOCTYPE html>

<?php

require('team_funs.php');

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

$sql = "SELECT * FROM SITE_CONFIGURATION";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$SUSPENDED = $row['SUSPENDED'];
$SCORES_IGNORED = $row['SCORES_IGNORED'];
$LOCKED = $row['LOCKED'];
$LOCKTIME = $row['LOCKTIME'];

if ($SUSPENDED) {
	header("Location: site_suspended.php");
	exit(0);
}

$sql = "SELECT TeamID,State,LastScore,RemainingMoney,Sell_Buy_IDs FROM Team WHERE UserID=" . $_SESSION['userID'] . " AND TeamName='" . $team . "'";
$res1 = $conn->query($sql);
if ($res1->num_rows == 0) {
	die("Internal Server Error");
}

$row1 = $res1->fetch_assoc();
$teamId = $row1['TeamID'];
$state = $row1['State'];
$sell_buy_ids = $row1['Sell_Buy_IDs'];
$team_last_score = $row1['LastScore'];
$remaining_money = floatval($row1['RemainingMoney']);
$_SESSION['TeamID'] = $teamId; //TODO Fix this. TeamID should be in url of get method.

if (!($sell_buy_ids === NULL)) {
	$check_changes = TRUE;
	$trans = explode("%", $sell_buy_ids); //Sell_Buy_IDs is something like <id>#<id>%<id>#<id>%<id>#<id>
	foreach ($trans as $tr) {
		$duo = explode("#", $tr);
		$duos[0][] = $duo[0];
		$duos[1][] = $duo[1];
	}	
	unset($tr);
}
else $check_changes = FALSE;

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

$sql = "SELECT PlayerID,PurchasePrice FROM TeamPlayer WHERE TeamID=" . $teamId;
$res1 = $conn->query($sql);
$pgi = $sgi = $pfi = 0;
$row1 = $res1->fetch_assoc();
while ($row1 != NULL) {
	$playerId = $row1['PlayerID'];
	if ($check_changes) {
		$key = array_search($playerId, $duos[0]);
		if (!($key === FALSE)) $playerId = $duos[1][$key];
	}
	$player_ids[] = $playerId;
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

if (!$LOCKED) $team_score = $team_last_score;
else if ($SCORES_IGNORED) $team_score = 0.0;
else $team_score = calc_score($team_members);

$sql = "SELECT * FROM TOP5";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
if ($row['Score'] === NULL) {
	$noTOP5 = TRUE;
}
else {
	$noTOP5 = FALSE;
}
$i = 0;
while ($row != NULL) {
	if ($noTOP5) {
		$top5[$i]['Score'] = '-';
		$top5[$i]['Name'] = '-';
	}
	else {
		$top5[$i]['Score'] = $row['Score'];
		$top5[$i]['Name'] = substr($row['FirstName'],0,1) . ". " . $row['LastName'];

	}
	$top5[$i]['Ranking'] = $row['Ranking'];
	$i++;
	$row = $res->fetch_assoc();
}

$sql = "SELECT * FROM MostPopular ORDER BY Score DESC";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$i=0;
while ($row != NULL) {
	if ($row['Score'] > 0) {
		$most_popular[$i]['Name'] = substr($row['FirstName'],0,1) . ". " . $row['LastName'];
		$most_popular[$i]['Score'] = $row['Score'];
	}
	else {
		$most_popular[$i]['Name'] = '-';
		$most_popular[$i]['Score'] = '-';
	}
	$i++;
	$row = $res->fetch_assoc();
}

$sql = "SELECT * FROM LessPopular ORDER BY Score ASC";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$i=0;
while ($row != NULL) {
	if ($row['Score'] < 0) {
		$less_popular[$i]['Name'] = substr($row['FirstName'],0,1) . ". " . $row['LastName'];
		$less_popular[$i]['Score'] = $row['Score'];
	}
	else {
		$less_popular[$i]['Name'] = '-';
		$less_popular[$i]['Score'] = '-';
	}
	$i++;
	$row = $res->fetch_assoc();
}

$stats = get_stat_leaders();

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
		border: 1px solid #f1f1f1;
		border-radius: .4em;
		margin-top: 10px;
	}

	li {
		float: left;
		border-right: 1px solid #555;
	}

	li a {
		display: block;
		color: #38b0de;
		font-weight: bold;
		font-size: 15px;
		text-align: center;
		padding: 10px 12px;
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
		/*border-bottom: 1px solid white;
		border-collapse: collapse;*/
		color: #585858;
		font-weight: bold;
		font-size: 12px;
		padding:4px;
		padding-left: 12px;
	}

	table#teamTable {
		margin-top: 7px;
		border: 2px solid royalblue;
		border-radius: .4em;
		border-spacing: 0px;
		/*border-collapse: collapse;*/
		background-color: white;
	}

	table#teamTable td:not(.player_column) {
		text-align: center;
		/*border-bottom: 1px solid white;
		border-collapse: collapse;*/
		color: #585858;
		font-weight: bold;
		font-size: 12px;
		padding: 4px;
	}

	table#headTable {
		/*background-color: #8db6cd;*/
		background-color: royalblue;
		border: 2px solid royalblue;
		border-radius: .4em;
		border-spacing: 0px;
		/*border-collapse: collapse;*/
		margin-top: 40px;
	}

	table#headTable td {
		font-size:14px;
		/*border-bottom: 1px solid white;*/
		/*border-collapse: collapse;*/
		color:white;
		font-weight: bold;
	}

	#teamTable tr:nth-child(even) { background: aliceblue; color: #232b2b; }
	#teamTable tr:nth-child(odd)  { background: #FFF; color: #232b2b; }
	
	#top_players {
		margin-top: 30px;
		border-spacing: 0px;
		border-radius: .4em;
		border: 2px solid royalblue;
		font-weight:bold;
		font-size: 12px;
		width: 100%;
		background-color: white;
	}
	#top_players td {
		padding: 4px;
		/*color: #232b2b;*/
		color: #585858;
		font-weight: bold;
	}


	#top_players tr:nth-child(even) { background: #FFF; color: #585858; }
	#top_players tr:nth-child(odd)  { background: aliceblue; color: #585858; }
	
	#popular_players {
		/*text-align:center;*/
		border-spacing: 0px;
		border-radius: .4em;
		border: 2px solid royalblue;
		font-weight:bold;
		font-size: 12px;
		width: 100%;
		background-color: white;
	}
	#popular_players td {
		padding: 4px;
		/*color: #232b2b;*/
		color: #585858;
		font-weight: bold;
	}


	#popular_players tr:nth-child(even) { background: #FFF; color: #585858; }
	#popular_players tr:nth-child(odd)  { background: aliceblue; color: #585858; }

	#hoops {
		width: 85px;
		height: 85px;
		/*position: absolute;
		left:50%;
		margin-left: -50px;*/
		border: 2px solid #8db6cd;
		border-radius: .4em;
	}

	#bottom {
		line-height: 0px;
		text-align: center;
		position: fixed;
		bottom: 50px;
		width: 100%;
	}

	#bs_fantasy {
		color:white;
		font-size:25px;
		font-weight:bold;
	}

	.right {
		float:right;
	}
	.pos_row {
		/*background-color: #73b1b7;
		background-color: #8db6cd;*/
		background-color: royalblue;
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
		text-align:center;
		background-color: #fefefe;
		margin: auto;
		padding: 20px;
		border: 2px solid royalblue;
		border-radius: .4em;
		width: 45%;
		height: 60%;
	}
	td a {
		text-decoration: none;
		background-color: orange;
		border: none;
		color:white;
		padding: 3px 3px;
		text-align:center;
		display: inline-block;
		cursor: pointer;
		font-size: 12px;
		font-weight: bold;
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
	td input {
		color: #585858;
		font-weight:bold;
	}
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
	.buy-table {
		border: 2px solid #8db6cd;
		border-radius: .4em;
		border-spacing: 0px;
		margin: 0 auto;
		/*border-collapse: collapse;
		background-color: white;*/
	}

	.buy-table tr:nth-child(even) { background: #e6f3f7; color: #232b2b; }
	.buy-table tr:nth-child(odd)  { background: #FFF; color: #232b2b; }

	.buy-table td {
		text-align:center;
		padding: 4px;
		color: #585858;
		font-weight: bold;
		font-size: 12px;
	}
	.header-row td {
		background-color: royalblue;
		color: white;
		font-weight: bold;
		font-size: 13px;
	}
	.stats-leaders-container {
		width: 80%;
		position:fixed;
		bottom: 10px;
		left: 50%;
		transform:translate(-50%);
		text-align:center;
		background-color:white;
		border:1px solid #8db6cd;
		border-radius: .4em;
		padding:10px;
	}
	.stats-category-leaders {
		display:inline-block;
		padding:0px 10px;
	}
	.stats-leaders-list {
		border:1px solid #8db6cd;
		border-radius:.4em;
		border-spacing:0px;
	}
	.stats-leaders-list td {
		color: #232b2b;
		font-weight: bold;
		font-size: 12px;
		padding:4px;
	}
	.stats-leaders-list tr:nth-child(even) { background: aliceblue; color: #232b2b; }
	.stats-leaders-list tr:nth-child(odd)  { background: #fff; color: #232b2b; }
	.green_button {
		background-color: #4CAF50;
		border: none;	
		color: white;
		text-align: center;
		text-decoration: none;
		display: inline-block;
		font-size: 12px;
		/*margin: 4px 2px;*/
		margin-left: 10px;
		cursor: pointer;
	}
		
		
</style>
</head>
<body>
	<div class="top-banner">
		<p>Basket League Fantasy 2018-19</p>
		<img src="./images/hoops2.jpg">
	</div>
	<ul>
		<li><a class="active" href="./teams.php">Ομάδες</a></li>
		<li><a href="./leagues.php">Λίγκες</a></li>
		<li><a href="./guides.php">Οδηγίες</a></li>
		<li style="float:right"><a href="./logout.php">Αποσύνδεση</a></li>
	</ul>
	
	<div>
	<div style="display:inline-block; width:60%">
	<table id="headTable" width="100%">
		<tr>
		<td width="70px"><b>Ομάδα:</b></td><td width="120px"><input size="10" type="text" name="team" value="<?php echo $team ?>"></td>
		<td width="150px">Σκορ εβδομάδας:</td>
		<td width="90px"><input size="6" type="text" name="week_score" value="<?php echo number_format($team_score,1,'.','') ?>"></td>
		<td style="float:right; width:70%; text-align:right; margin-top:3px;"><?php if (!$LOCKED) { ?><a class="green_button" id="save-changes-button" style="width:55%; padding:5px 0px;" onclick="saveChanges()">Αποθήκευση</a>
		<?php } ?></td>
		</tr>
		<tr>
		<td width="70px">Χρόνος:</td><td width="120px"><input id="countdown" size="10" type="text" name="time" value=""></td>
		<td width="150px">Χρήματα:</td>
		<td width="90px"><input id="money" size="6" type="text" name="money" value="<?php echo number_format($remaining_money,2,'.','') ?>"></td>
		<td style="float:right; width:70%; text-align:right; margin-top:3px;"><?php if (!$LOCKED) { ?><a class="green_button" id="reset-changes-button" style="width:55%; padding:5px 0px;" onclick="resetChanges()">Αναίρεση</a>
		<?php } ?></td>
		</tr>
	</table>
	<table id="teamTable" width="100%">
		<tr style="background: #e6f3f7">
		<td class="player_column" width="15%" style="border-left:0px">Παίκτης</td>
		<td width="15%">Ομάδα</td></td>
		<td class="right" width="12%" style="border-right:0px">Πώληση Αγορά</td>
		<td class="right" width="12%" style="border-right:0px">Τιμή αγοράς</td>
		<td class="right" width="12%">Τωρινή τιμή</td>
		<td class="right" width="14%">Πρόσφατο σκορ</td>
		<td class="right" width="15%">Επόμενος αντίπαλος</td>
		</tr>

		<tr>
		<td colspan="6" class="pos_row" style="border-right:0px; font-size:13px"><font color="white">Point Guards</font></td>
		</tr>

		<?php for ($i=0; $i<2; $i++) { ?>
		<tr id="<?php echo "pg" . $i ?>">
		<td id="<?php echo $team_members['pgs'][$i]['PlayerID'] ?>" name="name" width="15%" style="border-left:0px" class="player_column"><?php echo $team_members['pgs'][$i]['LastName'] ?></td>
		<td name="team" width="15%"><?php echo $team_members['pgs'][$i]['TeamName'] ?></td>
		<td name="button" class="right" width="12%" style="border-right:0px; padding:2px">
		<?php if (!$LOCKED) { ?>
		<a class="buy-sell-column" href="#" onclick="<?php echo "sellPlayer('pg" . $i . "'," . $team_members['pgs'][$i]['PlayerID'] . ")"?>">Sell</a>
		<?php } ?>
		</td>
		<td name="purchasePrice" class="right" width="12%" style="border-right:0px"><?php echo $team_members['pgs'][$i]['PurchasePrice'] ?></td>
		<td name="price" class="right" width="12%"><?php echo $team_members['pgs'][$i]['Price'] ?></td>
		<td name="lastScore" class="right" width="14%"><?php echo ($SCORES_IGNORED)?'-':($team_members['pgs'][$i]['LastWeekScore']) ?></td>
		<td name="nextOp" class="right" width="15%"><?php echo $opponents[$team_members['pgs'][$i]['TeamName']] ?></td>
		</tr>

		<?php } ?>


		<tr>
		<td colspan="6" class="pos_row" style="border-right:0px; font-size:13px"><font color="white">Shooting Guards and Small Forwards</font></td>
		</tr>

		<?php for ($i=0; $i<4; $i++) { ?>
		<tr id="<?php echo "sg" . $i ?>">
		<td id="<?php echo $team_members['sgs'][$i]['PlayerID'] ?>" name="name" width="15%" style="border-left:0px" class="player_column"><?php echo $team_members['sgs'][$i]['LastName'] ?></td>
		<td name="team" width="15%"><?php echo $team_members['sgs'][$i]['TeamName'] ?></td>
		<td naem="button" class="right" width="12%" style="border-right:0px; padding:2px">
		<?php if (!$LOCKED) { ?>
		<a class="buy-sell-column" href="#" onclick="<?php echo "sellPlayer('sg" . $i . "'," . $team_members['sgs'][$i]['PlayerID'] . ")"?>">Sell</a>
		<?php } ?>
		</td>
		<td name="purchasePrice" class="right" width="12%" style="border-right:0px"><?php echo $team_members['sgs'][$i]['PurchasePrice'] ?></td>
		<td name="price" class="right" width="12%"><?php echo $team_members['sgs'][$i]['Price'] ?></td>
		<td name="lastScore" class="right" width="14%"><?php echo ($SCORES_IGNORED)?'-':($team_members['sgs'][$i]['LastWeekScore']) ?></td>
		<td name="nextOp" class="right" width="15%"><?php echo $opponents[$team_members['sgs'][$i]['TeamName']] ?></td>
		</tr>

		<?php } ?>


		<tr>
		<td colspan="6" class="pos_row" style="border-right:0px; font-size:13px"><font color="white">Power Forwards and Centers</font></td>
		</tr>

		<?php for ($i=0; $i<4; $i++) { ?>
		<tr id="<?php echo "pf" . $i ?>">
		<td id="<?php echo $team_members['pfs'][$i]['PlayerID'] ?>" name="name" width="15%" style="border-left:0px" class="player_column"><?php echo $team_members['pfs'][$i]['LastName'] ?></td>
		<td name="team" width="15%"><?php echo $team_members['pfs'][$i]['TeamName'] ?></td>
		<td name="button" class="right" width="12%" style="border-right:0px; padding:2px">
		<?php if (!$LOCKED) { ?>
		<a class="buy-sell-column" href="#" onclick="<?php echo "sellPlayer('pf" . $i . "'," . $team_members['pfs'][$i]['PlayerID'] . ")"?>">Sell</a>
		<?php } ?>
		</td>
		<td name="purchasePrice" class="right" width="12%" style="border-right:0px"><?php echo $team_members['pfs'][$i]['PurchasePrice'] ?></td>
		<td name="price" class="right" width="12%"><?php echo $team_members['pfs'][$i]['Price'] ?></td>
		<td name="lastScore" class="right" width="14%"><?php echo ($SCORES_IGNORED)?'-':($team_members['pfs'][$i]['LastWeekScore']) ?></td>
		<td name="nextOp" class="right" width="15%"><?php echo $opponents[$team_members['pfs'][$i]['TeamName']] ?></td>
		</tr>
		<?php } ?>


	</table>
	</div>
	<div style="float:right; display:inline-block; width:30%; margin-top:40px;">
	<div id="popularity_tables">
	<table id="popular_players">
	<tr style="background-color: royalblue; text-align:center;">
		<td colspan="3" style="color:white">Οι πιο δημοφιλείς της τρέχουσας εβδομάδας</td>
	</tr>
	<?php $i=1; foreach($most_popular as $pop) { ?>
	<tr>
		<td style="width:5%"><?php echo $i++ . "." ?></td>
		<td style="width:40%;"><?php echo $pop['Name'] ?></td>
		<td style="float:right; margin-right:15px; <?php echo ($pop['Score']>0)?'':'text-align:center' ?>"><?php echo $pop['Score'] ?></td>
	</tr>
	<?php } ?>
	</table>
	<table id="popular_players" style="margin-top:25px;">
	<tr style="background-color: royalblue; text-align:center;">
		<td colspan="3" style="color:white">Οι λιγότερο δημοφιλείς της τρέχουσας εβδομάδας</td>
	</tr>
	<?php $i=1; foreach($less_popular as $not_pop) { ?>
	<tr>
		<td style="width:5%"><?php echo $i++ . "." ?></td>
		<td style="width:40%;"><?php echo $not_pop['Name'] ?></td>
		<td style="float:right; margin-right:15px; <?php echo ($not_pop['Score']>0)?'':'text-align:center' ?>"><?php echo $not_pop['Score'] ?></td>
	</tr>
	<?php } ?>
	</table>
	</div>
	<table id="top_players">
	<tr style="background-color: royalblue; text-align:center;">
		<td colspan="3" style="color:white">Οι καλύτεροι της αγωνιστικής σε &nbsp;<span style="padding:2px 2px; background-color:white; color:green"></span>
		<a style="border-radius:.7em; margin-left:3px; background-color:royalblue; color:white;"><b>&rarr;</b></a>
		</td>
	</tr>
	<?php for ($i=0; $i<5; $i++) { ?>
	<tr>
		<td style="width:5%"><?php echo ($i+1) . "." ?></td>
		<td style="width:40%;">-</td>
		<td style="float:right; margin-right:15px;">-</td>
	</tr>
	<?php } ?>
	</table>
	</div>
	</div>

<?php /*
	<div class="stats-leaders-container">
		<?php for ($i=0; $i<5; $i++) { ?>
		<div class="stats-category-leaders">
		<table class="stats-leaders-list">
			<tr style="background-color: #8db6cd; text-align:center;"><td style="padding:6px;" colspan=2>Category title</td></tr>
			<?php for ($j=0; $j<5; $j++) { ?>
			<tr><td style="width:130px; text-align:left">Z. LeDay</td><td style="float:right">25.3</td></tr>
			<?php } ?>
		</table>
		</div>
		<?php } ?>
	</div>
*/ ?>


	<div id="modal" class="modal">
		<div id = "modalContent" class="modal-content">
			<div id="pg_buy_table" style="display:none;"></div>
			<div id="sg_buy_table" style="display:none"></div>
			<div id="pf_buy_table" style="display:none"></div>
			<div id="save_msg" style="display:none"><p>Saving your changes...</p></div>
			<div id="reset_msg" style="display:none"><p>Reseting your team...</p></div>
		</div>
	</div>
	
</body>

<script>
/*TODO remove sold[] and bought[] because the ids are contained also in sold_info[][0] and sold_info[][1] respectively*/
var empty_row1 = "<tr id=\"";
var empty_row2 = "\"><td name=\"name\" width=\"15%\" style=\"border-left:0px\" class=\"player_column\">-</td><td name=\"team\" width=\"15%\">-</td><td name=\"button\" class=\"right\" width=\"12%\" style=\"border-right:0px; padding:2px\"><a class=\"button_link\" href=\"#\" onclick=\"displayModal(";
var empty_row3 = ")\">Buy</a></td><td name=\"purchasePrice\" class=\"right\" width=\"12%\" style=\"border-right:0px\">-</td><td name=\"price\" class=\"right\" width=\"12%\">-</td><td name=\"lastScore\" class=\"right\" width=\"14%\">-</td><td name=\"nextOp\" class=\"right\" width=\"15%\">-</td></tr>";

var modal = document.getElementById("modal");
var modalContent = document.getElementById("modalContent");
var moneyField = document.getElementById("money");
var MAX_CHANGES = 3;
var BUTTON_INDEX = 2;
var PLAYER_LIST_PRICE_INDEX = 3;
var TEAM_PLAYER_ROW_PRICE_INDEX = 4;
var cached = [false, false, false];
//var players = ["", "", ""];
var bi = 0;
var si = 0;
var sold_info = create_2D_array(MAX_CHANGES);
var pos_sold_bought = create_2D_array(MAX_CHANGES); //pos_sold_bought[i] = [<pos>,<sold_id>,<bought_id>]
var pending_id; //row id for which buy button has been pressed.
var saved; //indicates whether the team has been changed and saved.
var remainingMoney = <?php echo json_encode($remaining_money) ?>;
var remainingMoneyBackup = remainingMoney;
var indexes = {
  pg: 1,
  sg: 1,
  pf: 1
}; //These are indexes for the buy tables. They index the row of the most expensive affordable player.

for (var i=0; i<MAX_CHANGES; i++) { pos_sold_bought[i][0] = ""; pos_sold_bought[i][1] = -1; pos_sold_bought[i][2] = -1; }

<?php if ($state == 's') { ?>
	saved = true;
	disableTeamChanges();
<?php } else { ?>
	saved = false;
<?php } ?>

<?php
$js_array = json_encode($player_ids);
echo "var player_ids = " . $js_array . ";\n";
?>

function create_2D_array(rows) {
	var arr = [];
	for (var i=0; i<rows; i++) {
		arr[i] = [];
	}
	return arr;
}

function quickSort(prices) {

	function swap(i,j) {
		var tmp1 = prices[0][i];
		var tmp2 = prices[1][i];
		prices[0][i] = prices[0][j];
		prices[1][i] = prices[1][j];
		prices[0][j] = tmp1;
		prices[1][j] = tmp2;
	}

	function partition(left, right) {
		if (left < right) {
			var pivot = prices[0][left + Math.floor((right - left) / 2)],
				left_new = left,
				right_new = right;

			do {
				while (prices[0][left_new] > pivot) ++left_new;
				while (prices[0][right_new] < pivot) --right_new;
				if (left_new <= right_new) {
					swap(left_new, right_new);
					++left_new;
					--right_new;
				}
			} while (left_new <= right_new);

			partition(left, right_new);
			partition(left_new, right);
		}
	}

	partition(0, prices[0].length-1);

	//for (var i=0; i<prices[0].length; i++) console.log("Price: " + prices[0][i] + ", Index: " + prices[1][i]);
}

function makePositionTable(list, pos) {
	var ret = '<table class="buy-table"><tr class="header-row">' +
		'<td>Παίκτης</td><td>Ομάδα</td><td>Αντίπαλος</td>' + 
		'<td>Μέσος Όρος</td><td>Τιμή</td><td></td></tr>';
	var players = list.split("%");
	var player, id;
	var col_names = ['name', 'team', 'nextOp', 'meanScore', 'price', 'button']; //the order in which the names are stored in the array is important!
	var widthes = ['20%', '20%', '20%', '20%', '7%', '10%'];
	var prices = create_2D_array(2);
	var price_i = col_names.indexOf('price'), btn_i = col_names.indexOf('button');
	for (var i=0; i<players.length; i++) {
		player = players[i].split("#");
		prices[0][i] = player[price_i];
		prices[1][i] = i;
	}
	quickSort(prices);
	for (var i=0; i<players.length; i++) {
		player = players[prices[1][i]].split("#");
		//player = players[i].split("#");
		id = player[btn_i];
		ret += '<tr id="modal_' + id + (player_ids.includes(id)?'" style="display:none">':'">');
		for (var j=0; j<player.length; j++) {
			if (j == btn_i) {
				ret += '<td name="button" width="' + widthes[j] +
					'"><a href="#" onclick="buyPlayer(' + player[j] + ',' + player[price_i] + ')">Buy</a></td>';
			}
			else {
				ret += '<td name="' + col_names[j] + '" width="' + widthes[j] + '">' + player[j] + '</td>';
			}
		}
		ret += "</tr>";
	}
	ret += "</table>";
	return ret;
}

function getLeafValue(elem) {
	if (elem == null) return null;
	if (elem.children.length > 0) return getLeafValue(elem.children[0]);
	else return elem.innerHTML;
}

function display_already_sold(pos) {
	var elem;
	for (var i=0; i<si; i++) {
		if (sold_info[i][0].substring(0,2) == pos) {
			elem = document.getElementById("modal_"+sold_info[i][1]);
			if (elem != null) elem.style.display = "";
		}
	}
}

function moveDown(pos, rows) {
	var l = rows.length, i = indexes[pos], done = false;
	while ((i < l) && (!done)) {
		if (parseFloat(getLeafValue(rows[i].querySelector('td[name="price"]'))) > remainingMoney) {
			rows[i].querySelector('td[name="button"]').style.display = 'none';
			++i;
		} else {
			done = true;
			indexes[pos] = i;
		}
	}
	if (!done) {
		indexes[pos] = l;//In that, all players are not affordable
	}
}

function moveUp(pos, rows) {
	var i = indexes[pos], done = false;
	if (i == 1) {
		return;
	}
	--i;
	while ((i > 0) && (!done)) {
		if (parseFloat(getLeafValue(rows[i].querySelector('td[name="price"]'))) <= remainingMoney) {
			rows[i].querySelector('td[name="button"]').style.display = '';
			--i;
		} else {
			done = true;
			indexes[pos] = i + 1;
		}
	}
	if (!done) { //i==0
		indexes[pos] = 1;
	}
}

function displayCachedTable(pos) {
	var tbody = document.getElementById(pos + '_buy_table').querySelector('tbody');
	var rows = tbody.children;
	var l = rows.length, i = indexes[pos], price;
	console.log("[displayCachedTable] rows.length: " + l + ", i: " + i);
	if (i == l) {
		moveUp(pos, rows);
	}
	else {
		price = parseFloat(getLeafValue(rows[i].querySelector('td[name="price"]')));
		console.log("[dispalyCachedTable] price: " + price + ", money: " + remainingMoney);
		if (price > remainingMoney) {
			moveDown(pos, rows);
		}
		else {
			moveUp(pos, rows);
		}
	}
	document.getElementById(pos+"_buy_table").style.display = "block";
}

function displayModal(pos, player_id) {
	pending_id = pos;
	var pos_id;
	var p = pos.substring(0,2); //pg, sg or pf
	switch (p) {
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
		displayCachedTable(p);
	}
	else {
		modal.style.display = "block";
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				console.log("player_list.php response for position id " + pos_id + ": " + this.responseText);
				var table = document.getElementById(p+"_buy_table");
				table.innerHTML = makePositionTable(this.responseText, pos);
				cached[pos_id] = true;
				display_already_sold(p); //Show players of this position that have been sold before the table is appeared for the first time.
				displayCachedTable(p);
			}
		};
		xhttp.open("GET", "player_list.php?pos=" + pos_id, true);
		xhttp.send();
	}
}

window.onclick = function(event) {
	if (event.target == modal) {
		var p = pending_id.substring(0,2);
		var table = document.getElementById(p+"_buy_table");
		if (table != null) table.style.display = "none";
		modal.style.display = "none";
	}
}

function sellPlayer(pos, player_id) {
	var row;
	var columns;
	var elem;
	var price;

	pos_sold_bought[si][0] = pos;
	pos_sold_bought[si][1] = player_id;

	console.log("[sellPlayer] pos argument: " + pos);
	row = document.getElementById(pos);

	sold_info[si][0] = pos;
	sold_info[si][1] = player_id;
	columns = row.children;
	for (var j=0; j<columns.length; j++) {
		sold_info[si][j+2] = columns[j].innerHTML;
		if (j == TEAM_PLAYER_ROW_PRICE_INDEX) {
			price = parseFloat(columns[j].innerHTML);
			remainingMoney += price;
			moneyField.value = remainingMoney.toFixed(2);
		}
	}

	++si;
	if (si == MAX_CHANGES) disableSellButtons();
	row.innerHTML = empty_row1 + pos + empty_row2 + "'" + pos + "'" + "," + player_id + empty_row3;
	elem = document.getElementById("modal_"+player_id);
	if (elem != null) elem.style.display = "";

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
		console.log("[saveBuyId] did not found pos " + pos + " in pos_sold_bought array");
		resetChanges();
		alert("Some error occured and changes are lost. Please try again.");
	}
}

function buyPlayer(id, price) {
	var p = pending_id.substring(0,2);
	var table = document.getElementById(p+"_buy_table");
	var playerRow = document.getElementById('modal_' + id);
	var colNames = ['name', 'team', 'nextOp', 'meanScore', 'price'];
	var player = {};
	var teamRow = document.getElementById(pending_id);
	var columns = teamRow.children;
	var name;

	++bi;
	saveBuyId(id, pending_id);
	for (var i=0; i<colNames.length; i++) {
		player[colNames[i]] = getLeafValue(playerRow.querySelector('td[name="' + colNames[i] + '"]'));
		console.log(player[colNames[i]]);
	}
	player['lastScore'] = '-';
	player['button'] = '';
	player['purchasePrice'] = player['price'];
	for (var i=0; i<columns.length; i++) {
		name = columns[i].getAttribute('name');
		if (!player.hasOwnProperty(name)) {
			console.log("[buyPlayer] Player object doesn't have property '" + name + "'");
		} else {
			columns[i].innerText = player[name];
		}
	}
	columns[0].id = id;
	remainingMoney -= price;
	moneyField.value = remainingMoney.toFixed(2);
	table.style.display = "none";
	modal.style.display = "none";
	playerRow.style.display = "none";

	/*
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			var row = document.getElementById(pending_id);
			var table = document.getElementById(p+"_buy_table");
			console.log("player_row.php response for player id " + id + ": " + this.responseText);
			//TODO Update the columns of the row locally.
			row.innerHTML = this.responseText;
			row.children[0].id = id;
			remainingMoney -= price;
			moneyField.value = remainingMoney.toFixed(2);
			table.style.display = "none";
			modal.style.display = "none";
			document.getElementById("modal_" + id).style.display = "none";
		}
	};
	xhttp.open("GET", "player_row.php?playerid=" + id, true);
	xhttp.send();
	 */
}

function updatePlayerIDsArray() {
	var i,j;
	for (i=0; i<si; i++) {
		j = player_ids.indexOf(pos_sold_bought[i][1].toString());
		player_ids[j] = pos_sold_bought[i][2].toString();
		console.log("[updatePlayerIDsArray] j: " + j + " sold id: " + pos_sold_bought[i][1] + " bought id: " + pos_sold_bought[i][2]);
	}
}

function saveChanges() {
	if (si != bi) { //Put any other locally-detectable error condition here.
		alert("Some positions are empty!");
		//resetChanges();
	}
	else {
		//modalContent.innerHTML = "<p>Saving your changes...</p>";
		modal.style.display = "block";
		document.getElementById("save_msg").style.display = "block";
		
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				modal.style.display = "none";
				document.getElementById("save_msg").style.display = "none";
				if (this.responseText != "OK") {
					resetChanges();
					alert("An error occured and changes were not saved!\nPlease try again.");
				}
				else {
					updatePlayerIDsArray();
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
		var url_args = sold_ids + bought_ids;
		url_args += "&remMoney=" + remainingMoney.toFixed(2);
		console.log("[saveChanges] Ready to send save url arguments '" + url_args + "' to save_changes.php");

		xhttp.open("GET", "save_changes.php" + url_args, true);
		xhttp.send();
	}
}

function resetRow(row) {
	var values = row.split("#");
	var id = values[0]; //values[0] is the id of the player that was bought. The LastName column has this id.
	var row = document.getElementById(id).parentElement;
	var pos = row.id;
	var columns = row.children;
	var j = player_ids.indexOf(id);
	console.log("[resetRow] player_ids index: " + j);

	for (var i=0; i<columns.length; i++) {
		if (i == BUTTON_INDEX) {
			columns[i].innerHTML = "<a class=\"buy-sell-column\" href=\"#\" onclick=\"sellPlayer('" + pos + "'," + values[i+1] + ")\">Sell</a>";
			player_ids[j] = values[i+1]; //restore the sold player's id(who is brought back) to the player_ids array.
		}
		else {
			columns[i].innerHTML = values[i+1];
		}
	}
}

function localReset() {
	var row, rows, columns, j, tbody;
	var columns;
	var pos = ['pg', 'sg', 'pf'];
	/*
	for (var i=0; i<si; i++) {
		for (var j=0; j<9; j++) {
			console.log(sold_info[i][j] + ":");
		}
	}
	*/

	for (var i=0; i<si; i++) { //Handles automatically the case where si=0, where no changes have been made anyway.
		row = document.getElementById(sold_info[i][0]);
		columns = row.children;
		columns[0].id = sold_info[i][1];
		for (j=0; j<columns.length; j++) {
			columns[j].innerHTML = sold_info[i][j+2];
		}
	}

	for (var i=0; i<pos.length; i++) {
		tbody = document.getElementById(pos[i] + '_buy_table').querySelector('tbody');
		if (tbody != null) {
			rows = tbody.children;
			j = indexes[pos[i]] - 1;
			console.log('pos: ' + pos[i] + ', j: ' + j);
			if (j >=1 ) {
				for (var k=j; k>0; k--) {
					console.log('k: ' + k);
					rows[k].querySelector('td[name="button"]').style.display = '';
				}
			}
		}
	}

	remainingMoney = remainingMoneyBackup;
	moneyField.value = remainingMoney.toFixed(2);
	indexes['pg'] = indexes['sg'] = indexes['pf'] = 1;
	si = 0;
	bi = 0;
}


function resetActions() {
	saved = false;
	si = bi = 0;
	for (var i=0; i<MAX_CHANGES; i++) {
		pos_sold_bought[i][0] = "";
		pos_sold_bought[i][1] = -1;
		pos_sold_bought[i][2] = -1;
	}
	enableTeamChanges();
}

function resetChanges() {
	var elem;
	for (var i=0; i<si; i++) {
		if (pos_sold_bought[i][2] != -1) {
			elem = document.getElementById("modal_" + pos_sold_bought[i][2]);
			if (elem != null) elem.style.display = "";
		}
	}
	for (var i=0; i<si; i++) {
		elem = document.getElementById("modal_" + pos_sold_bought[i][1]);
		if (elem != null) elem.style.display = "none";
	}


	if (saved) {
		//modalContent.innerHTML = "<p>Reseting your changes...</p>";
		modal.style.display = "block";
		document.getElementById("reset_msg").style.display = "block";

		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				console.log("reset_team.php response: " + this.responseText);
				var duo = this.responseText.split("$");
				remainingMoneyBackup = remainingMoney = parseFloat(duo[1]);
				moneyField.value = remainingMoney.toFixed(2);
				var rows = duo[0].split("%");
				for (var i=0; i<rows.length; i++) {
					resetRow(rows[i]);
				}
				resetActions();
				modal.style.display = "none";
				document.getElementById("reset_msg").style.display = "none";
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
function disableTeamChanges(resetDisable=false) {
	var button_cols = document.getElementsByClassName("buy-sell-column");
	for (var i=0; i<button_cols.length; i++) {
		button_cols[i].style.display = "none";
	}
	document.getElementById("save-changes-button").style.display = "none";
	if (resetDisable) {
		document.getElementById("reset-changes-button").style.display = "none";
	}
}

function enableTeamChanges() {
	var button_cols = document.getElementsByClassName("buy-sell-column");
	for (var i=0; i<button_cols.length; i++) {
		button_cols[i].style.display = "inline-block";
	}
	document.getElementById("save-changes-button").style.display = "inline-block";
	document.getElementById("reset-changes-button").style.display = "inline-block";
}

function printPlayerIDs() {
	console.log(player_ids);
}


function Timer(s, m, h) {
	//this.createdTime = performance.now();
	this.seconds = s;
	this.minutes = m;
	this.hours = h;
	this.expired = true;
	this.timeField = document.getElementById("countdown");
	this.clearInfo;

	this.start = function() {
		this.expired = false;
		this.setTimeField();
		this.clearInfo = window.setInterval(function() { timer.update(); }, 1000);
	};
	this.stop = function() {
		window.clearInterval(this.clearInfo);
	};
	this.onExpire = function() {
		console.log('[timer.expired]');
		this.expired = true;
		this.stop();
		disableTeamChanges(true);
	};
	this.update = function() {
		var expired = false;

		if (this.seconds > 0) { //nothing is 0
		   this.seconds--;
		}
		else if (this.minutes > 0) { //seconds only are 0
		   this.seconds = 59;
		   this.minutes--;
		}
		else if (this.hours > 0) { //seconds and minutes are 0
		   this.seconds = this.minutes = 59;
		   this.hours--;
		}/*
		else if (this.days > 0) { //seconds, minutes and hours are 0
		   this.seconds = this.minutes = 59;
		   this.hours = 23;
		   this.days--;
		}
		*/
		else { //everything is zero
		   this.onExpire();
		}
		this.setTimeField();

	};
	this.setTimeField = function() {
		//console.log('[timer.setTimeField]');
		if (!this.expired) {
			this.timeField.value = ((this.hours>9)?'':'0') + (this.hours).toString() +
						':' + ((this.minutes>9)?'':'0') + (this.minutes).toString() +
						':' + ((this.seconds>9)?'':'0') + (this.seconds).toString();
		} else {
			this.timeField.value = '--:--:--';
		}
	};
	this.benchmark = function() { //This is for timers who have a day counter too.
		var testTimer = new Timer(42,42,17,9);
		var properties = ['seconds', 'minutes', 'hours'];
		var a, b, res, t = [0,0,0,0];

		a = performance.now();
		testTimer.update();
		b = performance.now();
		t[0] = b-a;
		for (var i=0; i<properties.length; i++) {
		   for (var j=0; j<=i; j++) {
		      testTimer[properties[j]] = 0;
		   }
		   a = performance.now();
		   testTimer.update();
		   b = performance.now();
		   t[i+1] = b-a;
		}

		res = (84960/86400)*t[0] + (1416/86400)*t[1] + (23/86400)*t[2] + (1/86400)*t[3];
		return res;
	};
	this.printState = function() {
		console.log('[timer.printState] Hours: ' + this.hours + ' - Minutes: ' + this.minutes + ' - Seconds: ' + this.seconds);
	};
}

function StatsLeadersManager(stats) {
	this.statsByCategory = stats;
	this.categories = Object.getOwnPropertyNames(this.statsByCategory);
	this.curr = 0;
	this.table = document.getElementById("top_players").querySelector('tbody');
	this.categoryField = this.table.children[0].querySelector('span');
	this.nameMapping = {
		Assists: 'Ασσίστ',
		Blocks: 'Τάπες',
		Index: 'Index',
		Rebounds: 'Ριμπάουντ',
		Steals: 'Κλεψίματα'
	};



	this.updateCurr = function() {
		this.curr = (this.curr + 1) % this.categories.length;
	};
	this.displayCurr = function() {
		var t = this.categories[this.curr];
		var cat = this.statsByCategory[t];
		var l1 = cat['Names'].length;
		var rows = this.table.children, cols;
		var l2 = rows.length;
		for (i=0; i<l1; i++) {
			cols = rows[i+1].children;
			cols[1].innerText = cat['Names'][i];
			cols[2].innerText = cat['Scores'][i];
		}
		for (i=l1+1; i<l2; i++) {
			cols = rows[i].children
			cols[1].innerText = '-';
			cols[2].innerText = '-';
		}
		this.categoryField.innerText = this.nameMapping[t];
	};
	this.start = function() {
		var a_tag = this.table.children[0].querySelector('a');
		a_tag.onclick = function() {
			statsLeadersManager.nextClicked();
		};
		this.curr = this.categories.indexOf('Index');
		this.displayCurr();
	};
	this.nextClicked = function() {
		this.updateCurr();
		this.displayCurr();
	};
	this.printStatsByCategory = function() {
		console.log(this.statsByCategory);
		console.log(this.categories);
	};
};

<?php
$now = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
$now_date = date_create_from_format('Y-m-d H:i:s', $now);
$lock_date = date_create_from_format('Y-m-d H:i:s', $LOCKTIME);
$interval = date_diff($now_date, $lock_date, TRUE);
$tmp = ($interval->d)*24 + $interval->h;
$h = json_encode($tmp);
$i = json_encode($interval->i);
$s = json_encode($interval->s);
?>

var timer = new Timer(<?php echo $s ?>, <?php echo $i ?>, <?php echo $h ?>);
timer.start();
var statsLeadersManager = new StatsLeadersManager(<?php echo json_encode($stats) ?>);
statsLeadersManager.start();

</script>

</html>

		
