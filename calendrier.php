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
if(phpversion() > "5.1.2") { include("controle_ids.php") ;}
$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

include("fonctions_conges.php") ;
include("INCLUDE.PHP/fonction.php");

if(substr($session, 0, 9)!="phpconges")
{
	session_start();
	$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
	if($_SESSION['config']['consult_calendrier_sans_auth']==FALSE)
	{
		//INTERDIT ! on renvoit à la racine du site
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=/\">";
		exit;
	}
}
else
	include("INCLUDE.PHP/session.php");

$DEBUG=FALSE;
//$DEBUG=TRUE ;

if($DEBUG==TRUE) { echo "lang_file=".$_SESSION['config']['lang_file']."<br>\n";  echo "_SESSION =<br>\n"; print_r($_SESSION); echo "<br><br>\n"; }


echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
echo "<html>\n";
echo "<head>\n";
?>
<script language=javascript>
// Fonction permettant d'afficher ou de cacher le tableau correspondant à l'id passé en paramètre
function afficher(id)
{
	el = document.getElementById(id);
	el.style.display = "block";
}

function cacher(id)
{
	el = document.getElementById(id);
	el.style.display = "none";
}
</script>

<?php


	echo "<TITLE> ".$_SESSION['config']['titre_application'].$_SESSION['config']['titre_calendrier']." </TITLE>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
	echo "<link href=\"".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";

	echo "</head>\n";

	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$selected      = getpost_variable("selected") ;
	$printable     = getpost_variable("printable", 0) ;
	$year          = getpost_variable("year", date("Y")) ;
	$mois          = getpost_variable("mois", date("n")) ;
	$first_jour    = getpost_variable("first_jour", 1) ;
//	$first_load    = getpost_variable("first_load", "Y") ;
	$select_groupe = getpost_variable("select_groupe", 0) ;


	/*************************************/

	//connexion mysql
	$mysql_link = connexion_mysql();

	// on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
	if(!isset($_SESSION["tab_j_feries"]))
	{
		init_tab_jours_feries($mysql_link);
		//print_r($_SESSION["tab_j_feries"]);   // verif DEBUG
	}

	// renvoit un tableau de tableau contenant les infos des types de conges et absences
	$tab_type_absence=recup_tableau_tout_types_abs($mysql_link, $DEBUG);


	if($printable!=1)  // si version écran :
		echo "<body text=\"#000000\" link=\"#000080\" vlink=\"#800080\" alink=\"#FF0000\" background=".$_SESSION['config']['bgimage'].">\n";
	else               // si version imprimable :
		echo "<body text=\"#000000\" link=\"#000080\" vlink=\"#800080\" alink=\"#FF0000\" class=\"edit\">\n";

	echo "<CENTER>\n";

//	echo "<hr align=\"center\" size=\"2\" width=\"90%\"> \n";

	$jour_today=date("j");
	$mois_today=date("m");
	$year_today=date("Y");
	$timestamp_today = mktime (0,0,0,$mois_today,$jour_today,$year_today);

	$mois_timestamp = mktime (0,0,0,$mois,1,$year);
	$nom_mois=date_fr("F", $mois_timestamp);

	// AFFICHAGE PAGE
	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
	echo "<tr>\n";
	echo "   <td align=\"center\">\n";
		echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
		echo "<tr>\n";
		echo "   <td align=\"center\"><img src=\"img/shim.gif\" width=\"200\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\"></td>\n";
		echo "   <td align=\"center\">\n";
	//	echo "   <H2>".$_SESSION['lang']['calendrier_titre']."</H2>\n";
		echo "   <H3>".$_SESSION['lang']['calendrier_titre'];
//		if( ($_SESSION['config']['gestion_groupes']==TRUE) && ($select_groupe!="") )
		if( ($_SESSION['config']['gestion_groupes']==TRUE) && ($select_groupe!=0) )
			echo "   <br>(".$_SESSION['lang']['divers_groupe']." : ".get_group_name_from_id($select_groupe, $mysql_link, $DEBUG).")\n";
		echo "   </H3>\n";
		echo "   </td>\n";
		// AFFICHAGE DE LA SELECTION D'UN GROUPE A AFFICHER
		echo "   <td align=\"right\">\n";
			if( ($_SESSION['config']['gestion_groupes']==TRUE) && ($printable!=1) )  // si gestion des groupes active et pas version imprimable
			{
				// affiche le select des groupes du user OU les groupes du resp (si user est resp) OU tous les groupes (si option de config ok)
				affiche_select_groupe($select_groupe, $selected, $printable, $year, $mois, $first_jour, $mysql_link, $DEBUG) ;
			}
			else
				echo "   <img src=\"img/shim.gif\" width=\"200\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\">\n";
		echo "   </td>\n";
		echo "</tr>\n";
		echo "</table>\n";
	echo "   </td>\n";
	echo "</tr>\n";

	mysql_close($mysql_link);

	echo "<tr>\n";
	echo "   <td align=\"center\">\n";
//	echo "   <h3>$nom_mois  $year</h3>\n";
	echo "   <b>$nom_mois  $year</b><br><br>\n";
	echo "   </td>\n";
	echo "</tr>\n";
	/**********************/
	/* Boutons de defilement */
	if($printable!=1)   // si version ecran :
	{
		echo "<tr>\n";
		echo "   <td align=\"center\">\n";
		affichage_boutons_defilement($first_jour, $mois, $year, $select_groupe, $DEBUG) ;
		echo "   </td>\n";
		echo "</tr>\n";
	}


	/***********************************/
	/* AFFICHAGE  TABLEAU (CALENDRIER) */
	echo "<tr>\n";
	echo "   <td align=\"center\">\n";
	affichage_calendrier($year, $mois, $first_jour, $timestamp_today, $printable, $selected, $tab_type_absence, $select_groupe, $DEBUG);
	echo "   </td>\n";
	echo "</tr>\n";


	/**********************/
	/* Boutons de defilement */
	if($printable!=1)   // si version ecran :
	{
		echo "<tr>\n";
		echo "   <td align=\"center\">\n";
		affichage_boutons_defilement($first_jour, $mois, $year, $select_groupe, $DEBUG) ;
		echo "   </td>\n";
		echo "</tr>\n";
	}

	echo "<tr>\n";
	echo "   <td><img src=\"img/shim.gif\" width=\"200\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\"></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "   <td align=\"center\">\n";

		/**********************/
		/* SOUS LE CALENDRIER */
		/**********************/
		echo "<table cellpadding=\"0\" cellspacing=\"5\" border=\"0\" width=\"90%\">\n";
		echo "<tr>\n";
		echo "   <td width=\"25%\" valign=\"top\" align=\"left\">\n";
		if($printable!=1) // si version ecran :
		{
			echo "      <br><a href=\"$PHP_SELF?session=$session&printable=1&year=$year&mois=$mois&first_jour=$first_jour&select_groupe=$select_groupe\" target=\"_blank\" method=\"post\">\n";
			echo "		<img src=\"img/fileprint_4_22x22.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['calendrier_imprimable']."\" alt=\"".$_SESSION['lang']['calendrier_imprimable']."\">\n";
			echo "      ".$_SESSION['lang']['calendrier_imprimable']."\n";
			echo "      </a>\n";
		}
		else  // si version imprimable
			echo "      <img src=\"img/shim.gif\" width=\"25\" height=\"25\" border=\"0\" vspace=\"0\" hspace=\"0\">\n";
		echo "   </td>\n";
		echo "   <td valign=\"top\" align=\"right\" class=\"cal-legende-highlight\">\n";
		echo "      <h4>légende :</h4>\n";
if (True) { 
echo "<em>warning: version provisoire non cohérente avec saisie des congés</em> \n" ; 
}
		echo "   </td>\n";
		echo "   <td width=\"150\" valign=\"top\" align=\"left\">\n";
				affiche_legende_type_absence($tab_type_absence, $DEBUG);
		echo "   </td>\n";
		echo "   <td width=\"30%\" valign=\"top\" align=\"left\">\n";
        affiche_legende();
/* affiche_new_legende(); */ 
		echo "   </td>\n";
		echo "</tr>\n";
		echo "</table>\n";

	echo "   </td>\n";
	echo "</tr>\n";
	echo "</table>\n";


	if($printable!=1)   // si version ecran :
	{
		echo "<center><hr align=\"center\" size=\"2\" width=\"90%\"></center> \n" ;
	}

	/********************/
	/* bouton retour */
	/********************/
	if($printable==1)   // si version imprimable :
	{
	// appel de la fenetre d'impression directe
?>
<script type="text/javascript" language="javascript1.2">
<!--
// Do print the page
if (typeof(window.print) != 'undefined') {
    window.print();
}
//-->
</script>
<?php
	}
	// si on est dans un acces sans authentification
	elseif(($_SESSION['config']['consult_calendrier_sans_auth']==TRUE) && (!isset($_SESSION['userlogin'])) )
	{
	}
	else // sinon (version ecran et session authentifiée
	{
		echo "<form action=\"\" method=\"POST\">\n";
		echo "<center><input type=\"button\" value=\"".$_SESSION['lang']['form_close_window']."\" onClick=\"javascript:window.close();\"></center>\n";
		echo "</form>\n";
//		//tentative de reload de la page pour eviter le bug d'affichage de firefox avec les div en positions relatives ....
//		if($first_load=="Y")
//		{
//			/* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
//			echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?session=$session&year=$year&mois=$mois&first_jour=$first_jour&first_load=N\">";
//		}
	}

	echo "</center>\n";
	echo "</body>\n";
	echo "</html>\n";


/*******************************************************************************/
/**********  FONCTIONS  ********************************************************/


// retourne le timestamp calculé du jour precedent
function jour_precedent($jour, $mois, $year)
{
	return mktime (0,0,0,$mois,$jour -1,$year);
}

// retourne le timestamp calculé du jour suivant
function jour_suivant($jour, $mois, $year)
{
	return mktime (0,0,0,$mois,$jour +1,$year);
}

/******************************/
/* Boutons de defilement */
/******************************/
function affichage_boutons_defilement($first_jour, $mois, $year, $select_groupe, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

		if($mois==12) $next_mois=1;  else $next_mois=$mois+1 ;
		if($mois==1) $prev_mois=12;  else $prev_mois=$mois-1 ;

		if($prev_mois==12) $prev_year=$year-1; else $prev_year=$year;
		if($next_mois==1) $next_year=$year+1; else $next_year=$year;

		$prev_first_jour=date("j", jour_precedent($first_jour, $mois, $year))  ;
			$prev_first_jour_mois=date("n", jour_precedent($first_jour, $mois, $year))  ;
			$prev_first_jour_year=date("Y", jour_precedent($first_jour, $mois, $year))  ;
		$next_first_jour=date("j", jour_suivant($first_jour, $mois, $year)) ;
			$next_first_jour_mois=date("n", jour_suivant($first_jour, $mois, $year)) ;
			$next_first_jour_year=date("Y", jour_suivant($first_jour, $mois, $year)) ;

		echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"90%\" >\n";
		echo "<tr>\n";
		echo "<td align=\"left\">
				<a href=\"$PHP_SELF?session=$session&first_jour=1&mois=$prev_mois&year=$prev_year&select_groupe=$select_groupe\" method=\"POST\"> << ".$_SESSION['lang']['divers_mois_precedent_maj_1']." </a>
			</td>\n";
		echo "<td align=\"left\">
				<a href=\"$PHP_SELF?session=$session&first_jour=$prev_first_jour&mois=$prev_first_jour_mois&year=$prev_first_jour_year&select_groupe=$select_groupe\" method=\"POST\"> << ".$_SESSION['lang']['calendrier_jour_precedent']." </a>
			</td>\n";
		echo "<td align=\"right\">
				<a href=\"$PHP_SELF?session=$session&first_jour=$next_first_jour&mois=$next_first_jour_mois&year=$next_first_jour_year&select_groupe=$select_groupe\" method=\"POST\"> ".$_SESSION['lang']['calendrier_jour_suivant']." >> </a>
			</td>\n";
		echo "<td align=\"right\">
				<a href=\"$PHP_SELF?session=$session&first_jour=1&mois=$next_mois&year=$next_year&select_groupe=$select_groupe\" method=\"POST\"> ".$_SESSION['lang']['divers_mois_suivant_maj_1']." >> </a>
			</td>\n";
		echo "</tr></table>\n";
		echo "<br>\n";

}



// AFFICHAGE  TABLEAU (CALENDRIER)
function affichage_calendrier($year, $mois, $first_jour, $timestamp_today, $printable, $selected, $tab_type_absence, $select_groupe, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

		//connexion mysql
		$mysql_link = connexion_mysql() ;

		// recup du tableau des types de conges (seulement les conges)
		$tab_type_cong=recup_tableau_types_conges($mysql_link, $DEBUG);
		if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE)
		{
			$tab_type_cong_excep=recup_tableau_types_conges_exceptionnels($mysql_link, $DEBUG);
		}

		/*****************************************/
		/** Récupération des users à afficher:  **/

		$tab_all_users=array();
		$tab_all_users=recup_tableau_des_users_a_afficher($select_groupe, $mysql_link, $DEBUG);

		/** FIN de Récupération des users à afficher:  **/
		/************************************************/


		/*************************/
		/**  AFFICHAGE TABLEAU  **/

		if($printable!=1)  // si version ecran :
			echo "<table cellpadding=\"1\" class=\"tablo-cal\" width=\"80%\">\n";
		else               // si version imprimable :
			echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"1\" width=\"80%\">\n";

		/*************************************/
		// affichage premiere ligne (semaines)
		echo "<tr align=\"center\">\n";

		// affichage nom prenom quotité
		$nb_colonnes=3;
		echo "	<td class=\"cal-user\" rowspan=\"2\">".$_SESSION['lang']['divers_nom_maj']."</td>\n\n";
		echo "	<td class=\"cal-user\" rowspan=\"2\">".$_SESSION['lang']['divers_prenom_maj']."</td>\n\n";
	//	echo "	<td class=\"cal-user\" rowspan=\"2\">%</td>";        //modif SA
      if($_SESSION['config']['affiche_soldes_calendrier']==TRUE)
		{
			// affichage des libellé des conges
			foreach($tab_type_cong as $id => $libelle)
			{
					//echo "<td class=\"cal-user\" rowspan=\"2\">".$_SESSION['lang']['divers_solde']." $libelle</td>\n"; modif SA
					$nb_colonnes=$nb_colonnes+1;
			}
			if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE)
			{
			foreach($tab_type_cong_excep as $id => $libelle)
				{
				   //echo "<td class=\"cal-user\" rowspan=\"2\">".$_SESSION['lang']['divers_solde']." $libelle</td>\n"; modif SA
					$nb_colonnes=$nb_colonnes+1;
				}
			}
		}

		// affichage des semaines
		// ... du premier jour voulu à la fin du mois
		for($j=$first_jour; checkdate($mois, $j, $year); $j++)
		{
			$j_timestamp=mktime (0,0,0,$mois, $j, $year);
			$j_num_semaine=date_fr("W", $j_timestamp);
			// attention date_fr("w", $j_timestamp) renvoit 0 pour dimanche !
			if(date_fr("w", $j_timestamp)==0)
				$j_num_jour_semaine=7;
			else
				$j_num_jour_semaine=date_fr("w", $j_timestamp);

			//DEBUG : echo "<td class=\"cal-day\" >$j_num_jour_semaine / $j_num_semaine</td>";
			if($j==$first_jour)
			{
				$colspan=8-$j_num_jour_semaine;
				echo "<td class=\"cal-day-first\" colspan=\"$colspan\" >".$_SESSION['lang']['divers_semaine']." $j_num_semaine</td>\n";
			}
			else
			{
				// on affiche que les lundi
				if($j_num_jour_semaine==1)
					echo "<td class=\"cal-day\" colspan=\"7\" >".$_SESSION['lang']['divers_semaine']." $j_num_semaine</td>\n";
			}

		}

		// ... si le premier jour voulu n'etait pas le premier du mois, on va jusqu'à la meme date du mois suivant.
		if($first_jour!=1)
		{
			for($j=1; $j<$first_jour; $j++)
			{
				if($mois==12)
				{
					$mois_select=1;
					$year_select=$year+1;
				}
				else
				{
					$mois_select=$mois+1 ;
					$year_select=$year;
				}

				$j_timestamp=mktime (0,0,0,$mois_select, $j, $year_select);
				$j_num_jour_semaine=date_fr("w", $j_timestamp);

				$j_num_semaine=date_fr("W", $j_timestamp);
				// attention date_fr("w", $j_timestamp) renvoit 0 pour dimanche !
				if(date_fr("w", $j_timestamp)==0)
					$j_num_jour_semaine=7;
				else
					$j_num_jour_semaine=date_fr("w", $j_timestamp);

				//DEBUG : echo "<td class=\"cal-day\" >$j_num_jour_semaine / $j_num_semaine</td>";
				if($j==$first_jour)
				{
					$colspan=8-$j_num_jour_semaine;
					echo "<td class=\"cal-day-first\" colspan=\"$colspan\" >".$_SESSION['lang']['divers_semaine']." $j_num_semaine</td>\n";
				}
				else
				{
					// on affiche que les lundi
					if($j_num_jour_semaine==1)
						echo "<td class=\"cal-day\" colspan=\"7\" >".$_SESSION['lang']['divers_semaine']." $j_num_semaine</td>\n";
				}
			}
		}

		echo "</tr>\n\n";


		/*************************************/
		// affichage 2ieme ligne (dates)
		echo "<tr align=\"center\">\n";

		// on affiche pas car on a fait de "rowspan" à la ligne supérieure
		// affichage d'une cellule vide sous les titres
		//echo "	<td class=\"cal-user\" colspan=\"$nb_colonnes\">&nbsp;</td>\n";

		// affichage des dates
		// ... du premier jour voulu à la fin du mois
		for($j=$first_jour; checkdate($mois, $j, $year); $j++)
		{
			$j_timestamp=mktime (0,0,0,$mois, $j, $year);
			$j_name=date_fr("D", $j_timestamp);
			$j_date_fr=date_fr("d-m-Y", $j_timestamp);
			$j_num_semaine=date_fr("W", $j_timestamp);
			$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

			// on affiche en gras le jour d'aujourd'hui
			if($j_timestamp==$timestamp_today)
				$text_titre_date="<b>$j_name <br>$j</b>";
			else
				$text_titre_date="$j_name <br>$j";

			// on regarde si c'est la premiere cellule ou non
			if($j==$first_jour)
				$cal_day="cal-day-first";
			else
				$cal_day="cal-day";

			// on affiche le titre -date (la date du jour)
			echo "<td class=\"$cal_day $td_second_class\" title=\"$j_date_fr / ".$_SESSION['lang']['divers_semaine']." $j_num_semaine\">$text_titre_date</td>";
		}

		// ... si le premier jour voulu n'etait pas le premier du mois, on va jusqu'à la meme date du mois suivant.
		if($first_jour!=1)
		{
			for($j=1; $j<$first_jour; $j++)
			{
				if($mois==12)
				{
					$mois_select=1;
					$year_select=$year+1;
				}
				else
				{
					$mois_select=$mois+1 ;
					$year_select=$year;
				}

				$j_timestamp=mktime (0,0,0,$mois_select, $j, $year_select);
				$j_name=date_fr("D", $j_timestamp);
				$j_date_fr=date_fr("d-m-Y", $j_timestamp);
				$j_num_semaine=date_fr("W", $j_timestamp);
				$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

				// on affiche en gras le jour d'aujourd'hui
				if($j_timestamp==$timestamp_today)
					echo "<td class=\"cal-day $td_second_class\" title=\"$j_date_fr / ".$_SESSION['lang']['divers_semaine']." $j_num_semaine\"><b>$j_name $j/$mois_select</b></td>";
				else
					echo "<td class=\"cal-day $td_second_class\" title=\"$j_date_fr / ".$_SESSION['lang']['divers_semaine']." $j_num_semaine\">$j_name $j/$mois_select</td>";
			}
		}
		echo "</tr>\n";


		/**************************************************/
		/**************************************************/
		/* recup des info de chaque jour pour tous les users et stockage dans 1 tableau de tableaux */

		$tab_calendrier=recup_tableau_periodes($mois, $first_jour, $year, $mysql_link, $DEBUG);
		if($DEBUG==TRUE) {	print_r($tab_calendrier); echo "<br>\n"; }



		/**************************************************/
		/* recup des rtt de chaque jour pour tous les users et stockage dans 2 tableaux de tableaux */
		/**************************************************/
		//$tab_rtt_echange  //tableau indexé dont la clé est la date sous forme yyyy-mm-dd
						//il contient pour chaque clé (chaque jour): un tableau indéxé ($tab_jour_rtt_echange) (clé= login)
						// qui contient lui même un tableau ($tab_echange) contenant les infos des echanges de rtt pour ce
						// jour et ce login (valeur du matin + valeur de l'apres midi ('Y' si rtt, 'N' sinon) )
		//$tab_rtt_planifiees=array();  //tableau indexé dont la clé est le login_user
					// il contient pour chaque clé login : un tableau ($tab_user_grille) indexé dont la
					// clé est la date_fin_grille.
					// qui contient lui meme pour chaque clé : un tableau ($tab_user_rtt) qui contient enfin
					// les infos pour le matin et l'après midi ('Y' si rtt, 'N' sinon) sur 2 semaines
					// ( du sem_imp_lu_am au sem_p_ve_pm ) + la date de début et de fin de la grille


		$tab_rtt_echange= recup_tableau_rtt_echange($mois, $first_jour, $year, $mysql_link );
		$tab_rtt_planifiees= recup_tableau_rtt_planifiees($mois, $first_jour, $year, $mysql_link );


		/**************************************************/
		/**************************************************/
		// affichage lignes suivantes (users)

		// pour chaque user :
		foreach($tab_all_users as $sql_login => $tab_current_user)
		{
			$sql_nom=$tab_current_user["nom"];
			$sql_prenom=$tab_current_user["prenom"];
			$sql_quotite=$tab_current_user["quotite"];

			// recup dans un tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
			$tab_cong_user = recup_tableau_conges_for_user($sql_login, $mysql_link, $DEBUG);

			if($printable==1)
				echo "<tr align=\"center\" class=\"cal-ligne-user-edit\">\n";
			elseif($selected==$sql_login)
				echo "<tr align=\"center\" class=\"cal-ligne-user-selected\">\n";
			else
				echo "<tr align=\"center\" class=\"cal-ligne-user\">\n";

			if($printable==1)
				$text_nom="<b>$sql_nom</b>";
			else
				$text_nom="<a href=\"$PHP_SELF?session=$session&selected=$sql_login&year=$year&mois=$mois&first_jour=$first_jour&printable=$printable&select_groupe=$select_groupe\" method=\"GET\">$sql_nom</a>";

			// affichage nom prenom quotité
		  	 echo "<td class=\"cal-user\">$text_nom</td><td class=\"cal-user\">$sql_prenom</td>";  //<td class=\"cal-user\">$sql_quotite%</td>"; modif SA

		if($_SESSION['config']['affiche_soldes_calendrier']==TRUE)
			{
				// affichage des divers soldes
				foreach($tab_cong_user as $id => $tab_conges)
				{
				//	echo "<td class=\"cal-user\">".$tab_conges['solde']."</td>"; //modif SA
				}
			}

			// pour chaque jour : (du premier jour demandé à la fin du mois ...)
			for($j=$first_jour; checkdate($mois, $j, $year); $j++)
			{
				$j_timestamp=mktime (0,0,0,$mois, $j, $year);
				$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

				$mois_select=$mois;
				$year_select=$year ;

				// affichage de la cellule correspondant au jour et au user considéré
				affiche_cellule_jour_user($sql_login, $j_timestamp, $year, $mois_select, $j, $td_second_class, $printable, $tab_calendrier, $tab_rtt_echange, $tab_rtt_planifiees, $tab_type_absence, $mysql_link);

			}
			// si le premier jour demandé n'est pas le 1ier du mois , on va jusqu'à la meme date le mois suivant :
			if($first_jour!=1)
			{
				// pour chaque jour jusqu'a la date voulue : (meme num de jour le mois suivant)
				for($j=1; $j<$first_jour; $j++)
				{
					$j_timestamp=mktime (0,0,0,$mois+1, $j, $year);
					$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

					if($mois==12)
					{
						$mois_select=1;
						$year_select=$year+1 ;
					}
					else
					{
						$mois_select=$mois+1 ;
						$year_select=$year ;
					}

					// affichage de la cellule correspondant au jour et au user considéré
					affiche_cellule_jour_user($sql_login, $j_timestamp, $year, $mois_select, $j, $td_second_class, $printable, $tab_calendrier, $tab_rtt_echange, $tab_rtt_planifiees, $tab_type_absence, $mysql_link);

				}
			}
			echo "\n</tr>\n";
		}

		echo "</table>\n\n";

		mysql_close($mysql_link);
}


// affichage de la cellule correspondant au jour et au user considéré
function affiche_cellule_jour_user($sql_login, $j_timestamp, $year_select, $mois_select, $j, $second_class, $printable, $tab_calendrier, $tab_rtt_echange, $tab_rtt_planifiees, $tab_type_absence, $mysql_link, $DEBUG=FALSE)
{
	$session=session_id();

	// info bulle
	$j_date_fr=date_fr("d/m/Y", $j_timestamp);
	$j_num_semaine=date_fr("W", $j_timestamp);
	$info_bulle=" title=\"$sql_login - $j_date_fr\" ";


	if($second_class=="weekend")
	{
		$class="cal-day_".$second_class ;
		if($printable!=1)  // si version écran :
			echo "<td class=\"$class\" $info_bulle>-</td>";
		else
			echo "<td class=\"$class\">-</td>";
	}
	else
	{
		$date_j=date("Y-m-d", $j_timestamp);

		$class_am="travail_am";
		$class_pm="travail_pm";
		$text_am="-";
		$text_pm="-";

		$val_matin="";
		$val_aprem="";
		// recup des infos ARTT ou Temps Partiel :
		// la fonction suivante change les valeurs de $val_matin $val_aprem ....
		recup_infos_artt_du_jour_from_tab($sql_login, $j_timestamp, $val_matin, $val_aprem, $tab_rtt_echange, $tab_rtt_planifiees, $mysql_link, $DEBUG=FALSE);

		//## AFICHAGE ##
		if($val_matin=="Y")
		{
			$class_am="rtt_am";
	//		$text_am="a";
		}
		if($val_aprem=="Y")
		{
			$class_pm = "rtt_pm";
	//		$text_pm="a";
		}

		$text_bulle_type_abs="";

		if( !(($val_matin=="Y")&&($val_aprem=="Y")) ) //si pas journée complète temps-partiel ou rtt, on regarde les conges)
		{
			// Récupération des conges du user
			if (array_key_exists($date_j, $tab_calendrier))   //verif la clé du jour exite dans $tab_calendrier
			{
				$tab_day=$tab_calendrier["$date_j"];  // on recup le tableau ($tab_jour) de la date que l'on affiche
				//print_r($tab_day);

				$nb_resultat_periode = count($tab_day);  //
				if($nb_resultat_periode>0)      // si on est dans une periode de conges
				{
					for ($i = 0; $i < $nb_resultat_periode; $i++)
					{
						// on regarde chaque periode l'une après l'autre
						$tab_per=$tab_day[$i];  // on recup le tableau de la periode
						if(in_array($sql_login, $tab_per))   // si la periode correspond au user que l'on est en train de traiter
						{
							//echo "tab_per =<br>\n"; print_r($tab_per); echo "<br>\n";

							$sql_p_type=$tab_per["p_type"];
							$sql_p_etat=$tab_per["p_etat"];
							$sql_p_date_deb=$tab_per["p_date_deb"];
							$sql_p_date_fin=$tab_per["p_date_fin"];
							$sql_p_demi_jour_deb=$tab_per["p_demi_jour_deb"];
							$sql_p_demi_jour_fin=$tab_per["p_demi_jour_fin"];
							$sql_p_fermeture_id=$tab_per["p_fermeture_id"];

							$sql_p_date_deb_fr=substr($sql_p_date_deb,8,2)."/".substr($sql_p_date_deb,5,2)."/".substr($sql_p_date_deb,0,4);
							$sql_p_date_fin_fr=substr($sql_p_date_fin,8,2)."/".substr($sql_p_date_fin,5,2)."/".substr($sql_p_date_fin,0,4);

							//si on est le premier jour ET le dernier jour de conges
							if( ($sql_p_date_deb==$date_j) && ($sql_p_date_fin==$date_j) )
							{
								if($sql_p_demi_jour_deb=="am")
								{
									$class_am=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
									$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
									        $text_bulle_type_abs=$tab_type_absence[$sql_p_type]['libelle']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
									else
									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
								}
								if($sql_p_demi_jour_fin=="pm")
								{
									$class_pm=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
									$text_pm=$tab_type_absence[$sql_p_type]['short_libelle'];
									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
									        $text_bulle_type_abs=$tab_type_absence[$sql_p_type]['libelle']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
									else
									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
								}
							}
							elseif($sql_p_date_deb==$date_j) //si on est le premier jour
							{
								if($sql_p_demi_jour_deb=="am")
								{
									$class_am=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
									$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
									$class_pm=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
									$text_pm=$tab_type_absence[$sql_p_type]['short_libelle'];
									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
									        $text_bulle_type_abs=$tab_type_absence[$sql_p_type]['libelle']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
									else
									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
								}
								else
								{
									$class_pm=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
									$text_pm=$tab_type_absence[$sql_p_type]['short_libelle'];
									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
									        $text_bulle_type_abs=$tab_type_absence[$sql_p_type]['libelle']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
									else
									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
								}
							}
							elseif($sql_p_date_fin==$date_j) //si on est le dernier jour
							{
								if($sql_p_demi_jour_fin=="pm")
								{
									$class_am=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
									$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
									$class_pm=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
									        $text_bulle_type_abs=$tab_type_absence[$sql_p_type]['libelle']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
									else
									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
									$text_bulle_type_abs=$tab_type_absence[$sql_p_type]['libelle']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
								}
								else
								{
									$class_am=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
									$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
									        $text_bulle_type_abs=$tab_type_absence[$sql_p_type]['libelle']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
									else
									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
								}
							}
							else // si on est ni le premier ni le dernier jour
							{
								$class_am=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_am";
								$text_am=$tab_type_absence[$sql_p_type]['short_libelle'];
								$class_pm=get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id)."_pm";
								$text_pm=$tab_type_absence[$sql_p_type]['short_libelle'];
									if ($tab_per['p_commentaire'] == "")   // *** si le commentaire est renseigné on l'affiche dans l'infobulle, sinon on affiche le type d'absence ***
									        $text_bulle_type_abs=$tab_type_absence[$sql_p_type]['libelle']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
									else
									        $text_bulle_type_abs=$tab_per['p_commentaire']."<br>$sql_p_date_deb_fr - $sql_p_date_fin_fr";
							}
						}
					}
				}
			}
		}

		if(($text_am=="a")&&($text_pm=="a"))
		{
			$text_am="abs";
			$text_pm="";
		}

		// on affiche qu'un seule fois le texte si c'est le même le matin et l'aprem :
		if($text_am==$text_pm)
			$text_pm="";
		elseif(($text_am=="-") &&($text_pm!="") ) //on a un "-" le matin et qq chose l'aprem :on affiche que le texte de l'aprem
			$text_am="";
		elseif(($text_am!="") && ($text_pm=="-"))  //on a un qq chose le matin et un "-" l'aprem :on affiche que le texte du matin
			$text_pm="";


		$class="cal-day_".$second_class."_".$class_am."_".$class_pm ;

		if($printable!=1)  // si version écran :
		{
			if( ($text_am=="-") && ($text_pm=="") )
			{
				echo "<td class=\"$class\"  $info_bulle>";
				echo "	$text_am $text_pm ";
			}
			else
			{
				//echo "<td class=\"$class\"  onmousedown=\"javascript:afficher('$sql_login-$j_timestamp');\" onmouseup=\"javascript:cacher('$sql_login-$j_timestamp');\">";
				echo "<td class=\"$class\"  onmouseover=\"javascript:afficher('$sql_login-$j_timestamp');\" onmouseout=\"javascript:cacher('$sql_login-$j_timestamp');\">";
				echo "	$text_am $text_pm ";

				// affiche l'info-bulle (affichée grace au javascript)
				//$texte_info_bulle=" $j_date_fr / ".$_SESSION['lang']['divers_semaine']." $j_num_semaine <br>$text_bulle_type_abs<br>periode";
				$texte_info_bulle=" $j_date_fr <br>$text_bulle_type_abs";
				echo "	<div class=\"cal-bulles\" align=\"center\" id='$sql_login-$j_timestamp' name='$sql_login-$j_timestamp' >
						$sql_login<br>
						<hr align=\"center\" size=\"1\" width=\"100\" color=\"#6699CC\" />

						$texte_info_bulle
						<hr align=\"center\" size=\"1\" width=\"100\" color=\"#6699CC\" />
						</div> ";
			}
		}
		else
		{
			echo "<td class=\"$class\" >";
			echo "	$text_am $text_pm ";
		}
		echo "</td>";
	}

}



// affichage de la légende des couleurs
function affiche_legende($DEBUG=FALSE)
{
	$session=session_id();

//	echo "      <table cellpadding=\"1\" cellspacing=\"1\" border=\"1\">\n" ;
	echo "      <table cellpadding=\"1\" class=\"tablo-cal\">\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['semaine_bgcolor']."\" class=\"cal-legende\"> - </td>\n" ;
	echo "         <td class=\"cal-legende\"> </td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['week_end_bgcolor']."\" class=\"cal-legende\"> - </td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_we']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['conges_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_conges']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['demande_conges_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_demande']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
//	echo "         <td bgcolor=\"".$_SESSION['config']['temps_partiel_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['temps_partiel_bgcolor']."\" class=\"cal-legende\"> - </td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_part_time']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['absence_autre_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_abs']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['fermeture_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['divers_fermeture']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      </table>\n" ;
}

function affiche_new_legende($DEBUG=FALSE)
{
	echo "      <table cellpadding=\"1\" class=\"tablo-cal-legende\">\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['semaine_bgcolor']."\" class=\"cal-legende\"> - </td>\n" ;
	echo "         <td class=\"cal-legende\"> </td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['week_end_bgcolor']."\" class=\"css-day-pub-holidays\"> - </td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_we']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['conges_bgcolor']."\" class=\"css-day-off\">abs</td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_conges']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['demande_conges_bgcolor']."\" class=\"css-day-off-not-validated\">abs</td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_demande']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
//	echo "         <td bgcolor=\"".$_SESSION['config']['temps_partiel_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['temps_partiel_bgcolor']."\" class=\"css-day-jlp\"> - </td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_part_time']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['absence_autre_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['calendrier_legende_abs']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      <tr align=\"center\">\n" ;
	echo "         <td bgcolor=\"".$_SESSION['config']['fermeture_bgcolor']."\" class=\"cal-legende\">abs</td>\n" ;
	echo "         <td class=\"cal-legende\"> ".$_SESSION['lang']['divers_fermeture']."</td>\n" ;
	echo "      </tr>\n" ;
	echo "      </table>\n" ;
}


// affichage de la légende explicative des abréviations
function affiche_legende_type_absence($tab_type_absence, $DEBUG=FALSE)
{
	$session=session_id();

//	echo "      <table cellpadding=\"1\" cellspacing=\"1\" border=\"1\">\n" ;
	echo "      <table cellpadding=\"1\" class=\"tablo-cal\">\n" ;
	foreach($tab_type_absence as $id_abs => $tab)
	{
		echo "      <tr align=\"center\">\n" ;
		echo "         <td class=\"cal-legende\"> ".$tab['short_libelle']." : </td>\n" ;
		echo "         <td class=\"cal-legende\"> ".$tab['libelle']." </td>\n" ;
		echo "      </tr>\n" ;
	}
	echo "      </table>\n" ;
}


// renvoit conges , demande ou autre ....
function get_class_titre($sql_p_type, $tab_type_absence, $sql_p_etat, $sql_p_fermeture_id, $DEBUG=FALSE)
{
	if($sql_p_fermeture_id!="")
		return "fermeture";
	elseif ($tab_type_absence[$sql_p_type]['type']=="absences")
		return "autre";
	elseif($sql_p_etat=='ok')
		return "conges";
	elseif( ($sql_p_etat=="demande") || ($sql_p_etat=="valid") )
		return "demande";
}





/**************************************************/
/* recup des info de chaque jour pour tous les users et stockage dans 1 tableau de tableaux */
/**************************************************/
function recup_tableau_periodes($mois, $first_jour, $year, $mysql_link, $DEBUG=FALSE)
{
	$tab_calendrier=array();  //tableau indexé dont la clé est la date sous forme yyyy-mm-dd
						//il contient pour chaque clé : un tableau ($tab_jour) qui contient lui même des
						// tableaux indexés contenant les infos des periode de conges dont ce jour fait partie
						// ($tab_periode)

	// pour chaque jour : (du premier jour demandé à la fin du mois ...)
	for($j=$first_jour; checkdate($mois, $j, $year); $j++)
	{
		$j_timestamp=mktime (0,0,0,$mois, $j, $year);

		$date_j=date("Y-m-d", $j_timestamp);
		$tab_jour=array();

		//$user_periode_sql = "SELECT  p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_type, p_etat FROM conges_periode WHERE ( p_etat='ok' OR  p_etat='demande' OR  p_type='formation' OR  p_type='mission' OR  p_type='autre' ) AND (p_date_deb<='$date_j' AND p_date_fin>='$date_j') ORDER BY p_date_deb ";
		$user_periode_sql = "SELECT  p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_type, p_etat, p_fermeture_id, p_commentaire
						FROM conges_periode
						WHERE ( p_etat='ok' OR  p_etat='demande' OR  p_etat='valid') AND (p_date_deb<='$date_j' AND p_date_fin>='$date_j')
						ORDER BY p_date_deb ";

		//echo "user_periode_sql = $user_periode_sql<br>\n";
		$user_periode_request = requete_mysql($user_periode_sql, $mysql_link, "recup_tableau_periodes", $DEBUG);

		$nb_resultat_periode = mysql_num_rows($user_periode_request);
		while($resultat_periode=mysql_fetch_array($user_periode_request))
		{
			$tab_periode=array();
			$tab_periode["p_login"]=$resultat_periode["p_login"];
			$tab_periode["p_type"]=$resultat_periode["p_type"];
			$tab_periode["p_etat"]=$resultat_periode["p_etat"];
			$tab_periode["p_date_deb"]=$resultat_periode["p_date_deb"];
			$tab_periode["p_date_fin"]=$resultat_periode["p_date_fin"];
			$tab_periode["p_demi_jour_deb"]=$resultat_periode["p_demi_jour_deb"];
			$tab_periode["p_demi_jour_fin"]=$resultat_periode["p_demi_jour_fin"];
			$tab_periode["p_fermeture_id"]=$resultat_periode["p_fermeture_id"];
			$tab_periode["p_commentaire"]=$resultat_periode["p_commentaire"];

			// on ne stoque les "demandes" que pour le user qui consulte (il ne voit pas celles des autres !)(suivant l'option de config)
//			if($resultat_periode["p_etat"]!="demande")
			if(($resultat_periode["p_etat"]!="demande") || ($_SESSION['config']['affiche_demandes_dans_calendrier']==TRUE) )
				$tab_jour[]=$tab_periode;
			elseif( (isset($_SESSION['userlogin'])) && ($resultat_periode["p_login"]==$_SESSION['userlogin']) )
				$tab_jour[]=$tab_periode;
		}
		$tab_calendrier[$date_j]=$tab_jour;
	}
	// si le premier jour demandé n'est pas le 1ier du mois , on va jusqu'à la meme date le mois suivant :
	if($first_jour!=1)
	{
		// pour chaque jour jusqu'a la date voulue : (meme num de jour le mois suivant)
		for($j=1; $j<$first_jour; $j++)
		{
			$j_timestamp=mktime (0,0,0,$mois+1, $j, $year);

			$date_j=date("Y-m-d", $j_timestamp);
			$tab_jour=array();

			$user_periode_sql = "SELECT  p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_type, p_etat,  p_fermeture_id
							FROM conges_periode
							WHERE ( p_etat='ok' OR  p_etat='demande' OR  p_etat='valid') AND (p_date_deb<='$date_j' AND p_date_fin>='$date_j') ";
			//echo "user_periode_sql = $user_periode_sql<br>\n";
			$user_periode_request = requete_mysql($user_periode_sql, $mysql_link, "recup_tableau_periodes", $DEBUG);

			$nb_resultat_periode = mysql_num_rows($user_periode_request);
			while($resultat_periode=mysql_fetch_array($user_periode_request))
			{
				$tab_periode=array();
				$tab_periode["p_login"]=$resultat_periode["p_login"];
				$tab_periode["p_type"]=$resultat_periode["p_type"];
				$tab_periode["p_etat"]=$resultat_periode["p_etat"];
				$tab_periode["p_date_deb"]=$resultat_periode["p_date_deb"];
				$tab_periode["p_date_fin"]=$resultat_periode["p_date_fin"];
				$tab_periode["p_demi_jour_deb"]=$resultat_periode["p_demi_jour_deb"];
				$tab_periode["p_demi_jour_fin"]=$resultat_periode["p_demi_jour_fin"];
				$tab_periode["p_fermeture_id"]=$resultat_periode["p_fermeture_id"];

			// on ne stoque les "demandes" que pour le user qui consulte (il ne voit pas celles des autres !)(suivant l'option de config)
//			if($resultat_periode["p_etat"]!="demande")
			if(($resultat_periode["p_etat"]!="demande") || ($_SESSION['config']['affiche_demandes_dans_calendrier']==TRUE) )
				$tab_jour[]=$tab_periode;
			elseif( (isset($_SESSION['userlogin'])) && ($resultat_periode["p_login"]==$_SESSION['userlogin']) )
				$tab_jour[]=$tab_periode;

			}
			$tab_calendrier[$date_j]=$tab_jour;
		}
	}

	return $tab_calendrier;
}

// Affichage d'un SELECT de formulaire pour choix d'un groupe
// affiche les groupes du user OU les groupes du resp (si user est resp) OU tous ls groupes (si option de config ok)
function affiche_select_groupe($select_groupe, $selected, $printable, $year, $mois, $first_jour, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// quelle liste de groupes recuperer ?
	if( ($_SESSION['config']['consult_calendrier_sans_auth']==TRUE) && (!isset($_SESSION['userlogin'])) || /*
        is_admin($_SESSION['userlogin'], $mysql_link, $DEBUG) || */
        $_SESSION['config']['calendrier_select_all_groups'] ) {
      	$list_groupes=get_list_all_groupes($mysql_link, $DEBUG);
    		}	
	elseif(is_resp($_SESSION['userlogin'], $mysql_link, $DEBUG)==TRUE)
	{
		// on propose la liste des groupes dont user est resp + groupes dont user est membre
		$list_groupes_1=get_list_groupes_du_resp($_SESSION['userlogin'], $mysql_link, $DEBUG);
		$list_groupes_2=get_list_groupes_du_user($_SESSION['userlogin'], $mysql_link, "", $DEBUG);
		$list_groupes = $list_groupes_1.",".$list_groupes_2 ;

		//correction de JBR: bug pour l' affichage du calendrier des grands responsables
		  if ($list_groupes_1=='')
		         $list_groupes =$list_groupes_2;
		  elseif ($list_groupes_2=='')
		         $list_groupes =$list_groups_1;
		  else
		         $list_groupes = $list_groupes_1.",".$list_groupes_2;
	}
	else {
      $list_groupes=get_list_groupes_du_user($_SESSION['userlogin'], $mysql_link, "", $DEBUG);
    } ; 
	echo "<form action=\"$PHP_SELF?session=$session&printable=$printable&selected=$selected&year=$year&mois=$mois&first_jour=$first_jour\" method=\"POST\">\n";
	$tab_groupes=array_unique(explode(",", $list_groupes));
	echo $_SESSION['lang']['calendrier_afficher_groupe']." : ";
	echo "<select name=select_groupe>\n";
    $lgroup_s = array();
    // ['groupename'] = groupeid 
	foreach($tab_groupes as $grp)
      {
        $lgroup_s[get_group_name_from_id($grp, $mysql_link, $DEBUG)] = $grp;
      }
    ksort($lgroup_s); 
	foreach($lgroup_s as $namegrp => $idgrp)
	{
      // $grp=trim($grp);
      if($idgrp == $select_groupe)
        echo "<option value=\"$idgrp\" selected>".$namegrp."</option>\n";
      else
        echo "<option value=\"$idgrp\">".$namegrp."</option>\n";
	}
	//option pour retour a l'affichage normal ...
	echo "<option value=\"0\">".$_SESSION['lang']['divers_normal_maj_1']."</option>\n";

	echo "</select>\n";
	echo "<input type=\"submit\" value=\"ok\">\n";
	echo "</form>\n";
}



// Récupération des users à afficher:
// renvoit un tableau de tableau
function recup_tableau_des_users_a_afficher($select_groupe, $mysql_link, $DEBUG=FALSE)
{
		// si acces sans authentification est permis : alors droit de voir tout le monde
		// sinon, on verifie si le user a le droite de voir tout le monde
		if( ($_SESSION['config']['consult_calendrier_sans_auth']==TRUE) && (!isset($_SESSION['userlogin'])) )
		{
			//si gestion des groupes et un groupe a ete selectionne
			if( ($_SESSION['config']['gestion_groupes']==TRUE) && ($select_groupe!=0) )
			{
				$sql = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
				$sql = $sql." WHERE u_login!='conges' AND u_login!='admin' ";

				//recup de la liste des users des groupes dont le user est membre
				$list_users=get_list_users_du_groupe($select_groupe, $mysql_link, $DEBUG);
				if($list_users!="")  //si la liste n'est pas vide ( serait le cas si groupe vide)
					$sql = $sql." AND u_login IN ($list_users) ORDER BY u_nom, u_prenom ";
			}
			else // affiche tous les users
			{
				$sql = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
				//$sql = $sql." WHERE u_login!='conges' AND u_resp_login = 'conges' ORDER BY u_nom, u_prenom";
				$sql = $sql." WHERE u_login!='conges'  AND u_login!='admin' ORDER BY u_nom, u_prenom";
			}
		}
		//sinon (authentification, le user est identifié)
		else
		{
			//construction de la requete sql pour recupérer les users à afficher :

			//si le user a le droit de voir tout le monde
			$user_see_all_in_calendrier=get_user_see_all($_SESSION['userlogin'], $mysql_link);
			if($user_see_all_in_calendrier==TRUE) // si le user a "u_see_all" à "Y" dans la table users : affiche tous les users
			{
				//si gestion des groupes et un groupe a ete selectionne
				if( ($_SESSION['config']['gestion_groupes']==TRUE) && ($select_groupe!=0) )
				{
					$sql = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
					$sql = $sql." WHERE u_login!='conges' AND u_login!='admin' ";
					$sql = $sql." AND ( u_login = '".$_SESSION['userlogin']."' ";

					//recup de la liste des users des groupes dont le user est membre
					$list_users=get_list_users_du_groupe($select_groupe, $mysql_link, $DEBUG);
					if($list_users!="")  //si la liste n'est pas vide ( serait le cas si groupe vide)
						$sql = $sql." OR u_login IN ($list_users) ";
						$sql = $sql." ) ";

					$sql = $sql." ORDER BY u_nom, u_prenom";
				}
				else
				{
					$sql = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
					//$sql = $sql." WHERE u_login!='conges' AND u_resp_login = 'conges' ORDER BY u_nom, u_prenom";
					$sql = $sql." WHERE u_login!='conges'  AND u_login!='admin' ORDER BY u_nom, u_prenom";
				}
			}
			// sinon (le user n'a pas le droit de voir tout le monde)
			else
			{
				//si gestion des groupes et un groupe a ete selectionne
				if( ($_SESSION['config']['gestion_groupes']==TRUE) && ($select_groupe!=0) )
				{
					$sql = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
					$sql = $sql." WHERE u_login!='conges' AND u_login!='admin' ";
					$sql = $sql." AND ( u_login = '".$_SESSION['userlogin']."' ";

					//recup de la liste des users des groupes dont le user est membre
					$list_users=get_list_users_du_groupe($select_groupe, $mysql_link, $DEBUG);
					if($list_users!="")  //si la liste n'est pas vide ( serait le cas si groupe vide)
						$sql = $sql." OR u_login IN ($list_users) ";
						$sql = $sql." ) ";

					$sql = $sql." ORDER BY u_nom, u_prenom";
				}
				// si user n'est pas un responsable
				else
				{
					if(is_resp($_SESSION['userlogin'], $mysql_link)!=TRUE)
					{
						$sql = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
						$sql = $sql." WHERE u_login!='conges' AND u_login!='admin' ";

						//si affichage par groupe : on affiche les membres des groupes du user ($_SESSION['userlogin'])
						if( ($_SESSION['config']['gestion_groupes']==TRUE) && ($_SESSION['config']['affiche_groupe_in_calendrier']==TRUE) )
						{
							$sql = $sql." AND ( u_login = '".$_SESSION['userlogin']."' ";
							//recup de la liste des users des groupes dont le user est membre
							$list_users=get_list_users_des_groupes_du_user($_SESSION['userlogin'], $mysql_link);
							if($list_users!="")  //si la liste n'est pas vide ( serait le cas si n'est membre d'aucun groupe)
								$sql = $sql." OR u_login IN ($list_users) ";
							$sql = $sql." ) ";
						}

						$sql = $sql." ORDER BY u_nom, u_prenom";
					}
					// si user est un responsable
					else
					{
						$sql = "SELECT DISTINCT u_login, u_nom, u_prenom, u_quotite FROM conges_users ";
						$sql = $sql." WHERE u_login!='conges' AND u_login!='admin' ";

						if($_SESSION['userlogin']!="conges")
						{
							$sql = $sql." AND ( u_login = '".$_SESSION['userlogin']."' ";

							//si affichage par groupe : on affiche les membres des groupes du user ($_SESSION['userlogin'])
							if( ($_SESSION['config']['gestion_groupes']==TRUE) && ($_SESSION['config']['affiche_groupe_in_calendrier']==TRUE) )
							{
								//recup de la liste des users des groupes dont le user est membre
								$list_users=get_list_users_des_groupes_du_user($_SESSION['userlogin'], $mysql_link);
								if($list_users!="")  //si la liste n'est pas vide ( serait le cas si n'est membre d'aucun groupe)
									$sql = $sql." OR u_login IN ($list_users) ";

							}

							//recup de la liste des users dont le user est responsable
							$list_users_2=get_list_all_users_du_resp($_SESSION['userlogin'], $mysql_link, $DEBUG);
							if($list_users_2!="")  //si la liste n'est pas vide ( serait le cas si n'est responsable d'aucun groupe)
								$sql = $sql." OR u_login IN ($list_users_2) ";

							$sql = $sql." ) ";
						}

						$sql = $sql." ORDER BY u_nom, u_prenom";

					}
				}
			}
		}

		$ReqLog = requete_mysql($sql, $mysql_link, "affichage_calendrier", $DEBUG);
		$tab_all_users=array();
		while ($resultat = mysql_fetch_array($ReqLog))
		{
			$tab_user=array();
			$tab_user['nom']=$resultat["u_nom"];
			$tab_user['prenom']=$resultat["u_prenom"];
			$tab_user['quotite']=$resultat["u_quotite"];
			$sql_login=$resultat["u_login"];
			$tab_all_users[$sql_login]= $tab_user;
		}

	return($tab_all_users);
}

?>

