<?php
/* REVISION !!
 * Scheduler attacks
 */
set_time_limit(0);
$_start = microtime(true);
$_limit = (strtotime("+1 min")-date('s',$_start));
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent
$attacks = Automate::factory()->getScheduler(); // return array sort by microtime
$paths = Automate::factory()->getPaths();

/** Get the attacks on the current cron **/
$_attacks = array();
$_human_iteration = array();

if (count($attacks) > 0) {
    foreach ($attacks as $microtime => $attack) {
        if ($microtime >= $_start && $microtime <= $_limit) {
            $_now = microtime(true);
            $_attacks[$microtime] = $attack;
        } else {
            continue;
        }
    }
}

if (count($_attacks) > 0) {
    $log = array();
    $proof = Automate::factory()->getProof();
    do {
        $_res = false;
        $_handles = array();
        foreach($_attacks as $microtime => $attack) {
            if ( $microtime <= $_now) {
                foreach ($attack as $k => $v) {
                    $origin = array('id' => $v['from']['id'], 'x' => $v['from']['x'], 'y' => $v['from']['y']);
                    $target = array('id' => $v['to']['id'], 'x' => $v['to']['x'], 'y' => $v['to']['y']);
                    if (empty($v['kata_target'])) {
                        $v['kata_target'] = false;
                    }
                    if ($v['iteration'] > 0) {
                        for($i = 0; $i < $v['iteration']; $i++) {
                            $_handles[] = Automate::factory()->schedulerAttack($origin, $target, $v['troops'], $v['method'], $v['kata_target'], TRUE, $proof);
                            $_toops = implode(',',$v['troops']);
                            $log[] = array('A', "{$v['method']} from {$v['from']['id']} [{$v['from']['x']}|{$v['from']['y']}], To: {$v['to']['x']}|{$v['to']['y']} with troops: {$_toops}");
                        }
                    } else {
                        $_handles[] = Automate::factory()->schedulerAttack($origin, $target, $v['troops'], $v['method'], $v['kata_target'], TRUE, $proof);
                        $log[] = array('A', "{$v['method']} from {$v['from']['id']} [{$v['from']['x']}|{$v['from']['y']}], To: {$v['to']['x']}|{$v['to']['y']}");
                    }
                }
                // Execute multi cURL
                @Automate::factory()->cURL_Multi_Exec($_handles);
                unset($_attacks[$microtime]); // Remove attacks
            }
        }
        usleep(10000); // wait 10 ms
        $_now = microtime(true);
    } while ( $_now < $_limit);

    // Remove file
    @unlink(dirname(dirname(__FILE__))."/{$paths['scheduler_flag']}");
    // Save log at the end
    if (count($log) > 0) {
        foreach($log as $v) {
            Automate::factory()->log($v[0], $v[1]);
        }
    }
} else {
    // Prepare the next attacks
    $sleep = 40000000;
    usleep($sleep); // wait 40 seconds
    $break = false;
    $proof = false;
    $log = array();
    $i = 1;
    // loop to search attacks on the next minuts
    do {
        $_now = '';
        $next_min = $i+1;
        $n_start = (strtotime("+{$i} min", $_start)-date('s',$_start)); // equal or more than
        $n_limit = (strtotime("+{$next_min} min", $_start)-date('s',$_start)); // less than
        // search attacks on the nexts minuts
        $_human_iteration = array();
        foreach ($attacks as $microtime => $attack) {
            if ($microtime >= $n_start && $microtime < $n_limit) {
                $_now = microtime(true);
                $_attacks[$microtime] = $attack;
                $_human_iteration[$microtime] = array(
                    'origin' => array('id' => $attack[0]['from']['id'], 'x' => $attack[0]['from']['x'], 'y' => $attack[0]['from']['y']),
                    'target' => array('id' => $attack[0]['to']['id'], 'x' => $attack[0]['to']['x'], 'y' => $attack[0]['to']['y']),
                    'troops' => $attack[0]['troops'],
                    'method' => $attack[0]['method'],
                    'kata_target' => $attack[0]['kata_target']
                );
            } else {
                continue;
            }
        }
        if (count($_human_iteration) > 0) {
            foreach ($_human_iteration as $attack) {
                if (empty($attack['kata_target'])) {
                    $attack['kata_target'] = false;
                }
                $proof = Automate::factory()->schedulerAttack($attack['origin'], $attack['target'], $attack['troops'], $attack['method'], $attack['kata_target']);
                if ($proof) {
                    $log[] = array('A', "$_now :: Preparing scheduler attack");
                    continue;
                } else {
                    $_now = microtime(true);
                    $log[] = array('E', "$_now :: Preparing scheduler attack");
                }
            }
        } else {
            $break = true;
        }
        $i++;
        usleep(100000); // 0,1 seconds
    } while (!$break);
    // Save proof & overwrite
    if ($proof) {
        $f = fopen(dirname(dirname(__FILE__))."/{$paths['proof']}", 'w+');
        if ($f) {
            fwrite($f, $proof);
            fclose($f);
        }
    }
    // Save log at the end
    if (count($log) > 0) {
        foreach($log as $v) {
            Automate::factory()->log($v[0], $v[1]);
        }
		// Make file to alert to others scripts for scheduler running
		$f = fopen(dirname(dirname(__FILE__))."/{$paths['scheduler_flag']}", 'w');
        if ($f) {
            fwrite($f, "running...");
            fclose($f);
            chmod(dirname(dirname(__FILE__))."/{$paths['scheduler_flag']}",0777);
        }
    }
}
$_end = microtime(true);
$execution_time = round($_end-$_start, 4);
echo "<br>$execution_time seconds<br>";
?>
