/* angelique integrated web service 
  client side function 
 */
function log( message ) { // DO NOTHING 
    $( "<div>" ).text( message ).prependTo( "#log" );
    $( "#log" ).scrollTop( 0 );
}; 

var repfake = [{"value":"didier.petitpas","label":"didier.petitpas label","id":{"dn":"uidNumber=10009,sn=an,sn=internes_si,dc=aviation-civile,dc=gouv,dc=fr","mail":"didier.petitpas@aviation-civile.gouv.fr","givenname":"Didier","cn":"PETITPAS Didier DGAC\/SAC-SPM","uid":"didier.petitpas","sn":"PETITPAS"}},{"value":"didier.piquot","label":"didier.piquot label","id":{"dn":"uidNumber=13461,sn=ldap-crna-e,sn=ne,sn=internes_si,dc=aviation-civile,dc=gouv,dc=fr","mail":"didier.piquot@aviation-civile.gouv.fr","givenname":"Didier","cn":"PIQUOT Didier DGAC\/CRNA-E","uid":"didier.piquot","sn":"PIQUOT"}},{"value":"didier.prevot","label":"didier.prevot label","id":{"dn":"uidNumber=13730,sn=ldap-sna-ne,sn=ne,sn=internes_si,dc=aviation-civile,dc=gouv,dc=fr","mail":"didier.prevot@aviation-civile.gouv.fr","givenname":"Didier","cn":"PREVOT Didier DGAC\/SNA-NE","uid":"didier.prevot","sn":"PREVOT"}}] ; 

// var suggestions ;  

function aiws_ready() {
    log("aiws_ready");
    $("#uid").autocomplete({
        // source:  "../aiws/aiws.php?rquest=find_uid&number=20" ,  // vrai appel au ldap 
        // source: "../../ws/integrated/main.php?noldap&rquest=fake_finduid" ,  // faux appel reponse fixee
        // appel par callback : response cb est appele ds le cb associe a .get
        source: function (request, response) {
            log("autoc_search : " + JSON.stringify(request));  
            // var term = request.term ; // objet de la recherche 
            // "../aiws/aiws.php?noldap&rquest=fake_finduid" ,
            // "../aiws/aiws.php?rquest=find_uid&number=20"
            $.ajax({
                url: "../ws/integrated/main.php?rquest=find_uid&number=20", 
                // url: "../ws/integrated/main.php?rquest=find_uid&number=20", 
                // url: "../ws/integrated/main.php?noldap&rquest=fake_finduid", 
                type: "GET",
                data: {'term': request.term }, // objet de la recherche
                dataType: "json",
                timeout: 5000 ,  // 5 secondes 
                success: 
                    function(data, textStatus, jqXHR)  {
                        var sdata = JSON.stringify(data) ; 
                        log("data is " + sdata ) ;
                        // animation sur la lÃ©gende si rÃ©sultat vide
                        if ( data.length == 0 ) refresh_uidlegend('aucun agent ne correspond') ;
                        response(data);
                    } , 
                error:
                    function(jqXHR, textStatus, errorThrown) {
                        log("ajaxrq aborted : " ) ; 
                        refresh_uidlegend('erreur: serveur ldap injoignable ');
                    } 
            }); 
            // response(repfake); 
            // response(data); 
        }, 
        
        minLength: 3,
        delay:800, // 0.8 sec before trigger 
        select: function( event, ui ) {
            log( ui.item ?
                 "Selected: "+ui.item.value+" nom:" + ui.item.id.sn+" prenom:"+ui.item.id.givenname+" mail:"+ui.item.id.mail : 
                 "Nothing selected, input was " + this.value );
            if ( ui.item ) { 
                field_nom = document.getElementById("sn");
                field_nom.value =  ui.item.id.sn ;
                field_prenom = document.getElementById("givenname");
                field_prenom.value =  ui.item.id.givenname ;
                field_mail = document.getElementById("mail");
                field_mail.value =  ui.item.id.mail ;
            }; 
		},
        open: function() {
            log("autocomplete open with " + this.value)    
			$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
		},
        close: function() {
            log("autocomplete close ")    
			$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
		}
    });
    // field_uidlegend = document.getElementById("uidlegend");
    // if (field_uidlegend) 
    //    document.getElementById("uidlegend").innerHTML = "taper 3 lettres ..." ;
    refresh_uidlegend("") ; 
    $("#uidanim").click(function () { refresh_uidlegend("message d'avert. s'affiche 2 sec."); } );
    // setInterval("refresh_uidlegend('aucun agent ne correspond')" , 10000);
};
function refresh_uidlegend(tmpmessage) {
    var mdefault = "saisir 3 premières lettres uid ..." ; 
    var uidl$ = $("#uidlegend") ;
    var uid$ = $("#uid") ; 

    // on referme le dialogue (roue qui tourne) si jamais il Ã©tait positionnÃ© ;
    uid$.removeClass( "ui-autocomplete-loading" ) ; 

    log("refresh_uidlegend with " + tmpmessage);
    if ( tmpmessage != "") {
//        log("ru msg");
        uidl$.html(tmpmessage) ;
        uidl$.addClass( "ui-legend" ); 
        setTimeout("refresh_uidlegend('')", 2000);
    } else {
//        log("ru") ; 
        uidl$.hide();
        uidl$.html(mdefault) ; 
        uidl$.removeClass( "ui-legend" );
        uidl$.show(2000);         
    }; 
}
function refresh_uidl2(tmpmessage) {
    var mdefault = "taper 3 lettres ..." ; 
    var field_uidlegend = document.getElementById("uidlegend");

    log("refresh_uidl2 with " + tmpmessage);
    if ( tmpmessage != "") {
        log("ru msg");
        field_uidlegend.innerHTML = tmpmessage ;
        setTimeout("refresh_uidl2('')", 2000);
    } else {
        log("ru") ; 
        field_uidlegend.innerHTML = mdefault ; 
    }; 
}

function autoc_search(request, response) {
    var term = request.term ;
    var results ; 
    log("autoc_search"); 
    $.get("../ws/integrated/main.php?noldap&rquest=fake_finduid" ,
          "",
          function (arg1)  { results = arg1 ; } ) ; 
    log("autoc_search " + results ); 
    response(results); 
}

// __attic 
function refresh_uidlegend_old1(tmpmessage) {
    var mdefault = "taper 3 lettres ..." ; 
    log("refresh_uidlegend with " + tmpmessage);
    var uidl$ = $("#uidlegend") ; 
    
    if ( tmpmessage != "") {
        log("ru msg");
        uidl$.html(tmpmessage) ;
        uidl$.toggle(1000);
        uidl$.html(mdefault) ;
        uidl$.toggle(1000);
    } else {
        log("ru") ; 
        uidl$.html(mdefault) ; 
    }
}


// //define callback to format results
//             source: function(req, add){
                     
//                 //pass request to server
//                 $.getJSON("friends.php?callback=?", req, function(data) {
                             
//                     //create array for response objects
//                     var suggestions = [];
                             
//                     //process response
//                     $.each(data, function(i, val){                             
//                     suggestions.push(val.name);
//                 });
                             
//                 //pass array to callback
//                 add(suggestions);