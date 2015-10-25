<?php
$_start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent

// Simulate human iteraction
if (!isset($_GET['player'])) {
    $_cron = true;
    sleep(rand(60,240));
} else {
    $_cron = false;
}
// Check if scheduler is running
if (Automate::factory()->isScheduler()) {
    Automate::factory()->log('E', "Scheduler script is running");
    exit();
}

$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
$villages = Automate::factory()->getVillages();
// Get variables
$_first_village = isset($_GET['first_village']) ? $_GET['first_village'] : false;
$_player = isset($_GET['player']) ? $_GET['player'] : $config['player'];
$_player_id = isset($_GET['player_id']) ? $_GET['player_id'] : $config['user_id'];
$_type = isset($_GET['type']) ? $_GET['type'] : 'own';
$_unixtime = time();

// Loop for tracking players
$__url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?{$config['info_player']}{$_player_id}";
$__res = @Automate::factory()->tracking($__url, false, true);
if (is_array($__res) && count($__res) > 0) {
	$__res = $__res['villages'];
    if (!isset($villages[$_type])) $villages[$_type] = array();
    foreach ($__res as $update_villages) {
        if (count($update_villages)>0) {
            if (isset($update_villages['id'])) {
                // Create new village if doesn't exists
                if (!isset($villages[$_type][$update_villages['id']])) {
                    $villages[$_type][$update_villages['id']] = Array();
                }
                $villages[$_type][$update_villages['id']]['id'] = $update_villages['id'];
                $villages[$_type][$update_villages['id']]['name'] = $update_villages['name'];
                if ($_type != 'own') {
                    $villages[$_type][$update_villages['id']]['player_id'] = $_player_id;
                    $villages[$_type][$update_villages['id']]['player_name'] = $_player;
                }
                $villages[$_type][$update_villages['id']]['x'] = $update_villages['x'];
                $villages[$_type][$update_villages['id']]['y'] = $update_villages['y'];
                $villages[$_type][$update_villages['id']]['points'] = $update_villages['points'];
                $villages[$_type][$update_villages['id']]['updated'] = $_unixtime;
                if (!isset($villages[$_type][$update_villages['id']]['troops'])) $villages[$_type][$update_villages['id']]['troops'] = array();
                if (!isset($villages[$_type][$update_villages['id']]['type'])) $villages[$_type][$update_villages['id']]['type'] = array();
            }
        }
    }

    // Save data
    $filename = dirname(dirname(__FILE__))."/{$paths['villages']}";
    if ($f = fopen($filename, 'w')) {
        fwrite($f, json_encode($villages));
        fclose($f);
    } else {
        Automate::factory()->log('E', "You don't have permission to write {$paths['villages']} file");
    }
}
// Update troops, materials and map only for own villages
if ($_first_village || $_cron) {
    sleep(rand(1,3));
    foreach ($villages['own'] as $village_id => $village) {
        // get own village data
        $url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?village={$village_id}{$config['overview']}";
        $village_data = @Automate::factory()->tracking($url, false, true, true);

        // Parse data
        if (is_array($village_data) && count($village_data) > 0) {
            $villages['own'][$village_id]['settlers'] = $village_data['settlers'];
            $villages['own'][$village_id]['materials'] = $village_data['materials'];
            $villages['own'][$village_id]['buildings'] = $village_data['buildings'];
            $_troops = explode('|', $village_data['settlement']);
            $villages['own'][$village_id]['troops'] = array(
                "farmer" => (int)$_troops[3],
                "sword" => (int)$_troops[4],
                "spear" => (int)$_troops[5],
                "axe" => (int)$_troops[6],
                "bow" => (int)$_troops[7],
                "spy" => (int)$_troops[8],
                "light" => (int)$_troops[9],
                "heavy" => (int)$_troops[10],
                "ram" => (int)$_troops[11],
                "kata" => (int)$_troops[12],
                "snob" => (int)$_troops[13]
            );
        }
        // Get map
        $url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/minimap.php?x={$village['x']}&y={$village['y']}";
        Automate::factory()->villageMap($village_id, $url);
        sleep(rand(1,2));
    }
    // Save data
    $filename = dirname(dirname(__FILE__))."/{$paths['villages']}";
    if ($f = fopen($filename, 'w')) {
        fwrite($f, json_encode($villages, TRUE));
        fclose($f);
    } else {
        Automate::factory()->log('E', "You don't have permission to write {$paths['villages']} file");
    }
}

$_end = microtime(true);
$execution_time = round($_end-$_start, 4);
print("<br>$execution_time seconds<br>");
if (isset($_GET['player'])) {
    echo '<h3>Updated own villages!</h3>';
    echo "<a href='{$config['localhost']}adminvillages.php'>Go back to Villages</a>";
}
