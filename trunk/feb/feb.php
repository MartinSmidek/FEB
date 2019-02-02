<?php

  // volba verze jádra Ezer
  $kernel= "ezer".(isset($_GET['ezer'])?$_GET['ezer']:'3.1'); 

  // rozlišení lokální a ostré verze
  $ezer_local= preg_match('/^\w+\.bean$/',$_SERVER["SERVER_NAME"])?1:0;

  // parametry aplikace FEB
  $app_name=  'evangelizační buňky';
//  $app_login= 'Guest/';                   // pouze pro automatické přihlášení
  $app_root=  'feb';
  $skin=      'ck';
  $app_js=    array("/feb/feb_fce.js","/$kernel/client/ezer_cms3.js");
  $app_css=   array("/feb/css/feb.css","/feb/css/edit.css","/$kernel/client/ezer_cms3.css");
  $abs_roots= array("/home/users/gandi/ezer.cz/web/feb","C:/Ezer/beans/feb");
  $rel_roots= array("http://feb.ezer.cz","http://feb.bean:8080");
  $kontakt=   "V případě zjištění problému nebo <br/>potřeby konzultace mi prosím napište<br/>
      na mail martin<i class='fa fa-at'></i>smidek.eu případně zavolejte 306&nbsp;150&nbsp;565
      <br/>Za spolupráci děkuje <br/>Martin Šmídek";
  
  $add_options= (object)array(
    'mini_debug' => 1,
    'path_files_href' => "'$rel_roots[$ezer_local]'",
    'path_files_s' => "'$abs_roots[$ezer_local]/'"  // absolutní cesta pro přílohy
  );
  $add_pars= array(
    'CKEditor' => "{
      version:'4.6',
      FEB:{
        skin:'moono-lisa',
        toolbar:[['Maximize','Styles','-','Bold','Italic','TextColor','BGColor', 'RemoveFormat',
          '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Outdent', 'Indent', 'Blockquote',
          '-','NumberedList','BulletedList','Table',
          '-','Link','Unlink','HorizontalRule','Image','Embed',
          '-','Source','ShowBlocks','RemoveFormat']],
        // Configure the Enhanced Image plugin to use classes instead of styles and to disable the
        // resizer (because image size is controlled by widget styles or the image takes maximum
        // 100% of the editor width).
        image2_alignClasses: [ 'image-align-left', 'image-align-center', 'image-align-right' ],
        image2_disableResizer: false,
        extraPlugins:'widget,filetools,embed,ezer',
        entities:true,  // →
        embed_provider: '//iframe.ly/api/oembed?url={url}&callback={callback}&api_key=313b5144bfdde37b95c235',
        uploadUrl:'feb/upload.php?root=feb&type=Images',
        stylesSet:[
          {name:'název',     element:'h1'},
          {name:'nadpis',    element:'h2'},
          {name:'podnadpis', element:'h3'},
          {name:'odstavec',  element:'p'},
          {name:'odstavec!', element:'p',   attributes:{'class':'p-clear'}}
        ],
        contentsCss:'feb/css/edit.css'
      }
    }"
  );
  // je to standardní aplikace se startem v kořenu
  require_once("$kernel/ezer_main.php");

?>
