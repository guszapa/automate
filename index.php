<?php
/**
 * Farm administration
 * @author katan
 */
include_once(dirname(__FILE__).'/automate.class.php'); // parent
$config = Automate::factory()->getConfig();
$logday = Automate::factory()->getLog();
$msg = '';
$error = false;
/**
 * Controller
 */
$action = null;
if (! empty($_GET)) {
    $action = $_GET['action'];
}
switch ($action) {
    case 'log': // OK
       if ( !empty($_GET['day'])) {
          $logday = Automate::factory()->getLog($_GET['day']);
       }
       break;
    case 'calculator': // OK
       if ( !empty($_POST)) {
          $_times = array();
          $_config = Automate::factory()->getConfig();
          foreach ($_config['troops_speed'] as $speed) {
             $_times[] = Automate::factory()->getDistance($_POST['from'], $_POST['to'], $speed);
          }
       }
       break;
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta name="viewport" content="width=device-width, user-scalable=no">
		<meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
		<title><?=$config['player']?> - Dashboard</title>
		<script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-1.10.1.min.js"></script>
		<script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-purl-2.3.1.js"></script>
		<link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
		<link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery-ui-1.10.3.custom.min.css">
		<style type="text/css">
          #calculator, #log {
            width: 780px;
            top: 30px;
            display: none;
          }
          #calculator { width: 560px; }
          /* MENU */
          body { padding: 20px; }
			 ul { list-style: none; padding: 0; margin:0; display: block; }
			 ul.dashboard li { padding: 0 10px; text-align: center; }
			 ul.dashboard li a { padding: 4px 8px; border-radius: 4px; }
			 ul.dashboard li a:hover { background-color: #e5e5e5; }
			 ul.dashboard li a img { width: 128px; height: 128px; padding-bottom: 4px; }
        </style>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				var action = jQuery.url().param('action');
            if (action == 'calculator') {
                jQuery('#modalbox').css('display', 'block');
                this.padding = parseInt(jQuery('#calculator').css('padding-left')) + parseInt(jQuery('#calculator').css('padding-right'));
                this.left = Math.round((jQuery(window).width() - (jQuery('#calculator').width()+this.padding))/2);
                jQuery('#calculator').css({'display': 'block', 'left': this.left + 'px'});
            }
            jQuery('#a_calculator').click(function(){
                jQuery('#modalbox').css('display', 'block');
                this.padding = parseInt(jQuery('#calculator').css('padding-left')) + parseInt(jQuery('#calculator').css('padding-right'));
                this.left = Math.round((jQuery(window).width() - (jQuery('#calculator').width()+this.padding))/2);
                jQuery('#calculator').css({'display': 'block', 'left': this.left + 'px'});
            });
            if (action == 'log') {
                jQuery('#modalbox').css('display', 'block');
                this.padding = parseInt(jQuery('#log').css('padding-left')) + parseInt(jQuery('#calculator').css('padding-right'));
                this.left = Math.round((jQuery(window).width() - (jQuery('#log').width()+this.padding))/2);
                jQuery('#log').css({'display': 'block', 'left': this.left + 'px'});
            }
            jQuery('#a_log').click(function(){
                jQuery('#modalbox').css('display', 'block');
                this.padding = parseInt(jQuery('#log').css('padding-left')) + parseInt(jQuery('#log').css('padding-right'));
                this.left = Math.round((jQuery(window).width() - (jQuery('#log').width()+this.padding))/2);
                jQuery('#log').css({'display': 'block', 'left': this.left + 'px'});
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
        });
		</script>
	</head>
	<style>

	</style>
	<body>
		<? if ( !empty($msg)) : ?>
            <div class="msg <?= ($error) ? 'error' : 'success'; ?>">
                <?= $msg; ?>
            </div>
        <? endif; ?>
        <div id="modalbox"></div>
        <!-- LOG -->
        <div id="log" class="modalbox">
           <div class="close">close</div>
            <br/>
            <div style="display:table; width: 100%;">
               <div style="display:table-cell;">
				  <a href="?action=log&day=<?=date('Ymd', strtotime('-7 day'))?>"><?=date('d-m-Y', strtotime('-7 day'))?></a>
				  <a href="?action=log&day=<?=date('Ymd', strtotime('-6 day'))?>"><?=date('d-m-Y', strtotime('-6 day'))?></a>
			      <a href="?action=log&day=<?=date('Ymd', strtotime('-5 day'))?>"><?=date('d-m-Y', strtotime('-5 day'))?></a>
			      <a href="?action=log&day=<?=date('Ymd', strtotime('-4 day'))?>"><?=date('d-m-Y', strtotime('-4 day'))?></a>
			      <a href="?action=log&day=<?=date('Ymd', strtotime('-3 day'))?>"><?=date('d-m-Y', strtotime('-3 day'))?></a>
                  <a href="?action=log&day=<?=date('Ymd', strtotime('-2 day'))?>"><?=date('d-m-Y', strtotime('-2 day'))?></a>
                  <a href="?action=log&day=<?=date('Ymd', strtotime('-1 day'))?>"><?=date('d-m-Y', strtotime('-1 day'))?></a>
               </div>
               <div style="display:table-cell; text-align: right;">
				  <a href="?action=log&day=<?=date('Ymd')?>"><?=date('d-m-Y')?></a>
               </div>
               <div style="clear:both;"></div>
            </div>
            <br/>
            <div style="font-size:0.85em; height: 400px; overflow: auto;">
                <? if($logday) : ?>
                   <?= nl2br($logday); ?>
                <? else : ?>
                   <p>There is no registers for this day.</p>
                <? endif; ?>
            </div>
        </div>
        <!-- Time Calculator -->
        <div id="calculator" class="modalbox">
           <div class="close">close</div>
           <div class="container">
              <form action="?action=calculator" method="post">
                    <ul>
                        <li>
                            <label><b>From:</b></label>
                            <span>
                                x: <input type="text" name="from[x]" class="span2" value="<?= (isset($_POST['from']['x'])) ? $_POST['from']['x'] : null ?>" maxlength="3"/> y: <input type="text" name="from[y]" class="span2" value="<?= (isset($_POST['from']['y'])) ? $_POST['from']['y'] : null ?>" maxlength="3"/>
                            </span>
                        </li>
                        <li>
                            <label><b>To:</b></label>
                            <span>
                                x: <input type="text" name="to[x]" class="span2" value="<?= (isset($_POST['to']['x'])) ? $_POST['to']['x'] : null ?>" maxlength="3"/> y: <input type="text" name="to[y]" class="span2" value="<?= (isset($_POST['to']['y'])) ? $_POST['to']['y'] : null ?>" maxlength="3"/>
                            </span>
                        </li>
                        <li>
                           <input type="submit" value="Submit"/>
                        </li>
                        <li>
                           <table class="farms">
                                <thead>
                                    <tr class="header">
                                        <th>Unity</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <? if(isset($_config['troops_speed'])) : ?>
                                          <? for($i=0; $i<count($_config['troops_speed']); $i++) : ?>
                                          <tr>
                                                <td class="span3">
                                                	<? $temp = array_slice($_config['troops_speed'], $i, 1, true); ?>
                                                	<img src="<?=$config['localhost']?>media/img/<?=key($temp)?>.gif" alt="<?=$name?>" title="<?=$name?>" style="vertical-align: middle;"/> <?=key($temp)?>
                                                </td>
                                                <td class="span5">
													<span data-time="<?=$_times[$i]?>"><?=Automate::factory()->getformatTime($_times[$i]);?></span>
												</td>
                                          </tr>
                                          <? endfor; ?>
                                    <? endif; ?>
                                </tbody>
                            </table>
                        </li>
                    </ul>
              </form>
           </div>
        </div>
      <div>
      <!-- MENU -->
	  <div>
		<h1><?=$config['player']?> <em style="font-size:0.6em">(<?=date('d/m/Y H:i:s')?> | <?=date_default_timezone_get();?>)</em></h1>
	  </div>
			<ul class="dashboard block">
				<!--
				<li class="left">
					<a href="<?=$config['localhost']?>config.php" class="left">
						<img src="<?=$config['localhost']?>media/img/settings.png"/><br/>Settings
					</a>
				</li>
				-->
				<li class="left">
					<a href="<?=$config['localhost']?>adminvillages.php?mode=attack" class="left">
						<img src="<?=$config['localhost']?>media/img/menu_village.png"/><br/>
            Villages administrator
					</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>adminautoleveler.php" class="left">
						<img src="<?=$config['localhost']?>media/img/menu_autoleveler.png"/><br/>
						Building's autoleveler
					</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>admintroops.php" class="left">
						<img src="<?=$config['localhost']?>media/img/menu_barracks.png"/><br/>
						Troops administrator
					</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>admintrade.php" class="left">
						<img src="<?=$config['localhost']?>media/img/menu_market.png"/><br/>
						Trade administrator
					</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>adminattacks.php" class="left">
						<img src="<?=$config['localhost']?>media/img/menu_attacks.png"/><br/>
						Attack scheduler
					</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>adminfarms.php" class="left">
						<img src="<?=$config['localhost']?>media/img/menu_farm.png"/><br/>
            Farms administrator
					</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>admintracking.php" class="left">
						<img src="<?=$config['localhost']?>media/img/tracking.png"/><br/>
						Tracking administrator
					</a>
				</li>
        <li class="left">
          <a href="<?=$config['localhost']?>game.php" class="left">
            <img src="<?=$config['localhost']?>media/img/messages.png"/><br/>
            Game access
          </a>
        </li>
				<li class="left">
					<a id="a_calculator" class="left">
						<img src="<?=$config['localhost']?>media/img/menu_timecalculator.png"/><br/>
            Speed/Time Calculator
					</a>
				</li>
				<li class="left">
					<a id="a_log" class="left">
						<img src="<?=$config['localhost']?>media/img/menu_log.png"/><br/>
            See Log
					</a>
				</li>
				<li class="both"></li>
			</ul>
			<hr/>
			<ul class="dashboard block">
				<li class="left">
					<a href="<?=$config['localhost']?>scripts/farms.php?start=now" class="left">Execute farms script</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>scripts/flag.php?start=now" class="left">Execute flag script</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>scripts/autoleveler.php?start=now" class="left">Execute Autoleveler script</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>scripts/scheduler.php?start=now" class="left">Execute scheduler script</a>
				</li>
        <li class="left">
          <a href="<?=$config['localhost']?>scripts/recruit.php?start=now" class="left">Execute troops recruit script</a>
        </li>
				<li class="left">
					<a href="<?=$config['localhost']?>scripts/tracking.php?start=now" class="left">Execute tracking script</a>
				</li>
				<li class="left">
					<a href="<?=$config['localhost']?>" class="left">Refresh page</a>
				</li>
				<li class="both"></li>
			</ul>
		</div>
	</body>
</html>
