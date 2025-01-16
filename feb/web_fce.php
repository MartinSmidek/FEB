<?php
/** seznam oprávnění - jejich relevantní součet je v $fe_level
 *   1  admin     administrátor stránek
 *   2  super     redaktor, který může editovat a mazat příspěvky jiných redaktorů
 *   4  redaktor	 může přidávat příspěvky, editovat a mazat svoje příspěvky
 *  16  testér    testování novinek
 */
define('ADMIN',   1);
define('SUPER',   2);
define('REDAKTOR',4);
define('TESTER', 16);  // přihlášený do CMS
# -------------------------------------------------------------------------------------==> page
function page($ref,$full_page,$level=0) { 
  global $CMS, $fe_user, $be_user;
  global $edit_entity, $edit_id;
  $CMS= 1;
  $be_user= isset($_SESSION['web']['be_user']) ? $_SESSION['web']['be_user'] : 0;
  $fe_user= isset($_SESSION['web']['fe_user']) ? $_SESSION['web']['fe_user'] : 0;
  read_menu($level);
  $path= explode('!',$ref);
  $elem= eval_menu($path);
  $html= eval_elem($elem);
  $page= show_page($html,$full_page);
  return (object)array('html'=>$page,'edit'=>$edit_entity,'id'=>$edit_id);
}
# -------------------------------------------------------------------------------------==> connect
// napojí databázi
function connect() { 
  global $ezer_db, $web_db;
  $web_db= "feb";
  // hostující servery
  $ezer_server= 
    $_SERVER["SERVER_NAME"]=='feb.bean'    ? 0 : (                      // 0:lokální 
    $_SERVER["SERVER_NAME"]=='evangelizacnibunky.cz'     ? 1 : (        // 1:endora
    $_SERVER["SERVER_NAME"]=='www.evangelizacnibunky.cz' ? 1 :  -1));   // 1:endora
  $hst1= 'localhost';
  $nam1= $ezer_server ? 'gandi'    : 'gandi';
  $pas1= $ezer_server ? 'radost'   : 'radost';
  $ezer_db= array( /* lokální */
    $web_db  =>  array(0,$hst1,$nam1,$pas1,'utf8'),
  );
  ezer_connect('feb');
}
# -------------------------------------------------------------------------------------==> def_menu
// načte záznamy z tabulky MENU do kterých uživatel smí vidět
// přidá položku has_subs pokud má hlavní menu submenu
function read_menu($level=0) { 
  global $fe_level, $menu, $CMS;
  // výpočet fe_level podle záznamu v ezer_db2.osoba.web_level a 
  $fe_level= $level + ($CMS ? 16 : 0);
  connect();
  // načtení menu
  $menu= array();
  $mn= mysql_qry("SELECT * FROM menu WHERE wid=2 AND typ>=0 ORDER BY typ,mid_top,rank");
  while ($mn && ($m= mysql_fetch_object($mn))) {
//    if ( $m->typ<0 ) continue; 
    if ( $m->typ==2 && !isset($menu[$m->mid_top]) ) continue; 
    // filtrace chráněných položek
    if ( $m->level<0 && $fe_level && ($fe_level & -$m->level) ) continue;  // -8 nezobrazit pro mrop
    if ( $m->level>0 && !($fe_level & $m->level) ) continue;    //  8 zobrazit jen pro mrop
    if ( $m->level<0 ) $m->level= 0;
    $menu[$m->mid]= $m;
    if ( $m->typ==2 ) $menu[$m->mid_top]->has_subs= true;
  }
}
# -------------------------------------------------------------------------------------==> eval_menu
# path = [ mid, ...]
function eval_menu($path) { 
  global $CMS, $currpage, $curr_idm, $curr_event, $tm_active, $ezer_server, $index;
  global $menu, $topmenu, $mainmenu, $elem, $backref, $top;
  $index= "index.php";
  $prefix= array("http://feb.bean:8080/","https://evangelizacnibunky.cz/")[$ezer_server];
  // pokud má menu M submenu S tak bude prvkem vnořené pole - první ještě patří do mainmenu
  $topmenu= $mainmenu= array();
  $currpage= implode('!',$path);
  $curr_idm= $curr_event= '';
  $top= array_shift($path);
  $main= $main_ref= $main_sub= 0;
  $elem= '';
  $input= '';
  $tm_active= '';
  $n_subs= $n_main= $o_main= 0;  // počet submenu, počet mainmenu, pořadí aktivního mainmenu zprava
  foreach ($menu as $m) {
    $level= '';
    // filtrace chráněných položek
    foreach (array(ADMIN=>'admin',SUPER=>'super',REDAKTOR=>'redaktor',TESTER=>'tester') 
        as $skill=>$class) {
      if ( $m->level & $skill ) {
        $level.= " $class";
      }
    }
    $href= $m->ref;
    $mid= $m->mid_sub ?: $m->mid; 
    $event= $m->mid_sub ? $menu[$m->mid_sub]->event : $m->event;
    $jmp= $CMS 
      ? "onclick=\"go(arguments[0],'page=$href','{$prefix}$href','$mid','$input',0,'$event');\""
        . " title='$mid obsahuje {$m->elem}'"
      : "href='{$prefix}$href'";
    switch ( (int)$m->typ ) {
    case 1:                             // zobrazení main menu
      $n_main++;
      $active= '';
      if ( $m->ref===$top ) {
        $main= $m->mid;
        $main_ref= $m->ref;
        $main_sub= $m->mid_sub;
        $o_main= $n_main;
        $active= $m->has_subs ? ' active subs' : ' active';
        $elem= $m->elem;
        $curr_idm= $main_sub ?: $m->mid;
        $curr_event= $m->event;
        $backref= $CMS 
          ? "onclick=\"go(arguments[0],'page=$href!*','{$prefix}$href!*','$curr_idm','$input',0,'$m->event');\""
//          : "href='{$prefix}$href!*'";
          : "href='{$prefix}$href'";
        $top= array_shift($path);
      }
      $arrow= $m->has_subs ? "<i class='fa fa-chevron-right'></i> " : '';
      $a= "<a $jmp class='jump$level$active'><span>$arrow$m->nazev</span></a>";
      $mainmenu[$m->mid]= $m->has_subs ? array($a) : $a;
      break;
    case 2:                             // zobrazení submenu aktivního mainmenu
      if ( $m->mid_top===$main ) {
        $n_subs++;
        $active= '';
        $href= "$main_ref!$m->ref";
        $href2= "$main_ref/$m->ref";
        if ( $top ? $m->ref===$top : $m->mid===$main_sub ) {
          $curr_idm= $m->mid;
          $curr_event= $m->event;
          $active= ' active';
          $elem= $m->elem;
          $backref= $CMS 
            ? "onclick=\"go(arguments[0],'page=$href!*','{$prefix}$href!*','$curr_idm','$input',0,'$m->event');\""
              . " title='$curr_idm obsahuje {$m->elem}'"
//            : "href='{$prefix}$href!*'";
            : "href='{$prefix}$href2'";
          $top= array_shift($path);
        }
        $jmp= $CMS 
          ? "onclick=\"go(arguments[0],'page=$href','{$prefix}$href','$m->mid','$input',0,'$m->event');\""
            . " title='$curr_idm obsahuje {$m->elem}'"
//          : "href='{$prefix}$href'";
          : "href='{$prefix}$href2'";
        $mainmenu[$main][]= "<a $jmp class='jump$level$active'><span>$m->nazev</span></a>";
      }
      break;
    }
  }
  return $elem;
}
# ------------------------------------------------------------------------------------==> title menu
# vygeneruje title=... oncontextmenu=... podle dodaných parametrů, kde
# title= obsah title
# items= pole zkratek 0123 kde 0=|- 
#   1: p|e|x|m|z		=přidat|editovat|eXclude|move|zobraz(abstrakt nebo clanek) 
#   2: a|c|k|s|o|f|t|i	=akce|článek|kniha|sekce|obrázky|fotky|time-kalendář|invitation-pozvánka
#   3: n|d				=nahoru|dolů  
# id,kid,mid jsou id a x je další parametr
# pokud je definováno pole cmenu (elementem menu) přidá se na začátek
function title_menu($title,$items,$typ,$id=0,$idm=0) {
  global $cmenu;
  $typ_cz= array('clanek'=>'článek','akce'=>'akci','bunka'=>'buňku');
  $typ_cz= $typ_cz[$typ];
  $cm= array();
  // přidej na začátek menu definované elementem menu
  if ( count($cmenu) ) {
    $items= implode(';',$cmenu).";$items";
  }
  $items= explode(';',$items);
  foreach ($items as $item) {
    $c= '';
    if ( '-'==substr($item,0,1) ) { // řádek před item
      $c= '-';
      $item= substr($item,1);
    }
    // případné parametry itemu budou číslovány od 1
    $x= explode(',',$item);
    $item= $x[0];
    switch ($item) {
    // e - editace
    case 'e':  $cm[]= "['{$c}editovat $typ_cz',function(el){ opravit('$typ',$id); }]"; break;
    // p - přidání
    case 'pcn': $cm[]= "['{$c}přidat článek na začátek',function(el){ pridat('clanek',$idm,1);}]"; break;
    case 'pcd': $cm[]= "['{$c}přidat článek na konec',function(el){ pridat('clanek',$idm,0);}]"; break;
      // x - rušení
    case 'xa':  $cm[]= "['{$c}odpojit popis akce',function(el){ odpojit($id,$idm);}]"; break;
    case 'xc':  $cm[]= "['{$c}zrušit článek',function(el){ zrusit($id,$idm);}]"; break;
  // m - posunutí
    case 'md': $cm[]= "['{$c}posunout dolů',function(el){ posunout('$typ',$id,$idm,1);}]"; break;
    case 'mn': $cm[]= "['{$c}posunout nahoru',function(el){ posunout('$typ',$id,$idm,0);}]"; break;
    default: fce_error("'$item' není menu");
    }
  }
  $on= " title='$title' oncontextmenu=\"Ezer.fce.contextmenu([\n"
      .implode(",\n",$cm)
      ."],arguments[0],0,0,'#$typ$id');return false;\"";
  return $on;
}
# -------------------------------------------------------------------------------------==> eval_elem
// desc :: key [ = ids ]
// ids  :: id1 [ / id2 ] , ...    -- id2 je klíč v lokální db pro ladění
function eval_elem($desc) {
  global $CMS, $curr_idm, $curr_event, $ezer_server, $load_ezer;
  global $edit_entity, $edit_id;
  $edit_entity= '';
  $edit_id= 0;
  $elems= explode(';',$desc);
  $html= '';
  $html= $CMS ? "<script>skup_mapka_off();</script>" : '';
  foreach ($elems as $elem) {
    list($typ,$ids)= explode('=',$elem.'=');
    // přemapování ids podle server/localhost
    $id= null;
    if ( $ids ) {
      $id= array();
      foreach (explode(',',$ids) as $id12) {
        list($id_server,$id_local)= explode('/',$id12);
        $id[]= $id_local ? (!$ezer_server ? $id_local : $id_server) : $id_server; 
      }
      $id= implode(',',$id);
    }
    $typ= str_replace(' ','',$typ);

    switch ($typ) {

    case 'hr':   # --------------------------------------------------- . <hr>
      $html.= "<hr>";
      break;

    case 'akce': # --------------------------------------------------- . akce
      $edit_entity= 'akce';
      $edit_id= $id;
      list($text,$prihl,$nazev)= select("web_text,web_prihl,web_menu","akce","id_akce=$id");
      if ( $prihl ) {
        $html.= cms_form_ref("Přihláška na seminář","seminar",$id,$nazev);
      }
      $menu= $CMS 
          ? title_menu("akce $id","e;-md;mn".($curr_event=='join_akce'?';-xa':'')."",'akce',$id,$curr_idm)
          : '';
      $html.= "<div id='akce$id' class='text'$menu>$text</div>";
      break;

    case 'bunka': # -------------------------------------------------- . bunka
      $edit_entity= 'bunka';
      $edit_id= $id;
      $web_text= select("web_text","cell","id_cell=$id");
      $menu= $CMS 
          ? title_menu("buňka $id","e;-md;mn",'bunka',$id,$curr_idm)
          : '';
      $html.= "<div id='bunka$id' class='text'$menu>$web_text</div>";
      break;

    case 'clanek': # ------------------------------------------------- . článek
      $edit_entity= 'clanek';
      $edit_id= $id;
      $web_text= select("web_text","clanek","id_clanek=$id");
      $menu= $CMS 
          ? title_menu("článek $id","e;-md;mn;-xc",'clanek',$id,$curr_idm)
          : '';
      $html.= "<div id='clanek$id' class='text'$menu>$web_text</div>";
      break;

    case 'mapa':    # ------------------------------------------------ . mapa
      $load_ezer= $CMS ? 0 : 1;
      $html.= 
        "<script>skup_mapka();</script>
         <div id='mapa'>MAPA</div>
         <div id='popis'></div>";
      break;

    }
  }
  return $html;
}
# -------------------------------------------------------------------------------------==> show_page
function show_page($html,$full_page=0) {
  global $CMS, $index, $mainmenu, $load_ezer;
  $url= '';
//  $url= "<div>{$_SERVER['REQUEST_URI']} --- ".(isset($_GET['page']) ? "page={$_GET['page']}" : '')."</div>";
  
  // definice do <HEAD>
  
  // jádro Ezer - jen pokud není aktivní CMS
  $script= '';
  $client= "./ezer3.1/client";
  // pokud není CMS nebude uživatel přihlášen - vstup do Ezer je přes _oninit
  $script.= $CMS ? '' : <<<__EOJ
    <script type="text/javascript">
      var Ezer= {};
      Ezer.get= { dbg:'1',err:'1',gmap:'1' };
      Ezer.web= {index:'index.php'};
      Ezer.cms= {form:{}};
      Ezer.fce= {};
      Ezer.str= {};
      Ezer.obj= {};
      Ezer.version= '3.1'; Ezer.root= 'man'; Ezer.app_root= 'man'; 
      Ezer.options= { /* load_ezer=$load_ezer */
        _oninit: 'skup_mapka',
        skin: 'db'
      };
    </script>
__EOJ;

  // gmaps
  $api_key= "AIzaSyC5Npr91tYnEh1fbewmrMyhxyFuGq61I54";
  $api_key= "AIzaSyCUxpFqLlYPHFzwY63mVcmFcFgF4TYfzyQ"; // Google Maps JavaScript API 'EvangeliacniBunky'
  $api_key= "AIzaSyAq3lB8XoGrcpbCKjWr8hJijuDYzWzImXo"; // Google Maps JavaScript API 'answer-test'
  $script.= !$load_ezer ? '' : <<<__EOJ
    <script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=$api_key"></script>
    <script src="/feb/web_fce.js" type="text/javascript" charset="utf-8"></script>
    <script src="$client/ezer_app3.js"  type="text/javascript" charset="utf-8"></script>
    <script src="$client/ezer3.js"      type="text/javascript" charset="utf-8"></script>
    <script src="$client/ezer_lib3.js"  type="text/javascript" charset="utf-8"></script>
__EOJ;

  // vytvoření menu
  $menu= "<ul>";
  foreach ($mainmenu as $m) {
    if ( is_array($m) ) {
      if ( count($m)>1 ) 
        $m[0]= str_replace('fa-chevron-right','fa-chevron-down',$m[0]);
      $menu.= "<li>$m[0]<ul>";
      for ($i= 1; $i<count($m); $i++) {
        $menu.= "<li>$m[$i]</li>";
      }
      $menu.= "</ul></li>";
    }
    else {
      $menu.= "<li>$m</li>";
    }
  }
  $menu.= "</ul>";
  
  // ozdobná hlavička stránky
  $header= !$full_page ? "$url" : <<<__EOD
        <div class="header">
          <img src="/feb/web/logo.png" alt="FEB" class="logo" />
          <h1>farní evangelizační buňky<br/><span>web systému farních evangelizačních buněk v ČR</span></h1>
          <div class="prouzek_bg">
          </div>
          <div class="prouzek">
            Co jsou evangelizační buňky?
            <a href="http://youtu.be/P65dwlaSM_8">přehrát video</a>
          </div>
          <img src="/feb/web/08.jpg" alt="" width="900" height="346"/>
          <!--iframe width="900" height="346" scrolling="no" frameborder="no" src="./feb/web/feb.html"></iframe-->
          <div class="citat">
            <em>"Hlásat evangelium je milostí a vlastním povoláním církve. 
              Církev existuje právě proto, aby hlásala evangelium."</em><br/>
            <span>citace z Evangelii Nuntiandi</span>
          </div>
        </div>
__EOD;

  // kompletace těla stránky
  $body=  <<<__EOD
      <div class="obal">
        $header
        <div class="dole">
          <div class="menu">
            $menu
          </div>
          <div class="obsah">
            $html
          </div>
        </div>
      </div>
__EOD;

  if ( $CMS ) {
    return <<<__EOD
    <div class="body">
      <script>
        Ezer.web= {index:'$index'};
        Ezer.cms= {form:{}};
      </script>
      $body
    </div>
__EOD;
  }
  else {
  // dokončení stránky s minimem jádra Ezer3 potřebným pro ezer_cms3.js
  $kernel= 'ezer3.1';
  $head=  <<<__EOD
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
      <base href="/" />
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
      <meta name="description" content="" />
      <meta name="keywords" content="" />
      <link href="/feb/css/web.css" rel="stylesheet" type="text/css" />
      <link href="/feb/css/edit.css" rel="stylesheet" type="text/css" />
      <link href="/$kernel/client/ezer_cms3.css" rel="stylesheet" type="text/css" />
      <link href="/$kernel/client/licensed/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
      <link rel="shortcut icon" href="/feb/img/feb.png">
      <script src="/feb/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
      <script src="$client/licensed/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
      <script src="/$kernel/client/ezer_cms3.js" type="text/javascript" charset="utf-8"></script>
      $script
      <title>Farní Evangelizační buňky</title>
    </head>
__EOD;
  echo <<<__EOD
      $head
    <body class="body">$url
      $body
    </body>
    </html>
__EOD;
  }
}
/** ==========================================================================================> MENU */
# ------------------------------------------------------------------------------------ menu add_elem
# přidá do menu další element
function menu_add_elem($mid,$table) {
  $elem= select("elem","menu","wid=2 AND mid=$mid");
  $ted= date('j.n.Y H:i');
  query("INSERT INTO $table (web_text) VALUES 
    ('<h1>Nadpis</h1><p>kontextové menu (pravé tlačítko myši) umožní editaci a posunutí 
    ... vytvořeno $ted</p>')");
  $id= mysql_insert_id();
  $elem= "$table=$id".($elem ? ";$elem" : '');
  query("UPDATE menu SET elem='$elem' WHERE wid=2 AND mid=$mid");
  return 1;
}
# ------------------------------------------------------------------------------------ menu add_akce
# přidá do menu $idm element akce=$id, akce musí existovat
function menu_add_akce($id,$idm) {
  $ok= 0;
  $elems= select("elem","menu","mid=$idm");
  // ujisti se o existenci akce
  $akce= select("COUNT(*)",'akce',"id_akce='$id'");
  if ($akce) {
    $elems= "akce=$id" . ($elems ? ";$elems" : '');
    query("UPDATE menu SET elem='$elems' WHERE mid=$idm");
    $ok= 1;
  }
  return $ok;
}
# ------------------------------------------------------------------------------------ menu del_elem
# vypustí z menu $idm element $elem=$id
function menu_del_elem($id,$idm,$elem) {
  $elems= select("elem","menu","mid=$idm");
  $elems= explode(';',$elems);
  $elems= array_diff($elems,array("$elem=$id"));
  $elems= implode(';',$elems);
  query("UPDATE menu SET elem='$elems' WHERE mid=$idm");
  return 1;
}
# --------------------------------------------------------------------------------------- menu shift
# posune menu o jedno dolů (pro down=0 nahoru)
function menu_shift($mid,$down) {
  // zjistíme všechna menu na stejné úrovni
  list($mid_top,$typ)= select("mid_top,abs(typ)","menu","mid=$mid");
  $cond= $typ==2 && $mid_top ? "mid_top=$mid_top" : (
    $typ==1 || $typ==0 ? "typ=$typ" : 0 );
  $ms= select("GROUP_CONCAT(mid ORDER BY rank)","menu","wid=2 AND $cond");
//                                              display("x:$ms");
  $ms= explode(',',$ms);
  $i= array_search($mid,$ms);
  $last= count($ms)-1;
  if ( $down ) { // dolů
    if ( $i<$last ) {
      $ms[$i]= $ms[$i+1];
      $ms[$i+1]= $mid;
    }
  }
  else { // nahoru
    if ( $i>0 ) {
      $ms[$i]= $ms[$i-1];
      $ms[$i-1]= $mid;
    }
  }
//                                              display("y:".implode(',',$ms));
  foreach ($ms as $i=>$mi) {
    $i1= $i+1;
    query("UPDATE menu SET rank=$i1 WHERE wid=2 AND mid=$mi");
  }
  return 1;
}
# ---------------------------------------------------------------------------------- menu shift_elem
# posune element o jedno dolů (pro down=0 nahoru)
function menu_shift_elem($typ,$id,$mid,$down) {
  // zjistíme seznam elementů
  $elems= select("elem","menu","mid=$mid");
                                                      display($elems);
  $ms= explode(';',$elems);
  $elem= "$typ=$id";
  $i= array_search($elem,$ms);
  $last= count($ms)-1;
  if ( $down ) { // dolů
    if ( $i<$last ) {
      $ms[$i]= $ms[$i+1];
      $ms[$i+1]= $elem;
    }
  }
  else { // nahoru
    if ( $i>0 ) {
      $ms[$i]= $ms[$i-1];
      $ms[$i-1]= $elem;
    }
  }
  $elems= implode(';',$ms);                 
                                                      display($elems);
  query("UPDATE menu SET elem='$elems' WHERE mid=$mid");
  return 1;
}
# ------------------------------------------------------------------------------------ menu upd_akce
# obnoví podmenu hlavního menu.ref=akce pro web_stav>0
#    rank:  {n} pořadí podle akce.zacatek
#    ref:   akce_{n} 
#    level: 0 pro web_stav=2 | TESTER pro web_stav=1
#    elem:  akce={id_akce}
# return: počet akcí + 1
function menu_upd_akce($wid) {
  global $web_db;
  $n= 0;
  $mid_akce= select("mid","menu","wid=$wid AND ref='seminare-v-cr'",$web_db);
  if ( !$mid_akce ) goto end;
  // odstraň staré podmenu
  query("DELETE FROM menu WHERE wid=$wid AND mid_top=$mid_akce",$web_db);
  // přidej nové podle tabulky akce
  $ra= mysql_query("
    SELECT id_akce,zacatek,web_stav,web_menu
    FROM akce WHERE web_stav>0
    ORDER BY zacatek DESC
  ");
  while ( $ra && (list($ida,$zacatek,$stav,$menu)= mysql_fetch_array($ra)) ) {
    $n++;
    $level= $stav==2 ? 0 : TESTER;
    $rok= substr($zacatek,0,4);
    query("INSERT INTO menu (wid,mid_top,typ,rank,level,ref,nazev,elem) 
           VALUE ($wid,$mid_akce,2,$n,$level,'seminare-v-cr-$n','$rok: $menu','akce=$ida')
        ",$web_db);
  }
end:  
  return $n+1;
}
# ----------------------------------------------------------------------------------- menu upd_bunka
# obnoví podmenu hlavního menu.ref=bunka pro web_stav>0
#    rank:  {n} pořadí podle bunka.zacatek
#    ref:   akce_{n} 
#    level: 0 pro web_stav=2 | TESTER pro web_stav=1
#    elem:  bunka={id_akce}
function menu_upd_bunka($wid) {
  global $web_db;
  $n= 0;
  $mid_akce= select("mid","menu","wid=$wid AND ref='bunky-v-cr'",$web_db);
  if ( !$mid_akce ) goto end;
  // odstraň staré podmenu
  query("DELETE FROM menu WHERE wid=$wid AND mid_top=$mid_akce",$web_db);
  // přidej nové podle tabulky cell
  $ra= mysql_query("
    SELECT id_cell,web_stav,web_menu
    FROM cell WHERE web_stav>0
    ORDER BY nazev DESC
  ");
  while ( $ra && (list($idb,$stav,$menu)= mysql_fetch_array($ra)) ) {
    $n++;
    $level= $stav==2 ? 0 : TESTER;
    query("INSERT INTO menu (wid,mid_top,typ,rank,level,ref,nazev,elem) 
           VALUE ($wid,$mid_akce,2,$n,$level,'bunka_$n','$menu','bunka=$idb')
        ",$web_db);
  }
end:  
  return $n;
}
# ---------------------------------------------------------------------------------------- menu save
function menu_save($wid,$tree) {
  global $web_db;
  $walk= function ($node,$delv='') use (&$walk,$web_db) {
    $n= 0;
    if ( isset($node->prop->data->ref) && $node->prop->data->mid>=0 ) {
      $fields= $values= $del= "";
      foreach($node->prop->data as $field => $v) {
        $values.= "$del\"$v\"";
        $fields.= "$del$field";
        $del= ',';
      }
      $qry= "INSERT INTO menu ($fields) VALUES ($values)";
      query($qry,$web_db);
      $n++;
    }
    if ( isset($node->down) ) {
      foreach($node->down as $child) {
        if ( !$delv ) 
          $delv= $values ? ',' : '';
        $n+= $walk($child,$delv);
      }
    }
    return $n;
  };
  query("DELETE FROM menu_save WHERE wid=$wid",$web_db);
  query("INSERT INTO menu_save SELECT * FROM menu",$web_db);
  query("DELETE FROM menu WHERE wid=$wid",$web_db);
  $m= json_decode($tree);
  $n= $walk($m);
  return "$n položek menu pro $wid";
}
# ---------------------------------------------------------------------------------------- menu undo
function menu_undo($wid) {
  global $web_db;
  query("DELETE FROM menu WHERE wid=$wid",$web_db);
  query("INSERT INTO menu SELECT * FROM menu_save WHERE wid=$wid",$web_db);
  return 1;
}
# ---------------------------------------------------------------------------------------- menu tree
function menu_tree($wid) {
  global $web_db;
  //{prop:°{id:'ONE'},down:°[°{prop:°{id:'TWO'}},°{prop:°{id:'THREE'}}]}
  $data= (object)array('mid'=>0);
  $menu= 
    (object)array(
      'prop' => (object)array('id'=>'menu'),
      'down' => array(
        (object)array(
          'prop' => (object)array('id'=>'top menu','data'=>$data),
          'down' => array()
        ),    
        (object)array(
          'prop' => (object)array('id'=>'main menu','data'=>$data),
          'down' => array()
        )
      )
    );    
  $mn= mysql_qry("SELECT * FROM menu WHERE wid=$wid ORDER BY typ,mid_top,rank",
      0,0,0,$web_db);
  while ( $mn && ($m= mysql_fetch_object($mn)) ) {
    $mid_top= $m->mid_top;
    $typ= abs($m->typ);
    $nazev= $m->ref;
    if ( $typ==0 ) {
      $node= (object)array('prop'=>(object)array('id'=>$nazev,'data'=>$m));
      $menu->down[0]->down[]= $node;
    }
    elseif ( $typ==1 ) {
      $node= (object)array('prop'=>(object)array('id'=>$nazev,'data'=>$m));
      $menu->down[1]->down[]= $node;
    }
    elseif ( $typ==2 ) {
      foreach ( $menu->down[1]->down as $sm ) {
        if ( $sm->prop->data->mid===$mid_top ) {
          $node= (object)array('prop'=>(object)array('id'=>$nazev,'data'=>$m));
          $sm->down[]= $node;
          break;
        }
      } 
    }
  }
  return $menu;
}
/** ========================================================================================> SERVER */
# funkce na serveru přes AJAX
# --------------------------------------------------------------------------------------- ask server
function ask_server($x) {
  global $z, $trace;
  switch ( $x->cmd ) {
    case 'mapa':
      // získej polohu buněk
      $names= $notes= $fara= $lats= $lngs= array();
      $rb= pdo_query("SELECT id_fara,c.nazev,f.obec,f.ulice,g.lat,g.lng 
        FROM cell AS c JOIN ve USING (id_cell) 
        JOIN fara AS f USING (id_fara) LEFT JOIN _geo AS g USING (id_geo)");
      while ($rb && (list($idf,$nazev,$obec,$ulice,$lat,$lng)= pdo_fetch_row($rb))) {
        $fara[$idf]= "$obec $ulice";
        if (!isset($names[$idf])) {
          $notes[$idf]= 1;
          $names[$idf]= $nazev;
        }
        else {
          $notes[$idf]++;
          $names[$idf].= " a $nazev";
        }
        if ($lat) {
          $lats[$idf]= $lat;
          $lngs[$idf]= $lng;
        }
      }
      $marks= $err= '';
      $n= 0; $del= '';
      $icon= "/feb/img/feb-logo.png";
      foreach ($notes as $idf=>$note) {
        $notes[$idf]= $note>1 ? "$note buňky " : 'buňka ';
        $n++;
        $title= $notes[$idf].'<br>'.str_replace(' a ','<br>',$names[$idf])
            ."<br>ve farnosti<br>$fara[$idf]";
        $title= str_replace(',',' ',$title);
        $marks.= "{$del}$n,{$lats[$idf]},{$lngs[$idf]},$title,$icon"; $del= ';';
      }
      // předej výsledek
      $z->mapa= (object)array('mark'=>$marks,'n'=>$n);
      $z->trace= $trace;
      break;
  }
end:
  return 1;
}
/** =================================================================================> CMS PŘIHLÁŠKY */
# funkce pro online přihlášky
# ------------------------------------------------------------------------------- cms send_potvrzeni
# pošle potvrzení o přijetí přihlášky
function cms_send_potvrzeni($email,$idl,$ida) {
  $nazev= select('nazev','akce',"id_akce=$ida");
//  $email= select('mail','lidi',"id_lidi=$idl");
  $reply= "evangelizacnibunky@seznam.cz";
  $subj= "Potvrzení přijetí přihlášky na  $nazev";
  $body= "Dobrý den, potvrzujeme vaši přihlášku na seminář. 
    <br>Bližší info vám zašleme dva týdny předem.
    <br><br>Přeji vám hezký den.
    <br>Viera Žalúdeková, mail: <a href='mailto:$reply'>$reply</a>";
  $okmsg= cms_mail_send($email,$subj,$body,$reply);
  return $okmsg;    
}
# ------------------------------------------------------------------------------------ feb mail_send
/**
 * Pošle mail přes SMTP službu pod gmailem
 * @param string $address adresa příjemnce mailu
 * @param string $subject předmět mailu
 * @param string $body text mailu
 * @param string $reply_to nepovinná adresa pro odpověď
 * @return object {ok:0/1,msg:''/popis chyby}
 */
function feb_mail_send($address,$subject,$body,$reply='') { 
  global $EZER;
  $web_path= $_SESSION['web']['path'];
  $phpmailer_path= "$web_path/ezer3.1/server/licensed/phpmailer";
  $_SESSION['trace']['feb_mail_send-1']= $phpmailer_path;
  $_SESSION['trace']['feb_mail_send-2']= file_exists("$web_path/ezer3.1/server/licensed/phpmailer") ? 1 : 0;
  $_SESSION['trace']['feb_mail_send-3']= file_exists($phpmailer_path) ? 1 : 0;
  $_SESSION['trace']['feb_mail_send-4']= $EZER->CMS->SEZNAM;
  require_once("$phpmailer_path/class.phpmailer.php");
  require_once("$phpmailer_path/class.smtp.php");
  $ret= (object)array('ok'=>1,'msg'=>'');
  // nastavení phpMail
  $mail= new PHPMailer(true);
  $_SESSION['trace']['feb_mail_send']= $mail ? 1 : 0;
//  try {
//    $mail->SMTPDebug = 0;
    $mail->SetLanguage('cs');//,"$phpmailer_path/language/");
    $mail->IsSMTP();
    $mail->SMTPAuth = true; // enable SMTP authentication
    $mail->SMTPSecure= "ssl"; // sets the prefix to the server
    $mail->Host= "smtp.seznam.cz"; // sets SEZNAM as the SMTP server
    $mail->Port= 465; // set the SMTP port for the SEZNAM server
    $mail->Username= $EZER->CMS->SEZNAM->mail;
    $mail->Password= $EZER->CMS->SEZNAM->pswd;
    $mail->CharSet= "UTF-8";
    $mail->IsHTML(true);
    // zpětné adresy
    $mail->ClearReplyTos();
    $mail->AddReplyTo($reply ? $reply : $EZER->CMS->SEZNAM->mail);
    if (method_exists('PHPMailer','SetFrom'))
      $mail->SetFrom($EZER->CMS->SEZNAM->mail, $EZER->CMS->SEZNAM->name);
    else {
      $mail->From= $EZER->CMS->SEZNAM->mail;
      $mail->FromName= $EZER->CMS->SEZNAM->name;
    }
    // vygenerování mailu
    $mail->Subject= $subject;
    $mail->Body= $body;
    // přidání příloh
    $mail->ClearAttachments();
    // přidání adresy
    $mail->ClearAddresses();
    $mail->AddAddress($address);
    // přidání kopií
    $mail->ClearCCs();
    if ( $reply )
      $mail->AddCC($reply);
    if ( $EZER->CMS->TEST ) {
      $ret->msg= "TESTOVÁNÍ - vlastní mail.send je vypnuto";
    }
    else {
    // odeslání mailu
//      $mail->Send();
//      $mail->smtpClose();
    // odeslání mailu
      try {
        $ok= $mail->Send();
        $ret->msg= $ok ? '' : $mail->ErrorInfo;
        $ret->err= $ok ? 0 : 1;
        $ret->ok= $ok ? 1 : 0;
      } 
      catch ( Exception $exc ) {
        $ret->msg= $mail->ErrorInfo;
        $ret->err= 2;
        $ret->ok= 0;
      }
    }
//  }
//  catch (Exception $e) {
//    $ret->msg= $mail->ErrorInfo;
//    $ret->ok= $e;
//    $ret->ok= 0;
//  }
  return $ret;
}
/** ==========================================================================================> MAPA */
# -----------------------------------------------------------------------------------==> .. map show
# vrátí strukturu pro gmap
# icon = CIRCLE[,scale:1-10][,ontop:1]|cesta k bitmapě nebo pole psc->icon
function map_show($names,$notes,$names_as_id=0,$icon='') {
//                                                debug($names,"mapa2_psc");
  // k PSČ zjistíme LAN,LNG
  $ret= (object)array('mark'=>'','n'=>0);
  $ic= '';
  if ( $icon ) {
    if ( !is_array($icon) )
      $ic=",$icon";
  }
  $marks= $err= '';
  $mis_psc= array();
  $err_psc= array();
  $chybi= array();
  $n= 0; $del= '';
  foreach ($names as $p=>$tit) {
    $p= trim($p);
    if ( preg_match('/\d\d\d\d\d/',$p) ) {
      $qs= "SELECT psc,lat,lng FROM psc_axy WHERE psc='$p'";
      $rs= pdo_qry($qs);
      if ( $rs && ($s= pdo_fetch_object($rs)) ) {
        $n++;
        $title= $notes[$p].'<br>'.str_replace(' a ','<br>',$tit);
        $title= str_replace(',',' ',$title);
        if ( is_array($icon) )
          $ic= ",{$icon[$p]}";
        $marks.= "{$del}$n,{$s->lat},{$s->lng},$title$ic"; $del= ';';
      }
      else {
        $err_psc[$p].= " $p";
        if ( !in_array($p,$chybi) ) 
          $chybi[]= $p;
      }
    }
    else {
      $mis_psc[$p].= " $p";
    }
  }
  // zjištění chyb
  if ( count($err_psc) || count($mis_psc) ) {
    if ( ($ne= count($mis_psc)) ) {
      $err= "$ne PSČ chybí nebo má špatný formát. Týká se to: ".implode(' a ',$mis_psc);
    }
    if ( ($ne= count($err_psc)) ) {
      $err.= "<br>$ne PSČ se nepovedlo lokalizovat. Týká se to: ".implode(' a ',$err_psc);
    }
  }
  $ret= (object)array('mark'=>$marks,'n'=>$n,'err'=>$err,'chybi'=>$chybi);
//                                                    debug($chybi,"chybějící PSČ");
  return $ret;
}
