<?php
$start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent

// Simulate human iteraction
if (!isset($_GET['start'])) {
	sleep(rand(30,70));
}
// Check if scheduler is running
if (Automate::factory()->isScheduler()) {
	Automate::factory()->log('E', "Scheduler script is running");
	exit();
}

$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
$villages = Automate::factory()->getVillages('own'); // For auto-defense
$snobs = Automate::factory()->getFlag();
$defense = Array();

// echo '<pre>';
// print_r($villages);
// echo '</pre>';

// echo '<pre>';
// print_r($snobs);
// echo '</pre>';

// Auto-defense is active
if (isset($config['autodefense']) && isset($config['autodefense']['active'])) {
	if (count($snobs) > 0) {
		foreach($snobs as $user => $users) {
			foreach($users as $colony => $colonies) {
				foreach($colonies as $unixtime => $attack) {
					// Check own previous support
					if (isset($attack['support']) && isset($attack['support'][$config['player']])) {
						echo "Exists previous support to {$colony} at {$attack['arrival']}<br/>";
					} else {
						// Check colonies and distance
						foreach ($villages as $village_id => $village) {
							$from = Array();
							$from['x'] = $village['x'];
							$from['y'] = $village['y'];
							// Get min speed
							$speed = Automate::factory()->speed_troops($config['autodefense']['troops'], 'A');
							// Get distance
							$distance = Automate::factory()->getDistance($from, $attack['coords'], $speed);
							// send defense with a defined max range
							if ($distance <= $config['autodefense']['max_range']) {
								$_defense = Array();
								$_defense["from"] = $from;
								$_defense["from"]["id"] = $village_id;
								$_defense["to"] = $attack['coords'];
								$_defense["to"]["id"] = "";
								$_defense["start"] = $unixtime - $distance;
								$_defense["iteration"] = 0;
								$_defense["method"] = "support";
								$_defense["kata_target"] = "";
								$defense[] = $_defense;
							}
						}
					}
				}
			}
		}

		echo '<pre>';
		print_r($defense);
		echo '</pre>';
	}
}
?>