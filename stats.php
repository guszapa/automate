<?php
/**
 * Autoleveler administration
 *
 * @author katan
 */
include_once 'automate.class.php';
/**
 * Controller
 */
$action = null;
$_week = date('W');
$_year = date('Y');
if (! empty($_GET)) {
    $id = isset($_GET['player']) ? (int)$_GET['player'] : null;
    $_week = isset($_GET['week']) ? (int)$_GET['week'] : $_week;
    $_year = isset($_GET['year']) ? (int)$_GET['year'] : $_year;
}
$tracking = Automate::factory()->getTracking($id);
$all_tracking = Automate::factory()->getTracking();
$history = Automate::factory()->getTrackingHistory($id, $_week, $_year);
$paths = Automate::factory()->getPaths();
$config = Automate::factory()->getConfig();
$msg = '';
$error = false;
$is_ajax = false;
$stats = false;

/** Paginator **/
$pages = 5;
$weeks = 52;
$paginator = array();
for ($i = 0; $i < $pages*2; $i++) {
	$__year = date('Y');
	if ($i < $pages) {
		$__week = $_week-($i+1);
		if ($__week < 1) {
			$__week = $weeks-$__week;
			$__year = $_year - 1;
		}
		$__week = $__week < 9 ? "0{$__week}" : $__week;
		$filename = ROOT.$paths['tracking_data']."/{$id}/{$__week}_{$__year}.json";
		if (is_file($filename)) {
			$paginator[] = array($__week,$__year);
		}
	} else if ($i == $pages) {
		$paginator[] = "current";
	} else {
		$__week = $_week+($i-$pages);
		if ($__week > $weeks) {
			$__week = $__week-$weeks;
			$__year = $_year + 1;
		}
		$__week = $__week < 9 ? "0{$__week}" : $__week;
		$filename = ROOT.$paths['tracking_data']."/{$id}/{$__week}_{$__year}.json";
		if (is_file($filename)) {
			$paginator[] = array($__week,$__year);
		}
	}
}

/** PROCCESS STATS **/
// alliance charts

if (is_array($all_tracking) && count($all_tracking) > 0) {
	
	// total points
	$pie_points = array();
	$sum_points = 0;
	foreach($all_tracking as $user_id => $stats) {
		$_history = Automate::factory()->getTrackingHistory($user_id, $_week, $_year);
		if ($_history) {
			foreach ($_history as $_track) {
				$_temp_points = 0;
				foreach($_track as $unixtime => $values) {
					$_temp_points += (int)$values['total_points'];
				}
			}
			$sum_points += $_temp_points;
		}
	}
	if ($sum_points) {
		array_push($pie_points, array('Others', round((($sum_points-$tracking['total_points'])/$sum_points)*100, 1)));
		array_push($pie_points, array('name' => $tracking['name'], 'y' => round(($tracking['total_points']/$sum_points)*100, 1), 'sliced' => true, 'selected' => true));
	}
	$pie_points = json_encode($pie_points);
	
	// troop points
	$pie_troops = $pie_ally_troops = $user_data = array();
	$sum_troops = $own_troops = 0;
	foreach($all_tracking as $user_id => $stats) {
		$_history = Automate::factory()->getTrackingHistory($user_id, $_week, $_year);
		if ($_history) {
			foreach ($_history as $_track) {
				$_temp_troops = 0;
				foreach($_track as $unixtime => $values) {
					$village_points = 0;
					$village_conquer = $values['total_villages'] > 1 ? 2500*($values['total_villages']-1) : 0;
					foreach($values['villages'] as $village) :
						$village_points += (int)$village['points'];
					endforeach;
					if ($id == $user_id) {
						$own_troops = $values['total_points'] - $village_points - $village_conquer;
					}
					$_temp_troops = $values['total_points'] - $village_points - $village_conquer;
				}
			}
			array_push($user_data, array('id' => $user_id, 'name' => $stats['name'], 'troops' =>  $_temp_troops));
			$sum_troops += $_temp_troops;
		}
	}
	// details troops
	foreach($user_data as $user) {
		if ($id == $user['id']) {
			array_push($pie_ally_troops, array('name' => $user['name'],'y' => round((($user['troops'])/$sum_troops)*100, 1), 'sliced' => true, 'selected' => true));
		} else {
			array_push($pie_ally_troops, array('name' => $user['name'],'y' => round((($user['troops'])/$sum_troops)*100, 1)));
		}
	}
	array_push($pie_troops, array('Others', round((($sum_troops-$own_troops)/$sum_troops)*100, 1)));
	array_push($pie_troops, array('name' => $tracking['name'], 'y' => round(($own_troops/$sum_troops)*100, 1), 'sliced' => true, 'selected' => true));
	$pie_troops = json_encode($pie_troops);
	$pie_ally_troops = json_encode($pie_ally_troops);
}
// Personal charts
if (is_array($history) && count($history) > 0) {
	$stats = true;
	
	// Total points
	$data = $t_min_points = array();
	$data['data'] = array();
	$i = $start = 0;
	foreach ($history as $track) :
		foreach($track as $unixtime => $values) :
			if(count($values) > 0) :
				if ($i == 0) $start = $unixtime*1000;
				$t_min_points[] = (int)$values['total_points'];
				array_push($data['data'], array($unixtime*1000, (int)$values['total_points']));
			endif;
			$i++;
		endforeach;
	endforeach;
	$t_min_points = min($t_min_points);
	$data['name'] = "Week $_week, $_year";
	$data['pointStart'] = $start;
	$_total_points = json_encode($data);
	
	// average points
	$data = $_min_average = array();
	$data['data'] = array();
	$i = $start = 0;
	foreach ($history as $track) :
		foreach($track as $unixtime => $values) :
			if(count($values) > 0) :
				if ($i == 0) $start = $unixtime*1000;
				$_min_average[] = (int)$values['average_points'];
				array_push($data['data'], array($unixtime*1000, (int)$values['average_points']));
			endif;
			$i++;
		endforeach;
	endforeach;
	$_min_average = min($_min_average);
	$data['name'] = "Week $_week, $_year";
	$data['pointStart'] = $start;
	$_average_points = json_encode($data);
    
    // villages
	$data = $_min_villages = array();
	$data['data'] = array();
	$i = $start = 0;
	foreach ($history as $track) :
		foreach($track as $unixtime => $values) :
			if(count($values) > 0) :
				if ($i == 0) $start = $unixtime*1000;
				$_min_villages[] = (int)$values['total_villages'];
				array_push($data['data'], array($unixtime*1000, (int)$values['total_villages']));
			endif;
			$i++;
		endforeach;
	endforeach;
	$_min_villages = min($_min_villages);
	$data['name'] = "Week $_week, $_year";
	$data['pointStart'] = $start;
	$_villages = json_encode($data);

	// troop points
	$data = $_min_troop = array();
	$data['data'] = array();
	$i = $start = $village_conquer = 0;
	foreach ($history as $track) :
		foreach($track as $unixtime => $values) :
			if(count($values) > 0) :
				$village_points = 0;
				$village_conquer = $values['total_villages'] > 1 ? 2500*($values['total_villages']-1) : 0;
				if (count($values['villages']) > 0) {
					foreach($values['villages'] as $village) :
						if (isset($village['points'])) $village_points += (int)$village['points'];
					endforeach;
				} else {
					$village_points = 0;
				}
				if ($i == 0) :
					$start = $unixtime*1000;
				endif;
				$troops_points = $values['total_points'] - $village_points - $village_conquer;
				$_min_troops[] = $troops_points;
				array_push($data['data'], array($unixtime*1000, $troops_points));
			endif;
			$i++;
		endforeach;
	endforeach;
	$_min_troops = min($_min_troops);
	$data['name'] = "Week $_week, $_year";
	$data['pointStart'] = $start;
	$_troops_points = json_encode($data);

	// Defeat opponents
	$data = array();
	$data['data'] = array();
	$i = $_min_defeat = $start = 0;
	foreach ($history as $track) :
		foreach($track as $unixtime => $values) :
			if(count($values) > 0) :
				if ($i == 0) : 
					$_min_defeat = (int)$values['defeat_opponents'];
					$start = $unixtime*1000;
				endif;
				array_push($data['data'], array($unixtime*1000, (int)$values['defeat_opponents']));
			endif;
			$i++;
		endforeach;
	endforeach;
	$data['name'] = "Week $_week, $_year";
	$data['pointStart'] = $start;
	$_defeat_points = json_encode($data);
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
        <title><?=$config['player']?> - <?=$tracking['name']?> stats</title>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/highcharts.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/highcharts-more.js"></script>
        <script type="text/javascript" src="<?=$config['localhost']?>media/js/modules/exporting.js"></script>
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
        <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/jquery-ui-1.10.3.custom.min.css">
        <style type="text/css">
          #player_tracking {
            width: 780px;
            top: 75px;
            display: none;
          }
          ul.current_stats {
          	width:360px;
          	margin: 0 auto;
          	list-style: none;
          	padding: 10px 4px;
          	background-color: #ccc;
          	border-radius: 4px;
          }
			 ul.current_stats li {
			 	padding: 2px 10px;
			 	font-size: 0.9em;
			 }
        </style>
        <script type="text/javascript">
			Number.prototype.format = function(decPlaces, thouSeparator, decSeparator) {
				var n = this,
				decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
				decSeparator = decSeparator == undefined ? "." : decSeparator,
				thouSeparator = thouSeparator == undefined ? "," : thouSeparator,
				sign = n < 0 ? "-" : "",
				i = parseInt(n = Math.abs(+n || 0).toFixed(decPlaces)) + "",
				j = (j = i.length) > 3 ? j % 3 : 0;
				return sign + (j ? i.substr(0, j) + thouSeparator : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator) + (decPlaces ? decSeparator + Math.abs(n - i).toFixed(decPlaces).slice(2) : "");
			};
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
                // ****** CHARTS *******
				function htooltip(el, data, text) {
					var html = '';
					switch(el) {
						case '#troop_points':
							var troops = data*4.5;
							html += '<br><b>'+ data.format(0,'.',',') +'</b> troop points' +'<br><b>'+ troops.format(0,'.',',') +'</b> '+ text;
							break;
						default:
							html += '<br><b>'+ data.format(0,'.',',') +'</b>';
							break;
					}
					return html;
				}
				function fhighcharts(el, _chart, _title, _subtitle, _data, _min, _tooltip) {
					switch(_chart) {
						case 'spline':
							$(el).highcharts({
								chart: {type: _chart},
								title: {text: _title},
								subtitle: {text: _subtitle},
								xAxis: {type: 'datetime', dateTimeLabelFormats: { second: '%Y-%m-%d<br/>%H:%M:%S',minute: '%Y-%m-%d<br/>%H:%M',hour: '%Y-%m-%d<br/>%H:%M',day: '%Y<br/>%m-%d',week: '%Y<br/>%m-%d',month: '%Y-%m',year: '%Y'}},
								series:[_data],
								yAxis: {title: {text: _title}, min: _min},
								tooltip: {formatter: function() { return '<b>'+ this.series.name +'</b><br/>'+ Highcharts.dateFormat('%Y-%m-%d<br/>%H:%M', this.x) + htooltip(el, this.y, _tooltip);}}
							});
							break;
						case 'pie':
							$(el).highcharts({
								chart: {plotBackgroundColor: null, plotBorderWidth: null, plotShadow: false},
								title: {text: _title},
								plotOptions: {pie: {allowPointSelect: true,cursor: 'pointer',dataLabels: {enabled: true,color: '#000000',connectorColor: '#000000',format: '<b>{point.name}</b>: {point.percentage:.1f} %'}}},
								series: [{type: _chart, name: _subtitle, data:_data}],
								tooltip: {formatter: function() { return this.series.name+': <b>'+ this.y +'%</b> <em>('+ Math.round((this.y/100)*_min).format(0,'.',',') +')</em>'; }}
							});
							break;
					}
				}
				<? if ($stats) : ?>
				
				
				fhighcharts('#pie_ally_troops', 'pie', 'Alliance troops', 'Troop points', <?=$pie_ally_troops?>, <?=$sum_troops?>, '');
				// Total points
				fhighcharts('#pie_points', 'pie', 'Total points VS Alliance points', 'Total points', <?=$pie_points?>, <?=$sum_points?>, '');
				// Number of troops
				fhighcharts('#pie_troops', 'pie', 'Troop points VS Alliance troops', 'Troop points', <?=$pie_troops?>, <?=$sum_troops?>, '');
				
				
				// Total points
				fhighcharts('#total_points', 'spline', 'Total points', '', <?=$_total_points?>, <?=$t_min_points?>, '');
				// Average points
				fhighcharts('#average_points', 'spline', 'Average points', 'Points of numbers defeat opponents', <?=$_average_points?>, <?=$_min_average?>, '');
                // villages
				fhighcharts('#villages', 'spline', 'Villages', 'Numbers of villages', <?=$_villages?>, <?=$_min_villages?>, '');
				// Number of troops and their points
				fhighcharts('#troop_points', 'spline', 'Troops points', 'Only troops points and number of troops', <?=$_troops_points?>, <?=$_min_troops?>, 'troops');
				// Defeat opponents
				fhighcharts('#defeat_opponents', 'spline', 'Defeat opponents', 'Points of numbers defeat opponents', <?=$_defeat_points?>, <?=$_min_defeat?>, '');
				<? endif; ?>
			});
        </script>
    </head>
    <body>
        <? if ( !empty($msg)) : ?>
            <div class="msg <?= ($error) ? 'error' : 'success'; ?>">
                <?= $msg; ?>
            </div>
        <? endif; ?>
        <!-- END -->
        <div class="bodyContainer">
            <div class="header">
                <h1><?=$config['player']?> - stats <em style="font-size:0.6em">(<?=date('d/m/Y H:i:s')?> | <?=date_default_timezone_get();?>)</em></h1>
            </div>
            <div class="new">
               </span> <a href="<?=$config['localhost']?>stats.php?player=<?=$id?>">Refresh</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>admintracking.php">« Go Back</a>
            </div>
            <br/>
			<h2 class="center" style="width:120px;">Week <?=$_week?> <?=$_year?></h2>
			<br/>
            <ul class="current_stats">
            	<li>
            		<label>Player name:</label>
            		<span><b><?=$tracking['name']?></b></span>
            	</li>
            	<li>
            		<label>Alliance (position):</label>
            		<span><b><?=isset($tracking['alliance']) ? $tracking['alliance'] : '-----'?></b></span>
            	</li>
            	<li>
            		<label>Total points:</label>
            		<span><b><?=number_format($tracking['total_points'], 0, ',', '.')?></b></span>
            	</li>
            	<li>
            		<label>Position:</label>
            		<span><b><?=number_format($tracking['position'], 0, ',', '.')?></b></span>
            	</li>
            	<li>
            		<label>Total villages:</label>
            		<span><b><?=number_format($tracking['total_villages'], 0, ',', '.')?></b></span>
            	</li>
            	<li>
            		<label>Average points:</label>
            		<span><b><?=number_format($tracking['average_points'], 0, ',', '.')?></b></span>
            	</li>
            	<li>
            		<label>Combats:</label>
            		<span><b><?=number_format($tracking['combats'], 0, ',', '.')?></b></span>
            	</li>
            	<li>
            		<label>Defeat opponents:</label>
            		<span><b><?=number_format($tracking['defeat_opponents'], 0, ',', '.')?></b></span>
            	</li>
            </ul>
			<? if (count($paginator) > 1) : ?>
            <br/>
			<ul class="form center" style="width: 460px">
			<? foreach($paginator as $i => $page) : ?>
				<li class="left padding8">
					<? if(is_array($page)) :?>
						<a href="<?=$config['localhost']?>stats.php?player=<?=$id?>&week=<?=$page[0]?>&year=<?=$page[1]?>">Week <?=$page[0]?> <?=$page[1]?></a>
					<? else : ?>
						<b style="font-size:0.9em;">Week <?=$_week?> <?=$_year?></b>
					<? endif; ?>
				</li>
			<? endforeach; ?>
				<li class="both"></li>
			</ul>
            <br/>
			<? endif; ?>
            <!-- CHARTS !!! -->
			<? if ($stats) : ?>
				<div class="table">
					<div id="pie_ally_troops" class="center"></div>
				</div>
				<div class="table">
					<div id="pie_points" class="table-cell"></div>
					<div id="pie_troops" class="table-cell"></div>
				</div>
				<div class="">
					<div id="total_points" class=""></div>
					<div id="average_points" class=""></div>
                    <div id="villages" class=""></div>
				</div>
				<div class="">
					<div id="troop_points" class=""></div>
					<div id="defeat_opponents" class=""></div>
				</div>
				<br/>
			<? else : ?>
				<p>There aren't data for generate charts yet</p>
			<? endif; ?>
        </div>
    </body>
</html>
<? endif; ?>