<?php # (c) 2017 Martin Smidek <martin@smidek.eu>

  $ezer_root= 'feb';

  /// viz \ref struktura-EZER.CMS
  
  $EZER= (object)array(
      // inicializace objektu EZER pro aplikace FEB
      'version'=>$_SESSION['feb']['ezer'],
      'options'=>(object)array(),
      'activity'=>(object)array(),
      'CMS'=>(object)array(
        // pro různé testovací účely  
        'TEST'=>0,
        /// informace o přístupu na gmailový účet  
        'GMAIL'=>(object)array(
          /// přístup na použitý gmail
          'mail'=>'web.evangelizacni.bunky@gmail.com',
          'pswd'=>'meditacnizahrada',
          'name'=>'Farní evangelizační buňky'
        ),
        'FORM'=>array(
          // přihláška na seminář - bez kontaktu na rodiče
          'seminar'=>array(
            'TYPE'=>array('allow_unknown','confirm'),
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
                  "Byl vám poslán mail s PINem ...",
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
                  "Vaše údaje byly zapsány, Vaši přihlášku jsme vzali na vědomí.",
              'CMS_submit_error_1'  => 
                  "Na tento seminář už jste přihlášen.",
              'cms_submit_missing'  => 
                  "<span class='problem'>Vyplňte prosím správně chybějící položky.</span>",
              'cms_error'=>
                  "Při zpracování formuláře došlo bohužel k chybě, selhalo spojení se serverem" ,
              'cms_IE_forbiden'=>
                  "Online přihlášení lze použít pouze z prohlížečů Chrome, Firefox, Edge" 
            ),
            'ELEM'=>array(
              'Ojmeno'    => array('t','*','jméno',190), 
              'Oprijmeni' => array('t','*','příjmení',160),
              'Onarozeni' => array('d','-','narození',68),
              'Oulice'    => array('t','*','ulice',177), 
              'Opsc'      => array('t','*','psč',82), 
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
              'Ochange'=>'web_change',
              'Rchange'=>'web_change',
              ''=>''
//            ),
//            'CALL'=>array(
//              'confirm_O'=> "cms_confirm"       // parametry IDO,IDA
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

  /// \cond 
  // přístup k databázi pro aplikaci FEB
  $mysql_db_track= '_track';
  $tracked= $mysql_tracked= ",lidi,fara,cell,akce,pack,na,ma,je,ve,go,_user,";
  $db= array('feb','feb');
  $dbs= array(
    array(  // ostré
      'feb'           => array(0,'localhost','gandi','radost','utf8'),
      'ezer_system'   => array(0,'localhost','gandi','radost','utf8','feb'),
      'ezer_kernel'   => array(0,'localhost','gandi','radost','utf8','feb')
      ),
    array(  // lokální
      'feb'           => array(0,'localhost','gandi','','utf8'),
      'ezer_system'   => array(0,'localhost','gandi','','utf8','feb')),
      'ezer_kernel'   => array(0,'localhost','gandi','','utf8')
  );
  
  $ezer_db= $dbs[$ezer_local];
  $mysql_db= $db[$ezer_local];
  /// \endcond

?>
