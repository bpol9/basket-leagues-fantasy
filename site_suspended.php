<!DOCTYPE html>

<html>
<head>
<title>Σε καταστολή</title>
</head>
<body>
<h1>Το site θα τεθεί και πάλι σε λειτουργία σύντομα</h1>

<?php
$d = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
$date1 = date_create_from_format('Y-m-d H:i:s', $d);
$date2 = date_create_from_format('Y-m-d H:i:s', '2018-12-8 15:30:00');
$interval = date_diff($date1, $date2, TRUE);
echo "You have $interval->y years, $interval->m months, $interval->d days, $interval->h hours, $interval->i minutes and $interval->s seconds left!";
?>

</body>
</html>
