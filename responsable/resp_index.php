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
include("resp_ajout_conges_all.php");
include("resp_traite_demande_all.php");
include("resp_traite_user.php");

$DEBUG = FALSE ;
//$DEBUG = TRUE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_resp", $DEBUG);


	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$onglet = getpost_variable("onglet", "page_principale");
	//var pour resp_traite_demande_all.php
	$tab_bt_radio   = getpost_variable("tab_bt_radio");
	$tab_text_refus = getpost_variable("tab_text_refus");
	//var pour resp_ajout_conges_all.php
	$ajout_conges            = getpost_variable("ajout_conges");
	$tab_champ_saisie        = getpost_variable("tab_champ_saisie");
	$tab_commentaire_saisie        = getpost_variable("tab_commentaire_saisie");
	//$tab_champ_saisie_rtt    = getpost_variable("tab_champ_saisie_rtt") ;
	$ajout_global            = getpost_variable("ajout_global");
	$ajout_groupe            = getpost_variable("ajout_groupe");
	$choix_groupe            = getpost_variable("choix_groupe");
	$tab_new_nb_conges_all   = getpost_variable("tab_new_nb_conges_all");
	$tab_calcul_proportionnel = getpost_variable("tab_calcul_proportionnel");
	$tab_new_comment_all     = getpost_variable("tab_new_comment_all");
	//var pour resp_traite_user.php
	$user_login   = getpost_variable("user_login") ;
	$year_calendrier_saisie_debut = getpost_variable("year_calendrier_saisie_debut", 0) ;
	$mois_calendrier_saisie_debut = getpost_variable("mois_calendrier_saisie_debut", 0) ;
	$year_calendrier_saisie_fin = getpost_variable("year_calendrier_saisie_fin", 0) ;
	$mois_calendrier_saisie_fin = getpost_variable("mois_calendrier_saisie_fin", 0) ;
	$tri_date = getpost_variable("tri_date", "ascendant") ;
	$tab_checkbox_annule = getpost_variable("tab_checkbox_annule") ;
	$tab_radio_traite_demande = getpost_variable("tab_radio_traite_demande") ;
	$tab_text_refus = getpost_variable("tab_text_refus") ;
	$tab_text_annul = getpost_variable("tab_text_annul") ;
	$new_demande_conges = getpost_variable("new_demande_conges", 0) ;
	$new_debut = getpost_variable("new_debut") ;
	$new_demi_jour_deb = getpost_variable("new_demi_jour_deb") ;
	$new_fin = getpost_variable("new_fin") ;
	$new_demi_jour_fin = getpost_variable("new_demi_jour_fin") ;
	$new_nb_jours = getpost_variable("new_nb_jours") ;
	$new_comment = getpost_variable("new_comment") ;
	$new_type = getpost_variable("new_type") ;
	/*************************************/

	//connexion mysql
	$mysql_link = connexion_mysql() ;


echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
echo "<html>\n";
echo "<head>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";	
echo "<link href=\"../".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
/* echo "<link href=\"../jquery-ui-1.10.3.custom_a.css\" rel=\"stylesheet\" type=\"text/css\" >\n"; */ 
echo "<title> ".$_SESSION['config']['titre_application'].$_SESSION['config']['titre_resp_index']." </title>\n";
// _nfj_ include("../fonctions_javascript.php") ;
echo "<script src=\"/jquery/js/jquery-1.10.2.min.js\"></script>\n";
echo "<script src=\"/jquery/js/jquery-ui-1.10.3.custom.min.js\"></script>\n";
echo "<script src=\"/jquery/development-bundle/ui/i18n/jquery.ui.datepicker-fr.js\"></script>\n";
echo "<script src=\"../jqcal.min.js\"></script>\n";
echo "<script>$(function() { jqcal_ready(); } ); </script>\n";

echo "</head>\n";
	
	$bgimage=$_SESSION['config']['URL_ACCUEIL_CONGES']."/".$_SESSION['config']['bgimage'];
	echo "<body text=\"#000000\" bgcolor=".$_SESSION['config']['bgcolor']." link=\"#000080\" vlink=\"#800080\" alink=\"#FF0000\" background=\"$bgimage\">\n";
	echo "<CENTER>\n";


	/*****************************************************************************/
	// AFFICHAGE DES BOUTONS "deconnexion" et "actualiser page" et "affichage calendrier" :

	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"98%\"><tr>\n";
	// bouton deconnexion
	if($_SESSION['config']['auth']==TRUE)
	{
		echo "<td width=\"120\" valign=\"middle\">\n";
		bouton_deconnexion();
		echo "</td>\n";
	}
	// bouton actualiser
	echo "<td width=\"150\" valign=\"middle\">\n";
	if($onglet == "resp_traite_user")
		bouton_actualiser("resp_traite_user&user_login=$user_login", $DEBUG);  // on ajoute le user_login en paramètre à passer dans le lien ...
	else
		bouton_actualiser($onglet, $DEBUG);
	echo "</td>\n";
	
	// cellule centrale vide
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
echo "&nbsp;&nbsp;&nbsp;] " ;
echo "</div>\n"; 
echo "</td>\n";
	
	/*** bouton mode utilisateur ***/ 
	echo "<td width=\"155\" align=\"right\" valign=\"middle\">\n";
	echo "<a href=\"../utilisateur/user_index.php?session=$session\" method=\"POST\" target=\"_blank\">" .
//			"<img src=\"../img/admin-tools-22x22.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['resp_menu_button_mode_user']."\" alt=\"".$_SESSION['lang']['resp_menu_button_mode_user']."\">" .
			"<img src=\"../img/user_4_22x22.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['resp_menu_button_mode_user']."\" alt=\"".$_SESSION['lang']['resp_menu_button_mode_user']."\">" .
			"</a> ".$_SESSION['lang']['resp_menu_button_mode_user']."\n";
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
	
	if($_SESSION['config']['resp_affiche_calendrier']==TRUE)
	{
		// bouton imprim calendrier
		echo "<td width=\"140\" align=\"right\" valign=\"middle\">\n";
			echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../imprim_calendrier.php?session=$session','imprimcal',200,200);\">" .
			//echo "<a href=\"../calendrier.php?session=$session\">" .
			 "<img src=\"../img/fileprint_4_22x22.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['button_imprim_calendar']."\" alt=\"".$_SESSION['lang']['button_imprim_calendar']."\">" .
			 "</a> ".$_SESSION['lang']['button_imprim_calendar']."\n";
		echo "</td>\n";
		
		// bouton calendrier
		if($_SESSION['config']['resp_affiche_calendrier']==TRUE)
		{
			echo "<td width=\"140\" align=\"right\" valign=\"middle\">\n";
				echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('../calendrier.php?session=$session','calendrier',850,600);\">" .
				//echo "<a href=\"../calendrier.php?session=$session\">" .
				 "<img src=\"../img/rebuild.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['button_calendar']."\" alt=\"".$_SESSION['lang']['button_calendar']."\">" .
				 "</a> ".$_SESSION['lang']['button_calendar']."\n";
			echo "</td>\n";
		}
	}
	echo "</tr></table>\n";
	
	// FIN AFFICHAGE DES BOUTONS ...
	/*****************************************************************************/
	


	/***********************************/
	// TITRE
	
	if($_SESSION['config']['responsable_virtuel']==FALSE)
	{
		$sql1 = "SELECT u_nom, u_prenom FROM conges_users where u_login = '".$_SESSION['userlogin']."' ";
		$ReqLog1 = requete_mysql($sql1, $mysql_link, "resp_main", $DEBUG);
		$resultat1 = mysql_fetch_array($ReqLog1); 
		
		echo "<H1>".$_SESSION['lang']['resp_menu_titre']." ".$resultat1["u_prenom"]." ".$resultat1["u_nom"]."</H1>\n\n";
	}
	else
		echo "<H1>".$_SESSION['lang']['divers_responsable_maj_1']."</H1>\n\n";

		
	/************************************/
	// AFFICHAGE DES ONGLETS
	
	echo "</CENTER>\n";
	echo "<table cellpadding=\"1\" cellspacing=\"2\" border=\"1\">\n" ;
	echo "<tr align=\"center\">\n";

		/**********************/
		/* ONGLET RETOUR MAIN */
		/**********************/
		if($onglet!="page_principale")
			echo "<td class=\"onglet\" width=\"200\"><a href=\"$PHP_SELF?session=$session&onglet=page_principale\" class=\"bouton-onglet\"> ".$_SESSION['lang']['resp_menu_button_retour_main']." </a></td>\n";
		else
			echo "<td class=\"current-onglet\" width=\"200\"><a href=\"$PHP_SELF?session=$session&onglet=page_principale\" class=\"bouton-current-onglet\"> ".$_SESSION['lang']['resp_menu_button_retour_main']." </a></td>\n";

		/**************************/
		/* ONGLET TRAITE DEMANDES */
		/**************************/
		if($_SESSION['config']['user_saisie_demande']==TRUE) 
		{
			if($onglet!="traitement_demandes")
				echo "<td class=\"onglet\" width=\"220\"><a href=\"$PHP_SELF?session=$session&onglet=traitement_demandes\" class=\"bouton-onglet\"> ".$_SESSION['lang']['resp_menu_button_traite_demande']." </a></td>\n";
			else
				echo "<td class=\"current-onglet\" width=\"220\"><a href=\"$PHP_SELF?session=$session&onglet=traitement_demandes\" class=\"bouton-current-onglet\"> ".$_SESSION['lang']['resp_menu_button_traite_demande']." </a></td>\n";
		}

		/***********************/
		/* ONGLET AJOUT CONGES */
		/***********************/
/* if($_SESSION['config']['resp_ajoute_conges']==TRUE)  // si le resp peut ajouter des conges */ 
/* en v3 : fonction accessible uniquement aux resp / gestionnaire si 'config''resp_ajoute_conges' est validé */ 
error_log("resp: ". $_SESSION['userlogin']." ".is_gest($_SESSION['userlogin'],$mysql_link,$DEBUG)." ". $_SESSION['config']['resp_ajoute_conges']);
  if ( is_gest($_SESSION['userlogin'],$mysql_link,$DEBUG) && $_SESSION['config']['resp_ajoute_conges'] )  
  // si le resp peut ajouter des conges
		{
			if($onglet!="ajout_conges")
				echo "<td class=\"onglet\" width=\"150\"><a href=\"$PHP_SELF?session=$session&onglet=ajout_conges\" class=\"bouton-onglet\"> ".$_SESSION['lang']['resp_ajout_conges_titre']." </a></td>\n";
			else
				echo "<td class=\"current-onglet\" width=\"150\"><a href=\"$PHP_SELF?session=$session&onglet=ajout_conges\" class=\"bouton-current-onglet\"> ".$_SESSION['lang']['resp_ajout_conges_titre']." </a></td>\n";
		}

		echo "</tr>\n";
		echo "</table>\n" ;
	

	/*************************************/
	/***  suite de la page             ***/
	/*************************************/
	
	echo "<CENTER>\n";
	echo "<table cellpadding='0' cellspacing='0' border='1' width='100%'>\n";
	echo "<tr align='center'>\n";
	echo "<td colspan=5>\n";

	/** initialisation des tableaux des types de conges/absences  **/
	// recup du tableau des types de conges (seulement les conges)
	$tab_type_cong=recup_tableau_types_conges($mysql_link, $DEBUG);

	// recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
//	if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) 
		$tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($mysql_link, $DEBUG);
	

	/*************************************/
	// on commence ici les tests pour savoir quelle page afficher
	if(($onglet=="") || ($onglet=="page_principale"))
	{
		$onglet="page_principale";
		page_principale($session, $tab_type_cong, $tab_type_conges_exceptionnels, $mysql_link, $DEBUG);
	}
	elseif($onglet=="traitement_demandes")
	{
		
		// titre
		echo "<H2>".$_SESSION['lang']['resp_traite_demandes_titre']."</H2>\n\n";
		//connexion mysql
		//$mysql_link = connexion_mysql() ;
		
		// si le tableau des bouton radio des demandes est vide , on affiche les demandes en cours
		if($tab_bt_radio=="")
			affiche_all_demandes_en_cours($tab_type_cong, $mysql_link, $DEBUG);
		else
			traite_all_demande_en_cours($mysql_link, $tab_bt_radio, $tab_text_refus, $DEBUG);
	}
	elseif($onglet=="ajout_conges")
	{
	
		if($DEBUG==TRUE) { echo "tab_new_nb_conges_all = <br>"; print_r($tab_new_nb_conges_all); echo "<br>\n" ;}
		if($DEBUG==TRUE) { echo "tab_calcul_proportionnel = <br>"; print_r($tab_calcul_proportionnel); echo "<br>\n" ;}
		
		
		// titre
		echo "<H2>".$_SESSION['lang']['resp_ajout_conges_titre']."</H2>\n\n";
		//connexion mysql
		//$mysql_link = connexion_mysql() ;
		
		if($ajout_conges=="TRUE")
		{
			ajout_conges($tab_champ_saisie, $tab_commentaire_saisie, $mysql_link, $DEBUG);
		}
		elseif($ajout_global=="TRUE")
		{
			ajout_global($tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $mysql_link, $DEBUG);
		}
		elseif($ajout_groupe=="TRUE")
		{
			ajout_global_groupe($choix_groupe, $tab_new_nb_conges_all, $tab_calcul_proportionnel, $tab_new_comment_all, $mysql_link, $DEBUG);
		}
		else
		{
			saisie_ajout($tab_type_cong,$mysql_link, $DEBUG);
		}
	}
	elseif($onglet=="resp_traite_user")
	{
		
		// si une annulation de conges a été selectionée :
		if($tab_checkbox_annule!="")
		{
          $onglet="resp_traite_user_valid" ; 
          /* _dpa ajout _valid en suffixe (cela desactive traitement de jqcal_ready 
             correspondant à onglet resp_traite_user) cela le temps du feedback d'annulation */ 

          annule_conges($user_login, $tab_checkbox_annule, $tab_text_annul, $mysql_link, $DEBUG);
		}
		// si le traitement des demandes a été selectionée :
		elseif($tab_radio_traite_demande!="")
		{
			traite_demandes($user_login, $tab_radio_traite_demande, $tab_text_refus, $mysql_link, $DEBUG);
		}
		// si un nouveau conges ou absence a été saisi pour un user :
		elseif($new_demande_conges==1)
		{
          error_log("resp_index::new_dem ".$user_login.$new_debut.$new_demi_jour_deb.$new_fin.$new_demi_jour_fin.$new_nb_jours.$new_comment.$new_type); 
          $onglet="resp_traite_user_valid" ;  
         /* _dpa ajout _valid en suffixe (cela desactive traitement de jqcal_ready 
             correspondant à onglet resp_traite_user) cela le temps du feedback de saisie */ 

          new_conges($user_login, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $mysql_link, $DEBUG);
		}
		else 
		{
			affichage($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $tri_date, $mysql_link, $DEBUG);
		}
	}
	
	// fermeture connexion mysql
	mysql_close($mysql_link);
	
	/*************************************/
	/***  fin de la page             ***/
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "<br>\n";
	echo "<hr align=\"center\" size=\"2\" width=\"90%\">\n";
	echo "</CENTER>\n";
echo "<p align=\"left\">\n" ; 

// _jqcal style=\"display: none;\"
echo "<div id=\"ongletname\" class=\"tech\" >".$onglet."</div>\n";  
echo "<div id=\"rootpath\"  class=\"tech\">".$_SESSION['config']['php_conges_rootpath']."</div>\n";  
echo "<div id=\"jqcal\" class=\"tech\" >jqcal</div>\n";  

/* _jqcal : une console pour debugguer le volet clientside */
if (False) {
  echo "<button id=\"dconsoleclear\" type=\"button\">console clear</button>\n";
  echo "<div>dconsole:\n"; 
  echo "<div id=\"dconsole\" style=\"height: 600px; width: 1200px; overflow: auto;\" ></div>\n";
  echo "</div>\n"; 
};
echo "</p>\n" ; 

echo "</body>\n";
echo "</html>\n";

/**************************************************************************************/
/********  FONCTIONS      ******/
/**************************************************************************************/

// affichage liste et résumé des users :
function page_principale($session, $tab_type_cong, $tab_type_conges_exceptionnels, $mysql_link, $DEBUG)
{
	
	/***********************************/
	// AFFICHAGE ETAT CONGES TOUS USERS
	
        /***********************************/
	// AFFICHAGE TABLEAU (premiere ligne)
	echo "<H2>".$_SESSION['lang']['resp_traite_user_etat_conges']."</H2>\n\n";
	
	echo "<TABLE cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n";
	
	echo "<tr align=\"center\">\n";
	echo "<td class=\"titre\">".$_SESSION['lang']['divers_nom_maj']."</td>\n";
	echo "<td class=\"titre\">".$_SESSION['lang']['divers_prenom_maj']."</td>\n";
	echo "<td class=\"titre\">".$_SESSION['lang']['divers_quotite_maj_1']."</td>" ;
	$nb_colonnes = 3;
	foreach($tab_type_cong as $id_conges => $libelle)
	{
		// cas d'une absence ou d'un congé
		echo "<td class=\"titre\"> $libelle"." / ".$_SESSION['lang']['divers_an_maj']."</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_solde_maj']." ".$libelle ."</td>";
		$nb_colonnes += 2;
	}
	// conges exceptionnels
	if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE)
	{
		foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle)
		{
			echo "<td class=\"titre\">".$_SESSION['lang']['divers_solde_maj']." $libelle</td>\n";
			$nb_colonnes += 1;
		}
	}
	echo "<td class=\"titre\"></td>";
    $nb_colonnes += 1;
    if($_SESSION['config']['editions_papier']==TRUE)
    {
		echo "<td class=\"titre\"></td>";
		$nb_colonnes += 1;
	}
	echo "</tr>\n";

	/***********************************/
	// AFFICHAGE USERS
	
	/***********************************/
	// AFFICHAGE DE USERS DIRECTS DU RESP

	// Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est responsable
	$tab_all_users=recup_infos_all_users_du_resp($_SESSION['userlogin'], $mysql_link, $DEBUG);
	if($DEBUG==TRUE) {echo "tab_all_users :<br>\n";  print_r($tab_all_users); echo "<br>\n"; }

	if(count($tab_all_users)==0) // si le tableau est vide (resp sans user !!) on affiche une alerte !
		echo "<tr align=\"center\"><td class=\"histo\" colspan=\"".$nb_colonnes."\">".$_SESSION['lang']['resp_etat_aucun_user']."</td></tr>\n" ;
	else
	{
		foreach($tab_all_users as $current_login => $tab_current_user)
		{		
			//tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
			$tab_conges=$tab_current_user['conges']; 
	
			$text_affich_user="<a href=\"resp_index.php?session=$session&onglet=resp_traite_user&user_login=$current_login\">".$_SESSION['lang']['resp_etat_users_afficher']."</a>" ;
			$text_edit_papier="<a href=\"../edition/edit_user.php?session=$session&user_login=$current_login\" target=\"_blank\">".$_SESSION['lang']['resp_etat_users_imprim']."</a>";
			echo "<tr align=\"center\">\n";
			echo "<td class=\"histo\">".$tab_current_user['nom']."</td><td class=\"histo\">".$tab_current_user['prenom']."</td><td class=\"histo\">".$tab_current_user['quotite']."%</td>";
			foreach($tab_type_cong as $id_conges => $libelle)
			{
				echo "<td class=\"histo\">".$tab_conges[$libelle]['nb_an']."</td>\n";
				echo "<td class=\"histo\">".$tab_conges[$libelle]['solde']."</td>";
			}
			if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) 
			{
				foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle) 
				{
					echo "<td class=\"histo\">".$tab_conges[$libelle]['solde']."</td>\n";
				}
			}
			echo "<td class=\"histo\">$text_affich_user</td>\n";
			if($_SESSION['config']['editions_papier']==TRUE)
				echo "<td class=\"histo\">$text_edit_papier</td>";
			echo "</tr>\n";
		}
	}

	/***********************************/
	// AFFICHAGE DE USERS DONT LE RESP EST GRAND RESP

	if($_SESSION['config']['double_validation_conges']==TRUE) 
	{
		// Récup dans un tableau de tableau des informations de tous les users dont $_SESSION['userlogin'] est GRAND responsable
		$tab_all_users_2=recup_infos_all_users_du_grand_resp($_SESSION['userlogin'], $mysql_link, $DEBUG);
		if($DEBUG==TRUE) {echo "tab_all_users_2 :<br>\n";  print_r($tab_all_users_2); echo "<br>\n"; }
		
		$compteur=0;  // compteur de ligne a afficher en dessous (dés que passe à 1 : on affiche une ligne de titre)

		foreach($tab_all_users_2 as $current_login_2 => $tab_current_user_2)
		{
			if(array_key_exists($current_login_2, $tab_all_users)==FALSE) // si le user n'est pas déjà dans le tableau précédent (deja affiché)
			{
				$compteur++;
				if($compteur==1)  // alors on affiche une ligne de titre
				{
					$nb_colspan=9;
					if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) 
						$nb_colspan=10;
					
					echo "<tr align=\"center\"><td class=\"histo\" colspan=\"$nb_colspan\"><i>".$_SESSION['lang']['resp_etat_users_titre_double_valid']."</i></td></tr>\n";
				}
					
				//tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
				$tab_conges_2=$tab_current_user_2['conges']; 
		
				$text_affich_user="<a href=\"resp_index.php?session=$session&onglet=resp_traite_user&user_login=$current_login_2\">".$_SESSION['lang']['resp_etat_users_afficher']."</a>" ;
				$text_edit_papier="<a href=\"../edition/edit_user.php?session=$session&user_login=$current_login_2\" target=\"_blank\">".$_SESSION['lang']['resp_etat_users_imprim']."</a>";
				echo "<tr align=\"center\">\n";
				echo "<td class=\"histo\">".$tab_current_user_2['nom']."</td><td class=\"histo\">".$tab_current_user_2['prenom']."</td><td class=\"histo\">".$tab_current_user_2['quotite']."%</td>";
				foreach($tab_type_cong as $id_conges => $libelle)
				{
					echo "<td class=\"histo\">".$tab_conges_2[$libelle]['nb_an']."</td><td class=\"histo\">".$tab_conges_2[$libelle]['solde']."</td>";
				}
				if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) 
				{
					foreach($tab_type_conges_exceptionnels as $id_type_cong => $libelle) 
					{
						echo "<td class=\"histo\">".$tab_conges_2[$libelle]['solde']."</td>\n";
					}
				}
				echo "<td class=\"histo\">$text_affich_user</td>\n";
				if($_SESSION['config']['editions_papier']==TRUE)
					echo "<td class=\"histo\">$text_edit_papier</td>";
				echo "</tr>\n";
			}
		}

	}
	
	echo "</TABLE><br><br>\n\n";

}


?>
