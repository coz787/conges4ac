/* -*-mode: Javascript; coding: utf-8; -*-
 * jqcal.js 
 * jquery calendar client-side function to manager display, interaction 
 * of jquery/datepicker element 
 * under LGPL by d.pavet / DGAC 
 */

var specialDays = [];  // ["2014/2/14","2014/2/17","2014/2/19"];
var offDays = [] ; // ["2014/1/1","2014/4/22", "2014/5/1", 
                   //  "2014/7/14"]; 
var wedays = [0,6] ; 
var weexcluded = true ; 

/* object special days : to be used by cb_highlightWorkDays handler */
/* will be provided by the server in the form and processed using JSON.parse */
var ospdays = {} ; 
/* in case JSON.parse catch an except, these defaut value is used */ 

var ospdaysdefault = {"currentdate": "2015-03-09",
                      "mindate":"2015-01-01","maxdate":"2016-01-31","period":{},
                      "weekend":{"days":[0,6],"legend":"we"} } ; 

var odpicker = {"start": {"defdate": ""},
                "end": {"defdate": ""},} ; 
var osession = {"session":null,"uid":null,"rootpath":null};
var oconge = {"start" : {"date":null, "datetext":null, "part":"am" }, 
              "end" :   {"date":null, "datetext":null, "part":"pm" },
              "new_type" : -1 } ; 
/* en donnees interne le format date text est yyyy-mm-dd
   oconge.start.date en format date javascript 
   oconge.start.datetext en format datetext 2015-06-06 */ 

var oechange = {"moved" : {"datetext":null, "part":null, "inipart":null }, 
                "chosen": {"datetext":null, "part":null, "inipart":null } } ; 

var oconfig = {"by":"user" ,
               "msg_saisie_deb":"" ,
               "msg_saisie_fin":"" ,
               "msg_w_date_anterieur":"" ,
               "msg_w_ampm":"" ,
               "msg_ok_congeavalider":"" }; 
var odpi_start = {
    "showbuttonpanel": true,
    "showWeek": true,
    "altfield": "#new_debut",
    "dateformat": "yy-mm-dd", 
    "defaultdate": "",
    "beforeshowday": cb_highlightWorkDays ,
    "mindate": "",
    "maxdate": "",
    "onchangemonthyear": cb_onchangemonthyear, 
    "onselect": cb_onselect,
    "cym": null  // current yearmonth 
};
var odpi_end = {
    "showbuttonpanel": true,
    "showWeek": true,
    "altfield": "#new_fin",
    "dateformat": "yy-mm-dd",
    "defaultdate": "",
    "beforeshowday": cb_highlightWorkDays ,
    "mindate": "",
    "maxdate": "",
    "onchangemonthyear": cb_onchangemonthyear, 
    "onselect": cb_onselect, 
    "cym": null  // current yearmonth 
}; 
var odpi_moved = {
    "showbuttonpanel": true,
    "showWeek": true,
    "altfield": "#dmoved",
    "dateformat": "yy-mm-dd",
    "defaultdate": "",
    "beforeshowday": cb_highlightMovableDays ,
    "mindate": "",
    "maxdate": "",
//    "onchangemonthyear": cb_onchangemonthyear, 
    "onselect": cb_onselect_echange,
    "cym": null  // current yearmonth 
};
var odpi_chosen = {
    "showbuttonpanel": true,
    "showWeek": true,
    "altfield": "#dchosen",
    "dateformat": "yy-mm-dd",
    "defaultdate": "",
    "beforeshowday": cb_highlightWorkDays ,
    "mindate": "",
    "maxdate": "",
//    "onchangemonthyear": cb_onchangemonthyear, 
    "onselect": cb_onselect_echange, 
    "cym": null  // current yearmonth 
}; 

// types d'affichage pour la selection de jour travailles utilisÃ©s 
// par la fonction cb_highlightWorkDays
// attribut : jour selectionable, style css 
// note: par defaut les autres jours SONT selectionables 
// new z table ss 2 formats:  z-daypart-type z-daypart1-type1-daypart2-type2
// z-am-offdem-pm-
var owdaydisplay = { 
    "z-da-pubhol" : [false, 'css-day-pub-holidays'],
    "z-da-we" :     [false, ''],
    "z-da-offval" : [false, 'css-day-off'],
    "z-da-offdem" : [false, 'css-day-off-not-validated'],
    "z-da-jlp" :    [false, 'css-day-jlp'], 
    "z-am--pm-offval" :       [true, 'css-day-off-pm'],  
    "z-am--pm-offdem" :       [true, 'css-day-off-not-validated-pm'],  
    "z-am--pm-jl" :           [true, 'css-day-djlp-pm'],  
    "z-am-offval-pm-" :       [true, 'css-day-off-am'],   
    "z-am-offval-pm-offdem" : [false, 'css-day-off-am css-day-off-not-validated-pm'],   
    "z-am-offval-pm-jl" :     [false, 'css-day-off-am css-day-djlp-pm'], 
    "z-am-offdem-pm-" :       [true, 'css-day-off-not-validated-am'],   
    "z-am-offdem-pm-offval" : [false,'css-day-off-not-validated-am css-day-off-pm'  ],  
    "z-am-offdem-pm-jl" :     [false,'css-day-off-not-validated-am css-day-djlp-pm'], 
    "z-am-jl-pm-" :           [true, 'css-day-djlp-am'],  
    "z-am-jl-pm-offval" :     [false,'css-day-djlp-am css-day-off-pm' ],   
    "z-am-jl-pm-offdem" :     [false,'css-day-djlp-am css-day-off-not-validated-pm' ], }; 

// types d'affichage pour la selection de jour Ã©changeables utilisÃ©s 
// par la fonction highlightMovableDays
// attribut : jour selectionable, style css 
// note: par defaut les autres jours NE SONT PAS selectionables 

var owdayechange = {
    "z-da-jlp"    :       [true, 'css-day-jlp'],
    "z-am--pm-jl" :       [true, 'css-day-djlp-pm'],  
    "z-am-offval-pm-jl" : [true, 'css-day-off-am css-day-djlp-pm'], 
    "z-am-offdem-pm-jl" : [true,'css-day-off-not-validated-am css-day-djlp-pm'], 
    "z-am-jl-pm-" :       [true, 'css-day-djlp-am'],  
    "z-am-jl-pm-offval" : [true,'css-day-djlp-am css-day-off-pm' ],   
    "z-am-jl-pm-offdem" : [true,'css-day-djlp-am css-day-off-not-validated-pm' ]
}; 

var dpicker_message$ ;
var dpicker_message_container$ ;

var dconsole$ ;  
var dpsval$, dpeval$ ; 

function fmt01(num) {
    if (num<10) 
        return("0"+num.toString()) ;
    else 
        return(num.toString()) ;
}
function mynow() {
    var now = new Date(); 
    return( fmt01(now.getHours())+":"+fmt01(now.getMinutes())+":"+fmt01(now.getSeconds())+"."+now.getMilliseconds() );
    // return( now.getHours()+":"+now.getMinutes()+":"+now.getSeconds() );
    // return( $.formatDate(now,"hh:mm:ss.S") ); 
    // return($.datepicker.formatDate("h:m:s.S",now));
};

function ym2date(year,month) {
    var sdate = year+"-"+month+"-01" ; 
    return $.datepicker.parseDate("yy-mm-dd",sdate); 
}
/* displaydate convertit datet du format yyyy-mm-dd en dd-mm-yyyy */ 
function displaydate(datet) { 
    var ldate = datet.split("-"); /* yyyy,mm,dd */ 
    ldate.reverse() ; 
    return ldate.join("-") ; 
}

function say(mytext) {
    stext = "at: " + mynow() + ": " + mytext + "<br>"; 
    if (dconsole$) {
        dconsole$.append(stext) ;
    }
}

function fb_message(wlevel,wtext) { // wlevel 0:ok, 1:normal message, 2:erreur saisie 
    say("fb_message:"+wlevel+""+wtext);
    dpicker_message$.hide();
    dpicker_message$.text("");
    if (wlevel==2) {
        dpicker_message_container$.removeClass("ui-fbmessage").addClass("ui-fbmessage-error") ; 
    } else {
        dpicker_message_container$.removeClass("ui-fbmessage-error").addClass("ui-fbmessage") ; 
    }; 
    
    dpicker_message$.text(wtext);
    // fb_message_anim(); 
//    dpicker_message$.fadeIn(2000);
    dpicker_message$.show(); // 1200);

} 
function build_datepicker(sid,odpi_cf) {
    try { 
        $(sid).datepicker("destroy");
    }  catch(e){ 
        smessage = "destroying dp "+ id +" triggers an error.ignored." + e  ;
        console.log(smessage); // alert(e); popup message 
        say(smessage); 
    }
    $(sid).datepicker( {
        showWeek:   odpi_cf.showWeek , 
        showButtonPanel: odpi_cf.showbuttonpanel,
        altField: odpi_cf.altfield,
        dateFormat: odpi_cf.dateformat, 
        defaultDate: odpi_cf.defaultdate,
        beforeShowDay: odpi_cf.beforeshowday,
        minDate: odpi_cf.mindate,
        maxDate: odpi_cf.maxdate,
        onChangeMonthYear: odpi_cf.onchangemonthyear,
        onSelect: odpi_cf.onselect
    });
}
function jqcal_ready() {
    // jqcal_refresh() ; 
    songletn = $("#ongletname"); 
    if (songletn) {
        songletname = songletn.text() ;
    } else {
        return ; // do nothing 
    }
    dconsole$ = $('#dconsole') ; 
    $("#dconsoleclear").click(
        function() { dconsole$.text(""); });

    field_jqcal = document.getElementById("jqcal");

    dpicker_message$ = $("#dpicker_message"); 
    dpicker_message_container$ = $("#dpicker_message_container");

    // aide en ligne sur utilisation des datepicker 
    $('div.caption img').click(function(){
          var body$ = $(this).closest('div.module').find('div.body');
          if (body$.is(':hidden')) {
            body$.show();
          }
          else {
            body$.hide();
          }
    });
    if (songletname=="nouvelle_absence" || songletname=="resp_traite_user" || 
        songletname=="echange_jour_absence") { 
        dpeval$ = $("#new_fin");  // input text 
        dpsval$ = $("#new_debut");  // input text 
        svacperiod$ = $('#vacperiod') ;
        //     say("typeof svacperiod = " + typeof(svacperiod)) ;
        if (svacperiod$) { 
            try {
                ospdays = JSON.parse(svacperiod$.text()); 
            } catch(e){ 
                ospdays = ospdaysdefault ; 
                smessage = "jqcal::error in parsing svacperiod " + e  ;
                console.log(smessage); // alert(e); popup message 
                say(smessage); 
            }
        };
        soconfig$ = $('#oconfig');
        if (soconfig$) {
            try {
                oconfig = JSON.parse(soconfig$.text());
                say("oconfig is "+ JSON.stringify(oconfig));
            } catch(e){ 
                smessage = "jqcal::error in parsing oconfig " + e  ;
                console.log(smessage); // alert(e); popup message 
                say(smessage); 
            }
        }; 
    };
    if (songletname=="nouvelle_absence" || songletname=="resp_traite_user") {
        $("#weexc").click(cb_weexcl); 
        odpi_start.defaultdate = $.datepicker.parseDate("yy-mm-dd",ospdays.currentdate) ;
        odpi_start.mindate = $.datepicker.parseDate("yy-mm-dd",ospdays.mindate) ;
        odpi_start.maxdate = $.datepicker.parseDate("yy-mm-dd",ospdays.maxdate) ;
        
        build_datepicker("#dpickerstart",odpi_start) ;
        odpi_start.cym = dpsval$.val().substr(0,7) ;

        odpi_end.defaultdate = $.datepicker.parseDate("yy-mm-dd",ospdays.currentdate) ;
        odpi_end.mindate = $.datepicker.parseDate("yy-mm-dd",ospdays.mindate) ;
        odpi_end.maxdate = $.datepicker.parseDate("yy-mm-dd",ospdays.maxdate) ;
        build_datepicker("#dpickerend",odpi_end); 
        odpi_end.cym = dpeval$.val().substr(0,7) ;


        say("odpi_start.cym = "+odpi_start.cym);
        say("odpi_end.cym = "+odpi_end.cym);

        /* convertit en format dd-mm-yyyy pour initialisation */ 
        $("#new_debut_disp").val(displaydate($("#new_debut").val())); 
        $("#new_fin_disp").val(displaydate($("#new_fin").val())); 

        /* callback positionne par nom de serie de radiobutton */
        $('[name="new_demi_jour_deb"]').click(function () {
            cb_onpartselect.call(this,"start");
        }); 
        $('[name="new_demi_jour_fin"]').click(function () {
            cb_onpartselect.call(this,"end");
        }); 

        // selecteur de type de conge et initialisation valeur 
        $('[name="new_type"]').click(cb_onnewtypeselect); 
        oconge.new_type = $('[name="new_type"]:checked').val() ; 

        query_conge(); // to update message 

    } else if (songletname=="echange_jour_absence") {
        odpi_moved.defaultdate = $.datepicker.parseDate("yy-mm-dd",ospdays.currentdate) ;
        odpi_moved.mindate = $.datepicker.parseDate("yy-mm-dd",ospdays.mindate) ;
        odpi_moved.maxdate = $.datepicker.parseDate("yy-mm-dd",ospdays.maxdate) ;
        build_datepicker("#dpickermoved",odpi_moved);

        odpi_chosen.defaultdate = $.datepicker.parseDate("yy-mm-dd",ospdays.currentdate) ;
        odpi_chosen.mindate = $.datepicker.parseDate("yy-mm-dd",ospdays.mindate) ;
        odpi_chosen.maxdate = $.datepicker.parseDate("yy-mm-dd",ospdays.maxdate) ;
        build_datepicker("#dpickerchosen",odpi_chosen); 

        $('[name="moment_absence_ordinaire"]').click(function () {
            cb_onpartselect_echange.call(this,"moved");
        });
        $('[name="moment_absence_souhaitee"]').click(function () {
            cb_onpartselect_echange.call(this,"chosen");
        });
        $('#but_reset').click(function () {
            say("reset");  // we reset the data and form 
            oechange = {"moved" : {"datetext":null, "part": null }, 
                        "chosen": {"datetext":null, "part": null } }; 
            query_echange();
        }); 
        query_echange(); 

    }; 
 
    /* creation des parties communes qqsoit l'onglet */ 
    if (field_jqcal) 
        document.getElementById("jqcal").innerHTML = "jqcal ready for "+ songletname;
    osession.session = $("#session").text() ;
    osession.rootpath = $("#rootpath").text() ;
    osession.uid = $("#uid").text() ;

    say("osession is "+ JSON.stringify(osession));
    say("jqcal is ready !");
    // say("owdaydisplay is " + JSON.stringify(owdaydisplay));
}

function jqcal_refresh() {
    $("#dpickerstart").datepicker("refresh");
    $("#dpickerend").datepicker("refresh");
    say("jqcal_refresh"); 
}
// callback function are prefixed by cb_ 
// cb_weexcl
function cb_weexcl() {
    say("cb_weexcl " + this.value + " " + this.checked);
    weexcluded = this.checked ; 
    jqcal_refresh() ;
};
function cb_onchangemonthyear(year,month,inst) {
    say("cb_onchangemonthyear " + inst.id + " " + year + " " + month );

    symtarget = year.toString() + "-" + fmt01(month); // date cible formatte "2014-05" 
    sdtarget  = symtarget + "-01" ; // date de maj datepicker 

    if (inst.id == "dpickerstart") {
        odpi_start.cym = symtarget ; 
        if (symtarget > odpi_end.cym) {
            say(" dstart > dend "); 
            odpi_end.defaultdate = sdtarget ; 
            say("odpi_end.defaultdate " + odpi_end.defaultdate); 
            build_datepicker("#dpickerend",odpi_end);
            odpi_end.cym = symtarget ;
        }; 
    } else if (inst.id == "dpickerend") {
        odpi_end.cym = symtarget ; 
        if (symtarget < odpi_start.cym) {
            say(" dend < dstart "); 
            odpi_start.defaultdate = sdtarget ; 
            say("odpi_start.defaultdate " + odpi_start.defaultdate); 
            build_datepicker("#dpickerstart",odpi_start);
            odpi_start.cym = symtarget ; 
        }; 
    };
}

function cb_onselect(dateText, inst) {
    say("cb_onselect " + inst.id + " " + dateText );
    if (inst.id == "dpickerstart") {
        oconge.start.datetext = dateText ;
        oconge.start.date = $.datepicker.parseDate("yy-mm-dd",dateText);
        $("#new_debut_disp").val(displaydate(dateText));
    } else if (inst.id == "dpickerend") {
        oconge.end.datetext = dateText ;
        oconge.end.date = $.datepicker.parseDate("yy-mm-dd",dateText); 
        $("#new_fin_disp").val(displaydate(dateText));
    } else {
        console.log("cb_onselect wrong selector " +  inst.id); 
    }
    query_conge();
};

function cb_onpartselect(spart) {
    say("cb_onpartselect " + spart + " " + $(this).val() );
    if (spart == "start") {
        oconge.start.part = $(this).val(); 
    } else if (spart == "end") {
        oconge.end.part = $(this).val(); 
    } else {
        console.log("cb_onpartselect wrong part " +  part); 
    }
    query_conge();
};
function cb_onnewtypeselect() {
    say("cb_onnewtypeselect " + this.value + " " + this.checked);
    oconge.new_type = this.value ; 
    query_conge();
};

// besoin de variabiliser suivant la langue 
function query_conge() {
    var bready = false ; 
    var wtext = "" ; 
    var wlevel ; // 1 warning, 2 error, 0 proceed
    say("query_conge with" +JSON.stringify(oconge)) ; 
    // check condition of call do ws::calc_joursnsolde 
    /* if ((oconge.start.date==null) && (oconge.end.date==null)) {
        wtext = "Saisissez date de dÃ©but et date fin ...";  wlevel = 1 ; 
    } else */
    if (oconge.start.date==null) {
        wtext = oconfig.msg_saisie_deb;  wlevel = 1 ; // "Saisissez date de dÃ©but ..."
    } else if (oconge.end.date==null) {
        wtext = oconfig.msg_saisie_fin;  wlevel = 1 ; // "Saisissez date de fin ..."
    } else if (oconge.start.date > oconge.end.date ) {
        wtext = oconfig.msg_w_date_anterieur;  wlevel = 2 ; // "warning : date de dÃ©but est > date de fin ..."
    // warning test on datetext 
    } else if (oconge.start.datetext == oconge.end.datetext ) { 
        if ((oconge.start.part == "pm") && (oconge.end.part == "am")) {
            wtext = oconfig.msg_w_ampm ; wlevel = 2 ; // "warning : vÃ©rifier choix am/pm ..."
    // in all other conditions we are ready to do ws::calc_joursnsolde
        } else {
            bready = true ; 
            wtext = "" ; wlevel = 0 ;
        }
    } else {
        bready = true ; 
        wtext = "" ; wlevel = 0 ;
    }
    if (bready) { 
        // fb_message(1,"ready to process nb of day, etc ..."); 
        say("query_conge ready_for_ws::calc_joursnsolde"); 
        // ajax takes place :
        $('[name="new_nb_jours"]').addClass("ui-fb-field");
        /* dpicker_message_container$.addClass("ui-fb-field"); */
        /* $.get(osession.rootpath+"/ws/in/main.php",
              {"rq":"calc_joursnsolde",
               "session":osession.session,
               "uid":osession.uid,
               "type":oconge.new_type,
               "sdate":oconge.start.datetext,
               "spart":oconge.start.part,
               "edate":oconge.end.datetext,
               "epart":oconge.end.part,
              },
              cb_calc_joursnsolde); */
        $.ajax({
               url: osession.rootpath+"/ws/in/main.php", 
               type: "GET",
               data: {"rq":"calc_joursnsolde",
                      "session":osession.session,
                      "uid":osession.uid,
                      "by":oconfig.by,
                      "type":oconge.new_type,
                      "sdate":oconge.start.datetext,
                      "spart":oconge.start.part,
                      "edate":oconge.end.datetext,
                      "epart":oconge.end.part,
                     },
               dataType: "json",
               timeout: 5000 ,  // 5 secondes 
               success: cb_calc_joursnsolde, 
               error: function(jqXHR, textStatus, errorThrown) {
                   smsg = "ajax::calc_joursnsolde on error "+textStatus+" "+errorThrown ; 
                   /* smsg = "la session a expirÃ©e ; veuillez vous dÃ©loger/reloger." ;  */
                   console.log(smsg);
                   say(smsg); 
                   fb_message(2,smsg); // _dpa : voir comment rediriger vers page login 
                   osession.rootpath = "" ; // _dpa_b10 
               } 
           });
 
    } else { // animation et affichage message warning 
        fb_message(wlevel,wtext); 
        $("#but_submit").attr('disabled',true);
    } 
}
// on utilise oresp et oconge pour maj champs de la FORM (POST)
function cb_calc_joursnsolde(oresp) {
    say("cb_calc_joursnsolde" + JSON.stringify(oresp));
    /* selon le retour on rend le nouveau conge validable */
    /* dpicker_message_container$.removeClass("ui-fb-field"); */
    $('[name="new_nb_jours"]').removeClass("ui-fb-field");
    
    if (oresp.ret==1) { // "conges ok, pensez a valider") variab  
        fb_message(0,oconfig.msg_ok_congeavalider); 
        $('[name="new_nb_jours"]').val(oresp.nbj); 
        if (oresp.soldeini == -1) {
            $('[name="solde_nouv"]').val(""); // cas d'1 absence 
        } else { // traitement du depassement 
            $('[name="solde_nouv"]').val(oresp.soldeini - oresp.demencours - oresp.nbj); 
        }
        $("#but_submit").hide();
        $("#but_submit").attr('disabled',false);
        $("#but_submit").fadeIn(2000);
    } else if (oresp.ret==2) { // conges en depassement 
        $('[name="new_nb_jours"]').val(oresp.nbj);
        $('[name="solde_nouv"]').val(oresp.soldeini - oresp.demencours - oresp.nbj);
        fb_message(2,oresp.retcomment);
        $("#but_submit").attr('disabled',true) ; 
    } else {
        $('[name="new_nb_jours"]').val("");
        $('[name="solde_nouv"]').val("");        
        fb_message(2,oresp.retcomment);
        $("#but_submit").attr('disabled',true);
    }
}
/* retourne vrai si zpattern est du type ztyp pour la partie 
   part "*" "da","am","pm" */ 
function is_ztype(zpattern,part,ztyp) {
    var lzpart = zpattern.split('-'); 
    var lzpartlen = lzpart.length; 
    var bret = false ; 
    if ( lzpartlen == 3 && part == "da" ) { 
        if (lzpart[2] == ztyp) {
            bret = true ; 
        }
    } else if ( lzpartlen == 5 ) { 
        if ( part == "am" ) { 
            idx = 2 ;
        } else if ( part == "pm" ) { 
            idx = 4 ;
        }
        if (lzpart[idx] == ztyp) {
            bret = true ; 
        }
    } 
    return bret ; 
}
/* cb and function for echange mode */ 
function cb_onselect_echange(dateText, inst) {
    say("cb_onselect_echange " + inst.id + " " + dateText );
    if (inst.id == "dpickermoved") {
        oechange.moved.datetext = dateText ;
        $("#new_debut_disp").val(displaydate(dateText));
        /* we set dpsval$.val as the original setting of the moved day */
        oechange.moved.inipart="j"; /* default value */ 
        if (oechange.moved.datetext in ospdays.period) {
            rtype = ospdays.period[oechange.moved.datetext].type ;
            say("cb_onselect_echange rtype=" + rtype); 
            if (is_ztype(rtype,"am","jl")) { 
                say("query_echange force moved as a");
                oechange.moved.inipart="a"; 
                oechange.moved.part="a"; 
                $("#dppart_moved_am").prop('checked', true); 
            } else if (is_ztype(rtype,"pm","jl")) {
                say("query_echange force moved as p");
                oechange.moved.inipart="p"; 
                oechange.moved.part="p";
                $("#dppart_moved_pm").prop('checked', true); 
            } else { 
                if (oechange.moved.part==null) { 
                    say("query_echange force moved as day");
                    $("#dppart_moved_day").prop('checked', true); 
                    oechange.moved.part="j";
                }
            } 
            dpsval$.val(oechange.moved.datetext+"-"+oechange.moved.inipart);
        } else { // oechange.moved.datetext not in ospdays.period
            bready=false; console.log("query_echange moved date not among possible value");
        }
    } else if (inst.id == "dpickerchosen") {
        oechange.chosen.datetext = dateText ;
        $("#new_fin_disp").val(displaydate(dateText));
        /* we set dpeval$.val as the original setting of the chosen day :
         EXTREME CARE mostly the opposite way of oechange.chosen.part */

        oechange.chosen.inipart="j"; /* default value */ 
        if (oechange.chosen.datetext in ospdays.period) {
            say("chosen date special" + oechange.chosen.datetext); 
            rtype = ospdays.period[oechange.chosen.datetext].type ;
            say("cb_onselect_echange rtype=" + rtype);
            if (is_ztype(rtype,"am","jl")) { 
                say("query_echange force chosen as p");
                oechange.chosen.part="p";
                oechange.chosen.inipart="p";
                $("#dppart_chosen_pm").prop('checked', true); 
                /* dpeval$.val(oechange.chosen.datetext+"-p"); */
            } else if (is_ztype(rtype,"pm","jl")) {
                oechange.chosen.part="a";
                oechange.chosen.inipart="a";
                say("query_echange force chosen as a");
                $("#dppart_chosen_am").prop('checked', true); 
                /* dpeval$.val(oechange.chosen.datetext+"-a"); */ 
            }
        }
        if (oechange.chosen.part==null) {
            say("query_echange force chosen as day");
            oechange.chosen.part="j";
            $("#dppart_chosen_day").prop('checked', true); 
        }
        say("query_echange setting dpecal to " + oechange.chosen.datetext
            +"-"+oechange.chosen.inipart); 
        dpeval$.val(oechange.chosen.datetext+"-"+oechange.chosen.inipart); 

    } else {
        console.log("cb_onselect_echange wrong selector " +  inst.id); 
    }
    query_echange(); 
}

function cb_onpartselect_echange(spart) {
    say("cb_onpartselect_echange " + spart + " " + $(this).val() );
    if (spart == "moved") {
        oechange.moved.part = $(this).val();
    } else if (spart == "chosen") {
        oechange.chosen.part = $(this).val();
    } else {
        console.log("cb_onpartselect_echange wrong part " +  part); 
    } 
    query_echange();
}

function query_echange() {
    var bready = false ; 
    var wtext = "" ; 
    var wlevel = -1 ; // 1 warning, 2 error, 0 proceed

    say("query_echange with" +JSON.stringify(oechange)) ; 
    if (oechange.moved.datetext==null) {
        bready=false; wtext=oconfig.msg_saisie_deb; wlevel=1; // "Saisissez date de dÃ©but ..."
    } else {
        bready=true ;
    }
    if (!bready) { 
        fb_message(wlevel,wtext); 
        $("#but_submit").attr('disabled',true);
        return ; 
    }
    if (oechange.chosen.datetext==null) { 
        bready=false; wtext=oconfig.msg_saisie_fin; wlevel=1 ; // "Saisissez date de fin ..."
    } else {
        bready=true;
    }
    if (!bready) { 
        fb_message(wlevel,wtext); 
        $("#but_submit").attr('disabled',true);
        return ; 
    }

    // at this level date are set, part day are set but are they coherent ?? 
    // warning test on datetext 
    if ( oechange.moved.part==null  ||   oechange.chosen.part==null || 
         ( oechange.moved.inipart!="j" && oechange.moved.part!=oechange.moved.inipart ) || 
         ( (oechange.moved.part=="a" || oechange.moved.part=="p") && oechange.chosen.part=="j") ||
         ( (oechange.chosen.part=="a" || oechange.chosen.part=="p") && oechange.moved.part=="j") 
    ) { 
        bready=false; wtext=oconfig.msg_w_ampm; wlevel=1 ;
        fb_message(wlevel,wtext); 
        $("#but_submit").attr('disabled',true);
        return ; 
    }

    if (bready) { 
        fb_message(0,oconfig.msg_ok_congeavalider);
        $("#but_submit").hide();
        $("#but_submit").attr('disabled',false);
        $("#but_submit").fadeIn(2000);
        // $("#but_submit").addClass("ui-fb-field");
    }
} 

// cette fonction appellÃ©e pour chaque jour, permet de donner un style affichage aux 
// jours travailles et les rendre selectionable/pas  
function cb_highlightWorkDays(date) {
    var wd = date.getDay()  ; // jour de la semaine 
    var sdate = $.datepicker.formatDate("yy-mm-dd",date) ;
    var rtype = "";
    var rlegend = "" ; 
    var olret = [true,''] ; /* [true,'','']) ;  par defaut selectionnable sans style, */
    var sprefix = "hwd "+$(this).attr('id') ; 

    // say(sprefix+" in "+ sdate + ":" + wd );
    if (sdate in ospdays.period) {
        oaday = ospdays.period[sdate] ; 
        rtype = oaday.type ;
        // _w3 
        rlegend = oaday.legend ; // new String(ospdays.period[sdate].legend) ;
        // say(sprefix + ":" + sdate + ":" + rtype+ ":" + rlegend);
        if (rtype in owdaydisplay) {
            // olret = new Object(owdaydisplay[rtype]) ; 
            // olret recoit une copie de owdaydisplay[rtype]
            olret = owdaydisplay[rtype].slice(0) ; 
            // lret.push(rlegend) ; 
        } else {
            smessage = "jqcal::hwd warning:" + sdate + " " + rtype + " not existing" ;
            console.log(smessage);
            say(smessage); 
        }
    }; // si c'est un week-end cela vient Ã©craser le statut 
       // du jour trouve ds ospdays
    if ( weexcluded  &&   $.inArray(wd, ospdays.weekend.days) != -1 ) { 
        rtype = "z-da-we" ; 
        // olret recoit une copie de owdaydisplay.twe
        olret = owdaydisplay[rtype].slice(0) ; 
        rlegend = ospdays.weekend.legend ;
    }
    olret.push(rlegend);
    return(olret);
}
/* callback appele pour mettre en valeur / parametrer affichage
   des jours dans le 1er datepicker pour echange de jour ; 
   seront Movable les jours de type owdayechange  
*/ 
function cb_highlightMovableDays(date) {
    var wd = date.getDay()  ; // jour de la semaine 
    var sdate = $.datepicker.formatDate("yy-mm-dd",date) ;
    var rlegend = "" ; 
    var olret = [] ; 

    if (sdate in ospdays.period) {
        oaday = ospdays.period[sdate] ; 
        rtype = oaday.type ;
        if (rtype in owdayechange) {
            olret = owdayechange[rtype].slice(0) ; // copie de
            relegend = oaday.legend ;
        } else {
            olret = [false,''] ; 
        }
    } else {
        olret = [false,''] ; 
    }
    olret.push(rlegend) ; 
    return(olret) ; 
} 

// __attic

/*


function cb_highlightWorkDays_1(date) {
    var wd = date.getDay()  ; // jour de la semaine 
    var sdate = $.datepicker.formatDate("yy-mm-dd",date) ;
    var rtype = "";
    var rlegend = "" ; 
    var lret = [] ; 

    // say("hwd " + sdate + ":" + wd );
    if (sdate in ospdays.period) {
        rtype = ospdays.period[sdate].type ;
        // say("hdwd " + sdate + ":" + rtype);
        // _w3 
        rlegend = new String(ospdays.period[sdate].legend) ;
        if (rtype in owdaydisplay) {
            lret = owdaydisplay[rtype] ; 
            lret.push(rlegend) ; 
            return lret ; 
        } else {
            smessage = "jqcal::hwd warning:" + sdate + " " + rtype + " not existing" ;
            console.log(smessage);
            say(smessage); 
            return [true,'',''];
        }
    } else if ( weexcluded  &&   $.inArray(wd, ospdays.weekend.days) != -1 ) { 
        rtype = "twe" ; 
        lret = owdaydisplay[rtype] ; 
        lret.push(ospdays.weekend.legend) ;
        return lret ;
    }
    // par defaut selectionnable sans style,legend
    return [true,'',''];
}


// cette fonction appellÃ©e pour chaque jour, permet de donner un style affichage aux 
// jours echangeables (selectionnable) et dÃ©selectionnÃ©es les autres  
function cb_highlightExchangeDays(date) {


    return [true,'',''];
}

var ospdays = { 
    "mindate": "2014/12/1", "maxdate": "2015/1/31", 
    "pubholidays" : { "days": ["2014/1/1","2014/3/1","2014/5/1"], "legend": "feriÃ©" },
    "weekend" : { "days": [0,6],  "legend": "we" },
    "offvalid" : {"2014/3/3": "3-3" , "2014/3/4":"3-4" , "2014/3/5":"3-5", "2014/3/31": "3-31" , } ,
    "offnotvalid" :  {"2014/3/6":"3-6", "2014/3/7":"3-7"} , 
    "jlp" : { "days": ["2014/3/12"] , "legend": "jlp" } ,
    "jlpam" : { "days": ["2014/3/19"], "legend": "djlpam" } ,
    "jlppm" : { "days": ["2014/3/26"], "legend": "djlppm" } 
};  
// var loffvalid = [] ;
// var loffnotvalid = [] ;
// highlightDays_1 :  cette fonction appellÃ©e pour chaque jour, permet de mettre en relief les 
// jours affichÃ©s (style), les rendre selectionable/pas  

function highlightDays_1(date) {
    var m = date.getMonth(), d = date.getDate(), y = date.getFullYear(),
    wd = date.getDay()  ;
    m += 1 ; 
    // say("hd" + y + m + d + ":" + wd ); 
    sdate = y + "/" + m + "/" + d ; 
    if ( $.inArray(sdate, dayspubholidays) != -1 ) { 
        return   [false, 'css-day-pub-holidays', 'fÃ©riÃ©']; 
    } else if ( weexcluded  &&   $.inArray(wd, wedays) != -1 ) { 
        return   [false, '', 'week-end']; 
    } else if($.inArray(sdate, daysoffvalid) != -1) { 
        return   [false, 'css-day-off ui-corner-all', 'conge validÃ©']; 
    } else if($.inArray(sdate, daysoffnotvalid) != -1) { 
        return   [false, 'css-day-off-not-validated', 'conge demandÃ©']; 
    } else if($.inArray(sdate, daysjlp) != -1) { 
        return   [false, 'css-day-jlp', 'jlp']; 
    } else if($.inArray(sdate, daysdjlpam) != -1) { 
        return   [true, 'css-day-djlp-am', 'djlp am']; 
    } else if($.inArray(sdate, daysdjlppm) != -1) { 
        return   [true, 'css-day-djlp-pm', 'djlp pm']; 
    }
//    } else if($.inArray(sdate, offDays) != -1) { 
//        return   [false, 'css-day-off', 'jour ferie']; 
//  }
    return [true,'',''];
}


function cb_highlightWorkDays_form1(date) {
    // var m = date.getMonth(), d = date.getDate(), y = date.getFullYear(),
    // m += 1 ; 
    // sdate = y + "/" + m + "/" + d ; 
    var wd = date.getDay()  ; // jour de la semaine 
    var sdate = $.datepicker.formatDate("yy-mm-dd",date) ;

    // say("hd " + sdate + ":" + wd );

    if ( $.inArray(sdate, ospdays.pubholidays.days) != -1 ) { 
        return   [false, 'css-day-pub-holidays', ospdays.pubholidays.legend]; 
    } else if ( weexcluded  &&   $.inArray(wd, ospdays.weekend.days) != -1 ) { 
        return   [false, '', ospdays.weekend.legend ]; 
    } else if($.inArray(sdate, loffvalid) != -1) {
        return   [false, 'css-day-off ui-corner-all', ospdays.offvalid[sdate]]; 
    } else if($.inArray(sdate, loffnotvalid) != -1) { 
        return   [false, 'css-day-off-not-validated', ospdays.offnotvalid[sdate]]; 
    } else if($.inArray(sdate, ospdays.jlp.days) != -1) { 
        return   [false, 'css-day-jlp', ospdays.jlp.legend]; 
    } else if($.inArray(sdate,ospdays.jlpam.days ) != -1) { 
        return   [true, 'css-day-djlp-am', ospdays.jlpam.legend]; 
    } else if($.inArray(sdate, ospdays.jlppm.days) != -1) { 
        return   [true, 'css-day-djlp-pm', ospdays.jlppm.legend]; 
    }
//    } else if($.inArray(sdate, offDays) != -1) { 
//        return   [false, 'css-day-off', 'jour ferie']; 
//  }
    return [true,'',''];
}




*/ 
/*  chantier animation 
var fbm_anim = false ;
function fb_message2(wlevel,wtext) { // wlevel 0:ok, 1:normal message, 2:erreur saisie 
    dpicker_message$.text(wtext);
}
function fb_message_anim() { 
    if (fbm_anim) {
        fbm_anim = false ;
        dpicker_message$.addClass("ui-fbmessage-loading"); // .attr("className",
    } else { // starting anim for a while 
        fbm_anim = true ;
        dpicker_message$.removeClass("ui-fbmessage-loading"); // .attr("className",
        setTimeout("fb_message_anim('')", 1000);
    }
}  */ 

/* function fb_message_wrong(wlevel,wtext) { // wlevel 0:ok, 1:normal message, 2:erreur saisie 
    // anim and other 
    if (fb_message_anim) { // anim running
        dpicker_message$.removeClass("ui-fbmessage-loading"); 
        if (wlevel==2) {
//            dpicker_message$.addClass("ui-fbmessage-error"); 
        } else {
//            dpicker_message$.removeClass("ui-fbmessage-error"); 
        }; 
        dpicker_message$.text(wtext); 
        fb_message_anim = false ;
    } else { // starting anim for a while 
        fb_message_anim = true ;
        dpicker_message$.text();
        dpicker_message$.addClass("ui-fbmessage-loading"); 
        setTimeout("fb_message("+wlevel+",'"+wtext+"')", 500);
    };
}
var owdaydisplay_oldt = {
    "tpubhol" : [false, 'css-day-pub-holidays'], 
    "twe"     : [false, ''],
    "toffval" : [false, 'css-day-off'], 
    "toffvalam" : [true, 'css-day-off-am'],
    "toffvalpm" : [true, 'css-day-off-pm'],
    "toffdem" : [false, 'css-day-off-not-validated'],
    "toffdemam" : [true, 'css-day-off-not-validated-am'],
    "toffdempm" : [true, 'css-day-off-not-validated-pm'],
    "tjlp"    : [false, 'css-day-jlp'],
    "tdjlpam" : [true, 'css-day-djlp-am'],     
    "tdjlppm" : [true, 'css-day-djlp-pm'] ,
    "toffvalamtdjlppm" : [false, 'css-day-off-am css-day-djlp-pm'],
    "toffvalpmtdjlpam" : [false, 'css-day-off-pm css-day-djlp-am'],
    "toffdemamtdjlppm" : [false, 'css-day-off-not-validated-am css-day-djlp-pm'], 
    "toffdempmtdjlpam" : [false, 'css-day-off-not-validated-pm css-day-djlp-am']
}; 

var owdayechange_oldt = {
    "tjlp"    : [true, 'css-day-jlp'],
    "tdjlpam" : [true, 'css-day-djlp-am'],     
    "tdjlppm" : [true, 'css-day-djlp-pm'],
    "toffvalamtdjlppm" : [true, 'css-day-off-am css-day-djlp-pm'],
    "toffvalpmtdjlpam" : [true, 'css-day-off-pm css-day-djlp-am'],
    "toffdemamtdjlppm" : [true, 'css-day-off-not-validated-am css-day-djlp-pm'], 
    "toffdempmtdjlpam" : [true, 'css-day-off-not-validated-pm css-day-djlp-am']
};


*/ 
