<?php
include_once 'automate.class.php';
$config = Automate::factory()->getConfig();
$numServers = 20;
$time = time();
$server = 1;
$username = '';
$hash = '';

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
   case 'step1':
      if (!empty($_POST)) {
         // Register payer OK
         $url = $config['protocol'].'://'.$config['domain'].'/zjax.php?func=signup&fclass=asys';
         $url .= "&name={$_POST['name']}&mail={$_POST['mail']}&hash={$_POST['password']}&gamerules=1&_={$_POST['unixtime']}";
         $res = Automate::factory()->register($url);

         // Register world TODO
         if ($res) {
            $url = $config['protocol'].'://'.$config['domain'].'/zjax.php?func=loginToGameround&fclass=asys&serverId='.$_POST['server'].'&_='.$_POST['unixtime'];
            Automate::factory()->register($url);
         }

         $server = $_POST['server'];
         $username = $_POST['name'];
         $hash = md5($_POST['password']);
      }
      break;

   case 'step2':

      echo '<pre>';
      print_r($_POST);
      echo '</pre>';

      // Login
      $url = $config['protocol'].'://s'.$_POST['server'].'.'.$config['domain'].'/game.php?village=0';
      unset($_POST['server']);
      $res = Automate::factory()->register($url,$_POST);


      echo '<pre>';
      print_r($url);
      echo '</pre>';

      echo '<pre>';
      print_r($res);
      echo '</pre>';

      break;
}
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta name="viewport" content="width=device-width, user-scalable=yes">
      <meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
      <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
      <title>Player register</title>
      <link media="screen" rel="stylesheet" type="text/css" href="<?=$config['localhost']?>media/css/common.css">
   </head>
   <body>
   <? if (!$action) : ?>
      <!-- STEP 1 -->
      <div>
         <h3>STEP 1, player register</h3>
         <br/>
         <p>
            <form action="<?=$config['localhost']?>register.php?action=step1" method="post">
               <ul>
                  <li>
                     <label>Player name:</label>
                     <input type="text" name="name" class="span10"/>
                  </li>
                  <li>
                     <label>e-Mail:</label>
                     <input type="text" name="mail" class="span10"/>
                  </li>
                  <li>
                     <label>Password:</label>
                     <input type="text" name="password" class="span10"/> (* Show characters to remember the password)
                  </li>
                  <li>
                     <label>Select world:</label>
                     <select type="text" name="server">
                        <? for($i=1; $i<=$numServers; $i++): ?>
                           <? if ($i < $numServers): ?>
                           <option value="<?=$i?>"><?=$i?></option>
                           <? else: ?>
                           <option value="<?=$i?>" selected="selected"><?=$i?></option>
                        <? endif; ?>
                        <? endfor; ?>
                     </select>
                  </li>
               </ul>
               <input type="hidden" name="unixtime" value="<?=$time?>"/>
               <input type="submit" value="Register"/>
            </form>
         </p>
      </div>
   <? endif; ?>
   <? if ($action == "step1") : ?>
      <!-- STEP 2 -->
      <div>
         <h3>STEP 2, login world</h3>
         <br/>
         <p>
            <form action="<?=$config['localhost']?>register.php?action=step2" method="post">
               <ul>
                  <li>
                     <table>
                        <th>
                           <td colspan="2">Select Cardinal direction:</td>
                        </th>
                        <tr>
                           <td>
                              <input value="1" name="param_direction" type="radio"> North west
                           </td>
                           <td>
                              <input value="2" name="param_direction" type="radio"> North east
                           </td>
                        </tr>
                        <tr>
                           <td>
                              <input value="3" name="param_direction" type="radio"> South west
                           </td>
                           <td>
                              <input value="4" name="param_direction" type="radio"> South east
                           </td>
                        </tr>
                     </table>
                  </li>
               </ul>
               <input type="hidden" name="server" value="<?=$server?>"/>
               <input type="submit" value="Select cardinal direction"/>
            </form>
         </p>
         <br/>
         <br/>
         <h2>¡Remember update the cookie file with the name player and the password hash!</h2>
         <br/>
         <span><?=$username?></span><br/>
         <span><?=$hash?></span><br/>
      </div>
   <? endif; ?>
   </body>
</html>
