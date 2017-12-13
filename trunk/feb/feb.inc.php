<?php # (c) 2010 Martin Smidek <martin@smidek.eu>
  error_reporting(E_ALL ^ E_NOTICE);
  global $ezer_root;

  $EZER= (object)array();

  // nastavení zobrazení PHP-chyb klientem při &err=1
  if ( isset($_GET['err']) && $_GET['err'] ) {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors', 'On');
  }

  // nastavení verze jádra
  $EZER->version= "ezer3";

  // test přístupu z jádra
  if ( $_POST['root']!=$ezer_root ) die(); //die("{\"POST\":\"{$_POST['root']}\"}");

  // identifikace ladícího serveru
  $ezer_local= preg_match('/^\w+\.bean$/',$_SERVER["SERVER_NAME"])?1:0;

  // cesty
  $abs_root= $_SESSION[$ezer_root]['abs_root'];
  $rel_root= $_SESSION[$ezer_root]['rel_root'];

  chdir($abs_root);//("../..");

  require_once("{$EZER->version}/server/ae_slib.php");
  require_once("{$EZER->version}/server/ezer_lib3.php");

  // OBECNÉ PARAMETRY

  // parametry s první hodnotou pro server a druhou (případně) pro local
  //   databáze => (,server,username,userpass,kódování,[jméno databáze])
  // databáze 'ezer_system' obsahuje platnou tabulku _user
  // (fyzické jméno databáze může být změněno pátým členem v tabulce $dbs)

  $db= array('feb','feb');
  $dbs= array(
    array(  // ostré
      'feb'           => array(0,'localhost','gandi','radost','utf8'),
      'ezer_system'   => array(0,'localhost','gandi','radost','utf8','feb'),
      'ezer_kernel'   => array(0,'localhost','gandi','radost','utf8','feb')
      ),
    array(  // lokální
      'feb'           => array(0,'localhost','gandi','','utf8'),
      'ezer_db2'      => array(0,'localhost','gandi','','utf8'),
      'ezer_system'   => array(0,'localhost','gandi','','utf8','feb')),
      'ezer_kernel'   => array(0,'localhost','gandi','','utf8','feb')
  );

  $path_root=  array($abs_root,$abs_root);
  // ostatní parametry
  $tracking= '_track';
  $tracked= ',_user,';
  root_inc($db,$dbs,$tracking,$tracked,$path_root,$path_pspad,$ezer_root);

  // PARAMETRY SPECIFICKÉ PRO APLIKACI

  // specifické cesty

  // moduly interpreta zahrnuté do aplikace - budou zpracovány i reference.i_doc pro tabulky kompilátoru
  $ezer_comp_ezer= "app,ezer,area,ezer_report,ezer_fdom1,ezer_fdom2";
  // moduly v Ezerscriptu mimo složku aplikace
  $ezer_ezer= array(
  );
  // standardní moduly v PHP obsažené v $ezer_path_root/ezer2 - vynechané v dokumentaci
  $ezer_php_libr= array(
    'server/session.php',
    'server/ezer_lib3.php',
    'server/reference.php',
    'ezer2_fce.php',
    'server/sys_doc.php',
    'server/ezer2.php'
  );
  // uživatelské i knihovní moduly v PHP obsažené v $ezer_path_root
  $ezer_php= array(
    "{$EZER->version}/ezer2_fce.php",
    "$ezer_root/feb_fce.php"
  );

  // parametrizace $EZER
  $EZER->options->web=    'evangelizacnibunky.cz';
  $EZER->options->author= 'Martin Šmídek';
  $EZER->options->mail=   'martin@smidek.eu'; //'smidek@proglas.cz';
  $EZER->options->phone=  '603 150 565';
  $EZER->options->skype=  'martin_smidek';
  $EZER->activity->skip=  'GAN';      // viz system.php::sys_table
  // vložení modulů
  foreach($ezer_php as $php) {
    require_once("$ezer_path_root/$php");
  }

function show_session() {
  debug($_SESSION);
  return 1;
}
?>
