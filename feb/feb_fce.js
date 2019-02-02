/* global CKEDITOR, Ezer */

// =========================================================================================> COMMON
// -------------------------------------------------------------------------------------- $
function $() {
  // mootools relikt
  Ezer.fce.error("MooTools $-call");
  return 1;
}
jQuery.fn.extend({
  // ------------------------------------------------- + scrollIntoViewIfNeeded
  Ezer_scrollIntoView: function() {
    var target= this[0];
    let rect = target.getBoundingClientRect(),
        bound= this.parent()[0].getBoundingClientRect();
    if (rect.bottom > bound.bottom) {
        target.scrollIntoView(false);
    }
    else if (rect.top < bound.top) {
        target.scrollIntoView(true);
    }
  }
});
// -------------------------------------------------------------------------------------- jump fokus
// nastaví polohu stránky
// zamění <span style='neodkaz'> na alert
function jump_fokus(fe_level) {
  // najdi cíl podle priority
  var jump= jQuery('#fokus_part') || jQuery('#fokus_case') || jQuery('#fokus_page');
  if ( jump[0] ) {
//    jump.Ezer_scrollIntoView();
    jump[0].scrollIntoView(true);
  }
  if ( fe_level || Ezer && Ezer.web && Ezer.web.fe_user ) {
    // zruší barevné označené odkazů pro nepřihlášené
    jQuery('span.neodkaz').removeClass('neodkaz')
  }
  else {
    // zamění <span style='neodkaz'> na alert
    jQuery('span.neodkaz a').prop('href','#');
    jQuery('span.neodkaz').prop('href','#').on('click',() => {
      jQuery('div.neodkaz').fadeIn();
    })
  }
  return 1;
}
// ----------------------------------------------------------------------------------------- noadmin
// zpřístupní ladící a administrátorské prvky pro a=1 znepřístupní pro a=0
function admin(a) {
//  let state= a ? 'block' : 'none',
//      margin= a ? '30px' : '0px';
//  jQuery('div.admin').css({display:state});
//  jQuery('div.cms_page').css({top:margin});
//  var logo=  jQuery('#logo'),
//      work=  jQuery('#work'),
//      dolni= jQuery('#dolni');
//  if ( !a ) work.css({height:'inherit'});
//  if ( logo ) logo.css({zIndex:a?99999:0});
//  if ( dolni ) dolni.css({display:a && Ezer.options.to_trace ?'block':'none'});
  return 1;
}
// ---------------------------------------------------------------------------------------------- go
// předá CMS info na kterou stránku webu přepnout
function go(e,href,mref,input,nojump) {
  if ( e ) e.stopPropagation();
  nojump= nojump||0;
  var url, http, page, u= href.split('page=');
  if ( u.length==2 ) {
    http= u[0];
    page= u[1].split('#');
    page= page[0];
  }
  else {
    http= u;
    page= 'home';
  }
  if ( input ) {
    // go je voláno přes <enter> v hledej
    var search= $('search').value;
    document.cookie= 'web_search='+search+';path=/';
    page= page + '!!'+ search;
  }
//  history.pushState({},'',mref ? mref : http+'page='+page);
  Ezer.run.$.part.feb.part.ora.part.web.part.p._call(0,nojump?'cms_menu':'cms_go',page)
  return false;
}
// ===========================================================================================> AJAX
// ------------------------------------------------------------------------------------------- error
function error(x) {
  alert(x);
}
// --------------------------------------------------------------------------------------------- ask
// ask(x,then): dotaz na server se jménem funkce po dokončení
function ask(x,then,arg) {
  var xx= x;
  jQuery.ajax({url:'index.php', data:x, method: 'POST',
    success: function(y) {
      if ( typeof(y)==='string' )
        error(`Došlo k chybě 1 v komunikaci se serverem - '${xx.cmd}'`);
      else if ( y.error )
        error(`Došlo k chybě 2 v komunikaci se serverem - 'y.error'`);
      else if ( then ) {
        then.apply(undefined,[y,arg]);
      }
    },
    error: function(xhr) {
      error("Došlo k chybě 3 v komunikaci se serverem");
    }
  })
}
// -------------------------------------------------------------------------------------==> CKEditor
CKEDITOR.plugins.add('ezer', {
  requires: 'widget,filetools',
  init: function (editor) {
    var max_size= 800,
       theIMG, loadingImage=
    'data:image/gif;base64,R0lGODlhDgAOAIAAAAAAAP///yH5BAAAAAAALAAAAAAOAA4AAAIMhI+py+0Po5y02qsKADs=';
    // ---------------------------------------------- rotace obrázku
    editor.addMenuItems({
      ezer_rotate_l: {label:'Otočit 90° doleva',  command:'ezer_rotate_l',group:'image',order:2},
      ezer_rotate_r: {label:'Otočit 90° doprava', command:'ezer_rotate_r',group:'image',order:3},
      ezer_rotate_s: {label:'Otočit 180°',        command:'ezer_rotate_s',group:'image',order:4}
    });
    editor.contextMenu.addListener(function (element,selection) {
      theIMG= 0;
      if ( element && element.$.nodeName=='IMG' )
        theIMG= element;
      else {
        var imgs= element.getElementsByTag('IMG').$;
        if ( imgs.length ) theIMG= imgs[0];
      }
      if ( theIMG ) { return {
        ezer_rotate_l:CKEDITOR.TRISTATE_OFF,
        ezer_rotate_r:CKEDITOR.TRISTATE_OFF,
        ezer_rotate_s:CKEDITOR.TRISTATE_OFF
      }}
    });
    editor.addCommand('ezer_rotate_l', { exec: function (editor) {
      var src= theIMG.getAttribute('src');
      ask({cmd:'cke',cke:'img_rotate',src:src,deg:90},imageback);
    }});
    editor.addCommand('ezer_rotate_r', { exec: function (editor) {
      var src= theIMG.getAttribute('src');
      ask({cmd:'cke',cke:'img_rotate',src:src,deg:-90},imageback);
    }});
    editor.addCommand('ezer_rotate_s', { exec: function (editor) {
      var src= theIMG.getAttribute('src');
      ask({cmd:'cke',cke:'img_rotate',src:src,deg:180},imageback);
    }});
    var imageback= function(y) {
      if ( y.ok ) {
        var W= theIMG.$ ? theIMG.$.naturalWidth  : theIMG.width;  // theIMG.getAttribute('width'),
            H= theIMG.$ ? theIMG.$.naturalHeight : theIMG.height; // theIMG.getAttribute('height');
        if ( y.deg!=180 ) {
          theIMG.setAttribute('width',H);
        }
        theIMG.setAttribute('src',y.src);
        theIMG.setAttribute('data-cke-saved-src',y.src);
        editor.fire('change');
      }
    };
    // ---------------------------------------------- vložení obrázku
    editor.on('paste', function (evt) {
      // nalezení instance Ezer.EditHtml
      var EditHtml= jQuery(editor.element.$.parentNode).data('ezer'), 
          LabelDrop= 0, ok= 1;
      ok= EditHtml.label_drop!=undefined;
      if ( ok ) {
        LabelDrop= EditHtml.label_drop;
        editor.widgets.add( 'ezer', {
          allowedContent: 'img[src]',
          requiredContent: 'img',
          pathName: 'ezer'
        });
        // This feature does not have a button, so it needs to be registered manually.
        editor.addFeature(editor.widgets.registered.ezer);
        ok= evt.data.dataTransfer.getFilesCount();
      }
      else Ezer.fce.error("CKEditor - chybí provázání EditHtml s LabelDrop!");
      if ( ok ) {
        var data= evt.data.dataTransfer.getData('img');
        var file= evt.data.dataTransfer.getFile(0);
        // dále budeme zpracovávat jen obrázky
        ok= file.type.substr(0,5)=='image';
        if ( !ok ) Ezer.fce.warning("přetažením do okna editoru lze vkládat jen obrázky");
      }
      if ( ok ) {
        // pokud existuje ondrop, zavolej a pokračuj jen pokud vrátí 1
        if ( EditHtml.part['ondrop'] ) {
          ok= EditHtml._call(0,'ondrop',file);  // uživatelská funkce ondrop pokud vrátí 0 končíme
        }
      }
      if ( ok ) {
        var loader= editor.uploadRepository.create(file );
        loader.on( 'loaded', function(evt) {
          if ( ok ) {
            LabelDrop.onUploaded= function(file) {
              theIMG.setAttribute('src',file.folder+'/'+file.name);
              theIMG.setAttribute('data-cke-saved-src',file.folder+'/'+file.name);
            }.bind(this);
            LabelDrop.DOM_addFile(file);
            Resample2(this.data,max_size,function(data64){ // výstup je base64
              theIMG= this._.events.loaded.$;
              theIMG.setAttribute('src', data64);
              file.data= dataURItoBlob(data64);
              LabelDrop.DOM_upload(file,1);
              // záměna src za cestu na serveru
              file.orig= 'drop';
            }.bind(this));
          }
        });
        var element= editor.document.createElement('img',{attributes:{src:loadingImage}} );
        editor.insertElement(element);
        var widget= editor.widgets.initOn(element);
        loader.define('loaded',element);
        loader.load();
      }
    });
  }
});
// --------------------------------------------------------------------------------------- Resample2
// http://stackoverflow.com/questions/18922880/html5-canvas-resize-downscale-image-high-quality
//   - hledat Hermite resize - je tam i Update: version 2.0
//     (faster, web workers + transferable objects) - https://github.com/viliusle/Hermite-resize
function Resample2(uri, size, onresample) {
  var canvas = this.document.createElement("canvas");
  var ctx = canvas.getContext("2d");
  var img = new Image();
  img.src= uri;
  img.onload = function(){
    var W = img.width;
    var H = img.height;
    if ( W>=size || H>=size ) {
      canvas.width = W;
      canvas.height = H;
      ctx.drawImage(img, 0, 0); //draw image
      // resize
      if ( W>H ) { H= (size/W)*H; W= size; }
      else { W= (size/H)*W; H= size; }
      resample_single(canvas, W, H, onresample);
    }
    else {
      onresample(uri);
    }
  };
}
/**
 * Hermite resize - fast image resize/resample using Hermite filter. 1 cpu version!
 */
function resample_single(canvas, width, height, onresample) {
  var resize_canvas= true;
  var width_source = canvas.width;
  var height_source = canvas.height;
  width = Math.round(width);
  height = Math.round(height);

  var ratio_w = width_source / width;
  var ratio_h = height_source / height;
  var ratio_w_half = Math.ceil(ratio_w / 2);
  var ratio_h_half = Math.ceil(ratio_h / 2);

  var ctx = canvas.getContext("2d");
  var img = ctx.getImageData(0, 0, width_source, height_source);
  var img2 = ctx.createImageData(width, height);
  var data = img.data;
  var data2 = img2.data;

  for (var j = 0; j < height; j++) {
    for (var i = 0; i < width; i++) {
      var x2 = (i + j * width) * 4;
      var weight = 0;
      var weights = 0;
      var weights_alpha = 0;
      var gx_r = 0;
      var gx_g = 0;
      var gx_b = 0;
      var gx_a = 0;
      var center_y = (j + 0.5) * ratio_h;
      var yy_start = Math.floor(j * ratio_h);
      var yy_stop = Math.ceil((j + 1) * ratio_h);
      for (var yy = yy_start; yy < yy_stop; yy++) {
        var dy = Math.abs(center_y - (yy + 0.5)) / ratio_h_half;
        var center_x = (i + 0.5) * ratio_w;
        var w0 = dy * dy; //pre-calc part of w
        var xx_start = Math.floor(i * ratio_w);
        var xx_stop = Math.ceil((i + 1) * ratio_w);
        for (var xx = xx_start; xx < xx_stop; xx++) {
          var dx = Math.abs(center_x - (xx + 0.5)) / ratio_w_half;
          var w = Math.sqrt(w0 + dx * dx);
          if (w >= 1) {
            //pixel too far
            continue;
          }
          //hermite filter
          weight = 2 * w * w * w - 3 * w * w + 1;
          var pos_x = 4 * (xx + yy * width_source);
          //alpha
          gx_a += weight * data[pos_x + 3];
          weights_alpha += weight;
          //colors
          if (data[pos_x + 3] < 255)
            weight = weight * data[pos_x + 3] / 250;
          gx_r += weight * data[pos_x];
          gx_g += weight * data[pos_x + 1];
          gx_b += weight * data[pos_x + 2];
          weights += weight;
        }
      }
      data2[x2] = gx_r / weights;
      data2[x2 + 1] = gx_g / weights;
      data2[x2 + 2] = gx_b / weights;
      data2[x2 + 3] = gx_a / weights_alpha;
    }
  }
  //clear and resize canvas
  if (resize_canvas === true) {
    canvas.width = width;
    canvas.height = height;
  } else {
    ctx.clearRect(0, 0, width_source, height_source);
  }
  //draw
  ctx.putImageData(img2, 0, 0);
  // retrieve the canvas content as base64 encoded image and pass the result to the callback
  onresample(canvas.toDataURL("image/jpeg"));
}
