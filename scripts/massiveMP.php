<?php
$start = microtime(true);
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).'/automate.class.php'); // parent

/* @TODO
 * Massive MP
 */
$paths = Automate::factory()->getPaths();
$config = Automate::factory()->getConfig();
$_urlranking = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?s=ranking&m=player&order=&start=";
$_urlmessage = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?s=messages&m=new";
$_urlsend = "{$config['protocol']}://{$config['server']}.{$config['domain']}/game.php?s=messages&m=new&a=messageSend&p=";

$page = 0;
$_start = 0;
$_limit = 20;
$flag = true;

$subject = "Feliz día de Sant Jordi";
$message = "Hola comunidad,\n\nQueremos felicitar a todos los Jordi y Jorge en este día tan señalado para algunos.\n";
$message .= "Desde soporte hemos estado trabajando en unas guías y herramientas que creemos que os ayudarán, y hoy las queremos compartir con todos vosotros:\n\n";
$message .= "Guía de uso y funcionalidades: [URL=http://www.mediafire.com/view/1v2sp362bskn2lt/Guía_Kingsage_Automate.pdf]http://www.mediafire.com/view/1v2sp362bskn2lt/Guía_Kingsage_Automate.pdf[/URL]\n";
$message .= "Guía de instalación: [URL=http://www.mediafire.com/view/tiss2cqb3ntenbi/Guía_de_instalación.pdf]http://www.mediafire.com/view/tiss2cqb3ntenbi/Guía_de_instalación.pdf[/URL]\n";
$message .= "La herramienta: [URL=http://www.mediafire.com/download/893wq3vljmtn3hw/automate-kingsage.zip]http://www.mediafire.com/download/893wq3vljmtn3hw/automate-kingsage.zip[/URL]\n\n";
$message .= "Si tienen alguna duda al respecto pueden contactar con [player]Soporte[/player], o bien, en el foro externo.\n\nSaludos a todos,\nSoporte.";

while ($flag) {
   $players = @Automate::factory()->getRankingPlayers("{$_urlranking}{$page}");

   if (!empty($players) || count($players) > 0) {

      for ($i=0; $i<count($players); $i++) {
         // Send Messages to exists players and points over 0
         if (isset($players[$i]['name']) && $players[$i]['points'] > 0) {
            $proof = @Automate::factory()->sendMessage("{$_urlmessage}");
            if ($proof) {
               $data = Array();

               $data["msg_to"] = $players[$i]['name'];
               $data["msg_subject"] = $subject;
               $data["msg_text"] = $message;
               if (@Automate::factory()->sendMessage("{$_urlsend}{$proof}", $data)) {
                  echo "MP sended to: {$players[$i]['name']}<br>";
                  usleep(125000); // 250ms
               }
            }
         }
      }

      // Next page
      $_start++;
      $page = $_start * $_limit;
      usleep(125000); // 250ms

   } else {
      // Exist if empty playyers
      $flag = false;
   }
}

$end = microtime(true);
$execution_time = round($end-$start, 4);
echo "$execution_time seconds";
if (isset($_GET['start'])) {
	echo "<br><br>";
	echo "<a href='{$config['localhost']}'>Go to index</a>";
}
?>
