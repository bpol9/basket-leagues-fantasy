$('.message a').click(function(){
   $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
});

var errorMessages = {
	shortLength: 'Ο κωδικός θα πρέπει να περιέχει τουλάχιστον 7 χαρακτήρες',
	atLeastOneNumber: 'Ο κωδικός θα πρέπει να περιέχει τουλάχιστον έναν αριθμό',
	passMismatch: 'Οι δύο κωδικοί δεν ταιριάζουν',
	badEmail: 'Μη έγκυρη διεύθυνση e-mail'
};

var serverCodeToMsg = {
	1: 'Το όνομα χρήστη χρησιμοποιείται ήδη',
	2: 'Το email έχει ήδη χρησιμοποιηθεί για άλλη εγγραφή',
	3: 'Συνέβη κάποιο λάθος στον εξυπηρετητή, παρακαλώ δοκιμάστε ξανά',
	4: 'Η εγγραφή έγινε με επιτυχία'
};

function onRegisterSubmitted() {
	console.log('[onRegisterSubmitted]');
	var uname = document.getElementById('reg_uname').value;
	var pass = document.getElementById('reg_pass').value;
	var confPass = document.getElementById('reg_conf_pass').value;
	var email = document.getElementById('reg_email').value;
	var p_tag = document.getElementById('warning_msg');
	p_tag.innerText = '';
	p_tag.style.color = 'red';
	if ((uname.legngth == 0) || (pass.length == 0) || (confPass.length == 0) || (email.length == 0)) {
		return; //it will be handled by 'required'
	}

	if (pass.length < 7) {
		p_tag.innerText = errorMessages.shortLength;
		return;
	}
	else if (!containsNumber(pass)) {
		p_tag.innerText = errorMessages.atLeastOneNumber;
		return;
	}
	else if (pass != confPass) {
		p_tag.innerText = errorMessages.passMismatch;
		return;
	}
	else if (!containsPapaki(email)) {
		p_tag.innerText = errorMessages.badEmail;
		return;
	} else { //Everything is good
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				console.log('[onRegisterSumbitted] server respose: ' + this.responseText);
				if (this.responseText == "4") {
					p_tag.style.color = 'green';

				}
				p_tag.innerText = serverCodeToMsg[this.responseText];
			}
		};
		xhttp.open("POST", "register.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send("username=" + uname + "&password=" + pass + "&email=" + email);
	}
}

function containsPapaki(str) {
	if (str.indexOf('@') == -1) {
		return false;
	} else {
		return true;
	}
}

function containsNumber(str) {
	return /\d/.test(str);
}
