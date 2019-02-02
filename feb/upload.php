<?php # (c) 2018 Martin Smidek <martin@smidek.eu>
error_reporting(E_ALL & ~E_NOTICE);
session_start();
if ( isset($_FILES['upload']) ) {
  // nastavení cesty
  if ( isset($_GET['root']) && $_GET['root']=='test' ) {
    $root= 'C:/#';
    $path= 'cms/';
  }
  else {
    $root= $_SESSION['feb']['abs_root'];
    $path= $_SESSION['feb']['inc_path'];
  }
  // případně založení složky
  $dir= str_replace('//','/',rtrim("$root/$path",'/'));
  $ok= mkdir($dir,0777,1);
  if ( !$ok ) { 
    $msg= "nelze vytvořit složku $dir"; goto err; }
  // soubor vložený do CKEditoru
  $xname= $_FILES['upload']['tmp_name'];
  $fname= utf2ascii(urldecode($_FILES['upload']['name']),'.');
  move_uploaded_file($xname,str_replace('//','/',"$dir/$fname"));
  $url= str_replace('//','/',"/$path/$fname");
  $ret= <<<__EOD
  {
    "uploaded": 1,
    "fileName": "$fname",
    "url": "$url"
  }
__EOD;
  echo $ret;
  exit;
err:
  $ret= <<<__EOD
  {
    "uploaded": 0,
    "error": {
      "message": "$msg"
    }
  }
__EOD;
  echo $ret;
  exit;
}
# -------------------------------------------------------------------------------------------------- utf2ascii
# konverze z UTF-8 do písmen, číslic a podtržítka, konvertují se i html entity
function utf2ascii($val,$allow='') {
  $txt= preg_replace('~&(.)(?:acute|caron);~u', '\1', $val);
  $txt= preg_replace('~&(?:nbsp|amp);~u', '_', $txt);
  $ref= preg_replace("~[^\\pL0-9_$allow]+~u", '_', $txt);
  $ref= trim($ref, "_");
//     setLocale(LC_CTYPE, "cs_CZ.utf-8");                      bohužel nebývá nainstalováno
//     $url= iconv("utf-8", "us-ascii//TRANSLIT", $url);
  $ref= strtr($ref,array('ě'=>'e','š'=>'s','č'=>'c','ř'=>'r','ž'=>'z','ý'=>'y','á'=>'a','í'=>'i',
                         'é'=>'e','ů'=>'u','ú'=>'u','ó'=>'o','ď'=>'d','ť'=>'t','ň'=>'n'));
  $ref= strtr($ref,array('Ě'=>'E','Š'=>'S','Č'=>'C','Ř'=>'R','Ž'=>'Z','Ý'=>'Y','Á'=>'A','Í'=>'I',
                         'É'=>'E','Ů'=>'U','Ú'=>'U','Ó'=>'O','Ď'=>'D','Ť'=>'T','Ň'=>'N'));
  $ref= mb_strtolower($ref);
  $ref= preg_replace("~[^-a-z0-9_$allow]+~", '', $ref);
  return $ref;
}
?>
