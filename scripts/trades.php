<?php
$_start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent

// Simulate human iteraction
if (!isset($_GET['start'])) {
	sleep(rand(40,180));
}
// Check if scheduler is running
if (Automate::factory()->isScheduler()) {
	Automate::factory()->log('E', "Scheduler script is running");
	exit();
}

/** REVISION!
 * Check trades tu send it
 */
$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
$_trades = json_decode(Automate::factory()->getTrades(),TRUE);
$_tradings = json_decode(Automate::factory()->getTradings(),TRUE);
$_ownVillages = Automate::factory()->getVillages('own');
$_allyVillages = Automate::factory()->getVillages('ally');
$_villages = Array();

function array_merge_recursive_distinct(array &$array1, array &$array2)
{
    $merged = $array1;
    foreach ($array2 as $key => &$value)
    {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
        {
            $merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
        }
        else
        {
            $merged[$key] = $value;
        }
    }
    return $merged;
}

function mergeVillages(&$array1, &$array2) {
	$merged = $array1;
	foreach ( $array2 as $key => &$value ) {
		if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
	  		$merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
		} 
		else {
			$merged [$key] = $value;
		}
	}
	return $merged;
}

if (count($_trades) > 0) {
	// Merge villages
	$_villages = mergeVillages($_ownVillages, $_allyVillages);

	$now = time();
	$data = is_array($_tradings) ? $_tradings : Array();
	foreach($_trades as $key => $trade) {
		if (isset($_tradings[$trade['id']])) {
			// Check if has been disabled
			if (!isset($trade['enabled']) || $trade['enabled'] == "true") {
				// interval
				if ($trade['method'] == 'interval') {
					$end = strtotime("+{$trade['interval']} min", $_tradings[$trade['id']]['start']);
					if ($now > $end) {
						$data[$trade['id']] = $trade;
						$data[$trade['id']]['x'] = $_villages[$trade['to']]['x']; // Add coords
						$data[$trade['id']]['y'] = $_villages[$trade['to']]['y'];
						$data[$trade['id']]['start'] = time(); // Add time start
						unset($data[$trade['id']]['id'], $data[$trade['id']]['method'], $data[$trade['id']]['interval'], $data[$trade['id']]['lessthan'], $data[$trade['id']]['to']);
						
						$res = Automate::factory()->Trading($data[$trade['id']]); // Send trade
						if (!$res) {
							unset($data[$trade['id']]);
							Automate::factory()->log('E', "The trading with ID {$trade['id']} wasn't sended");
						} else {
							Automate::factory()->log('T', "From {$_villages[$trade['from']]['name']} to {$_villages[$trade['to']]['name']} with: {$trade['stone']} | {$trade['wood']} | {$trade['iron']}");
						}
					} else {
						continue;
					}
				} else {
					// Less than
				}
			} else {
				Automate::factory()->log('E', "The trading with ID {$trade['id']} has been disabled.");
			}
		} else {
			// New trade
			$data[$trade['id']] = $trade;
			$data[$trade['id']]['x'] = $_villages[$trade['to']]['x']; // Add coords
			$data[$trade['id']]['y'] = $_villages[$trade['to']]['y'];
			$data[$trade['id']]['start'] = time(); // Add time start
			unset($data[$trade['id']]['id'], $data[$trade['id']]['method'], $data[$trade['id']]['interval'], $data[$trade['id']]['lessthan'], $data[$trade['id']]['to']);
			
			$res = Automate::factory()->Trading($data[$trade['id']]); // Send trade
			if (!$res) {
				unset($data[$trade['id']]);
				Automate::factory()->log('E', "The trading with ID {$trade['id']} wasn't sended");
			} else {
				Automate::factory()->log('T', "From {$_villages[$trade['from']]['name']} to {$_villages[$trade['to']]['name']} with: {$trade['stone']} | {$trade['wood']} | {$trade['iron']}");
			}
		}
	}
	// Save tradings
	if (count($data) > 0) {
		if ($f = fopen(dirname(dirname(__FILE__))."/{$paths['trading']}", 'w')) {
		   fwrite($f, json_encode($data));
		   fclose($f);
		} else {
		   Automate::factory()->log('E', "You don't have permission to write {$paths['trading']} file");
		}
	}
}

// End
$_end = microtime(true);
$execution_time = round($_end-$_start, 4);
echo "<br>$execution_time seconds";
if (isset($_GET['start'])) {
	echo "<br><br>";
	echo "<a href='{$config['localhost']}'>Go to index</a>";
}
?>