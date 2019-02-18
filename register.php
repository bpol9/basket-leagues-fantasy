<?php

$db_server = "localhost";
$db_uname = "spanoulis";
$db_pass = "calathes";
$db_name = "basket_league_fantasy";

$conn = new mysqli($db_server, $db_uname, $db_pass, $db_name);
if ($conn->connect_error != NULL) {
	die("Connection to database failed");
}
$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$reg_date = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);


$sql = "SELECT Username, Email " .
	"FROM Account " .
	"WHERE Username='" . $username . "' OR Email='" . $email . "'";
$res = $conn->query($sql);

if ($res->num_rows > 0) { //Username or email already in database, check first username
	$row = $res->fetch_assoc();
	if ($row['Username'] === $username) { //Username taken
		echo "1";
	}
	else if ($row['Email'] === $email) { //Email already registered
		echo "2";
	}
}
else { //Username, email are not in the database
	$sql = "INSERT INTO Account (Username, Password, Email, Reg_date) " .
		"Values('" . $username . "','" . $password . "','" . $email . "','" . $reg_date . "')";
	if ($conn->query($sql) === FALSE) { //Insertion failed for some reason
		echo "3";
	}
	else { //Success
		echo "4";
	}
}

$conn->close();

?>
