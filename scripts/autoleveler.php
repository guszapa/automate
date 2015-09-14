<?php
$start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent

// Simulate human iteraction
if (!isset($_GET['start'])) {
	sleep(rand(30,120));
}
// Check if scheduler is running
if (Automate::factory()->isScheduler()) {
	Automate::factory()->log('E', "Scheduler script is running");
	exit();
}

/* OK!
 * building's autoleveler
 */
$villages_json = Automate::factory()->getVillages();
$villages = json_decode(Automate::factory()->getAutoleveler(), TRUE);
$rules = json_decode(Automate::factory()->getBuildingsRules(), TRUE);
$paths = Automate::factory()->getPaths();
$config = Automate::factory()->getConfig();
$save = $ended = false;
$time = array(); // if queue is full, check the timestamp to remove
$_updated = time();

// Remove buildings finished first
foreach ($villages as $village_id => $queue) {
	// Get last building time and check buildings ended
	foreach($queue['working'] as $i => $working) {
		foreach($working as $name => $_data) {
			$time[$village_id] = $_data['end'];
			// check the timestamp to remove it
			if (time() > $_data['end']) {
				// Change current level
				$villages[$village_id]['buildings'][$name]++;
				// Remove building queue
				unset($villages[$village_id]['working'][$i]);
				$ended = true;
				$time[$village_id] = time(); // reset time
			}
		}
	}
}
// Add Buildings to build
foreach ($villages as $village_id => $queue) {
	$_time = 0;	
	// Add new building to working
	if (count($queue['working']) < 3) {
		$_max_building = (int)$config['max_building_queue'] - count($queue['working']);
		for ($i=0; $i < $_max_building; $i++) {
			// Get name and value to upgrade
			if (isset($queue['queue'][$i])) {
				$build = $queue['queue'][$i];
				foreach ($build as $name => $level) {
					// Calculate current materials from last updated and building level
					$_buildIt = true;
					if (isset($villages_json['own'][$village_id]['updated'])) {
						foreach($villages_json['own'][$village_id]['materials'] as $material => $value) {
							$_materialInHour = $rules['buildings'][$material]['feature'][$villages[$village_id]['buildings'][$material]-1]*$config['speed'];
							$_inSeconds = round(($_materialInHour/60)/60, 4, PHP_ROUND_HALF_DOWN);
							$_materialDiff = round(($_updated - $villages_json['own'][$village_id]['updated'])*$_inSeconds, 0, PHP_ROUND_HALF_DOWN);
							$_current = $villages_json['own'][$village_id]['materials'][$material] + $_materialDiff;
							if ($_current < $rules['buildings'][$name][$material][$level-1]) {
								$_buildIt = false;
								break;
							}
						}
					}
					if ($_buildIt) {
						$param = array(
							'id' => $village_id,
							'name' => $name,
							'stone' => $rules['buildings'][$name]['stone'][$level-1],
							'wood' => $rules['buildings'][$name]['wood'][$level-1],
							'iron' => $rules['buildings'][$name]['iron'][$level-1]
						);
						$_res = @Automate::factory()->autoLeveler($param);
						// Remove settlers from $_res array and reassign
						if (isset($_res['settlers'])) {
							$_settlers = $_res['settlers'];
							unset($_res['settlers']);
						}
						if (!isset($_res['error'])) {
							if ($_time == 0) {
								$_time = isset($time[$village_id]) ? $time[$village_id] : time();
							}
							// Get time for last building queued or get now if empty
							$_time += $rules['buildings'][$name]['buildTime'][$level-1]*($rules['buildings']['main']['feature'][$queue['buildings']['main']]/100)*$rules['timeMultipler'];
							$villages[$village_id]['working'][] = array($name => array('level' => $level, 'end' => $_time));
							// Remove building from queue
							array_shift($villages[$village_id]['queue']);
							// Update materials and settlers on villages data
							if (!empty($_settlers)) {
								$villages_json['own'][$village_id]['settlers'] = $_settlers;
							}
							$villages_json['own'][$village_id]['materials'] = $_res;
							$villages_json['own'][$village_id]['updated'] = $_updated;
							$save = true;
							sleep(rand(3,6)); // wait to next building
						} else {
							unset($_res['error']);
							if (!empty($_settlers)) {
								$villages_json['own'][$village_id]['settlers'] = $_settlers;
							}
							$villages_json['own'][$village_id]['materials'] = $_res;
							$villages_json['own'][$village_id]['updated'] = $_updated;
							// Break loop
							break 2;
						}
					} else {
						break 2;
					}
				}
			}
		}
	}
}
// Save autoleveler
if ($ended || $save) {
	if ($f = fopen(dirname(dirname(__FILE__))."/{$paths['autoleveler']}", 'w')) {
		fwrite($f, json_encode($villages, TRUE));
		fclose($f);
	} else {
		Automate::factory()->log('E', "You don't have permission to write {$paths['autoleveler']} file");
	}
	if ($f = fopen(dirname(dirname(__FILE__))."/{$paths['villages']}", 'w')) {
		fwrite($f, json_encode($villages_json, TRUE));
		fclose($f);
	} else {
		Automate::factory()->log('E', "You don't have permission to write {$paths['villages']} file");
	}
}

$end = microtime(true);
$execution_time = round($end-$start, 4);
echo "$execution_time seconds";
if (isset($_GET['start'])) {
	echo "<br><br>";
	echo "<a href='{$config['localhost']}'>Go to index</a>";
}
?>