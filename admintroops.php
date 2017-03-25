<?php
include_once 'automate.class.php';
$own_villages = Automate::factory()->getVillages('own');
$recruit = json_decode(Automate::factory()->getRecruit(), TRUE);
$buildings = json_decode(Automate::factory()->getBuildingsRules(), TRUE);
$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
$_time = time();
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
    case 'recruit': // OK
        if ( !empty($_POST)) {
            // Check empty values
            if (empty($_POST['village'])) $msg = $error = "You must select a village";
            // Save scheduler
            if ( !$error) {
               $_village = $_POST['village'];
               unset($_POST['village']);
               $_primary = '';
               foreach ($_POST as $key => $value) {
                  if ($key != "primary") {
                    if ($_primary == '' && $value > 0) {
                       $_primary = $key;
                    }
                    if (isset($recruit[$_village]) && array_key_exists($key, $recruit[$_village]) && (int)$recruit[$_village] > 0) {
                       $recruit[$_village][$key] += (int)$_POST[$key];
                    } else {
                       $recruit[$_village][$key] = (int)$_POST[$key];
                    }
                  } else {
                     $recruit[$_village][$key] = $_POST[$key]; // Primary recruit
                  }
               }
               if (!isset($_POST['primary'])) {
                 $recruit[$_village]['primary'] = $_primary; // Add first troop like primary recruit
               }
               if ($f = fopen($paths['recruit'], 'w')) {
                   fwrite($f, json_encode($recruit));
                   fclose($f);
                   $msg = "The recruit troops has been saved. <a href='{$config['localhost']}admintroops.php'>refresh page</a>";
               } else {
                   Automate::factory()->log('E', "You don't have permission to write {$paths['recruit']} file");
               }
            }
        }
        break;
    case 'delete': // OK
    	  unset($recruit[$id]);
    	  // Save file
        if ($f = fopen($paths['recruit'], 'w')) {
        	  fwrite($f, json_encode($recruit));
	        fclose($f);
	        $msg = "The recruit troops has been deleted. <a href='{$config['localhost']}admintroops.php'>refresh page</a>";
	     } else {
	     	  Automate::factory()->log('E', "You don't have permission to write {$paths['recruit']} file");
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
        <title><?=$config['player']?> - Village building's autoleveler</title>
        <style type="text/css">
    		  .modalbox {
    			 padding: 12px 16px;
    		  }
    		  .queue {
      			max-height: 420px;
      			overflow: hidden;
      			overflow-y: auto;
    		  }
          #village_buildings, .queued_buildings {
            width: 710px;
            top: 50px;
            display: none;
          }
          .a-select-buildings {
          	width: 110px; height: 30px;
          	display: inline-block;
          	padding: 0 8px;
          	cursor: pointer;
          	border: 1px solid #ccc;
          }
          .a-select-buildings.active { background-color: #eee; }
          .select-buildings {
          	display: none;
          	width: 126px;
          	position: absolute;
          	right: 0;
          	top: 31px;
          	border: 1px solid #ccc;
          	background-color: #fff;
          }
          .select-buildings ul li { padding: 0; }
          .select-buildings ul li a {
          	display: block;
          	padding: 0 8px;
          	color: #444;
          	text-decoration: none;
          }
          .select-buildings ul li a:hover { background-color: #ebf990; }
          .add-autoleveler {
          	padding: 1px 8px;
          	cursor: pointer;
          	font-weight: bold;
          	font-size: 21px;
          	border-radius: 14px;
          	background-color: #b4cf00;
          }
    		  .autoleveler {
      			max-height: 360px;
      			overflow-x: hidden;
      			overflow-y: auto;
    		  }
          @media screen and (max-width: 800px){
            #village_buildings, .queued_buildings {
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
          }
        </style>
		<script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-purl-2.3.1.js"></script>
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery-ui-1.10.3.custom.min.css">
        <script type="text/javascript">
            jQuery(document).ready(function() {
               // Confirm befrore delete
                jQuery('.delete-row').on('click', function() {
                     if (confirm('¿Are you sure to delete this row?')) window.location.href = '<?=$config['localhost']?>' + jQuery(this).data('location');
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
        <div class="bodyContainer">
            <div class="header">
				<h1><?=$config['player']?> - Troops administration <em style="font-size:0.6em">(<?=date('d/m/Y H:i:s')?> | <?=date_default_timezone_get();?>)</em></h1>
			</div>
			<div class="content">
				<div class="new">
                    </span> <a href="<?=$config['localhost']?>admintroops.php">Refresh</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>">« Go Back</a>
                </div>
				<? if (is_array($own_villages) && count($own_villages) > 0) : ?>
               <!-- Add troops -->
               <h4 style="margin-top: 16px;">Recruit</h4>
               <form action="<?=$config['localhost']?>admintroops.php?action=recruit" method="post">
                  <table class="farms">
                    <thead>
                        <tr>
                           <th>Village</th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/farmer.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/sword.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/spear.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/axe.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/bow.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/spy.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/light.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/heavy.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/ram.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/kata.gif" style="vertical-align: middle;"/></span></th>
                           <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/snob.gif" style="vertical-align: middle;"/></span></th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                           <td>
                              <select class="span16" name="village"> <!-- No name value, via ajax -->
                                 <option disabled="disabled" selected="selected">- Select village -</option>
                                 <? foreach($own_villages as $village) : ?>
                                    <option value="<?=$village['id']?>" <?=(isset($_POST['from_id']) && $_POST['from_id'] == $village['id']) ? ' selected="selected"' : null;?>>
                                       <?="{$village['name']} ({$village['x']}|{$village['y']})";?> - <em><?=$village["settlers"]?> settlers</em>
                                    </option>
                                 <? endforeach; ?>
                               </select>
                           </td>
                           <? foreach($config['troops_speed'] as $key => $value) : ?>
                              <td>
                                 <input type="text" name="<?=$key?>" class="span2" value="<?=isset($_POST[$key]) && $_POST[$key] > 0 ? $_POST[$key] : ''?>"/>
                              </td>
                           <? endforeach; ?>
                           <td>
                              <input type="submit" value="Recruit" class="green button" />
                           </td>
                        </tr>
                        <tr>
                          <td>Primary troop to recruit</td>
                          <? foreach ($config['troops_speed'] as $key => $value) : ?>
                           <td>
                              <input type="radio" name="primary" value="<?=$key?>"/>
                           </td>
                          <? endforeach; ?>
                          <td></td>
                        </tr>
                     </tbody>
                  </table>
               </form>
               <!-- Recruiting troops -->
               <h4 style="margin-top: 16px;">Recruiting troops</h4>
               <table class="farms">
                 <thead>
                     <tr>
                        <th>Village</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/farmer.gif" style="vertical-align: middle;"/></span> farmer</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/sword.gif" style="vertical-align: middle;"/></span> sword</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/spear.gif" style="vertical-align: middle;"/></span> spear</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/axe.gif" style="vertical-align: middle;"/></span> axe</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/bow.gif" style="vertical-align: middle;"/></span> bow</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/spy.gif" style="vertical-align: middle;"/></span> spy</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/light.gif" style="vertical-align: middle;"/></span> light</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/heavy.gif" style="vertical-align: middle;"/></span> heavy</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/ram.gif" style="vertical-align: middle;"/></span> ram</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/kata.gif" style="vertical-align: middle;"/></span> kata</th>
                        <th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/snob.gif" style="vertical-align: middle;"/></span> snob</th>
                        <th></th>
                     </tr>
                  </thead>
                  <tbody
                     <? if (count($recruit) > 0) : ?>
                     <? foreach ($recruit as $village_id => $troops) : ?>
                        <? $_recruiting = 0; ?>
                        <? foreach ($troops as $troop => $value) : ?>
                           <? $_recruiting += (int)$value; ?>
                        <? endforeach; ?>
                        <? if ($_recruiting > 0) : ?>
                           <tr>
                              <td>
                                 <?=$own_villages[$village_id]['name']?>
                              </td>
                              <? foreach ($troops as $troop => $value) : ?>
                                <? if ($troop != 'primary') : ?>
                                 <td <?=$troops['primary'] == $troop ? 'style="color:green;"': null?>>
                                    <b><?=$value?></b>
                                    <span class="block">
                                    <? if ($value > 0 && isset($own_villages[$village_id]['buildings']['barracks']) && $own_villages[$village_id]['buildings']['barracks'] > 0) : ?>
                                       <em class="inline-block">
                                          <? $_barracks_feature = ($buildings['buildings']['barracks']['feature'][$own_villages[$village_id]['buildings']['barracks']-1]/100)*2; ?>
                                          <? $_recruiting_time = $value*(($buildings['troops'][$troop]['buildTime']*$_barracks_feature)/$config['speed']); ?>
                                          (<?= date('d/m/Y H:i', $_time+round($_recruiting_time,0)) ?>
                                           - <?=Automate::factory()->getformatTime($_recruiting_time)?>)
                                       </em>
                                    <? endif; ?>
                                    </span>
                                 </td>
                                <? endif; ?>
                              <? endforeach; ?>
                              <td>
                                 <a class="delete-row" data-location="admintroops.php?action=delete&id=<?=$village_id?>" data-id="<?=$village_id?>">Delete</a>
                              </td>
                           </tr>
                        <? endif; ?>
                     <? endforeach; ?>
                     <? endif; ?>
                  </tbody>
               </table>
					<!-- All troops -->
               <h4 style="margin-top: 16px;">Current troops</h4>
					<table class="farms">
					  <thead>
						<tr>
						  <th rowspan="2">num</th>
						  <th rowspan="2" class="span3">ID</th>
						  <th rowspan="2" colspan="4">Name</th>
						  <th rowspan="2" class="span3">Free settlers</th>
						  <th colspan="11">Troops</th>
						</tr>
						<tr>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/farmer.gif" style="vertical-align: middle;"/></span> farmer</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/sword.gif" style="vertical-align: middle;"/></span> sword</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/spear.gif" style="vertical-align: middle;"/></span> spear</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/axe.gif" style="vertical-align: middle;"/></span> axe</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/bow.gif" style="vertical-align: middle;"/></span> bow</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/spy.gif" style="vertical-align: middle;"/></span> spy</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/light.gif" style="vertical-align: middle;"/></span> light</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/heavy.gif" style="vertical-align: middle;"/></span> heavy</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/ram.gif" style="vertical-align: middle;"/></span> ram</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/kata.gif" style="vertical-align: middle;"/></span> kata</th>
							<th><span class="inline" style="line-height: 24px;"><img src="<?=$config['localhost']?>media/img/snob.gif" style="vertical-align: middle;"/></span> snob</th>
						</tr>
					  </thead>
					  <tbody>
						<?
							$total_troops = Array();
							$i = 0;
						?>
						<? foreach ($own_villages as $village_id => $village) : ?>
							  <tr>
								  <td><?=$i+1?></td>
								  <td>
									<a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$village['id'].$config['main']?>" target="_blank">
										<?= $village['id'] ?>
									</a>
								  </td>
								  <td colspan="4">
									<?= $village['name'] ?> <em>(<?=$village['x']?> | <?=$village['y']?>)</em>
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
							  </tr>
							  <? $i++; ?>
						<? endforeach; ?>
						<? if ($i == 0) : ?>
						<tr>
							<td colspan="14">You don't have any villages.</td>
						</tr>
						<? endif; ?>
						<? else : ?>
						<tr>
							<td colspan="14">You don't have any villages.</td>
						</tr>
						<? endif; ?>
						<? if (isset($total_troops) && count($total_troops) > 0) : ?>
						<tr class="footer">
							<td colspan="7" rowspan="2">
								<a name="troops"></a>
								<b>Total troops</b>
							</td>
							<? foreach($total_troops as $name => $troop) : ?>
							<td>
								<b><?=$name?></b>
							</td>
							<? endforeach ?>
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
		</div>
    </body>
</html>
<? endif; ?>
