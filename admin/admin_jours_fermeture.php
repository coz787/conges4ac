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

include("../config_ldap.php");
include("../fonctions_conges.php") ;
include("../INCLUDE.PHP/fonction.php");
include("../INCLUDE.PHP/session.php");
include("../fonctions_calcul.php");


$DEBUG=FALSE;
//$DEBUG=TRUE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);


	/*** initialisation des variables ***/
	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$choix_action 				= getpost_variable("choix_action");
	$year						= getpost_variable("year", 0);
	$groupe_id					= getpost_variable("groupe_id");
	$id_type_conges				= getpost_variable("id_type_conges");
//	$new_date_debut				= getpost_variable("new_date_debut", date("d/m/Y")); // valeur par dédaut = aujourd'hui
//	$new_date_fin  				= getpost_variable("new_date_fin", date("d/m/Y"));   // valeur par dédaut = aujourd'hui
	$new_date_debut				= getpost_variable("new_date_debut"); // valeur par dédaut = aujourd'hui
	$new_date_fin  				= getpost_variable("new_date_fin");   // valeur par dédaut = aujourd'hui
	$fermeture_id  				= getpost_variable("fermeture_id", 0);
	$fermeture_date_debut		= getpost_variable("fermeture_date_debut");
	$fermeture_date_fin			= getpost_variable("fermeture_date_fin");
	$code_erreur				= getpost_variable("code_erreur", 0);
	/*************************************/
	if($new_date_debut=="")
	{
		if($year==0)
			$new_date_debut=date("d/m/Y") ;
		else
			$new_date_debut=date("d/m/Y", mktime(0,0,0, date("m"), date("d"), $year) ) ;
	}
	if($new_date_fin=="")
	{
		if($year==0)
			$new_date_fin=date("d/m/Y") ;
		else
			$new_date_fin=date("d/m/Y", mktime(0,0,0, date("m"), date("d"), $year) ) ;
	}

	if($DEBUG==TRUE) { echo "choix_action = $choix_action // year = $year // groupe_id = $groupe_id<br>\n"; }
	if($DEBUG==TRUE) { echo "new_date_debut = $new_date_debut // new_date_fin = $new_date_fin<br>\n"; }
	if($DEBUG==TRUE) { echo "fermeture_id = $fermeture_id // fermeture_date_debut = $fermeture_date_debut // fermeture_date_fin = $fermeture_date_fin<br>\n"; }


	//connexion mysql
	$mysql_link = connexion_mysql() ;

	// initialisation de l'action à effectuer
	if($choix_action=="")
	{
		// si pas de gestion par groupe
		if($_SESSION['config']['gestion_groupes']==FALSE)
			 $choix_action="saisie_dates";
		// si gestion par groupe et fermeture_par_groupe
		elseif(($_SESSION['config']['fermeture_par_groupe']==TRUE) && ($groupe_id=="") )
			 $choix_action="saisie_groupe";
		else
			 $choix_action="saisie_dates";
	}

	// init de l'annee
	if($year ==0)
		$year= date("Y");


	/***********************************/
	/*  VERIF DES DATES RECUES   */
	//
	$tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
	$timestamp_date_debut = mktime(0,0,0, $tab_date_debut[1], $tab_date_debut[0], $tab_date_debut[2]) ;
	$date_debut_yyyy_mm_dd = $tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0] ;
	$tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
	$timestamp_date_fin = mktime(0,0,0, $tab_date_fin[1], $tab_date_fin[0], $tab_date_fin[2]) ;
	$date_fin_yyyy_mm_dd = $tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0] ;
	$timestamp_today = mktime(0,0,0, date("m"), date("d"), date("Y")) ;

	if($DEBUG==TRUE) { echo "timestamp_date_debut = $timestamp_date_debut // timestamp_date_fin = $timestamp_date_fin // timestamp_today = $timestamp_today<br>\n"; }

	// on verifie si les jours fériés de l'annee de la periode saisie sont enregistrés : sinon BUG au calcul des soldes des users !
	if( (verif_jours_feries_saisis($date_debut_yyyy_mm_dd, $mysql_link, $DEBUG)==FALSE)
	    && (verif_jours_feries_saisis($date_fin_yyyy_mm_dd, $mysql_link, $DEBUG)==FALSE) )
		$code_erreur=1 ;  // code erreur : jour feriés non saisis

	if($choix_action=="commit_new_fermeture")
	{
		// on verifie que $new_date_debut est anterieure a $new_date_fin
		if($timestamp_date_debut > $timestamp_date_fin)
			$code_erreur=2 ;  // code erreur : $new_date_debut est posterieure a $new_date_fin
		// on verifie que ce ne sont pas des dates passées
		elseif($timestamp_date_debut < $timestamp_today)
			$code_erreur=3 ;  // code erreur : saisie de date passée

		// on ne verifie QUE si date_debut ou date_fin sont !=  d'aujourd'hui
		// (car aujourd'hui est la valeur par dédaut des dates, et on ne peut saisir aujourd'hui puisque c'est fermé !)
		elseif( ($timestamp_date_debut==$timestamp_today) || ($timestamp_date_fin==$timestamp_today) )
		{
			$code_erreur=4 ;  // code erreur : saisie de aujourd'hui
		}
		else
		// on verifie si la periode saisie ne chevauche pas une :
		// fabrication et initialisation du tableau des demi-jours de la date_debut à la date_fin
		{
			$tab_periode_calcul = make_tab_demi_jours_periode($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, "am", "pm", $DEBUG);
			if(verif_periode_chevauche_periode_groupe($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, $tab_periode_calcul, $groupe_id, $mysql_link, $DEBUG) == TRUE)
				$code_erreur=5 ;  // code erreur : fermeture chevauche une periode deja saisie
		}
	}
	if($code_erreur!=0)
		 $choix_action="saisie_dates";   // comme cela, on renvoit sur la saisie de dates



	/***********************************/
	// AFFICHAGE DE LA PAGE
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
	echo "<link href=\"../".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
	echo "<title>".$_SESSION['config']['titre_application']."</title>\n";
	echo "</head>\n";

	echo "<body>\n";
	echo "<center>\n";
	echo "<h1>".$_SESSION['lang']['admin_jours_fermeture_titre']."  $year</h1>\n";


	if($choix_action=="saisie_groupe")
         	saisie_groupe_fermeture($mysql_link, $DEBUG);
	elseif($choix_action=="saisie_dates")
	{
			include("../fonctions_javascript_calendrier.php");
			affiche_javascript_et_css_des_calendriers();
			if($groupe_id=="")     // choix du groupe n'a pas été fait ($_SESSION['config']['fermeture_par_groupe']==FALSE)
				$groupe_id=0;
	        saisie_dates_fermeture($year, $groupe_id, $new_date_debut, $new_date_fin, $code_erreur, $mysql_link, $DEBUG);
	}
	elseif($choix_action=="commit_new_fermeture")
	        commit_new_fermeture($new_date_debut, $new_date_fin, $groupe_id, $id_type_conges, $mysql_link, $DEBUG);
	elseif($choix_action=="annul_fermeture")
	        confirm_annul_fermeture($fermeture_id, $fermeture_date_debut, $fermeture_date_fin, $DEBUG);
	elseif($choix_action=="commit_annul_fermeture")
	        commit_annul_fermeture($fermeture_id, $groupe_id, $mysql_link, $DEBUG);

	mysqli_close($mysql_link);

	echo "</center>\n";
	echo "</body>\n";
	echo "</html>\n";




/***************************************************************/
/**********  FONCTIONS  ****************************************/

function saisie_groupe_fermeture($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();


	echo "<h2>fermeture pour tous ou pour un groupe ?</h2>\n";

	echo "<table cellpadding=\"2\" cellspacing=\"3\" border=\"1\" >\n";
	echo "<tr align=\"center\">\n";
	echo "<td valign=\"top\" class=\"histo\">\n";
		/********************/
		/* Choix Tous       */
		/********************/

		// AFFICHAGE TABLEAU
		echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n" ;
		//table contenant les bountons
		echo "<table cellpadding=\"2\" cellspacing=\"3\" border=\"0\" >\n";
			echo "<tr align=\"center\">\n";
			echo "<td valign=\"top\">\n";
			echo "<b>".$_SESSION['lang']['admin_jours_fermeture_fermeture_pour_tous']." !</b><br>&nbsp;\n";
			echo "</td>\n";
			echo "</tr>\n";

			echo "<tr align=\"center\">\n";
			echo "<td valign=\"top\">\n";
			echo "&nbsp;\n";
			echo "</td>\n";
			echo "</tr>\n";

			echo "<tr align=\"center\">\n";
			echo "<td>\n";
				echo "<input type=\"hidden\" name=\"groupe_id\" value=\"0\">\n";
				echo "<input type=\"hidden\" name=\"choix_action\" value=\"saisie_dates\">\n";
				echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">  \n";
			echo "</td>\n";
			echo "</tr>\n";
		echo "</table>\n";
		echo "</form>\n" ;
	echo "</td>\n";
	echo "<td valign=\"top\" class=\"histo\">\n";
		/********************/
		/* Choix Groupe     */
		/********************/
		// Récuperation des informations :
		$sql_gr = "SELECT g_gid, g_groupename, g_comment FROM conges_groupe ORDER BY g_groupename"  ;

		// AFFICHAGE TABLEAU

		echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n" ;
		//table contenant les bountons
		echo "<table cellpadding=\"2\" cellspacing=\"3\" border=\"0\" >\n";
			echo "<tr align=\"center\">\n";
			echo "<td valign=\"top\">\n";
			echo "<b>".$_SESSION['lang']['admin_jours_fermeture_fermeture_par_groupe'].".</b><br>".$_SESSION['lang']['resp_ajout_conges_choix_groupe']."\n";
			echo "</td>\n";
			echo "</tr>\n";

			echo "<tr align=\"center\">\n";
			echo "<td valign=\"top\">\n";
			$ReqLog_gr = requete_mysql($sql_gr, $mysql_link, "saisie_groupe_fermeture", $DEBUG);
			echo "<select name=\"groupe_id\">";
			while ($resultat_gr = mysqli_fetch_array($ReqLog_gr))
			{
				$sql_gid=$resultat_gr["g_gid"] ;
				$sql_group=$resultat_gr["g_groupename"] ;
				$sql_comment=$resultat_gr["g_comment"] ;

				echo "<option value=\"$sql_gid\">$sql_group";
			}
			echo "</select>";
			echo "</td>\n";
			echo "</tr>\n";

			echo "<tr align=\"center\">\n";
			echo "<td>\n";
				echo "<input type=\"hidden\" name=\"choix_action\" value=\"saisie_dates\">\n";
				echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">  \n";
			echo "</td>\n";
			echo "</tr>\n";
		echo "</table>\n";
		echo "</form>\n" ;
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	echo "<br>\n" ;
	echo "<form action=\"\" method=\"POST\">\n" ;
	echo "<input type=\"button\" value=\"".$_SESSION['lang']['form_cancel']."\" onClick=\"javascript:window.close();\">\n";
	echo "</form>\n" ;

}


function saisie_dates_fermeture($year, $groupe_id, $new_date_debut, $new_date_fin, $code_erreur, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	$tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
	$timestamp_date_debut = mktime(0,0,0, $tab_date_debut[1], $tab_date_debut[0], $tab_date_debut[2]) ;
	$date_debut_yyyy_mm_dd = $tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0] ;
	$tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
	$timestamp_date_fin = mktime(0,0,0, $tab_date_fin[1], $tab_date_fin[0], $tab_date_fin[2]) ;
	$date_fin_yyyy_mm_dd = $tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0] ;
	$timestamp_today = mktime(0,0,0, date("m"), date("d"), date("Y")) ;
//	$year=$tab_date_debut[2];


	// on construit le tableau de l'année considérée
	$tab_year=array();
	get_tableau_jour_fermeture($year, $tab_year,  $groupe_id, $mysql_link, $DEBUG);
	if($DEBUG==TRUE) { echo "tab_year = "; print_r($tab_year); echo "<br>\n"; }


	/************************************************/
	// GESTION DES ERREURS DE SAISIE :
	//
	// $code_erreur=1 ;  // code erreur : jour feriés non saisis
	// $code_erreur=2 ;  // code erreur : $new_date_debut est posterieure a $new_date_fin
	// $code_erreur=3 ;  // code erreur : saisie de date passée
	// $code_erreur=4 ;  // code erreur : saisie de aujourd'hui
	// $code_erreur=5 ;  // code erreur : fermeture chevauche une periode deja saisie

	// on verifie que $new_date_debut est anterieure a $new_date_fin
//	if($timestamp_date_debut > $timestamp_date_fin)
	if($code_erreur==2)
		echo "<br><center><h3><font color=\"red\">".$_SESSION['lang']['admin_jours_fermeture_dates_incompatibles'].".</font></h3></center><br><br>\n";
	// on verifie que ce ne sont pas des dates passées
//	if($timestamp_date_debut < $timestamp_today)
	if($code_erreur==3)
		echo "<br><center><h3><font color=\"red\">".$_SESSION['lang']['admin_jours_fermeture_date_passee_error'].".</font></h3></center><br><br>\n";
	// on verifie si les jours fériés de l'annee de la periode saisie sont enregistrés : sinon BUG au calcul des soldes des users !
//	if( (verif_jours_feries_saisis($date_debut_yyyy_mm_dd, $mysql_link, $DEBUG)==FALSE)
//	    && (verif_jours_feries_saisis($date_fin_yyyy_mm_dd, $mysql_link, $DEBUG)==FALSE) )
	if($code_erreur==1)
		echo "<br><center><h3><font color=\"red\">".$_SESSION['lang']['admin_jours_fermeture_annee_non_saisie'].".</font></h3></center><br><br>\n";

	// on verifie si la periode saisie ne chevauche pas une :
	// fabrication et initialisation du tableau des demi-jours de la date_debut à la date_fin
//	if( ($timestamp_date_debut!=$timestamp_today) || ($timestamp_date_fin!=$timestamp_today) )  // on ne verifie QUE si date_debut ou date_finc sont !=  d'aujourd'hui
////	{
////		echo "<br><center><h3><font color=\"red\">".$_SESSION['lang']['admin_jours_fermeture_fermeture_aujourd_hui'].".</font></h3></center><br><br>\n";
	if($code_erreur==4)
		echo "<br><center><h3><font color=\"red\">".$_SESSION['lang']['admin_jours_fermeture_fermeture_aujourd_hui'].".</font></h3></center><br><br>\n";
////	}
////	else
//	{
//		$tab_periode_calcul = make_tab_demi_jours_periode($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, "am", "pm", $DEBUG);
//		if(verif_periode_chevauche_periode_groupe($date_debut_yyyy_mm_dd, $date_fin_yyyy_mm_dd, $tab_periode_calcul, $groupe_id, $mysql_link, $DEBUG) == TRUE)
	if($code_erreur==5)
			echo "<br><center><h3><font color=\"red\">".$_SESSION['lang']['admin_jours_fermeture_chevauche_periode'].".</font></h3></center><br><br>\n";
//	}


	/************************************************/
	// FORMULAIRE DE SAISIE D'UNE NOUVELLE FERMETURE  + liens de navigation d'une annee a l'autre

	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"width=\"100%\">\n";
	echo "<tr align=\"center\">\n";
	// cellulle de gauche : bouton annee precedente
	echo "<td align=\"left\">\n";
		$annee_precedente=$year-1;
		echo "<a href=\"$PHP_SELF?session=$session&year=$annee_precedente&groupe_id=$groupe_id\"> << ".$_SESSION['lang']['admin_jours_chomes_annee_precedente']."</a>\n";
	echo "</td>\n";
	// cellulle centrale : saisie d'une fermeture
	echo "<td width=\"450\">\n";
	echo "<fieldset class=\"cal_saisie\">\n";
	echo "<legend class=\"boxlogin\">".$_SESSION['lang']['admin_jours_fermeture_new_fermeture']."</legend>\n";
	
	/************************************************/
	// FORMULAIRE
	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n" ;

	/************************************************/
	// table contenant le fieldset
	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
	echo "<tr align=\"center\">\n";
		echo "<td>\n";
		echo "<fieldset class=\"cal_saisie\">\n";
			// tableau contenant saisie de date (avec javascript pour afficher les calendriers)
			echo "<table cellpadding=\"2\" cellspacing=\"2\" border=\"0\">\n";
			echo "<tr align=\"center\">\n";
				echo "<td>\n";
				echo $_SESSION['lang']['divers_date_debut']." : <input type=\"text\" name=\"new_date_debut\" class=\"calendrier DatePicker_trigger\" value=\"$new_date_debut\" />\n" ;
				echo "</td>\n";
			echo "</tr>\n";
			echo "</table>\n";
		echo "</fieldset>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "<fieldset class=\"cal_saisie\">\n";
			// tableau contenant les mois
			echo "<table cellpadding=\"2\" cellspacing=\"2\" border=\"0\">\n";
			// ligne des boutons de défilement
			echo "<tr align=\"center\">\n";
				echo "<td>\n";
				echo $_SESSION['lang']['divers_date_fin']." : <input type=\"text\" name=\"new date_fin\" class=\"calendrier DatePicker_trigger\" value=\"$new_date_fin\"  />\n" ;
				echo "</td>\n";
			echo "</tr>\n";
			echo "</table>\n";
		echo "</fieldset>\n";
		echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	/************************************************/
	// SELECTION DU TYPE DE CONGES AUQUEL AFFECTER CETTE FERMETURE
	echo "<br>\n";
	// Affichage d'un SELECT de formulaire pour choix d'un type d'absence
	echo $_SESSION['lang']['admin_jours_fermeture_affect_type_conges'];
	affiche_select_conges_id($mysql_link, $DEBUG);
	echo "<br><br>\n";

	/************************************************/
	//table contenant les boutons
	echo "<table cellpadding=\"2\" cellspacing=\"3\" border=\"0\" >\n";
	echo "<tr align=\"center\">\n";
	echo "<td>\n";
	echo "<input type=\"hidden\" name=\"groupe_id\" value=\"$groupe_id\">\n";
	echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit_new_fermeture\">\n";
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">  \n";
	echo "<input type=\"button\" value=\"".$_SESSION['lang']['form_cancel']."\" onClick=\"javascript:window.close();\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	echo "</form>\n" ;
	// FIN FORMULAIRE
	
	echo "</fieldset>\n";
	echo "</td>\n";
	// cellulle de droite : bouton annee suivante
	echo "<td align=\"right\">\n";
		$annee_suivante=$year+1;
		echo "<a href=\"$PHP_SELF?session=$session&year=$annee_suivante&groupe_id=$groupe_id\">".$_SESSION['lang']['admin_jours_chomes_annee_suivante']." >> </a>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	echo "<br><br>\n";


	/************************************************/
	// HISTORIQUE DES FERMETURES

	$tab_periodes_fermeture = array();
	get_tableau_periodes_fermeture($tab_periodes_fermeture, $groupe_id, $mysql_link, $DEBUG);
	if(count($tab_periodes_fermeture)!=0)
	{
		echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		echo "<tr align=\"center\">\n";
		echo "<td>\n";
		echo "<fieldset class=\"cal_saisie\">\n";
		echo "<legend class=\"boxlogin\">".$_SESSION['lang']['admin_jours_fermeture_enregistrees']."</legend>\n";
		// tableau contenant saisie de date (avec javascript pour afficher les calendriers)
		echo "<table class=\"histo\">\n";
		foreach($tab_periodes_fermeture as $tab_periode)
		{
			$date_affiche_1=eng_date_to_fr($tab_periode['date_deb']);
			$date_affiche_2=eng_date_to_fr($tab_periode['date_fin']);
			$fermeture_id =($tab_periode['fermeture_id']);

			echo "<tr align=\"center\">\n";
			echo "<td>\n";
			echo $_SESSION['lang']['divers_du']." <b>$date_affiche_1</b> ".$_SESSION['lang']['divers_au']." <b>$date_affiche_2</b>  (id $fermeture_id)\n";
			echo "</td>\n";
			echo "<td>\n";
			echo "<a href=\"$PHP_SELF?session=$session&choix_action=annul_fermeture&fermeture_id=$fermeture_id&fermeture_date_debut=$date_affiche_1&fermeture_date_fin=$date_affiche_2\">".$_SESSION['lang']['admin_annuler_fermeture']."</a>\n";
			echo "</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo "</fieldset>\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
	}

	/************************************************/
	// CALENDRIER DES FERMETURES

	echo "<br><br>\n";
	affiche_calendrier_fermeture($year, $tab_year, $DEBUG);

}


function affiche_calendrier_fermeture($year, $tab_year, $DEBUG=FALSE)
{
			// tableau contenant les mois
			echo "<table cellpadding=\"2\" cellspacing=\"2\" border=\"0\">\n";

			// ligne janvier / fevrier / mars / avril
			echo "<tr align=\"center\" valign=\"top\">\n";
				echo "<td>\n"; // janvier
					affiche_calendrier_fermeture_mois($year, "01", $tab_year);
				echo "</td>\n";
				echo "<td>\n"; // fevrier
					affiche_calendrier_fermeture_mois($year, "02", $tab_year);
				echo "</td>\n";
				echo "<td>\n"; // mars
					affiche_calendrier_fermeture_mois($year, "03", $tab_year);
				echo "</td>\n";
				echo "<td>\n"; // avril
					affiche_calendrier_fermeture_mois($year, "04", $tab_year);
				echo "</td>\n";
			echo "</tr>\n";
			// ligne mai / juin / juillet / aout
			echo "<tr align=\"center\" valign=\"top\">\n";
				echo "<td>\n"; // mai
					affiche_calendrier_fermeture_mois($year, "05", $tab_year);
				echo "</td>\n";
				echo "<td>\n"; // juin
					affiche_calendrier_fermeture_mois($year, "06", $tab_year);
				echo "</td>\n";
				echo "<td>\n"; // juillet
					affiche_calendrier_fermeture_mois($year, "07", $tab_year);
				echo "</td>\n";
				echo "<td>\n"; // aout
					affiche_calendrier_fermeture_mois($year, "08", $tab_year);
				echo "</td>\n";
			echo "</tr>\n";
			// ligne septembre / octobre / novembre / decembre
			echo "<tr align=\"center\" valign=\"top\">\n";
				echo "<td>\n"; // septembre
					affiche_calendrier_fermeture_mois($year, "09", $tab_year);
				echo "</td>\n";
				echo "<td>\n"; // octobre
					affiche_calendrier_fermeture_mois($year, "10", $tab_year);
				echo "</td>\n";
				echo "<td>\n"; // novembre
					affiche_calendrier_fermeture_mois($year, "11", $tab_year);
				echo "</td>\n";
				echo "<td>\n"; // décembre
					affiche_calendrier_fermeture_mois($year, "12", $tab_year);
				echo "</td>\n";
			echo "</tr>\n";
			echo "</table>\n";
}


function  affiche_calendrier_fermeture_mois($year, $mois, $tab_year, $DEBUG=FALSE)
{
	$jour_today=date("j");
	$jour_today_name=date("D");

	$first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
	$mois_name=date_fr("F", $first_jour_mois_timestamp);
	$first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	if($first_jour_mois_rang==0)
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"250\" bgcolor=".$_SESSION['config']['semaine_bgcolor'].">\n";
	/* affichage  2 premieres lignes */
	echo "	<tr align=\"center\" bgcolor=\"".$_SESSION['config']['light_grey_bgcolor']."\"><td colspan=7 class=\"titre\"> $mois_name $year </td></tr>\n" ;
	echo "	<tr bgcolor=\"".$_SESSION['config']['light_grey_bgcolor']."\">\n";
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['lundi_1c']."</td>\n";
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['mardi_1c']."</td>\n";
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['mercredi_1c']."</td>\n";
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['jeudi_1c']."</td>\n";
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['vendredi_1c']."</td>\n";
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['samedi_1c']."</td>\n";
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['dimanche_1c']."</td>\n";
	echo "	</tr>\n" ;

	/* affichage ligne 1 du mois*/
	echo "<tr>\n";
	// affichage des cellules vides jusqu'au 1 du mois ...
	for($i=1; $i<$first_jour_mois_rang; $i++)
	{
		if( (($i==6)&&($_SESSION['config']['samedi_travail']==FALSE)) || (($i==7)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie2\">-</td>";
	}
	// affichage des cellules du 1 du mois à la fin de la ligne ...
	for($i=$first_jour_mois_rang; $i<8; $i++)
	{
		$j=$i-$first_jour_mois_rang+1 ;
		$j_timestamp=mktime (0,0,0,$mois,$j,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 2 du mois*/
	echo "<tr>\n";
	for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 3 du mois*/
	echo "<tr>\n";
	for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 4 du mois*/
	echo "<tr>\n";
	for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 5 du mois (peut etre la derniere ligne) */
	echo "<tr>\n";
	for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	for($i; $i<36-$first_jour_mois_rang+1; $i++)
	{
		if( (($i==35-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE)) || (($i==36-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie2\">-</td>";
	}
	echo "</tr>\n";

	/* affichage ligne 6 du mois (derniere ligne)*/
	echo "<tr>\n";
	for($i=36-$first_jour_mois_rang+1; checkdate($mois, $i, $year); $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$j_date=date("Y-m-d", $j_timestamp);
		$j_day=date("d", $j_timestamp);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		if(in_array ("$j_date", $tab_year))
			$td_second_class="fermeture";

		echo "<td  class=\"cal-saisie $td_second_class\">$j_day</td>";
	}
	for($i; $i<43-$first_jour_mois_rang+1; $i++)
	{
		if( (($i==42-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE)) || (($i==43-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)))
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie2\">-</td>";
	}
	echo "</tr>\n";

	echo "</table>\n";
}




function commit_new_fermeture($new_date_debut, $new_date_fin, $groupe_id, $id_type_conges, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();


	// on transforme les formats des dates
	$tab_date_debut=explode("/",$new_date_debut);   // date au format d/m/Y
	$date_debut=$tab_date_debut[2]."-".$tab_date_debut[1]."-".$tab_date_debut[0];
	$tab_date_fin=explode("/",$new_date_fin);   // date au format d/m/Y
	$date_fin=$tab_date_fin[2]."-".$tab_date_fin[1]."-".$tab_date_fin[0];
	if($DEBUG==TRUE) { echo "date_debut = $date_debut  // date_fin = $date_fin<br>\n"; }


	/*****************************/
	// on construit le tableau des users affectés par les fermetures saisies :
	if($groupe_id==0)  // fermeture pour tous !
		$list_users = get_list_all_users($mysql_link, $DEBUG);
	else
		$list_users = get_list_users_du_groupe($groupe_id, $mysql_link, $DEBUG);

	$tab_users = explode(",", $list_users);
	if($DEBUG==TRUE) { echo "tab_users =<br>\n"; print_r($tab_users) ; echo "<br>\n"; }

//******************************
// !!!!
	// type d'absence à modifier ....
//	$id_type_conges = 1 ; //"cp" : conges payes

	//calcul de l'ID de de la fermeture (en fait l'ID de la saisie de fermeture)
	$new_fermeture_id=get_last_fermeture_id($mysql_link, $DEBUG) + 1;

	/***********************************************/
	/** enregistrement des jours de fermetures   **/
	$tab_fermeture=array();
	for($current_date=$date_debut; $current_date <= $date_fin; $current_date=jour_suivant($current_date))
	{
		$tab_fermeture[] = $current_date;
	}
	if($DEBUG==TRUE) { echo "tab_fermeture =<br>\n"; print_r($tab_fermeture) ; echo "<br>\n"; }
	// on insere les nouvelles dates saisies dans conges_jours_fermeture
	$result=insert_year_fermeture($new_fermeture_id, $tab_fermeture, $groupe_id, $mysql_link, $DEBUG);

	$opt_debut='am';
	$opt_fin='pm';

	/*********************************************************/
	/** insersion des jours de fermetures pour chaque user  **/
	foreach($tab_users as $current_login)
	{
	    $current_login = trim($current_login);
		// on enleve les quotes qui ont été ajoutées lors de la creation de la liste
		$current_login = trim($current_login, "\'");

		// on compte le nb de jour à enlever au user (par periode et au total)
		// on ne met à jour la table conges_periode
		$nb_jours = 0;
		$comment="" ;

		$nb_jours = compter($current_login, $date_debut, $date_fin, $opt_debut, $opt_fin, -1, $comment, $mysql_link, $DEBUG);
		if ($DEBUG) echo "<br>user_login : " . $current_login . " nbjours : " . $nb_jours . "<br>\n";

		// on ne met à jour la table conges_periode .
		$commentaire = $_SESSION['lang']['divers_fermeture'];
		$etat = "ok" ;
		$num_periode = insert_dans_periode($current_login, $date_debut, $opt_debut, $date_fin, $opt_fin, $nb_jours, $commentaire, $id_type_conges, $etat, $new_fermeture_id, $mysql_link, $DEBUG) ;

		// mise à jour du solde de jours de conges pour l'utilisateur $current_login
		if ($nb_jours != 0)
		{
//		        $sql = "UPDATE conges_solde_user SET su_solde = su_solde - $nb_jours WHERE su_login='$current_login' AND su_abs_id = ( SELECT ta_id FROM conges_type_absence WHERE ta_short_libelle='cp') " ;
		        $sql = "UPDATE conges_solde_user SET su_solde = su_solde - $nb_jours WHERE su_login='$current_login' AND su_abs_id = $id_type_conges " ;
		        if ($DEBUG) echo "<br>$sql<br>$mysql_link<br>";
		        $ReqLog = requete_mysql($sql, $mysql_link, "commit_saisie_fermeture", $DEBUG);
		}
	}

	// on recharge les jours fermés dans les variables de session
	init_tab_jours_fermeture($_SESSION['userlogin'], $mysql_link, $DEBUG);
	
	if($result==TRUE)
		echo "<br>".$_SESSION['lang']['form_modif_ok'].".<br><br>\n";
	else
		echo "<br>".$_SESSION['lang']['form_modif_not_ok']." !<br><br>\n";

	$comment_log = "saisie des jours de fermeture de $date_debut a $date_fin" ;
	log_action(0, "", "", $comment_log, $mysql_link, $DEBUG);

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr><td align=\"center\">\n";
//	echo "	<input type=\"button\" value=\"".$_SESSION['lang']['form_close_window']."\" onClick=\"javascript:window.close();\">\n";
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_ok']."\">\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}



//function confirm_saisie_fermeture($tab_checkbox_j_ferme, $year_calendrier_saisie, $groupe_id, $DEBUG=FALSE)
function confirm_annul_fermeture($fermeture_id, $fermeture_date_debut, $fermeture_date_fin, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	echo "<table>\n";
	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo $_SESSION['lang']['divers_fermeture_du']."  <b>$fermeture_date_debut</b> ".$_SESSION['lang']['divers_au']." <b>$fermeture_date_fin</b>.<br>\n";
	echo "<b>".$_SESSION['lang']['admin_annul_fermeture_confirm'].".</b><br>\n";
	echo "<input type=\"hidden\" name=\"fermeture_id\" value=\"$fermeture_id\">\n";
	echo "<input type=\"hidden\" name=\"fermeture_date_debut\" value=\"$fermeture_date_debut\">\n";
	echo "<input type=\"hidden\" name=\"fermeture_date_fin\" value=\"$fermeture_date_fin\">\n";
	echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit_annul_fermeture\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "&nbsp;\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td width=\"100\" align=\"center\">\n";
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_confirmer_maj_1']."\">\n";
	echo "</form>\n";
	echo "</td>\n";

	echo "<td width=\"100\" align=\"center\">\n";
	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_cancel']."\">\n";
	echo "</form>\n";
	echo "</td>\n";

	echo "</tr>\n";
	echo "</table>\n";

}

function commit_annul_fermeture($fermeture_id, $groupe_id, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	if($DEBUG==TRUE) { echo "fermeture_id = $fermeture_id <br>\n"; }


	/*****************************/
	// on construit le tableau des users affectés par les fermetures saisies :
	if($groupe_id==0)  // fermeture pour tous !
		$list_users = get_list_all_users($mysql_link, $DEBUG);
	else
		$list_users = get_list_users_du_groupe($groupe_id, $mysql_link, $DEBUG);

	$tab_users = explode(",", $list_users);
	if($DEBUG==TRUE) { echo "tab_users =<br>\n"; print_r($tab_users) ; echo "<br>\n"; }

	/***********************************************/
	/** suppression des jours de fermetures   **/
	// on suprimme les dates de cette fermeture dans conges_jours_fermeture
	$result=delete_year_fermeture($fermeture_id, $mysql_link, $DEBUG);


	// on va traiter user par user pour annuler sa periode de conges correspondant et lui re-crediter son solde
	foreach($tab_users as $current_login)
	{
	    $current_login = trim($current_login);
		// on enleve les quotes qui ont été ajoutées lors de la creation de la liste
		$current_login = trim($current_login, "\'");

		// on recupère les infos de la periode ....
		$sql_credit="SELECT p_num, p_nb_jours, p_type FROM conges_periode WHERE p_login='$current_login' AND p_fermeture_id='$fermeture_id' ";
		$result_credit = requete_mysql($sql_credit, $mysql_link, "commit_annul_fermeture", $DEBUG);
		$row_credit = mysqli_fetch_array($result_credit);
		$sql_num_periode=$row_credit['p_num'];
		$sql_nb_jours_a_crediter=$row_credit['p_nb_jours'];
		$sql_type_abs=$row_credit['p_type'];


		// on ne met à jour la table conges_periode .
		$etat = "annul" ;
	 	$sql = "UPDATE conges_periode SET p_etat = '$etat' WHERE p_num=$sql_num_periode " ;
	    $ReqLog = requete_mysql($sql, $mysql_link, "commit_annul_fermeture", $DEBUG);

		// mise à jour du solde de jours de conges pour l'utilisateur $current_login
		if ($sql_nb_jours_a_crediter != 0)
		{
		        $sql = "UPDATE conges_solde_user SET su_solde = su_solde + $sql_nb_jours_a_crediter WHERE su_login='$current_login' AND su_abs_id = $sql_type_abs " ;
		        if ($DEBUG) echo "<br>$sql<br>$mysql_link<br>";
		        $ReqLog = requete_mysql($sql, $mysql_link, "commit_annul_fermeture", $DEBUG);
		}
	}

	if($result==TRUE)
		echo "<br>".$_SESSION['lang']['form_modif_ok'].".<br><br>\n";
	else
		echo "<br>".$_SESSION['lang']['form_modif_not_ok']." !<br><br>\n";

	// on enregistre cette action dan les logs
	if($groupe_id==0)  // fermeture pour tous !
		$comment_log = "annulation fermeture $fermeture_id (pour tous) " ;
	else
		$comment_log = "annulation fermeture $fermeture_id (pour le groupe $groupe_id)" ;
	log_action(0, "", "", $comment_log, $mysql_link, $DEBUG);

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr><td align=\"center\">\n";
	echo "	<input type=\"submit\" value=\"".$_SESSION['lang']['form_ok']."\">\n";
//	echo "	<input type=\"button\" value=\"".$_SESSION['lang']['form_close_window']."\" onClick=\"javascript:window.close();\">\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";

}


function insert_year_fermeture($fermeture_id, $tab_j_ferme, $groupe_id, $mysql_link, $DEBUG=FALSE)
{
	$sql_insert="";
	foreach($tab_j_ferme as $jf_date )
	{
		$sql_insert="INSERT INTO conges_jours_fermeture (jf_id, jf_gid, jf_date) VALUES ($fermeture_id, $groupe_id, '$jf_date') ;";
		$result_insert = requete_mysql($sql_insert, $mysql_link, "insert_year_fermeture", $DEBUG);
	}
	return TRUE;
}

function delete_year_fermeture($fermeture_id, $mysql_link, $DEBUG=FALSE)
{
	$sql_delete="DELETE FROM conges_jours_fermeture WHERE jf_id = '$fermeture_id' ;";
	$result = requete_mysql($sql_delete, $mysql_link, "delete_year_fermeture", $DEBUG);
	return TRUE;
}


// retourne un tableau des jours fermes de l'année dans un tables passé par référence
function get_tableau_jour_fermeture($year, &$tab_year,  $groupe_id, $mysql_link, $DEBUG=FALSE)
{
	$sql_select = " SELECT jf_date FROM conges_jours_fermeture WHERE DATE_FORMAT(jf_date, '%Y-%m-%d') LIKE '$year%'  ";
	// on recup les fermeture du groupe + les fermetures de tous !
	if($groupe_id==0)
		$sql_select = $sql_select."AND jf_gid = 0";
	else
		$sql_select = $sql_select."AND  (jf_gid = $groupe_id OR jf_gid =0 ) ";
	$res_select = requete_mysql($sql_select, $mysql_link, "get_tableau_jour_fermeture", $DEBUG);
//	$res_select = mysqli_query($sql_select, $mysql_link);
//	attention ne fonctionne pas avec requete_mysql
//	$res_select = requete_mysql($sql_select, $mysql_link, "get_tableau_jour_feries", $DEBUG);

	$num_select = mysqli_num_rows($res_select);

	if($num_select!=0)
	{
	        while($result_select = mysqli_fetch_array($res_select))
		{
		        $tab_year[]=$result_select["jf_date"];
		}
	}
}


// retourne un tableau des periodes de fermeture (pour un groupe donné (gid=0 pour tout le monde))
function get_tableau_periodes_fermeture(&$tab_periodes_fermeture, $groupe_id, $mysql_link, $DEBUG=FALSE)
{
   $req_1="SELECT DISTINCT conges_periode.p_date_deb, conges_periode.p_date_fin, conges_periode.p_fermeture_id FROM conges_periode, conges_jours_fermeture " .
   		" WHERE conges_periode.p_fermeture_id = conges_jours_fermeture.jf_id AND conges_periode.p_etat='ok' AND conges_jours_fermeture.jf_gid = '$groupe_id' " .
  		" ORDER BY conges_periode.p_date_deb DESC ";
   $res_1 = requete_mysql($req_1, $mysql_link, "get_tableau_periodes_fermeture", $DEBUG);

	$num_select = mysqli_num_rows($res_1);

	if($num_select!=0)
	{
	    while($result_select = mysqli_fetch_array($res_1))
		{
			$tab_periode=array();
			$tab_periode['date_deb']=$result_select["p_date_deb"];
			$tab_periode['date_fin']=$result_select["p_date_fin"];
			$tab_periode['fermeture_id']=$result_select["p_fermeture_id"];
			$tab_periodes_fermeture[]=$tab_periode;
		}
	}

}


// recup l'id de la derniere fermeture (le max)
function get_last_fermeture_id($mysql_link, $DEBUG=FALSE)
{
   $req_1="SELECT MAX(jf_id) FROM conges_jours_fermeture ";
   $res_1 = requete_mysql($req_1, $mysql_link, "get_last_fermeture_id", $DEBUG);
   $row_1 = mysqli_fetch_row($res_1);
   if(!$row_1)
      return 0;     // si la table est vide, on renvoit 0
   else
      return $row_1[0];

}


// Affichage d'un SELECT de formulaire pour choix d'un type d'absence
function affiche_select_conges_id($mysql_link, $DEBUG=FALSE)
{
	$tab_conges=recup_tableau_types_conges($mysql_link, $DEBUG);
	$tab_conges_except=recup_tableau_types_conges_exceptionnels($mysql_link, $DEBUG);
	
	echo "<select name=id_type_conges>\n";

	foreach($tab_conges as $id => $libelle)
	{
		if($libelle == 1)
			echo "<option value=\"$id\" selected>$libelle</option>\n";
		else
			echo "<option value=\"$id\">$libelle</option>\n";
	}
	if(count($tab_conges_except)!=0)
	{
		foreach($tab_conges_except as $id => $libelle)
		{
			if($libelle == 1)
				echo "<option value=\"$id\" selected>$libelle</option>\n";
			else
				echo "<option value=\"$id\">$libelle</option>\n";
		}
	}

	echo "</select>\n";
}


?>
