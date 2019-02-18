
<?php

function abort_transaction($msg="No debug message available.\n\n", $conn) {
	$conn->rollback();
	echo $msg;
	$conn->close();
	exit(1);
}

function find_max_score($arr) {
	$c = count($arr);
	$max = -1.0;
	$ret = -1;
	for ($i=0; $i<$c; $i++) {
	   $tmp = floatval($arr[$i]['Score']);
	   if ($tmp > $max) {
	      $max = $tmp;
	      $ret = $i;
	   }
	}
	return $ret;
}

$servername = "localhost";
$uname = "spanoulis";
$pass = "calathes";
$db = "basket_league_fantasy";

$conn = new mysqli($servername, $uname, $pass, $db);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$conn->autocommit(FALSE);
//$conn->begin_transaction(MYSQLI_TRANS_READ_WRITE);

$sql = "SELECT LastWeekScore, LastName, FirstName FROM Player";
$res = $conn->query($sql);
if ($res === FALSE) {
	abort_transaction("'" . $sql . "' returned FALSE.\n\n", $conn);
}
$row = $res->fetch_assoc();
$i=0;
while ($row != NULL) {
	$players[$i]['Score'] = $row['LastWeekScore'];
	$players[$i]['Fname'] = $row['FirstName'];
	$players[$i]['Lname'] = $row['LastName'];
	$i++;
	$row = $res->fetch_assoc();
}

$i=0;
while ($i < 5) {
	$j = find_max_score($players);
	$top5[$i]['FirstName'] = $players[$j]['Fname'];
	$top5[$i]['LastName'] = $players[$j]['Lname'];
	$top5[$i]['Score'] = $players[$j]['Score'];
	array_splice($players, $j, 1);
	$i++;
}

$ranks = ['1','2','3','4','5'];
$i = 0;
while ($i < 5) {
	$sql = "UPDATE TOP5 " .
		"SET FirstName = '". $top5[$i]['FirstName'] .
		"', LastName = '" . $top5[$i]['LastName'] .
		"', Score = " . $top5[$i]['Score'] .
		" WHERE Ranking = '" . $ranks[$i] . "'";
	if ($conn->query($sql) === FALSE) {
		abort_transaction("'" . $sql . "' returned FALSE.\n\n", $conn);
	}
	$i++;
}

$conn->commit();
$conn->close();

echo "Top5 table was updated successfully.\n\n";

?>
