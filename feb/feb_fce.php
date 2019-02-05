<?php # (c) 2017 Martin Smidek <martin@smidek.eu>
/** =========================================================================================> MAILY */
# ----------------------------------------------------------------------------- feb pack_pridat_lidi
# přidat adresáty z jiné tabulky
function feb_pack_pridat_lidi($idp,$rel,$tab,$id_tab) {
  $difs= 0;
  $rl= mysql_qry("SELECT id_lidi FROM $rel WHERE id_$tab=$id_tab");
  while ( $rl && list($idl)= mysql_fetch_row($rl) ) {
    $idg= select('id_lidi','go',"id_pack=$idp AND id_lidi=$idl");
    if ( !$idg ) {
      query("INSERT INTO go (id_pack,id_lidi,stav) VALUE ($idp,$idl,0)");
      $difs+= mysql_affected_rows();
    }
  }
  return $difs;
}
# ------------------------------------------------------------------------------ feb pack_ubrat_lidi
# ubrat adresáty z jiné tabulky
# -- ubrat lze jen neposlané maily
function feb_pack_ubrat_lidi($idp,$rel,$tab,$id_tab) {
  $difs= 0;
  $rl= mysql_qry("SELECT id_lidi FROM $rel WHERE id_$tab=$id_tab");
  while ( $rl && list($idl)= mysql_fetch_row($rl) ) {
    $idg= select('id_go','go',"id_pack=$idp AND id_lidi=$idl");
    if ( $idg ) {
      query("DELETE FROM go WHERE id_go=$idg");
      $difs+= mysql_affected_rows();
    }
  }
  return $difs;
}
# ------------------------------------------------------------------------------------ feb pack_send
# ASK
# odešli dávku $kolik mailů ($kolik=0 znamená testovací poslání)
# $from,$fromname = From,ReplyTo
# $test = 1 mail na tuto adresu (pokud je $kolik=0)
# pokud je definováno $id_mail s definovaným text MAIL.body, použije se - jinak DOPIS.obsah
# pokud je definováno $foot tj. patička, připojí se na konec
# použije se SMTP server podle SESSION
# stavy: ok=5, chyba=6, znovu=4
function feb_pack_send($idp,$kolik,$from,$fromname,$test='',$idl=0,$foot='') {
  // připojení případné přílohy
  $attach= function($mail,$fname) {
    global $ezer_root;
    if ( $fname ) {
      foreach ( explode(',',$fname) as $fnamesb ) {
        list($fname)= explode(':',$fnamesb);
        $fpath= "docs/$ezer_root/".trim($fname);
        $mail->AddAttachment($fpath);
  } } };
  //
  $y= (object)array('_error'=>0);
  $stav_ok=5; $stav_chyba=6; $stav_znovu=4;
  $pro= '';
  // přečtení dopisu
  list($encl,$subj,$text)= select('pack_encl,pack_subj,pack_text','pack',"id_pack=$idp");
  // napojení na mailer
  $mail= feb_new_PHPMailer();
  if ( !$mail ) { 
    $y->_html.= "<br><b style='color:#700'>odesílací adresa nelze použít (SMTP)</b>";
    $y->_error= 1;
    goto end;
  }
  $mail->From= $from;
  $mail->AddReplyTo($from);
  $mail->FromName= $fromname;
  $mail->Subject= $subj;
  $mail->Body= $text . $foot;
  // připoj přílohy
  $attach($mail,$encl);
  if ( $kolik==0 ) {
    // testovací poslání sobě
    $mail->AddAddress($test);   // pošli si
    // pošli
//    $ok= $mail->Send();
    $ok= 1; display("mail.Send()");
    if ( $ok  )
      $y->html.= "<br><b style='color:#070'>Byl odeslán mail na $test $pro - je zapotřebí zkontrolovat obsah</b>";
    else {
      $err= $mail->ErrorInfo;
      $y->html.= "<br><b style='color:#700'>Při odesílání mailu došlo k chybě: $err</b>";
      $y->_error++;
    }
  }
  else {
    // poslání dávky $kolik mailů
    $n= $nko= 0;
    $gs= pdo_qry("SELECT id_go,id_lidi,mail FROM go JOIN lidi USING (id_lidi)
      WHERE id_pack=$idp AND mail!='' AND stav IN (0,$stav_znovu)");
    while ( $gs && (list($idg,$idl,$adresy)= pdo_fetch_row($gs)) ) {
      // posílej mail za mailem
      if ( $n>=$kolik ) break;
      $n++;
      $i= 0;
      $mail->ClearAddresses();
      $mail->ClearCCs();
      foreach(preg_split("/,\s*|;\s*|\s+/",trim($adresy," ,;"),-1,PREG_SPLIT_NO_EMPTY) as $adresa) {
        if ( !$i++ )
          $mail->AddAddress($adresa);   // pošli na 1. adresu
        else                            // na další jako kopie
          $mail->AddCC($adresa);
      }
      // zkus poslat mail
//      try { $ok= $mail->Send(); } catch(Exception $e) { $ok= false; $msg= $e; }
      $ok= 1; display("mail.Send()");
      if ( !$ok  ) {
        $err= $mail->ErrorInfo;
        $y->html.= "<br><b style='color:#700'>Při odesílání mailu pro $idl došlo k chybě: $err</b>";
        $y->_error++;
        $nko++;
      }
      // zapiš výsledek do tabulky
      $stav= $ok ? $stav_ok : $stav_chyba;
      $msg= $ok ? '' : $mail->ErrorInfo;
      query("UPDATE go SET stav=$stav,msg=\"$msg\" WHERE id_go=$idg");
    }
    $y->html.= "<br><b style='color:#070'>Bylo odesláno $n emailů "
            .  ( $nko ? "s $nko chybami " : "bez chyb" ) . "</b>";
  }
end:  
  return $y;
}
# -------------------------------------------------------------------------------- feb new_PHPMailer
# nastavení parametrů pro SMTP server podle user.options.smtp
function feb_new_PHPMailer() {  
  global $ezer_path_serv, $ezer_root;
  // získání parametrizace SMTP
  $idu= $_SESSION[$ezer_root]['user_id'];
  $i_smtp= sys_user_get($idu,'opt','smtp');
  $smtp_json= select1('hodnota','_cis',"druh='smtp_srv' AND data=$i_smtp");
  $smtp= json_decode($smtp_json);
  if ( json_last_error() != JSON_ERROR_NONE ) {
    $mail= null;
    fce_warning("chyba ve volbe SMTP serveru" . json_last_error_msg());
    goto end;
  }
  // inicializace phpMailer
  $phpmailer_path= "$ezer_path_serv/licensed/phpmailer";
  require_once("$phpmailer_path/class.phpmailer.php");
  require_once("$phpmailer_path/class.smtp.php");
  $mail= new PHPMailer;
  $mail->SetLanguage('cz',"$phpmailer_path/language/");
  $mail->IsSMTP();
  $mail->CharSet = "UTF-8";
  $mail->IsHTML(true);
  $mail->Mailer= "smtp";
  foreach ($smtp as $part=>$value) {
    $mail->$part= $value;
  }
end:  
  return $mail;
}
# =====================================================================================> . XLS tisky
# -------------------------------------------------------------------------------------- feb sestava
# generování sestavy, kde par:
#   typ = lidi | lidi-akce
#   hdr = název
#   tit = nadpisy sloupců
#   fld = seznam položek s prefixem
#   cnd = podmínka NEBO 
#   ids = seznam klíčů 
function feb_sestava($par,$export=false) {
  $html= '';
  switch($par->typ) {
  case 'lidi':      $html= feb_sestava_lidi($par,$export); break;
  case 'akce-lidi': $html= feb_sestava_lidi($par,$export); break;
  }
  return $html;
}
# --------------------------------------------------------------------------------- feb sestava_lidi
function feb_sestava_lidi($par,$export=false) { 
  $typ= $par->typ;
  $tit= $par->tit;   
  $fld= $par->fld;
  $select_fld= substr(strtr(",$fld",array(',_vek'=>'',',_n'=>'')),1);
  $cnd= $par->cnd;
  $ids= $par->ids;
//  $hav= $par->hav ? "HAVING {$par->hav}" : '';
  $ord= $par->ord ? $par->ord : "prijmeni,jmeno";
  $n= 0;
  // kontrola a dekódování parametrů
  $tits= explode(',',$tit);
  $flds= explode(',',$fld);
  if ( isset($par->ids) && !$ids ) {
    $html= "... je třeba vybrat řádky klávesou Insert"; goto end;
  }
  // číselníky
  $stat= map_cis('stat','hodnota');  $stat[0]= '';
  // získání dat 
  $clmn= array();
//  $expr= array();       // pro výrazy
  $qry= $typ=='lidi' 
      ? "SELECT $select_fld FROM lidi WHERE id_lidi IN ($par->ids) ORDER BY $ord" : (
        $typ=='akce-lidi' 
      ? "SELECT $select_fld FROM akce 
         JOIN na USING (id_akce)
         JOIN lidi USING (id_lidi)
         WHERE $cnd
         ORDER BY $ord " : ''
  );
  $res= mysql_qry($qry);
  while ( $res && ($x= mysql_fetch_object($res)) ) {
    $n++;
    $clmn[$n]= array();
    // doplnění počítaných položek
    $x->narozeni_dmy= sql_date1($x->narozeni);
    foreach($flds as $f) {
      switch ($f) {
      case '_n':
        $clmn[$n][$f]= $n;
        break;
      case 'narozeni':
        $clmn[$n][$f]= $x->narozeni_dmy;
        break;
      case 'psc':   
        $psc= str_replace(' ','',$x->psc).' ';
        $clmn[$n][$f]= substr($psc,0,3)." ".substr($psc,3,2); 
        break;
      case 'stat':
        $clmn[$n][$f]= $stat[$x->stat];
        break;
      case '_vek':
        $clmn[$n][$f]= $x->narozeni=='0000-00-00' ? '?' : roku_k($x->narozeni);
        break;
      default: $clmn[$n][$f]= $x->$f;
      }
    }
  }
  return feb_table($par,$tits,$flds,$clmn,$export);
end:
  return $html;
}
# ---------------------------------------------------------------------------------------- feb table 
function feb_table($par,$tits,$flds,$clmn,$export=false) {  
  // zobrazení tabulkou
  $href= '';
  $n= 0;
  if ( $export ) {
    $href= feb_excel($par,(object)array('tits'=>$tits,'flds'=>$flds,'clmn'=>$clmn));
  }
  // titulky
  foreach ($tits as $idw) {
    list($id)= explode(':',$idw);
    $ths.= "<th>$id</th>";
  }
  foreach ($clmn as $c) {
    $n++;
    $tab.= "<tr>";
    foreach ($flds as $f) {
      switch ($f) {
      case '_n':  
        $tab.= "<td style='text-align:right'>$n</td>"; break;
      case 'psc':  
        $tab.= "<td style='text-align:right'>".str_replace(' ','&nbsp;',$c[$f])."</td>"; break;
      case 'narozeni':  
        $tab.= "<td style='text-align:right'>{$c[$f]}</td>"; break;
      default:
        $tab.= "<td style='text-align:left'>{$c[$f]}</td>"; break;
      }
    }
    $tab.= "</tr>";
  }
  $html= "<div class='stat'>Seznam má $n řádků$href<br><br><table><tr>$ths</tr>$tab</table></div>";
  return $html;
}
# ---------------------------------------------------------------------------------------- feb excel
# generování tabulky do excelu
# tab.tits = názvy sloupců
# tab.flds = názvy položek
# tab.clmn = hodnoty položek
# tab.atrs = formáty
# tab.expr = vzorce
function feb_excel($par,$tab) { 
  global $xA, $xn;
  $html= '';
  // vlastní export do Excelu
  $name= cz2ascii("vypis_").date("Ymd_Hi");
  $xls= <<<__XLS
    |open $name
    |sheet vypis;;L;page
    |A1 $par->hdr ::bold size=14 
__XLS;
  // titulky a sběr formátů
  $fmt= $sum= array();
  $n= 4;
  $lc= 0;
  $clmns= $del= '';
  $xA= array();                                 // překladová tabulka: název sloupce => písmeno
  if ( $tab->flds ) foreach ($tab->flds as $f) {
    $A= Excel5_n2col($lc);
    $xA[$f]= $A;
    $lc++;
  }
  $lc= 0;
  if ( $tab->tits ) foreach ($tab->tits as $idw) {
    if ( $idw=='^' ) continue;
    $A= Excel5_n2col($lc);
    list($id,$w,$f,$s)= explode(':',$idw);      // název sloupce : šířka : formát : suma
    if ( $f ) $fmt[$A]= $f;
    if ( $s ) $sum[$A]= true;
    $xls.= "|$A$n $id";
    if ( $w ) {
      $clmns.= "$del$A=$w";
      $del= ',';
    }
    $lc++;
  }
  if ( $clmns ) $xls.= "\n|columns $clmns ";
  $xls.= "\n|A$n:$A$n bcolor=ffc0e2c2 wrap border=+h|A$n:$A$n border=t\n";
  $n1= $n= 5;                                   // první řádek dat (pro sumy)
  // datové řádky
  if ( $tab->clmn ) foreach ($tab->clmn as $i=>$c) {
    $xls.= "\n";
    $lc= 0;
    foreach ($c as $id=>$val) {
      if ( $id[0]=='^' ) continue;
      $A= Excel5_n2col($lc);
      $format= '';
      if (isset($tab->expr[$i][$id]) ) {
        // buňka obsahuje vzorec
        $val= $tab->expr[$i][$id];
        $format.= ' bcolor=ffdddddd';
        $xn= $n;
        $val= preg_replace_callback("/\[([^,]*),([^\]]*)\]/","akce_vyp_subst",$val);
      }
      else {
        // buňka obsahuje hodnotu
        $val= strtr($val,array("\n\r"=>"  ","®"=>""));
        if ( isset($fmt[$A]) ) {
          switch ($fmt[$A]) {
          // aplikace formátů
          case 'l': $format.= ' left'; break;
          case 'd': $format.= ' right date'; break;
//          case 'd': $val= sql2xls($val); $format.= ' right date'; break;
          }
        }
      }
      if (isset($tab->atrs[$i][$id]) ) {
        // buňka má nastavený formát
        $format.= ' '.$tab->atrs[$i][$id];
      }
      $format= $format ? "::$format" : '';
      $val= str_replace("\n","{}",$val);        // ochrana proti řádkům v hodnotě - viz ae_slib
      $xls.= "|$A$n $val $format";
      $lc++;
    }
    $n++;
  }
  $n--;
  $xls.= "\n|A$n1:$A$n border=+h|A$n1:$A$n border=t";
  // sumy sloupců
  if ( count($sum) ) {
    $xls.= "\n";
    $nn= $n;
    $ns= $n+2;
    foreach ($sum as $A=>$x) {
      $xls.= "|$A$ns =SUM($A$n1:$A$nn) :: bcolor=ffdddddd";
    }
  }
  // časová značka
  $kdy= date("j. n. Y v H:i");
  $n+= 4;
  $xls.= "|A$n Výpis byl vygenerován $kdy :: italic";
  // konec
  $xls.= <<<__XLS
    \n|close
__XLS;
  // výstup
                                                                display($xls);
  $inf= Excel2007($xls,1);
  if ( $inf ) {
    $html= " se nepodařilo vygenerovat - viz začátek chybové hlášky";
    fce_error($inf);
  }
  else {
    $html= ", byl vygenerován také ve formátu <a href='docs/$name.xlsx' target='xlsx'>Excel</a>.";
  }
  return $html;
}
/** ===========================================================================================> ORA */
# -------------------------------------------------------------------------------------- lidi spojit
# spojení duplicitních záznamů 
# upd=0 .. dá informaci do y.msg
# upd=1 .. procede spojení
function lidi_spojit($orig,$dupl,$upd) {
  $y= (object)array('ok'=>1, 'msg'=>'');
  // vyloučíme chyby
  if ( !$dupl )       { $y->ok= 0; $y->msg= "POZOR dole nikdo není"; goto end; }
  if ( $dupl==$orig ) { $y->ok= 0; $y->msg= "POZOR dole i nahoře je týž člověk"; goto end; }
  list($prijmeni1,$jmeno1,$knez1,$del1)= 
      select("TRIM(prijmeni),TRIM(jmeno),knez,deleted","lidi","id_lidi=$orig");
  list($prijmeni2,$jmeno2,$knez2,$del2)= 
      select("TRIM(prijmeni),TRIM(jmeno),knez,deleted","lidi","id_lidi=$dupl");
  if ( $del1 || $del2 ) { $y->ok= 0; $y->msg= "POZOR záznam je už smazaný"; goto end; }
  // probereme podezřelosti
  if ( $prijmeni1!=$prijmeni2 ) { $y->msg.= " neshodují se příjmení ..."; }
  if ( $jmeno1!=$jmeno2 ) { $y->msg.= " neshodují se křestní jména ..."; }
  if ( $knez1!=$knez2 ) { $y->msg.= " tak je nebo není kněz? ..."; }
  if ( $upd ) {
    ezer_qry("UPDATE","lidi",$dupl,array((object)array('fld'=>'deleted', 'op'=>'u','val'=>1)));
    $y->msg= " Záznam $dupl smazán a ";
    // přenesení spojek
    foreach( array('je'=>'farnost','ma'=>'buňka','na'=>'akce') as $r=>$rr ) {
      query("UPDATE $r SET id_lidi=$orig WHERE id_lidi=$dupl");
      $y->msg.= " ".mysql_affected_rows()." x přesunut v '$rr' ";
    }
  }
  else {
    $y->msg.= "mám opravdu zrušit záznam č.$dupl a zachovat záznam č.$orig";
  }
end:
  return $y;
}
/** ==========================================================================================> DATA */
function feb_random($table) {
//  $count= 100;
//  switch ($table) {
//  case 'lidi':
//    query("TRUNCATE TABLE lidi",'feb');
//    $osoby= $lidi= array(); $n= 0;
//    $polozek= 4;
//    $tr= mysql_qry("
//      SELECT jmeno,prijmeni,ulice,CONCAT(psc,'*',obec)
//      FROM ezer_db2.osoba 
//      WHERE deleted='' AND LEFT(prijmeni,1)!='-'
//        AND psc BETWEEN '75000' AND '78999'
//      LIMIT $count");
//    while ( $tr && ($osoba= mysql_fetch_row($tr)) ) {
//      for ($i= 0; $i<$polozek; $i++) {
//        $osoby[$i][$n]= $osoba[$i];
//      }
//      $n++;
//    }
////                                debug($osoby);
//    for ($i= 0; $i<$polozek; $i++) {
//      shuffle($osoby[$i]);
//    }
////                                debug($osoby);
//    // zápis do LIDI
//    for ($k= 0; $k<$n; $k++) {
//      $jmeno= $osoby[0][$k];
//      $prijmeni= $osoby[1][$k];
//      $ulice= $osoby[2][$k];
//      $psc= explode('*',$osoby[3][$k])[0];
//      $obec= explode('*',$osoby[3][$k])[1];
//      $mail= strtolower(utf2ascii($jmeno)).'.'.strtolower(utf2ascii($prijmeni))
//          .'@'.array('seznam.cz','gmail.com')[mt_rand(0,1)];
//      $telefon= mt_rand(601,609).' '.mt_rand(100,999).' '.mt_rand(100,999);
//      query("INSERT INTO lidi (jmeno,prijmeni,ulice,psc,obec,mail,telefon) VALUES "
//          . "('$jmeno','$prijmeni','$ulice','$psc','$obec','$mail','$telefon')",'feb');
//    }
//
//    break;
//  }
  return 1;
}
function feb_fara($n) {
//  query("TRUNCATE TABLE fara",'feb');
//  query("TRUNCATE TABLE je",'feb');
//  query("INSERT INTO feb.fara (nazev,ulice,psc,obec) 
//         SELECT farnost,ulice,psc,misto FROM ezer_rz.fary");
  return 1;
}
?>
