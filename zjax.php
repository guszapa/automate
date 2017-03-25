<?php
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/zjax.php";

echo Automate::factory()->getGame($url.'?'.$_SERVER['QUERY_STRING'], $_POST, true, true);
?>
