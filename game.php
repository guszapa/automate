<?php
/**
 * Farm administration
 * @author katan
 */
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php";
$output = '';

/**
 * Controller
 */
$querystring = '';
if (! empty($_GET)) {
    $querystring = $_SERVER['QUERY_STRING'];
}
$output = Automate::factory()->getGame($url+'?'+$querystring);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <meta name="viewport" content="width=device-width, user-scalable=no">
      <meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
      <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
      <title>Messages</title>
  </head>
  <body style="background-color: #444;">
    <?=print_r($messages)?>
    <br>
    <h4>
      <a href="<?=$config['localhost']?>game.php">Back</a>
    </h4>
  </body>
</html>
