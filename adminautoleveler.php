<?php
include_once 'automate.class.php';
$autoleveler = json_decode(Automate::factory()->getAutoleveler(), TRUE);
$buildings = json_decode(Automate::factory()->getBuildingsRules(), TRUE);
$own_villages = Automate::factory()->getVillages('own');
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
    case 'new-village': // OK
        if ( !empty($_POST)) {
            // Check empty values
            if (empty($_POST['village'])) $msg = $error = "You must select a village";
            // Save scheduler
            if ( !$error) {
            	$autoleveler[$_POST['village']] = $_POST;
            	$autoleveler[$_POST['village']]['buildings'] = $own_villages[$_POST['village']]['buildings'];

              foreach ($buildings['buildings'] as $key => $value) {
                if (!array_key_exists($key, $autoleveler[$_POST['village']]['buildings'])) {
                  $autoleveler[$_POST['village']]['buildings'][$key] = 0;
                }
              }

            	$autoleveler[$_POST['village']]['working'] = array();
            	$autoleveler[$_POST['village']]['queue'] = array();
            	unset($autoleveler[$_POST['village']]['village']);
               if ($f = fopen($paths['autoleveler'], 'w')) {
                   fwrite($f, json_encode($autoleveler));
                   fclose($f);
                   $msg = "The village has been saved for building autoloader. <a href='{$config['localhost']}adminautoleveler.php'>refresh page</a>";
               } else {
                   Automate::factory()->log('E', "You don't have permission to write {$paths['autoleveler']} file");
               }
            }
        }
        break;
    case 'save-queue': // REVISION !!
       $error = !isset($_POST['queue']) ? true : false;
       if ( !$error) {
       		$village_id = $_POST['village'];
       		unset($_POST['village']);
       		$autoleveler[$village_id]['queue'] = $_POST['queue'];
       		//array_push($autoleveler[$village_id]['queue'], $data);
       		if ($f = fopen($paths['autoleveler'], 'w')) {
       				fwrite($f, json_encode($autoleveler));
       				fclose($f);
       				$msg = "The buildings's queue has been saved. <a href='{$config['localhost']}adminautoleveler.php'>refresh page</a>";
       		} else {
       				Automate::factory()->log('E', "You don't have permission to write {$paths['autoleveler']} file");
            }
       } else {
       		$msg = "No buildings to add queued. <a href='{$config['localhost']}adminautoleveler.php'>refresh page</a>";
       }
       break;
    case 'get-village': // OK
    	  $is_ajax = TRUE;
    	  $village_data = Automate::factory()->getVillage($_GET['type'], $id);
    	  echo $village_data;
    	  break;
    case 'get-rules': // OK
    	  $is_ajax = TRUE;
    	  echo Automate::factory()->getBuildingsRules();
    	  break;
    case 'get-buildings': // OK
    	  $is_ajax = TRUE;
    	  echo Automate::factory()->getAutoleveler($id);
    	  break;
    case 'delete': // OK
    	  unset($autoleveler[$id]);
    	  // Save file
        if ($f = fopen($paths['autoleveler'], 'w')) {
        	  fwrite($f, json_encode($autoleveler));
	        fclose($f);
	        $msg = "The village has been deleted. <a href='{$config['localhost']}adminautoleveler.php'>refresh page</a>";
	     } else {
	     	  Automate::factory()->log('E', "You don't have permission to write {$paths['autoleveler']} file");
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
            	var rules; // Rules
            	var village_id; // Village ID to handle
                var building; // Building name clicked
                var main_level; // Main level
                var error = false; // Handle error
                var data = new Array(); // Data to save
            	 // Rules
            	 $.ajax({
            	 		url: "<?=$config['localhost']?>adminautoleveler.php?action=get-rules",
                		context: document.body,
                		dataType: 'json'
                }).done(function(data) {
                		rules = data;
					 });
                // OPEN modals
                jQuery('#a_village_buildings').click(function(){
                    jQuery('#modalbox').css('display', 'block');
                    this.padding = parseInt(jQuery('#village_buildings').css('padding-left')) + parseInt(jQuery('#village_buildings').css('padding-right'));
                    this.left = Math.round((jQuery(window).width() - (jQuery('#village_buildings').width()+this.padding))/2);
                    jQuery('#village_buildings').css({'display': 'block', 'left': this.left + 'px'});
                });
                // Insert/edit buildings queued
                jQuery('.queue').click(function(){
                		village_id = jQuery(this).data('id');
                		jQuery('#modalbox').css('display', 'block');
                		this.padding = parseInt(jQuery('.queued_buildings').css('padding-left')) + parseInt(jQuery('.queued_buildings').css('padding-right'));
                		this.left = Math.round((jQuery(window).width() - (jQuery('.queued_buildings').width()+this.padding))/2);
                		jQuery('#modal-'+village_id).css({'display': 'block', 'left': this.left + 'px'});
                });
                // CLOSE modals
                jQuery('.close').click(function(){
                    jQuery('#modalbox').css('display', 'none'); jQuery('#village_buildings').css('display', 'none'); jQuery('.queued_buildings').css('display', 'none');
                });
                // CLOSE ESC modals
                jQuery(window).keyup(function(e){
                    if (e.keyCode == 27) { // press ESC
                        jQuery('#modalbox').css('display', 'none'); jQuery('#village_buildings').css('display', 'none'); jQuery('.queued_buildings').css('display', 'none');
                    }
                });
                // Confirm befrore delete
                jQuery('.delete-row').on('click', function() {
                		if (confirm('¿Are you sure to delete this row?')) window.location.href = '<?=$config['localhost']?>' + jQuery(this).data('location');
                });
                // Insert village name and coords via ajax
                jQuery('#own-selected').on('change', function(){
                	$.ajax({
                		url: "/adminautoleveler.php?action=get-village&type=own&id="+jQuery(this).val(),
                		context: document.body,
                		dataType: 'json'
                	}).done(function(data) {
                		jQuery('#village_name').val(data.name);
                		jQuery('#coord_x').val(data.x);
                		jQuery('#coord_y').val(data.y);
						});
                });
                // Remove previous queued buildings
                jQuery('.remove-queue').on('click', function() { jQuery(this).parent().remove(); })
                // Rules for buildings
                jQuery('.a-select-buildings').click(function() {
                		jQuery(this).toggleClass('active');
                		if (jQuery(this).hasClass('active')) {
                			jQuery('.select-buildings').css('display', 'block');
                		} else {
                			jQuery('.select-buildings').css('display', 'none');
                		}
                });
                /** handle add building levels **/
                jQuery('a.add-building').click(function() {
                	building = jQuery(this).data('name');
                	jQuery('.a-select-buildings').toggleClass('active');
                	jQuery('.a-select-buildings').html('<img src="<?=$config['localhost']?>media/img/'+jQuery(this).data('name')+'.png"> '+jQuery(this).data('name'));
                	jQuery('<input/>', {id: 'toBuilding', type: 'hidden', name: jQuery(this).data('name'), value: jQuery(this).data('name') }).appendTo('.a-select-buildings');
                	jQuery('.select-buildings').css('display', 'none');
                	// Get current buildings
                	$.ajax({
                		url: "<?=$config['localhost']?>adminautoleveler.php?action=get-buildings&type=own&id="+village_id,
                		context: document.body,
                		dataType: 'json'
                	}).done(function(data) {
                     main_level = data.main;
                     // check requirements
                     this.requirement = rules.buildings[building].requirement;
                     if(this.requirement !== undefined) {
                        jQuery.each(this.requirement, function(i, obj){
                           jQuery.each(obj, function(name, value){
                              error = (data[name] < value) ? true : false;
                              if (error) alert('You need research '+name+' to level '+value);
                           });
                        });
                     } else {
                        error = false; // fix previous error with requirements
                     }
                     // Active levels
                     if (!error) {
                        if (data[building] < rules.buildings[building].max_level)
                        {
                           // Active select & remove all options
                           jQuery('.select-levels').removeAttr('disabled');
                           jQuery('.select-levels').empty();
                           for(i=parseInt(data[building])+1; i <= parseInt(rules.buildings[building].max_level); i++) {
                              jQuery('<option/>', {value: i, html: i }).appendTo('.select-levels');
                           }
                        } else {
                           alert('You have researched all levels');
                           jQuery('.select-levels').attr('disabled', 'disabled');
                        }
                     } else {
                        jQuery('.select-levels').attr('disabled', 'disabled');
                     }
					});
                });
                // Add building levels
                jQuery('.add-autoleveler').click(function() {
                  this.select = jQuery('#modal-'+village_id+' .select-levels');
                  if ( !this.select.attr('disabled')) {
                     for (i=parseInt(this.select.children().val()); i<=parseInt(this.select.val()); i++) {
                        var o = {};
                        o[building] = i;
                        data.push(o);
                     }
                     printData();
                  }
                });
                /** End rules **/

                /* print data array */
                function printData() {
                   jQuery('#json-'+village_id).empty();
                   jQuery.each(data, function(i,obj){
                      jQuery.each(obj, function(name,val){
                         var date = new Date(((parseInt(rules.buildings[name]['buildTime'][parseInt(val-1)])*1000)*(parseInt(rules.buildings[name]['buildTime'][parseInt(main_level-1)]))/100)*rules['timeMultipler']);
                         var hours = date.getHours(); var minutes = date.getMinutes(); var seconds = date.getSeconds();
                         var formattedTime = hours + 'h ' + minutes + 'm ' + seconds + 's';
                         jQuery('<li/>', {'class':'c'+i}).appendTo('#json-'+village_id);
                         jQuery('<input/>', {type: 'hidden', name: 'queue[]['+name+']', value: val}).appendTo('#json-'+village_id+' li.c'+i);
                         jQuery('<span/>', {html: parseInt(i+1)+'. ', 'style': 'font-size:16px; font-weight: bold;'}).appendTo('#json-'+village_id+' li.c'+i);
                         jQuery('<img/>', {src: '<?=$config['localhost']?>media/img/'+name+'.png', alt: name}).appendTo('#json-'+village_id+' li.c'+i);
                         jQuery('<span/>', {html: name+' <b>'+val+'</b> <span><em>( <img src="<?=$config['localhost']?>media/img/stone.png"/>'+rules.buildings[name]['stone'][parseInt(val-1)]+' <img src="<?=$config['localhost']?>media/img/wood.png"/>'+rules.buildings[name]['wood'][parseInt(val-1)]+' <img src="<?=$config['localhost']?>media/img/iron.png"/>'+rules.buildings[name]['iron'][parseInt(val-1)]+')</em></span> <span>Estimated time: '+formattedTime+'</span>'}).appendTo('#json-'+village_id+' li.c'+i);
                         jQuery('<div/>', {'class': 'right remove-queue', 'html': '<b>x</b>', style: 'font-size:16px; padding: 1px 4px; cursor: pointer;'}).appendTo('#json-'+village_id+' li.c'+i).on('click', function(){
                            // Remove selected queued
                            jQuery(this).parent().remove();
                         });
                      });
                   });
                   jQuery('#toBuilding').remove();
                }
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
        <div id="village_buildings" class="modalbox">
            <div class="close">close</div>
            <div class="container">
                <form id="village_form" action="<?=$config['localhost']?>adminautoleveler.php?action=new-village" method="post">
                    <ul>
                    		<li>
                            <label>Select Village</label>
                            <select id="own-selected" class="span16" name="village"> <!-- No name value, via ajax -->
                            	<option disabled="disabled" selected="selected">- Select village -</option>
                            	<? foreach($own_villages as $village) : ?>
                            		<option value="<?=$village['id']?>" <?=(isset($_POST['from_id']) && $_POST['from_id'] == $village['id']) ? ' selected="selected"' : null;?>><?="{$village['name']} ({$village['x']}|{$village['y']})";?></option>
                            	<? endforeach; ?>
                            </select>
                            <input type="hidden" id="village_name" name="name" value="<?= (isset($_POST['name'])) ? $_POST['name'] : null;?>"/>
                            <input type="hidden" id="coord_x" name="x" value="<?= (isset($_POST['x'])) ? $_POST['x'] : null;?>"/>
                            <input type="hidden" id="coord_y" name="y" value="<?= (isset($_POST['y'])) ? $_POST['y'] : null;?>"/>
                        </li>
                    </ul>
                    <input type="submit" value="Add"/>
                </form>
            </div>
        </div>
        <!-- END -->
        <!-- ADD BUILDING LEVELS QUEUED -->
        <? if (is_array($autoleveler) && count($autoleveler) > 0) : ?>
            <? foreach ($autoleveler as $village_id => $data) : ?>
        <div class="queued_buildings modalbox" id="modal-<?=$village_id?>">
            <div class="close">close</div>
            <div class="container">
                <form class="village_form" action="<?=$config['localhost']?>adminautoleveler.php?action=save-queue" method="post">
                    <ul>
                    		<li>
                    			<div class="inlineblock left" style="position:relative">
	                    			<span>Add building</span>
   	                 			<span class="a-select-buildings">
   	                 				Select building ▼
   	                 			</span>
   	                 			<div class="select-buildings">
                    					<ul>
                               	<? foreach($buildings['buildings'] as $name => $_data) : ?>
                                		<li>
                                			<a id="a-<?=$name?>" class="add-building" href="#add-<?=$name?>" data-name="<?=$name?>">
                                				<img src="<?=$config['localhost']?>media/img/<?=$name?>.png" title="<?=$name?>"/>&nbsp;&nbsp;&nbsp;<?=$name?>
                                			</a>
                                		</li>
                                	<? endforeach; ?>
                    					</ul>
                    				</div>
                    			</div>
                    			<div class="inlineblock left" style="position:relative">
                    				<span>To level</span>
   	                 			<select class="select-levels" disabled="disabled">
   	                 				<option>- Select building-</option>
   	                 			</select>
                    			</div>
                    			<div class="inlineblock left" style="position:relative">
                    				<span class="add-autoleveler">+</span>
                    			</div>
                           <div class="both"></div>
                    		</li>
                    		<!-- previuos queue -->
								<li class="queue">
									<? if (count($data['queue']) > 0) : ?>
									<ul>
									<? foreach($data['queue'] as $i => $queue) : ?>
											<li class="alignleft">
											<? foreach($queue as $name => $level) : ?>
													<input type="hidden" name="queue[][<?=$name?>]" value="<?=$level?>"/>
													<span><b><?=$i+1?>.</b></span> <img src="<?=$config['localhost']?>media/img/<?=$name?>.png"/> <span><?=$name?> <b><?=$level?></b></span>
													<span class="separator"><em>(<img src="<?=$config['localhost']?>media/img/stone.png"/><?=$buildings['buildings'][$name]['stone'][$level-1]?> <img src="<?=$config['localhost']?>media/img/wood.png"/><?=$buildings['buildings'][$name]['wood'][$level-1]?> <img src="<?=$config['localhost']?>media/img/iron.png"/><?=$buildings['buildings'][$name]['iron'][$level-1]?>)</em></span>
													<span class="separator">Estimated time: <?=Automate::factory()->getformatTime($buildings['buildings'][$name]['buildTime'][$level-1]*($buildings['buildings']['main']['feature'][$autoleveler[$village_id]['buildings']['main']]/100)*$buildings['timeMultipler'])?></span>
													<div class="right remove-queue" style="font-size:16px; padding: 1px 4px; cursor: pointer;"><b>x</b></div>
											<? endforeach; ?>
										</li>
										<? endforeach; ?>
									</ul>
									<? endif; ?>
									<!-- add queued via javascript -->
									<ul id="json-<?=$village_id?>"></ul>
								</li>
                        <li>
                           <input type="submit" value="Save"/>
                        </li>
                    </ul>
                    <div id="villageIdQueue">
                    		<input type="hidden" name="village" value="<?=$village_id?>"/>
                    </div>
                </form>
         	</div>
		  </div>
		  		<? endforeach; ?>
        <? endif; ?>
		  <!-- END -->
        <div class="bodyContainer">
            <div class="header">
              <h1><?=$config['player']?> - Village building's autoleveler <em style="font-size:0.6em">(<?=date('d/m/Y H:i:s')?> | <?=date_default_timezone_get();?>)</em></h1>
            </div>
                <div class="new">
                    <a id="a_village_buildings">Add village</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>adminautoleveler.php">Refresh</a> <span class="separator"> | </span> <a href="<?=$config['localhost']?>">« Go Back</a>
                </div>
                <?
                $editable = (isset($action) && isset($id)) && $action == 'edit';
                ?>
                <form action="<?=$config['localhost']?>adminautoleveler.php?action=<?=($editable) ? 'edit' : 'disable'?>" method="post">
                <!-- OWN VILLAGES -->
                <div class="left" style="margin-right: 10px;">
                <table class="farms">
                  <thead>
                    <tr>
                      <th>Village ID</th>
                      <th>Village name</th>
                      <th>Current buildings</th>
					  <th>Current materials</th>
					  <th class="span3">Free settlers</th>
                      <th>Working on</th>
                      <th>Queue</th>
                      <th class="span7">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  <? if (is_array($autoleveler) && count($autoleveler) > 0) : ?>
                    <? foreach ($autoleveler as $village_id => $data) : ?>
                    		<tr>
                    			<td>
									<a href="<?=$config['protocol']?>://<?=$config['server']?>.<?=$config['domain']?>/game.php?village=<?=$village_id.$config['main']?>" target="_blank">
										<?= $village_id ?>
									</a>
                    			<td>
									<?=$own_villages[$village_id]['name']?> (<?=$own_villages[$village_id]['x']?>|<?=$own_villages[$village_id]['y']?>)
								</td>
                    			<td class="alignleft">
                    			<? foreach($data['buildings'] as $name => $level) : ?>
                    				<div class="block">
                    					<b><?=$level?></b> <img src="<?=$config['localhost']?>media/img/<?=$name?>.png"/> <?=ucfirst($name)?>
										<? if (isset($buildings['buildings'][$name]['feature'][$level-1])) : ?>
										<span><em>(<?=($name == 'stone' || $name == 'wood' || $name == 'iron') ? $buildings['buildings'][$name]['feature'][$level-1]*$config['speed'] : $buildings['buildings'][$name]['feature'][$level-1]?>)</em></span>
										<? endif; ?>
                    				</div>
                    			<? endforeach; ?>
                    			</td>
								<td>
									<?=isset($own_villages[$village_id]['updated'])
										? '<em>Last updated '.date('Y/m/d H:i:s', $own_villages[$village_id]['updated']).'</em><br/><br/>'
										: null
									?>
									<? if(isset($own_villages[$village_id]['materials'])) : ?>
									<span class="block"><img src="<?=$config['localhost']?>media/img/stone.png"/>
										<?=number_format($own_villages[$village_id]['materials']['stone'], 0,'','.')?>
									</span>
									<span class="block"><img src="<?=$config['localhost']?>media/img/wood.png"/>
										<?=number_format($own_villages[$village_id]['materials']['wood'], 0,'','.')?>
									</span>
									<span class="block"><img src="<?=$config['localhost']?>media/img/iron.png"/>
										<?=number_format($own_villages[$village_id]['materials']['iron'], 0,'','.')?>
									</span>
									<br/><em class="block">Updated every 20 minuts (aprox.)</em>
									<? else : ?>
									<em>- waiting -<em>
									<? endif; ?>
								</td>
								<td>
									<? if(isset($own_villages[$village_id]['settlers'])) : ?>
									<?=number_format($own_villages[$village_id]['settlers'], 0,'','.')?>
									<? else : ?>
									<em>- waiting -<em>
									<? endif; ?>
								</td>
                    			<td>
                    				<? if (isset($data['working']) && count($data['working']) > 0) : ?>
                    					<ul>
                    					<? foreach($data['working'] as $i => $working) : ?>
                    						<li class="alignleft">
                    						<? foreach($working as $name => $value) : ?>
                    							<span><b><?=$i+1?>.</b></span> <img src="<?=$config['localhost']?>media/img/<?=$name?>.png"/> <span><?=$name?> <?=$value['level']?></span>
                    							<span class="separator">(<?=strftime('%a %e %h %k:%M:%S', $value['end'])?>)</span>
                    						<? endforeach; ?>
                    						</li>
                    					<? endforeach; ?>
                    					</ul>
                    				<? else : ?>
                    					There aren't yet buildings to build
                    				<? endif; ?>
                    			</td>
                    			<td style="vertical-align: top;">
                    				<div class="autoleveler">
                    				<? if (isset($data['queue']) && count($data['queue']) > 0) : ?>
                    					<ul>
                    					<? foreach($data['queue'] as $i => $queue) : ?>
                    						<li class="alignleft">
                    						<? foreach($queue as $name => $level) : ?>
	                    							<span><b><?=$i+1?>.</b></span> <img src="<?=$config['localhost']?>media/img/<?=$name?>.png"/> <span><?=$name?> <?=$level?></span>
	                    							<span class="separator"><em>(<img src="<?=$config['localhost']?>media/img/stone.png"/><?=$buildings['buildings'][$name]['stone'][$level-1]?> <img src="<?=$config['localhost']?>media/img/wood.png"/><?=$buildings['buildings'][$name]['wood'][$level-1]?> <img src="<?=$config['localhost']?>media/img/iron.png"/><?=$buildings['buildings'][$name]['iron'][$level-1]?>)</em></span>
	                    							<span class="separator">Estimated time: <?=Automate::factory()->getformatTime($buildings['buildings'][$name]['buildTime'][$level-1]*($buildings['buildings']['main']['feature'][$autoleveler[$village_id]['buildings']['main']]/100)*$buildings['timeMultipler'])?>
	                    							</span>
                    						<? endforeach; ?>
                    						</li>
                    					<? endforeach; ?>
                    					</ul>
                    				<? else : ?>
                    					There aren't yet buildings queued
                    				<? endif; ?>
                    				</div>
                    			</td>
                    			<td>
                    				<a class="queue green button" data-id="<?=$village_id?>">add buildings</a>
                    				<br/><br/>
                    				<a class="delete-row" data-location="adminautoleveler.php?action=delete&id=<?=$village_id?>"  data-id="<?=$village_id?>">Delete</a>
                    			</td>
                    		</tr>
                    <? endforeach; ?>
                  <? else : ?>
                    <tr>
                        <td colspan="8">You don't have any autoleveler village.</td>
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
