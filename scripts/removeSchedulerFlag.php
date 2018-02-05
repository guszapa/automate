<?php
$start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent
$paths = Automate::factory()->getPaths();
$config = Automate::factory()->getConfig();

$res = unlink(dirname(dirname(__FILE__))."/{$paths['scheduler_flag']}");
if ($res) {
	echo "<h3>The scheduler flag to prevent running other scripts has been removed successfully</h3><br>";
} else {
	echo "<h2 style='color: #f70000;'>Error trying to remove scheduler flag file</2><br>";
}

$end = microtime(true);
$execution_time = round($end-$start, 4);
echo "<br>$execution_time seconds";
if (isset($_GET['start'])) {
	echo "<br><br>";
	echo "<a href='{$config['localhost']}'>Go to index</a>";
}
?>