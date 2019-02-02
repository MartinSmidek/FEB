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
  // connect
  $web_db= "feb";
  $ezer_local= preg_match('/^.*\.(bean)$/',$_SERVER["SERVER_NAME"]);
  $hst1= 'localhost';
  $nam1= $ezer_local ? 'gandi'    : 'gandi';
  $pas1= $ezer_local ? ''         : 'radost';
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
//  $web_db= "feb";
//  $ezer_local= preg_match('/^.*\.(bean)$/',$_SERVER["SERVER_NAME"]);
//  $hst1= 'localhost';
//  $nam1= $ezer_local ? 'gandi'    : 'gandi';
//  $pas1= $ezer_local ? ''         : 'radost';
//  $ezer_db= array( /* lokální */
//    $web_db  =>  array(0,$hst1,$nam1,$pas1,'utf8'),
//  );
//  ezer_connect('feb');
  // načtení menu
  $menu= array();
  $mn= mysql_qry("SELECT * FROM menu WHERE wid=2 AND typ>=0 ORDER BY typ,rank");
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
  global $CMS, $currpage, $tm_active, $ezer_local, $index;
  global $menu, $topmenu, $mainmenu, $elem, $backref, $top;
  $index= "index.php";
  $prefix= $ezer_local
      ? "http://feb.bean:8080/$index?page="
      : "http://feb.ezer.cz/$index?page=";
  $prefix= $ezer_local
      ? "http://feb.bean:8080/"
      : "http://feb.ezer.cz/";
  // pokud má menu M submenu S tak bude prvkem vnořené pole - první ještě patří do mainmenu
  $topmenu= $mainmenu= array();
  $currpage= implode('!',$path);
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
    $jmp= $CMS 
      ? "onclick=\"go(arguments[0],'page=$href','{$prefix}$href','$input',0);\""
      : "href='{$prefix}$href'";
    switch ( (int)$m->typ ) {
//    case 0:                             // zobrazení top menu
//      $active= '';
//      if ( $m->ref===$top ) {
//        $active= ' active';
//        $elem= $m->elem;
////        $top= array_pop($path);
//        $tm_active= " class='active'";
//        $backref= $CMS 
//          ? "onclick=\"go(arguments[0],'page=$href!*','{$prefix}$href!*','$input',0);\""
//          : "href='{$prefix}$href!*'";
//        $top= array_shift($path);
//      }
//      if ( $m->nazev ) {
//        $topmenu[$m->mid]= "<a $jmp class='jump$level$active'><span>$m->nazev</span></a>";
//      }
//      break;
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
        $backref= $CMS 
          ? "onclick=\"go(arguments[0],'page=$href!*','{$prefix}$href!*','$input',0);\""
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
          $active= ' active';
          $elem= $m->elem;
          $backref= $CMS 
            ? "onclick=\"go(arguments[0],'page=$href!*','{$prefix}$href!*','$input',0);\""
//            : "href='{$prefix}$href!*'";
            : "href='{$prefix}$href2'";
          $top= array_shift($path);
        }
        $jmp= $CMS 
          ? "onclick=\"go(arguments[0],'page=$href','{$prefix}$href','$input',0);\""
//          : "href='{$prefix}$href'";
          : "href='{$prefix}$href2'";
        $mainmenu[$main][]= "<a $jmp class='jump$level$active'><span>$m->nazev</span></a>";
      }
      break;
    }
  }
  return $elem;
}
# -------------------------------------------------------------------------------------==> eval_elem
// desc :: key [ = ids ]
// ids  :: id1 [ / id2 ] , ...    -- id2 je klíč v lokální db pro ladění
function eval_elem($desc) {
  global $CMS, $ezer_local, $load_ezer;
  global $edit_entity, $edit_id;
  $edit_entity= '';
  $edit_id= 0;
  $elems= explode(';',$desc);
  $html= '';
//  $html= $CMS ? "<script>skup_mapka_off();</script>" : '';
  foreach ($elems as $elem) {
    list($typ,$ids)= explode('=',$elem.'=');
    // přemapování ids podle server/localhost
    $id= null;
    if ( $ids ) {
      $id= array();
      foreach (explode(',',$ids) as $id12) {
        list($id_server,$id_local)= explode('/',$id12);
        $id[]= $id_local ? ($ezer_local ? $id_local : $id_server) : $id_server; 
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
      $html.= $text;
      break;

    case 'bunka': # -------------------------------------------------- . bunka
      $edit_entity= 'bunka';
      $edit_id= $id;
      $html.= select("web_text","cell","id_cell=$id");
      break;

    case 'clanek': # ------------------------------------------------- . článek
      $edit_entity= 'clanek';
      $edit_id= $id;
      $html.= select("web_text","clanek","id_clanek=$id");
      break;

    case 'mapa':    # ------------------------------------------------ . mapa
      global $CMS;
      $load_ezer= true;
      $html.= !$CMS ? '' : <<<__EOT
        <script>skup_mapka();</script>
__EOT;
      break;

    }
  }
  return $html;
}
# -------------------------------------------------------------------------------------==> show_page
function show_page($html,$full_page=0) {
  global $CMS, $index, $mainmenu;
  $url= '';
//  $url= "<div>{$_SERVER['REQUEST_URI']} --- ".(isset($_GET['page']) ? "page={$_GET['page']}" : '')."</div>";
  
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
  $head=  <<<__EOD
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <!-- saved from url=(0029)http://evangelizacnibunky.cz/ -->
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
      <base href="/" />
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
      <meta name="description" content="" />
      <meta name="keywords" content="" />
      <link href="/feb/css/web.css" rel="stylesheet" type="text/css" />
      <link href="/feb/css/edit.css" rel="stylesheet" type="text/css" />
      <link href="/ezer3/client/ezer_cms3.css" rel="stylesheet" type="text/css" />
      <link href="/ezer3/client/licensed/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
      <link rel="shortcut icon" href="/feb/img/feb.logo.png">
      <script src="/feb/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
      <script src="/ezer3/client/ezer_cms3.js" type="text/javascript" charset="utf-8"></script>
      <script type="text/javascript">
        var Ezer= {
          web: {index:'$index'},
          cms: {form:{}}
        };
      </script>
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
  query("INSERT INTO $table () VALUES ()");
  $id= mysql_insert_id();
  $elem= ($elem ? "$elem;" : '') . "$table=$id";
  query("UPDATE menu SET elem='$elem' WHERE wid=2 AND mid=$mid");
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
  $mid_akce= select("mid","menu","wid=$wid AND ref='bunka'",$web_db);
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
# ------------------------------------------------------------------------------------------ session
# getter a setter pro _SESSION
function session($is,$value=null) {
  $i= explode(',',$is);
  if ( is_null($value) ) {
    // getter
    switch (count($i)) {
    case 1: $value= $_SESSION[$i[0]]; break;
    case 2: $value= $_SESSION[$i[0]][$i[1]]; break;
    case 3: $value= $_SESSION[$i[0]][$i[1]][$i[2]]; break;
    }
  }
  else {
    // setter
    switch (count($i)) {
    case 1: $_SESSION[$i[0]]= $value; break;
    case 2: $_SESSION[$i[0]][$i[1]]= $value; break;
    case 3: $_SESSION[$i[0]][$i[1]][$i[2]]= $value; break;
    }
    $value= 1;
  }
  return $value;
}
# --------------------------------------------------------------------------------------- ask server
function ask_server($x) {
//  global $y, $trace;
  switch ( $x->cmd ) {
//
//  case 'send_pin': // ------------------------------------------------------------------ send pin
//    // ask({cmd:'send_pin',mail:mail} ...
//    $y->ok= emailIsValid($x->mail,$err) ? 1 : 0;
//    if ( $y->ok ) {
//      // vytvoř pin a zapiš do session
//      $pin= $_SESSION['feb']['pin']= rand(1000,9999);
//      // odeslání mailu
//      $ret= mail_send('answer@setkani.org', $x->mail, "Přihlášení na $x->akce", 
//          "Pokud jste žádal(a) o přihlášení na seminář, 
//          napište prosím vedle svojí mailové adresy $pin a použijte znovu tlačítko Odeslat.
//          <br>Pokud se jedná o omyl, pak prosím tento mail ignorujte.");
//      if ( $ret->msg ) {
//        $y->state = 'err';
//        $y->txt .= "Lituji, mail se nepovedlo odeslat ($ret->msg)";
//        goto end;
//      }
//      $y->state = 'wait'; // čekáme na zadání PINu z mailu
//    }
//    else {
//      $y->txt= "'$x->to' nevypadá jako mailová adresa ($err)";
//    }
//    break;
//
//  case 'seek_db': // -------------------------------------------------------------------- seek db
//    $y->id= 0;
//    connect();
//    $ra= mysql_query("SELECT id_lidi,pin,prijmeni,jmeno,telefon,ulice,psc,obec
//                      FROM lidi WHERE mail='$x->mail' ");
//    if ( $ra && $f= mysql_fetch_object($ra) ) ;
//    if ( $f->id_lidi ) {
//      // navrať osobní data 
//      $y->id= $f->id_lidi;
//      $y->pin= $f->pin;
//      $y->db= $f;
//    } 
//    break;
//
  }
end:
  return 1;
}
?>
