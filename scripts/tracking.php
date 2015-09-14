<?php
$_start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent

// Simulate human iteraction
if (!isset($_GET['start'])) {
    sleep(rand(60,240));
}
// Check if scheduler is running
if (Automate::factory()->isScheduler()) {
    Automate::factory()->log('E', "Scheduler script is running");
    exit();
}

$tracking = Automate::factory()->getTracking();
$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();

// Loop for tracking players
foreach($tracking as $player_id => $data) {
   sleep(rand(4,8));
    if ($data['enabled']) {
        // Parse player info
        $res = @Automate::factory()->tracking("{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?{$config['info_player']}{$player_id}");
        if (is_array($res) && count($res) > 0) {
            // get previous data
            $_now = time();
            $_week = date('W'); $_year = date('Y');
            $path = dirname(dirname(__FILE__))."/{$paths['tracking_data']}/{$player_id}";
            $filename = $path."/{$_week}_{$_year}.json";
            // directory exist?
            if (!is_dir($path)) {
                mkdir($path, 0777);
            }
            // File exists?
            if (file_exists($filename)) {
                $_previous_data = json_decode(file_get_contents($filename), TRUE);
            } else {
                $_previous_data = array();
            }
            array_push($_previous_data, array($_now => $res)); // Add new data
            // Save data on weekly file
            if ($f = fopen($filename, 'w')) {
                fwrite($f, json_encode($_previous_data));
                fclose($f);
                @chmod($filename, 0777); // don't show errors
                // Overwrite data
                $_player_name = $tracking[$player_id]['name'];
                $_enabled = $tracking[$player_id]['enabled'];
                $tracking[$player_id] = $res;
                $tracking[$player_id]['name'] = $_player_name;
                $tracking[$player_id]['enabled'] = $_enabled;
                // Update player info
               if ($f = fopen(dirname(dirname(__FILE__))."/{$paths['tracking']}", 'w')) {
                    fwrite($f, json_encode($tracking));
                    fclose($f);
               } else {
                  Automate::factory()->log('E', "You don't have permission to write {$paths['tracking']} file");
               }
             } else {
                   Automate::factory()->log('E', "You don't have permission to write on {$filename} file");
            }
        } else {
            Automate::factory()->log('E', "Parse error when try to catch player info for tracking system");
        }
    } else {
        continue;
    }
}
$_end = microtime(true);
$execution_time = round($_end-$_start, 4);
print("<br>$execution_time seconds<br>");
if (isset($_GET['player'])) {
    echo '<h3>Updated stats!</h3>';
    echo "<a href='{$config['localhost']}admintracking.php'>Go back to tracking users</a>";
}
?>
