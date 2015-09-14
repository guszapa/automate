<?php
/**
 * Autoleveler administration
 *
 * @author katan
 */
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
/**
 * Controller
 */
if (! empty($_POST)) {
    echo "<pre>";
	print_r($_POST);
	echo "</pre>";
	
	/*	
   if ($f = fopen($paths['config'], 'w')) {
	   fwrite($f, json_encode($scheduler_json));
	   fclose($f);
	   $msg = "The attack have been saved. <a href='{$config['localhost']}config.php'>refresh page</a>";
   } else {
	   Automate::factory()->log('E', "You don't have permission to write {$paths['config']} file");
   }*/
}
/**
 * View
 */
?>
<? if (!$is_ajax) : ?>
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
					<? switch($key) : case 'headers': ?>
					<li>
						<label><b><?=$key?>:</b></label>
						<? foreach($data as $v) : ?>
						<span class="left">
							<input type="text" name="<?=$key?>[]" value="<?=$v?>"/>
						</span>
						<? endforeach; ?>
						<span class="both"></span>
					</li>
					<? break; ?>
					<? case 'troops_speed': ?>
					<li>
						<label><b><?=$key?>:</b></label>
						<? foreach($data as $k => $v) : ?>
						<span class="left">
							<i style="font-size:0.8em;"><?=$k?></i>
							<input type="text" class="span2" name="<?=$key?>[<?=$k?>]" value="<?=$v?>"/>
						</span>
						<? endforeach; ?>
						<span class="both"></span>
					</li>
					<? break; ?>
					<? case 'gzip': ?>
					<li>
						<label><b><?=$key?>:</b></label>
						<i style="font-size:0.8em;">True</i><input type="radio" name="<?=$key?>" value="1" <?= $data == 1 ? 'checked' : null ?>/>
						<i style="font-size:0.8em;">False</i><input type="radio" name="<?=$key?>" value="0" <?= $data == 0 ? 'checked' : null ?>/>
					</li>
					<? break; ?>
					<? case 'autodefense': ?>
					<li>
						<label><b><?=$key?>:</b></label>
						<i style="font-size:0.8em;">True</i><input type="radio" name="<?=$key?>" value="1" <?= $data == 1 ? 'checked' : null ?>/>
						<i style="font-size:0.8em;">False</i><input type="radio" name="<?=$key?>" value="0" <?= $data == 0 ? 'checked' : null ?>/>
					</li>
					<? break; ?>
					<? case 'premium': ?>
					<li>
						<label><b><?=$key?>:</b></label>
						<i style="font-size:0.8em;">True</i><input type="radio" name="<?=$key?>" value="1" <?= $data == 1 ? 'checked' : null ?>/>
						<i style="font-size:0.8em;">False</i><input type="radio" name="<?=$key?>" value="0" <?= $data == 0 ? 'checked' : null ?>/>
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
<? endif; ?>