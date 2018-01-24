<?php
define('ROOT', realpath(dirname(__FILE__)).'/');
class Automate {

   private static $_paths = array(
       'path' => "json",
       'config' => 'config/automate.json',
       'buildings' => 'config/autoleveler.json',
       'farms' => 'json/farms.json',
       'villages' => 'json/villages.json',
       'flag_attacks' => 'json/flag_attacks.json',
       'flag' => 'json/flag.json',
       'farm_attacks' => 'json/farm_attacks.json',
       'autoleveler' => 'json/autoleveler.json',
       'scheduler' => 'json/scheduler.json',
       'current_scheduler' => 'json/current_scheduler.json',
       'scheduler_flag' => 'scripts/scheduler_flag.txt',
       'proof' => 'scripts/proof.txt',
       'tracking' => 'json/tracking.json',
       'tracking_data' => 'json/tracking',
       'trade' => 'json/trade.json',
       'trading' => 'json/trading.json',
       'village_map' => 'media/map',
       'recruit' => 'json/recruit.json'
       );
   private static $_owner = 'ubuntu';
   private static $_instance;
   private $_config; // automate config
   private $_attacks; // save farm_attacks data

   public static function factory()
   {
      if (is_dir(ROOT.self::$_paths['path'])) {
          if (is_file(ROOT.self::$_paths['config'])) {
             if (!empty(self::$_instance)) {
                return self::$_instance;
             } else {
                return self::$_instance = new Automate(ROOT.self::$_paths['config']);
             }
          } else {
              self::log('E', 'The script need a file (kingsage.json) on directory (json) to run it');
              return FALSE;
          }
      } else {
          self::log('E', 'The script need a folder called "json" to run it');
          return FALSE;
      }
   }

   private function __construct($config)
   {
      $_config = file_get_contents($config);
      $this->_config = json_decode($_config, true);
   }

   /** OK !
    * GETTERS
    * @return array
    */
   public function getConfig() {
      return self::$_instance->_config;
   }
   public function getPaths() {
     return self::$_paths;
   }
   public function getFarms() {
      $filename = ROOT.self::$_paths['farms'];
      return (is_file($filename)) ? file_get_contents($filename) : FALSE;
   }
   public function getFarmAttacks($save = FALSE) {
      $filename = ROOT.self::$_paths['farm_attacks'];
      if (is_file($filename)) {
          $_attacks = file_get_contents($filename);
          // used for saves_attacks
          if ($save) self::$_instance->_attacks = json_decode($_attacks, TRUE);
          return $_attacks;
      } else {
          return FALSE;
      }
   }
   public function getLog($day = null) {
      $day = (is_null($day)) ? date('Ymd') : $day;
      $path = 'logs';
      $logname = ROOT."{$path}/{$day}.log";
      return (is_file($logname)) ? file_get_contents($logname) : FALSE;
   }
   public function getVillages($type = null) {
      $filename = ROOT.self::$_paths['villages'];
      $villages = (is_file($filename)) ? json_decode(file_get_contents($filename), TRUE) : FALSE;
      if ($villages) {
        if ($type) {
            return isset($villages[$type]) ? $villages[$type] : FALSE;
        } else {
            return $villages;
        }
      } else {
          return FALSE;
      }
   }
   public function getAutoleveler($id = null) {
       $filename = ROOT.self::$_paths['autoleveler'];
       if ( !is_null($id)) {
           $buildings = is_file($filename) ? json_decode(file_get_contents($filename), TRUE) : FALSE;
           // Update buildings if there're queue
           if ( !empty($buildings[$id]['queue'])) {
               $data = array();
               foreach ($buildings[$id]['buildings'] as $name => $level) {
                   $onqueue = false;
                   foreach ($buildings[$id]['queue'] as $queue) {
                       foreach ($queue as $_name => $_level) {
                           if ($_name == $name) {
                               $onqueue = true;
                               $data[$name] = $_level;
                           }
                       }
                   }
                   if( !$onqueue) {
                       $data[$name] = $level;
                   }
               }
           } else {
               $data = $buildings[$id]['buildings'];
           }

           return ($buildings) ? json_encode($data) : FALSE;
       } else {
           return is_file($filename) ? file_get_contents($filename) : FALSE;
       }
   }
   public function getTracking($id = null) {
      $filename = ROOT.self::$_paths['tracking'];
      if (is_null($id)) {
          return (is_file($filename)) ? json_decode(file_get_contents($filename), TRUE) : FALSE;
      } else {
          if (is_file($filename)) {
              $_player = json_decode(file_get_contents($filename), TRUE);
              return $_player[$id];
          } else {
              return FALSE;
          }
      }
   }
   public function getTrackingHistory($player_id, $week, $year) {
      $week = (int)$week < 9 ? '0'.(int)$week : $week;
      $filename = ROOT.self::$_paths['tracking_data']."/{$player_id}/{$week}_{$year}.json";
      if (is_file($filename)) {
          return json_decode(file_get_contents($filename), TRUE);
      } else {
          return FALSE;
      }
   }
   public function getTrades() {
      $filename = ROOT.self::$_paths['trade'];
      return (is_file($filename)) ? file_get_contents($filename) : FALSE;
   }
   public function getTradings() {
      $filename = ROOT.self::$_paths['trading'];
      return (is_file($filename)) ? file_get_contents($filename) : FALSE;
   }
   public function getRecruit() {
      $filename = ROOT.self::$_paths['recruit'];
      return (is_file($filename)) ? file_get_contents($filename) : FALSE;
   }
   /** Ajax **/
   public function getVillage($type, $id) {
      $filename = ROOT.self::$_paths['villages'];
      $villages = (is_file($filename)) ? json_decode(file_get_contents($filename), TRUE) : FALSE;
      return ($villages) ? json_encode($villages[$type][$id]) : FALSE;
   }
   public function getScheduler() {
      $filename = ROOT.self::$_paths['scheduler'];
      return (is_file($filename)) ? json_decode(file_get_contents($filename), TRUE) : FALSE;
   }
   public function getCurrentScheduler() {
      $filename = ROOT.self::$_paths['current_scheduler'];
      return (is_file($filename)) ? json_decode(file_get_contents($filename), TRUE) : FALSE;
   }
   public function getBuildingsRules() {
      $filename = ROOT.self::$_paths['buildings'];
      return is_file($filename) ? file_get_contents($filename) : FALSE;
   }
   public function isScheduler() {
      return file_exists(ROOT.self::$_paths['scheduler_flag']);
   }
   public function getProof() {
      $filename = ROOT.self::$_paths['proof'];
      return is_file($filename) ? file_get_contents($filename) : FALSE;
   }
   public function getFlag() {
      $filename = ROOT.self::$_paths['flag'];
      return (is_file($filename)) ? json_decode(file_get_contents($filename), TRUE) : FALSE;
   }

   /** OK!
    * Calculate de distance origin/target in seconds format
    *
    * @param array $from
    * @param array $to
    * @param Integer $speed
    * @return integer (distance in seconds)
    */
   public function getDistance(Array $from, Array $to, $speed_troop)
   {
      $speed_server = self::$_instance->_config['speed_troops'];
      $time = ($speed_troop * sqrt(pow(($to['x']-$from['x']),2) + pow(($to['y']-$from['y']),2)))/$speed_server;
      $time_temp = explode(".", $time);
      if (isset($time_temp[1])) {
        $time_temp[1] = (isset($time_temp[1][1])) ? "{$time_temp[1][0]}{$time_temp[1][1]}" : "{$time_temp[1][0]}0";
        if ($time_temp[1][1] == 9) $time_temp[1] = $time_temp[1]+1;
        $time_temp[1] = round($time_temp[1]/10, 1, PHP_ROUND_HALF_UP)*10;
        $time_temp[1] = (int)$time_temp[1];
      }
      if (isset($time_temp[1])) $secs = ($time_temp[1] > 9) ? ($time_temp[1]*60)/100 : $time_temp[1];
      return (isset($time_temp[1])) ? round((($time_temp[0])*60)+$secs) : round(($time_temp[0])*60);
   }
   /** OK!
    * Ajax function
    * Return departure datetime from arrival datetime
    */
   public function getStartDistance($post) {
      $_from = array('x' => $post['from_x'], 'y' => $post['from_y']);
      $_to = array('x' => $post['to_x'], 'y' => $post['to_y']);
      $_method = ($post['method'] == 'spy') ? 'S' : NULL;
      $_speed = $this->speed_troops($post['troops'], $_method);
      $_time = $this->getDistance($_from, $_to, $_speed);
      $_strtime = $this->getformatTime($_time);
      $_arrival = strtotime($post['datetime']);
      return json_encode(array('distance'=> $_strtime, 'start' => date('m/d/Y H:i:s', $_arrival-$_time)), TRUE);
   }
   public function getStartGameDistance($from, $to, $troops, $datetime, $method) {
      $method = ($method == 'spy') ? 'espy' : $method;
      $res = $this->attack($from, $to, $troops, $method, TRUE);
      $_time = false;
      if ($res) {
        $dom = new DOMDocument();
        if (@$dom->loadHTML($res)) {
            $classname = 'borderlist';
            $finder = new DomXPath($dom);
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
            foreach ($nodes as $key => $tables) {
                if (preg_match('/\d{1,3}:\d{1,2}:\d{1,2}/', $tables->nodeValue, $match)) {
					$_time = $match[0];
					if (preg_match('/ [1-9]{1} /', $tables->nodeValue, $match)) {
						$_pTime = explode(':',$_time);
						$_pTime[0] = (int)$_pTime[0] + ((int)$match[0]*24);
						$_time = implode(':', $_pTime);

					}
                    break 1;
                } else {
                    continue;
                }
            }
        } else {
            $res = FALSE;
        }
      }
      if ($_time) {
        $_time = explode(':', $_time);
        $_time = (((int)$_time[0]*3600)+((int)$_time[1]*60))+(int)$_time[2];
        $_strtime = $this->getformatTime($_time);
        return json_encode(array('type' => 'ok', 'distance' => $_strtime, 'start' => date('m/d/Y H:i:s', $datetime-$_time)), TRUE);
      } else {
        return $res ? json_encode(array('type' => 'ko', 'msg' => $res)) : json_encode(array('type' => 'ko', 'msg' => 'OUCH! Something was wrong!!'));
      }
   }

   /** OK!
    * Review the attack file to resend attacks
    * @param integer $target_id The id village
    * @return mixed null|boolean
    */
   public function getAttackStatus($target_id)
   {
      $farmattacks = json_decode($this->getFarmAttacks(TRUE), TRUE);
      if (count($farmattacks) > 0) {
        if (array_key_exists($target_id, $farmattacks)) {
            if (time() > $farmattacks[$target_id]['unixtime']) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return NULL;
        }
      } else {
        return NULL;
      }
   }
   /** OK!
    * Check if the attack arrive between 08am and 12pm
    * @param array from
    * @param array to
    * @param array troops
    * @return boolean
    */
   public function getNocturnalMode(Array $from, Array $to, $troops, $mode)
   {
      $speed = $this->speed_troops($troops, $mode);
      $time = $this->getDistance($from, $to, $speed);
      $now = strtotime('now');
      $_arrive = $now+$time;
      $_8am = strtotime('today 8:00:00');
      $_12pm = strtotime('today 23:59:59');
      return (($_arrive >= $_8am ) && ($_arrive <= $_12pm));
   }
   /** OK!
    * Check if the attack arrive between specific range (start,end)
    * @param array from
    * @param array to
    * @param array troops
    * @return boolean
    */
   public function getRangeMode(Array $from, Array $to, Array $range, $troops, $mode)
   {
      $speed = $this->speed_troops($troops, $mode);
      $time = $this->getDistance($from, $to, $speed);
      $now = strtotime('now');
      $_arrive = $now+$time;
      $_start = strtotime("today {$range['start']}");
      $_end = strtotime("today {$range['end']}:59");
      return (($_arrive >= $_start ) && ($_arrive <= $_end));
   }
   /* OK
    * Datetime to arrival
    * @param integer $datetime
    * @return string
    */
   public function getformatTime($datetime) {
      $_string = '';
         $_days = floor($datetime / 86400);
      $_hours = ($datetime / 3600) % 24;
      $_mins = ($datetime / 60) % 60;
      $_secs = ($datetime) % 60;
      $_string .= ($_days == 0) ? "" : "{$_days}d ";
      $_string .= ($_hours  == 0) ? "" : "{$_hours}h ";
      $_string .= "{$_mins}m {$_secs}s";
      return $_string;
   }
   /** REVISION !!!
    * Simulate human interaction to send attack from origin to target
    *
    * @param array $origin ('id'=>'colony ID','name'=>'xxx123','x'=>111,'y'=>222)
    * @param array $target ('id'=>'colony ID','name'=>'xxx123','x'=>122,'y'=>233)
    * @param array $troops ('xxx'=>0, 'yyy' => 20, ...)
    * @param string $mode ('attack' | 'spy')
    * @return void
    */
   public function attack(Array $origin, Array $target, Array $troops, $mode = 'attack', $simulate = FALSE)
   {
      // URL Barracks with a target
      $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain'].'/game.php?village='.$origin['id'].'&'.self::$_instance->_config['barracks'].'&target='.$target['id'];
      // GET barracks form
      if ($res = $this->_cURL($url)) {
         if (!$simulate) sleep(rand(5,9));
         $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain'].'/game.php?village='.$origin['id'].'&'.self::$_instance->_config['barracks_send'];
         // change zero values to null value
         $troops_temp = array();
         foreach ($troops as $k => $v) {
            if ((int)$v == 0) {
               $troops_temp["{$k}"] = null;
            } else {
               $troops_temp["{$k}"] = $v;
            }
         }
         $data = $troops_temp;
         $data['send_x'] = $target['x'];
         $data['send_y'] = $target['y'];
         $data['attack'] = null;
         $data['espy'] = null;
         if ($mode == 'espy') {
            $data['espy'] = self::$_instance->_config['espy'];
         } else {
            $data['attack'] = self::$_instance->_config['attack'];
         }
         $data['support'] = null;
         $data['discharge'] = null;
         // POST confirm attack
         if ($res = $this->_cURL($url, $data)) {
            if (preg_match('/<p class="error">.+<\/p>/', $res, $error)) {
                // Save error in log
                @$this->log('E', "Attack to {$target['name']} ({$target['x']}|{$target['y']}) -> " . strip_tags($error[0]));
                if ($simulate) return strip_tags($error[0]);
             } else {

                // REVISION: Detectar si el ataque es a un aliado y si es así añadir el checkbox
                // if (preg_match_all('/expr/', $res)) {
                //    $data['prevent'] = 'the value input';
                // }
                if ($simulate) return $res;
                sleep(rand(2,3));
                preg_match_all('/p=\w{4}/', $res, $temp);
                $proof = explode('=', $temp[0][3]);
                $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain'].'/game.php?village='.$origin['id'].'&'.self::$_instance->_config['attack_send']."&p={$proof[1]}";
                $data = $troops;
                $data['send_x'] = $target['x'];
                $data['send_y'] = $target['y'];
                $data['attack'] = null;
                $data['espy'] = null;
                if ($mode == 'espy') {
                   $data['espy'] = self::$_instance->_config['espy'];
                } else {
                   $data['attack'] = self::$_instance->_config['attack'];
                }
                $data['transport'] = 'no';

                // POST Sent attack
                if ($res = $this->_cURL($url, $data)) {
                    return true;
                } else {
                    $this->log('C', $res);
                }
             }
         } else {
             $this->log('C', $res);
         }
      } else {
          $this->log('C', $res);
      }
   }

   /** REVISION !!
    * Simulate human interaction to send scheduler attacks
    * @param array $origin ('id'=>'colony ID','name'=>'xxx123','x'=>111,'y'=>222)
    * @param array $target ('x'=>122,'y'=>233)
    * @param array $troops ('xxx'=>0, 'yyy' => 20, ...)
    * @param string $mode ('attack' | 'spy' | 'support')
    * @param boolean $start
    */
   public function schedulerAttack(Array $origin, Array $target, Array $troops, $mode = 'attack', $kata_target = FALSE, $launch = FALSE, $proof = NULL)
   {
       // To prepare iterations
       if (!$launch) {
           // URL Barracks with a target
          $url_target = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain'].'/game.php?village='.$origin['id'].'&'.self::$_instance->_config['barracks'].'&target='.$target['id'];
          if ($res = $this->_cURL($url_target, NULL)) {
              // change zero values to null value
             $troops_temp = array();
             foreach ($troops as $k => $v) {
                if ((int)$v == 0) {
                   $troops_temp["{$k}"] = null;
                } else {
                   $troops_temp["{$k}"] = $v;
                }
             }
             $data = $troops_temp;
             $data['send_x'] = $target['x'];
             $data['send_y'] = $target['y'];
             $data['attack'] = null;
             $data['espy'] = null;
             if ($mode == 'spy') {
                $data['espy'] = self::$_instance->_config['espy'];
             } else {
                $data['attack'] = self::$_instance->_config['attack'];
             }
             if ($kata_target) {
                $data['kata_target'] = $target;
             }
             $data['support'] = null;
             $data['discharge'] = null;
             // POST confirm attack
             $url_send = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain'].'/game.php?village='.$origin['id'].'&'.self::$_instance->_config['barracks_send'];
             if ($res = $this->_cURL($url_send, $data)) {
                /*
                 if (preg_match('/<p class="error">.+<\/p>/', $res, $error)) {
                    return FALSE;
                 }
                 */
                 preg_match_all('/p=\w{4}/', $res, $temp);
                 if (isset($temp[0][3])) {
                    $_proof = explode('=', $temp[0][3]);
                    return $_proof[1];
                 } else {
                    return FALSE;
                 }
             }
          }
      } else {
          // launch attacks
         $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain'].'/game.php?village='.$origin['id'].'&'.self::$_instance->_config['attack_send']."&p={$proof}";
         $data = $troops; // Review troops value zero !!!
         $data['send_x'] = $target['x'];
         $data['send_y'] = $target['y'];
         $data['attack'] = null;
         $data['espy'] = null;
         if ($mode == 'spy') {
             $data['espy'] = self::$_instance->_config['espy'];
         }
         if ($mode == 'attack') {
             $data['attack'] = self::$_instance->_config['attack'];
         }
         if ($kata_target) {
            $data['kata_target'] = $kata_target;
         }
         $data['transport'] = 'no';
         // Create cURL multi handle
         return $this->cURL_Multi_Init($url, $data);
      }
   }

   /** OK!
    * Save log
    *
    * @param string $type (A|C|E|F|S)
    * @param string $message
    * @return $_instance
    */
   public function log($type, $message)
   {
      // One log for day
      $log_file = date('Ymd').'.log';
      $path = ROOT.'logs';
      $logname = "{$path}/{$log_file}";
      if ( !is_dir($path)) {
          mkdir($path);
      }
      if (is_writable($path)) {
          $f = fopen($logname, 'a');
          $_date = date('d/m/Y H:i:s');
          switch(strtoupper($type)) {
             case 'A':
               fwrite($f, "$_date ATTACK: $message\n");
               break;
             case 'C':
               fwrite($f, "$_date CURL_ERROR: $message (empty for login error, banned, account not validated ...)\n");
               break;
             case 'E':
               fwrite($f, "$_date ERROR: $message\n");
               break;
             case 'F':
               fwrite($f, "$_date FLAG: $message\n");
               break;
             case 'R':
               fwrite($f, "$_date RECRUIT: $message\n");
               break;
             case 'S':
               fwrite($f, "$_date ESPY: $message\n");
               break;
             case 'T':
               fwrite($f, "$_date TRADE: $message\n");
               break;
          }
          fclose($f);
          if (!is_writable($logname)) {
            chown($logname, self::$_owner);
            chmod($logname,0777);
          }
      } else {
          echo "<h1>Warning! You don't have permission to write a log file.</h1><h3>Review your permissions</h3>";
          exit();
      }
      return self::$_instance;
   }

   /** OK!
    * Calculate the minium speed
    *
    * @param array $troops
    * @param string $mode Attack mode for espy
    * @return integer $min_speed
    */
   public function speed_troops($troops, $mode)
   {
      if ($mode == 'S') {
         return self::$_instance->_config['espy_speed'];
      } else {
         $min_speed = 0;
         foreach ($troops as $kt => $vt) {
             if ($vt > 0) {
                 foreach(self::$_instance->_config['troops_speed'] as $ks => $vs) {
                     if ($kt === $ks) {
                         if ($vs > $min_speed) {
                             $min_speed = $vs;
                         }
                     } else {
                         continue;
                     }
                 }
             }
         }
         return $min_speed;
      }
   }

   /** OK!
    * Save attacks in attacks.json
    *
    * @param array $from    array('id'=> 123456, 'x' => 456, 'y' => '455')
    * @param array $to      array('id'=> 123457, 'x' => 457, 'y' => '457')
    * @param array $troops  array('farmer' => 0, 'sword' => 0 ...)
    * @param integer $time The unixtime when the attack was launched
    * @param string $mode  Attack mode for espy
    */
   public function save_attack(Array $from, Array $to, Array $troops, $time, $mode = NULL, $id_attack = FALSE)
   {
      // Calculate the speed and the arrival time
      $_speed_troop = $this->speed_troops($troops, $mode);
      $distance = $this->getDistance($from, $to, $_speed_troop);
      $arrival = $time+($distance*2); // comeback in seconds
	  $id = ($id_attack) ? $id_attack : $to['id']; // ID attack
      $f = fopen(ROOT.self::$_paths['farm_attacks'], 'w');
      if (count(self::$_instance->_attacks) > 0) {
          if (array_key_exists($id, self::$_instance->_attacks)) {
              // update attack if exists
              self::$_instance->_attacks[$id]['timestamp'] = date('d/m/Y H:i:s', $arrival);
              self::$_instance->_attacks[$id]['unixtime'] = $arrival;
          } else {
              // add attack
              self::$_instance->_attacks[$id] = array(
                  'timestamp' => date('d/m/Y H:i:s', $arrival),
                  'unixtime' => $arrival
              );
          }
          fwrite($f, json_encode(self::$_instance->_attacks));
      } else {
          // First time
          $attacks = array(
            "{$id}" => array(
              'timestamp' => date('d/m/Y H:i:s', $arrival),
              'unixtime' => $arrival
            )
          );
          fwrite($f, json_encode($attacks));
          // Update $_attacks property in the first time
          self::$_instance->_attacks = $attacks;
      }
      fclose($f);
   }

   /** OK!
    * Parsing html flag
    * @param string $url
    * @return mixed [boolean|array]
    */
   public function parser_attack($url)
   {
       $html = $this->_cURL($url);
       if ($html) {
           $count = 0;
           $attacks = array();
           $dom = new DOMDocument();
           if ($dom->loadHTML($html)) {
            $servertime = $dom->getElementById('servertime');
            $table = $dom->getElementsByTagName('table');
            foreach($table as $pos => $rows) {
                if ($pos == 15) { // with resume menu
                //if ($pos == 14) { // whithout resume alchemist (buy with 2 or more villages)
                    $trs = $rows->getElementsByTagName('tr');
                    foreach($trs as $i => $tr) {
                         //var_dump($tr);
                         // Paginator
                         if ($i==0) {
                            $tds = $tr->getElementsByTagName('td');
                               foreach($tds as $k => $td) {
                                   foreach($td->childNodes as $node) {
                                       if(!($node instanceof \DomText)) $count++;
                                   }
                            }
                         }
                        if ($i > 0 ){
                            $tds = $tr->getElementsByTagName('td');
                            foreach($tds as $k => $td) {
                                if ($k > 0) {
                                    $user = array();
                                    // Attack TO (array)
                                    if ($k == 1) {
                                        $links = $td->getElementsByTagName('a');
                                        // [0] player name, [1] colony name
                                        foreach($links as $link) {
                                            $user[] = trim($link->nodeValue);
                                        }
                                        preg_match_all('/\(\d{3}\|\d{3}\)/', $td->nodeValue, $to); // GET coords
                                        $_to = explode('|', str_replace(array('(', ')'), array('', ''), $to[0][count($to[0])-1]));
                                        $attacks[$i]['to'] = array ('player' => $user[0], 'colony' => $user[1], 'x' => $_to[0], 'y' => $_to[1]);
                                    }
                                    // Attack FROM (array)
                                    if ($k == 2) {
                                        $links = $td->getElementsByTagName('a');
                                        // [0] player name, [1] alliance tag, [2] colony name
                                        foreach($links as $link) {
                                            $user[] = trim($link->nodeValue);
                                        }
                                        preg_match_all('/\(\d{3}\|\d{3}\)/', $td->nodeValue, $from); // GET coords
                                        $_from = explode('|', str_replace(array('(', ')'), array('', ''), $from[0][count($from[0])-1]));
                                        $attacks[$i]['from'] = array ('player' => $user[0], 'ally' => $user[1], 'colony' => $user[2], 'x' => $_from[0], 'y' => $_from[1]);
                                    }
                                    // arrived at
                                    if ($k == 3) {
                                        $attacks[$i]['when'] = trim($td->nodeValue);
                                    }
                                    // countdown
                                    if ($k == 4) {
                                        $attacks[$i]['countdown'] = trim($td->nodeValue);
                                    }
                                    // unixtime
                                    $spans = $td->getElementsByTagName('span');
                                    foreach($spans as $span) {
                                        $attacks[$i]['unixtime'] = $span->getAttribute('time');
                                    }
                                } else {
                                    continue;
                                }
                            }
                        } else {
                            continue;
                        }
                    }
                } else {
                    continue;
                }
            }
            sort($attacks);
            $attacks['pages'] = $count;
            $attacks['servertime'] = $servertime->getAttribute('time');
            return $attacks;
        } else {
            return FALSE;
        }
       } else {
           return FALSE;
       }
   }

   /** OK!
    * Save the last attacks on flag
    * @param Array $attacks
    * @return void
    */
   public function save_flagattacks(Array $attacks, $flag)
   {
      // One log for day
      $filename = ROOT.self::$_paths['flag_attacks'];
      $flagfile = ROOT.self::$_paths['flag']; // File to save snobs to use autodefense

      if ( !is_dir(ROOT.self::$_paths['path'])) {
          mkdir(ROOT.self::$_paths['path']);
      }
      if (is_writable($filename)) {
          // json attacks
          $json = array();
          $f = fopen($filename, 'w');
         foreach($attacks as $attack) {
             $json[] = $attack;
         }
         fwrite($f, json_encode($json));
         fclose($f);

         // write flag
         if (isset($flag) && is_writable($flagfile)) {
            $f = fopen($flagfile, 'w');
           fwrite($f, json_encode($flag));
           fclose($f);
         } else {
          echo "<h1>Warning! You don't have permission to write flag json file.</h1><h3>Review your permissions</h3>";
          exit();
         }

      } else {
          echo "<h1>Warning! You don't have permission to write a json file.</h1><h3>Review your permissions</h3>";
         exit();
      }
   }

   /** OK!
    * Autoleveler building
    * @param Array $building
    * @return boolean
    */
   public function autoLeveler($building) {
        $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain'].'/game.php?village='.$building['id'].self::$_instance->_config['main'];
        if ($html = $this->_cURL($url)) {
            //sleep(rand(4,7));
            $dom = new DOMDocument();
            if (@$dom->loadHTML($html)) {
                $stone = (int)str_replace('.', '', $dom->getElementById('stone')->nodeValue);
                $wood = (int)str_replace('.', '', $dom->getElementById('wood')->nodeValue);
                $iron = (int)str_replace('.', '', $dom->getElementById('iron')->nodeValue);
                $_data = array(
                    'stone' => $stone,
                    'wood' => $wood,
                    'iron' => $iron
                );
                // Add building to queue if there're materials to build
                if ($stone > $building['stone'] && $wood > $building['wood'] && $iron > $building['iron']) {
                    $classname = 'box';
                    // Select all buldings divs
                    $finder = new DomXPath($dom);
                    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
                    $loc = $hash = '';
                    // Get one of children for for hash value
                    foreach ($nodes as $pos => $node) {
                        $_regex1 = '/'.strtolower(self::$_instance->_config['autoleveler'][0]).'/';
                        $_regex2 = '/'.strtolower(self::$_instance->_config['autoleveler'][1]).'/';
                        if (preg_match($_regex1, strtolower($node->nodeValue)) || preg_match($_regex2, strtolower($node->nodeValue))) {
                            $loc = $node;
                            $linknode = $loc->getElementsByTagName('a');
                            foreach($linknode as $node) {
                                if (preg_match('/p=\w{4}/', $node->getAttribute('href'), $hash)) {
                                    $hash = explode('=', $hash[0]);
                                    $loc = true;
                                    break 2;
                                } else {
                                    $loc = false;
                                }
                            }
                        }
                    }
                    if ($loc) { // Check if empty
                        $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain']."/game.php?village={$building['id']}&".self::$_instance->_config['main_build']."&p={$hash[1]}&build={$building['name']}";
                        $this->_cURL($url);
                        $_data['stone'] = $stone-$building['stone'];
                        $_data['wood'] = $stone-$building['wood'];
                        $_data['iron'] = $stone-$building['iron'];
                        $_data['settlers'] = $this->_settlers($dom);
                        return $_data;
                    } else {
                        $_data['error'] = true;
                        return $_data;
                    }
                } else {
                    $_data['error'] = true;
                    return $_data;
                }
            }
       }
   }

   /** REVISION!!!
     * Tracking player info
    * @param string $url
    * @param boolean $only_villages for return only villages
    * @return mixed [booleana|array]
    */
   public function tracking($url, $show = TRUE, $only_villages = FALSE, $own = FALSE)
   {
        $html = $this->_cURL($url);
        //if ($show) echo($html);
        if ($html) {
            $dom = new DOMDocument();
            if (@$dom->loadHTML($html)) {
                $player_info = array();
                $table = $dom->getElementsByTagName('table');
                foreach($table as $pos => $rows) {
                    if (!$only_villages && $pos == 15) {
                        // PLAYER INFO TABLE
                        $trs = $rows->getElementsByTagName('tr');
                        foreach($trs as $i => $tr) {
                            // Alliance
                            if ($i == 1) {
                                $alliance = explode(':', trim($tr->nodeValue));
                                $player_info['alliance'] = isset($alliance[1]) ? trim($alliance[1]) : '';
                            }
                            // Total points
                            if ($i == 2) {
                                $points = explode(':', trim($tr->nodeValue));
                                $player_info['total_points'] = isset($points[1]) ? str_replace('.', '', trim($points[1])) : 0;
                            }
                            // Position
                            if ($i == 3) {
                                $position = explode(':', trim($tr->nodeValue));
                                $player_info['position'] = isset($position[1]) ? str_replace('.', '', trim($position[1])) : 0;
                            }
                            // Total villages
                            if ($i == 4) {
                                $villages = explode(':', trim($tr->nodeValue));
                                $player_info['total_villages'] = isset($villages[1]) ? str_replace('.', '', trim($villages[1])) : 0;
                            }
                            // Average points
                            if ($i == 5) {
                                $average = explode(':', trim($tr->nodeValue));
                                $player_info['average_points'] = isset($average[1]) ? str_replace('.', '', trim($average[1])) : 0;
                            }
                            // Combats
                            if ($i == 6) {
                                $combats = explode(':', trim($tr->nodeValue));
                                $player_info['combats'] = isset($combats[1]) ? str_replace('.', '', trim($combats[1])) : 0;
                            }
                            // Defeat opponents
                            if ($i == 7) {
                                $opponents = explode(':', trim($tr->nodeValue));
                                $player_info['defeat_opponents'] = isset($opponents[1]) ? str_replace('.', '', trim($opponents[1])) : 0;
                            }
                        }
                    } else if ($pos == 17 && !$own) {
                        // VILLAGES TABLE
                        $player_info['villages'] = array();
                        $trs = $rows->getElementsByTagName('tr');
                        foreach($trs as $i => $tr) {
                            if ($i > 0) {
                                $villages = array();
                                $tds = $tr->getElementsByTagName('td');
                                foreach($tds as $j => $td) {
                                    // Village ID and name
                                    if ($j == 0) {
                                        // Village ID
                                        $_href = $td->getElementsByTagName('a');
                                        foreach ($_href as $k => $_href) {
                                            if ($k == 0) {
                                                $__href = $_href->getAttribute('href');
                                                if (preg_match('/id=\d+/', $__href, $__id)) {
                                                    $villages['id'] = (int)str_replace('id=', '', $__id[0]);
                                                }
                                                break;
                                            }
                                        }
                                        // village name
                                        $villages['name'] = trim($td->nodeValue);
                                    }
                                    // Coordenates
                                    if ($j == 1) {
                                        $_coords = explode('|', trim($td->nodeValue));
                                        $villages['x'] = (int)$_coords[0];
                                        $villages['y'] = (int)$_coords[1];
                                    }
                                    // Village points
                                    if ($j == 2) {
                                        $villages['points'] = (int)str_replace('.', '', trim($td->nodeValue));
                                    }
                                }

                                if (!empty($villages)) {
                                  if (isset($villages['x'])) {
                                    array_push($player_info['villages'], $villages);
                                  }
                                }
                            } else {
                                continue;
                            }
                        }
                    }
                }
                if ($own) {
					           $_rules = $this->getBuildingsRules();
                    $villages = array();
                    // Village data
                    $villages['settlement'] = trim($dom->getElementById('settlement')->nodeValue);
                    // materials
                    $villages['materials'] = array(
                        'stone' => (int)str_replace('.', '', $dom->getElementById('stone')->nodeValue),
                        'wood' => (int)str_replace('.', '', $dom->getElementById('wood')->nodeValue),
                        'iron' => (int)str_replace('.', '', $dom->getElementById('iron')->nodeValue)
                    );
          					// bulding levels levels
          					$villages['buildings'] = Array();
          					$_buildings = $dom->getElementById('village_view');
          					foreach($_buildings->getElementsByTagName('label') as $k => $building) {
          						$_class = explode(' ', $building->getAttribute('class'));
          						$_building = str_replace('gfx_tip_', '', $_class[1]);
          						$villages['buildings'][$_building] = $building->nodeValue;
          					}
                    // freeSettlers
                    $villages['settlers'] = $this->_settlers($dom);
                }
                return $own ? $villages : $player_info;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /** REVISION!
    * Trade with villages
    * @param Array $trade
    * @return boolean
    */
   public function Trading($trade) {
      $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain']."/game.php?village={$trade['from']}".self::$_instance->_config['market'];

      $data = Array();
      $data['send_res2'] = $trade['stone']; // stone
      $data['send_res1'] = $trade['wood']; // wood
      $data['send_res3'] = $trade['iron']; // iron
      $data['send_x'] = $trade['x'];
      $data['send_y'] = $trade['y'];
      $data['village_name'] = "{$trade['x']}|{$trade['y']}";

      $res = $this->_cURL($url, $data);
      if ($res) {
         if (preg_match('/<p class="error">.+<\/p>/', $res, $error)) {
            return FALSE;
         }
         if (preg_match_all('/p=\w{4}/', $res, $hash)) {
            $hash = explode('=', $hash[0][3]);
            $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain']."/game.php?village={$trade['from']}".self::$_instance->_config['market_send']."&p={$hash[1]}";
            unset($data['village_name']);
            // Send trade
            $res = $this->_cURL($url, $data);
            return $res ? TRUE : FALSE;
         } else {
            return FALSE;
         }
      } else {
         return FALSE;
      }
   }
   /**
    * Save village map if doesn't exists
    * @param integer village_id
    * @param string url
    * @return boolean
    */
   public function villageMap($village_id, $url) {
      // Create directory if nos exists
      if (!is_dir(ROOT.self::$_paths['village_map'])) {
         mkdir(ROOT.self::$_paths['village_map'], 0777);
      }
      $file = ROOT.self::$_paths['village_map']."/{$village_id}.png";

      // Remove previous map
      if (is_file($file)) {
        unlink($file);
      }
      $referer = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain']."/game.php?s=map&village={$village_id}";
      $_image = $this->_cURL($url, null, true, true, $referer);
      // Save IMage
      if ($f = fopen($file, 'w')) {
         fwrite($f, $_image);
         fclose($f);
         chmod($file, 0777);
         return TRUE;
      } else {
         return FALSE;
      }
   }
   /**
    * Get data for recruit troops
    * @param  integer $village_id Village ID
    * @return mixed [Array/boolean]
    */
   public function getRecruitData($village_id) {
      $data = Array();
      $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain']."/game.php?village={$village_id}&".self::$_instance->_config['barracks_troops'];
      $html = $this->_cURL($url, null, true);
      $dom = new DOMDocument();
      if(@$dom->loadHTML($html)) {
         $finder = new DomXPath($dom);
         $_name = 'kingsage';
         $_form = $finder->query("//*[contains(concat(' ', normalize-space(@name), ' '), ' $_name ')]");
         $_action = $_form->item(0)->getAttribute('action');

         if (preg_match("/p=\w{4}/", $_action, $_match)) {
            $_proof = str_replace('p=','', $_match[0]);
            // Proof
            $data['proof'] = $_proof;
            // Materials
            $data['materials'] = array(
            'stone' => (int)str_replace('.', '', $dom->getElementById('stone')->nodeValue),
            'wood' => (int)str_replace('.', '', $dom->getElementById('wood')->nodeValue),
            'iron' => (int)str_replace('.', '', $dom->getElementById('iron')->nodeValue)
            );
            // Available settlers
            $data['settlers'] = $this->_settlers($dom);
            return $data;
         } else {
            return false;
         }
      } else {
         return false;
      }
   }
   public function Recruit($village_id, $data, $proof) {
      $referer = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain']."/game.php?village={$village_id}&".self::$_instance->_config['barracks_troops'];
      $url = self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain']."/game.php?village={$village_id}&".self::$_instance->_config['barracks_recruit']."&p=".$proof;
      $res = $this->_cURL($url, $data, true, false, $referer);
      if ($res) {
         return true;
      }
      return false;
   }

   public function getRankingPlayers ($url) {
    $html = $this->_cURL($url);
    $dom = new DOMDocument();
    if(@$dom->loadHTML($html)) {
       $finder = new DomXPath($dom);
       $_name = 'borderlist';
       $_tables = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $_name ')]");

       $_players = Array();

       foreach ($_tables as $key => $_table) {
        if ($key === 2) {
          $_trs = $_table->getElementsByTagName('tr');
          foreach ($_trs as $i => $_tr) {
            $_tds = $_tr->getElementsByTagName('td');
            $_player = Array();
            foreach ($_tds as $pos => $_td) {
              if ($pos === 0) {
                $_player["position"] = $_td->textContent;
              }
              if ($pos === 1) {
                if (preg_match('/[a-zA-Z0-9_\!\.:, ]+/',$_td->textContent, $match)) {
                  $_player["name"] = trim($match[0]);
                }
              }
              if ($pos === 2) {
                $_player["alliance"] = $_td->textContent;
              }
              if ($pos === 3) {
                $_points = trim($_td->textContent);
                $_player["points"] = (int)$_points;
              }
              if ($pos === 4) {
                $_player["villages"] = $_td->textContent;
              }
            }
            if (!empty($_player)) {
              $_players[$i] = $_player;
            }
          }
        }
       }
       return $_players;
     }
   }

   public function getGame ($url, $data, $wait, $image) {
      return $this->_cURL($url, $data, $wait, $image);
   }

   public function getMessages ($url) {
    return $this->_cURL($url);
   }

   public function sendMessage ($url, $data) {
    if (!isset($data)) {
      // GET proof;
      $html = $this->_cURL($url);
      $dom = new DOMDocument();
      if(@$dom->loadHTML($html)) {
        $forms = $dom->getElementsByTagName("form"); // DOMNodeList Object

        foreach ($forms as $key => $form) {
            if (preg_match("/p=\w{4}/", $form->getAttribute('action'), $_match)) {
              $_proof = str_replace('p=','', $_match[0]);
              return $_proof;
            }
         }
      }
      return false;
    } else {
      // SEND Message
      return $this->_cURL($url, $data);
    }
   }

   public function register ($url) {
    return $this->_cURL($url);
   }

   public function getFlagSnobJson ($url) {
    $opts = array (
      'http' => array (
          'method' => 'GET',
          'header'=> "Content-type: application/json"
          )
      );
    $context = stream_context_create($opts); 
    $snobs_json = file_get_contents($url, false, $context);
    return json_decode($snobs_json, true);
   }

   /**
    * Create a handle for multiple cURL
    * @param string $url
    * @param array $data (POST data)
    * @return resource $curl
    */
   public function cURL_Multi_Init($url, $data) {
       // curl init
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_COOKIE, self::$_instance->_config['cookie']);
      curl_setopt($curl, CURLOPT_COOKIEFILE, ROOT.self::$_instance->_config['cookie_file']); // Read cookies
      curl_setopt($curl, CURLOPT_COOKIEJAR, ROOT.self::$_instance->_config['cookie_file']); // Save cookies in a file when curl close
      curl_setopt($curl, CURLOPT_TIMEOUT, 30);

      curl_setopt($curl, CURLOPT_USERAGENT, self::$_instance->_config['useragent']); // Simulating user browse
      curl_setopt($curl, CURLOPT_HEADER, TRUE); // display the infromation for the header
      curl_setopt($curl, CURLOPT_HTTPHEADER, self::$_instance->_config['headers']); // array('Content-type: text/plain', 'Content-length: 100')

      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // get the information and return the filestream
          if (self::$_instance->_config['gzip']) {
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate'); // Decoding response
          }
          curl_setopt($curl, CURLOPT_URL, $url); // the access url
       if ( !is_null($data) && !empty($data)) {
         curl_setopt($curl, CURLOPT_POST, TRUE); // Send a post request
         curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      } else {
         curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
      }
       return $curl; // Return curl resource
   }

   /**
    * Execute and close a multi cURL handles
    * @param array $multi (handles)
    * @return array $res (handle info)
    */
   public function cURL_Multi_Exec($multi) {
       $res = array();
       $mh = curl_multi_init();
       foreach($multi as $handle) {
           curl_multi_add_handle($mh,$handle);
       }
       // Execute
       $running = NULL;
       do {
           curl_multi_exec($mh, $running);
       } while($running > 0);
        // Close
        foreach($multi as $id => $handle) {
            $res[$id] = @curl_multi_info_read($handle);
            curl_multi_remove_handle($mh, $handle);
        }
        curl_multi_close($mh);
        return $res;
   }

   private function _settlers(&$dom) {
       // freeSettlers
       $classname = 'freeSettlers';
       // Select all buldings divs
       $finder = new DomXPath($dom);
       $_settlers = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
       foreach ($_settlers as $settlers) {
           if (!empty($settlers->textContent)) {
               return (int)str_replace('.', '', trim($settlers->textContent));
           }
       }
   }

   /** OK!
    * Execute a cURL conection
    *
    * @param string $url
    * @param array $data
    * @return $_instance
    */
   private function _cURL($url, $data = null, $wait = TRUE, $image = FALSE, $referer = FALSE) {
      // curl init
      $curl = curl_init(); // start CURL conversation
      curl_setopt($curl, CURLOPT_URL, $url); // the access url
      curl_setopt($curl, CURLOPT_COOKIE, self::$_instance->_config['cookie']);
      curl_setopt($curl, CURLOPT_COOKIEFILE, ROOT.self::$_instance->_config['cookie_file']); // Read cookies
      curl_setopt($curl, CURLOPT_COOKIEJAR, ROOT.self::$_instance->_config['cookie_file']); // Save cookies in a file when curl close
      curl_setopt($curl, CURLOPT_TIMEOUT, 30);

      curl_setopt($curl, CURLOPT_USERAGENT, self::$_instance->_config['useragent']); // Simulating web browser
      curl_setopt($curl, CURLOPT_HEADER, ($image) ? 0 : 1); // display the infromation for the header
      curl_setopt($curl, CURLOPT_HTTPHEADER, self::$_instance->_config['headers']); // array('Content-type: text/plain', 'Content-length: 100')
      if ($image) {
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
      }
      if ($referer) {
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, $referer);
      }

      curl_setopt($curl, CURLOPT_RETURNTRANSFER, ($wait) ? 1 : 0); // get the information and return the filestream
      if (self::$_instance->_config['gzip']) {
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate'); // Decoding response
      }
      if ( !is_null($data) && !empty($data)) {
        // Parser array values to string conversion
        $data_parsed = array();
        foreach($data as $key => $value) {
          if (is_array($value)) {
            for($i = 0; $i < count($value); $i++){
              $data_parsed["{$key}[{$i}]"] = $value[$i];
            }
          } else {
            $data_parsed[$key] = $value;
          }
        }
        // End parser

        curl_setopt($curl, CURLOPT_POST, TRUE); // Send a post request
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_parsed);
      } else {
         curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
      }
      $res = curl_exec($curl); // execute operation

      if (curl_errno($curl)) {
          $this->log('E', curl_error($curl));
          return false;
      } else {
         $info = curl_getinfo($curl);
         curl_close($curl); // close CURL

         // Permanent redirection when session is used on another device/browser
         if ($info['http_code'] == '302')
         {
            // check location error
            if ($info['redirect_url'] == self::$_instance->_config['protocol'].'://'.self::$_instance->_config['server'].'.'.self::$_instance->_config['domain'].'/error.php?e=294') {
               if ($wait) sleep('3'); // wait to callback
               return $this->_cURL($url, $data);
            }
         }
         return $res;
      }
   }
} /** END automate Class */
?>
