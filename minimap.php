<?php
/**
 * Farm administration
 * @author katan
 */
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/minimap.php";

echo Automate::factory()->getGame($url.'?'.$_SERVER['QUERY_STRING'], null, true, true);
?>