<?php
# Aplikace FEB pro Komunitu blahoslavenství
# (c) 2017-2025 Martin Šmídek <martin@smidek.eu>

  global // import 
    $ezer_root; 
  global // export
    $EZER, $ezer_server, $ezer_version;
  global // klíče
    $api_gmail_user, $api_gmail_pass;
  
  // vyzvednutí ostatních hodnot ze SESSION
  $ezer_server=  $_SESSION[$ezer_root]['ezer_server'];
  $ezer_version= $_SESSION[$ezer_root]['ezer'];
  $abs_root=     $_SESSION[$ezer_root]['abs_root'];
  $rel_root=     $_SESSION[$ezer_root]['rel_root'];
  chdir($abs_root);

  /// viz \ref struktura-EZER.CMS
  
  $EZER= (object)array(
      // inicializace objektu EZER pro aplikace FEB
      'version'=>'ezer'.$_SESSION['feb']['ezer'],
      'options'=>(object)array(),
      'activity'=>(object)array(),
      'CMS'=>(object)array(
        // pro různé testovací účely  
        'TEST'=>0,
        /// informace o přístupu na gmailový účet  
        'SEZNAM'=>(object)array(
          /// přístup na použitý gmail
//          'mail'=>'web.evangelizacni.bunky@gmail.com',
          'mail'=>'web.evangelizacni.bunky@seznam.cz',
          'pswd'=>'MeditacniZahrada',                   // POZOR musí být i v číselníku smtp_srv !!!
          'name'=>'Farní evangelizační buňky'
        ),
        'FORM'=>array(
          // přihláška na seminář - bez kontaktu na rodiče
          'seminar'=>array(
            'TYPE'=>array('allow_unknown','confirm','send_mail'),
            'TEXT'=>array( 
              'cms_confirm'  => 
                  "<span style='font-size:8pt'>
                    Vyplněním této přihlášky dávám výslovný souhlas s použitím uvedených osobních 
                    údajů pro potřeby pořadatele pro organizace akcí v souladu s Nařízením 
                    Evropského parlamentu a Rady (EU) 2016/679 ze dne 27. dubna 2016 o ochraně 
                    fyzických osob a zákonem č. 101/2000 Sb. ČR. v platném znění. Současně 
                    souhlasím s tím, že pořadatel je oprávněn dokumentovat její průběh – 
                    pořizovat foto, audio, video záznamy a tyto materiály může použít pro účely 
                    další propagace své činnosti.
                  </span>",
              'cms_confirm_missing_1' =>
                  "<span class='problem'>Projevte prosím souhlas ...</span>",
              'cms_confirm_missing_2' =>
                  "Váš souhlas jsme vzali na vědomí",
              'cms_create_1'  => 
                  "Napište prosím svoji mailovou adresu, na kterou vám dojde 
                  mail s PINem, který umožní dokončit vyplnění přihlášky ...",
              'CMS_mail_error_1'  => 
                  "Pro přihlášení na akci je nutné nejprve vyplnit mailovou adresu",
              'CMS_mail_error_2'  => 
                  "Mail je v databázi vícekrát",
              'CMS_mail_error_3'  => 
                  "<b style='color:red'>Lituji, mail se nepovedlo odeslat ({SMTP})</b>",
              'CMS_mail_error_4'  => 
                  "Myslím, že '{MAIL}' nevypadá jako správná mailová adresa ({MSG})",
              'CMS_mail_txt'  => 
                  "Pokud jste žádal(a) o přihlášení na {AKCE} napište prosím vedle svojí 
                  mailové adresy {PIN} a použijte tlačítko <b>Potvrdit PIN</b>.
                  <br>Pokud se jedná o omyl, pak prosím tento mail ignorujte.",
              'CMS_mail_1'  => 
                  "Byl vám poslán mail s PINem. Opište prosím doručený PIN do formuláře.",
              'CMS_pin_1'  => 
                  "Děkujeme za potvrzení PINu, nyní prosím zkontrolujte případně opravte
                   nebo doplňte vaše osobní údaje.",
              'CMS_pin_2'  => 
                  "Děkujeme za potvrzení PINu, nyní prosím vyplňte vaše osobní údaje.",
              'CMS_pin_error_1'  => 
                  "Pro přihlášení je zapotřebí opsat PIN z došlého mailu.",
              'CMS_pin_error_2'  => 
                  "Pro přihlášení je zapotřebí <b>správně opsat</b> PIN z došlého mailu.",
              'cms_pin_no'  => 
                  "Bohužel tato akce je určena jen pro známé ... Zavolejte nám.",
              'CMS_submit_1'  => 
                  "Vaše údaje byly zapsány, děkujeme za opravu údajů.",
              'CMS_submit_2'  => 
                  "Vaše údaje byly zapsány, děkujeme za vložení údajů.",
              'CMS_submit_3'  => 
                  "Vaše údaje byly zapsány, děkujeme za vzkaz pro pořadatele.",
              'CMS_submit_4'  => 
                  "Vaše údaje byly zapsány, Vaše přihláška již byla dříve evidována.",
              'CMS_submit_5'  => 
                  "Vaše přihláška na akci byla zaevidována a byl Vám poslán potvrzující mail.",
              'CMS_submit_error_2'  => 
                  "Při zpracování opravených údajů bohužel došlo k chybě (2).",
              'CMS_submit_error_4'  => 
                  "Při zpracování vkládaných údajů bohužel došlo k chybě (4).",
              'CMS_submit_error_3'  => 
                  "Při zpracování přihlášky bohužel došlo k chybě (3).",
              'cms_submit_missing'  => 
                  "<span class='problem'>Vyplňte prosím správně chybějící položky.</span>",
              'cms_submit_bad_date'  => 
                  "<span class='problem'>Zadejte prosím datum ve tvaru den.měsíc.rok (d.m.rrrr)</span>",
              'cms_error'=>
                  "Při zpracování formuláře došlo bohužel k chybě, selhalo spojení se serverem" ,
              'cms_send_mail_error'=>
                  "Při zpracování formuláře došlo bohužel k chybě, selhalo spojení se serverem" ,
              'cms_IE_forbiden'=>
                  "Online přihlášení lze použít pouze z prohlížečů Chrome, Firefox, Edge" 
            ),
            'ELEM'=>array(
              'Ojmeno'    => array('t','+','jméno',190), 
              'Oprijmeni' => array('t','+','příjmení',160),
              'Onarozeni' => array('d','-','narození',68),
              'Oulice'    => array('t','*','ulice',140), 
              'Opsc'      => array('t','*','psč',40), 
              'Oobec'     => array('t','*','obec',158),
              'Oknez'     => array('c','-','jsem kněz'),
              'Rpoznamka' => array('t','-','poznámka pro pořadatele',435,50)
            ),
            'SQL'=>array(
              'O'=>array('lidi','id_lidi','mail'),
              'A'=>array('akce','id_akce'),
              'R'=>array('na','id_na','id_lidi','id_akce'),
              'mail'=>array("feb","SELECT id_lidi FROM lidi WHERE !deleted AND mail='{MAIL}'"),
              'select_O'=>array("feb","SELECT {*} FROM lidi WHERE !deleted AND id_lidi={IDO}"),
              'select_R'=>array("feb","SELECT {*} FROM na WHERE id_lidi={IDO} AND id_akce={IDA}"),
              'select_A'=>array("feb","SELECT {*} FROM akce WHERE id_akce={IDA}"),
              'Ochange'=>'web_change',
              'Rchange'=>'web_change',
              ''=>''
            ),
            'CALL'=>array(
              'sendmail_OA'=> "cms_send_potvrzeni",     // parametry mail,IDO,IDA
              'sendmail' => "feb_mail_send"             // par: email,subj,body,reply => {ok,msg}
            )
          ),
          // přihlášení do CMS
          'login'=>array(
            'sql_mail'=>"SELECT id_user FROM _user WHERE mail='{mail}'",
            'LOGIN'=>"login_by_mail({IDO})"  
          )
        )
      )
    );
  
  // databáze
  $deep_root= "../files/feb";
  require_once("$deep_root/feb.dbs.php");
  
  // archiv sql
  $path_backup= "$deep_root/sql";
  
  $tracked= ',clen,dar,projekt,ukol,dopis,role,_user,_cis,';
  
  // PHP moduly aplikace FEB
  $app_php= array(
      'feb/feb_fce.php',
      'feb/web_fce.php',
      "ezer$ezer_version/server/ezer_cms3.php");
  
  // PDF knihovny
  require_once('tcpdf/tcpdf.php');

  // stará verze json
  require_once("ezer$ezer_version/server/licensed/JSON_Ezer.php");

  // je to aplikace se startem v rootu
  chdir($_SESSION[$ezer_root]['abs_root']);
  require_once("{$EZER->version}/ezer_ajax.php");

  // specifické cesty
  global $ezer_path_root;

  $path_www= './';
?>
