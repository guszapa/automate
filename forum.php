<?php
/**
 * Farm administration
 * @author katan
 */
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/forum.php";
$output = '';

$output = Automate::factory()->getGame($url.'?'.$_SERVER['QUERY_STRING'], $_POST, true, false);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <meta name="viewport" content="width=device-width, user-scalable=no">
      <meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
      <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
      <title>Game</title>
  </head>
  <body style="background-color: #444;">
    <?=print_r($output)?>
    <br>
    <h4>
      <a href="<?=$config['localhost']?>forum.php">Back</a>
    </h4>
  </body>
</html>
