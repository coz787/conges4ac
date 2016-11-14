/* -*-mode: Javascript; coding: utf-8; -*-
 * admin_mod.js 
 * client-side functions of use by admin mod of php_conges
 * 
 * under LGPL by d.pavet / DGAC 
 */
var oconfig = {"msg_nouveau_solde": " comme nouveau solde, pensez à valider " ,
               "msg_hp_controle": "nombre de jours hors période est entre 0 et 2" }; 

var oconfigartt = {"user":"simon.adalbert","date_deb_grille_actu":"2016-10-01",
                   "smindate":"", "smaxdate":"" } ; 
var dconsole$ ; 
var oschemeartt = { "dmindate": null, 
                    "dmaxdate": null } ; 

function say(mytext) {
    stext = "at: " + mynow() + ": " + mytext + "<br>"; 
    if (dconsole$) {
        dconsole$.append(stext) ;
    }
}

function admin_mod_ready() { 
    fbmessage$ = $('#fbmessage') ;
    fbmessage$.addClass("ui-fbmessage") ; 
    jhpvalid_btn$ = $('#jhpvalid'); 
    newjhp_input$ = $('#newjhp') ;
    actualjhp_div$ = $('#actualjhp'); 
    jhpsolde_input$ = $('#jhpsolde');
    pnum$ = $('#p_num') ; // $('[name="new_demi_jour_deb"]')
    hpmod$ = $('#hpmod') ;
    soconfig$ = $('#oconfig');
    if (soconfig$) {
        try {
            oconfig = JSON.parse(soconfig$.text());
        } catch(e){ 
            smessage = "admin_mod_ready::error in parsing oconfig " + e  ;
            console.log(smessage); // alert(e); popup message 
        }
    };     
    $('#jhpvalid').click(cb_jhpval);
    soconfigartt$ = $('#oconfigartt');
    if (soconfigartt$) {
        try {
            oconfigartt = JSON.parse(soconfigartt$.text());
        } catch(e){ 
            smessage = "admin_mod_ready::error in parsing oconfigartt " + e  ;
            console.log(smessage); // alert(e); popup message 
        }
    };     

    /* _admin_mod_artt_ */ 
    oschemeartt.dmindate = $.datepicker.parseDate("yy-mm-dd",oconfigartt.smindate) ;
    oschemeartt.dmaxdate = $.datepicker.parseDate("yy-mm-dd",oconfigartt.smaxdate) ;
    
    $('#dpickernewscheme').datepicker({ 
        altField: "#newschemed" ,
        showButtonPanel: true,
        showWeek: true, 
        dateFormat: "yy-mm-dd",
        beforeShowDay: cb_highlightschemeday ,  
        /* pas indispensable mais apporte la bulle de commentaire */ 
        minDate:  oschemeartt.dmindate ,
        maxDate:  oschemeartt.dmaxdate , 
    });
 
/*    $('#dpickernewscheme').datepicker();   {   
        showButtonPanel: true,
        showweek: true, 
        dateFormat: "yy-mm-dd", 
        beforeShowDay: cb_highlightschemeday ,
        onSelect: cb_onselectschemeday }); 
*/ 
}


function cb_jhpval() {
    newjhp = parseFloat(newjhp_input$.val());

    if (newjhp >= 0 && newjhp <= 2) {
        newsolde = newjhp - parseFloat(actualjhp_div$.text()) + 
            parseFloat(jhpsolde_input$.val()) ; 
        // place nouveau solde des conges normaux 
        jhpsolde_input$.val(newsolde.toString()) ;
        // highlight des champs solde et jourhpsolde ET readonly 
        jhpsolde_input$.addClass("ui-inputhl") ;
        newjhp_input$.addClass("ui-inputhl") ;
        jhpsolde_input$.attr('readonly', true) ;
        newjhp_input$.attr('readonly', true) ;
        // maj du mode uniquement qd les données sont bonnes 
        if (pnum$.val() == "" ) {
            hpmod$.val('cre') ;
        } else {
            hpmod$.val('mod') ;
        }

        // msg proposant de valider 
        valmsg = newsolde.toString() + oconfig.msg_nouveau_solde  ; 
        fb_msg(0, valmsg);
        // masquage bouton saisie et disparition 
        jhpvalid_btn$.attr('disabled',true);
        jhpvalid_btn$.hide('slow');
        
    } else { 
        fb_msg(2,oconfig.msg_hp_controle );  
        return ; 
    }

}

function fb_msg(wlevel,wtext) { // wlevel 0:ok, 1:normal message, 2:erreur saisie 
    if (wlevel==2) {
        fbmessage$.removeClass("ui-fbmessage").addClass("ui-fbmessage-error") ; 
    } else {
        fbmessage$.removeClass("ui-fbmessage-error").addClass("ui-fbmessage") ; 
    }; 
    fbmessage$.text(wtext);
}
function cb_onselectschemeday(dateText, inst) {
    say("cb_onselect " + inst.id + " " + dateText );
}
function cb_highlightschemeday(date) {
    if ( date < oschemeartt.dmindate ) { 
        return [false,'','date avant le début de la grille actuelle'] ;
    } else if ( date > oschemeartt.dmaxdate ) {
        return [false,'','date trop anticipée'] ;
    } else {
        return [true,'','date sélectionnable'] ; 
    } 
}
