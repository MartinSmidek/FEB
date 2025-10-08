<?php
# Aplikace FEB pro Komunitu blahoslavenství
# (c) 2017-2025 Martin Šmídek <martin@smidek.eu>
  
//echo("Stranky http://www.evangelizacnibunky.cz jsou docasne mimo provoz, "
//    . "<br>pripravujeme jejich novou verzi, "
//    . "<br>budou v provozu behem kratke doby. "
//    . "<br>Dekujeme za pochopeni."
//    . "<br><br><br><br>");
//exit();

$ezer_version= isset($_GET['ezer']) ? $_GET['ezer'] : '3.3'; 
$_GET['pdo']= 2; 
$_GET['touch']= 0; // nezavede jquery.touchSwipe.min.js => filtry v browse jdou upravit myší

$cms= 'man';
$index= "index.php";

// servery a jejich cesty
$deep_root= "../files/feb";
require_once("$deep_root/feb.dbs.php");

# ------------------------------------------ init

$microtime_start= microtime();
if ( !isset($_SESSION) ) session_start();
$_SESSION['web']['index']= $index;
// nastavení zobrazení PHP-chyb klientem při &err=1
if ( isset($_GET['err']) && $_GET['err'] ) {
  error_reporting(E_ALL ^ E_NOTICE);
  ini_set('display_errors', 'On');
}
else 
  error_reporting(0);

const EZER_PDO_PORT= 2;
require_once("ezer$ezer_version/pdo.inc.php");
require_once("ezer$ezer_version/server/ezer_pdo.php");
require_once("feb/web_fce.php");
require_once("feb/mini.php");
require_once("ezer$ezer_version/server/ezer_cms3.php");
//require_once("feb/feb.par.php");

# ------------------------------------------ ajax
//if ( isset($_GET['mail']) && $_GET['mail']=='me' ) {
//  $TEST= (object)array('cms'=> 1,'cmd'=>'prihlaska_mail','mail'=>"martin@smidek.eu");
//}
if ( isset($_GET['mapa']) ) {
  $TEST= (object)array('cmd'=>'mapa');
}
if ( isset($TEST) || count($_POST) ) {
  connect();
  $z= $TEST ? $TEST : array2object($_POST);
  if ( $z->cms ) {
    $ok= cms_server($z);
  }
  else {
    $ok= ask_server($z);
  }
  header('Content-type: application/json; charset=UTF-8');
  $yjson= json_encode($z);
  echo $yjson;
  exit;
}

$fe_level= isset($_SESSION['web']['fe_level']) ? $_SESSION['web']['fe_level'] : 0;
//if ( $fe_level && ($fe_level & 1) ) {
//  chdir('man');
//  $fe_user= $be_user= $_SESSION['web']['fe_user'];
//  require_once("man/man.php"); 
//}
//else {
//  require_once("man/2mini.php");
  # ------------------------------------------ web
  global $ezer_path_root, $GET_rok;

  $http= isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) ? $_SERVER["HTTP_X_FORWARDED_PROTO"] : 'http';
  $href= "$http://".$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].
    $_SERVER['SCRIPT_NAME'].'?page=';
  $path= isset($_GET['page']) ? explode('!',$_GET['page']) : array('home');
//  $fe_user= isset($_SESSION['web']['fe_user']) ? $_SESSION['web']['fe_user'] : 0;
//  $be_user= 0;
//  $fe_host= 0;
//  $fe_user_display= isset($_GET['login']) ? 'block' : 'none';
//  $ezer_local= preg_match('/^\w+\.bean/',$_SERVER["SERVER_NAME"]);

  // pamatování GET
//  $GET_rok= isset($_GET['rok']) ? $_GET['rok'] : '';

  // absolutní cesta
//  $ezer_path_root= $_SESSION['web']['path']= array(
//      "C:/Ezer/beans/feb",
//      "/home/users/gandi/evangelizacnibunky.cz/web"
//  )[$ezer_server];
  global $CMS, $load_ezer;
  $CMS= 0;
  //require_once("man/2template_ch.php");
  //require_once("man/2mini.php");
  read_menu();
  $path= isset($_GET['page']) ? explode('!',$_GET['page']) : array('home');
  $elem= eval_menu($path);
  $html= eval_elem($elem);
  $full_page= isset($_GET['header']) ? $_GET['header'] : 1;
  show_page($html,$full_page);
  exit;
//}
