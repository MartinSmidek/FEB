<?php # styly pro jádro Ezer verze 3  (c) 2010 Martin Smidek <martin@smidek.eu>
header("Content-type: text/css");
// ch: barvy webu www.chlapi.cz
  $path= "../../skins/ch";                     // cesta k background-image
  $bila= '#ffffff'; $cerna= '#000000';            // základní barvy
  // barvy specifické pro styl
  $nasedla= '#e6e6e6'; $seda= '#4d4d4d';
  $cervena= '#a90533'; $oranzova= '#ef7f13';  $lososova= '#F0E2C2';
  $zelena= '#2c4989'; $nazelenala= '#365faf'; $zelenkava= '#c0cae2'; $zelenoucka= '#EFF1FD';
  $zelenkava= '#97bef7'; // úprava Dolany/ch
  // prvky - musí být v global
  $c= $cerna; $b= $nasedla; $ab= $bila;
  $c_appl= $zelena;
  $c_menu= $bila; $b_menu= $seda;
  $c_main= $zelena; $b_main= $seda;
  $c_group= $bila; $b_group= $nazelenala; $s_group= $seda;
  $c_item= $seda; $b_item= $zelenkava; $bd_item= '#ddd'; $fb_item= $oranzova; $fc_item= $bila;
  $s_item= $s2_item= $seda;
//   $b_brow= '#ccc'; $b2_brow= $lososova; $b3_brow= $bila; $b4_brow= $zelenkava;
  $b_brow= '#ccc'; $b2_brow= $bila; $b3_brow= $bila; $b4_brow= $nasedla;
    $b5_brow= $nasedla; $b6_brow= $nasedla; $b7_brow= $zelenkava; $b8_brow= $zelenoucka;
    $c_brow= $seda; $s1_brow= $nazelenala; $s2_brow= $cervena;
  $c_kuk= $zelena; $c2_kuk= $bila; $c3_kuk= $cerna; $b_kuk= $oranzova; $s_kuk= $oranzova;
  $b_warn= '#eef2ae'; $c_warn= '#000000';
  $b_doc_modul= $oranzova; $b_doc_menu= $zelena; $b_doc_form= $zelena;
  $b_parm= $oranzova; $b_part= $zelenkava; $b_work= $zelenkava;
  // úpravy ezer.css.php
  $w_right= 750;        // šířka panel.right
echo <<<__EOD

/* --------------------------------------------------------------------------------------==> FEB */

 .rel { background-color:#afe5e5; z-index:0; border-radius:5px; }
 .parm_off { background-color:transparent !important; }

/* --------------------------------------------------------------------------------------==> skin */

/* rámečky formulářů */

.ch.work { background-color:$b_work; z-index:0; border-radius:5px; }
.ch.parm { background-color:$b_parm; border:1px solid #f5f5f5; z-index:0; border-radius:5px; }
.ch.karta { background:$b_group url($path/doc_menu.gif) no-repeat left center; color:$c_group; overflow:hidden;
  font-size:14px; font-weight:bold; margin:2px 0; padding:5px 50px; clear:both; white-space:nowrap;
}

/* ------------------------------------------------------------------------------------==> Browse */

.ch .BrowseSmart table {
  border-spacing:0px;
  z-index:2; position:absolute; empty-cells:show; padding:0; width:0;
  margin:0; table-layout:fixed; border-collapse:separate; background-color:$b_brow;
  font-size:9pt; border:1px solid $s1_brow; overflow:hidden; }
/* hlavička */
.ch .BrowseSmart td.th {
  background:url($path/browse_header.png) repeat-x center -1px;
  color:$c_brow; font-size:9pt; font-weight:bold; vertical-align:middle; cursor:default;
  height:17px; line-height:12px; text-align:left; overflow:hidden; padding:0; text-indent:5px; }
.ch .BrowseSmart td.ShowSort:hover {
  background:transparent url($path/browse_sort_hover.png) repeat-x scroll 0 -1px !important;  }
/* dotazy */
.ch .BrowseSmart td.BrowseNoQry {
  padding:0; background-color:$b6_brow;
  border-left:1px solid $b_brow; border-bottom:1px solid $b_brow;  }
.ch .BrowseSmart td.BrowseQry {
  padding:0; padding:0 !important;
  border-left:1px solid $b_brow; border-bottom:1px solid $b_brow;
  vertical-align:top; }
.ch .BrowseSmart .BrowseQry input {
  background-color:$b8_brow; border:0; padding:0px; width:100%; height:16px; font:inherit;
  line-height:14px; margin:-1px 0; }
/* řádky */
.ch .BrowseSmart td {
  white-space:nowrap; overflow:hidden;
  vertical-align:bottom; cursor:default; padding:0 2px; /*line-height:14px;*/
  border-left:1px solid $b_brow; border-bottom:1px solid $b_brow;  }
.ch .BrowseSmart td.tag0 {
  background-color:$b7_brow; padding:0; }
.ch .BrowseSmart td.tag1 {
  background-image:url($path/browse_mark2.gif); width:10px; }
.ch .BrowseSmart td.tr-even {
  background-color:$b2_brow; }
.ch .BrowseSmart td.tr-odd {
  background-color:$b4_brow; }
.ch .BrowseSmart .tr-form {
  font-weight:bold; }
.ch .BrowseSmart .tr-sel {
  color:$s2_brow !important; }
.ch .BrowseSmart td.BrowseNoClmn {
  padding-left:0; border-left:0; width:0; }
/* reload */
.ch .BrowseSmart td.BrowseReload {
  background:url($path/browse_reload.png) no-repeat !important; cursor:pointer !important;
  padding:0; width:8px; }
/* posuvník */
.ch .BrowseSmart td.BrowseSet {
  background:url($path/browse_set.png) no-repeat !important; cursor:pointer !important;
  padding:0; width:16px; }
.ch .BrowseSmart div.BrowsePosuv {
  z-index:1; width:16px; background-color:$b5_brow; }
.ch .BrowseSmart .BrowseUp, .ch .BrowseSmart .BrowseDn, .ch .BrowseSmart .BrowseHandle {
  height: 16px; width: 15px;
  background-color:$b5_brow; background-repeat:no-repeat; background-position:center; }
.ch .BrowseSmart .BrowseUp       { background-image:url($path/browse_pgup0.png); }
.ch .BrowseSmart .BrowseUp.act   { background-image:url($path/browse_pgup.png); }
.ch .BrowseSmart .BrowseUp.act:hover { background-image:url($path/browse_pgup_act.png); }
.ch .BrowseSmart .BrowseDn       { background-image:url($path/browse_pgdn0.png); }
.ch .BrowseSmart .BrowseDn.act   { background-image:url($path/browse_pgdn.png); }
.ch .BrowseSmart .BrowseDn.act:hover { background-image:url($path/browse_pgdn_act.png); }
.ch .BrowseSmart .BrowseHandleUp   {
  background-image:url($path/browse_handle_up.png); height:6px;
  background-position: 1px top; background-repeat: no-repeat; }
.ch .BrowseSmart .BrowseHandleMi   {
  background-image:url($path/browse_handle_mi.png); height:100%;
  background-position: 1px center; background-repeat: repeat-y; }
.ch .BrowseSmart .BrowseHandleMi:hover {
  background-image:url($path/browse_handle_act_mi.png); }
.ch .BrowseSmart .BrowseHandleDn   {
  background-image:url($path/browse_handle_dn.png); height:6px; bottom:0px;
  background-position: 1px bottom; background-repeat: no-repeat; }
.ch .BrowseSmart div.BrowseHandle:hover { background-image:url($path/browse_handle_act.png); }
/* patička */
.ch .BrowseSmart th {
  background:url($path/browse_header.png) repeat-x center center; color:$c_brow; font-size:8pt;
  height:14px; text-align:left; border:0; white-space:nowrap; overflow:hidden; cursor:default; }

__EOD;
?>
