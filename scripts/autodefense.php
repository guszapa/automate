<?php
$start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent

// Simulate human iteraction
if (!isset($_GET['start'])) {
	sleep(rand(1,10));
}
// Check if scheduler is running
if (Automate::factory()->isScheduler()) {
	Automate::factory()->log('E', "Scheduler script is running");
	exit();
}

$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
$all_villages = Automate::factory()->getVillages(); // For auto-defense
$villages = Automate::factory()->getVillages('own'); // For auto-defense
$scheduler_json = Automate::factory()->getScheduler();
$snob_origin = Automate::factory()->getFlagSnobJson($config['autodefense']['flag_file']);
$snobs = Automate::factory()->getFlag(); // own snob/flag file
$defense = Array();

// update snob
function updateSnob (&$snob_origin, &$snobs) {
	$_time = time();
	// Remove old attacks previously
	foreach($snobs as $user => $users) {
		foreach($users as $colony => $colonies) {
			foreach($colonies as $unixtime => $attack) {
				if ($_time >= $unixtime) {
					unset($snobs[$user][$colony][$unixtime]);
				}
			}
		}
	}
	// Add new snobs on ally flag
	foreach($snob_origin as $user_origin => $users_origin) {
		foreach($users_origin as $colony_origin => $colonies_origin) {
			foreach($colonies_origin as $unixtime_origin => $attack_origin) {
				$match = false;
				// Check if exist on the own snob/flag json file
				foreach($snobs as $user => $users) {
					foreach($users as $colony => $colonies) {
						foreach($colonies as $unixtime => $attack) {
							// if has the same unixtame and has previous support, mark as match
							if ($unixtime == $unixtime_origin && isset($attack['support'])) {
								$match = true;
							}
						}
					}
				}

				// save attack if not matches
				if (!$match) {
					// if not exists user add all users data
					if (!isset($snobs[$user_origin])) {
						$snobs[$user_origin] = $users_origin;
					}
					// if not exists colony add all colonies data
					if (!isset($snobs[$user_origin][$colony_origin])) {
						$snobs[$user_origin][$colony_origin] = $colonies_origin;
					}
					// if not exists the same attack all attack sata
					if (!isset($snobs[$user_origin][$colony_origin][$unixtime_origin])) {
						$snobs[$user_origin][$colony_origin][$unixtime_origin] = $attack_origin;
					}
					// if exists increase one more snob
					else {
						$snobs[$user_origin][$colony_origin][$unixtime_origin]["quantity"] = $attack_origin["quantity"]+1;
					}
				}
			}
		}
	}
}

// check if the village has required troops
// OK
function hasTroops (&$villages, $village_id, $troops) {
	$hasTroops = 0;
	$village_troops = $villages[$village_id]["troops"];

	foreach ($village_troops as $troop => $value) {
		if (isset($troops[$troop])) {
			if ($value >= $troops[$troop]) {
				$hasTroops++;
			}
		}
	}
	return $hasTroops == count($troops);
}
// OK
function reduceTroops (&$villages, $village_id, $troops) {
	$village_troops = $villages[$village_id]["troops"];

	foreach ($village_troops as $troop => $value) {
		if (isset($troops[$troop])) {
			$villages[$village_id]['troops'][$troop] -= $troops[$troop];
		}
	}
}

// Update snobs vs flag/snobs from the flag account
updateSnob ($snob_origin, $snobs);

// Auto-defense is active
if (isset($config['autodefense']) && isset($config['autodefense']['active'])) {
	if (count($snobs) > 0) {
		foreach($snobs as $user => $users) {
			foreach($users as $colony => $colonies) {
				foreach($colonies as $unixtime => $attack) {
					// Check own previous support
					if (isset($attack['support']) && in_array($config['player'], $attack['support'])) {
						echo "Exists previous support to {$colony} at {$attack['arrival']}<br/>";
					} else if (isset($attack['transfer']) && $attack['transfer']) {
						echo "Transfer colony between same alliance<br/>";
					} else {
						// Check colonies and distance
						$count_colonies = 0;
						foreach ($villages as $village_id => $village) {
							// Check if the village has troops
							if (hasTroops($villages, $village_id, $config['autodefense']['troops'])) {
								$from = Array();
								$from['x'] = $village['x'];
								$from['y'] = $village['y'];
								// Get min speed
								$speed = Automate::factory()->speed_troops($config['autodefense']['troops'], 'A');
								// Get distance
								$distance = Automate::factory()->getDistance($from, $attack['coords'], $speed);

								// send defense with a defined max range and max colonies to send defenses
								if ($distance <= $config['autodefense']['max_range'] && $count_colonies < $config['autodefense']['max_colonies']) {
									// get departure time to send defense
									$start = $unixtime - $distance;

									// Wagons
									for($i=0; $i < $config['autodefense']['wagons']; $i++) {
										$mileseconds = $i * $config['autodefense']['miliseconds'];
										// Add miliseconds
										$_start_temp = explode(".", $start);
										$start = "{$_start_temp[0]}.{$mileseconds}";

										// Create defense to scheduler
										$defense[$start] = Array();
										$_support = Array();
										$_support["from"] = $from;
										$_support["from"]["id"] = $village_id;
										$_support["to"] = $attack['coords'];
										$_support["to"]["id"] = "";
										$_support["iteration"] = 0;
										$_support["method"] = "support";
										$_support["datetime"] =  date('m/d/Y H:i:s', $unixtime);
										$_support["departure"] =  date('m/d/Y H:i:s', $start);
										$_support["kata_target"] = "";
										$_support["troops"] = $config['autodefense']['troops'];
										
										array_push($defense[$start], $_support);
										// Add own support
										if (!isset($snobs[$user][$colony][$unixtime]["support"])) {
											$snobs[$user][$colony][$unixtime]["support"] = Array();
										}
										array_push($snobs[$user][$colony][$unixtime]["support"], $config['player']);
										// reduce troops for next iteration
										reduceTroops($villages, $village_id, $config['autodefense']['troops']);
									}
									$count_colonies ++;
								}
							} else {
								echo "<b>You don't have requiere troops on the village: {$village['name']}</b><br>";
							}
						}
					}
				}
			}
		}

		echo '<pre>';
		print_r($defense);
		echo '</pre>';

		// Save auto-defense to scheduler json file
		// OK
		if (count($defense) > 0) {
			if (is_array($scheduler_json)) {
				$to_save = array_merge($scheduler_json, $defense);
			} else {
				$to_save = $defense;
			}

			ksort($to_save, SORT_NUMERIC); //order by departure time ASC
			if ($f = fopen(dirname(dirname(__FILE__))."/{$paths['scheduler']}", 'w')) {
				fwrite($f, json_encode($to_save));
				fclose($f);
				Automate::factory()->log('F', "Scheduler auto-defense to detected snoobs");
				echo "<b>Scheduler auto-defense to detected snoobs</b><br>";
			} else {
				Automate::factory()->log('E', "You don't have permission to write {$paths['scheduler']} file");
				echo "<b>You don't have permission to write {$paths['scheduler']} file</b><br>";
			}
		}

		// Update village json file with the current troops
		// OK
		if (count($defense) > 0) {
			if ($f = fopen(dirname(dirname(__FILE__))."/{$paths['villages']}", 'w')) {
				$all_villages['own'] = $villages;
				fwrite($f, json_encode($all_villages));
				fclose($f);
				Automate::factory()->log('F', "Update villages troops json file");
				echo "<b>Update villages troops json file</b><br>";
			} else {
				Automate::factory()->log('E', "You don't have permission to write {$paths['villages']} file");
				echo "<b>You don't have permission to write {$paths['villages']} file</b><br>";
			}
		}
	}

	// Update flag json file with the user support
	// OK
	if ($f = fopen(dirname(dirname(__FILE__))."/{$paths['flag']}", 'w')) {
		fwrite($f, json_encode($snobs));
		fclose($f);
		Automate::factory()->log('F', "Update own flag json file");
		echo "<b>Update own flag json file</b><br>";
	} else {
		Automate::factory()->log('E', "You don't have permission to write {$paths['flag']} file");
		echo "<b>You don't have permission to write {$paths['flag']} file</b><br>";
	}
}
?>