<?php # (c) 2017 Martin Smidek <martin@smidek.eu>

  // rozlišení lokální a ostré verze
  $ezer_local= preg_match('/^\w+\.bean$/',$_SERVER["SERVER_NAME"])?1:0;

  require_once("feb/feb.par.php");

  $app_php=   array('feb/feb_fce.php','feb/web_fce.php',"ezer{$EZER->version}/server/ezer_cms3.php");
  
  // je to standardní aplikace se startem v kořenu
  require_once("ezer{$EZER->version}/ezer_ajax.php");
?>
