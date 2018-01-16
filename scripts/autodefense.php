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
$scheduler_json = Automate::factory()->getScheduler();
$snob_origin = Automate::factory()->getFlagSnobJson($config['autodefense']['flag_file']);
$snobs = Automate::factory()->getFlag(); // own snob/flag file
$defense = Array();

$snobs = updateSnob ($snob_origin, $snobs);

function updateSnob (&$snob_origin, &$snobs) {
	foreach($snob_origin as $user => $users) {
		foreach($users as $colony => $colonies) {
			foreach($colonies as $unixtime => $attack) {
				// Check if exist 
				
			}
		}
	}
}


// Auto-defense is active
// if (isset($config['autodefense']) && isset($config['autodefense']['active'])) {
// 	if (count($snobs) > 0) {
// 		foreach($snobs as $user => $users) {
// 			foreach($users as $colony => $colonies) {
// 				foreach($colonies as $unixtime => $attack) {
// 					// Check own previous support
// 					if (isset($attack['support']) && in_array($config['player'], $attack['support'])) {
// 						echo "Exists previous support to {$colony} at {$attack['arrival']}<br/>";
// 					} else {
// 						// Check colonies and distance
// 						$count_colonies = 0;

// 						foreach ($villages as $village_id => $village) {
// 							$from = Array();
// 							$from['x'] = $village['x'];
// 							$from['y'] = $village['y'];
// 							// Get min speed
// 							$speed = Automate::factory()->speed_troops($config['autodefense']['troops'], 'A');
// 							// Get distance
// 							$distance = Automate::factory()->getDistance($from, $attack['coords'], $speed);

// 							// send defense with a defined max range and max colonies to send defenses
// 							if ($distance <= $config['autodefense']['max_range'] && $count_colonies < $config['autodefense']['max_colonies']) {
// 								// get departure time to send defense
// 								$start = $unixtime - $distance;
// 								$mileseconds = ($count_colonies) * $config['autodefense']['miliseconds'];
// 								// Add miliseconds
// 								$start = "{$start}.{$mileseconds}";

// 								// Create defense to scheduler
// 								$defense[$start] = Array();
// 								$_support = Array();
// 								$_support["from"] = $from;
// 								$_support["from"]["id"] = $village_id;
// 								$_support["to"] = $attack['coords'];
// 								$_support["to"]["id"] = "";
// 								$_support["iteration"] = 0;
// 								$_support["method"] = "support";
// 								$_support["datetime"] =  date('m/d/Y H:i:s', $unixtime);
// 								$_support["departure"] =  date('m/d/Y H:i:s', $start);
// 								$_support["kata_target"] = "";
// 								$_support["troops"] = $config['autodefense']['troops'];
								
// 								array_push($defense[$start], $_support);
// 								// Add own support
// 								if (!isset($snobs[$user][$colony][$unixtime]["support"])) {
// 									$snobs[$user][$colony][$unixtime]["support"] = Array();
// 								}
// 								array_push($snobs[$user][$colony][$unixtime]["support"], $config['player']);
// 								$count_colonies ++;
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}

// 		echo '<pre>';
// 		print_r($defense);
// 		echo '</pre>';

// 		// Save auto-defense to scheduler json file
// 		if (count($defense) > 0) {
// 			$to_save = array_merge($scheduler_json, $defense);
// 			ksort($to_save, SORT_NUMERIC); //order by departure time ASC
// 			if ($f = fopen("../".$paths['scheduler'], 'w')) {
// 				fwrite($f, json_encode($to_save));
// 				fclose($f);
// 				Automate::factory()->log('F', "Scheduler auto-defense to detected snoobs");
// 				echo "<b>Scheduler auto-defense to detected snoobs</b><br>";
// 			} else {
// 				Automate::factory()->log('E', "You don't have permission to write {$paths['scheduler']} file");
// 				echo "<b>You don't have permission to write {$paths['scheduler']} file</b><br>";
// 			}
// 		}

// 		// Update flag json file with the user support
// 		if (count($defense) > 0) {
// 			if ($f = fopen("../".$paths['flag'], 'w')) {
// 				fwrite($f, json_encode($snobs));
// 				fclose($f);
// 				Automate::factory()->log('F', "Add user support to flag json file");
// 				echo "<b>Add user support to flag json file</b><br>";
// 			} else {
// 				Automate::factory()->log('E', "You don't have permission to write {$paths['flag']} file");
// 				echo "<b>You don't have permission to write {$paths['flag']} file</b><br>";
// 			}
// 		}
// 	}
// }
?>