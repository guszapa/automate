<?php
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?s=messages";
$messages = '';

/**
 * Controller
 */
$id = null;
if (! empty($_GET)) {
    $id = $_GET['id'];
    $url = $url."&m=in&id={$id}";
}
$messages = Automate::factory()->getMessages($url);
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
    <? if (isset($id)) : ?>
      <a href="<?=$config['localhost']?>messages.php">Back</a>
    <? else: ?>
      <a href="<?=$config['localhost']?>">Return to menu</a>
    <? endif; ?>
    </h4>
  </body>
</html>
