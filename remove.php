<?php

if (!session_id()) session_start();

$pos = $_GET['pos'];
$index = $_GET['index'];
$team = $_GET['team'];
$playerId = $_GET['playerId'];
if (!isset($_SESSION['rc'])) $_SESSION['rc'] = 0;
$rc = $_SESSION['rc']++; //remove counter
$_SESSION['removed'][$pos][] = $index; //can be removed, because the information is also on the array below.
$_SESSION['removed'][$rc]['playerId'] = $playerId;
$_SESSION['removed'][$rc]['pos'] = $pos;
$_SESSION['removed'][$rc]['index'] = $index;
$_SESSION['removed'][$rc]['purchasePrice'] = $_SESSION[$pos][$index]['PurchasePrice'];

header("Location: team.php?team=" . urlencode($team));

?>
