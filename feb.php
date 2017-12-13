<?php
  // nastavení zobrazení PHP-chyb klientem při &err=1
  if ( isset($_GET['err']) && $_GET['err'] ) {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors', 'On');
  }

  // rozlišení lokální a ostré verze
  $ezer_local= preg_match('/^\w+\.bean$/',$_SERVER["SERVER_NAME"])?1:0;

  $ezer_root= 'feb';
  $skin= 'ck';

  // parametry aplikace
  $app=      'feb';
  $app_name= 'evangelizační buňky';
  $CKEditor= isset($_GET['editor'])  ? $_GET['editor']  : '4.6';
  $dbg=      isset($_GET['dbg'])     ? $_GET['dbg']     : 1;                          /* debugger */
  $gapi=     isset($_GET['gapi'])    ? $_GET['gapi']    : 0; //!($ezer_local || $ezer_ksweb);
  $gmap=     isset($_GET['gmap'])    ? $_GET['gmap']    : 0; //!($ezer_local || $ezer_ksweb);
  //$verze se nastavuje v feb.php.inc

  // inicializace SESSION
  if ( !isset($_SESSION) ) {
    session_unset();
    session_start();
  }
  $_SESSION[$app]['GET']= array();

  if ( $ezer_local ) {
    // lokální cesty 
    $rel_root= "feb.bean:8080";
    $abs_root= "C:/Ezer/beans/feb";
  }
  else {
    $rel_root= "feb.ezer.cz";
    $abs_root= "/home/users/gandi/ezer.cz/web/feb";
  }
  $_SESSION[$app]['abs_root']= $abs_root;
  $_SESSION[$app]['rel_root']= $rel_root;
  $_SESSION[$app]['app_path']= "";
  // kořeny pro LabelDrop
  $path_files_href= "";
  $path_files_s= "$abs_root/";
  $path_files_h= substr($abs_root,0,strrpos($abs_root,'/'))."/files/$app/";

  set_include_path(get_include_path().PATH_SEPARATOR.$abs_root);
  $_POST['root']= $ezer_root;
  require_once("$app.inc.php");
  
  $http= $ezer_local ? 'http' : 'http';
  $cms= "$http://$rel_root/$ezer_root";
  $client= "$http://$rel_root/{$EZER->version}/client";
  $licensed= "$client/licensed";

  // -------------------------------------------------------------------------------------- Ezer 3
  $js= array_merge(
    // ckeditor 
    array("$licensed/ckeditor$CKEditor/ckeditor.js"),
    array("$licensed/pikaday/pikaday.js"),
    array("$licensed/jquery-3.2.1.min.js","$licensed/jquery-noconflict.js","$client/licensed/jquery-ui.min.js"),
    // jádro Ezer3
    array(
      "$client/ezer_app3.js","$client/ezer3.js","$client/ezer_area3.js","$client/ezer_rep3.js",
      "$client/ezer_fdom3.js","$client/ezer_lib3.js","$client/ezer_tree3.js"
    ),
    // rozhodnout zda používat online mapy
    $gmap ? array(
      "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js",
      "https://maps.googleapis.com/maps/api/js?sensor=false") : array(),
    // uživatelské skripty
    array("$cms/feb_fce.js"),
    // end
    array()
  );
  $css= array(
    "$client/ezer3.css.php=skin",
    "feb/feb.css.php",
    "$client/licensed/font-awesome/css/font-awesome.min.css",
    "$client/licensed/pikaday/pikaday.css","$client/licensed/jquery-ui.min.css"
  );
  
  // přihlášení pro ladění FEB
  $options= (object)array(              // přejde do Ezer.options...
    'curr_version' => 0,                // při přihlášení je nahrazeno nejvyšší ezer_kernel.version
    'must_log_in' => 0,
    'path_files_href' => "'$path_files_href'",  // relativní cesta do složky docs/{root}
    'path_files_s' => "'$path_files_s'",        // absolutní cesta do složky docs/{root}
    'path_files_h' => "'$path_files_h'"         // absolutní cesta do složky ../files/{root}
  );
  $kontakt= " V případě zjištění problému nebo <br/>potřeby konzultace mi prosím napište<br/>
        na mail&nbsp;<a href='mailto:{$EZER->options->mail}{$EZER->options->mail_subject}'>{$EZER->options->mail}</a> "
      . ($EZER->options->phone ? "případně zavolejte&nbsp;{$EZER->options->phone} " : '')
      . ($EZER->options->skype ? "nebo použijte Skype&nbsp;<a href='skype:{$EZER->options->skype}?chat'>{$EZER->options->skype}</a>" : '')
      . "<br/>Za spolupráci děkuje <br/>{$EZER->options->author}";

  $pars= (object)array(
    'favicon' => "{$app}_local.png",
    'app_root' => "$rel_root",      // startovní soubory app.php a app.inc.php jsou v kořenu
    'dbg' => $dbg,                                                                    /* debugger */
    'watch_ip' => false,
    'watch_key' => false,
    'autologin' => 'Guest/',
    'contact' => $kontakt,
    'CKEditor' => "{
      version:'$CKEditor',
      EzerHelp2:{
        toolbar:[['PasteFromWord','-','Bold','Italic','TextColor','BGColor',
          '-','JustifyLeft','JustifyCenter','JustifyRight',
          '-','Link','Unlink','HorizontalRule','Image',
          '-','NumberedList','BulletedList',
          '-','Outdent','Indent',
          '-','Source','ShowBlocks','RemoveFormat']],
        extraPlugins:'ezersave,imageresize', removePlugins:'image'
      }
    }"
  );
  root_php3($app,$app_name,'test',$skin,$options,$js,$css,$pars);
?>
