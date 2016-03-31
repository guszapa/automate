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
 * troops autoleveler
 */
$villages = Automate::factory()->getVillages('own');
$recruit = json_decode(Automate::factory()->getRecruit(), TRUE);
$rules = json_decode(Automate::factory()->getBuildingsRules(), TRUE);
$paths = Automate::factory()->getPaths();
$config = Automate::factory()->getConfig();
$_updated = time();

// function findTroop(&$troops) {
// 	$_troop = '';
// 	foreach ($troops as $key => $value) {
// 		if ((int)$value > 0) {
// 			$_troop = $key;
// 			break 1;
// 		}
// 	}
// 	return $_troop;
// }

/**
 * Calculate the number of troops can be recruit
 * @param  [Array] 	&$data
 * @param  [Array] 	&$rules
 * @param  [Array] 	&$troops
 * @param  [string] $troop
 * @return [number]				number of recruitable troops 
 */
function numberRecruit(&$data, &$rules, &$troops, $troop) {
	if ($troops[$troop] > 0) {
		$_max = 0;
		$_material;
		$_materialCount = 0;

		// search min. material
		foreach ($data['materials'] as $key => $value) {
			if ($_materialCount == 0 || $_materialCount > $value) {
				$_material = $key;
			}
		}
		// Search max. value
		foreach ($rules['troops'][$troop]['materials'] as $key => $value) {
			if ($_max == 0) {
				$_max = $value;
			} else {
				if ($_max < $value) {
					$_max = $value;
				}
			}
		}
		$_troopsRecruitable = floor($data['materials'][$_material]/$_max);
		$_recruit = $_troopsRecruitable > $troops[$troop] ? $troops[$troop] : $_troopsRecruitable;
		$_recruitable = (int)$data['settlers'] > $_max ? $_recruit : $data['settlers']-1;
		// Reduce materials
		$data['materials']['stone'] -= $_recruitable*$rules['troops'][$troop]['materials']['stone'];
		$data['materials']['wood'] -= $_recruitable*$rules['troops'][$troop]['materials']['wood'];
		$data['materials']['iron'] -= $_recruitable*$rules['troops'][$troop]['materials']['iron'];
		return $_recruitable;
	}
}

// Loop arround villages
foreach ($recruit as $village_id => $troops) {

	// Call game to get the required data like materials and settlers
	if ($data = Automate::factory()->getRecruitData($village_id)) {
		$remaining_troops = 0;

		// Loop arround troops to recruit
		foreach ($troops as $troop => $value) {

			// There're troops to recruit
			if ((int)$value > 0) {
				// Get the recruitable troop number
				$quantity = numberRecruit($data, $rules, $troops, $troop);

				if ($quantity > 0) {
					// Add troop and quantity to recruit
					$post[$troop] = $quantity;
					// Reduce troops
					$troops[$troop] = $recruit[$village_id][$troop] -= $quantity;
				}

				echo "<pre><b>troop:</b> {$troop}</pre>";
				echo "<pre><b>quantity:</b> {$quantity}</pre>";
				echo "<pre><b>Add troop:</b>"; print_r($post); echo "</pre>";
				echo "<pre><b>Current materials: </b> "; print_r($data['materials']); echo "</pre>";
				echo "<hr/>";
			}
			// remaining troops to recruit
			$remaining_troops += $recruit[$village_id][$troop];
			echo "<pre><b>remaining troops:</b> {$remaining_troops}</pre>";
			echo "<hr/>";
		}

		// remove row if doesn't have any troop to recruit
		if ($remaining_troops == 0) {
			unset($recruit[$village_id]);

			echo "<pre><b>Remove village without troops:</b> {$village_id}</pre>";
		}
	}


	// // There're troops for recruit, call to game to update information and get proof
	// if ($_count_troops > 0) {
	// 	if ($data = Automate::factory()->getRecruitData($village_id)) {

	// 		$post = Array();
	// 		$troop = '';
	// 		$quantity = 0;

	// 		echo "<pre><b>current troops:</b> "; print_r($troops); echo "</pre>";
	// 		echo "<pre><b>Count troops:</b> {$_count_troops}</pre>";
	// 		echo "<pre><b>Current materials: </b> "; print_r($data['materials']); echo "</pre>";
	// 		echo "<hr/>";

	// 		if (isset($troops['primary'])) {
	// 			$troop = $troops['primary'];
	// 			$quantity = numberRecruit($data, $rules, $troops, $troop);
	// 			$post[$troop] = $quantity; // Add troop and quantity to recruit

	// 			echo "<pre><b>primary troop:</b> {$troop}</pre>";
	// 			echo "<pre><b>quantity:</b> {$quantity}</pre>";
	// 			echo "<pre><b>Add troop:</b> "; print_r($post); echo "</pre>";
	// 			echo "<pre><b>Current materials: </b> "; print_r($data['materials']); echo "</pre>";
	// 			echo "<hr/>";

	// 		} else {
	// 			$troop = findTroop($troops);
	// 			$quantity = numberRecruit($data, $rules, $troops, $troop);
	// 			$post[$troop] = $quantity; // Add troop and quantity to recruit

	// 			echo "<pre><b>troop:</b> {$troop}</pre>";
	// 			echo "<pre><b>quantity:</b> {$quantity}</pre>";
	// 			echo "<pre><b>Add troop:</b>"; print_r($post); echo "</pre>";
	// 			echo "<pre><b>Current materials: </b> "; print_r($data['materials']); echo "</pre>";
	// 			echo "<hr/>";
	// 		}
	// 		// Reduce troops
	// 		$troops[$troop] = $recruit[$village_id][$troop] -= $quantity;

	// 		echo "<pre><b>Reduce troops:</b> {$troops[$troop]}</pre>";
	// 		echo "<pre><b>Current troops:</b> "; print_r($troops); echo "</pre>";
	// 		echo "<hr/>";

	// 		// If current primary troops has been a zero, change or delete row
	// 		if ($recruit[$village_id][$troop] == 0) {
	// 			$_troop = findTroop($troops);

	// 			echo "<pre><b>troop:</b> {$_troop}</pre>";

	// 			if ($_troop != '') {
	// 				$recruit[$village_id]['primary'] = $_troop;
	// 				$quantity = numberRecruit($data, $rules, $troops, $_troop);
	// 				$post[$_troop] = $quantity; // Add troop and quantity to recruit
	// 				// Reduce troops
	// 				$troops[$_troop] = $recruit[$village_id][$_troop] -= $quantity;

	// 				echo "<pre><b>change primary troop:</b> {$_troop}</pre>";
	// 				echo "<pre><b>quantity:</b> {$quantity}</pre>";
	// 				echo "<pre><b>Add troop:</b>"; print_r($post); echo "</pre>";
	// 				echo "<pre><b>Reduce troops:</b> {$troops[$_troop]}</pre>";
	// 				echo "<pre><b>Current materials: </b> "; print_r($data['materials']); echo "</pre>";
	// 				echo "<pre><b>Current troops:</b> "; print_r($troops); echo "</pre>";

	// 			} else {
	// 				unset($recruit[$village_id]); // remove row if doesn't have any troop to recruit

	// 				echo "<pre><b>Remove village without troops:</b> {$recruit[$village_id]}</pre>";
	// 				echo "<pre><b>Current troops:</b> "; print_r($troops); echo "</pre>";
	// 				echo "<pre><b>quantity:</b> {$quantity}</pre>";
	// 			}
	// 		}

	// 		echo "<hr>";
	// 		echo "<pre><b>Troops to recruit on game:</b> "; print_r($post); echo "</pre>";

	// 		sleep(rand(2,6));

	// 		/*
	// 		// Call to game to recruit troops
	// 		if ($html = Automate::factory()->Recruit($village_id, $post, $data['proof'])) {
	// 			// Revision error
	// 			if (preg_match('/<p class="error">.+<\/p>/', $html, $error)) {
	// 				// If has been error save on log
	// 				@Automate::factory()->log('E', "Recruit - " . strip_tags($error[0]));
	// 			} else {
	// 				// Save recruit data
	// 				if ($f = fopen(dirname(dirname(__FILE__))."/{$paths['recruit']}", 'w')) {
	// 					fwrite($f, json_encode($recruit, TRUE));
	// 					fclose($f);
	// 				} else {
	// 					@Automate::factory()->log('E', "Failed save data on file ".dirname(dirname(__FILE__))."/{$paths['recruit']}");
	// 				}
	// 				// Save log
	// 				$_troops_recruited = '';
	// 				foreach ($post as $key => $value) {
	// 					if ($_troops_recruited != '') $_troops_recruited .= ', ';
	// 					$_troops_recruited .= "{$key}: {$value}";
	// 				}
	// 				@Automate::factory()->log('R', " on {$villages[$village_id]['name']} with troops {$_troops_recruited}");
	// 			}
	// 		}
	// 		*/
	// 	}
	// }
}

$end = microtime(true);
$execution_time = round($end-$start, 4);
echo "$execution_time seconds";
if (isset($_GET['start'])) {
	echo "<br><br>";
	echo "<a href='{$config['localhost']}'>Go to index</a>";
}
?>
