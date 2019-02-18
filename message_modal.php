<!DOCTYPE html>
<html>
	<head>
		<title>Μηνύματα</title>
<style>
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
			padding: 0px 0px;
			border: 1px solid #34495E;
			border-radius: .4em;
			width: 27%;
			height: 15%;
		}

		.button {
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
		.msg {
			font-size: 13px;
			font-weight: bold;
			color: #585858;
		}
</style>
	</head>

<body>
	<h1>Alert messages with modals</h1>
	<button type="button" onclick="showMessage('confirm','15%')">Show Confirm</button>
	<button type="button" onclick="showMessage('alert','17%')">Show Alert</button>
	<div id="modal" class="modal">
		<div id="modal_content" class="modal-content">
			<div id="alert" style="display:none; text-align:center; padding-top:0px; width:100%">
				<p class="msg_title">Μη έγκυρο όνομα ομάδος</p>
				<p class="msg">Επιτρέπονται λατινικοί και ελληνικοί χαρακτήρες, και επιπλέον η τελεία(.), η κάτω παύλα(_) και το κενό.</p>
				<a class="button" onclick="hideMessage('alert')">Συγγνώμη</a>
			</div>
			<div id="confirm" style="display:none; text-align:center; padding-top:0px; width:100%">
				<p class="msg_title">Επιβεβαίωση</p>
				<p class="msg">Μετά την αποθήκευση της ομάδας θα έχετε το δικαίωμα μόνο για 3 επιπλέον αλλαγές.</p>
				<a class="button" onclick="hideMessage('confirm')">Σύμφωνοι</a>
				<a class="button" onclick="hideMessage('confirm')">Άκυρο</a>
			</div>
		</div>
	</div>

<script>
function showMessage(id,h) {
	document.getElementById('modal_content').style.height = h;
	document.getElementById(id).style.display = "block";
	document.getElementById('modal').style.display = "block";
}

function hideMessage(id) {
	document.getElementById(id).style.display = "none";
	document.getElementById('modal').style.display = "none";
}
</script>
</body>
</html>
