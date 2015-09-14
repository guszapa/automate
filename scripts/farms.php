<?php
$start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent

// Simulate human iteraction
if (!isset($_GET['start'])) {
	sleep(rand(50,180));
}
// Check if scheduler is running
if (Automate::factory()->isScheduler()) {
	Automate::factory()->log('E', "Scheduler script is running");
	exit();
}

/* OK!
 * Automate attacks
 */
$config = Automate::factory()->getConfig();
$allfarms = json_decode(Automate::factory()->getFarms(),TRUE);

foreach($allfarms as $id => $farm) {
	// Check if attack is echo
	if ($farm['enabled'] == 'true') {
		// Check nocturnal mode
		if ($farm['nocturnal'] == 'false') {
		  $mode = ($farm['mode'] == 'attack') ? 'A' : 'S';
		  if ( !Automate::factory()->getNocturnalMode($farm['from'], $farm['to'], $farm['troops'], $mode)) continue;
		}
		// Check range
		if (isset($farm['start']) && isset($farm['end'])) {
			if ((strlen($farm['start']) > 1) && (strlen($farm['end']) > 1)) {
				 $mode = ($farm['mode'] == 'attack') ? 'A' : 'S';
				 if ( !Automate::factory()->getRangeMode($farm['from'],$farm['to'], array('start' => $farm['start'], 'end' => $farm['end']), $farm['troops'], $mode)) continue;
			}
		}
		$res = false;
		$status = Automate::factory()->getAttackStatus($id);
		$mode = ($farm['mode'] == 'attack') ? 'attack' : 'espy';
		if ($farm['iteration'] > 0) {
			$_handles = array();
			if (is_bool($status) && $status) {
				// Connect to get proof and prepare the attack
				$proof = Automate::factory()->schedulerAttack($farm['from'], $farm['to'], $farm['troops'], $farm['mode']);
				for($i = 0; $i < $farm['iteration']; $i++) {
					$_handles[] = Automate::factory()->schedulerAttack($farm['from'], $farm['to'], $farm['troops'], $farm['mode'], FALSE, TRUE, $proof);
				}
				// Execute multi cURL
				sleep(3);
				$res = Automate::factory()->cURL_Multi_Exec($_handles);
			} else if (is_null($status)) {
				// Connect to get proof and prepare the attack
				$proof = Automate::factory()->schedulerAttack($farm['from'], $farm['to'], $farm['troops'], $farm['mode']);
				for($i = 0; $i < $farm['iteration']; $i++) {
					$_handles[] = Automate::factory()->schedulerAttack($farm['from'], $farm['to'], $farm['troops'], $farm['mode'], FALSE, TRUE, $proof);
				}
				// Execute multi cURL
				sleep(3);
				$res = Automate::factory()->cURL_Multi_Exec($_handles);
			}
			
		} else {
			// Only one attack
			if (is_bool($status) && $status) {
				// Resend attack
				$res = Automate::factory()->attack($farm['from'], $farm['to'], $farm['troops'], $mode);
			} else if (is_null($status)) {
				// Send new attack
				$res = Automate::factory()->attack($farm['from'], $farm['to'], $farm['troops'], $mode);
			}
		}
		// If attacks has been sended, save the attack on log and update the attacks list
		if ($res){
			$time = time();
			/**
			 * Register attack in attacks.json and xxxx.log
			 */
			$mode = ($mode == 'espy') ? 'S' : 'A';
			// If marked untracked attack, the attack doesn't saved
			if(!isset($farm['untracked']) || !$farm['untracked']) {
				Automate::factory()->save_attack($farm['from'], $farm['to'], $farm['troops'], $time, $mode, $id);
			}
			$_text = "from {$farm['to']['name']} ({$farm['to']['x']}|{$farm['to']['y']}) to {$farm['to']['name']} ({$farm['to']['x']}|{$farm['to']['y']}) with troops (".implode(',', $farm['troops']).')';
			$_text += $farm['iteration'] > 0 ? " x{$farm['iteration']}" : '';
			Automate::factory()->log($mode, $_text);
		}
	} else {
		continue; // The attack is disabled
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