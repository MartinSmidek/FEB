<?php # (c) 2017 Martin Smidek <martin@smidek.eu>

  # nastavení systému Ans(w)er před voláním AJAX
  #   $answer_db  = logický název hlavní databáze 

  global // import 
    $ezer_root; 
  global // export
    $EZER, $ezer_server;
  
  // vyzvednutí ostatních hodnot ze SESSION
  $ezer_server=  $_SESSION[$ezer_root]['ezer_server'];
  $kernel= "ezer{$_SESSION[$ezer_root]['ezer']}";
  $abs_root=     $_SESSION[$ezer_root]['abs_root'];
  $rel_root=     $_SESSION[$ezer_root]['rel_root'];
  chdir($abs_root);

  // inicializace objektu Ezer 
  require_once("feb/feb.par.php");

  $app_php=   array('feb/feb_fce.php','feb/web_fce.php',"$kernel/server/ezer_cms3.php");
  
  // je to standardní aplikace se startem v kořenu
  require_once("$kernel/ezer_ajax.php");
?>
