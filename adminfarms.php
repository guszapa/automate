<?php
/**
 * Farm administration
 *
 * @author katan
 */
include_once 'automate.class.php';
$farms_json = json_decode(Automate::factory()->getFarms(), TRUE);
$own_villages = Automate::factory()->getVillages('own');
$all_villages = Automate::factory()->getVillages();
$village_default = false;
$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
$msg = '';
$error = false;
$is_ajax = false;
/**
 * Controller
 */
$action = null;
if (! empty($_GET)) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? $_GET['id'] : null;
}
switch ($action) {
    case 'new-farm': // OK
        if ( !empty($_POST)) {
            // Check empty troops
            if ( !$error) {
                // Check empty troops
                $troops = 0;
                foreach ($_POST['troops'] as $v) $troops += (int)$v;
                if ($troops == 0) {
                    $msg = "You can not send zero troops";
                    $error = true;
                }
            }
            // Save the new farm
            if ( !$error) {
                $new_farm = $_POST;
				$id = time();
                if ( !isset($_POST['enabled'])) $new_farm['enabled'] = 'false';
                if ( !isset($_POST['nocturnal'])) $new_farm['nocturnal'] = 'false';
				unset($new_farm['fromname']);
                $farms_json[time()] = $new_farm;
                if ($f = fopen($paths['farms'], 'w')) {
                    fwrite($f, json_encode($farms_json));
                    fclose($f);
                    $msg = "The farm have been saved. <a href='{$config['localhost']}adminfarms.php'>refresh page</a>";
                } else {
                    Automate::factory()->log('E', "You don't have permission to write {$paths['farms']} file");
                }
            }
        }
        break;
    case 'delete': // OK
        if ($id) {
            $new_farms = $farms_json;
			unset($new_farms[$id]);
            if ($f = fopen($paths['farms'], 'w')) {
                fwrite($f, json_encode($new_farms));
                fclose($f);
				$farms_json = $new_farms;
				$msg = "The farm have been deleted. <a href='{$config['localhost']}adminfarms.php'>refresh page</a>";
            } else {
                Automate::factory()->log('E', "You don't have permission to write {$paths['farms']} file");
            }
        }
        break;
    case 'edit': // OK
        if ( !empty($_POST)) {
            // Check empty troops
            if ( !$error) {
                $troops = 0;
                  foreach ($_POST['troops'] as $v) $troops += (int)$v;
                  if ($troops == 0) {
                      $msg = "You can not send zero troops";
                      $error = true;
                  }
            }
            // Save edit farm
            if ( !$error) {
				$id = $_POST['id'];
				unset($_POST['id']);
				unset($_POST['fromname']);
                $edit_farm = $_POST;
                if ( !isset($_POST['nocturnal'])) $edit_farm['nocturnal'] = 'false';
                $farms_json[$id] = $edit_farm;
                if ($f = fopen($paths['farms'], 'w')) {
                    fwrite($f, json_encode($farms_json));
                    fclose($f);
                    $msg = "The changes have been saved. <a href='{$config['localhost']}adminfarms.php'>refresh page</a>";
                } else {
                    Automate::factory()->log('E', "You don't have permission to write {$paths['farms']} file");
                }
            }
        }
        break;
    case 'disable': // OK
        if ( !empty($_POST)) {
           if (isset($_POST['select']) && count($_POST['select']) > 0) {
              for($i=0; $i < count($_POST['select']); $i++) {
				 $farms_json[$_POST['select'][$i]]['enabled'] = isset($_POST['enable']) ? 'true' : 'false';
              }
              if ($f = fopen($paths['farms'], 'w')) {
                  fwrite($f, json_encode($farms_json));
                  fclose($f);
                  $msg = "The changes have been saved. <a href='{$config['localhost']}adminfarms.php'>refresh page</a>";
              } else {
                  Automate::factory()->log('E', "You don't have permission to write {$paths['farms']} file");
              }
           }
        }
        break;
	case 'get-village': // OK
	  $is_ajax = TRUE;
	  $village_data = Automate::factory()->getVillage($_GET['type'], $id);
	  echo $village_data;
	  break;
}
/**
 * View
 */
?>
<? if (!$is_ajax) : ?>

<!DOCTYPE html>
<html lang="en">
    <head>
		<meta name="viewport" content="width=device-width, user-scalable=yes">
        <meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
        <title><?=$config['player']?> - Farm administration</title>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery.ui.timepicker.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-purl-2.3.1.js"></script>
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery.ui.timepicker.css">
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery-ui-1.10.3.custom.min.css">
        <style type="text/css">
          #new_farm, #new_village, #calculator, #log {
            width: 780px;
            top: 75px;
            display: none;
          }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                var action = jQuery.url().param('action');
                // OPEN
                jQuery('#a_new_farm').click(function(){
                    jQuery('#modalbox').css('display', 'block');
                    this.padding = parseInt(jQuery('#new_farm').css('padding-left')) + parseInt(jQuery('#new_farm').css('padding-right'));
                    this.left = Math.round((jQuery(window).width() - (jQuery('#new_farm').width()+this.padding))/2);
                    jQuery('#new_farm').css({'display': 'block', 'left': this.left + 'px'});
                });
                jQuery('#a_new_village').click(function(){
                    jQuery('#modalbox').css('display', 'block');
                    this.padding = parseInt(jQuery('#new_village').css('padding-left')) + parseInt(jQuery('#new_village').css('padding-right'));
                    this.left = Math.round((jQuery(window).width() - (jQuery('#new_village').width()+this.padding))/2);
                    jQuery('#new_village').css({'display': 'block', 'left': this.left + 'px'});
                });
                // Edit Village
                jQuery('#village-select').on('change', function(){
                  this.village_id = jQuery(this).val();
                  jQuery('#village_id').val(this.village_id);
                  jQuery('#village_id').css('disabled', 'disabled');
                });
                // CLOSE
                jQuery('.close').click(function(){
                    jQuery('#modalbox').css('display', 'none'); jQuery('#new_farm').css('display', 'none'); jQuery('#new_village').css('display', 'none'); jQuery('#calculator').css('display', 'none'); jQuery('#log').css('display', 'none');
                });
                // CLOSE ESC
                jQuery(window).keyup(function(e){
                    if (e.keyCode == 27) { // press ESC
                        jQuery('#modalbox').css('display', 'none'); jQuery('#new_farm').css('display', 'none'); jQuery('#new_village').css('display', 'none'); jQuery('#calculator').css('display', 'none'); jQuery('#log').css('display', 'none');
                    }
                });
				// Insert troops & coords (own village)
                jQuery('.own-selected').on('change', function(){
                    $.ajax({
                        url: "<?=$config['localhost']?>adminfarms.php?action=get-village&type=own&id="+jQuery(this).val(),
                        context: document.body,
                        dataType: 'json'
                        }).done(function(data) {
                            jQuery.each(data.troops, function(key, value) {
                                jQuery('#'+key).val(value);
                            });
                            jQuery('#from_name').val(data.name); jQuery('#from_name_edit').val(data.name);
                            jQuery('#from_x').val(data.x); jQuery('#from_x_edit').val(data.x);
                            jQuery('#from_y').val(data.y); jQuery('#from_y_edit').val(data.y);
                            jQuery('#from_id').val(data.id); jQuery('#from_id_edit').val(data.id);
                        });
                });
				// Insert coords (enemy village)
                jQuery('.target-selected').on('change', function(){
                    var type = jQuery(this).data('type');
                    $.ajax({
                        url: "<?=$config['localhost']?>adminfarms.php?action=get-village&type="+type+"&id="+jQuery(this).val(),
                        context: document.body,
                        dataType: 'json'
                        }).done(function(data) {
							jQuery('#to_name').val(data.name);
                            jQuery('#to_id').val(data.id);
                            jQuery('#to_x').val(data.x);
                            jQuery('#to_y').val(data.y);
                        });
                });
                // Datepicker
                jQuery( ".timepicker" ).timepicker({
                  timeSeparator: ':',
                  showLeadingZero: true,
                  showPeriod: false,
                  showPeriodLabels: false
                });
                // Select all/none farms
                jQuery('#select-all').on('click', function(){
                   if(jQuery(this).is(':checked')) {
                      jQuery(".chk:checkbox:not(:checked)").attr("checked", "checked");
                   } else {
                      jQuery(".chk:checkbox:checked").removeAttr("checked");
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
        <!-- NEW FARM -->
        <div id="new_farm" class="modalbox">
            <div class="close">close</div>
            <div class="container">
                <? if ($own_villages) : ?>
                <form action="<?=$config['localhost']?>adminfarms.php?action=new-farm" method="post">
                    <ul>
                        <li>
                            <label>From village</label>
                            <span>
                                <select class="own-selected" name="fromname">
								<option disabled="disabled" selected="selected">- Select a own target -</option>
                                <? foreach ($own_villages as $from_id => $village) : ?>
									<? if (!$village_default) : ?>
									<?    $village_default = $from_id; ?>
									<? endif; ?>
                                    <option value="<?= $from_id ?>"><?= $village['name'] ?></option>
                                <? endforeach; ?>
                                </select>
                            </span>
							<span>ID: <input id="from_id" class="span4" type="text" name="from[id]" maxlength="3" placeholder="Village ID" value="<?= (isset($_POST['from']['id'])) ? $_POST['from']['id'] : null;?>"/></span>
							<input type="hidden" id="from_name" name="from[name]" value="<?= (isset($_POST['from']['name'])) ? $_POST['from']['name'] : null;?>"/>
							<input type="hidden" id="from_x" name="from[x]" value="<?= (isset($_POST['from']['x'])) ? $_POST['from']['x'] : null;?>"/>
                            <input type="hidden" id="from_y" name="from[y]" value="<?= (isset($_POST['from']['y'])) ? $_POST['from']['y'] : null;?>"/>
                        </li>
						<li>
							<label>To: <em>(target village)</em></label>
                            <? 	if(count($all_villages) > 0) : ?>
                                <? 	foreach($all_villages as $type => $villages) : ?>
                                    <? 	if(count($villages) > 0) : ?>
                                    <div class="inlineblock">
                                        <select class="target-selected" class="span16" data-type="<?=$type?>">
                                            <option disabled="disabled" selected="selected">- Select a <?=$type?> target -</option>
                                            <!-- create optgroups -->
                                            <? $optgroups = array() ?>
                                            <? foreach($villages as $key => $village) : ?>
                                                <? if (is_string($village['type'])) : ?>
                                                    <? $optgroups[$village['type']][] = $village; ?>
                                                <? else : ?>
                                                    <? $optgroups['others'][] = $village;?>
                                                <? endif; ?>
                                            <? endforeach; ?>
                                            <!-- print select -->
                                            <? foreach($optgroups as $type => $villages) : ?>
                                                <optgroup label="<?=$type?>">
                                                <? foreach($villages as $village) : ?>
                                                    <option value="<?=$village['id']?>"<?=(isset($_POST['to']['id']) && $_POST['to']['id'] == $village['id']) ? ' selected="selected"' : null;?>><?=isset($village['player_name']) ? "{$village['player_name']} - " : null?><?="{$village['name']} ({$village['x']}|{$village['y']})";?></option>
                                                <? endforeach; ?>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <?	endif; ?>
                                <?	endforeach; ?>
                                <input id="to_id" type="hidden" name="to[id]" value="<?= (isset($_POST['to']['id'])) ? $_POST['to']['id'] : null;?>"/>
								<input type="hidden" id="to_name" name="to[name]" value="<?= (isset($_POST['to']['name'])) ? $_POST['to']['name'] : null;?>"/>
                            <?	endif; ?>
                            <span>- o -</span>
                            <span>X: <input id="to_x" class="span2 distance" type="text" name="to[x]" maxlength="3" placeholder="XXX" value="<?= (isset($_POST['to']['x'])) ? $_POST['to']['x'] : null;?>"/></span>
                            <span>Y: <input id="to_y" class="span2 distance" type="text" name="to[y]" maxlength="3" placeholder="YYY" value="<?= (isset($_POST['to']['y'])) ? $_POST['to']['y'] : null;?>"/></span>
						</li>
						<li>
                             <label>Iteration:</label>
                             <select class="span10" name="iteration">
                                 <? for($i=0; $i<=16; $i++) : ?>
                                     <? if ($i != 1) : ?>
                                     <option value=<?=$i;?><?=(isset($_POST['iteration']) && $_POST['iteration'] == $i) ? ' selected="selected"' : null;?>><?=$i;?></option>
                                     <? endif; ?>
                                 <? endfor; ?>
                             </select>
                        </li>
                        <li>
                            <label>Enabled</label>
                            <?
                            if (isset($_POST['enabled'])) {
                               $cheked = ($_POST['enabled'] == 'true') ? 'checked="checked"' : null;
                            } else {
                               $cheked = 'checked="checked"';
                            }
                            ?>
                            <span><input type="checkbox" name="enabled" class="span4" value="true" <?=$cheked?>/></span></span>
                        </li>
                        <li>
                            <label>Attack mode</label>
                            <span>
                                <input type="radio" name="mode" value="attack" <?= (isset($_POST['mode']) && $_POST['mode']=='attack') ? 'checked="checked"' : 'checked="checked"' ?>/> 
                                Attack
                            </span>
                            <span>
                                <input type="radio" name="mode" value="spy" <?= (isset($_POST['mode']) && $_POST['mode']=='spy') ? 'checked="checked"' : null ?>/>
                                Spy <em>(silent)</em>
                            </span>
                        </li>
                        <li>
                            <label>Nocturnal attack</label>
                            <span><input type="checkbox" name="nocturnal" class="span4" value="true" <?= (isset($_POST['nocturnal'])) ? 'checked="checked"' : null ?>/></span>
                            <em style="font-size: 0.8em;">(optional - allow attack on nocturnal range)</em>
                        </li>
						<li>
                            <label>Untracked attack</label>
                            <span><input type="checkbox" name="untracked" class="span4" value="true" <?= (isset($_POST['untracked'])) ? 'checked="checked"' : null ?>/></span>
                            <em style="font-size: 0.8em;">(optional - No register the attack, usefull with range time for daily attacks from any distance)</em>
                        </li>
                        <li>
                            <label>Range time <em style="font-size: 0.8em;">(optional)</em></label>
                            <span>
                              <input type="text" name="start" class="span4 timepicker" value="<?=(isset($_POST['start'])) ? $_POST['start'] : null ?>"/> 
                              Start
                            </span>
                            <span>
                              <input type="text" name="end" class="span4 timepicker" value="<?=(isset($_POST['end'])) ? $_POST['end'] : null ?>"/> 
                              End
                            </span>
                            <em style="font-size: 0.8em;">(out this range will not attack the target)</em>
                        </li>
                        <li>
                            <table class="farms">
                                <thead>
                                    <tr class="header">
                                        <th><img src="<?=$config['localhost']?>media/img/farmer.gif" alt="farmer" title="farmer" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/sword.gif" alt="sword" title="sword" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/spear.gif" alt="spear" title="spear" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/axe.gif" alt="axe" title="axe" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/bow.gif" alt="bow" title="bow" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/spy.gif" alt="spy" title="spy" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/light.gif" alt="light" title="light" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/heavy.gif" alt="heavy" title="heavy" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/ram.gif" alt="ram" title="ram" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/kata.gif" alt="kata" title="kata" style="vertical-align: middle;"/></th>
                                        <th><img src="<?=$config['localhost']?>media/img/snob.gif" alt="snob" title="snob" style="vertical-align: middle;"/></th>
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
                                            <input type="text" name="troops[axe]" class="span2" value="<?= (isset($_POST['troops']['axe'])) ? $_POST['troops']['axe'] : '0' ?>" maxlength="5"/
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
                                            <input type="text" name="troops[ram]" class="span2" value="<?= (isset($_POST['troops']['ram'])) ? $_POST['troops']['ram'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[kata]" class="span2" value="<?= (isset($_POST['troops']['kata'])) ? $_POST['troops']['kata'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input type="text" name="troops[snob]" class="span2" value="<?= (isset($_POST['troops']['snob'])) ? $_POST['troops']['snob'] : '0' ?>" maxlength="2"/>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                    </ul>
                    <input type="submit" value="Save"/>
                </form>
                <? else : ?>
                <p>
                    Not exist any village, create one first on <a href="<?=$config['localhost']?>adminvillages.php">Village administrator</a>
                </p>
                <? endif; ?>
            </div>
        </div>
        <div class="bodyContainer">
            <div class="header">
              <h1><?=$config['player']?> - Farm administration <em style="font-size:0.6em">(<?=date('d/m/Y H:i:s')?> | <?=date_default_timezone_get();?>)</em></h1>
            </div>
            <div class="content">
                <div class="new">
                    <a id="a_new_farm">New farm</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>adminfarms.php">Refresh</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>">« Go Back</a>
                </div>
                <?
                $editable = (isset($action) && isset($id)) && $action == 'edit';
                ?>
                <form action="<?=$config['localhost']?>adminfarms.php?action=<?=($editable) ? 'edit' : 'disable'?>" method="post">
                <table class="farms">
                  <thead>
                    <tr>
                      <th rowspan="2" style="width:20px;"></th>
                      <? if ($editable) : ?>
                      <th rowspan="2">Name</th>
                      <? else : ?>
                      <th rowspan="2">From</th>
                      <? endif; ?>
                      <th rowspan="2">To</th>
					  <th rowspan="2">Range time</th>
					  <th rowspan="2">Itineration</th>
                      <th colspan="11">Troops</th>
                      <th rowspan="2">Actions</th>
                    </tr>
                    <tr class="troops">
						<th><img src="<?=$config['localhost']?>media/img/farmer.gif" alt="farmer" title="farmer" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/sword.gif" alt="sword" title="sword" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/spear.gif" alt="spear" title="spear" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/axe.gif" alt="axe" title="axe" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/bow.gif" alt="bow" title="bow" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/spy.gif" alt="spy" title="spy" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/light.gif" alt="light" title="light" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/heavy.gif" alt="heavy" title="heavy" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/ram.gif" alt="ram" title="ram" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/kata.gif" alt="kata" title="kata" style="vertical-align: middle;"/></th>
						<th><img src="<?=$config['localhost']?>media/img/snob.gif" alt="snob" title="snob" style="vertical-align: middle;"/></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?
                      if ($farms_json) :
						$i = 0;
						$_troops = array();
                    ?>
                    <? foreach ($farms_json as $target_id => $value) : ?>
                            <? if (is_array($value)) : ?>
                              <?
                              if (isset($id) && $target_id == $id) :
                                  $class = 'selected';
                              elseif ($value['enabled'] == 'false') :
                                  $class = 'disabled';
                              elseif ($value['mode'] == 'spy') :
                                  $class = 'spy';
                              else :
                                  $class = null;
                              endif;
                              ?>
                              <tr class="<?=$class?>">
                                  <td>
                                     <input type="checkbox" class="chk" name="select[]" value="<?=$target_id?>"/>
                                  </td>
                                  <? if ($editable && $target_id == $id) : ?>
                                  <td>
									 <span>
										<select class="own-selected" name="fromname">
											<? foreach ($own_villages as $from_id => $village) : ?>
												<? if (!$village_default) : ?>
												<?    $village_default = $from_id; ?>
												<? endif; ?>
												<option value="<?= $from_id ?>" <?=$from_id == $value['from']['id'] ? 'selected="selected"' : null?>><?= $village['name'] ?></option>
											<? endforeach; ?>
										</select>
										<input type="hidden" id="from_id_edit" name="from[id]" value="<?= (isset($value['from']['id'])) ? $value['from']['id'] : null;?>"/>
										<input type="hidden" id="from_name_edit" name="from[name]" value="<?= (isset($value['from']['name'])) ? $value['from']['name'] : null;?>"/>
										<input type="hidden" id="from_x_edit" name="from[x]" value="<?= (isset($value['from']['x'])) ? $value['from']['x'] : null;?>"/>
										<input type="hidden" id="from_y_edit" name="from[y]" value="<?= (isset($value['from']['y'])) ? $value['from']['y'] : null;?>"/>
									 </span>
                                      <br/>
                                      <span>
                                          <input type="radio" name="mode" value="attack" <?= ($value['mode']=='attack') ? 'checked="checked"' : null ?>/> 
                                          Attack
                                      </span>
                                      <span>
                                          <input type="radio" name="mode" value="spy" <?= ($value['mode']=='spy') ? 'checked="checked"' : null ?>/> 
                                          Spy <em>(silent)</em>
                                      </span>
                                      <br/>
                                      <span>
                                        <input type="checkbox" name="nocturnal" value="true" <?= ($value['nocturnal']=='true') ? 'checked="checked"' : null ?>/> 
                                      </span>
                                      Nocturnal attacks
                                      <?if ($value['enabled'] == 'false') : ?>
                                        <input type="hidden" name="enabled" value="false"/>
                                      <? else : ?>
                                        <input type="hidden" name="enabled" value="true"/>
                                      <? endif; ?>
                                  </td>
                                  <? else : ?>
                                  <td>
                                      <?= $value['from']['name'] ?><br/><em>(<?= $value['from']['x'] ?>|<?= $value['from']['y'] ?>)</em>
                                  </td>
                                  <? endif; ?>
                                  <td>
                                      <? if ($editable && $target_id == $id) : ?>
                                          X <input type="text" class="span2" value="<?= $value['to']['x'] ?>" name="to[x]"/> Y <input type="text" class="span2" value="<?= $value['to']['y'] ?>" name="to[y]"/>
										  <input type="hidden" name="to[id]" value="<?= (isset($value['to']['id'])) ? $value['to']['id'] : null;?>"/>
										  <input type="hidden" name="to[name]" value="<?= (isset($value['to']['name'])) ? $value['to']['name'] : null;?>"/>
                                      <? else : ?>
                                          <?=$value['to']['name']?><br/>
                                          <?=$value['to']['x']?> | <?=$value['to']['y']?>
                                      <? endif; ?>
                                  </td>
								  <td>
									  <? if ($editable && $target_id == $id) : ?>
										<span> Start: 
                                           <input type="text" name="start" class="span3 timepicker" value="<?=$value['start']?>" placeholder="Start"/> 
                                        </span>
                                        <span> End: 
                                           <input type="text" name="end" class="span3 timepicker" value="<?=$value['end']?>" placeholder="End"/> 
                                        </span>
									  <? else : ?>
									     <?= ($value['start'] !='') ? "From {$value['start']} to {$value['end']}" : ''?>
									  <? endif; ?>
								  </td>
								  <td>
									<? if ($editable && $target_id == $id) : ?>
                                             <select class="span3" name="iteration">
                                                 <? for($j=0; $j<=16; $j++) : ?>
                                                     <? if ($j != 1) : ?>
                                                     <option value=<?=$j;?><?=($value['iteration'] == $j) ? ' selected="selected"' : null;?>><?=$j;?></option>
                                                     <? endif; ?>
                                                 <? endfor; ?>
                                            </select>
                                        <? else : ?>
                                          <?=($value['iteration'] > 0) ? "x{$value['iteration']}" : $value['iteration']?>
                                      <? endif; ?>
								  </td>
                                  <? foreach($value['troops'] as $name => $troop) : ?>
                                      <td>
                                        <span>
                                            <? if ($editable && $target_id == $id) : ?>
                                                <input type="text" class="span2" value="<?= $troop; ?>" name="troops[<?= $name; ?>]" maxlength="5"/>
                                            <? else : ?>
                                                <?= $troop ?>
                                            <? endif; ?>
											<? if (isset($_troops[$name])) : ?>
											<?    $_troops[$name] += $troop; ?>
											<? else : ?>
											<?    $_troops[$name] = $troop; ?>
											<? endif; ?>
                                        </span>
                                      </td>
                                  <? endforeach; ?>
                                  <td>
                                      <? if ($editable && $target_id == $id) : ?>
										  <input type="hidden" name="id" value="<?=$target_id?>"/>
                                          <input type="submit" value="Save"/><br/><a href="<?=$config['localhost']?>adminfarms.php#<?=$target_id?>" name="<?=$target_id?>">Cancel</a>
                                      <? else : ?>
                                          <a href="<?=$config['localhost']?>adminfarms.php?action=edit&id=<?=$target_id?>#<?=$target_id?>">Edit</a> | <a href="<?=$config['localhost']?>adminfarms.php?action=delete&id=<?=$target_id?>">Delete</a>
                                      <? endif; ?>
                                  </td>
                              </tr>
                              <? $i++; ?>
                            <? endif; ?>
                    <? endforeach; ?>
					<tr>
						<td colspan="5"></td>
						<? if (count($_troops) > 0) : ?>
							<? foreach ($_troops as $num) : ?>
								<td><?=$num?></td>
							<? endforeach; ?>
						<? else : ?>
							<td>0</td>
						<? endif; ?>
						<td><b>Total troops</b></td>
					</tr>
                    <? if ($i == 0) : ?>
                    <tr>
                        <td colspan="16">You don't have any farm.</td>
                    </tr>
                    <? endif; ?>
                    <? else : ?>
                    <tr>
                        <td colspan="16">You don't have any farm.</td>
                    </tr>
                    <? endif; ?>
                    <tr style="border-top: 1px solid #ccc;">
                        <td><input id="select-all" type="checkbox"/></td>
                        <td colspan="16" style="text-align: left;">
                           Select all
                           <input type="submit" name="disable" value="disable"/> <input type="submit" name="enable" value="enable"/>
                        </td>
                     </tr>
                  </tbody>
                </table>
                <? if ($editable) : ?>
                </form>
                <? endif; ?>
            </div>
            <div style="margin-top: 16px; font-size: 0.85em;">
                <div style="width:16px; height:16px; border:1px solid #E0878B; background-color:#F7C6C8; display:inline-block;"></div> Disabled
                <div style="width:16px; height:16px; border:1px solid #99C4EA; background-color:#D1E4F3; display:inline-block;"></div> Spy mode <em>(Silent)</em>
            </div>
        </div>
    </body>
</html>
<? endif; ?>