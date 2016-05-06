<?php
/**
 * Farm administration
 *
 * @author katan
 */
include_once 'automate.class.php';
$villages_json = Automate::factory()->getVillages();
$buildings = json_decode(Automate::factory()->getBuildingsRules(), TRUE);
$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
// get first village
if (count($villages_json['own'])) {
    foreach ($villages_json['own'] as $village_id => $village) {
        $first_village_id = $village_id;
        break;
    }
}
$msg = '';
$error = false;
/**
 * Controller
 */
$action = null;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'dashboard';
if (! empty($_GET)) {
    $action = isset($_GET['action']) ? $_GET['action'] : NULL;
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $who = isset($_GET['who']) ? $_GET['who'] : null;
}
switch ($action) {
    case 'new-village': // OK
        if ( !empty($_POST)) {
            // Check empty values
            foreach ($_POST as $k => $v) {
                if ($k !== 'name') {
                    if (empty($v)) {
                        $msg = "The field <b>{$k}</b> can not be empty";
                        $error = true;
                        break(2);
                    }
                }
            }
            // Save villages
            if ( !$error) {
                 $village_json = array();
                 $new_village = $_POST;
                 $who = $new_village['who'];
                 unset($new_village['who']);
                 $villages_json[$who][$new_village['id']] = $new_village;
                if ($f = fopen($paths['villages'], 'w')) {
                    fwrite($f, json_encode($villages_json));
                    fclose($f);
                    $msg = "The village have been saved. <a href='{$config['localhost']}adminvillages.php?mode={$mode}'>refresh page</a>";
                } else {
                    Automate::factory()->log('E', "You don't have permission to write {$paths['villages']} file");
                }
            }
        }
        break;
    case 'delete': // OK
        if ($id) {
            $new_villages = $villages_json;
            unset($new_villages[$who][$id]);
            if ($f = fopen($paths['villages'], 'w')) {
                fwrite($f, json_encode($new_villages));
                fclose($f);
                $msg = "The village have been deleted. <a href='{$config['localhost']}adminvillages.php?mode={$mode}'>refresh page</a>";
                $villages_json = $new_villages; // Update deleted
            } else {
                Automate::factory()->log('E', "You don't have permission to write {$paths['villages']} file");
            }
        } else {
            // delete All
            $villages_json[$_GET['type']] = array();
            if ($f = fopen($paths['villages'], 'w')) {
                fwrite($f, json_encode($villages_json));
                fclose($f);
                $msg = "All {$_GET['type']} villages has been deleted. <a href='{$config['localhost']}adminvillages.php?mode={$mode}'>refresh page</a>";
            } else {
                Automate::factory()->log('E', "You don't have permission to write {$paths['villages']} file");
            }
        }
        break;
    case 'edit': // OK
        if ( !empty($_POST)) {
            // Check empty values
            foreach ($_POST as $k => $v) {
                if ($k !== 'name') {
                    if (empty($v)) {
                        $msg = "The field <b>{$k}</b> can not be empty";
                        $error = true;
                        break(2);
                    }
                }
            }
            // Save edit farm
            if ( !$error) {
                $edit_village = $_POST;
                unset($edit_village['who']); // Remove who value
                foreach ($edit_village as $key => $data) {
                    $villages_json[$_POST['who']][$edit_village['id']][$key] = $data;
                }
                if ($f = fopen($paths['villages'], 'w')) {
                    fwrite($f, json_encode($villages_json));
                    fclose($f);
                    $msg = "The changes have been saved. <a href='{$config['localhost']}adminvillages.php?mode={$mode}'>refresh page</a>";
                } else {
                    Automate::factory()->log('E', "You don't have permission to write {$paths['villages']} file");
                }
            }
        }
        break;
    case 'sort':
          // SORT BY NAME ALL VILLAGES
          echo 'sort';
         break;
}
// Generate troop stats
$attackVillages = 0; $defenseVillages = 0; $spyVillages = 0; $totalVillages = 0; $totalTroopPoints = 45000;
$attackTroops = 0; $defenseTroops = 0; $spyTroops = 0;
foreach ($villages_json['own'] as $village_id => $village) {
    // The village troops type
    if (in_array('attack', $village['type']) || in_array('siege', $village['type'])) $attackVillages++;
    if (in_array('defense', $village['type']) || in_array('static-defense', $village['type'])) $defenseVillages++;
    if (in_array('spy', $village['type'])) $spyVillages++;
    // Current troops
    foreach ($village['troops'] as $name => $troops) {
        if (in_array('attack', $village['type']) || in_array('siege', $village['type'])) {
            if (isset($buildings['troops'][$name])) $attackTroops += $troops * $buildings['troops'][$name]['settlers'];
        }
        if (in_array('defense', $village['type']) || in_array('static-defense', $village['type'])) {
            if (isset($buildings['troops'][$name])) $defenseTroops += $troops * $buildings['troops'][$name]['settlers'];
        }
        if (in_array('spy', $village['type'])) {
            if (isset($buildings['troops'][$name])) $spyTroops += $troops * $buildings['troops'][$name]['settlers'];
        }
    }
    $totalVillages++;
}
// The village troops type
$attackPercent = ($attackVillages > 0) ? round((($attackVillages * 100)/$totalVillages),1) : 0;
$defensePercent = ($defenseVillages > 0) ? round((($defenseVillages * 100)/$totalVillages),1) : 0;
$spyPercent = ($spyVillages > 0) ? round((($spyVillages * 100)/$totalVillages),1) : 0;
// Current troops
$attackTroopsPercent = ($attackTroops > 0) ? round((($attackTroops * 100)/($totalTroopPoints*$attackVillages)), 1) : 0;
$defenseTroopsPercent = ($defenseTroops > 0) ? round((($defenseTroops * 100)/($totalTroopPoints*$defenseVillages)), 1) : 0;
$spyTroopsPercent = ($spyTroops > 0) ? round((($spyTroops * 100)/($totalTroopPoints*$spyVillages)), 1) : 0;

/**
 * View
 */
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, user-scalable=yes">
        <meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
        <title><?=$config['player']?> - Village administration</title>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-purl-2.3.1.js"></script>
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery-ui-1.10.3.custom.min.css">
        <style type="text/css">
          #new_village, #add_villages {
            width: 720px;
            top: 75px;
            display: none;
          }
          #a_add_enemies, #a_add_allied { float: right; clear: right; }
          #a_del_enemies, #a_del_allied { float: left; clear: left; }
          table tbody tr.footer { background-color: #eee; }
          table tbody tr td.image { max-width: 200px; }
          table tbody tr td.image div {
             max-height: 180px;
             overflow: hidden;
             position: relative;
          }
          table tbody tr td.image div:hover { overflow: visible; }
          table tbody tr td.image div img {
             position: relative;
			 top: -125px;
			left: -232px;
          }
          table tbody tr td.image div:hover img {
             top: 0;
             left: 0;
             z-index: 1;
          }
          .title-bar {
            font-size: 13px;
            margin: 8px 0 4px 0;
          }
          .static-bar {
            position: relative;
            width: 250px;
            height: 18px;
            background-color: #eee;
          }
          .color-bar {
            position: absolute;
            top: 1px;
            height: 90%;
          }
          .color-bar > span {
            position: absolute;
            width: 100%;
            height: 100%;
            font-weight: bold;
            font-size: 12px;
            line-height: 16px;
            text-align: center;
            color: #fff;
          }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                var village_type = null;
                // OPEN
                jQuery('#a_new_village').click(function(){
                    jQuery('#modalbox').css('display', 'block');
                    this.padding = parseInt(jQuery('#new_village').css('padding-left')) + parseInt(jQuery('#new_village').css('padding-right'));
                    this.left = Math.round((jQuery(window).width() - (jQuery('#new_village').width()+this.padding))/2);
                    jQuery('#new_village').css({'display': 'block', 'left': this.left + 'px'});
                });
                jQuery('#a_add_enemies').click(function(){
                    jQuery('#add_ally_type').removeAttr('checked');
                    jQuery('#add_enemy_type').attr('checked', 'checked');
                    jQuery('#modalbox').css('display', 'block');
                    this.padding = parseInt(jQuery('#add_villages').css('padding-left')) + parseInt(jQuery('#add_villages').css('padding-right'));
                    this.left = Math.round((jQuery(window).width() - (jQuery('#add_villages').width()+this.padding))/2);
                    jQuery('#add_villages').css({'display': 'block', 'left': this.left + 'px'});
                });
                jQuery('#a_add_allied').click(function(){
                    jQuery('#add_enemy_type').removeAttr('checked');
                    jQuery('#add_ally_type').attr('checked', 'checked');
                    jQuery('#modalbox').css('display', 'block');
                    this.padding = parseInt(jQuery('#add_villages').css('padding-left')) + parseInt(jQuery('#add_villages').css('padding-right'));
                    this.left = Math.round((jQuery(window).width() - (jQuery('#add_villages').width()+this.padding))/2);
                    jQuery('#add_villages').css({'display': 'block', 'left': this.left + 'px'});
                });
                // Edit Village
                jQuery('#village-select').on('change', function(){
                  this.village_id = jQuery(this).val();
                  jQuery('#village_id').val(this.village_id);
                  jQuery('#village_id').css('disabled', 'disabled');
                });
                // CLOSE
                jQuery('.close').click(function(){
                    jQuery('#modalbox').css('display', 'none'); jQuery('#add_villages').css('display', 'none'); jQuery('#new_village').css('display', 'none');
                });
                // CLOSE ESC
                jQuery(window).keyup(function(e){
                    if (e.keyCode == 27) { // press ESC
                        jQuery('#modalbox').css('display', 'none'); jQuery('#add_villages').css('display', 'none'); jQuery('#new_village').css('display', 'none');
                    }
                });
                // Enemy labels
                jQuery('.radio_b').on('change', function(){
                        if(jQuery(this).is(':checked') && jQuery(this).attr('id')) {
                            jQuery('.enemy_label').css('display', 'block');
                        } else {
                            jQuery('.enemy_label').css('display', 'none');
                        }
                });
            });
        </script>
    </head>
    <body>
        <? if ( !empty($msg)) : ?>
            <div class="msg <?= ($error) ? 'error' : 'success'; ?>">
                <?= $msg; ?>
            </div>
        <? endif; ?>
        <div id="modalbox"></div>
        <!-- NEW VILLAGE -->
        <div id="new_village" class="modalbox">
            <div class="close">close</div>
            <div class="container">
                <form action="<?=$config['localhost']?>adminvillages.php?action=new-village" method="post">
                    <ul>
                            <li>
                            <label>Village is from:</label>
                            <span><input class="radio_b" type="radio" name="who" value="own" checked="checked"/>own</span>
                            <span><input class="radio_b" type="radio" name="who" value="ally"/>ally</span>
                            <span><input id="enemy_label" class="radio_b" type="radio" name="who" value="enemy"/>enemy</span>
                        </li>
                        <li>
                            <label>Village ID</label>
                            <span><input id="village_id" type="text" name="id" class="span4" maxlength="6"/></span>
                        </li>
                        <li class="enemy_label hidden">
                            <label>Player ID</label>
                            <span><input id="player_id" type="text" name="player_id" class="span4"/></span>
                        </li>
                        <li class="enemy_label hidden">
                            <label>Player Name</label>
                            <span><input id="player_name" type="text" name="player_name" class="span20"/> <em>(Can be empty)</em></span>
                        </li>
                        <li>
                            <label>Village Name</label>
                            <span><input id="village_name" type="text" name="name" class="span20"/> <em>(Can be empty)</em></span>
                        </li>
                        <li>
                            <label>Coordenates</label>
                            <span>
                                x: <input id="village_x" type="text" name="x" class="span2" maxlength="3"/> y: <input id="village_y" type="text" name="y" class="span2" maxlength="3"/>
                            </span>
                        </li>
                        <li>
                            <label>Village type</label>
                            <span><input type="checkbox" name="type[]" value="attack"/>attack</span>
                            <span><input type="checkbox" name="type[]" value="defense"/>defense</span>
                            <span><input type="checkbox" name="type[]" value="spy"/>spy</span>
                            <span><input type="checkbox" name="type[]" value="fake"/>fake</span>
                        </li>
                        <li>
                            <table class="farms fixed">
                                <thead>
                                    <tr class="header">
                                        <th>farmer</th>
                                        <th>sword</th>
                                        <th>spear</th>
                                        <th>axe</th>
                                        <th>bow</th>
                                        <th>spy</th>
                                        <th>light</th>
                                        <th>heavy</th>
                                        <th>ram</th>
                                        <th>kata</th>
                                        <th>snob</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" name="troops[farmer]" class="span2" value="<?= (isset($_POST['troops']['farmer'])) ? $_POST['troops']['farmer'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[sword]" class="span2" value="<?= (isset($_POST['troops']['sword'])) ? $_POST['troops']['sword'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[spear]" class="span2" value="<?= (isset($_POST['troops']['spear'])) ? $_POST['troops']['spear'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[axe]" class="span2" value="<?= (isset($_POST['troops']['axe'])) ? $_POST['troops']['axe'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[bow]" class="span2" value="<?= (isset($_POST['troops']['bow'])) ? $_POST['troops']['bow'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[spy]" class="span2" value="<?= (isset($_POST['troops']['spy'])) ? $_POST['troops']['spy'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[light]" class="span2" value="<?= (isset($_POST['troops']['light'])) ? $_POST['troops']['light'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[heavy]" class="span2" value="<?= (isset($_POST['troops']['heavy'])) ? $_POST['troops']['heavy'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[ram]" class="span1" value="<?= (isset($_POST['troops']['ram'])) ? $_POST['troops']['ram'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[kata]" class="span1" value="<?= (isset($_POST['troops']['kata'])) ? $_POST['troops']['kata'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[snob]" class="span1" value="<?= (isset($_POST['troops']['snob'])) ? $_POST['troops']['snob'] : '0' ?>" maxlength="2"/>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                    </ul>
                    <input type="submit" value="Save"/>
                </form>
            </div>
        </div>
        <!-- END -->
        <!-- Add Enemy/Allied colonies -->
        <div id="add_villages" class="modalbox">
            <div class="close">close</div>
            <div class="container">
                <form action="<?=$config['localhost']?>scripts/villages.php" method="get">
                    <ul>
                        <li>
                            <label>Type villages:</label>
                            <span><input id="add_enemy_type" class="radio_b" type="radio" name="type" value="enemy"/>enemy</span>
                            <span><input id="add_ally_type" class="radio_b" type="radio" name="type" value="ally"/>ally</span>
                        </li>
                        <li>
                            <label>Player ID</label>
                            <span><input type="text" name="player_id" class="span4"/></span>
                        </li>
                        <li>
                            <label>Player name</label>
                            <span><input type="text" name="player" class="span60"/></span>
                        </li>
                        <li>
                            <input type="submit" value="Add/Update Villages"/>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
        <!-- END -->
        <div class="bodyContainer">
            <div class="header">
              <h1><?=$config['player']?> - Village administration <em style="font-size:0.6em">(<?=date('d/m/Y H:i:s')?> | <?=date_default_timezone_get();?>)</em></h1>
            </div>
            <div class="content">
                <div class="new">
                    <a id="a_new_village" class="pointer">New Village</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>scripts/villages.php?first_village=1&player=<?=$config['player']?>">Update own villages</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>">Refresh</a> <span class="separator"> | </span> ▼ <a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>#troops">Go Down</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>">« Go Back</a>
                </div>
                <div class="tabs">
                    <ul>
                        <li class="left <?=($mode == 'dashboard') ? 'selected' : NULL;?>">
                            <a href="<?=$config['localhost']?>adminvillages.php?mode=dashboard">Dashboard</a>
                        </li>
                        <li class="left <?=($mode == 'attack') ? 'selected' : NULL;?>">
                            <a href="<?=$config['localhost']?>adminvillages.php?mode=attack">Attack</a>
                        </li>
                        <li class="left <?=($mode == 'siege') ? 'selected' : NULL;?>">
                            <a href="<?=$config['localhost']?>adminvillages.php?mode=siege">Attack & Siege</a>
                        </li>
                        <li class="left <?=($mode == 'defense') ? 'selected' : NULL;?>">
                            <a href="<?=$config['localhost']?>adminvillages.php?mode=defense">Defense</a>
                        </li>
                        <li class="left <?=($mode == 'static-defense') ? 'selected' : NULL;?>">
                            <a href="<?=$config['localhost']?>adminvillages.php?mode=static-defense">Static defense</a>
                        </li>
                        <li class="left <?=($mode == 'fake') ? 'selected' : NULL;?>">
                            <a href="<?=$config['localhost']?>adminvillages.php?mode=fake">Fake</a>
                        </li>
                        <li class="left <?=($mode == 'spy') ? 'selected' : NULL;?>">
                            <a href="<?=$config['localhost']?>adminvillages.php?mode=spy">Spy</a>
                        </li>
                        <li class="left <?=($mode == 'enemies') ? 'selected' : NULL;?>">
                            <a href="<?=$config['localhost']?>adminvillages.php?mode=enemies">Enemies</a>
                        </li>
                        <li class="left <?=($mode == 'allies') ? 'selected' : NULL;?>">
                            <a href="<?=$config['localhost']?>adminvillages.php?mode=allies">Allies</a>
                        </li>
                        <li class="both"></li>
                    </ul>
                </div>
                <?
                $editable = (isset($action) && isset($id)) && $action == 'edit';
                ?>
                <form action="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=<?=($editable) ? 'edit' : 'disable'?>" method="post">
                <!-- OWN VILLAGES -->
                <? if ($mode=='dashboard') : ?>
                <div>
                <div class="block">
                    <div class="inlineblock">
                        <div class="block title-bar">The village troops type</div>
                        <div class="block static-bar">
                            <? if ($attackPercent > 0) : ?>
                            <div class="inline color-bar" style="left: 0; width: <?=$attackPercent?>%; background-color: orange">
                                <span><?=$attackPercent?>% attack</span>
                            </div>
                            <? endif; ?>
                            <? if ($defensePercent > 0) : ?>
                            <div class="inline color-bar" style="left: <?=$attackPercent?>%; width: <?=$defensePercent?>%; background-color: green">
                                <span><?=$defensePercent?>% defense</span>
                            </div>
                            <? endif; ?>
                            <? if ($spyPercent > 0) : ?>
                            <div class="inline color-bar" style="left: <?=$attackPercent + $defensePercent?>%; width: <?=$spyPercent?>%; background-color: blue">
                                <span><?=$spyPercent?>% spy</span>
                            </div>
                            <? endif; ?>
                        </div>
                    </div>
                </div>
                <table class="farms">
                  <thead>
                    <tr>
                      <th colspan="18" style="background-color:#fff">
                        Own villages
                        <em style="font-weight:normal;">
                            <? if (isset($first_village_id)) : ?>
                                <?=isset($villages_json['own'][$first_village_id]['updated']) ? 'Last updated '.date('Y/m/d H:i:s', $villages_json['own'][$first_village_id]['updated']) : null ?>
                            <? endif; ?>
                        </em>
                      </th>
                    </tr>
                    <tr>
                      <th class="span1" style="background-color:#fff">num</th>
                      <th class="span1" style="background-color:#fff">ID</th>
                      <th style="background-color:#fff">map</th>
                      <th colspan="6" style="background-color:#fff">Name</th>
                      <th style="background-color:#fff">Points</th>
                      <th class="span2" style="background-color:#fff">Free settlers</th>
                      <th colspan="2" class="span4" style="background-color:#fff">Materials</th>
                      <th colspan="2" class="span6" style="background-color:#fff">Troops</th>
                      <th colspan="2" class="span4" style="background-color:#fff">Type</th>
                      <th colspan="2" style="background-color:#fff">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?
                      if (isset($villages_json['own'])) :
                        $villages_own = $villages_json['own'];
                        $total_troops = Array();
                        $i = 0;
                    ?>
                    <? foreach ($villages_own as $village_id => $village) : ?>
                              <tr>
                                  <td rowspan="3" style="border-bottom: 4px solid #111;"><?=$i+1?></td>
                                  <td rowspan="3" style="border-bottom: 4px solid #111;">
                                      <? if ($editable && $village['id'] == $id) : ?>
                                          <input type="text" class="span3" value="<?= $village['id'] ?>" name="id"/>
                                      <? else : ?>
                                            <a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$village['id'].$config['main']?>" target="_blank">
                                              <?= $village['id'] ?>
                                          </a>
                                      <? endif; ?>
                                  </td>
                                  <td rowspan="3" class="image" style="border-bottom: 4px solid #111;">
                                     <div>
                                        <img src="<?=$config['localhost']?>media/map/<?=$village_id?>.png" alt="<?=$village['name']?> map"/>
                                     </div>
                                  </td>
                                  <td colspan="6">
                                      <? if ($editable && $village['id'] == $id) : ?>
                                          <input type="text" class="span3" value="<?= $village['name'] ?>" name="name"/>
                                      <? else : ?>
                                          <?= $village['name'] ?> <em>(<?=$village['x']?>|<?=$village['y']?>)</em>
                                      <? endif; ?>
                                  </td>
                                  <td>
                                    <? if (isset($village['points'])) : ?>
                                        <?=number_format($village['points'],0, '', '.')?>
                                    <? endif; ?>
                                  </td>
                                  <td>
                                    <? if (isset($village['settlers'])) : ?>
                                        <?=number_format($village['settlers'],0, '', '.')?>
                                    <? endif; ?>
                                  </td>
                                  <td colspan="2">
                                    <? if (isset($village['materials'])) : ?>
                                        <div class="block">
                                            <span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/stone.png" alt="stone" title="stone" style="vertical-align: middle;"/></span>
                                            <span class="inline" style="line-height: 24px;">&nbsp;<?=number_format($village['materials']['stone'],0, '', '.')?></span>
                                        </div>
                                        <div class="block">
                                            <span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/wood.png" alt="wood" title="wood" style="vertical-align: middle;"/></span>
                                            <span class="inline" style="line-height: 24px;">&nbsp;<?=number_format($village['materials']['wood'],0, '', '.')?></span>
                                        </div>
                                        <div class="block">
                                            <span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/iron.png" alt="iron" title="iron" style="vertical-align: middle;"/></span>
                                            <span class="inline" style="line-height: 24px;">&nbsp;<?=number_format($village['materials']['iron'],0, '', '.')?></span>
                                        </div>
                                    <? endif; ?>
                                  </td>
                                  <td colspan="2" class="alignleft">
                                    <? foreach($village['troops'] as $name => $troop) : ?>
                                        <? $total_troops[$name] = !isset($total_troops[$name]) ? $troop : $total_troops[$name]+$troop; ?>
                                        <? if ($editable && $village['id'] == $id) : ?>
                                          <div class="block">
                                            <div class="left" style="line-height: 36px;"><?=$name?>:</div>
                                            <div class="right" style="line-height: 36px;"><input type="text" class="span2" value="<?= $troop; ?>" name="troops[<?= $name; ?>]" maxlength="5"/></div>
                                            <div class="both"></div>
                                          </div>
                                          <? else: ?>
                                            <? if ($troop > 0) : ?>
                                                <div class="block">
                                                        <span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/<?=$name?>.gif" alt="<?=$name?>" title="<?=$name?>" style="vertical-align: middle;"/></span>
                                                        <span class="inline" style="line-height: 24px;">&nbsp;<?=number_format($troop, 0, ',', '.')?></span>
                                                        <span class="both"></span>
                                                </div>
                                            <? endif; ?>
                                         <? endif; ?>
                                     <? endforeach; ?>
                                  </td>
                                  <td colspan="2" class="alignleft">
                                        <?
                                        if (isset($village['type']) && count($village['type']) > 0) :
                                            foreach($village['type'] as $type) :
                                                if (!$editable) :
                                                    echo "<div class='block aligncenter'><img src='{$config['localhost']}media/img/{$type}_type.png' title='{$type}' alt='{$type}'/></div>";
                                                endif;
                                            endforeach;
                                        endif;
                                        ?>
                                        <? if ($editable && $village['id'] == $id) : ?>
                                            <div class="block">
                                                <input type="checkbox" name="type[]" value="attack" <?=in_array('attack', $village['type']) ? 'checked="checked"' : null; ?>/>attack
                                            </div>
                                            <div class="block">
                                                <input type="checkbox" name="type[]" value="siege" <?=in_array('siege', $village['type']) ? 'checked="checked"' : null; ?>/>siege
                                            </div>
                                            <div class="block">
                                                <input type="checkbox" name="type[]" value="defense" <?=in_array('defense', $village['type']) ? 'checked="checked"' : null; ?>/>defense
                                            </div>
                                            <div class="block">
                                                <input type="checkbox" name="type[]" value="static-defense" <?=in_array('static-defense', $village['type']) ? 'checked="checked"' : null; ?>/>static defense
                                            </div>
                                            <div class="block">
                                                <input type="checkbox" name="type[]" value="spy" <?=in_array('spy', $village['type']) ? 'checked="checked"' : null; ?>/>spy
                                            </div>
                                            <div class="block">
                                                <input type="checkbox" name="type[]" value="fake" <?=in_array('fake', $village['type']) ? 'checked="checked"' : null; ?>/>fake
                                            </div>
                                        <? endif; ?>
                                  </td>
                                  <td rowspan="3" colspan="2" style="border-bottom: 4px solid #111;">
                                      <? if ($editable && $village['id'] == $id) : ?>
                                          <input type="submit" value="Save"/><br/><a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>" name="v<?=$village['id']?>">Cancel</a>
                                          <input type="hidden" name="who" value="own"/>
                                      <? else : ?>
                                          <a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=edit&id=<?=$village['id']?>#v<?=$village['id']?>">Edit</a> | <a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=delete&id=<?=$village['id']?>&who=own">Delete</a>
                                      <? endif; ?>

                                  </td>
                              </tr>
							  <tr>
								<? foreach($village['buildings'] as $building => $level) : ?>
									<td style="background-color:#fff">
										<img src="<?=$config['localhost']?>media/img/<?=$building?>.png" alt="<?=$building?>" title="<?=$building?>" style="vertical-align: middle;"/>
									</td>
								<? endforeach; ?>
							  </tr>
							  <tr>
								<? foreach($village['buildings'] as $building => $level) : ?>
									<td style="background-color:#fff; border-bottom: 4px solid #111;"><?=$level?></td>
								<? endforeach; ?>
							  </tr>
                              <? $i++; ?>
                    <? endforeach; ?>
                    <? if ($i == 0) : ?>
                    <tr>
                        <td colspan="18">You don't have any own villages.</td>
                    </tr>
                    <? endif; ?>
                    <? else : ?>
                    <tr>
                        <td colspan="18">You don't have any own villages.</td>
                    </tr>
                    <? endif; ?>
                    <? if (isset($total_troops) && count($total_troops) > 0) : ?>
                    <tr>
                        <td colspan="6" rowspan="2">
                            <a name="troops"></a>
                            <b>Troops</b>
                        </td>
                        <? foreach($total_troops as $name => $troop) : ?>
                        <td>
                            <b><?=$name?></b>
                        </td>
                        <? endforeach ?>
						<td rowspan="2"></td>
                    </tr>
                    <tr>
                        <? foreach($total_troops as $name => $troop) : ?>
                        <td>
                            <?=number_format($troop,0,'','.')?>
                        </td>
                        <? endforeach ?>
                    </tr>
                    <? endif; ?>
                  </tbody>
                </table>
                <? if ($editable) : ?>
                <? endif; ?>
                </div>
                <? endif; ?>
                <!-- END OWN VILLAGES -->

                <!-- ENEMIES VILLAGES -->
                <? if ($mode=='enemies') : ?>
                <div>
                <?
                $editable = (isset($action) && isset($id)) && $action == 'edit';
                ?>
                <table class="farms">
                        <thead>
                    <tr>
                      <th colspan="7">
                        <a id="a_del_enemies" href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=delete&type=enemy" onclick="confirm('Are you sure to delete all enemy villages?')">Remove villages</a>
                        Enemy villages
                        <a id="a_add_enemies" class="green button">Add enemy colonies</a>
                      </th>
                    </tr>
                    <tr>
                      <th class="span3">ID</th>
                      <th>Name</th>
                      <th>points</th>
                      <th>Player</th>
                      <th class="span5">Coords (x|y)</th>
                      <th class="span4">Type</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                          <?
                      if (isset($villages_json['enemy'])) :
                        $villages_enemy = $villages_json['enemy'];
                         $i = 0;
                    ?>
                    <? foreach ($villages_enemy as $village_id => $village) : ?>
                              <tr>
                                  <td>
                                      <? if ($editable && $village['id'] == $id) : ?>
                                          <input type="text" class="span3" value="<?= $village['id'] ?>" name="id"/>
                                      <? else : ?>
                                        <a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$first_village_id.$config['info_village'].$village['id']?>" target="_blank">
                                            <?= $village['id'] ?>
                                        </a>
                                      <? endif; ?>
                                  </td>
                                  <td>
                                      <? if ($editable && $village['id'] == $id) : ?>
                                          <input type="text" class="span3" value="<?= $village['name'] ?>" name="name"/>
                                      <? else : ?>
                                          <?= $village['name'] ?>
                                      <? endif; ?>
                                  </td>
                                  <td>
                                    <? if (isset($village['points'])) : ?>
                                        <?=number_format($village['points'],0, '', '.')?>
                                    <? endif; ?>
                                  </td>
                                  <td>
                                       <a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$first_village_id.$config['info_player'].$village['player_id']?>" target="_blank">
                                        <?=$village['player_name']?>
                                       </a>
                                  </td>
                                  <td>
                                      <? if ($editable && $village['id'] == $id) : ?>
                                          x: <span><input type="text" class="span3" value="<?= $village['x'] ?>" name="x"/><span>
                                          y: <span><input type="text" class="span3" value="<?= $village['y'] ?>" name="y"/></span>
                                      <? else : ?>
                                          <?=$village['x']?>|<?=$village['y']?>
                                      <? endif; ?>
                                  </td>
                                  <td>
                                    <? if ($editable && $village['id'] == $id) : ?>
                                        <div class="block">
                                            <input type="radio" name="type" value="attack" <?=($village['type'] == 'attack') ? 'checked="checked"' : null; ?>/>attack
                                        </div>
                                        <div class="block">
                                            <input type="radio" name="type" value="spy" <?=($village['type'] == 'spy') ? 'checked="checked"' : null; ?>/>spy
                                        </div>
                                        <div class="block">
                                            <input type="radio" name="type" value="fake" <?=($village['type'] == 'fake') ? 'checked="checked"' : null; ?>/>fake
                                        </div>
                                    <? else : ?>
                                        <?=is_string($village['type']) ? $village['type'] : null;?>
                                    <? endif; ?>
                                  </td>
                                  <td>
                                      <? if ($editable && $village['id'] == $id) : ?>
                                          <input type="submit" value="Save"/><br/><a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>" name="v<?=$village['id']?>">Cancel</a>
                                          <input type="hidden" name="who" value="enemy"/>
                                      <? else : ?>
                                          <a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=edit&id=<?=$village['id']?>#v<?=$village['id']?>">Edit</a> | <a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=delete&id=<?=$village['id']?>&who=enemy">Delete</a>
                                      <? endif; ?>
                                  </td>
                              </tr>
                              <? $i++; ?>
                    <? endforeach; ?>
                    <? if ($i == 0) : ?>
                    <tr>
                        <td colspan="7">You don't have any enemy villages.</td>
                    </tr>
                    <? endif; ?>
                    <? else : ?>
                    <tr>
                        <td colspan="7">You don't have any enemy villages.</td>
                    </tr>
                    <? endif; ?>
                  </tbody>
                </table>
                <? if ($editable) : ?>
                <? endif; ?>
                </div>
                <? endif; ?>
                <!-- END ENEMY VILLAGES -->

                <!-- ALLY VILLAGES -->
                <? if ($mode=='allies') : ?>
                <div>
                <?
                $editable = (isset($action) && isset($id)) && $action == 'edit';
                ?>
                <table class="farms">
                        <thead>
                    <tr>
                      <th colspan="6">
                        <a id="a_del_allied" href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=delete&type=ally" onclick="confirm('Are you sure to delete all allied villages?')">Remove villages</a>
                        Ally villages
                        <a id="a_add_allied" class="green button">Add allied colonies</a></th>
                    </tr>
                    <tr>
                      <th class="span3">ID</th>
                      <th>Name</th>
                      <th>points</th>
                      <th>Player</th>
                      <th class="span5">Coords (x|y)</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?
                      if (isset($villages_json['ally'])) :
                        $villages_ally = $villages_json['ally'];
                         $i = 0;
                    ?>
                    <? foreach ($villages_ally as $village_id => $village) : ?>
                    <tr>
                        <td>
                            <? if ($editable && $village['id'] == $id) : ?>
                                <input type="text" class="span3" value="<?= $village['id'] ?>" name="id"/>
                            <? else : ?>
                                <a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$first_village_id.$config['info_village'].$village['id']?>" target="_blank">
                                    <?= $village['id'] ?>
                                </a>
                            <? endif; ?>
                        </td>
                        <td>
                            <? if ($editable && $village['id'] == $id) : ?>
                                <input type="text" class="span3" value="<?= $village['name'] ?>" name="name"/>
                            <? else : ?>
                                <?= $village['name'] ?>
                            <? endif; ?>
                        </td>
                        <td>
                            <? if (isset($village['points'])) : ?>
                                <?=number_format($village['points'],0, '', '.')?>
                            <? endif; ?>
                        </td>
                        <td>
                           <a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$first_village_id.$config['info_player'].$village['player_id']?>" target="_blank">
                            <?=$village['player_name']?>
                           </a>
                        </td>
                        <td>
                              <? if ($editable && $village['id'] == $id) : ?>
                                x: <span><input type="text" class="span3" value="<?= $village['x'] ?>" name="x"/><span>
                                y: <span><input type="text" class="span3" value="<?= $village['y'] ?>" name="y"/></span>
                            <? else : ?>
                                <?=$village['x']?>|<?=$village['y']?>
                            <? endif; ?>
                        </td>
                        <td>
                            <? if ($editable && $village['id'] == $id) : ?>
                                <input type="submit" value="Save"/><br/><a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>" name="v<?=$village['id']?>">Cancel</a>
                                <input type="hidden" name="who" value="ally"/>
                            <? else : ?>
                                <a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=edit&id=<?=$village['id']?>#v<?=$village['id']?>">Edit</a> | <a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=delete&id=<?=$village['id']?>&who=ally">Delete</a>
                            <? endif; ?>
                        </td>
                    </tr>
                              <? $i++; ?>
                    <? endforeach; ?>
                    <? if ($i == 0) : ?>
                    <tr>
                        <td colspan="6">You don't have any ally villages.</td>
                    </tr>
                    <? endif; ?>
                    <? else : ?>
                    <tr>
                        <td colspan="6">You don't have any ally villages.</td>
                    </tr>
                    <? endif; ?>
                  </tbody>
                </table>
                <? if ($editable) : ?>
                <? endif; ?>
                </div>
                <!-- END ALLY VILLAGES -->
                <? endif; ?>

                <!-- 

                    MODES VILLAGES

                -->
                <? if ($mode!='dashboard' && $mode!='enemies' && $mode!='allies') : ?>
                <div>

                <!-- Attack stats -->
                <? if (($mode == 'attack' || $mode == 'siege') && $attackTroopsPercent > 0) : ?>
                <div class="inlineblock" style="margin-left: 16px">
                    <div class="block title-bar">Current attack troops</div>
                    <div class="block static-bar">
                        <div class="inline color-bar" style="left: 0; width: <?=$attackTroopsPercent?>%; background-color: orange">
                            <span><?=$attackTroopsPercent?>% attack</span>
                        </div>
                    </div>
                </div>
                <? endif; ?>

                <!-- Defense stats -->
                <? if (($mode == 'defense' || $mode == 'static-defense') && $defenseTroopsPercent > 0) : ?>
                <div class="inlineblock" style="margin-left: 16px">
                    <div class="block title-bar">Current defense troops</div>
                    <div class="block static-bar">
                        <div class="inline color-bar" style="left: 0; width: <?=$defenseTroopsPercent?>%; background-color: green">
                            <span><?=$defenseTroopsPercent?>% defense</span>
                        </div>
                    </div>
                </div>
                <? endif; ?>

                <!-- Spy stats -->
                <? if ($mode == 'spy' && $spyTroopsPercent > 0) : ?>
                <div class="inlineblock" style="margin-left: 16px">
                    <div class="block title-bar">Current spy troops</div>
                    <div class="block static-bar">
                        <div class="inline color-bar" style="left: 0; width: <?=$spyTroopsPercent?>%; background-color: blue">
                            <span><?=$spyTroopsPercent?>% spy</span>
                        </div>
                    </div>
                </div>
                <? endif; ?>

                <table class="farms">
                  <thead>
                    <tr>
                      <th rowspan="2">num</th>
                      <th rowspan="2" class="span3">ID</th>
                      <th rowspan="2" colspan="4">Name</th>
                      <th rowspan="2">Points</th>
                      <th rowspan="2" class="span3">Free settlers</th>
                      <th colspan="11">Troops</th>
                      <th rowspan="2" colspan="3">Actions</th>
                    </tr>
                    <tr>
                        <th>farmer</th>
                        <th>sword</th>
                        <th>spear</th>
                        <th>axe</th>
                        <th>bow</th>
                        <th>spy</th>
                        <th>light</th>
                        <th>heavy</th>
                        <th>ram</th>
                        <th>kata</th>
                        <th>snob</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?
                      if (isset($villages_json['own'])) :
                        $villages_own = $villages_json['own'];
                        $total_troops = Array();
                        $i = 0;
                    ?>
                    <? foreach ($villages_own as $village_id => $village) : ?>
                        <? if (in_array($mode, $village['type'])) : ?>
                              <tr>
                                  <td><?=$i+1?></td>
                                  <td>
                                      <? if ($editable && $village['id'] == $id) : ?>
                                          <input type="text" class="span3" value="<?= $village['id'] ?>" name="id"/>
                                      <? else : ?>
                                            <a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$village['id'].$config['main']?>" target="_blank">
                                              <?= $village['id'] ?>
                                          </a>
                                      <? endif; ?>
                                  </td>
                                  <td colspan="4">
                                      <? if ($editable && $village['id'] == $id) : ?>
                                          <input type="text" class="span3" value="<?= $village['name'] ?>" name="name"/>
                                      <? else : ?>
                                          <?= $village['name'] ?> <em>(<?=$village['x']?> | <?=$village['y']?>)</em>
                                      <? endif; ?>
                                  </td>
                                  <td>
                                    <? if (isset($village['points'])) : ?>
                                        <?=number_format($village['points'],0, '', '.')?>
                                    <? endif; ?>
                                  </td>
                                  <td>
                                    <? if (isset($village['settlers'])) : ?>
                                        <?=number_format($village['settlers'],0, '', '.')?>
                                    <? endif; ?>
                                  </td>
                                  <? foreach($village['troops'] as $name => $troop) : ?>
                                    <? $total_troops[$name] = !isset($total_troops[$name]) ? $troop : $total_troops[$name]+$troop; ?>
                                    <? if ($troop > 0) : ?>
                                        <td class="aligncenter"><?=number_format($troop, 0, ',', '.')?></span></td>
                                    <? else : ?>
                                        <td>0</td>
                                    <? endif; ?>
                                  <? endforeach; ?>
                                  <td colspan="3">
                                      <a href="<?=$config['localhost']?>adminvillages.php?mode=<?=$mode?>&action=delete&id=<?=$village['id']?>&who=own">Delete</a>
                                  </td>
                              </tr>
                              <? $i++; ?>
                        <? endif; ?>
                    <? endforeach; ?>
                    <? if ($i == 0) : ?>
                    <tr>
                        <td colspan="16">You don't have any attack villages.</td>
                    </tr>
                    <? endif; ?>
                    <? else : ?>
                    <tr>
                        <td colspan="16">You don't have any attack villages.</td>
                    </tr>
                    <? endif; ?>
                    <? if (isset($total_troops) && count($total_troops) > 0) : ?>
                    <tr class="footer">
                        <td colspan="8" rowspan="2">
                            <a name="troops"></a>
                            <b>Troops</b>
                        </td>
                        <? foreach($total_troops as $name => $troop) : ?>
                        <td>
                            <b><?=$name?></b>
                        </td>
                        <? endforeach ?>
                        <td rowspan="2" colspan="3"></td>
                    </tr>
                    <tr class="footer">
                        <? foreach($total_troops as $name => $troop) : ?>
                        <td>
                            <?=number_format($troop,0,'','.')?>
                        </td>
                        <? endforeach ?>
                    </tr>
                    <? endif; ?>
                  </tbody>
                </table>
                </div>
                <? endif; ?>
                <!-- END ATTACK -->
                </form>
            </div>
        </div>
    </body>
</html>
