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
  <style type="text/css">
    html {
      background-color: #111;
      background-image: none !important;
      color:#ccc;
      font-size:11px;
    }
    img {
      opacity: 0.4;
    }

    a, a:visited, a:link {
      color: #f3f3f3 !important;
      text-decoration: underline !important;
    }
    .shortcut-element a, .quickstart_pane a {
      font-size: 12px !important;
    }
    input {
      color: #222 !important;
    }
    .background {
      display: none;
    }
    .click {
      color: #fff !important;
      text-decoration: underline !important;
    }
    #map th {
      color: #111;
    }
    #map td {
      color: #333; 
    }
    .map_colors td {
      color: #eee !important;
      font-size: 0.95em !important;
    }
    #map img, #minimap img {
      opacity: 0.8 !important;
      background: none !important;
    }
  </style>
  <body style="background-color: #111;">
    <?=print_r($output)?>
  </body>
</html>
