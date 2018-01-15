<?php
$start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent

// Simulate human iteraction
if (!isset($_GET['start'])) {
	sleep(rand(30,70));
}
// Check if scheduler is running
if (Automate::factory()->isScheduler()) {
	Automate::factory()->log('E', "Scheduler script is running");
	exit();
}

/* OK !!
 * Flag attacks
 */
$config = Automate::factory()->getConfig();
$paths = Automate::factory()->getPaths();
$villages = Automate::factory()->getVillages('own'); // For auto-defense
$url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?{$config['flag']}";
$attacks = @Automate::factory()->parser_attack($url);
$snobs = updateFlag(Automate::factory()->getFlag());

$html = $bbcode = '';
$alliance_email = false;

// Flag file (like a log files, one per day)
$flag_file = array();
$_flag_file = dirname(dirname(__FILE__))."/{$paths['flag_attacks']}";
if (file_exists($_flag_file)) {
	$flag_file = (filesize($_flag_file) > 0) ? json_decode(file_get_contents($_flag_file), true) : array();
}

echo '<pre>';
print_r($attacks);
echo '<pre>';

// Check if parser run correctly
if ($attacks) {
	attackrevision($config, $html, $bbcode, $attacks, $flag_file, $attacks['pages'], $attacks['servertime'], $alliance_email, $snobs);
} else {
	Automate::factory()->log('E', 'Parsing flag attacks'); // Save Error
   // Retry
   $attacks = @Automate::factory()->parser_attack($url);
   attackrevision($config, $html, $bbcode, $attacks, $flag_file, $attacks['pages'], $attacks['servertime'], $alliance_email, $snobs);
}
/** OK !!
 */
function attackrevision(&$config, &$html, &$bbcode, Array &$attacks, &$flag_file, $pages, $servertime, $alliance_email, &$snobs) {
   $start = 0;
   $snobs_count = 0;
   $pages = ($pages == 0) ? 1 : $pages;
   $all_attacks = $attacks;
   for($i=0; $i<$pages; $i++) {
	  unset($attacks['pages'], $attacks['servertime']);
      foreach($attacks as $key => $attack) {
         $from = array('x' => $attack['from']['x'], 'y' => $attack['from']['y']);
         $to = array('x' => $attack['to']['x'], 'y' => $attack['to']['y']);
         $kata_speed = (int)Automate::factory()->getDistance($from, $to, $config['troops_speed']['kata']);
         $snob = ($attack['unixtime']-40) > $kata_speed;
         // Snob
         if ($snob) {
            // Exists previous attack?
            if (count($flag_file) > 0) {
               foreach ($flag_file as $old_attack) {
                  if (($attack['to']['x'] == $old_attack['to']['x']) && ($attack['to']['y'] == $old_attack['to']['y']) && ($attack['from']['x'] == $old_attack['from']['x']) && ($attack['from']['y'] == $old_attack['from']['y']) && ($attack['when'] == $old_attack['when'])) {
                     continue 2; // Break this loop and continues the $attack loop
                  }
               }
            }
			   $_arrival = date('d/m/Y H:i:s', $attack['unixtime']+$servertime+$config['flag_time']);
            $html .= "<tr><td>{$attack['to']['player']} - {$attack['to']['colony']}</td><td>{$attack['from']['player']} ({$attack['from']['ally']}) - {$attack['from']['colony']}</td><td>{$_arrival}</td><td>{$attack['countdown']}</td></tr>";
			   $bbcode .= "<br>[player]{$attack['to']['player']}[/player]<br>[img_snob] [village]{$attack['to']['x']}|{$attack['to']['y']}[/village] [b]{$_arrival}[/b]<br/><br/>Atacante: {$attack['from']['player']} [village]{$attack['from']['x']}|{$attack['from']['y']}[/village]<br/><br/>";
            $snobs_count++;

            // Add snobs to file
            addSnobs($snobs, $attack, $servertime, $_arrival);

			   $alliance_email = true;

         }
         if ((strtolower($attack['to']['player']) == strtolower($config['player'])) && !$snob) {
         //$_player = strtolower(trim($attack['to']['player']));
         //if (($_player == 'xxx' || $_player == 'yyy') && !$snob) {
            // Exists previous attack?
            if (count($flag_file) > 0) {
               foreach ($flag_file as $old_attack) {
                  if (($attack['to']['x'] == $old_attack['to']['x']) && ($attack['to']['y'] == $old_attack['to']['y']) && ($attack['from']['x'] == $old_attack['from']['x']) && ($attack['from']['y'] == $old_attack['from']['y']) && ($attack['when'] == $old_attack['when'])) {
                     continue 2; // Break this loop and continues the $attack loop
                  }
               }
            }
			// Get type of troops are attacking
            $_speedtroop = array();
            foreach ($config['troops_speed'] as $name => $speed) {
				$_speedtime = (int)Automate::factory()->getDistance($from, $to, $speed);
            	if ( $attack['unixtime'] < $_speedtime) {
            		$_speedtroop[] = $name;
            	}
            }
			$_speedtroop = implode(" | ", $_speedtroop);
            $html .= "<tr><td>{$attack['to']['player']} - {$attack['to']['colony']}</td><td>{$attack['from']['player']} ({$attack['from']['ally']}) - {$attack['from']['colony']}</td><td>{$attack['when']}</td><td>{$attack['countdown']} <em>({$_speedtroop})</em></td></tr>";
         }
      }
      if ($i+1 < $pages) {
         $start += 50;
         $url = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?{$config['flag']}&start={$start}";
         $attacks = @Automate::factory()->parser_attack($url);
         $all_attacks = array_merge($all_attacks, $attacks);
      }
   }
   // if HTML have empty, there aren't new attacks
   if ( !empty($html)) {




      // Save attacks and sendmail
      Automate::factory()->save_flagattacks($all_attacks, $snobs);
      if ($snobs_count > 0) Automate::factory()->log('F', "{$snobs_count} snobs has been detected."); // Save Log
      // Sendmail
      $html = "<html><head><style type='text/css'>.center{text-align:center;} table{border:1px solid #ccc;border-spacing:0;} th{background-color:#ccc;border-bottom:1px solid #ccc;text-shadow:1px 1px 0 #eee;} td,th{border-left:1px solid #ccc;padding: 4px 8px;}</style></head><body><table><thead><tr><th>TO</th><th>FROM</th><th>ARRIVAL</th><th class='center'>COUNTDOWN</th></tr></thead><tbody>{$html}</tbody></table><br/>{$bbcode}</body></html>";
      $mailer = sendmail($html, $config, $alliance_email);
      if ($mailer) {
         echo '<br>email sended<br>';
      } else {
         Automate::factory()->log('E', "The email with flag attacks wasn't sended"); // Save Error
         echo "<br>The email with flag attacks wasn't sended<br>";
      }
   } else {
      echo "<br>There aren't new attacks<br>";
   }
}

function updateFlag (&$flag) {
   $_time = time();
   foreach($flag as $user => $users) {
      foreach($users as $colony => $colonies) {
         foreach($colonies as $unixtime => $attacks) {
            if ($_time > $unixtime) {
               unset($flag[$user][$colony][$unixtime]); // Remove old attacks
            }
         }
         // Remove colony if is empty
         if (count($flag[$user][$colony]) == 0) {
            unset($flag[$user][$colony]);
         }
      }
      // Remove user if is empty
      if (count($flag[$user]) == 0) {
         unset($flag[$user]);
      }
   }
}

function addSnobs (&$snobs, &$attack, &$servertime, &$arrival) {
   if (!isset($snobs[$attack['to']['player']])) {
      // Create player array if not exists
      $snobs[$attack['to']['player']] = array();
   }
   if (isset($snobs[$attack['to']['player']])) {
      $_village = $attack['to']['colony'];
      // Create village array if not exists
      if (!isset($snobs[$attack['to']['player']][$_village])) {
         $snobs[$attack['to']['player']][$_village] = array();
      }
      if (isset($snobs[$attack['to']['player']][$_village])) {
         $_unixtime = $attack['unixtime'] + $servertime;
         if (!isset($snobs[$attack['to']['player']][$_village][$_unixtime])) {
            $snobs[$attack['to']['player']][$_village][$_unixtime] = array();
            $snobs[$attack['to']['player']][$_village][$_unixtime]['quantity'] = 1;
            $snobs[$attack['to']['player']][$_village][$_unixtime]['arrival'] = $arrival;
            $snobs[$attack['to']['player']][$_village][$_unixtime]['coords'] = array();
            $snobs[$attack['to']['player']][$_village][$_unixtime]['coords']['x'] = $attack['to']['x'];
            $snobs[$attack['to']['player']][$_village][$_unixtime]['coords']['y'] = $attack['to']['y'];
         } else {
            $snobs[$attack['to']['player']][$_village][$_unixtime]['quantity']++;
         }
      }
   }
   // echo "<pre>";
   // print_r($snobs);
   // echo "</pre>";
}

/** OK
 * Send email with Swift mailer
 */
function sendmail(&$html, $config, $alliance_email) {
	include_once ROOT.'lib/swift_required.php';
	// Create the Transport
	$transport = Swift_SmtpTransport::newInstance($config['email']['host'], $config['email']['port'], 'ssl')
		->setUsername($config['email']['username'])
		->setPassword($config['email']['password']);

	// Create the Mailer using your created Transport
	$mailer = Swift_Mailer::newInstance($transport);

   $today = date('d/m/Y');
	// Create a message
   $message = Swift_Message::newInstance("Attack report {$today} on world {$config['server']}")
		->setFrom($config['email']['from'])
		->setTo($config['email']['to'])
		->setBody(strip_tags($html))
		->addPart($html, 'text/html');
   if ($alliance_email) {
      if (isset($config['email']['cc']) && count($config['email']['cc']) > 0) {
	      $message->setCc($config['email']['cc']);
	  }
   }
   // Send the message
   return $mailer->send($message);
}

$end = microtime(true);
$execution_time = round($end-$start, 4);
echo "<br>$execution_time seconds";
if (isset($_GET['start'])) {
	echo "<br><br>";
	echo "<a href='{$config['localhost']}'>Go to index</a>";
}
?>
