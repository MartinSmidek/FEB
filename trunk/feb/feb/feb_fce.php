<?php # (c) 2017 Martin Smidek <martin@smidek.eu>

function feb_random($table) {
  $count= 100;
  switch ($table) {
  case 'lidi':
    query("TRUNCATE TABLE lidi",'feb');
    $osoby= $lidi= array(); $n= 0;
    $polozek= 4;
    $tr= mysql_qry("
      SELECT jmeno,prijmeni,ulice,CONCAT(psc,'*',obec)
      FROM ezer_db2.osoba 
      WHERE deleted='' AND LEFT(prijmeni,1)!='-'
        AND psc BETWEEN '75000' AND '78999'
      LIMIT $count");
    while ( $tr && ($osoba= mysql_fetch_row($tr)) ) {
      for ($i= 0; $i<$polozek; $i++) {
        $osoby[$i][$n]= $osoba[$i];
      }
      $n++;
    }
//                                debug($osoby);
    for ($i= 0; $i<$polozek; $i++) {
      shuffle($osoby[$i]);
    }
//                                debug($osoby);
    // zápis do LIDI
    for ($k= 0; $k<$n; $k++) {
      $jmeno= $osoby[0][$k];
      $prijmeni= $osoby[1][$k];
      $ulice= $osoby[2][$k];
      $psc= explode('*',$osoby[3][$k])[0];
      $obec= explode('*',$osoby[3][$k])[1];
      $mail= strtolower(utf2ascii($jmeno)).'.'.strtolower(utf2ascii($prijmeni))
          .'@'.array('seznam.cz','gmail.com')[mt_rand(0,1)];
      $telefon= mt_rand(601,609).' '.mt_rand(100,999).' '.mt_rand(100,999);
      query("INSERT INTO lidi (jmeno,prijmeni,ulice,psc,obec,mail,telefon) VALUES "
          . "('$jmeno','$prijmeni','$ulice','$psc','$obec','$mail','$telefon')",'feb');
    }

    break;
  }
}
function feb_fara($n) {
  query("TRUNCATE TABLE fara",'feb');
  query("TRUNCATE TABLE je",'feb');
  query("INSERT INTO feb.fara (nazev,ulice,psc,obec) 
         SELECT farnost,ulice,psc,misto FROM ezer_rz.fary");
  return 1;
}
?>
