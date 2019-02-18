<!DOCTYPE>

<?php

if (!session_id()) session_start();

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error != NULL) {
	die("Connection to database failed: " . $conn->connect_error);
}

$sql = "SELECT LeagueName, LeagueID " .
	"FROM " .
	   "(SELECT LeagueID " .
	   "FROM TeamLeague JOIN Team USING (TeamID) " .
	   "WHERE UserID = " . $_SESSION['userID'] . ") t " .
	"JOIN League USING (LeagueID)";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$league_no=0;
while ($row != NULL) {
	$_SESSION['leagues'][$league_no] = $row['LeagueName'];
	$_SESSION['leagueIDs'][$league_no] = $row['LeagueID'];
	$league_no++;
	$row = $res->fetch_assoc();
}

$sql = "SELECT TeamName, TeamID " .
	"FROM Team " .
	"WHERE UserID = " . $_SESSION['userID'];
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$teams_no = 0;
while ($row != NULL) {
	$_SESSION['teams'][$teams_no] = $row['TeamName'];
	$_SESSION['teamIDs'][$teams_no] = $row['TeamID'];
	$teams_no++;
	$row = $res->fetch_assoc();
}

$conn->close();

?>

<html>
<head>
<title>Λίγκες</title>
</head>
<style>
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
	.container {
		margin-top:15px;
	}
	.league-container {
		margin-top: 15px;
		display:inline-block;
		float: right;
		width: 50%;
	}
	.list-container {
		margin-top: 15px;
		display: inline-block;
		text-align:center;
		width: 40%;
	}
	.leagues-list {
		border: 2px solid royalblue;
		border-radius: .4em;
		border-spacing: 0px;
		width:100%;
	}
	.leagues-list td {
		/*padding: 2px 4px;*/
	}
	.leagues-list tr:nth-child(even) {background: #FFF;}
	.leagues-list tr:nth-child(odd)  {background: aliceblue;}
	.button {
		background-color: orange;
		border: 1px solid orange;
		border-radius: .4em;
		color: white;
		padding: 12px 15px;
		text-align: center;
		text-decoration: none;
		display: inline-block;
		font-weight: bold;
		font-size: 12px;
		margin: 4px 2px;
		cursor: pointer;
	}
	.league-link {
		font-size:12px;
		font-weight:bold;
		color:#506070;
		cursor:pointer;
		padding:4px 4px 4px 10px;
	}
	#league_table {
		width: 85%;
		margin: 0 auto;
		border: 2px solid #34495E;
		border-radius: .4em;
		border-spacing: 0px;
	}
	#league_table thead td {
		padding: 5px 5px;
	}
	#league_table thead {
		background-color:#34495E;
		color:white;
		font-weight:bold;
		font-size:14px;
		text-align:center;
	}
	#league_table tbody td {
		font-size:13px;
		font-weight:bold;
		color:#2f4f4f;
		padding:4px 4px;
	}
	#league_table tbody td:last-child {
		float:right;
		padding-right:5px;
	}
	#league_table tbody td:first-child {
		padding-left:5px;
		width:5%;
	}
	#league_table tbody tr:nth-child(odd)  { background-color: #FFF; }
	#league_table tbody tr:nth-child(even) { background-color: #CCC; }
	#join_league_form {
		margin: 0 auto;
		width: 75%;
		background-color: aliceblue;
		border: 2px solid lightblue;
		border-radius: .4em;
		margin-top:40px;
		text-align: center;
		display: none;
		padding:20px 0px;
	}
	#join_league_form .text-field {
		border: 2px solid white;
		display: inline-block;
		border-radius: .3em;
		color: #506070;
		font-weight: bold;
		padding: 4px 2px;
	}
	#join_league_form .submit-button {
		background-color: orange;
		color: white;
		font-weight: bold;
		border: 1px solid orange;
		border-radius: .4em;
		margin-top: 35px;
		padding: 4px 2px;
		text-align: center;
		cursor: pointer;
		width: 85px;
	}
	#join_league_form .ok-button {
		background-color: orange;
		color: white;
		font-weight: bold;
		border: 1px solid orange;
		border-radius: .4em;
		margin-top: 15px;
		padding: 4px 2px;
		text-align: center;
		cursor: pointer;
		font-size:12px;
		width: 40px;
	}
	.input-label {
		display:inline-block;
		width: 190px;
		/*border:1px solid orange;
		border-radius:.4em;
		background-color: orange;*/
		color:#506080;
		font-size:12px;
		font-weight:bold;
		text-align:left;
	}
	.team-select {
		background-color:white;
		color:#506070;
		font-size:12px;
		font-weight:bold;
		width:165px;
	}
	.input-line {
		margin-top: 5px;
	}
	#create_league_form {
		margin: 0 auto;
		width: 85%;
		background-color: aliceblue;
		border: 2px solid lightblue;
		border-radius: .4em;
		margin-top:40px;
		text-align: center;
		display: none;
		padding:20px 0px;
	}
	#create_league_form .text-field {
		border: 2px solid white;
		display: inline-block;
		border-radius: .3em;
		color: #506070;
		font-weight: bold;
		padding: 4px 2px;
	}
	#create_league_form .submit-button {
		background-color: orange;
		color: white;
		font-weight: bold;
		border: 1px solid orange;
		border-radius: .4em;
		margin-top: 35px;
		padding: 4px 2px;
		text-align: center;
		cursor: pointer;
		width: 85px;
	}
	#create_league_input_box .warning {
		color:red;
		padding:0px 15px;
		margin-bottom:20px;
		font-size:11px;
		font-weight:normal;
		display:none;
	}
	#create_league_form .ok-button {
		background-color: orange;
		color: white;
		font-weight: bold;
		border: 1px solid orange;
		border-radius: .4em;
		margin-top: 15px;
		padding: 4px 2px;
		text-align: center;
		cursor: pointer;
		font-size:12px;
		width: 40px;
	}
	.toggle-league-button {
		color:white;
		font-weight:bold;
		cursor:pointer;
		float:right;
		margin-right:10px;
	}
</style>

<body>
<div class="top-banner">
	<p>Basket League Fantasy 2018-19</p>
	<img src="./images/hoops2.jpg">
</div>
<div>
<ul>
	<li><a class="active" href="./teams.php">Ομάδες</a></li>
	<li><a href="./leagues.php">Λίγκες</a></li>
	<li><a href="./guides.php">Οδηγίες</a></li>
	<li style="float:right"><a href="./logout.php">Αποσύνδεση</a></li>
</ul>
</div>
<div class="container">
<div class="list-container">
<table class="leagues-list" id="leagues_table">
<tr style="background-color:royalblue;">
<td style="text-align:center; color: white; font-weight:bold; font-size:14px; padding:2px 4px;">Οι Λίγκες στις οποίες συμμετέχω</td>
<?php for ($i=0; $i<$league_no; $i++) { ?>
<tr><td class="league-link" onclick="(function showLeague() { leagueTableManager.displayLeague('<?php echo $_SESSION['leagueIDs'][$i] ?>'); })()"><?php echo $_SESSION['leagues'][$i] ?>
</td></tr>
<?php } ?>
</table>
<div id="create_join_league" style="text-align:center; margin-top:30px;">
<a class="button" onclick="(function joinLeague() { joinLeagueManager.joinLeagueStart(); })()">Συμμετοχή σε Λίγκα</a>
<a class="button" onclick="(function createLeague() { createLeagueManager.createLeagueStart(); })()">Δημιουργία Λίγκας</a>
</div>
<div id="join_league_form">
<div id="join_league_input_box">
<div class="input-line">
<label class="input-label" for="join_league_name">Όνομα Λίγκας</label>
<input class="text-field" type="text" size="15" id="join_league_name" placeholder="" name="leagueName" required />
</div>
<div class="input-line">
<label class="input-label" for="join_league_pass">Κωδικός Λίγκας</label>
<input class="text-field" type="password" size="15" id="join_league_pass" placeholder="" name="leaguePass" required />
</div>
<div class="input-line" style="margin-top:5px;">
<label class="input-label" for="join_league_team">Ομάδα</label>
<select class="team-select" id="join_league_team">
<?php for ($i=0; $i<$teams_no; $i++) { ?>
<option value="<?php echo $_SESSION['teamIDs'][$i] ?>"><?php echo $_SESSION['teams'][$i] ?></option>
<?php } ?>
</select>
</div>
<input class="submit-button" type="submit" value="Εντάξει" onclick="(function addToLeague() { joinLeagueManager.addTeamToLeague(); })()" />
<input class="submit-button" type="submit" value="Άκυρο" onclick="(function cancelJoin() { joinLeagueManager.cancelJoin(); })()" />
</div>
<div id="join_league_info_box" style="display:none;">
<p style="color:#506080; font-weight:bold; font-size:13px"></p>
<a class="ok-button" style="display:none" onclick="(function ok() { joinLeagueManager.confirmServerResponse(); })()">ΟΚ</a>
</div>
</div>
<div id="create_league_form">
<div id="create_league_input_box">
<p class="warning"></p>
<div class="input-line">
<label class="input-label" for="create_league_name">Όνομα Λίγκας</label>
<input class="text-field" type="text" size="15" id="create_league_name" placeholder="" name="leagueName" required />
</div>
<div class="input-line">
<label class="input-label" for="create_league_pass">Κωδικός Λίγκας</label>
<input class="text-field" type="password" size="15" id="create_league_pass" placeholder="" name="leaguePass" required />
</div>
<div class="input-line">
<label class="input-label" for="create_league_conf_pass">Επιβεβαίωση κωδικού</label>
<input class="text-field" type="password" size="15" id="create_league_conf_pass" placeholder="" name="leaguePassConf" required />
</div>
<div class="input-line">
<label class="input-label" for="crete_league_deadline">Προθεσμία</label>
<input class="text-field" type="text" size="15" id="create_league_deadline" placeholder="Σε ημέρες, πχ 5" name="leagueDeadline" required />
</div>
<div class="input-line" style="margin-top:5px;">
<label class="input-label" for="create_league_team">Ομάδα</label>
<select class="team-select" id="create_league_team">
<?php for ($i=0; $i<$teams_no; $i++) { ?>
<option value="<?php echo $_SESSION['teamIDs'][$i] ?>"><?php echo $_SESSION['teams'][$i] ?></option>
<?php } ?>
</select>
</div>
<input class="submit-button" type="submit" value="Έτοιμη" onclick="(function createLeague() { createLeagueManager.createLeague(); })()" />
<input class="submit-button" type="submit" value="Άκυρο" onclick="(function cancelCreate() { createLeagueManager.abortCreation(); })()" />
</div>
<div id="create_league_info_box" style="display:none;">
<p style="color:#506080; font-weight:bold; font-size:13px"></p>
<a class="ok-button" style="display:none" onclick="(function ok() { createLeagueManager.msgConfirmed(); })()">ΟΚ</a>
</div>
</div> <!--create_league_form--!>
</div> <!--list-container--!>

<div class="league-container">
<table id="league_table">
<thead>
<tr><td colspan="3">
	<span id="league_name_title">Όνομα Λίγκας</span>
	<a class="toggle-league-button" onclick="(function toggle() { leagueTableManager.toggleOrder(); })()">&rarr;</a>
</td></tr>
</thead>
<tbody>
</tbody>
</table>
</div>
</div>
</body>

<script>

function createLeague() {
	var div1 = document.getElementById('create_join_league');
	var div2 = document.getElementById('create_league_form');
	div1.style.display = 'none';
	div2.style.display = 'block';
}

function cancelJoinLeague() {
	var div1 = document.getElementById('create_join_league');
	var div2 = document.getElementById('join_league_form');
	div1.style.display = 'block';
	div2.style.display = 'none';
}

function switchLeftWithRight() {
	var left = document.getElementsByClassName('list-container')[0];
	var right = document.getElementsByClassName('league-container')[0];
	left.style.float = 'right';
	right.style.float = 'left';
}

function CreateLeagueManager(teamsNo) {
	this.teamsNo = teamsNo;
	this.serverDelimeter = '-';
	this.MINIMUM_PASSWORD_LENGTH = 7;
	this.endCreateForm = false;
	this.serverCodesToMsgs = {
		1: 'Δυστυχώς υπάρχει ήδη μία λίγκα με αυτό το όνομα. Παρακαλώ επιλέξτε ένα άλλο.',
		2: 'Συνέβη κάποιο σφάλμα εξυπηρετητή. Παρακαλώ δοκιμάστε ξανά.',
		3: 'Η λίγκα δημιουργήθηκε με επιτυχία!'
	};
	this.localMsgs = {
		badLeagueName:     "Μη έγκυρο όνομα λίγκας. Επιτρέπονται λατινικοί και ελληνικοί χαρακτήρες, " +
				    "το κενό, η τελεία('.') και η κάτω παύλα('_')",
		badPassword:       'Μη έγκυρος κωδικός. Ο κωδικός θα πρέπει να περιέχει τουλάχιστον ' +
				    this.MINIMUM_PASSWORD_LENGTH + ' χαρακτήρες και τουλάχιστον έναν αριθμό',
		passwordsMismatch: 'Οι κωδικοί δεν ταιριάζουν',
		badDeadline:       'Μη έγκυρη προθεσμία. Εισάγετε μόνο έναν αριθμό, πχ 13',
		teamNecessary:     'Δεν έχετε καμία ομάδα. Χρειάζεται πρώτα να δημιουργήστε μία!',
		selectTeam:        'Επιλέξτε μία από τις ομάδες σας για ένταξη στη λίγκα',
		waitingResponse:   'Γίνεται δημιουργία της λίγκας...'
	};
	this.nameChars = this.initNameChars();
	this.nameField = document.getElementById('create_league_name');
	this.passField = document.getElementById('create_league_pass');
	this.confPassField = document.getElementById('create_league_conf_pass');
	this.deadlineField = document.getElementById('create_league_deadline');
	this.teamField = document.getElementById('create_league_team');
	this.infoBox = document.getElementById('create_league_info_box');
	this.msgText = this.infoBox.querySelector('p');
	this.okButton = this.infoBox.querySelector('a');
	this.inputBox = document.getElementById('create_league_input_box');
	this.inputWarning = this.inputBox.querySelector('p');
	this.containerBox = document.getElementById('create_league_form');
	this.createJoinBox = document.getElementById('create_join_league');

	this.passwordsMatch = function(pass1, pass2) {
		if (pass1 === pass2) {
			return true;
		} else {
			return false;
		}
	};
	this.validName = function(input) {
		var ret = true;
		var l = input.length;

		if (l == 0) ret = false;
		for (var i=0; i<l; i++) {
			if (!this.nameChars.hasOwnProperty(input.charAt(i))) {
				ret = false;
				break;
			}
		}
		return ret;
	};
	this.validPassword = function(pass) {
		return pass.length > this.MINIMUM_PASSWORD_LENGTH && /\d/.test(pass);
	};
	this.validDeadline = function(input) { //checks if the input supplied is a positive integer
		var n = Math.floor(Number(input));
		return n !== Infinity && String(n) === input && n > 0;
	};
	this.setLocalWarning = function(key) {
		this.inputWarning.innerText = this.localMsgs[key];
		this.inputWarning.style.display = 'inline-block';
	};
	this.resetLocalWarning = function() {
		this.inputWarning.innerText = '';
		this.inputWarning.style.display = 'none';
	};
	this.msgConfirmed = function() {
		this.infoBox.style.display = 'none';
		this.msgText.innerText = '';
		this.okButton.style.display = 'none';
		if (this.endCreateForm === true) {
			this.containerBox.style.display = 'none';
			this.inputBox.style.display = 'block';
			this.createJoinBox.style.display = 'block';
			this.endCreateForm = false;
		}
		else {
			this.inputBox.style.display = 'block';
		}
	};
	this.requestLeagueCreation = function(name, pass, deadline, teamID) {
		this.inputBox.style.display = 'none';
		this.msgText.innerText = this.localMsgs.waitingResponse;
		this.infoBox.style.display = 'block';
		var xhttp = new XMLHttpRequest();
		var that = this;
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			   console.log('[requestLeagueCreation] Got response from server: ' + this.responseText);
			   var response = this.responseText;
			   var splitted = response.split(that.serverDelimeter);
			   if (splitted[0] == "3") { //success, splitted[1] is the league id
			      that.endCreateForm = true;
			      leaguesListManager.addRow(splitted[1], name);
			   }
			   that.msgText.innerText = that.serverCodesToMsgs[splitted[0]];
			   that.okButton.style.display = 'inline-block';			     
			}
		};
		xhttp.open("POST", "create_league.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send("name=" + name + "&pass=" + pass + "&deadline=" + deadline + "&teamID=" + teamID);
		console.log('[requestLeagueCreation] Sent request with parameters: ' + name + ', ' + pass + ', ' + deadline + ', ' + teamID);
	};			   
	this.createLeague = function() {
		var name = this.nameField.value, pass = this.passField.value,
			confPass = this.confPassField.value, deadline = this.deadlineField.value,
			teamID = this.teamField.value;
		if (!this.validName(name)) {
			this.setLocalWarning('badLeagueName');
		}
		else if (!this.validPassword(pass)) {
			this.setLocalWarning('badPassword');
		}
		else if (!this.passwordsMatch(pass, confPass)) {
			this.setLocalWarning('passwordsMismatch');
		}
		else if (!this.validDeadline(deadline)) {
			this.setLocalWarning('badDeadline');
		}
		else if (teamID === -1) {
			this.setLocalWarning('selectTeam');
		}
		else { //Every input is right
			this.resetLocalWarning();
			this.requestLeagueCreation(name, pass, deadline, teamID);
		}

	};
	this.abortCreation = function() {
		this.containerBox.style.display = 'none';
		this.resetLocalWarning();
		this.msgText.innerText = '';
		this.okButton.style.display = 'none';
		this.infoBox.style.display = 'none';
		this.inputBox.style.display = 'block';
		this.createJoinBox.style.display = 'block';
	};
	this.createLeagueStart = function() {
		this.createJoinBox.style.display = 'none';
		if (this.teamsNo == 0) {
			this.endCreateForm = true;
			this.inputBox.style.display = 'none';
			this.msgText.innerText = this.localMsgs.teamNecessary;
			this.okButton.style.display = 'inline-block';
			this.infoBox.style.display = 'block';
			this.containerBox.style.display = 'block';
		}
		else {
			this.containerBox.style.display = 'block';
		}
	};
}

CreateLeagueManager.prototype.initNameChars = function() {

	function genCharArray(charA, charZ) {
		var a = [], i = charA.charCodeAt(0), j = charZ.charCodeAt(0);
		for (; i <= j; ++i) {
			a.push(String.fromCharCode(i));
		}
		return a;
	}

	var arr1 = genCharArray('a', 'z');
	var arr2 = genCharArray('A', 'Z');
	var arr3 = genCharArray('α', 'ω');
	var arr4 = genCharArray('Α', 'Ω');
	var arr5 = genCharArray('0', '9');
	var arr6 = ['_', '.', ' '];
	var arr7 =  arr1.concat(arr2, arr3, arr4, arr5, arr6);

	var hashTable = {}; //All objects in javascript are hash tables
	for (var i=0; i<arr7.length; i++) {
		hashTable[arr7[i]] = null; //add all the allowed chars as keys in the hash table. Values(null) are irrelevant.
	}
	return hashTable;
}

function LeaguesListManager() {
	this.table = document.getElementById('leagues_table');
	this.tbody = this.table.querySelector('tbody');

	this.addRow = function(id,name) {
		var row = this.tbody.insertRow(-1);
		var cell = row.insertCell(0);
		cell.innerText = name;
		cell.classList.add('league-link');
		cell.onclick = function() { leagueTableManager.displayLeague(id); };
	};
}

function LeagueTableManager() {
	this.currentLeagueID = -1;
	this.cachedLeagues = {};
	this.rowsNo = 0;
	this.displayedRowsNo = 0;
	this.total = false;
	this.errorCodes = {
		500: "Internal Server Error"
	};
	this.table = document.getElementById('league_table');
	this.thead = this.table.querySelector('thead');
	this.tbody = this.table.querySelector('tbody');
	this.title = this.thead.querySelector('span[id="league_name_title"]');

	this.undisplayRows = function(n) {
		var rows = this.tbody.children;
		var l = this.displayedRowsNo-1;
		for (var i=0; i<n; i++) {
			rows[l-i].style.display = 'none';
		}
	};
	this.removeRows = function(n) {
		for (var i=0; i<n; i++) {
			this.tbody.deleteRow(-1);
		}
	};
	this.displayRows = function(n) {
		var rows = this.tbody.children;
		var l = this.displayedRowsNo;
		for (var i=0; i<n; i++) {
			rows[l+i].style.display = '';
		}
	};
	this.addRows = function(n) {
		console.log('[addRows] Called with n: ' + n);
		var row;
		for (var i=0; i<n; i++) {
			row = this.tbody.insertRow(-1);
			row.insertCell(0); //team rank
			row.insertCell(1); //team name
			row.insertCell(2); //team score
		}

	};
	this.adjustRows = function(newValue) {
		console.log('[adjustRows] Called with parameter: ' + newValue);
		if (newValue < this.displayedRowsNo) {
			this.undisplayRows(this.displayedRowsNo - newValue);
			this.displayedRowsNo = newValue;
		}
		else if ((newValue > this.displayedRowsNo) && (newValue <=  this.rowsNo)) {
			this.displayRows(newValue - this.displayedRowsNo);
			this.displayedRowsNo = newValue;
		}
		else if (newValue > this.rowsNo) {
			this.displayRows(this.rowsNo - this.displayedRowsNo);
			this.addRows(newValue - this.rowsNo);
			this.displayedRowsNo = this.rowsNo = newValue;
		}
	};
	this.setBodyRow = function(rowI, contents) {
		var row = this.tbody.children[rowI];
		var cols = row.children;
		var l = cols.length;

		//if contents.legnth != row.length that's bad
		for (var i=0; i<l; i++) {
			cols[i].innerText = contents[i];
		}
	};
	this.createOrder = function(scores) {
		function swap(i,j) {
		   var tmp1 = copy[i];
		   var tmp2 = indxs[i];
		   copy[i] = copy[j];
		   indxs[i] = indxs[j];
		   copy[j] = tmp1;
		   indxs[j] = tmp2;
		}

		function partition(left, right) {
		   if (left < right) {
		      var pivot = copy[left + Math.floor((right - left) / 2)],
		      left_new = left,
		      right_new = right;

		      do {
		         while (copy[left_new] > pivot) ++left_new;
		         while (copy[right_new] < pivot) --right_new;
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

		var copy = scores.slice(0);
		var l = copy.length;
		var indxs = [];
		for (var i=0; i<l; i++) {
			indxs[i] = i;
		}

		partition(0, l-1);
		return indxs;
	};
	this.createLeagueOrders = function(id) {
		var league = this.cachedLeagues[id];
		league['orders'] = [];
		league.orders[0] = this.createOrder(league.scores[0]);
		league.orders[1] = this.createOrder(league.scores[1]);
	};
	this.setCachedLeague = function(id) {
		this.currentLeagueID = id;
		var league = this.cachedLeagues[id];
		console.log('[setCachedLeague] league: ' + league);
		var title = league.name + ' - ' + ((this.total)?'Σύνολο':'Αγωνιστική');
		this.title.innerText = title;
		var l = league.teamsNo;
		var k = (this.total)?0:1;
		var order = league.orders[k];
		var arr = [];

		this.adjustRows(l);
		for (var i=0; i<l; i++) {
			arr[0] = (i+1).toString() + '.';
			arr[1] = league.names[order[i]];
			arr[2] = league.scores[k][order[i]];
			this.setBodyRow(i, arr);
		}
	};
	this.toggleOrder = function() {
		console.log('[leagueTableManager.toggleOrder]');
		this.total = !(this.total);
		var id = this.currentLeagueID;
		this.setCachedLeague(id);
	};
	this.displayLeague = function(id) {
		console.log('[displayLeague] Called for leagueID: ' + id);
		if (this.cachedLeagues.hasOwnProperty(id)) {
			this.setCachedLeague(id);
		}
		else {
			var xhttp = new XMLHttpRequest();
			var that = this;
			xhttp.onreadystatechange = function() {
			   if (this.readyState == 4 && this.status == 200) {
			      var response = this.responseText;
			      console.log('[XMLHttpRequest] Got response from server: ' + response);
			      if (that.errorCodes.hasOwnProperty(response)) {
			         alert("Server responded with error message '" + that.errorCodes[response] + "'. Please try again later.");
			      }
			      else {
			         that.cachedLeagues[id] = JSON.parse(response);
			         that.createLeagueOrders(id);
			         that.setCachedLeague(id);
			      }
			   }
			};
			xhttp.open("GET", "request_league.php?id=" + id, true);
			xhttp.send();
		}
	};
}

function JoinLeagueManager(teamsNo) {
	this.teamsNo = teamsNo;
	this.serverCodes = {
		1: "Λάθος όνομα λίγκας!",
		2: "Λανθασμένος κωδκός λίγκας!",
		3: "Η προθεσμία ένταξης στην λίγκα έχει παρέλθει!",
		4: "Συνέβη κάποιο σφάλμα εξυπηρετητή. Παρακαλώ δοκιμάστε ξανά.",
		5: "Προστεθήκατε στη λίγκα με επιτυχία!",
		6: "Μία ομάδα σας βρίσκεται ήδη στη λίγκα!"
	};
	this.localMsgs = {
		teamNecessary:   "Δεν έχετε καμία ομάδα. Πρέπει πρώτα να δημιουργήσετε μία!",
		noTeamSelected:  "Επιλέξετε ποια από τις ομάδες σας θα συμμετάσχει στη λίγκα." +
				 "Αν δεν έχετε καμία ομάδα θα πρέπει να δημιουργήσετε πρώτα μία",
		waitingResponse: "Γίνεται προσθήκη της ομάδας στη λίγκα..."
	};
	this.containerBox = document.getElementById("join_league_form");
	this.inputBox = document.getElementById("join_league_input_box");
	this.infoBox = document.getElementById("join_league_info_box");
	this.msgText = this.infoBox.querySelector('p');
	this.okButton = this.infoBox.querySelector('a');
	this.joinCreateBox = document.getElementById('create_join_league');
	this.lastServerResponse = -1;
	this.serverDelimeter = "-";
	this.endJoinForm = false;

	this.addTeamToLeague = function() {
		console.log('[addTeamToLeague]');
		var leagueName = document.getElementById("join_league_name").value;
		var leaguePass = document.getElementById("join_league_pass").value;
		var teamID = document.getElementById("join_league_team").value;
		if (teamID === "-1") {
			this.inputBox.style.display = 'none';
			this.infoBox.style.display = 'block';
			this.msgText.innerText = this.localMsgs.noTeamSelected;
		}
		else {
			this.inputBox.style.display = 'none';
			this.infoBox.style.display = 'block';
			this.msgText.innerText = this.localMsgs.waitingResponse;
			var xhttp = new XMLHttpRequest();
			var that = this;
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var response = this.responseText;
					var splitted = response.split(that.serverDelimeter);
					console.log('[addTeamToLeague] Got server response: ' + response);
					that.lastServerResponse = parseInt(splitted[0]);
					that.msgText.innerText = that.serverCodes[splitted[0]];
					that.okButton.style.display = 'inline-block';
					if (splitted[0] == "5") {
						leaguesListManager.addRow(splitted[1], leagueName);
						that.endJoinForm = true;
					}
				}
			};
			xhttp.open("POST", "add_team_to_league.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("leagueName=" + leagueName + "&leaguePass=" + leaguePass + "&teamID=" + teamID);
		}
	};
	this.confirmServerResponse = function() {
		this.infoBox.style.display = 'none';
		this.okButton.style.display = 'none';
		this.msgText.innerText = '';
		if (this.endJoinForm) {
			this.containerBox.style.display = 'none';
			this.inputBox.style.display = 'block';
			this.joinCreateBox.style.display = 'block';
			//this.lastServerResponse = -1;
			this.endJoinForm = false;
		}
		else {
			this.inputBox.style.display = 'block';
		}
	};
	this.cancelJoin = function() {
		this.containerBox.style.display = 'none';
		this.joinCreateBox.style.display = 'block';
	}
	this.joinLeagueStart = function() {
		this.joinCreateBox.style.display = 'none';
		if (this.teamsNo == 0) {
			this.endJoinForm = true;
			this.inputBox.style.display = 'none';
			this.containerBox.style.display = 'block';
			this.msgText.innerText = this.localMsgs.teamNecessary;
			this.okButton.style.display = 'inline-block';
			this.infoBox.style.display = 'block';
		}
		else {
			this.containerBox.style.display = 'block';
		}
	};
}


var leagueTableManager = new LeagueTableManager();
var joinLeagueManager = new JoinLeagueManager(<?php echo json_encode($teams_no) ?>);
var leaguesListManager = new LeaguesListManager();
var createLeagueManager = new CreateLeagueManager(<?php echo json_encode($teams_no) ?>);

</script>
</html>
