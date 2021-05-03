/* global Ezer */
// ===========================================================================================> SKUP
// -------------------------------------------------------------------------------------- ondomready
var panel, label, geo;
//function ondomready() {
//  skup_mapka();
//}
var code= {
  "app": {
    "part": {
      "x": {
        "options": {"css":"mapa"},
        "type": "panel.main",
        "part": {
          "f": {
            "options": {"css":"mapa"},
            "type": "var", "_of": "form", "_init": "$.x._f"
          },
          "_f": {
            "type": "form",
            "part": {
              "l": {
                "options": {"css":"mapa"},
                "type": "label.map"
              }
            }
          }
        }
      }
    }
  }
};
// -------------------------------------------------------------------------------------- skup mapka
function skup_mapka() {
  if ( typeof(Ezer)=='undefined' || !Ezer.App ) return;
  
  let mapa= jQuery('#mapa');
  label= jQuery('div.cms_mapa');
  if ( label[0] ) {
    label.data('ezer').DOM_Block= mapa;
    label.css({display:'block'});
    label= label.data('ezer');
  }
  else {
    jQuery('#skup0').css({display:'block'});
    Ezer.App.load_root(code);
    panel= Ezer.run.$.part.x;
    label= panel.part.f.value.part.l;
    label.DOM_Block= mapa;
  }
  label.part= {
    onmarkclick: function(mark) {
      skup_dialog(mark);
  }};
  label.init('ROADMAP');
  ask({cmd:'mapa',mapa:'bunky'},skup_mapka_);
}
function skup_mapka_(y) {
  if ( y && y.mapa ) {
    geo= {ezer:'PSČ',mark:y.mapa.mark};
    label.set(geo);
  }
}
// ---------------------------------------------------------------------------------- skup mapka_off
// používá se jen v CMS
function skup_mapka_off() {
  label= jQuery('div.cms_mapa');
  label.css({display:'none'});
}  
// ------------------------------------------------------------------------------------- skup dialog
function skup_dialog(mark) {
  var mark_json= JSON.stringify({id:mark.id,title:mark.title});
//  jQuery('#skup0').css({display:'none'});
//  jQuery('#skup2').css({display:'none'});
  jQuery('#popis').css({display:'block'}).html(
    mark.title
//    mark.title+"<div><a class='jump' onclick='skup_dialog2("+mark_json+");'> \
//    <span>Chci se zeptat organizátorů</span></a></div>"
  );
}
// ------------------------------------------------------------------------------------- skup dialog
function skup_dialog2(mark) {
//  jQuery('#skup1').css({display:'none'});
//  jQuery('#skup2').css({display:'block'}).html(
//      mark.title
//    + "<div>"
//    + "  <input class='skup_x' type='text' id='skup_from' placeholder='tvůj email'>"
//    + "  <textarea class='skup_x' id='skup_body' placeholder='dotaz na organizátory'></textarea>"
//    + "<a class='skup_x jump' onclick=\"skup_sendmail('"+mark.id+"','"+mark.title+"');\">Poslat mail</a>"
//    + "<a class='skup_x jump' onclick=\"jQuery('#skup2').css({display:'none'});\">Zpět</a>"
//    + "<div id='skup_msg'></div>"
//    + "</div>"
//  );
}
// ===========================================================================================> AJAX
// --------------------------------------------------------------------------------------------- ask
// ask(x,then): dotaz na server se jménem funkce po dokončení
function ask(x,then,arg) {
  var xx= x;
  jQuery.ajax({url:Ezer.web.index, data:x, method: 'POST',
    success: function(y) {
      if ( typeof(y)==='string' )
        error("Došlo k chybě 1 v komunikaci se serverem - '"+xx.cmd+"'");
      else if ( y.error )
        error("Došlo k chybě 2 v komunikaci se serverem - '"+y.error+"'");
      else if ( then ) {
        then.apply(undefined,[y,arg]);
      }
    },
    error: function(xhr) {
      error("Došlo k chybě 3 v komunikaci se serverem");
    }
  })
}
// ------------------------------------------------------------------------------------------- error
function error(msg) {
  alert(msg + " pokud napises na martin@smidek.eu pokusim se pomoci, Martin");
}
