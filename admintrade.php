<?php
/**
 * Farm administration
 *
 * @author katan
 */
include_once 'automate.class.php';
$villages = Automate::factory()->getVillages();
$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
$trade = json_decode(Automate::factory()->getTrades(), true);
$first_village_id = 0;
$msg = '';
$error = false;
/**
 * Controller
 */
$action = null;
if (! empty($_GET)) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $pos = isset($_GET['pos']) ? $_GET['pos'] : null;
}
switch ($action) {
    case 'add-trade': // OK
		if (!empty($_POST['village_id'])) {
			if ($f = fopen($paths['trade'], 'w')) {
			   if (!is_array($trade)) $trade = array();
			   $_POST[$_POST['village_id']]['id'] = time();
			   array_push($trade, $_POST[$_POST['village_id']]);
			   fwrite($f, json_encode($trade));
			   fclose($f);
			   $msg = "The trade have been saved. <a href='{$config['localhost']}admintrade.php'>refresh page</a>";
		    } else {
			   Automate::factory()->log('E', "You don't have permission to write {$paths['trade']} file");
		    }
		}
        break;
    case 'delete':
		if ($f = fopen($paths['trade'], 'w')) {
		   unset($trade[$pos]);
		   sort($trade);
		   fwrite($f, json_encode($trade));
		   fclose($f);
		   $msg = "The trade have been deleted. <a href='{$config['localhost']}admintrade.php'>refresh page</a>";
		} else {
		   Automate::factory()->log('E', "You don't have permission to write {$paths['trade']} file");
		}
    case 'edit':
		if (!empty($_POST)) {
			foreach($_POST[$_POST['from']] as $key => $value) {
				$trade[$_POST['pos']][$key] = $value;
			}
			if ($f = fopen($paths['trade'], 'w')) {
				fwrite($f, json_encode($trade));
				fclose($f);
				$msg = "The trade have been saved. <a href='{$config['localhost']}admintrade.php'>refresh page</a>";
			} else {
			   Automate::factory()->log('E', "You don't have permission to write {$paths['trade']} file");
			}
		}
        break;
}
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
        <title><?=$config['player']?> - Trade administration</title>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-1.10.1.min.js"></script>
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
        <style type="text/css">
		  a { cursor: pointer; }
          #new_trade {
            width: 500px;
            top: 75px;
			display: none;
          }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                // OPEN
                jQuery('#a_new_trade').click(function(){
                    jQuery('#modalbox').css('display', 'block');
                    this.padding = parseInt(jQuery('#new_trade').css('padding-left')) + parseInt(jQuery('#new_trade').css('padding-right'));
                    this.left = Math.round((jQuery(window).width() - (jQuery('#new_trade').width()+this.padding))/2);
                    jQuery('#new_trade').css({'display': 'block', 'left': this.left + 'px'});
                });
                // CLOSE
                jQuery('.close').click(function(){
                    jQuery('#modalbox').css('display', 'none'); jQuery('#new_trade').css('display', 'none');
                });
                // CLOSE ESC
                jQuery(window).keyup(function(e){
                    if (e.keyCode == 27) { // press ESC
                        jQuery('#modalbox').css('display', 'none'); jQuery('#new_trade').css('display', 'none');
                    }
                });
				// Confirm before delete
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
              <h1><?=$config['player']?> - Trade administration <em style="font-size:0.6em">(<?=date('d/m/Y H:i:s')?> | <?=date_default_timezone_get();?>)</em></h1>
            </div>
            <div class="content">
                <div class="new">
                    <a href="<?=$config['localhost']?>admintrade.php">Refresh</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>">« Go Back</a>
                </div>
				<form action="<?=$config['localhost']?>admintrade.php?action=add-trade" method="post">
					<table class="farms">
						<thead>
							<tr>
								<th rowspan="2">Village ID</th>
								<th rowspan="2">Village Name</th>
								<th colspan="3">Materials</th>
								<th rowspan="2" colspan="2">Method</th>
								<th rowspan="2">Actions</th>
							</tr>
							<tr class="troops">
								<th><img src="<?=$config['localhost']?>media/img/stone.png"/> stone</th>
								<th><img src="<?=$config['localhost']?>media/img/wood.png"/> wood</th>
								<th><img src="<?=$config['localhost']?>media/img/iron.png"/> iron</th>
							</tr>
						</thead>
						<tbody>
						<? if (count($villages['own']) > 0) : ?>
							<? $i = 0; ?>
							<? foreach($villages['own'] as $village_id => $village) : ?>
								<? if ($i === 0) $first_village_id = $village_id; ?>
							<tr>
								<td>
									<a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$first_village_id.$config['info_village'].$village_id?>" target="_blank">
									<?=$village_id?>
									<input type="hidden" class="span3" name="<?=$village_id?>[from]" value="<?=$village_id?>"/>
								</td>
								<td>
									<?=$village['name']?> 
									<span syle="font-size:0.9em;">[<?=$village['x']?>|<?=$village['y']?>]</span> 
									<?=isset($village['points']) ? '<em>('.number_format($village['points'],0,'','.').')</em>' : null ?>
								</td>
								<td>
									<div class="block">
										<?=isset($village['materials']['stone']) ? number_format($village['materials']['stone'],0,'','.') : '---' ?>
									</div>
									<div class="block">
										<input type="text" class="span3" name="<?=$village_id?>[stone]" placeholder="stone"/>
									</div>
								</td>
								<td>
									<div class="block">
										<?=isset($village['materials']['wood']) ? number_format($village['materials']['wood'],0,'','.') : '---' ?>
									</div>
									<div class="block">
										<input type="text" class="span3" name="<?=$village_id?>[wood]" placeholder="wood"/>
									</div>
								</td>
								<td>
									<div class="block">
										<?=isset($village['materials']['iron']) ? number_format($village['materials']['iron'],0,'','.') : '---' ?>
									</div>
									<div class="block">
										<input type="text" class="span3" name="<?=$village_id?>[iron]" placeholder="iron"/>
									</div>
								</td>
								<td>
									<div class="block">
										<div class="inline">
											<input type="radio" name="<?=$village_id?>[method]" value="interval"/>
										</div>
										<div class="inline">
											<input type="text" class="span6" name="<?=$village_id?>[interval]" placeholder="Interval in minutes"/>
										</div>
									</div>
								</td>
								<td>
									<div class="left alignleft" style="margin:14px 6px 0 0;">
										<div class="block">
											<div class="inline">
												<input type="radio" name="<?=$village_id?>[method]" value="lessthan"/>
											</div>
											<div class="inline">
												<input type="text" class="span4" name="<?=$village_id?>[lessthan]" placeholder="less than"/>
											</div>
										</div>
									</div>
									<div class="left alignleft">
										<div class="block">
											<input type="checkbox" name="<?=$village_id?>[materials][stone]" value="stone"/> <img src="<?=$config['localhost']?>media/img/stone.png"/> stone
										</div>
										<div class="block">
											<input type="checkbox" name="<?=$village_id?>[materials][wood]" value="wood"/> <img src="<?=$config['localhost']?>media/img/wood.png"/> wood
										</div>
										<div class="block">
											<input type="checkbox" name="<?=$village_id?>[materials][iron]" value="iron"/> <img src="<?=$config['localhost']?>media/img/iron.png"/> iron
										</div>
									</div>
									<div class="both"></div>
								</td>
								<td>
									<div class="block">
										<!-- Own villages -->
										<div class="block">
											<span>Own villages:</span>
											<select name="<?=$village_id?>[to]">
											<? foreach($villages['own'] as $_village_id => $village) : ?>
												<? if ($village_id !== $_village_id) : ?>
													<option value="<?=$_village_id?>"><?=$village['name']?></option>
												<? endif; ?>
											<? endforeach; ?>
											</select>
											<hr/>
										</div>
										<!-- Allied villages -->
										<? if (isset($villages['ally']) && count($villages['ally']) > 0) : ?>
										<div class="block">
											<span>Allied villages:</span>
											<select name="<?=$village_id?>[to]">
											<? foreach($villages['ally'] as $_village_id => $village) : ?>
												<option value="<?=$_village_id?>"><?=$village['name']?></option>
											<? endforeach; ?>
											</select>
											<hr/>
										</div>
										<? endif; ?>
									</div>
									<div class="block">
										<button type="submit" name="village_id" value="<?=$village_id?>">Add trade</button>
									</div>
								</td>
							</tr>
							<? $i++ ?>
							<? endforeach; ?>
						<? else : ?>
							<td colspan="5">You don't have any village, create one firts.</td>
						<? endif; ?>
						</tbody>
					</table>
				</form>
				<br/><a href="" name="trade"></a>
				<?
                $editable = (isset($action) && isset($id)) && $action == 'edit';
				$pos = 0;
                ?>
                <form action="<?=$config['localhost']?>admintrade.php?action=edit" name="trade" method="post">
					<table class="farms">
						<thead>
							<tr>
								<th rowspan="2">From</th>
								<th rowspan="2">To</th>
								<th colspan="3">Materials</th>
								<th rowspan="2">Method</th>
								<th rowspan="2">Options</th>
								<th rowspan="2">Actions</th>
							</tr>
							<tr class="troops">
								<th><img src="<?=$config['localhost']?>media/img/stone.png"/> stone</th>
								<th><img src="<?=$config['localhost']?>media/img/wood.png"/> wood</th>
								<th><img src="<?=$config['localhost']?>media/img/iron.png"/> iron</th>
							</tr>
						</thead>
						<tbody>
							<? if (!empty($trade)) : ?>
								<? foreach ($trade as $key => $value) : ?>
								<tr>
									<td><?=$villages['own'][$value['from']]['name']?></td>
									<td><?=$villages['own'][$value['to']]['name']?></td>
									<td>
										<? if ($editable && $key == $id) : ?>
											<input type="text" class="span3" name="<?=$value['from']?>[stone]" value="<?=$value['stone']?>" placeholder="stone"/>
										<? else : ?>
											<?=number_format($value['stone'],0,'','.')?>
										<? endif; ?>
									</td>
									<td>
										<? if ($editable && $key == $id) : ?>
											<input type="text" class="span3" name="<?=$value['from']?>[wood]" value="<?=$value['wood']?>" placeholder="wood"/>
										<? else : ?>
											<?=number_format($value['wood'],0,'','.')?>
										<? endif; ?>
									</td>
									<td>
										<? if ($editable && $key == $id) : ?>
											<input type="text" class="span3" name="<?=$value['from']?>[iron]" value="<?=$value['iron']?>" placeholder="iron"/>
										<? else : ?>
											<?=number_format($value['iron'],0,'','.')?>
										<? endif; ?>
									</td>
									<td><?=$value['method']?></td>
									<td>
										<? if (!empty($value['interval'])) : ?>
											<? if ($editable && $key == $id) : ?>
												<input type="text" class="span6" name="<?=$value['from']?>[interval]" value="<?=$value['interval']?>" placeholder="Interval in minutes"/>
											<? else : ?>
												<?=number_format($value['interval'],0,'','.')?>
											<? endif; ?>
										<? else : ?>
											<div class="block">
												<b>Less than:</b> 
												<? if ($editable && $key == $id) : ?>
													<input type="text" class="span4" name="<?=$village_id?>[lessthan]" value="<?=$value['lessthan']?>" placeholder="less than"/>
												<? else : ?>
													<?=number_format($value['lessthan'],0,'','.')?>
												<? endif; ?>
											</div>
											<div class="block">
												<b>For:</b> 
												<? foreach($value["materials"] as $v) : ?>
												<span><img src="<?=$config['localhost']?>media/img/<?=$v?>.png"/>&nbsp;</span>
												<? endforeach; ?>
											</div>
										<? endif; ?>
									</td>
									<td>
                                      <? if ($editable && $key == $id) : ?>
                                          <input type="submit" value="Save"/><br/><a href="<?=$config['localhost']?>admintrade.php" name="pos<?=$pos?>">Cancel</a>
                                          <input type="hidden" name="pos" value="<?=$pos?>"/>
										  <input type="hidden" name="from" value="<?=$value['from']?>"/>
                                      <? else : ?>
                                          <a href="<?=$config['localhost']?>admintrade.php?action=edit&id=<?=$pos?>#pos<?=$pos?>">Edit</a> | <a href="<?=$config['localhost']?>admintrade.php?action=delete&id=<?=$value['from']?>&pos=<?=$pos?>#pos<?=$pos?>" class="delete-row">Delete</a>
                                      <? endif; ?>
                                  </td>
								</tr>
								<? $pos++ ?>
								<? endforeach; ?>
							<? endif; ?>
						</tbody>
					</table>
                </form>
            </div>
            <div style="margin-top: 16px;"></div>
        </div>
    </body>
</html>