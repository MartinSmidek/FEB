<?php

$cms= 'man';
$ezer_local= $_SERVER['SERVER_NAME']=='feb.bean' ? 1 : 0;
$index= $ezer_local ? "index.php" : "index.php";

# ------------------------------------------ init

$microtime_start= microtime();
if ( !isset($_SESSION) ) session_start();
$_SESSION['web']['index']= $index;
if ( isset($_GET['err']) && $_GET['err'] ) error_reporting(E_ERROR); else error_reporting(0);
ini_set('display_errors', 'On');
require_once("feb/web_fce.php");
require_once("feb/mini.php");
require_once("ezer3/server/ezer_cms3.php");
require_once("feb/feb.par.php");

# ------------------------------------------ ajax
if ( isset($_GET['mail']) && $_GET['mail']=='me' ) {
  $TEST= (object)array('cms'=> 1,'cmd'=>'prihlaska_mail','mail'=>"martin@smidek.eu");
}
if ( isset($TEST) || count($_POST) ) {
  $y= $TEST ? $TEST : array2object($_POST);
  if ( $y->cms ) {
    $ok= cms_server($y);
  }
  else {
    $ok= ask_server($y);
  }
  header('Content-type: application/json; charset=UTF-8');
  $yjson= json_encode($y);
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

  $href= $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].
    $_SERVER['SCRIPT_NAME'].'?page=';
  $path= isset($_GET['page']) ? explode('!',$_GET['page']) : array('home');
//  $fe_user= isset($_SESSION['web']['fe_user']) ? $_SESSION['web']['fe_user'] : 0;
//  $be_user= 0;
//  $fe_host= 0;
//  $fe_user_display= isset($_GET['login']) ? 'block' : 'none';
  $ezer_local= preg_match('/^\w+\.bean/',$_SERVER["SERVER_NAME"]);

  // pamatování GET
//  $GET_rok= isset($_GET['rok']) ? $_GET['rok'] : '';

  // absolutní cesta
  $ezer_path_root= $_SESSION['web']['path']= $_SERVER['DOCUMENT_ROOT'];
  global $CMS;
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
?>
