<!DOCTYPE html>

<?php

function qSort($arr) {
	$lte = $gt = array();
	if (count($arr) < 2) {
		return $arr;
	}
	$pivot_key = key($arr);
	$pivot = array_shift($arr);
	$i1 = 0;
	$i2 = 0;
	foreach($arr as $val) {
	   if ($val['p'] <= $pivot['p']) {
	      $lte[$i1]['p'] = $val['p'];
	      $lte[$i1]['i'] = $val['i'];
	      ++$i1;
	   } else {
	      $gt[$i2]['p'] = $val['p'];
	      $gt[$i2]['i'] = $val['i'];
	      ++$i2;
	   }
	}
	return array_merge(qSort($gt), array(array('p'=>$pivot['p'], 'i'=>$pivot['i'])), qSort($lte));
}

function getSecondColumn($arr) {
	foreach ($arr as $val)
	   $ret[] = $val['i'];
	return $ret;
}



if (!session_id()) session_start();

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	die("Connection to database failed");
}

$pos_keys = ['pgs', 'sgs', 'pfs'];
$col_names = ['name', 'team', 'buyButton', 'nextOp', 'meanScore', 'price', 'ID'];
$map_names = array('name' => 'Name', 'team' => 'TeamName', 'meanScore' => 'MeanScore', 'nextOp' => 'NextOpponent', 'price' => 'Price', 'ID' => 'PlayerID', 'buyButton' => 'BuyButton');
$sql = "SELECT PlayerID, FirstName, LastName, Price, MeanScore, NextOpponent, TeamName FROM Player WHERE Position = 'PG'";
$res['pgs'] = $conn->query($sql);
$sql = "SELECT PlayerID, FirstName, LastName, Price, MeanScore, NextOpponent, TeamName FROM Player WHERE Position = 'SG' OR Position = 'SF' OR Position='SG/SF'";
$res['sgs'] = $conn->query($sql);
$sql = "SELECT PlayerID, FirstName, LastName, Price, MeanScore, NextOpponent, TeamName FROM Player WHERE Position = 'PF' OR Position = 'C' OR Position='PF/C'";
$res['pfs'] = $conn->query($sql);
$conn->close();

foreach ($pos_keys as $key) {
   $row = $res[$key]->fetch_assoc();
   $i = 0;
   while ($row != NULL) {
      $row['Name'] = $row['LastName'] . " " . substr($row['FirstName'], 0, 1) . ".";
      $row['BuyButton'] = "<a class=\"button\" href =\"#\" onclick=\"userActions.buy(" . $row['PlayerID'] . "," . $row['Price'] . ")\">Buy</a>";
      foreach ($col_names as $col)
	      $players[$key][$i][$col] = $row[$map_names[$col]];
      $prices[$i]['p'] = $row[$map_names['price']];
      $prices[$i]['i'] = $i;
      ++$i;
      $row = $res[$key]->fetch_assoc();
   }
   $nr[$key] = $i;
   $sorted_indexes[$key] = getSecondColumn(qSort($prices));
}

unset($prices);
unset($res);
unset($col_names[6]); //this array is used later for the columns of a table, and ID is not one of those columns

?>

<html>
<head>
	<title>Νέα ομάδα</title>
	<style>
		ul {
			list-style-type: none;
			margin: 0;
			padding: 0;
			overflow: hidden;
			/*background-color: #f1f1f1;*/
			background-color: #f1f1f1;;
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
			/*background-color: #34495E; */
			margin: auto;
			padding: 20px;
			border: 1px solid #888;
			width: 50%;
			height: 60%;
		}

		.msg-modal-content {
			background-color: #fefefe;
			/*background-color: #34495E; */
			margin: auto;
			padding: 0px 0px;
			border: 1px solid #34495E;
			border-radius: .4em;
			width: 27%;
			height: 15%;
		}

		td.last-column {
			/*float: right;*/
			cursor: pointer;
			background-color: white;
			border: 1px solid green;
			color:black;
			/*padding: 1px 1px;
			padding-left:3px;
			padding-right:3px;
			*/
			text-align:center;
			/*display: inline-block;*/
		}
		.button {
			background-color: orange;
			border: none;
			color: black;
			padding: 3px 3px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 12px;
			/*margin: 4px 2px;*/
			margin-left: 10px;
			cursor: pointer;
		}

		.head-table {
			background: #34495E;
			color: #fff;
			border-radius: .4em;
			overflow: hidden;
			width: 100%;
			font-size: 12px;
			font-weight: bold;
			margin-top: 15px;
		}

		.team-table {
			background: #34495E;
			color: #fff;
			border: 2px solid #34495E;
			border-radius: .4em;
			overflow: hidden;
			width: 100%;
			font-size: 12px;
			font-weight: bold;
			text-align: center;
			border-spacing: 0px;
			/*margin-top: 15px;*/
		}

		.team-table td {
			padding: 3px 3px;
		}

		.team-table tr:nth-child(even) { background: #CCC; color: #232b2b; }
		.team-table tr:nth-child(odd)  { background: #FFF; color: #232b2b; }

		.buy-table {
			background: #34495E;
			color: #fff;
			border: 2px solid #34495E;
			border-radius: .4em;
			overflow: hidden;
			width: 100%;
			font-size: 12px;
			font-weight: bold;
			text-align: center;
			border-spacing: 0px;
		}

		.buy-table td {
			padding: 3px 3px;
		}

		.buy-table tr:nth-child(even) { background: #fff; color: #232b2b; }
		.buy-table tr:nth-child(odd)  { background: #fff; color: #232b2b; }

		.fix-float1 {
			text-align: center;
			margin: 4px 10px;
			display: inline-block;
		}

		.fix-float2 {
			/*padding: 5px 10px;*/
			text-align: center;
			margin: 4px 6px;
			display: inline-block;
		}

		.msg_box {
			display: none;
			text-align: center;
			padding-top: 0px;
			width: 100%;
		}

		.msg_button {
			background-color: orange;
			border: 1px solid orange;
			border-radius: .4em;
			color: white;
			font-weight: bold;
			padding: 3px 3px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 13px;
			/*margin: 4px 2px;*/
			margin-top: 10px;
			cursor: pointer;
		}

		.msg_title {
			margin-top: 0px;
			background-color: #34495E;
			color: #fff;
			width: 100%;
			font-weight: bold;
		}

		.msg_text {
			font-size: 13px;
			font-weight: bold;
			color: #585858;
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
	</style>
<head>

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

	<div style="width:60%" class="team-head">
	<table class="head-table">
	<tr>
		<td style="width:15%;">Όνομα ομάδος:</td>
		<td style="width:18%"><input id="name_field" style="color:#585858; font-weight:bold" type="text" size="13" name="teamName" maxlength="25"></td>
		<td style="width:18%;">Πρώτος αρχηγός:</td>
		<td style="width:17%"><select id="first_captain" style="color:#585858; font-weight:bold; width:100%" onchange="teamTable.onFirstCaptainChange()">
			<option value="-1">-Επιλέξτε</option>
		</select></td>
		<td style="float:right; width:70%; text-align:right; margin-top:4px"> 
			<a class="button" style="padding:5px 0px; margin:0px 0px; width:75%;" onclick="userActions.reset()">Επαναφορά</a>
			
		</td>
	</tr>
	<tr>
		<td style="width:15%;">Χρήματα:</td>
		<td style="width:18%;"><input id="money_field" style="color:#585858; font-weight:bold" type="text" size="13" name="moneyLeft"></td>
		<td style="width:18%;">Δεύτερος αρχηγός:</td>
		<td style="width:17%"><select id="second_captain" style="color:#585858; font-weight:bold; width:100%">
			<option value="-1">-Επιλέξτε</option>
		</select></td>
		<td style="float:right; width:70%; text-align:right; margin-top:4px">
			<a class="button" style="padding:5px 0px; margin:0px 0px; width:75%;" onclick="userActions.save()">Αποθήκευση</a>
			
		</td>
	</tr>
	</table>
	</div>

	<div style="width:60%; margin-top:10px" class="team-body">
	<table class="team-table">
	<?php 
	$pos_names = array('Point Guards', 'Shooting Guards and Small Forwards', 'Power Forwards and Centers');
	$pos_short = array('pg', 'sg', 'pf');
	$pos_nr = array(2, 4, 4);
	for ($i=0; $i<count($pos_names); $i++) { ?>
		<tr style="background: #34495E; color: #fff">
			<td colspan="6"><?php echo $pos_names[$i] ?></td>
		</tr>
		<?php for ($j=0; $j<$pos_nr[$i]; $j++) {
		$row_id = $pos_short[$i] . $j; ?>
		<tr id="<?php echo $row_id ?>">
			<td name="name" style="text-align:center; width:18%">-</td>
			<td name="team" style="width:18%">-</td>
			<td name="buyButton" style="float:right"><a class="button" onclick="btManager.displayTable('<?php echo $row_id ?>')">Buy</a></td>
			<td name="nextOp" style="float:right"><span class="fix-float1">-</span></td>
			<td name="price" style="float:right"><span class="fix-float1">-</span></td>
			<td name="meanScore" style="float:right"><span class="fix-float1">-</span></td>
		</tr>
		<?php
		}
	}
	?>			

	</table>
	</div>
	<div id="modal" class="modal">
	<div id="modal_content" class="modal-content">
		<?php $col_styles = array("width:20%", "width:17%", "float:right; width:12%",
				          "float:right; width:20%", "float:right; width:12%", "float:right; width:12%");
		$col_titles = array("Παίκτης", "Ομάδα", "", "Επόμενος Αντίπαλος", "Μέσος Όρος", "Τιμή");
		$span_needed = array(false, false, false, true, true, true); ?>
		<div id="pgs_table" style="display:none">
		   <table class="buy-table">
		      <tr style="background: #34495E; color:#fff">
		      <?php $j=0; foreach($col_titles as $title) { ?>
			 <td style="<?php echo $col_styles[$j++] ?>"><?php echo $title ?></td>
		      <?php } ?>
		      </tr>
		      <?php $sis = & $sorted_indexes['pgs'];
		      for($i=0; $i<$nr['pgs']; $i++) { ?>
		      <tr id="<?php echo 'modal_' . $players['pgs'][$sis[$i]]['ID'] ?>">
			 <?php $j=0;
		         foreach ($col_names as $col) { ?>
			 <td name="<?php echo $col ?>" style="<?php echo $col_styles[$j] ?>">
		         <?php if ($span_needed[$j]) echo "<span class=\"fix-float2\">"; ?>
			 <?php echo $players['pgs'][$sis[$i]][$col] ?>
		         <?php if ($span_needed[$j++]) echo "</span>"; ?></td>
		         <?php } ?>
		      </tr>
		      <?php } ?>
		   </table>
		</div>
		<div id="sgs_table" style="display:none">
		   <table class="buy-table">
		      <tr style="background: #34495E; color:#fff">
		      <?php $j=0; foreach($col_titles as $title) { ?>
			 <td style="<?php echo $col_styles[$j++] ?>"><?php echo $title ?></td>
		      <?php } ?>
		      </tr>
		      <?php $sis = & $sorted_indexes['sgs'];
		      for($i=0; $i<$nr['sgs']; $i++) { ?>
		      <tr id="<?php echo 'modal_' . $players['sgs'][$sis[$i]]['ID'] ?>">
			 <?php $j=0;
		         foreach ($col_names as $col) { ?>
			 <td name="<?php echo $col ?>" style="<?php echo $col_styles[$j] ?>">
		         <?php if ($span_needed[$j]) echo "<span class=\"fix-float2\">"; ?>
			 <?php echo $players['sgs'][$sis[$i]][$col] ?>
		         <?php if ($span_needed[$j++]) echo "</span>"; ?></td>
		         <?php } ?>
		      </tr>
		      <?php } ?>
		   </table>
		</div>
		<div id="pfs_table" style="display:none">
		   <table class="buy-table"> 
		      <tr style="background: #34495E; color:#fff">
		      <?php $j=0; foreach($col_titles as $title) { ?>
			 <td style="<?php echo $col_styles[$j++] ?>"><?php echo $title ?></td>
		      <?php } ?>
		      </tr>
		      <?php $sis = & $sorted_indexes['pfs'];
		      for($i=0; $i<$nr['pfs']; $i++) { ?>
		      <tr id="<?php echo 'modal_' . $players['pfs'][$sis[$i]]['ID'] ?>">
			 <?php $j=0;
		         foreach ($col_names as $col) { ?>
			 <td name="<?php echo $col ?>" style="<?php echo $col_styles[$j] ?>">
		         <?php if ($span_needed[$j]) echo "<span class=\"fix-float2\">"; ?>
		         <?php echo $players['pfs'][$sis[$i]][$col] ?>
		         <?php if ($span_needed[$j++]) echo "</span>"; ?></td>
		         <?php } ?>
		      </tr>
		      <?php } ?>
		   </table>
		</div>
	</div>
	</div>
	<div id="msg_modal" class="modal">
	<div id="msg_modal_content" class="msg-modal-content">
		<div id="alerts" class="msg_box">
		<p class="msg_title"></p>
		<p class="msg_text"></p>
		<a class="msg_button" onclick="userNotifier.alerted()">Συγγνώμη</a>
		</div>
		<div id="confirms" class="msg_box">
		<p class="msg_title"></p>
		<p class="msg_text"></p>
		<a class="msg_button" onclick="userNotifier.notConfirmed()">Άκυρο</a>
		<a class="msg_button" onclick="userNotifier.confirmed()">Σύμφωνοι</a>
		</div>
	</div>
	</div>
	
<script>

function showPos(id) {
	document.getElementById("modal").style.display = "block";
	document.getElementById(id).style.display = "block";
}

window.onclick = function(event) {
	if (event.target == modal) {
		document.getElementById("pgs_table").style.display = "none";
		document.getElementById("sgs_table").style.display = "none";
		document.getElementById("pfs_table").style.display = "none";
		document.getElementById("modal").style.display = "none";
	}
}
 

function PlayerRow(name="", team="", meanScore="", nextOp="", price="") {
	this.name = name;
	this.team = team;
	this.meanScore = meanScore;
	this.nextOp = nextOp;
	this.price = price;

	this.fillFromID = function(id) {
		var mR = document.getElementById('modal_'+id);
		var names = Object.getOwnPropertyNames(this);
		var col;
		for (var i=0; i<names.length; i++) {
		   col = utils.getLeafValue(mR.querySelector('td[name="' + names[i] + '"]'));
		   if (col != null) this[names[i]] = col;
		}
	};

}

function UserNotifier() {
	this.modal = document.getElementById("msg_modal");
	this.modal_content = document.getElementById("msg_modal_content");
	this.alerts = document.getElementById("alerts");
	this.confirms = document.getElementById("confirms");
	this.confirmCallback = function() {};
	this.confirmSaveTitle = "Επιβεβαίωση";
	this.confirmSaveMsg = "Μετά την αποθήκευση της ομάδας θα έχετε το δικαίωμα μόνο για 3 επιπλέον αλλαγές.";
	this.badTeamNameTitle = "Μη έγκυρο όνομα ομάδος";
	this.badTeamNameMsg = "Επιτρέπονται λατινικοί και ελληνικοί χαρακτήρες, και επιπλέον η τελεία(.), η κάτω παύλα(_) και το κενό.";
	this.showSavingStatus = function() {};
	this.hideSavingStatus = function() {};
	this.saveSuccessful = function() {
		console.log('Team saved successfully!');
	};
	this.saveNotSuccessful = function() {
		console.log('Team not saved :(');
	};
	this.badTeamName = function() {
		var h;
		this.modal.style.display = "block";
		//this.modal_content.style.height = "17%";
		this.alerts.querySelector('p[class="msg_title"]').innerText = this.badTeamNameTitle;
		this.alerts.querySelector('p[class="msg_text"]').innerText = this.badTeamNameMsg;
		this.alerts.style.display = "block";
		h = this.alerts.clientHeight + 10;
		this.modal_content.style.height = h + 'px';

	};
	this.teamNotFilled = function() {};
	this.confirmSave = function() {
		var boxHeight;
		this.confirmCallback = userActions.confirmedSave;
		this.modal.style.display = "block";
		//this.modal_content.style.height = "15%";
		this.confirms.querySelector('p[class="msg_title"]').innerText = this.confirmSaveTitle;
		this.confirms.querySelector('p[class="msg_text"]').innerText = this.confirmSaveMsg;
		this.confirms.style.display = "block";
		console.log(this.confirms.clientHeight);
		boxHeight = this.confirms.clientHeight + 10;
		this.modal_content.style.height = boxHeight + 'px';
	};
	this.alerted = function() {
		this.alerts.style.display = "none";
		this.modal.style.display = "none";
	};
	this.confirmed = function() {
		this.confirmCallback();
	};
	this.notConfirmed = function() {
		this.confirms.style.display = "none";
		this.modal.style.display = "none";
	};
}

function AjaxManager() {
	this.requestSave = function() {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
		   if (this.readyState == 4 && this.status == 200) {
		      userNotifier.hideSavingStatus();
		      if (this.responseText == "OK") {
		         userNotifier.saveSuccessful();
		      }
		      else {
		         userNotifier.saveNotSuccessful();
		      }

		   }
		};
		var ids_prices = '';
		console.log("ajax no_players: "+team.numberOfPlayers);
		for (var i=0; i<team.numberOfPlayers; i++) {
			console.log("ajax i: "+i+" id: "+team.bought[i].id+" price: "+team.bought[i].price);
			ids_prices += team.bought[i].id + '_' + team.bought[i].price + '-';
		}
		console.log("ajax ids_prices: "+ids_prices);
		ids_prices = ids_prices.slice(0, -1); //remove last '-'
		var url_args = '?ids=' + ids_prices + '&remMoney=' + team.moneyLeft.toFixed(2) + '&teamName=' + team.name;
		xhttp.open("GET", "save_new_team.php"+url_args, true);
		console.log("[AjaxManager.requestSave()] Ready to send save request with url arguments: " + url_args);
		xhttp.send();
	};
}

function UserActions() {
	this.buy = function(playerID, price) {
		team.onBuy(playerID, price);
		teamTable.onBuy(playerID);
		btManager.onBuy(playerID);
	};
	this.sell = function(playerID, rowID, price) {};
	this.reset = function() {
		teamTable.reset();
		/* The order of calling btManager.reset() and team.reset() is important */
		btManager.reset();
		team.reset();
	};
	this.save = function() {
		team.setName();
		if (!team.validName()) {
			userNotifier.badTeamName();
		}
		else if (!team.isFilled()) {
			userNotifier.teamNotFilled();
		}
		else {
			userNotifier.confirmSave();
		}
	};
	this.confirmedSave = function() {
		userNotifier.showSavingStatus();
		ajaxManager.requestSave();
	};
}

function TeamTable() {
	this.table = document.getElementsByClassName("team-table")[0];
	this.moneyField = document.getElementById("money_field");
	this.moneyField.value = "600.00";
	this.nameField = document.getElementById("name_field");
	this.fCList = document.getElementById("first_captain");
	this.sCList = document.getElementById("second_captain");
	this.sCListDisabled = -1;
	this.rowIDs = ['pg0', 'pg1', 'sg0', 'sg1', 'sg2', 'sg3', 'pf0', 'pf1', 'pf2', 'pf3'];
	this.rowCols = ["name", "team", "buyButton", "meanScore", "price", "nextOp"];
	this.getName = function() {
		return this.nameField.value;
	};
	this.onBuy = function(playerID) {
		var mL = team.moneyLeft.toFixed(2);
		this.moneyField.value = mL;
		var nR = new PlayerRow();
		nR.fillFromID(playerID);
		this.setRow(nR, playerID);
		this.addCaptainOption(playerID, nR['name']);
	};
	this.setRow = function(playerRow, id) {
		var rowID = btManager.getRowClicked();
		var row = document.getElementById(rowID);
		var col_names = Object.getOwnPropertyNames(playerRow);
		var col, a_tag;
		var price = playerRow['price'];
		for (var i=0; i<col_names.length; i++) {
		   col = row.querySelector('td[name="' + col_names[i] + '"]');
		   if (col != null) {
		      col = utils.getLeafElem(col);
		      col.innerHTML = playerRow[col_names[i]];
		   }
		}
		a_tag = utils.getLeafElem(row.querySelector('td[name="buyButton"]'));
		a_tag.innerHTML = "Sell";
		a_tag.setAttribute("onclick", "");
		a_tag.onclick = function() { //Should be userActions.sell(id, price, rowID)
			/* The order these methods are called is important */
			team.onSell(id, price);
			teamTable.onSell(id, rowID);
			btManager.onSell(id);
		};
	};
	this.onSell = function(playerID, rowID) {
		var mL = team.moneyLeft.toFixed(2);
		this.moneyField.value = mL;
		this.unsetRow(rowID);
		this.removeCaptainOption(playerID);
	};
	this.unsetRow = function(rowID) {
		var row = document.getElementById(rowID);
		var elem;
		for (var i=0; i<this.rowCols.length; i++) {
			elem = utils.getLeafElem(row.querySelector('td[name="' + this.rowCols[i] + '"]'));
			if (this.rowCols[i] == "buyButton") {
				elem.innerHTML = 'Buy';
				elem.onclick = function() { btManager.displayTable(rowID); };
			}
			else elem.innerHTML = "-";
		}
	};
	this.addCaptainOption = function(id, name) {
		var opt = document.createElement('option');
		opt.value = id;
		opt.text = name;
		this.fCList.add(opt);
		this.sCList.add(opt.cloneNode(true));
	};
	this.removeCaptainOption = function(id) {
		var l = this.fCList.options.length;
		for (var i=0; i<l; i++) {
		   if (this.fCList.options[i].value == id) {
		      this.fCList.remove(i);
		      break;
		   }
		}
		l = this.sCList.options.length;
		for (var i=0; i<l; i++) {
		   if (this.sCList.options[i].value == id) {
		      this.sCList.remove(i);
		      break;
		   }
		}
	};
	this.onFirstCaptainChange = function() {
		if (this.sCListDisabled > 0)
		   this.sCList.options[this.sCListDisabled].disabled = false;
		if (this.fCList.value == -1) {
		   this.sCListDisabled = -1;
		   return;
		}
		if (this.fCList.value == this.sCList.value) {
		   this.sCList.value = -1;
		}
		this.sCListDisabled = this.fCList.selectedIndex;
		this.sCList.options[this.sCListDisabled].disabled = true;
	}
	this.reset = function() {
		this.emptyTable();
		this.emptyCaptainLists();
		this.resetMoneyNameFields();
	};
	this.emptyTable = function() {
		var l = this.rowIDs.length;
		for (var i=0; i<l; i++) {
			this.unsetRow(this.rowIDs[i]);
		}
	};
	this.emptyCaptainLists = function() {
		var l = this.fCList.options.length;
		console.log("[emptyCaptainList] options length: " + l);
		for (var i=l-1; i>0; i--) {
			this.fCList.remove(i);
			this.sCList.remove(i);
		}
		this.fCList.value = -1;
		this.sCList.value = -1;
	};
	this.resetMoneyNameFields = function() {
		this.moneyField.value = "600.00";
		this.nameField.value = "";
	};

}

function Utils() {
	this.genCharArray = function(charA, charZ) {
		var a = [], i = charA.charCodeAt(0), j = charZ.charCodeAt(0);
		for (; i <= j; ++i) {
			a.push(String.fromCharCode(i));
		}
		return a;
	};

	this.create_2D_array = function(rows) {
		var arr = [];
		for (var i=0; i<rows; i++) {
			arr[i] = [];
		}
		return arr;
	};

	this.getLeafValue = function(elem) {
		if (elem == null) return null;
		if (elem.children.length > 0) return this.getLeafValue(elem.children[0]);
		else return elem.innerHTML;
	};

	this.getLeafElem = function(elem) {
		if (elem.children.length > 0) return this.getLeafElem(elem.children[0]);
		else return elem;
	};
}

function BuyTablesManager() {
	this.rowClicked = "";
	this.modal = document.getElementById("modal");
	this.tables;
	this.indexes;

	//setUpTables();
	this.hideTables = function() {
		this.tables['pgs'].style.display = "none";
		this.tables['sgs'].style.display = "none";
		this.tables['pfs'].style.display = "none";
	};
	this.hideModal = function() {
		this.modal.style.display = "none";
	};
	this.getRowClicked = function() {
		return this.rowClicked;
	};
	this.updateTableAfterBuy = function(pos_key) {
		console.log("updateTableAfterBuy entering. indexes["+pos_key+"]="+this.indexes[pos_key]);
		var tbody = this.tables[pos_key].querySelector('tbody');
		var rows = tbody.children;
		var lM = team.moneyLeft;
		var all_hidden = true;
		var first = this.indexes[pos_key] + 1;
		for (var i=first; i<rows.length; i++) { //First row is the header so start from i=1.
			if (parseFloat(utils.getLeafValue(rows[i].querySelector('td[name="price"]'))) > lM) {
				rows[i].querySelector('td[name="buyButton"]').style.visibility = "hidden";
			}
			else {
				this.indexes[pos_key] = i-1;
				all_hidden = false;
				break;
			}
		}
		if (all_hidden) { this.indexes[pos_key] = rows.length - 1; } //All players on this table are not affordable.
		console.log("updateTableAfterBuy exiting. indexes["+pos_key+"]="+this.indexes[pos_key]);
	};
	this.updateTableAfterSell = function(pos_key) {
		console.log("updateTableAfterSell entering. indexes["+pos_key+"]="+this.indexes[pos_key]);
		var tbody = this.tables[pos_key].querySelector('tbody');
		var rows = tbody.children;
		var lM = team.moneyLeft;
		var got_one = false;
		var i = this.indexes[pos_key];

		while ((!got_one)  && (i > 0)) {
		   if (parseFloat(utils.getLeafValue(rows[i].querySelector('td[name="price"]'))) <=lM) {
		      rows[i].querySelector('td[name="buyButton"]').style.visibility = "visible";
		      --i;
		   }
		   else {
		      got_one = true;
		      this.indexes[pos_key] = i;
		   }
		}
		if (!got_one) { this.indexes[pos_key] = 0; } //All players on this table are affordable
		console.log("updateTableAfterSell exiting. indexes["+pos_key+"]="+this.indexes[pos_key]);
	};
	this.resetTable = function(pos_key) {
		var tbody = this.tables[pos_key].querySelector('tbody');
		var rows = tbody.children;
		for (var i=this.indexes[pos_key]; i>0; i--) {
			rows[i].querySelector('td[name="buyButton"]').style.visibility = "visible";
		}
	};

	this.onBuy = function(playerID) {
		this.hideTables();
		var modalRow = document.getElementById('modal_'+playerID);
		modalRow.style.display = "none";
		this.updateTableAfterBuy('pgs');
		this.updateTableAfterBuy('sgs');
		this.updateTableAfterBuy('pfs');
		this.hideModal();
	};
	this.onSell = function(playerID) {
		document.getElementById('modal_'+playerID).style.display = "";
		this.updateTableAfterSell('pgs');
		this.updateTableAfterSell('sgs');
		this.updateTableAfterSell('pfs');
	};
	this.reset = function() {
		var b = team.bought;
		var l = b.length;
		for (var i=0; i<l; i++) {
			document.getElementById('modal_'+b[i].id).style.display = "";
		}
		this.resetTable('pgs');
		this.resetTable('sgs');
		this.resetTable('pfs');
	};
	this.displayTable = function(pos) {
		this.modal.style.display = "block";
		this.rowClicked = pos;
		var pos_key = pos.substring(0,2) + 's'; //pos is something like pg0, so make it pgs
		this.tables[pos_key].style.display = "block";
	};
}

/*
BuyTablesManager.prototype.initTables = function() {
	this.tables['pgs'] = document.getElementById("pgs_table");
	this.tables['sgs'] = document.getElementById("sgs_table");
	this.tables['pfs'] = document.getElementById("pfs_table");
};
 */

function setUpTables() {
	this.tables = {};
	this.tables['pgs'] = document.getElementById("pgs_table");
	this.tables['sgs'] = document.getElementById("sgs_table");
	this.tables['pfs'] = document.getElementById("pfs_table");
	this.indexes = {};
	this.indexes['pgs'] = 0;
	this.indexes['sgs'] = 0;
	this.indexes['pfs'] = 0;
}

BuyTablesManager.prototype = new setUpTables();


function TeamUnderConstruction() {
	this.moneyLeft = 600.0;
	this.numberOfPlayers = 0;
	this.bought = [];
	this.allowedChars = this.initAllowedChars();
	this.name = "";
	this.enoughMoney = function(price) {
		return ((this.moneyLeft>=Price)?true:false);
	};
	this.setName = function() {
		this.name = teamTable.getName();
	};
	this.validName = function() {
		var ret = true;
		var l = this.name.length;
		if (l == 0) ret = false;
		for (var i=0; i<l; i++) {
		   if (!this.allowedChars.hasOwnProperty(this.name.charAt(i))) {
		      ret = false;
		      break;
		   }
		}
		return ret;

	};
	this.isFilled = function() {
		return (this.numberOfPlayers == 10);
	};
	this.getIndexById = function(id) {
		var found = false, i = 0;
		while (!found) {
			if (this.bought[i].id == id) {
			   found = true;
			}
			else ++i;
		}
		return i;
	};
	this.onBuy = function(playerID, price) {
		this.bought[this.numberOfPlayers] = {};
		this.bought[this.numberOfPlayers].id = playerID;
		this.bought[this.numberOfPlayers].price = price;
		this.numberOfPlayers++;
		this.moneyLeft -= parseFloat(price);
		console.log("team property: " + this.moneyLeft);
	};
	this.onSell = function(playerID, price) {
		var index = this.getIndexById(playerID);
		this.bought.splice(index, 1);
		this.numberOfPlayers--;
		this.moneyLeft += parseFloat(price);
		console.log("team property: " + this.moneyLeft);
	};
	this.reset = function() {
		this.moneyLeft = 600.0;
		this.boudght = []; //this.bought.length = 0;
		this.numberOfPlayers = 0;
		this.teamName = "";
	};
}

TeamUnderConstruction.prototype.initAllowedChars = function() {
	var arr1 = utils.genCharArray('a', 'z');
	var arr2 = utils.genCharArray('A', 'Z');
	var arr3 = utils.genCharArray('α', 'ω');
	var arr4 = utils.genCharArray('Α', 'Ω');
	var arr5 = utils.genCharArray('0', '9');
	var arr6 = ['_', '.', ' '];
	var arr7 =  arr1.concat(arr2, arr3, arr4, arr5, arr6);

	var hashTable = {}; //All objects in javascript are hash tables
	for (var i=0; i<arr7.length; i++) {
		hashTable[arr7[i]] = null; //add all the allowed chars as keys in the hash table. Values(null) are irrelevant.
	}
	return hashTable;
}

var utils = new Utils();
var team = new TeamUnderConstruction();
var btManager = new BuyTablesManager();
var teamTable = new TeamTable();
var userActions = new UserActions();
var userNotifier = new UserNotifier();
var ajaxManager = new AjaxManager();

</script>

</body>			
</html>
