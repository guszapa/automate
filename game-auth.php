<?php
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$url = "http://es.kingsage.gameforge.com/index.php?code=b68cc58838e368de346cb119f9fc5394&uid=522236&pass=92087d83192d880ddada1d02b313217b&md5=1&a=accountActivation&p=952e";
echo Automate::factory()->getGame($url.'?'.$_SERVER['QUERY_STRING'], $_POST, true, true);
?>
