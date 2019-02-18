<?php

function calc_score($players) {
	$poss = ['pgs', 'sgs', 'pfs'];
	$score = 0.0;
	foreach ($poss as $pos) {
		foreach ($players[$pos] as $player) {
			if ($player['LastWeekScore'] != NULL) $score += $player['LastWeekScore'];
		}
	}
	return $score;
}

function get_stat_leaders() {
	$sql = "SELECT * FROM StatsLeaders " .
		"ORDER BY Category ASC, Score DESC";
	$res = $GLOBALS['conn']->query($sql);
	$row = $res->fetch_assoc();
	while ($row != NULL) {
		$curr = $row['Category'];
		//$cats[] = $curr;
		$i=0;
		while ($curr === $row['Category']) {
			$stats[$curr]['Scores'][$i] = $row['Score'];
			$stats[$curr]['Names'][$i] = substr($row['FirstName'],0,1) . ". " . $row['LastName'];
			$i++;
			$row = $res->fetch_assoc();
			if ($row === NULL) break;
		}
	}
	return $stats;
}

?>

