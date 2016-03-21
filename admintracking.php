<?php
/**
 * Autoleveler administration
 *
 * @author katan
 */
include_once 'automate.class.php';
$tracking = Automate::factory()->getTracking();
$paths = Automate::factory()->getPaths();
$config = Automate::factory()->getConfig();
$msg = '';
$error = false;
$is_ajax = false;

// Parse tracking data to grouping by alliance
$trackingParsed = array();
foreach ($tracking as $key => $value) {
  $userData = array();
  // Create a alliance if not exists
  if (!array_key_exists($value['alliance'], $trackingParsed)) {
    $trackingParsed[$value['alliance']] = array(); 
  }
  // Add info inside their alliance
  $userData[$key] = $value;
  $trackingParsed[$value['alliance']] = $userData;
}

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
    case 'new-tracking': // OK
        if ( !empty($_POST)) {
            // Call game to recovery de user data
            $_url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?{$config['info_player']}{$_POST['id']}";
            $_res = @Automate::factory()->tracking($_url, false, false);
            if (is_array($_res) && count($_res) > 0) {
                $_res['name'] = $_POST['name'];
                $_res['enabled'] = true;
                // Save data
                $tracking[$_POST['id']] = $_res;
                if ($f = fopen($paths['tracking'], 'w')) {
                   fwrite($f, json_encode($tracking));
                   fclose($f);
                   @chmod($paths['tracking'], 0777);
                   $msg = "The player tracking has been saved. <a href='{$config['localhost']}admintracking.php'>refresh page</a>";
               } else {
                   Automate::factory()->log('E', "You don't have permission to write {$paths['tracking']} file");
               }
            }
        }
        break;
    case 'disabled': // OK
        if ( !empty($_POST)) {
           if (isset($_POST['select']) && count($_POST['select']) > 0) {
              foreach($_POST['select'] as $k => $player_id) {
                  $tracking[$player_id]['enabled'] = (isset($_POST['disable'])) ? 0 : 1;
              }
              if ($f = fopen($paths['tracking'], 'w')) {
                  fwrite($f, json_encode($tracking));
                  fclose($f);
                  $msg = "The changes have been saved. <a href='{$config['localhost']}admintracking.php'>refresh page</a>";
              } else {
                  Automate::factory()->log('E', "You don't have permission to write {$paths['tracking']} file");
              }
           }
        }
        break;
    case 'delete': // OK
    	  unset($tracking[$id]);
    	  // Save file
        if ($f = fopen($paths['tracking'], 'w')) {
        	  fwrite($f, json_encode($tracking));
	        fclose($f);
	        $msg = "The village has been deleted. <a href='{$config['localhost']}admintracking.php'>refresh page</a>";
	     } else {
	     	  Automate::factory()->log('E', "You don't have permission to write {$paths['tracking']} file");
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
        <title><?=$config['player']?> - Tracking players Administrator</title>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-ui-1.10.3.custom.min.js"></script>
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery-ui-1.10.3.custom.min.css">
        <style type="text/css">
          #player_tracking {
            width: 780px;
            top: 75px;
			display: none;
          }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                // OPEN modals
                jQuery('#a_new_tracking').click(function(){
                    jQuery('#modalbox').css('display', 'block');
                    this.padding = parseInt(jQuery('#player_tracking').css('padding-left')) + parseInt(jQuery('#player_tracking').css('padding-right'));
                    this.left = Math.round((jQuery(window).width() - (jQuery('#player_tracking').width()+this.padding))/2);
                    jQuery('#player_tracking').css({'display': 'block', 'left': this.left + 'px'});
                });
                // CLOSE modals
                jQuery('.close').click(function(){
                    jQuery('#modalbox').css('display', 'none'); jQuery('#player_tracking').css('display', 'none');
                });
                // CLOSE ESC modals
                jQuery(window).keyup(function(e){
                    if (e.keyCode == 27) { // press ESC
                        jQuery('#modalbox').css('display', 'none'); jQuery('#player_tracking').css('display', 'none');
                    }
                });
                // Confirm befrore delete
                jQuery('.delete-row').on('click', function() {
                		if (confirm('¿Are you sure to delete this row?')) window.location.href = '<?=$config['localhost']?>' + jQuery(this).data('location');
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
        <!-- ADD VILLAGE WITH BUILDING LEVELS -->
        <div id="player_tracking" class="modalbox">
            <div class="close">close</div>
            <div class="container">
                <form id="village_form" action="<?=$config['localhost']?>admintracking.php?action=new-tracking" method="post">
                    <ul>
                    		<li>
                            <label>Player ID</label>
                            <input type="text" name="id" class="span3" value="<?= (isset($_POST['id'])) ? $_POST['id'] : null;?>"/>
                        </li>
                        <li>
                            <label>Player Name</label>
                            <input type="text" name="name" class="span6" value="<?= (isset($_POST['name'])) ? $_POST['name'] : null;?>"/>
                        </li>
                    </ul>
                    <input type="submit" value="Save"/>
                </form>
            </div>
        </div>
        <!-- END -->
        <div class="bodyContainer">
            <div class="header">
              <h1><?=$config['player']?> - Tracking players Administrator <em style="font-size:0.6em">(<?=date('d/m/Y H:i:s')?> | <?=date_default_timezone_get();?>)</em></h1>
            </div>
              <div class="new">
                  <a id="a_new_tracking">New tracking</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>admintracking.php">Refresh</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>">« Go Back</a>
              </div>
              <form action="<?=$config['localhost']?>admintracking.php?action=disabled" method="post">
                <!-- Loop for any alliance -->                
                <? foreach($trackingParsed as $key => $track) : ?>
                  <!-- Alliance title -->
                  <h2 class="left" style="width: 100%; margin-top: 16px">
                    <? if ($key == '') : ?>
                      Without alliance
                    <? else : ?>
                      <?= $key ?>
                    <? endif ?>
                  </h2>
                  <!-- players stats -->
                  <div class="left" style="margin-right: 10px;">
                    <table class="farms">
                      <thead>
                        <tr>
                          <th class="span1"></th>
                          <th>Player ID</th>
                          <th>Player Name</th>
                          <th>Aliance (position)</th>
                          <th>Total points</th>
                          <th>Position</th>
                          <th>Villages</th>
                          <th>Average points</th>
                          <th>Combats</th>
                          <th>Defeated opponents</th>
                          <th class="span7">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <? if (is_array($track) && count($track) > 0) : ?>
                          <? foreach ($track as $player_id => $data) : ?>
                          		<tr class="<?=$data['enabled'] ? 'enable' : 'disabled'?>">
                          			<td><input type="checkbox" class="chk" name="select[]" value="<?=$player_id?>"/></td>
                          			<td>
                          				<a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?<?=$config['info_player']?><?=$player_id?>" target="_blank">
                          					<?=$player_id ?>
                          				</a>
                          			</td>
                          			<td><?=$data['name']?></td>
                          			<td><?=isset($data['alliance']) ? $data['alliance'] : '------'?></td>
                          			<td><?=number_format($data['total_points'], 0, ',', '.')?></td>
                          			<td><?=number_format($data['position'], 0, ',', '.')?></td>
                          			<td><?=number_format($data['total_villages'], 0, ',', '.')?></td>
                          			<td><?=number_format($data['average_points'], 0, ',', '.')?></td>
                          			<td><?=number_format($data['combats'], 0, ',', '.')?></td>
                          			<td><?=number_format($data['defeat_opponents'], 0, ',', '.')?></td>
                          			<td>
                          				<br/>
                          				<a href="<?=$config['localhost']?>stats.php?player=<?=$player_id?>" class="queue green button">Stats</a>
                          				<a class="delete-row" data-location="admintracking.php?action=delete&id=<?=$player_id?>"  data-id="<?=$player_id?>">Delete</a>
                          				<br/><br/>
                          			</td>
                          		</tr>
                          <? endforeach; ?>
                        <? else : ?>
                          <tr>
                              <td colspan="11">You don't have any player tracking.</td>
                          </tr>
                        <? endif; ?>
                        	<tr style="border-top: 1px solid #ccc;">
                              <td><input id="select-all" type="checkbox"/></td>
                              <td colspan="10" style="text-align: left;">
                                 <span>Select all</span>
                                 <input type="submit" name="disable" value="Disable"/>
                                 <input type="submit" name="enable" value="Enable"/>
                              </td>
                           </tr>
                        </tbody>
                      </table>
                    </div>
                </form>
              <? endforeach; ?>
            </div>
        </div>
    </body>
</html>
<? endif; ?>