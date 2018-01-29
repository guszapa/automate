<?php
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php";
echo Automate::factory()->getGame($url.'?'.$_SERVER['QUERY_STRING'], $_POST, true, false);
?>
<style type="text/css">
    html {
      background-color: #444;
      color:#eee;
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
    }
    #banner_container {
      display: none !important;
    }
</style>