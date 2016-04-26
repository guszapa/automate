<?php
/**
 * Farm administration
 *
 * @author katan
 */
include_once 'automate.class.php';
$scheduler_json = Automate::factory()->getScheduler();
$own_villages = Automate::factory()->getVillages('own');
$all_villages = Automate::factory()->getVillages();
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
    $mtime = isset($_GET['microtime']) ? $_GET['microtime'] : null;
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
}
switch ($action) {
    case 'new-scheduler': // OK
        if ( !empty($_POST)) {
            // Check empty values
            if (empty($_POST['datetime'])) $msg = $error = "The field <b>datetime</b> can not be empty";
            if (empty($_POST['to']['x'])) $msg = $error = "The coords fields <b>To</b> can not be empty, select a enemy village or insert coordenates";
            if (empty($_POST['from']['id'])) $msg = $error = "The field <b>From ID</b> can not be empty, select a own village or insert the Village ID";
            $toops = 0;
            foreach ($_POST['troops'] as $v) $toops += $v;
            if ($toops == 0) $msg = $error = "You can't send zero troops, review and insert troops";
            // microtime format
            $_miliseconds = explode('.',$_POST['datetime']);
            $departure = strtotime($_POST['departure']).".{$_miliseconds[1]}0";
            // Fix server lag
            // $departure = $departure + $config['server_lag'];
            if ($departure < microtime(true)) $msg = $error = "Your departure is out of time !!";
            if ($departure < 1) $msg = $error = "Departure time error. <a href='{$config['localhost']}adminattacks.php'>refresh page</a>";
            // Save scheduler
            if ( !$error) {
               $scheduler_json[$departure][] = $_POST;
               ksort($scheduler_json, SORT_NUMERIC); //order by departure time ASC
               if ($f = fopen($paths['scheduler'], 'w')) {
                   fwrite($f, json_encode($scheduler_json));
                   fclose($f);
                   $msg = "The attack have been saved. <a href='{$config['localhost']}adminattacks.php'>refresh page</a>";
               } else {
                   Automate::factory()->log('E', "You don't have permission to write {$paths['scheduler']} file");
               }
            }
        }
        break;
    case 'get-village': // OK
          $is_ajax = TRUE;
          $village_data = Automate::factory()->getVillage($_GET['type'], $id);
          echo $village_data;
          break;
    case 'get-distance': // OK
          $is_ajax = TRUE;
          $village_data = Automate::factory()->getStartDistance($_POST);
          echo $village_data;
          break;
    case 'get-game-distance': // REVISION!
          $is_ajax = TRUE;
          $from = array('id'=> $_POST['from_id'], 'x'=> $_POST['from_x'], 'y'=> $_POST['from_y']);
          $to = array('id'=> $_POST['to_id'], 'x'=> $_POST['to_x'], 'y'=> $_POST['to_y']);
          $village_data = Automate::factory()->getStartGameDistance($from, $to, $_POST['troops'], strtotime($_POST['datetime']), $_POST['method']);
          echo $village_data;
          break;
    case 'delete': // OK
        if ($mtime && is_int($id)) {
                if (count($id) > 1) {
                    unset($scheduler_json[$mtime][$id]);
                } else {
                    unset($scheduler_json[$mtime]);
                }
                ksort($scheduler_json, SORT_NUMERIC); //order by departure time ASC	
                // Save file
            if ($f = fopen($paths['scheduler'], 'w')) {
                fwrite($f, json_encode($scheduler_json));
                fclose($f);
                $msg = "The scheduler have been deleted. <a href='{$config['localhost']}adminattacks.php'>refresh page</a>";
             } else {
                 Automate::factory()->log('E', "You don't have permission to write {$paths['scheduler']} file");
             }
        }
        break;
    case 'remove-all':
        if (is_file($paths['scheduler'])) {
            unlink($paths['scheduler']); // Remove file
            $msg = "All schedulers have been deleted. <a href='{$config['localhost']}adminattacks.php'>refresh page</a>";
        }
        header( "Location: {$_SERVER['PHP_SELF']}" );
        break;
    case 'edit': // OK
        if ( !empty($_POST)) {
                // Save data
                $scheduler_json[$_POST['mtime']][$_POST['pos']]['troops'] = $_POST['troops'];
                $scheduler_json[$_POST['mtime']][$_POST['pos']]['iteration'] = $_POST['iteration'];
                $scheduler_json[$_POST['mtime']][$_POST['pos']]['method'] = $_POST['method'];
                ksort($scheduler_json, SORT_NUMERIC); //order by departure time ASC
                // Save file
            if ($f = fopen($paths['scheduler'], 'w')) {
                fwrite($f, json_encode($scheduler_json));
                fclose($f);
                $msg = "The scheduler have been saved. <a href='{$config['localhost']}adminattacks.php'>refresh page</a>";
             } else {
                 Automate::factory()->log('E', "You don't have permission to write {$paths['scheduler']} file");
             }
        }
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
        <title><?=$config['player']?> - Scheduler Attack/Spy/Defense</title>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-purl-2.3.1.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-ui-timepicker-addon.js"></script>
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery-ui-1.10.3.custom.min.css">
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery-ui-timepicker-addon.css">
        <style type="text/css">
          table.scheduler { margin-bottom: 24px; }
          #new_farm, #new_village, #calculator, #log {
            width: 720px;
            top: 75px;
            display: none;
          }
          form ul li table td, .modalbox form ul li table th { padding: 0; }
          .empty, .empty-all {
            float: right;
            padding: 0 4px;
            background: #ECB3B3;
            cursor: pointer;
            margin-left: 0;
          }
          .empty-all {
              font-weight: bold;
              font-size: 0.8em;
              line-height: 20px;
              float: left;
              margin-bottom: 4px;
          }
          form ul li span.empty:hover { background: #E35454; }
          .button { font-size:0.85em; padding: 2px 4px; }
          @media screen and (max-width: 800px){
            #new_farm, #new_village {
                position: absolute;
                width: 580px !important;
                top: 50px;
                left: 0 important!;
                display: none;
              }
            table.farms input[type=text] {
                height: 20px;
                padding: 2px 0;
                font-size: 0.95em;
                text-align: center;
            }
            .span2 { width: 38px; }
            .span1 { width: 18px; }
            .span16 { width: 160px; }
            .modalbox { padding: 8px 10px; }
            .modalbox .container { margin-top: 0; }
            #str_departure { font-size:0.85em; }
          }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                // OPEN
                jQuery('#a_new_village').click(function(){
                    jQuery('#modalbox').css('display', 'block');
                    this.padding = parseInt(jQuery('#new_village').css('padding-left')) + parseInt(jQuery('#new_village').css('padding-right'));
                    this.left = Math.round((jQuery(window).width() - (jQuery('#new_village').width()+this.padding))/2);
                    jQuery('#new_village').css({'display': 'block', 'left': this.left + 'px'});
                });
                // Insert troops & coords (own village)
                jQuery('#own-selected').on('change', function(){
                    $.ajax({
                        url: "<?=$config['localhost']?>adminattacks.php?action=get-village&type=own&id="+jQuery(this).val(),
                        context: document.body,
                        dataType: 'json'
                        }).done(function(data) {
                            jQuery.each(data.troops, function(key, value) {
                                jQuery('#'+key).val(value);
                            });
                            jQuery('#from_x').val(data.x);
                            jQuery('#from_y').val(data.y);
                            jQuery('#from_id').val(data.id);
                        });
                });
                jQuery("#checktime").on('click', function() {
                    if (jQuery(this).hasClass('enabled')) {
                        jQuery(this).html('wait ...');
                        $.ajax({
                            url: "<?=$config['localhost']?>adminattacks.php?action=get-game-distance",
                            context: document.body,
                            dataType: 'json',
                            type: 'post',
                            data: {datetime:$('#arrival').val(),method:$('input[name=method]:checked','#scheduler_form').val(),from_id: $('#from_id').val(),from_x: $('#from_x').val(),from_y:$('#from_y').val(),to_id: $('#to_id').val(),to_x:$('#to_x').val(), to_y: $('#to_y').val(),troops:{'farmer':$('#farmer').val(),'sword':$('#sword').val(),'spear':$('#spear').val(),'axe':$('#axe').val(),'bow':$('#bow').val(),'spy':$('#spy').val(),'light':$('#light').val(),'heavy':$('#heavy').val(),'ram':$('#ram').val(),'kata':$('#kata').val(),'snob':$('#snob').val()}}
                        }).done(function(data) {
                            if (data.type == 'ok') {
                                jQuery('#str_departure').html("<b>Departure:</b> "+data.start+" <em>("+data.distance+")<em>");
                                jQuery('#departure').val(data.start);
                            } else {
                                alert(data.msg);
                            }
                            jQuery("#checktime").html('check!');
                        });
                    }
                });
                // Insert coords (enemy village)
                jQuery('.target-selected').on('change', function(){
                    var type = jQuery(this).data('type');
                    $.ajax({
                        url: "<?=$config['localhost']?>adminattacks.php?action=get-village&type="+type+"&id="+jQuery(this).val(),
                        context: document.body,
                        dataType: 'json'
                        }).done(function(data) {
                            jQuery('#to_id').val(data.id);
                            jQuery('#to_x').val(data.x);
                            jQuery('#to_y').val(data.y);
                        });
                });
                $msg = false;
                // Calculate distance
                jQuery('.distance').on('distance', function(){
                    jQuery(this).on('change', function(){
                        if (jQuery('#from_x').val() && jQuery('#from_x').val() && jQuery('#to_x').val() && jQuery('#to_x').val())
                        {
                            $.ajax({
                                url: "<?=$config['localhost']?>adminattacks.php?action=get-distance",
                                context: document.body,
                                dataType: 'json',
                                type: 'post',
                                data: {from_x: $('#from_x').val(),from_y:$('#from_y').val(),to_x:$('#to_x').val(), to_y: $('#to_y').val(),method:$('input[name=method]:checked','#scheduler_form').val(),datetime:$('#arrival').val(),troops:{'farmer':$('#farmer').val(),'sword':$('#sword').val(),'spear':$('#spear').val(),'axe':$('#axe').val(),'bow':$('#bow').val(),'spy':$('#spy').val(),'light':$('#light').val(),'heavy':$('#heavy').val(),'ram':$('#ram').val(),'kata':$('#kata').val(),'snob':$('#snob').val()}}
                            }).done(function(data) {
                                jQuery('#str_departure').html("<b>Departure:</b> "+data.start+" <em>("+data.distance+")<em>");
                                jQuery('#departure').val(data.start);
                                jQuery("#checktime").removeClass('disabled').addClass('enabled');
                            });
                        } else {
                            if (!$msg) {
                                $msg = true;
                                alert('Fill first the upper fields');
                            }
                        }
                    });
                });
                // trigger
                $( ".distance" ).trigger( "distance" );
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
                // Select all/none farms
                jQuery('#select-all').on('click', function(){
                   if(jQuery(this).is(':checked')) {
                      jQuery(".chk:checkbox:not(:checked)").attr("checked", "checked");
                   } else {
                      jQuery(".chk:checkbox:checked").removeAttr("checked");
                   }
                });
                // Datepicker
                jQuery( ".timepicker" ).datetimepicker({
                  timeFormat: 'HH:mm:ss.l',
                  stepHour: 1, stepMinute: 1, stepSecond: 1, stepMillisec: 100,
                  hour: <?=date('H')?>,
                  minute: <?=date('i')?>,
                  secondMin: 00, secondMax: 59,
                  millisecMin: 100
                });
                // Empty troops
                jQuery('.empty').on('click', function(){
                    jQuery('#'+jQuery(this).data('id')).val(0);
                    jQuery('#'+jQuery(this).data('id')).change();
                });
                jQuery('.empty-all').on('click', function(){
                    jQuery('table').find("input").val(0);
                    
                });
				        // Confirm before delete
                jQuery('.delete-all').on('click', function() {
                		if (confirm('Are you sure to delete all schedulers?')) window.location.href = '<?=$config['localhost']?>' + jQuery(this).data('location');
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
                <form id="scheduler_form" action="?action=new-scheduler" method="post">
                    <ul>
                            <li>
                            <label>From: <em>(own village)</em></label>
                            <select id="own-selected" class="span16"> <!-- No name value, via ajax -->
                                <option disabled="disabled" selected="selected">- Select a own village -</option>
                                <? foreach($own_villages as $village) : ?>
                                    <option value="<?=$village['id']?>""<?=(isset($_POST['from_id']) && $_POST['from_id'] == $village['id']) ? ' selected="selected"' : null;?>><?="{$village['name']} ({$village['x']}|{$village['y']})";?></option>
                                <? endforeach; ?>
                            </select>
                            <span>- o -</span>
                            <span>ID: <input id="from_id" class="span4" type="text" name="from[id]" maxlength="3" placeholder="Village ID" value="<?= (isset($_POST['from_id'])) ? $_POST['from_id'] : null;?>"/></span>
                            <input type="hidden" id="from_x" name="from[x]" value="<?= (isset($_POST['from_x'])) ? $_POST['from_x'] : null;?>"/>
                            <input type="hidden" id="from_y" name="from[y]" value="<?= (isset($_POST['from_y'])) ? $_POST['from_y'] : null;?>"/>
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
                             <? $_method = isset($_POST['method']) ? true : false; ?>
                             <label>Method:</label>
                             <span>attack: <input type="radio" name="method" value="attack" <?=($_method && ($_POST['method'] == 'attack')) ? 'checked="checked"' : 'checked="checked"';?>/></span>
                             <span>spy: <input type="radio" name="method" value="spy" <?=($_method && ($_POST['method'] == 'spy')) ? 'checked="checked"' : null;?>/></span>
                             <span>support: <input type="radio" name="method" value="support" <?=($_method && ($_POST['method'] == 'support')) ? 'checked="checked"' : null;?>/></span>
                        </li>
                        <li>
                            <label>Arrival: <em>(to target)</em></label>
                            <input id="arrival" type="text" name="datetime" class="span10 timepicker distance" value="<?= (isset($_POST['datetime'])) ? $_POST['datetime'] : null;?>"/>
                            <div id="checktime" class="green button inline disabled">check!</div>
                            <div id="str_departure" class="inline"></div>
                            <input id="departure" type="hidden" name="departure" value="<?= (isset($_POST['departure'])) ? $_POST['departure'] : null;?>"/>
                        </li>
                        <li>
                            <label>Kata target:</label>
                            <select name="kata_target">
                                <option value="" selected="selected">-Select one-</option>
                                <option value="stone">stone</option>
                                <option value="wood">wood</option>
                                <option value="iron">iron</option>
                                <option value="storage">storage</option>
                                <option value="farm">farm</option>
                                <option value="barracks">barracks</option>
                                <option value="wall">wall</option>
                                <option value="stable">stable</option>
                                <option value="market">market</option>
                                <option value="garage">garage</option>
                                <option value="snob">snob</option>
                                <option value="smith">smith</option>
                                <option value="statue">statue</option>
                            </select>
                            <span>
                                <em>(optional, default none)</em>
                            </span>
                        </li>
                        <li>
                            <div class="empty-all">-reset all-</div>
                            <table class="farms fixed">
                                <thead>
                                    <tr class="header">
                                        <th>farmer <span class="empty" data-id="farmer">x</span></th>
                                        <th>sword <span class="empty" data-id="sword">x</span></th>
                                        <th>spear <span class="empty" data-id="spear">x</span></th>
                                        <th>axe <span class="empty" data-id="axe">x</span></th>
                                        <th>bow <span class="empty" data-id="bow">x</span></th>
                                        <th>spy <span class="empty" data-id="spy">x</span></th>
                                        <th>light <span class="empty" data-id="light">x</span></th>
                                        <th>heavy <span class="empty" data-id="heavy">x</span></th>
                                        <th>ram <span class="empty" data-id="ram">x</span></th>
                                        <th>kata <span class="empty" data-id="kata">x</span></th>
                                        <th>snob <span class="empty" data-id="snob">x</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input id="farmer" type="text" name="troops[farmer]" class="span2 distance" value="<?= (isset($_POST['troops']['farmer'])) ? $_POST['troops']['farmer'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input id="sword" type="text" name="troops[sword]" class="span2 distance" value="<?= (isset($_POST['troops']['sword'])) ? $_POST['troops']['sword'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input id="spear" type="text" name="troops[spear]" class="span2 distance" value="<?= (isset($_POST['troops']['spear'])) ? $_POST['troops']['spear'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input id="axe" type="text" name="troops[axe]" class="span2 distance" value="<?= (isset($_POST['troops']['axe'])) ? $_POST['troops']['axe'] : '0' ?>" maxlength="5"/
                                        </td>
                                        <td>
                                            <input id="bow" type="text" name="troops[bow]" class="span2 distance" value="<?= (isset($_POST['troops']['bow'])) ? $_POST['troops']['bow'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input id="spy" type="text" name="troops[spy]" class="span2 distance" value="<?= (isset($_POST['troops']['spy'])) ? $_POST['troops']['spy'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input id="light" type="text" name="troops[light]" class="span2 distance" value="<?= (isset($_POST['troops']['light'])) ? $_POST['troops']['light'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input id="heavy" type="text" name="troops[heavy]" class="span2 distance" value="<?= (isset($_POST['troops']['heavy'])) ? $_POST['troops']['heavy'] : '0' ?>" maxlength="5"/>
                                        </td>
                                        <td>
                                            <input id="ram" type="text" name="troops[ram]" class="span1 distance" value="<?= (isset($_POST['troops']['ram'])) ? $_POST['troops']['ram'] : '0' ?>" maxlength="4"/>
                                        </td>
                                        <td>
                                            <input id="kata" type="text" name="troops[kata]" class="span1 distance" value="<?= (isset($_POST['troops']['kata'])) ? $_POST['troops']['kata'] : '0' ?>" maxlength="4"/>
                                        </td>
                                        <td>
                                            <input id="snob" type="text" name="troops[snob]" class="span1 distance" value="<?= (isset($_POST['troops']['snob'])) ? $_POST['troops']['snob'] : '0' ?>" maxlength="2"/>
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
        <div class="bodyContainer">
            <div class="header">
              <h1><?=$config['player']?> - Scheduler Attack/Spy/Defense <em style="font-size:0.6em">(<?=date('d/m/Y H:i:s')?> | <?=date_default_timezone_get();?>)</em></h1>
            </div>
                <div class="new">
                    <a id="a_new_village" href="#new-farm">New Scheduler</a> <span class="separator"> | </span> <a class="delete-all" data-location="adminattacks.php?action=remove-all">Remove schedulers</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>adminattacks.php">Refresh</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>">« Go Back</a>
                </div>
                <?
                $editable = (isset($action) && isset($id)) && $action == 'edit';
                ?>
                <form action="?action=<?=($editable) ? 'edit' : 'disable'?>" method="post">
                <div>
                <table class="farms scheduler">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Departure Time</th>
                      <th>From</th>
                      <th>To</th>
                      <th>Troops</th>
                      <th class="span2">Iteration</th>
                      <th class="span2">Method</th>
                      <th class="span3">Kata target</th>
                      <th class="span3">Departure</th>
                      <th class="span3">Arrival</th>
                      <th class="span5">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  <? $count = 1; ?>
                  <? if (count($scheduler_json) > 0 && $scheduler_json) : ?>
                    <? foreach ($scheduler_json as $microtime => $attacks) : ?>
                            <? foreach ($attacks as $i => $attack) : ?>
                              <? if ($i == 1) { $count++; } ?>
                              <tr>
                                  <td><?=$count?></td>
                                   <? if($i == 0) : ?>
                                  <td rowspan="<?=(count($attacks) > 1) ? count($attacks) : '1';?>">
                                       <?= $microtime ?>
                                  </td>
                                  <? endif; ?>
                                  <td>
                                       <?=$own_villages[$attack['from']['id']]['name']?> (<?=$own_villages[$attack['from']['id']]['x']?>|<?=$own_villages[$attack['from']['id']]['y']?>)
                                  </td>
                                  <!-- ENEMY VILLAGE -->
                                  <td>
                                   <? if (isset($attack['to']['id']) && $attack['to']['id'] != '') : ?>
                                      <? foreach($all_villages as $types) : ?>
                                          <? foreach($types as $village_id => $village) : ?>
                                            <? if ($village_id == $attack['to']['id']) : ?>
                                            <?=isset($village['player_name']) ? "{$village['player_name']} - ": NULL ?><?=$village['name']?> 
                                            <em>
                                                (<a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$attack['from']['id'].$config['info_village'].$attack['to']['id']?>" target="_blank"><?=$attack['to']['x']?>|<?=$attack['to']['y']?></a>)
                                            </em>
                                            <? break(2); ?>
                                            <? endif; ?>
                                          <? endforeach; ?>
                                      <? endforeach; ?>
                                   <? else : ?>
                                      <?=$attack['to']['x']?>|<?=$attack['to']['y']?>
                                   <? endif; ?>
                                  </td>
                                  <!-- TROOPS -->
                                  <td class="alignleft">
                                        <? foreach($attack['troops'] as $name => $troop) : ?>
                                             <? if ($editable && $mtime == $microtime && $i == $id) : ?>
                                             <div>
                                          <span class="left" style="line-height: 36px; width:50%; font-size: 0.95em;"><?=$name?>:</span>
                                          <span class="right" style="line-height: 36px; width:50%;"><input type="text" class="span2" value="<?= $troop; ?>" name="troops[<?= $name; ?>]" maxlength="5"/></span>
                                          <span class="both"></span>
                                        </div>
                                        <? else: ?>
                                             <? if ($troop > 0) : ?>
                                                  <div class="inline">
                                                      <div class="inlineblock">
                                                         <span class="left" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/<?=$name?>.gif" alt="<?=$name?>" title="<?=$name?>" style="vertical-align: middle;"/></span>
                                                          <span class="left" style="line-height: 24px;">&nbsp;<?=number_format($troop, 0, ',', '.')?></span>
                                                          <span class="both"></span>
                                                      </div>
                                                 </div>
                                             <? endif; ?>
                                            <? endif; ?>
                                      <? endforeach; ?>
                                  </td>
                                  <!-- ITERATION -->
                                  <td class="aligncenter">
                                      <? if ($editable && $mtime == $microtime && $i == $id) : ?>
                                             <select class="span3" name="iteration">
                                                 <? for($j=0; $j<=16; $j++) : ?>
                                                     <? if ($j != 1) : ?>
                                                     <option value=<?=$j;?><?=($attack['iteration'] == $j) ? ' selected="selected"' : null;?>><?=$j;?></option>
                                                     <? endif; ?>
                                                 <? endfor; ?>
                                            </select>
                                        <? else : ?>
                                          <?=($attack['iteration'] > 0) ? "x{$attack['iteration']}" : $attack['iteration']?>
                                      <? endif; ?>
                                  </td>
                                  <td class="aligncenter">
                                      <? if ($editable && $mtime == $microtime && $i == $id) : ?>
                                          <select class="span5" name="method">
                                              <option value="attack" <?=($attack['method'] == 'attack') ? 'selected="selected"' : null;?>>attack</option>
                                                  <option value="spy" <?=($attack['method'] == 'spy') ? 'selected="selected"' : null;?>>spy</option>
                                                  <option value="support" <?=($attack['method'] == 'support') ? 'selected="selected"' : null;?>>support</option>
                                          </select>
                                      <? else : ?>
                                          <?=$attack['method']?>
                                      <? endif; ?>
                                  </td>
                                  <td>
                                    <?=$attack['kata_target']?>
                                  </td>
                                  <td class="aligncenter">
                                      <?=strftime("%e %b %k:%M:%S", strtotime($attack['departure']))?>
                                  </td>
                                  <td class="aligncenter">
                                      <?=strftime("%e %b %k:%M:%S", strtotime($attack['datetime']))?>
                                  </td>
                                  <td>
                                    <? if ($editable && $mtime == $microtime && $i == $id) : ?>
                                        <input type="hidden" name="mtime" value="<?=$microtime?>"/>
                                        <input type="hidden" name="pos" value="<?=$i?>"/>
                                       <input type="submit" value="Save"/><br/><a href="?#cancel">Cancel</a>
                                    <? else : ?>
                                       <a href="?action=edit&microtime=<?=$microtime?>&id=<?=$i?>">Edit</a> | <a href="?action=delete&microtime=<?=$microtime?>&id=<?=$i?>">Delete</a>
                                    <? endif; ?>
                                  </td>
                              </tr>
                        <? endforeach; ?>
                        <? $count++ ?>
                    <? endforeach; ?>
                  <? else : ?>
                    <tr>
                        <td colspan="10">You don't have any scheduler attack.</td>
                    </tr>
                  <? endif; ?>
                  </tbody>
                </table>
                </form>
            </div>
        </div>
    </body>
</html>
<? endif; ?>