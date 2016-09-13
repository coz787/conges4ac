<?php
/*************************************************************************************************
PHP_CONGES : Gestion Interactive des Congés
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/

//appel de PHP-IDS que si version de php > 5.1.2
if(phpversion() > "5.1.2") { include("../controle_ids.php") ;}
$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

include("../fonctions_conges.php") ;
include("../INCLUDE.PHP/fonction.php");
include("../INCLUDE.PHP/session.php");

//include($_SESSION['config']['lang_file']) ;

$DEBUG=FALSE;
//$DEBUG=TRUE;

// $scd = session_get_cookie_params(); //  ini_get("session.cookie_domain") ;
// error_log("session_get_cookie_params = ".$scd) ; 
if($_SESSION['config']['where_to_find_user_email']=="ldap"){ include("../config_ldap.php");}

if($DEBUG==TRUE) { echo "lang_file=".$_SESSION['config']['lang_file']."<br>\n";  echo "$_SESSION =<br>\n"; print_r($_SESSION); echo "<br><br>\n"; }

	/*** initialisation des variables ***/
	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$onglet = getpost_variable("onglet");
	$year_calendrier_saisie_debut = getpost_variable("year_calendrier_saisie_debut", 0);
	$mois_calendrier_saisie_debut = getpost_variable("mois_calendrier_saisie_debut", 0);
	$year_calendrier_saisie_fin = getpost_variable("year_calendrier_saisie_fin", 0);
	$mois_calendrier_saisie_fin = getpost_variable("mois_calendrier_saisie_fin", 0);
	$tri_date = getpost_variable("tri_date", "ascendant");
	$new_demande_conges = getpost_variable("new_demande_conges", 0);
	$new_echange_rtt    = getpost_variable("new_echange_rtt", 0);
	$new_debut = getpost_variable("new_debut");
	$new_demi_jour_deb = getpost_variable("new_demi_jour_deb");
	$new_fin = getpost_variable("new_fin");
	$new_demi_jour_fin = getpost_variable("new_demi_jour_fin");
	$new_nb_jours = getpost_variable("new_nb_jours");
	$new_comment = getpost_variable("new_comment");
	$new_type = getpost_variable("new_type");
	$moment_absence_ordinaire = getpost_variable("moment_absence_ordinaire");
	$moment_absence_souhaitee = getpost_variable("moment_absence_souhaitee");
	$change_passwd = getpost_variable("change_passwd", 0);
	$new_passwd1 = getpost_variable("new_passwd1");
	$new_passwd2 = getpost_variable("new_passwd2");
	$year_affichage = getpost_variable("year_affichage" , date("Y") );
	/*************************************/

	//connexion mysql
	$mysql_link = connexion_mysql();

	// on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
	if(!isset($_SESSION["tab_j_feries"]))
	{
		init_tab_jours_feries($mysql_link, $DEBUG);
		//print_r($_SESSION["tab_j_feries"]);   // verif DEBUG
	}


	/*************************************/
	/***  debut de la page             ***/

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	$titre=$_SESSION['config']['titre_application'].$_SESSION['config']['titre_user_index']." ".$_SESSION['userlogin'];
	echo "<TITLE> $titre</TITLE>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
//	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
	echo "<link href=\"../".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
/*     echo "<link href=\"../jquery-ui-1.10.3.custom_a.css\" rel=\"stylesheet\" type=\"text/css\" >\n"; */

// _nfj_ include("../fonctions_javascript.php") ;
    echo "<script src=\"/jquery/js/jquery-1.10.2.min.js\"></script>\n";
	echo "<script src=\"/jquery/js/jquery-ui-1.10.3.custom.min.js\"></script>\n";
	echo "<script src=\"/jquery/development-bundle/ui/i18n/jquery.ui.datepicker-fr.js\"></script>\n";
    echo "<script src=\"../jqcal.min.js\"></script>\n";
    echo "<script>$(function() { jqcal_ready(); } ); </script>\n";

	echo "</head>\n";

	$bgimage=$_SESSION['config']['URL_ACCUEIL_CONGES']."/".$_SESSION['config']['bgimage'];
	echo "<body text=\"#000000\" bgcolor=".$_SESSION['config']['bgcolor']." link=\"#000080\" vlink=\"#800080\" alink=\"#FF0000\" background=\"$bgimage\">\n";
/*
echo "<pre>";
print_r($_SERVER); 
echo "</pre>"; 
*/
/*
echo "<pre>";
print_r($_SESSION); 
echo "</pre>"; 
echo "<p>" ; 
echo "<a href=\"../ws/in/main.php?session=$session&rq=print_session\">" ;
echo "wsinmain" ;
echo "</a>" ; 
echo "</p>" ; */

	/*************************************/
	/*** affichage "deconnexion" et "actualiser page" et "mode administrateur" et "affichage calendrier" ***/
	/*************************************/
	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"><tr>\n";
	/*** bouton deconnexion  ***/
	if($_SESSION['config']['auth']==TRUE)
	{
		echo "<td width=\"120\" valign=\"middle\">\n";
		bouton_deconnexion($DEBUG);
		echo "</td>\n";
	}

	/*** bouton actualiser  ***/
	echo "<td width=\"140\" valign=\"middle\">\n";
	bouton_actualiser($onglet, $DEBUG);
	echo "</td>\n";

	/*** bouton éditions papier  ***/
	if($_SESSION['config']['editions_papier']==TRUE)
	{
		echo "<td width=\"130\" valign=\"middle\">\n";
		echo "<a href=\"../edition/edit_user.php?session=$session&user_login=".$_SESSION['userlogin']."\" target=\"_blank\">" .
				"<img src=\"../img/edition-22x22.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['button_editions']."\" alt=\"".$_SESSION['lang']['button_editions']."\">" .
				"</a> ".$_SESSION['lang']['button_editions']."\n";
		echo "</td>\n";
	}

	/*** bouton export calendar  ***/
	if($_SESSION['config']['export_ical_vcal']==TRUE)
	{
		echo "<td width=\"155\" valign=\"middle\">\n";
		echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../export_vcalendar.php?session=$session&user_login=".$_SESSION['userlogin']."','icalvcal',380,240);\">" .
				"<img src=\"../img/export-22x22.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['button_export_2']."\" alt=\"".$_SESSION['lang']['button_export_2']."\">" .
				"</a> ".$_SESSION['lang']['button_export_1']."\n";
		echo "</td>\n";
	}

	/*** cellule centrale vide  ***/
  echo "<td valign=\"bottom\" align=\"center\" >\n";
    /* on y affiche la date courante */ 
$lcdate = conges_get_date() ; 
if ( $lcdate["mode"]==0 ) { 
  echo "<div>\n"; 
} else {
  echo "<div class=\"techdateforce\">\n";
}
echo "Nous sommes le [&nbsp;&nbsp;&nbsp;";
echo sprintf("%02d-%02d-%02d",$lcdate['year'],$lcdate['mon'],$lcdate['mday']);
echo "&nbsp;&nbsp;&nbsp;]" ; 
echo "</div>\n"; 
echo "</td>\n";


	/*** bouton mode administrateur  ***/
	if(is_admin($_SESSION['userlogin'], $mysql_link, $DEBUG))
	{
		echo "<td width=\"155\" align=\"right\" valign=\"middle\">\n";
		echo "<a href=\"../admin/admin_index.php?session=$session\" method=\"POST\" target=\"_blank\">" .
				"<img src=\"../img/admin-tools-22x22.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['button_admin_mode']."\" alt=\"".$_SESSION['lang']['button_admin_mode']."\">" .
				"</a> ".$_SESSION['lang']['button_admin_mode']."\n";
		echo "</td>\n";
	}

	/*** bouton calendrier  ***/
	if($_SESSION['config']['user_affiche_calendrier']==TRUE)
	{
		echo "<td width=\"155\" align=\"right\" valign=\"middle\">\n";
		// affichage dans un popup
		echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../calendrier.php?session=$session','calendrier',850,600);\">" .
				"<img src=\"../img/rebuild.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['button_calendar']."\" alt=\"".$_SESSION['lang']['button_calendar']."\">" .
				"</a> ".$_SESSION['lang']['button_calendar']."\n";
		echo "</td>\n";
	}
	echo "</tr></table>\n";

	echo "<CENTER>\n";


	/*************************************/
	/***  suite de la page             ***/
	/*************************************/
	// si le user peut saisir ses demandes et qu'il vient d'en saisir une ...
    // _newdem
/* if ($new_demande_conges==1) {
  echo "<pre>";
  echo "new_dem".$new_debut." ". $new_demi_jour_deb." ". $new_fin." ". $new_demi_jour_fin." ". $new_nb_jours." ". $new_comment." ". $new_type;
  echo "</pre>";
}
*/ 
	if(($new_demande_conges==1) && ($_SESSION['config']['user_saisie_demande']==TRUE)) {
		new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $mysql_link, $DEBUG);
	}
	elseif(($new_echange_rtt==1)&&($_SESSION['config']['user_echange_rtt']==TRUE)) {
		echange_absence_rtt($onglet, $new_debut, $new_fin, $new_comment, $moment_absence_ordinaire, $moment_absence_souhaitee, $mysql_link, $DEBUG);
	}
	elseif($change_passwd==1) {
		change_passwd($mysql_link, $new_passwd1, $new_passwd2, $DEBUG);
	}
	else {
		if($onglet=="")	$onglet="historique_conges";
		affichage($onglet, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date, $year_affichage, $mysql_link, $DEBUG);
	}

	mysql_close($mysql_link);

	/*************************************/
	/***  fin de la page             ***/
	echo "<hr align=\"center\" size=\"2\" width=\"90%\">\n";
	echo "<br>\n";
	echo "</CENTER>\n";

	echo "</body>\n";
	echo "</html>\n";




/**************************************************************************************/
/********  FONCTIONS      ******/
/**************************************************************************************/

function affichage($onglet, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date, $year_affichage, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// si les mois et année ne sont pas renseignés, on prend ceux du jour
	if($year_calendrier_saisie_debut==0)
		$year_calendrier_saisie_debut=date("Y");
	if($mois_calendrier_saisie_debut==0)
		$mois_calendrier_saisie_debut=date("m");
	if($year_calendrier_saisie_fin==0)
		$year_calendrier_saisie_fin=date("Y");
	if($mois_calendrier_saisie_fin==0)
		$mois_calendrier_saisie_fin=date("m");
	//echo "$mois_calendrier_saisie_debut  $year_calendrier_saisie_debut  -  $mois_calendrier_saisie_fin  $year_calendrier_saisie_fin<br>\n";

	$sql1 = "SELECT u_nom, u_prenom FROM conges_users where u_login = '".$_SESSION['userlogin']."' ";
	$ReqLog1 = requete_mysql($sql1, $mysql_link, "affichage", $DEBUG) ;

	while ($resultat1 = mysql_fetch_array($ReqLog1)) {
		$NOM=$resultat1["u_nom"];
		$PRENOM=$resultat1["u_prenom"];
	}

    // _jqcal : hidden field in the page to be used by javascript 
    echo "<div class=\"tech\"> \n";   // style=\"display: none;\" 
    echo "<span id=\"uid\" >". $_SESSION['userlogin'] ."</span>\n"; 
    echo "<span id=\"session\">". $session ."</span>\n"; 
    echo "</div>\n";

	// TITRE
	echo "<H2>".$_SESSION['lang']['resp_menu_button_mode_user'].": ".$_SESSION['userlogin']." - $PRENOM $NOM</H2>\n\n";

	/********************/
	/* Bilan des Conges */
	/********************/
	// affichage du tableau récapitulatif des solde de congés d'un user
	affiche_tableau_bilan_conges_user($_SESSION['userlogin'], $mysql_link, $DEBUG);

	// _jqcal printf("<br><br><br>\n");
    echo "<br>\n" ;

	/*********************************/
	/*   AFFICHAGE DES ONGLETS...    */
	/*********************************/
	$nb_colonnes=2 ; // on affiche toujours au moins 2 onglets (histo conges et histo absences)
	echo "</center>\n" ;
	echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"1\">\n" ;
	echo "<tr align=\"center\">\n";
		if(($_SESSION['config']['user_saisie_demande']==TRUE)||($_SESSION['config']['user_saisie_mission']==TRUE))
		{
			if($onglet!="nouvelle_absence")
				echo "<td class=\"onglet\" width=\"150\"><a href=\"$PHP_SELF?session=$session&onglet=nouvelle_absence\" class=\"bouton-onglet\"> ".$_SESSION['lang']['divers_nouvelle_absence']." </a></td>\n";
			else
				echo "<td class=\"current-onglet\" width=\"150\"><a href=\"$PHP_SELF?session=$session&onglet=nouvelle_absence\" class=\"bouton-current-onglet\"> ".$_SESSION['lang']['divers_nouvelle_absence']." </a></td>\n";
			$nb_colonnes=$nb_colonnes+1;
		}
		if($_SESSION['config']['user_echange_rtt']==TRUE)
		{
			if($onglet!="echange_jour_absence")
				echo "<td class=\"onglet\" width=\"170\"><a href=\"$PHP_SELF?session=$session&onglet=echange_jour_absence\" class=\"bouton-onglet\"> ".$_SESSION['lang']['user_onglet_echange_abs']." </a></td>\n";
			else
				echo "<td class=\"current-onglet\" width=\"170\"><a href=\"$PHP_SELF?session=$session&onglet=echange_jour_absence\" class=\"bouton-current-onglet\"> ".$_SESSION['lang']['user_onglet_echange_abs']." </a></td>\n";
			$nb_colonnes=$nb_colonnes+1;
		}
		if($_SESSION['config']['user_saisie_demande']==TRUE)
		{
			if($onglet!="demandes_en_cours")
				echo "<td class=\"onglet\" width=\"150\"><a href=\"$PHP_SELF?session=$session&onglet=demandes_en_cours\" class=\"bouton-onglet\"> ".$_SESSION['lang']['user_onglet_demandes']." </a></td>\n";
			else
				echo "<td class=\"current-onglet\" width=\"150\"><a href=\"$PHP_SELF?session=$session&onglet=demandes_en_cours\" class=\"bouton-current-onglet\"> ".$_SESSION['lang']['user_onglet_demandes']." </a></td>\n";
			$nb_colonnes=$nb_colonnes+1;
		}

		if($onglet!="historique_conges")
			echo "<td class=\"onglet\" width=\"170\"><a href=\"$PHP_SELF?session=$session&onglet=historique_conges\" class=\"bouton-onglet\"> ".$_SESSION['lang']['user_onglet_historique_conges']." </a></td>\n";
		else
			echo "<td class=\"current-onglet\" width=\"170\"><a href=\"$PHP_SELF?session=$session&onglet=historique_conges\" class=\"bouton-current-onglet\"> ".$_SESSION['lang']['user_onglet_historique_conges']." </a></td>\n";

		if($onglet!="historique_autres_absences")
			echo "<td class=\"onglet\" width=\"200\"><a href=\"$PHP_SELF?session=$session&onglet=historique_autres_absences\" class=\"bouton-onglet\"> ".$_SESSION['lang']['user_onglet_historique_abs']." </a></td>\n";
		else
			echo "<td class=\"current-onglet\" width=\"200\"><a href=\"$PHP_SELF?session=$session&onglet=historique_autres_absences\" class=\"bouton-current-onglet\"> ".$_SESSION['lang']['user_onglet_historique_abs']." </a></td>\n";
		if(($_SESSION['config']['auth']==TRUE) && ($_SESSION['config']['user_ch_passwd']==TRUE))
		{
			if($onglet!="changer_mot_de_passe")
				echo "<td class=\"onglet\" width=\"170\"><a href=\"$PHP_SELF?session=$session&onglet=changer_mot_de_passe\" class=\"bouton-onglet\"> ".$_SESSION['lang']['user_onglet_change_passwd']." </a></td>\n";
			else
				echo "<td class=\"current-onglet\" width=\"170\"><a href=\"$PHP_SELF?session=$session&onglet=changer_mot_de_passe\" class=\"bouton-current-onglet\"> ".$_SESSION['lang']['user_onglet_change_passwd']." </a></td>\n";
			$nb_colonnes=$nb_colonnes+1;
		}
	echo "</tr>\n";
	echo "</table>\n" ;


	/**************************************/
	/*   AFFICHAGE DE LA PAGE DEMANDéE    */
	/**************************************/

	echo "<CENTER>\n" ;
	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"100%\">\n" ;
	echo "<tr align=\"center\">\n";

	/**************************/
	/* Nouvelle Demande */
	/**************************/
	if($onglet=="nouvelle_absence")
	{
		echo "<td colspan=$nb_colonnes>\n";
		// echo "<H3>".$_SESSION['lang']['divers_nouvelle_absence']." :</H3>\n\n";

		//affiche le formulaire de saisie d'une nouvelle demande de conges
		saisie_nouveau_conges($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet, "user", $mysql_link, $DEBUG);

		echo "</td>\n";
	}


	/**************************************/
	/* Echange absence rtt/jour travaillé */
	/**************************************/
	if($onglet=="echange_jour_absence")
	{
		echo "<td colspan=$nb_colonnes>\n";
		// echo "<H3>".$_SESSION['lang']['user_echange_rtt']." :</H3>\n\n";

		//affiche le formulaire de saisie d'une nouvelle demande de conges
		saisie_echange_rtt($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet, "user", $mysql_link, $DEBUG);

		echo "</td>\n";
	}


	/**************************/
	/* Etat demandes en cours */
	/**************************/
	if($onglet=="demandes_en_cours")
	{
		echo "<td colspan=$nb_colonnes>\n";
		echo "<h3>".$_SESSION['lang']['user_etat_demandes']." :</h3>\n" ;

		//affiche le tableau des demandes en cours
		affichage_demandes_en_cours($tri_date, $onglet, $mysql_link, $DEBUG);

		echo "</td>\n";
	}


	/*************************/
	/* Historique des Conges */
	/*************************/
	if($onglet=="historique_conges")
	{
		echo "<td colspan=$nb_colonnes>\n";
		echo "<h3>".$_SESSION['lang']['user_historique_conges']." :</h3>\n";

		//affiche le tableau de l'hitorique des conges
		affichage_historique_conges($tri_date, $year_affichage, $onglet, $mysql_link, $DEBUG);

		echo "</td>\n";
	}


	/**********************************/
	/* Historique des absences autres */
	/**********************************/
	if($onglet=="historique_autres_absences")
	{
		echo "<td colspan=$nb_colonnes>\n";
		echo "<h3>".$_SESSION['lang']['user_historique_abs']." :</h3>\n";

		//affiche le tableau de l'hitorique des absences
		affichage_historique_absences($tri_date, $year_affichage, $onglet, $mysql_link, $DEBUG);

		echo "</td>\n";
	}

	/**************************/
	/* Changer Password */
	/**************************/
	if($onglet=="changer_mot_de_passe")
	{
		echo "<td colspan=$nb_colonnes>\n";
		echo "<H3>".$_SESSION['lang']['user_change_password']." :</H3>\n\n";

		echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n" ;
		echo "<table cellpadding=\"2\" class=\"tablo\" width=\"500\">\n";
		echo "<tr><td class=\"titre\">".$_SESSION['lang']['user_passwd_saisie_1']."</td><td class=\"titre\">".$_SESSION['lang']['user_passwd_saisie_2']."</td></tr>\n";

		$text_passwd1="<input type=\"password\" name=\"new_passwd1\" size=\"10\" maxlength=\"20\" value=\"\">" ;
		$text_passwd2="<input type=\"password\" name=\"new_passwd2\" size=\"10\" maxlength=\"20\" value=\"\">" ;
		echo "<tr align=\"center\">\n";
		echo "<td>$text_passwd1</td><td>$text_passwd2</td>\n";
		echo "</tr>\n";

		echo "</table><br>\n";
		echo "<input type=\"hidden\" name=\"change_passwd\" value=1>\n";
		echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">   <input type=\"reset\" value=\"".$_SESSION['lang']['form_cancel']."\">\n";
		echo "</form>\n" ;

		echo "</td>\n";
	}

	echo "</tr>\n";

	/*** FIN AFFICHAGE DE LA PAGE DEMANDéE  ***/
	/******************************************/

	echo "</table>\n";
	echo "</CENTER>\n";
    echo "<p align=\"left\">\n" ; 

    // _jqcal style=\"display: none;\"
    echo "<div id=\"ongletname\" class=\"tech\">".$onglet."</div>\n";  
    echo "<div id=\"rootpath\" class=\"tech\">".$_SESSION['config']['php_conges_rootpath']."</div>\n";  
    echo "<div id=\"jqcal\" class=\"tech\">jqcal</div>\n";  
    
    /* _jqcal : une console pour debugguer le volet clientside */
    if (False) {
      echo "<button id=\"dconsoleclear\" type=\"button\">console clear</button>\n";
      echo "<div>dconsole:\n"; 
      echo "<div id=\"dconsole\" style=\"height: 600px; width: 1200px; overflow: auto;\" ></div>\n";
      echo "</div>\n"; 
    }; 
    echo "</p>\n" ; 
    
	/***  affichage "deconnexion" et "actualiser page"  ***/
	/******************************************/
	echo "<table>\n";
	echo "<tr>\n";
	if($_SESSION['config']['auth']==TRUE)
	{
		echo "<td valign=\"middle\">\n";
		bouton_deconnexion($DEBUG);
		echo "</td>\n";
		echo "<td valign=\"middle\">\n";
		echo "<img src=\"../img/shim.gif\" width=\"20\" height=\"22\" border=\"0\">\n";
		echo "</td>\n";
	}
	echo "<td valign=\"middle\">\n";
	bouton_actualiser($onglet, $DEBUG);
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	echo "<CENTER>\n";

}

// verifie les parametre de le nouvelle demande :si ok : enregistre la demande dans table conges_periode
function new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $mysql_link, $DEBUG=FALSE)
{
//$DEBUG=TRUE;
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	//echo " $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type<br><br>\n";

	// verif validité des valeurs saisies
	$valid=verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $DEBUG);

	// verifie que le solde de conges sera encore positif après validation
	if($_SESSION['config']['solde_toujours_positif']==TRUE)
	{
		$valid = $valid && verif_solde_user($_SESSION['userlogin'], $new_type, $new_nb_jours, $mysql_link, $DEBUG);
	}

	if($valid==TRUE)
	{
      $type_abs = get_type_abs($new_type, $mysql_link, $DEBUG) ; 
      if( ($type_abs=="conges") || ($type_abs=="conges_exceptionnels") )
			$new_etat="demande" ;
		else
			$new_etat="ok" ;

		$new_comment=addslashes($new_comment);
		echo $_SESSION['userlogin']."---$new_debut---$new_demi_jour_deb---$new_fin---$new_demi_jour_fin---$new_nb_jours---$new_comment---$new_type---$new_etat<br>\n";

		$periode_num=insert_dans_periode($_SESSION['userlogin'], $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $new_etat, 0, $mysql_link, $DEBUG);

		if($periode_num!=0)
		{
			echo $_SESSION['lang']['form_modif_ok']." !<br><br>\n";
			//envoi d'un mail d'alerte au responsable (si demandé dans config de php_conges)
			if($_SESSION['config']['mail_new_demande_alerte_resp']==TRUE) {
				alerte_mail($_SESSION['userlogin'], ":responsable:", $periode_num, "new_demande", 
                            $mysql_link, $DEBUG);
            };
            /* echo "<pre> type_abs = ".$type_abs." </pre>" ;  */ 
            /* _intcalendar : si conges = absence , c'est une "accept_conges" , alors 
             envoyer un mail de confirmation au demandeur porteur evenement agenda */ 
            if ($type_abs == "absences") {
              alerte_mail($_SESSION['userlogin'], $_SESSION['userlogin'], $periode_num, 
                          "accept_conges", $mysql_link, $DEBUG); 
            };
		}
		else
			echo $_SESSION['lang']['form_modif_not_ok']." !<br><br>\n";
	}
	else
	{
			echo $_SESSION['lang']['resp_traite_user_valeurs_not_ok']." !<br><br>\n";
	}

		/* RETOUR PAGE PRINCIPALE */
		echo " <form action=\"$PHP_SELF?session=$session&onglet=demandes_en_cours\" method=\"POST\"> \n";
		echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_retour']."\">\n";
		echo " </form> \n";

}
/* _dpa: CARE fonction extremement dangereuse "tricky" :
   $new_debut_string = aaaa-mm-jj-<part> de la journee à déplacer a signifie absent matin 
   $new_fin_string   = aaaa-mm-jj-<part> de la journee choisie possible a signifie present matin 
   $moment_absence_ordinaire  = partie du jour ou on était absent a = matin 
   $moment_absence_souhaitee  = partie du jour ou on sera absent a = matin 

   en clair aaaa-mm-jj-a pour $new_debut_string signifie absence le matin 
            aaaa-mm-jj-a pour $new_fin_string   signifie absence l'apres-midi 

 */ 
function echange_absence_rtt($onglet, $new_debut_string, $new_fin_string, $new_comment, $moment_absence_ordinaire, $moment_absence_souhaitee, $mysql_link, $DEBUG=FALSE)
{
//$DEBUG=TRUE;

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	$duree_demande_1="";
	$duree_demande_2="";
	$valid=TRUE;

	if (($DEBUG==TRUE) || True) 
	{
		echo "echange_absence_rtt: $new_debut_string, $new_fin_string, $new_comment, $moment_absence_ordinaire, $moment_absence_souhaitee<br>\n";
	}


	// verif si les dates sont renseignées  (si ce n'est pas le cas, on ne verifie meme pas la suite !)
	// $new_debut et $new_fin sont des string au format : $year-$mois-$jour-X  (avec X = j pour "jour entier", a pour "a" (matin), et p pour "pm" (apres midi) )
	if( ($new_debut_string=="")||($new_fin_string=="") )
		$valid=FALSE;
	else
	{
		$date_1=explode("-", $new_debut_string);
		$year_debut=$date_1[0];
		$mois_debut=$date_1[1];
		$jour_debut=$date_1[2];
		$demi_jour_debut=$date_1[3];

		$new_debut="$year_debut-$mois_debut-$jour_debut";

		$date_2=explode("-", $new_fin_string);
		$year_fin=$date_2[0];
		$mois_fin=$date_2[1];
		$jour_fin=$date_2[2];
		$demi_jour_fin=$date_2[3];

		$new_fin="$year_fin-$mois_fin-$jour_fin";


		/********************************************/
		// traitement du jour d'absence à remplacer

		// verif de la concordance des demandes avec l'existant, et affectation de valeurs à entrer dans la database
		if($demi_jour_debut=="j") // on est absent la journee
		{
			if($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
			{
				$nouvelle_presence_date_1="J";
				$nouvelle_absence_date_1="N";
				$duree_demande_1="jour";
			}
			elseif($moment_absence_ordinaire=="a") // on demande à etre present le matin
			{
				$nouvelle_presence_date_1="M";
				$nouvelle_absence_date_1="A";
				$duree_demande_1="demi";
			}
			elseif($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
			{
				$nouvelle_presence_date_1="A";
				$nouvelle_absence_date_1="M";
				$duree_demande_1="demi";
			}
		}
		elseif($demi_jour_debut=="a") // on est absent le matin
		{
			if($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
			{
				$nouvelle_presence_date_1="J";
				$nouvelle_absence_date_1="N";
				$duree_demande_1="demi";
			}
			elseif($moment_absence_ordinaire=="a") // on demande à etre present le matin
			{
				if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
				{
					$nouvelle_presence_date_1="M";
					$nouvelle_absence_date_1="A";
				}
				else
				{
					$nouvelle_presence_date_1="J";
					$nouvelle_absence_date_1="N";
				}
				$duree_demande_1="demi";
			}
			elseif($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
			{
				if($DEBUG==TRUE) { echo "false_1<br>\n";}
				$valid=FALSE;
			}
		}
		elseif($demi_jour_debut=="p") // on est absent l'aprem
		{
			if($moment_absence_ordinaire=="j") // on demande à etre present tte la journee
			{
				$nouvelle_presence_date_1="J";
				$nouvelle_absence_date_1="N";
				$duree_demande_1="demi";
			}
			elseif($moment_absence_ordinaire=="a") // on demande à etre present le matin
			{
				if($DEBUG==TRUE) { echo "false_2<br>\n";}
				$valid=FALSE;
			}
			elseif($moment_absence_ordinaire=="p") // on demande à etre present l'aprem
			{
				if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
				{
					$nouvelle_presence_date_1="A";
					$nouvelle_absence_date_1="M";
				}
				else
				{
					$nouvelle_presence_date_1="J";
					$nouvelle_absence_date_1="N";
				}
				$duree_demande_1="demi";
			}
		}
		else
			$valid=FALSE;


		/**********************************************/
		// traitement du jour de présence à remplacer

		// verif de la concordance des demandes avec l'existant, et affectation de valeurs à entrer dans la database
		if($demi_jour_fin=="j") // on est present la journee
		{
			if($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
			{
				$nouvelle_presence_date_2="N";
				$nouvelle_absence_date_2="J";
				$duree_demande_2="jour";
			}
			elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
			{
				$nouvelle_presence_date_2="A";
				$nouvelle_absence_date_2="M";
				$duree_demande_2="demi";
			}
			elseif($moment_absence_souhaitee=="p") // on demande à etre absent l'aprem
			{
				$nouvelle_presence_date_2="M";
				$nouvelle_absence_date_2="A";
				$duree_demande_2="demi";
			}
		}
		elseif($demi_jour_fin=="a") // on est present le matin
		{
			if($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
			{
				$nouvelle_presence_date_2="N";
				$nouvelle_absence_date_2="J";
				$duree_demande_2="demi";
			}
			elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
			{
				if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
				{
					$nouvelle_presence_date_2="A";
					$nouvelle_absence_date_2="M";
				}
				else
				{
					$nouvelle_presence_date_2="N";
					$nouvelle_absence_date_2="j";
				}
				$duree_demande_2="demi";
			}
			elseif($moment_absence_souhaitee=="p") // on demande à etre absent l'aprem
			{
				if($DEBUG==TRUE) { echo "false_3<br>\n";}
				$valid=FALSE;
			}
		}
		elseif($demi_jour_fin=="p") // on est present l'aprem
		{
			if($moment_absence_souhaitee=="j") // on demande à etre absent tte la journee
			{
				$nouvelle_presence_date_2="N";
				$nouvelle_absence_date_2="J";
				$duree_demande_2="demi";
			}
			elseif($moment_absence_souhaitee=="a") // on demande à etre absent le matin
			{
				if($DEBUG==TRUE) { echo "false_4<br>\n";}
				$valid=FALSE;
			}
			elseif($moment_absence_souhaitee=="p") // on demande à etre absent l'aprem
			{
				if($new_debut==$new_fin) // dans ce cas, on veut intervertir 2 demi-journées
				{
					$nouvelle_presence_date_2="M";
					$nouvelle_absence_date_2="A";
				}
				else
				{
					$nouvelle_presence_date_2="N";
					$nouvelle_absence_date_2="J";
				}
				$duree_demande_2="demi";
			}
		}
		else
		{
			if($DEBUG==TRUE) { echo "false_5<br>\n";}
			$valid=FALSE;
		}


		if($DEBUG==TRUE)
		{
			echo "$new_debut - $demi_jour_debut :: $new_fin - $demi_jour_fin<br>\n";
			echo "$duree_demande_1  :: $duree_demande_2<br>\n";
		}
		// verif de la concordance des durée (journée avec journée ou 1/2 journée avec1/2 journée)
		if( ($duree_demande_1=="") || ($duree_demande_2=="") || ($duree_demande_1!=$duree_demande_2) )
			$valid=FALSE;
	}

	if($valid)
	{
		echo $_SESSION['userlogin']."---$new_debut---$new_fin---$new_comment<br>\n" ;
        // _dpa array qui sera passe a 
        $lperiode = array('deb' => $new_debut ,
                          'fin' => $new_fin ); 

		// insert du jour d'absence ordinaire (qui n'en sera plus un ou qu'a moitie ...)
		// e_presence = N (non) , J (jour entier) , M (matin) ou A (apres-midi)
		// verif si le couple user/date1 existe dans conges_echange_rtt ...
		$sql_verif_echange1="SELECT e_absence, e_presence from conges_echange_rtt WHERE e_login='".$_SESSION['userlogin']."' AND e_date_jour='$new_debut' ";
		$result_verif_echange1 = requete_mysql($sql_verif_echange1, $mysql_link, "echange_absence_rtt", $DEBUG) ;

		$count_verif_echange1=mysql_num_rows($result_verif_echange1);

        /* $new_comment=addslashes($new_comment); */
        /* _protectsql_ dpa */ 
        $new_comment = mysql_escape_string($new_comment);

		// si le couple user/date1 existe dans conges_echange_rtt : on update
		if($count_verif_echange1!=0)
		{
			//$resultat1=mysql_fetch_array($result_verif_echange1);
			//if($resultatverif_echange1['e_absence'] == 'N' )
			$sql1 = "UPDATE conges_echange_rtt
					SET e_absence='$nouvelle_absence_date_1', e_presence='$nouvelle_presence_date_1', e_comment='$new_comment'
					WHERE e_login='".$_SESSION['userlogin']."' AND e_date_jour='$new_debut' ";
		}
		else // sinon : on insert
		{
			$sql1 = "INSERT into conges_echange_rtt (e_login, e_date_jour, e_absence, e_presence, e_comment)
					VALUES ('".$_SESSION['userlogin']."','$new_debut','$nouvelle_absence_date_1', '$nouvelle_presence_date_1', '$new_comment')" ;
		}
		$result1 = requete_mysql($sql1, $mysql_link, "echange_absence_rtt", $DEBUG);

		// insert du jour d'absence souhaité (qui en devient un)
		// e_absence = N (non) , J (jour entier) , M (matin) ou A (apres-midi)
		// verif si le couple user/date2 existe dans conges_echange_rtt ...
		$sql_verif_echange2="SELECT e_absence, e_presence from conges_echange_rtt WHERE e_login='".$_SESSION['userlogin']."' AND e_date_jour='$new_fin' ";
		$result_verif_echange2 = requete_mysql($sql_verif_echange2, $mysql_link, "echange_absence_rtt", $DEBUG);

		$count_verif_echange2=mysql_num_rows($result_verif_echange2);

		// si le couple user/date2 existe dans conges_echange_rtt : on update
		if($count_verif_echange2!=0)
		{
			$sql2 = "UPDATE conges_echange_rtt
					SET e_absence='$nouvelle_absence_date_2', e_presence='$nouvelle_presence_date_2', e_comment='$new_comment'
					WHERE e_login='".$_SESSION['userlogin']."' AND e_date_jour='$new_fin' ";
		}
		else // sinon: on insert
		{
			$sql2 = "INSERT into conges_echange_rtt (e_login, e_date_jour, e_absence, e_presence, e_comment)
					VALUES ('".$_SESSION['userlogin']."','$new_fin','$nouvelle_absence_date_2', '$nouvelle_presence_date_2', '$new_comment')" ;
		}
		$result2 = requete_mysql($sql2, $mysql_link, "echange_absence_rtt", $DEBUG) ;

        if ($result2) { // success
          $comment_log = "echange absence - rtt  ($new_debut_string / $new_fin_string)";
          log_action(0, "", $_SESSION['userlogin'], $comment_log, $mysql_link, $DEBUG);
          echo $_SESSION['lang']['form_modif_ok']." !<br><br>\n";
          //envoi d'un mail d'alerte au responsable (si demandé dans config de php_conges)

          // _dpa revision : au lieu de $result2, on passe objet operiode = array 
          if ($_SESSION['config']['mail_echange_rtt_alerte_resp']) { 
            alerte_mail($_SESSION['userlogin'], ":responsable:", $lperiode, "echange_absence_rtt", 
                        $mysql_link, $DEBUG);
          };
        }
        else { // echec 
          echo $_SESSION['lang']['form_modif_not_ok']." !<br><br>\n";
        };
	}
	else
	{
			echo $_SESSION['lang']['resp_traite_user_valeurs_not_ok']." !<br><br>\n";
	}
		/* RETOUR PAGE PRINCIPALE */
		echo " <form action=\"$PHP_SELF?session=$session\" method=\"POST\"> \n";
		//echo " <form action=\"$PHP_SELF?session=$session&onglet=$onglet\" method=\"POST\"> \n";
		echo "<input type=\"submit\" value=\"Retour\">\n";
		echo " </form> \n";
}

//affiche le tableau des demandes en cours
function affichage_demandes_en_cours($tri_date, $onglet, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// Récupération des informations
	// on ne recup QUE les periodes de type "conges"(cf table conges_type_absence) ET QUE les demandes
	$sql3 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, p_num, ta_libelle
			FROM conges_periode as a, conges_type_absence as b
			WHERE a.p_login = '".$_SESSION['userlogin']."'
			AND (a.p_type=b.ta_id)
			AND ( (b.ta_type='conges') OR (b.ta_type='conges_exceptionnels') )
			AND ((p_etat='demande') OR (p_etat='valid')) ";
	if($tri_date=="descendant")
		$sql3=$sql3." ORDER BY p_date_deb DESC ";
	else
		$sql3=$sql3." ORDER BY p_date_deb ASC ";
	$ReqLog3 = requete_mysql($sql3, $mysql_link, "affichage_demandes_en_cours", $DEBUG) ;

	$count3=mysql_num_rows($ReqLog3);
	if($count3==0)
	{
		echo "<b>".$_SESSION['lang']['user_demandes_aucune_demande']."</b><br>\n";
	}
	else
	{
		// AFFICHAGE TABLEAU
		echo "<table cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n" ;
		echo "<tr>\n";
		echo "<td class=\"titre\">";
		echo " <a href=\"$PHP_SELF?session=$session&onglet=$onglet&tri_date=descendant\"><img src=\"../img/1downarrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>";
		echo $_SESSION['lang']['divers_debut_maj_1'] ;
		echo " <a href=\"$PHP_SELF?session=$session&onglet=$onglet&tri_date=ascendant\"><img src=\"../img/1uparrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>";
		echo "</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_fin_maj_1']."</td>" ;
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_type_maj_1']."</td>" ;
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_nb_jours_pris_maj_1']."</td>" ;
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_comment_maj_1']."</td>" ;
		echo "<td></td><td></td>" ;
		if($_SESSION['config']['affiche_date_traitement']==TRUE)
		{
			echo "<td class=\"titre\">".$_SESSION['lang']['divers_date_traitement']."</td>\n" ;
		}
		echo "</tr>\n" ;

		while ($resultat3 = mysql_fetch_array($ReqLog3))
		{
			$sql_p_date_deb = eng_date_to_fr($resultat3["p_date_deb"], $DEBUG);
			$sql_p_demi_jour_deb = $resultat3["p_demi_jour_deb"];
			if($sql_p_demi_jour_deb=="am") $demi_j_deb="mat";  else $demi_j_deb="aprm";
			$sql_p_date_fin = eng_date_to_fr($resultat3["p_date_fin"], $DEBUG);
			$sql_p_demi_jour_fin = $resultat3["p_demi_jour_fin"];
			if($sql_p_demi_jour_fin=="am") $demi_j_fin="mat";  else $demi_j_fin="aprm";
			$sql_p_nb_jours = $resultat3["p_nb_jours"];
			$sql_p_commentaire = $resultat3["p_commentaire"];
			//$sql_p_type = $resultat3["p_type"];
			$sql_p_type = $resultat3["ta_libelle"];
			$sql_p_etat = $resultat3["p_etat"];
			$sql_p_date_demande = $resultat3["p_date_demande"];
			$sql_p_date_traitement = $resultat3["p_date_traitement"];
			$sql_p_num = $resultat3["p_num"];

			// si on peut modifier une demande :on defini le lien à afficher
			if($_SESSION['config']['interdit_modif_demande']==FALSE)
			{
				//on ne peut pas modifier une demande qui a déja été validé une fois (si on utilise la double validation)
				if($sql_p_etat=="valid")
					$user_modif_demande="&nbsp;";
				else
					$user_modif_demande="<a href=\"user_modif_demande.php?session=$session&p_num=$sql_p_num&onglet=$onglet\">".$_SESSION['lang']['form_modif']."</a>" ;
			}
			$user_suppr_demande="<a href=\"user_suppr_demande.php?session=$session&p_num=$sql_p_num&onglet=$onglet\">".$_SESSION['lang']['form_supprim']."</a>" ;
			echo "<tr>\n" ;
			echo "<td class=\"histo\">$sql_p_date_deb _ $demi_j_deb</td><td class=\"histo\">$sql_p_date_fin _ $demi_j_fin</td>" ;
			echo "<td class=\"histo\">$sql_p_type</td>" ;
			echo "<td class=\"histo\">".affiche_decimal($sql_p_nb_jours, $DEBUG)."</td>" ;
			echo "<td class=\"histo\">$sql_p_commentaire</td>" ;
			if($_SESSION['config']['interdit_modif_demande']==FALSE)
			{
				echo "<td class=\"histo\">$user_modif_demande</td>" ;
			}
			echo "<td class=\"histo\">$user_suppr_demande</td>\n" ;
			if($_SESSION['config']['affiche_date_traitement']==TRUE)
			{
				echo "<td class=\"histo-left\">".$_SESSION['lang']['divers_demande']." : $sql_p_date_demande<br>".$_SESSION['lang']['divers_traitement']." : $sql_p_date_traitement</td>\n" ;
			}
			echo "</tr>\n" ;
		}
		echo "</table>\n" ;
	}
	echo "<br><br>\n\n" ;
}



//affiche le tableau de l'hitorique des conges
function affichage_historique_conges($tri_date, $year_affichage, $onglet, $mysql_link, $DEBUG=FALSE)
{
//$DEBUG=TRUE;
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// affichage de l'année et des boutons de défilement
	$year_affichage_prec = $year_affichage-1 ;
	$year_affichage_suiv = $year_affichage+1 ;

	echo "<b>";
	echo "<a href=\"$PHP_SELF?session=$session&onglet=historique_conges&year_affichage=$year_affichage_prec\"><<</a>";
	echo "&nbsp&nbsp&nbsp  $year_affichage &nbsp&nbsp&nbsp";
	echo "<a href=\"$PHP_SELF?session=$session&onglet=historique_conges&year_affichage=$year_affichage_suiv\">>></a>";
	echo "</b><br><br>\n";


	// Récupération des informations
	// on ne recup QUE les periodes de type "conges"(cf table conges_type_absence) ET pas les demandes
	$sql2 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, ta_libelle
			 FROM conges_periode as a, conges_type_absence as b
			WHERE a.p_login = '".$_SESSION['userlogin']."'
			AND (a.p_type=b.ta_id)
			AND ( (b.ta_type='conges') OR (b.ta_type='conges_exceptionnels') )
			AND (p_etat='ok' OR  p_etat='refus' OR  p_etat='annul')
			AND (p_date_deb LIKE '$year_affichage%' OR p_date_fin LIKE '$year_affichage%') ";

	if($tri_date=="descendant")
		$sql2=$sql2." ORDER BY p_date_deb DESC ";
	else
		$sql2=$sql2." ORDER BY p_date_deb ASC ";

	$ReqLog2 = requete_mysql($sql2, $mysql_link, "affichage_historique_conges", $DEBUG) ;

	$count2=mysql_num_rows($ReqLog2);
	if($count2==0)
	{
		echo "<b>".$_SESSION['lang']['user_conges_aucun_conges']."</b><br>\n";
	}
	else
	{
		// AFFICHAGE TABLEAU
		echo "<table cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n";
		echo "<tr>\n";
		echo " <td class=\"titre\">\n";
		echo " <a href=\"$PHP_SELF?session=$session&onglet=$onglet&tri_date=descendant\"><img src=\"../img/1downarrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
		echo $_SESSION['lang']['divers_debut_maj_1'] ;
		echo " <a href=\"$PHP_SELF?session=$session&onglet=$onglet&tri_date=ascendant\"><img src=\"../img/1uparrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
		echo " </td>\n";
		echo " <td class=\"titre\">".$_SESSION['lang']['divers_fin_maj_1']."</td>\n";
		echo " <td class=\"titre\">".$_SESSION['lang']['divers_nb_jours_maj_1']."</td>\n";
		echo " <td class=\"titre\">".$_SESSION['lang']['divers_comment_maj_1']."</td>\n";
		echo " <td class=\"titre\">".$_SESSION['lang']['divers_type_maj_1']."</td>\n";
		echo " <td class=\"titre\">".$_SESSION['lang']['divers_etat_maj_1']."</td>\n";
		if($_SESSION['config']['affiche_date_traitement']==TRUE)
		{
			echo "<td class=\"titre\">".$_SESSION['lang']['divers_date_traitement']."</td>\n" ;
		}

		echo "</tr>\n";

		while ($resultat2 = mysql_fetch_array($ReqLog2))
		{
			$sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"], $DEBUG);
			$sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
			if($sql_p_demi_jour_deb=="am") $demi_j_deb="mat";  else $demi_j_deb="aprm";
			$sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"], $DEBUG);
			$sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
			if($sql_p_demi_jour_fin=="am") $demi_j_fin="mat";  else $demi_j_fin="aprm";
			$sql_p_nb_jours = $resultat2["p_nb_jours"];
			$sql_p_commentaire = $resultat2["p_commentaire"];
			//$sql_p_type = $resultat2["p_type"];
			$sql_p_type = $resultat2["ta_libelle"];
			$sql_p_etat = $resultat2["p_etat"];
			$sql_p_motif_refus=$resultat2["p_motif_refus"] ;
			$sql_p_date_demande = $resultat2["p_date_demande"];
			$sql_p_date_traitement = $resultat2["p_date_traitement"];

			echo "<tr>\n";
				echo "<td class=\"histo\">$sql_p_date_deb _ $demi_j_deb</td>\n";
				echo "<td class=\"histo\">$sql_p_date_fin _ $demi_j_fin</td>\n";
				echo "<td class=\"histo\">$sql_p_nb_jours</td>\n";
				echo "<td class=\"histo\">$sql_p_commentaire";
				if($sql_p_etat=="refus")
				{
					if($sql_p_motif_refus=="")
						$sql_p_motif_refus=$_SESSION['lang']['divers_inconnu'];
					echo "<br><i>".$_SESSION['lang']['divers_motif_refus']." : $sql_p_motif_refus</i>";
//					echo "<br><i>motif : $sql_p_motif_refus</i>";
				}
				elseif($sql_p_etat=="annul")
				{
					if($sql_p_motif_refus=="")
						$sql_p_motif_refus=$_SESSION['lang']['divers_inconnu'];
					echo "<br><i>".$_SESSION['lang']['divers_motif_annul']." : $sql_p_motif_refus</i>";
//					echo "<br><i>motif : $sql_p_motif_refus</i>";
				}
				echo "</td>\n";

				echo "<td class=\"histo\">$sql_p_type</td>\n";
				echo "<td class=\"histo\">";
				if($sql_p_etat=="refus")
					echo $_SESSION['lang']['divers_refuse'];
				elseif($sql_p_etat=="annul")
					echo $_SESSION['lang']['divers_annule'];
				else
					echo "$sql_p_etat";
				echo "</td>\n" ;
				if($_SESSION['config']['affiche_date_traitement']==TRUE)
				{
//					echo "<td class=\"histo-left\">".$_SESSION['lang']['divers_demande']." : $sql_p_date_demande<br>".$_SESSION['lang']['divers_traitement']." : $sql_p_date_traitement</td>\n" ;
					echo "<td class=\"histo-left\">".$_SESSION['lang']['divers_demande']." : $sql_p_date_demande<br>\n";
					$text_lang_a_afficher="divers_traitement_$sql_p_etat" ; // p_etat='ok' OR  p_etat='refus' OR  p_etat='annul' .....
					echo $_SESSION['lang'][$text_lang_a_afficher]." : $sql_p_date_traitement</td>\n" ;
				}
				echo "</tr>\n";
		}
		echo "</table>\n\n";
	}
	echo "<br><br>\n" ;
}



//affiche le tableau de l'hitorique des absences
function affichage_historique_absences($tri_date, $year_affichage, $onglet, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// affichage de l'année et des boutons de défilement
	$year_affichage_prec = $year_affichage-1 ;
	$year_affichage_suiv = $year_affichage+1 ;

	echo "<b>";
	echo "<a href=\"$PHP_SELF?session=$session&onglet=historique_autres_absences&year_affichage=$year_affichage_prec\"><<</a>";
	echo "&nbsp&nbsp&nbsp  $year_affichage &nbsp&nbsp&nbsp";
	echo "<a href=\"$PHP_SELF?session=$session&onglet=historique_autres_absences&year_affichage=$year_affichage_suiv\">>></a>";
	echo "</b><br><br>\n";


	// Récupération des informations
	$sql4 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_motif_refus, p_date_demande, p_date_traitement, p_num, ta_libelle
			FROM conges_periode as a, conges_type_absence as b
			WHERE a.p_login = '".$_SESSION['userlogin']."'
			AND (a.p_type=b.ta_id)
			AND (b.ta_type='absences')
			AND (p_date_deb LIKE '$year_affichage%' OR p_date_fin LIKE '$year_affichage%') ";

	if($tri_date=="descendant")
		$sql4=$sql4." ORDER BY p_date_deb DESC ";
	else
		$sql4=$sql4." ORDER BY p_date_deb ASC ";

	$ReqLog4 = requete_mysql($sql4, $mysql_link, "affichage_historique_absences", $DEBUG) ;

	$count4=mysql_num_rows($ReqLog4);
	if($count4==0)
	{
		echo "<b>".$_SESSION['lang']['user_abs_aucune_abs']."</b><br>\n";
	}
	else
	{
		// AFFICHAGE TABLEAU
		echo "<table cellpadding=\"2\"  class=\"tablo\" width=\"80%\">\n";
		echo "<tr>\n";
		echo "<td class=\"titre\">\n";
		echo " <a href=\"$PHP_SELF?session=$session&onglet=$onglet&tri_date=descendant\"><img src=\"../img/1downarrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
		echo $_SESSION['lang']['divers_debut_maj_1'] ;
		echo " <a href=\"$PHP_SELF?session=$session&onglet=$onglet&tri_date=ascendant\"><img src=\"../img/1uparrow-16x16.png\" width=\"16\" height=\"16\" border=\"0\" title=\"trier\"></a>\n";
		echo "</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_fin_maj_1']."</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_nb_jours_maj_1']."</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_comment_maj_1']."</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['user_abs_type']."</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_etat_maj_1']."</td>\n";
		echo "<td></td><td></td>\n";
		if($_SESSION['config']['affiche_date_traitement']==TRUE)
		{
			echo "<td class=\"titre\">".$_SESSION['lang']['divers_date_traitement']."</td>\n" ;
		}
		echo "</tr>\n";

		while ($resultat4 = mysql_fetch_array($ReqLog4))
		{
			$sql_login= $resultat4["p_login"];
			$sql_date_deb= eng_date_to_fr($resultat4["p_date_deb"], $DEBUG);
			$sql_p_demi_jour_deb = $resultat4["p_demi_jour_deb"];
			if($sql_p_demi_jour_deb=="am") $demi_j_deb="mat";  else $demi_j_deb="aprm";
			$sql_date_fin= eng_date_to_fr($resultat4["p_date_fin"], $DEBUG);
			$sql_p_demi_jour_fin = $resultat4["p_demi_jour_fin"];
			if($sql_p_demi_jour_fin=="am") $demi_j_fin="mat";  else $demi_j_fin="aprm";
			$sql_nb_jours= affiche_decimal($resultat4["p_nb_jours"], $DEBUG);
			$sql_commentaire= $resultat4["p_commentaire"];
			//$sql_type=$resultat4["p_type"];
			$sql_type=$resultat4["ta_libelle"];
			$sql_etat=$resultat4["p_etat"];
			$sql_motif_refus=$resultat4["p_motif_refus"] ;
			$sql_date_demande = $resultat4["p_date_demande"];
			$sql_date_traitement = $resultat4["p_date_traitement"];
			$sql_num= $resultat4["p_num"];

			// si le user a le droit de saisir lui meme ses absences et qu'elle n'est pas deja annulee, on propose de modifier ou de supprimer
			if(($sql_etat != "annul")&&($_SESSION['config']['user_saisie_mission']==TRUE))
			{
				$user_modif_mission="<a href=\"user_modif_demande.php?session=$session&p_num=$sql_num&onglet=$onglet\">".$_SESSION['lang']['form_modif']."</a>" ;
				$user_suppr_mission="<a href=\"user_suppr_demande.php?session=$session&p_num=$sql_num&onglet=$onglet\">".$_SESSION['lang']['form_supprim']."</a>" ;
			}
			else
			{
				$user_modif_mission=" - " ;
				$user_suppr_mission=" - " ;
			}

			echo "<tr>\n";
			echo "<td class=\"histo\">$sql_date_deb _ $demi_j_deb</td>\n";
			echo "<td class=\"histo\">$sql_date_fin _ $demi_j_fin</td>\n";
			echo "<td class=\"histo\">$sql_nb_jours</td>\n";
			echo "<td class=\"histo\">$sql_commentaire";
			if($sql_etat=="refus")
			{
				if($sql_motif_refus=="")
					$sql_motif_refus=$_SESSION['lang']['divers_inconnu'];
				echo "<br><i>".$_SESSION['lang']['divers_motif_refus']." : $sql_motif_refus</i>";
			}
			elseif($sql_etat=="annul")
			{
				if($sql_motif_refus=="")
					$sql_motif_refus=$_SESSION['lang']['divers_inconnu'];
				echo "<br><i>".$_SESSION['lang']['divers_motif_annul']." : $sql_motif_refus</i>";
			}
			echo "</td>\n";
			echo "<td class=\"histo\">$sql_type</td>\n";
			echo "<td class=\"histo\">";
			if($sql_etat=="refus")
				echo $_SESSION['lang']['divers_refuse'];
			elseif($sql_etat=="annul")
				echo $_SESSION['lang']['divers_annule'];
			else
				echo "$sql_etat";
			echo "</td>\n";
			echo "<td class=\"histo\">$user_modif_mission</td>\n";
			echo "<td class=\"histo\">$user_suppr_mission</td>\n" ;
			if($_SESSION['config']['affiche_date_traitement']==TRUE)
			{
				echo "<td class=\"histo-left\">".$_SESSION['lang']['divers_demande']." : $sql_date_demande<br>".$_SESSION['lang']['divers_traitement']." : $sql_date_traitement</td>\n" ;
			}
			echo "</tr>\n";
		}
		echo "</table>\n\n";
	}
	echo "<br><br>\n";
}



function change_passwd($mysql_link, $new_passwd1, $new_passwd2, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	if((strlen($new_passwd1)==0) || (strlen($new_passwd2)==0) || ($new_passwd1!=$new_passwd2)) // si les 2 passwd sont vides ou differents
	{
		echo $_SESSION['lang']['user_passwd_error']."<br>\n" ;
	}
	else
	{
		$passwd_md5=md5($new_passwd1);
		$sql1 = "UPDATE conges_users SET  u_passwd='$passwd_md5' WHERE u_login='".$_SESSION['userlogin']."' " ;
		$result = requete_mysql($sql1, $mysql_link, "change_passwd", $DEBUG) ;

		if($result==TRUE)
			echo $_SESSION['lang']['form_modif_ok']." <br><br> \n";
		else
			echo $_SESSION['lang']['form_mofif_not_ok']."<br><br> \n";
	}

	$comment_log = "changement Password";
	log_action(0, "", $_SESSION['userlogin'], $comment_log, $mysql_link, $DEBUG);

	/* APPEL D'UNE AUTRE PAGE */
	echo " <form action=\"$PHP_SELF?session=$session\" method=\"POST\"> \n";
	echo " <input type=\"submit\" value=\"Retour\">\n";
	echo " </form> \n";

}
// devient une facade 
function verif_solde_user($user_login, $type_conges, $nb_jours, $mysql_link, $DEBUG=FALSE)
{
  $comment = array() ; 
  $demencours = 0 ; 
  $soldeuser = 0 ; 
  $verif = verif_solde_user_engine($user_login,"user",$_SESSION['config']['solde_toujours_positif'],
                                   $type_conges, $nb_jours, $comment,$demencours,$soldeuser,
                                   $mysql_link,$DEBUG); 
  if (!$verif) { 
    echo "<br><font color=\"red\">".$comment."</font><br>\n" ; 
  }
  return $verif ;
} 
  


?>
