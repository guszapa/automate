<?php
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
/**
 * Controller
 */
if (! empty($_POST)) {
    $_config = array_merge($config, $_POST);

    if (isset($_config['autodefense']) && isset($_config['autodefense']['troops'])) {
	    // Remove zero troops
	    foreach ($_config['autodefense']['troops'] as $key => $value) {
	    	if ($value <= 0) unset($_config['autodefense']['troops'][$key]);
	    }
	    $_config['autodefense']['active'] = $_config['autodefense']['active'] == "true";
    }
    
    if ($f = fopen($paths['config'], 'w')) {
        fwrite($f, json_encode($_config));
        fclose($f);
        $msg = "The config has been saved. <a href='{$config['localhost']}config.php'>refresh page</a>";
    } else {
        Automate::factory()->log('E', "You don't have permission to write {$paths['config']} file");
    }
}
/**
 * View
 */
?>
<!DOCTYPE html>
<html lang="en">
    <head>
		<meta name="viewport" content="width=device-width, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
        <title>Config settings</title>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-ui-1.10.3.custom.min.js"></script>
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery-ui-1.10.3.custom.min.css">
    </head>
    <body>
        <? if ( !empty($msg)) : ?>
            <div class="msg <?= ($error) ? 'error' : 'success'; ?>">
                <?= $msg; ?>
            </div>
        <? endif; ?>
        <!-- END -->
        <div class="bodyContainer">
            <div class="new">
               </span> <a href="<?=$config['localhost']?>config.php">Refresh</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>">« Go Back</a>
            </div>
            <br/>
			<form action="<?=$config['localhost']?>config.php" method="post">
				<ul>
				<? foreach($config as $key => $data) : ?>
					<? switch($key) : case 'headers': ?><!-- don't show headers --><? break; ?>
					<? case 'protocol': ?><!-- don't show protocol --><? break; ?>
					<? case 'gzip': ?><!-- don't show gzip --><? break; ?>
					<? case 'cookie_file': ?><!-- don't show gzip --><? break; ?>
					<? case 'barracks': ?><!-- don't show barracks --><? break; ?>
					<? case 'troops_speed': ?><!-- don't show troops_speed --><? break; ?>
					<? case 'espy_speed': ?><!-- don't show spy_speed --><? break; ?>
					<? case 'premium': ?><!-- don't show premium --><? break; ?>
					<? case 'attack': ?><!-- don't show attack --><? break; ?>
					<? case 'espy': ?><!-- don't show espy --><? break; ?>
					<? case 'info_player': ?><!-- don't show info_player --><? break; ?>
					<? case 'info_village': ?><!-- don't show info_village --><? break; ?>
					<? case 'overview': ?><!-- don't show overview --><? break; ?>
					<? case 'main': ?><!-- don't show main --><? break; ?>
					<? case 'main_build': ?><!-- don't show main_build --><? break; ?>
					<? case 'barracks_send': ?><!-- don't show barracks_send --><? break; ?>
					<? case 'barracks_troops': ?><!-- don't show barracks_troops --><? break; ?>
					<? case 'barracks_recruit': ?><!-- don't show barracks_recruit --><? break; ?>
					<? case 'market': ?><!-- don't show market --><? break; ?>
					<? case 'market_send': ?><!-- don't show market_send --><? break; ?>
					<? case 'attack_send': ?><!-- don't show attack_send --><? break; ?>
					<? case 'flag': ?><!-- don't show flag --><? break; ?>
					<? case 'flag_offset': ?><!-- don't show flag_offset --><? break; ?>
					<? case 'flag_time': ?><!-- don't show flag_time --><? break; ?>
					<? case 'max_building_queue': ?><!-- don't show max_building_queue --><? break; ?>
					<? case 'autoleveler': ?><!-- don't show autoleveler --><? break; ?>
					<? case 'email': ?><!-- don't show email --><? break; ?>
					<? case 'autodefense': ?>
					<li>
						<fieldset>
							<div style="margin-left: 16px">
								<div>
									<label><b><?=$key?>:</b></label>
									<i style="font-size:0.8em;">True</i><input type="radio" name="<?=$key?>[active]" value="true" <?= $data['active'] ? 'checked' : null ?>/>
									<i style="font-size:0.8em;">False</i><input type="radio" name="<?=$key?>[active]" value="false" <?= !$data['active'] ? 'checked' : null ?>/>
								</div>
								<div>
									<label><b>flag_file:</b></label>
									<input type="text" name="<?=$key?>[flag_file]" value="<?=$data['flag_file']?>" class="span20"/>
								</div>
								<div>
									<label><b>max_range:</b></label>
									<input type="text" name="<?=$key?>[max_range]" value="<?=$data['max_range']?>" class="span20"/>
								</div>
								<div>
									<label><b>max_colonies:</b></label>
									<input type="text" name="<?=$key?>[max_colonies]" value="<?=$data['max_colonies']?>" class="span20"/>
								</div>
								<div>
									<label><b>wagons:</b></label>
									<input type="text" name="<?=$key?>[wagons]" value="<?=$data['wagons']?>" class="span20"/>
								</div>
								<div>
									<label><b>miliseconds:</b></label>
									<input type="text" name="<?=$key?>[miliseconds]" value="<?=$data['miliseconds']?>" class="span20"/>
								</div>
								<div>
									<label style="display: block; width: 100%"><b>troops:</b></label>
								</div>
								<div>
									<? foreach($config['troops_speed'] as $unit => $value) : ?>
										<span style="display: inline-block">
											<img src="<?=$config['localhost']?>media/img/<?=$unit?>.gif" alt="<?=$unit?>" title="<?=$unit?>"/>
											<input type="text" name="<?=$key?>[troops][<?=$unit?>]" value="<?=isset($data['troops'][$unit]) ? $data['troops'][$unit] : 0?>" class="span2"/>
										</span>
									<? endforeach; ?>
								</div>
							</div>
						</fieldset>
					</li>
					<? break; ?>
					<? default: ?>
					<li>
						<label class="span10 inlineblock"><b><?=$key?>:</b></label>
						<input type="text" name="<?=$key?>" value="<?=$data?>" class="span20"/>
					</li>
					<? break; ?>
					<? endswitch; ?>
				<? endforeach; ?>
					<li>
						<label class="span10 inlineblock">&nbsp;</label>
						<input type="submit" value="save"/>
					</li>
				</ul>
			</form>
        </div>
    </body>
</html>
