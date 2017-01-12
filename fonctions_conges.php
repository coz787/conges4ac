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

//#################################################################################################

//appel de PHP-IDS que si version de php > 5.1.2
if(phpversion() > "5.1.2") { include("controle_ids.php") ;}

$g_users= array(); // pour
$o_users= array(); // liste des users pour lesquels la fonction glau a deja ete appelle
$glau_cpt = 0;

// constantes partagees 

$aday = new DateInterval('P1D');
$a30day = new DateInterval('P30D');
$afourteen = 14 ; 
$aweek     = 7  ;
// tartt : association 
$tartt = array('d'=>'z-da-jlp', 'a' =>'z-am-jl-pm-','p'=>'z-am--pm-jl'); 
$sendoftime = "9999-12-31" ;
$dendoftime = new DateTime($sendoftime) ; 
$tjlppartday = array('z-am-jl--','z-am--pm-jl') ; 

/* version ac3_01 : les routines servant affichage des outils de saisie
   calendrier deviennent inutile ; mise de cote par prefix "atic_" */
 
// affichage du calendrier avec les case à cocher, du mois du début du congés
function  atic_affiche_calendrier_saisie_date_debut($user_login, $year, $mois, $mysql_link, $DEBUG=FALSE)
{

	$jour_today=date("j");
	$jour_today_name=date("D");

	$first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
//	$mois_name=date("F", $first_jour_mois_timestamp);
	$mois_name=date_fr("F", $first_jour_mois_timestamp);
	//$first_jour_mois_name=date("D", $first_jour_mois_timestamp);
	$first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	if($first_jour_mois_rang==0)
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"250\" bgcolor=\"".$_SESSION['config']['semaine_bgcolor']."\">\n";
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
	// affichage des cellules cochables du 1 du mois à la fin de la ligne ...
	for($i=$first_jour_mois_rang; $i<8; $i++)
	{
		$j=$i-$first_jour_mois_rang+1 ;
		$j_timestamp=mktime (0,0,0,$mois,$j,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_debut", $mysql_link, $DEBUG);
	}
	echo "</tr>\n";

	/* affichage ligne 2 du mois*/
	echo "<tr>\n";
	for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++) {
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_debut", $mysql_link, $DEBUG);
	}
	echo "</tr>\n";

	/* affichage ligne 3 du mois*/
	echo "<tr>\n";
	for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_debut", $mysql_link, $DEBUG);
	}
	echo "</tr>\n";

	/* affichage ligne 4 du mois*/
	echo "<tr>\n";
	for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++) {
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_debut", $mysql_link, $DEBUG);
	}
	echo "</tr>\n";

	/* affichage ligne 5 du mois (peut etre la derniere ligne) */
	echo "<tr>\n";
	for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_debut", $mysql_link, $DEBUG);
	}
	for($i; $i<36-$first_jour_mois_rang+1; $i++) {
		if( (($i==35-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==36-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
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
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_debut", $mysql_link, $DEBUG);
	}
	for($i; $i<43-$first_jour_mois_rang+1; $i++)
	{
		if( (($i==42-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==43-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)))
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie2\">-</td>";
	}
	echo "</tr>\n";

	echo "</table>\n";
}


// affichage du calendrier avec les case à cocher, du mois de fin du congés
function  atic_affiche_calendrier_saisie_date_fin($user_login, $year, $mois, $mysql_link, $DEBUG=FALSE)
{


	$jour_today=date("j");
	$jour_today_name=date("D");

	$first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
//	$mois_name=date("F", $first_jour_mois_timestamp);
	$mois_name=date_fr("F", $first_jour_mois_timestamp);
	//$first_jour_mois_name=date("D", $first_jour_mois_timestamp);
	$first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	if($first_jour_mois_rang==0)
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"250\" bgcolor=\"".$_SESSION['config']['semaine_bgcolor']."\">\n";
	/* affichage  2 premieres lignes */
	echo "	<tr align=\"center\" bgcolor=\"".$_SESSION['config']['light_grey_bgcolor']."\"><td colspan=7 class=\"titre\"> $mois_name $year </td></tr>\n" ;
	echo "	<tr align=\"center\"  bgcolor=\"".$_SESSION['config']['light_grey_bgcolor']."\">\n" ;
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['lundi_1c']."</td>\n" ;
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['mardi_1c']."</td>\n" ;
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['mercredi_1c']."</td>\n" ;
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['jeudi_1c']."</td>\n" ;
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['vendredi_1c']."</td>\n" ;
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['samedi_1c']."</td>\n" ;
	echo "		<td class=\"cal-saisie2\">".$_SESSION['lang']['dimanche_1c']."</td>\n" ;
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
	// affichage des cellules cochables du 1 du mois à la fin de la ligne ...
	for($i=$first_jour_mois_rang; $i<8; $i++) {
		$j=$i-$first_jour_mois_rang+1 ;
		$j_timestamp=mktime (0,0,0,$mois,$j,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_fin", $mysql_link, $DEBUG);
	}
	echo "</tr>\n";

	/* affichage ligne 2 du mois*/
	echo "<tr>\n";
	for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_fin", $mysql_link, $DEBUG);
	}
	echo "</tr>\n";

	/* affichage ligne 3 du mois*/
	echo "<tr>\n";
	for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_fin", $mysql_link, $DEBUG);
	}
	echo "</tr>\n";

	/* affichage ligne 4 du mois*/
	echo "<tr>\n";
	for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++) {
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_fin", $mysql_link, $DEBUG);
	}
	echo "</tr>\n";

	/* affichage ligne 5 du mois (peut etre la derniere ligne)*/
	echo "<tr>\n";
	for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++)
	{
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_fin", $mysql_link, $DEBUG);
	}
	for($i; $i<36-$first_jour_mois_rang+1; $i++)
	{
		if( (($i==35-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==36-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
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
		$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);

		affiche_cellule_jour_cal_saisie($user_login, $j_timestamp, $td_second_class, "new_fin", $mysql_link, $DEBUG);
	}
	for($i; $i<43-$first_jour_mois_rang+1; $i++)
	{
		if( (($i==42-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==43-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie2\">-</td>";
	}
	echo "</tr>\n";

	echo "</table>\n";
}




// affichage du calendrier du mois avec les case à cocher sur les jour d'absence
function  atic_affiche_calendrier_saisie_jour_absence($user_login, $year, $mois, $mysql_link, $DEBUG=FALSE)
{
	$jour_today=date("j");
	$jour_today_name=date("D");

	$first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
	$mois_name=date_fr("F", $first_jour_mois_timestamp);
	$first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	if($first_jour_mois_rang==0)
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

//	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"250\" bgcolor=".$_SESSION['config']['semaine_bgcolor'].">\n";
	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" bgcolor=".$_SESSION['config']['semaine_bgcolor'].">\n";
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
		if( (($i==6)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==7)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
//		echo "<td bgcolor=$bgcolor width=\"14%\" class=\"cal-saisie2\">-</td>";
		echo "<td bgcolor=$bgcolor class=\"cal-saisie2\">-</td>";
	}
	// affichage des cellules cochables du 1 du mois à la fin de la ligne ...
	for($i=$first_jour_mois_rang; $i<8; $i++)
	{
		$j=$i-$first_jour_mois_rang+1 ;
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$j,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==6)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==7)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
//			echo "<td bgcolor=$bgcolor width=\"14%\" class=\"cal-saisie\">$j</td>";
			echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$j</td>";
		}
		else
		{
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j, $DEBUG);
		}
	}
	echo "</tr>\n";

	/* affichage ligne 2 du mois*/
	echo "<tr>\n";
	for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++)
	{
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==14-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==15-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$i</td>";
		}
		else
		{
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	echo "</tr>\n";

	/* affichage ligne 3 du mois*/
	echo "<tr>\n";
	for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++)
	{
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==21-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==22-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$i</td>";
		}
		else
		{
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	echo "</tr>\n";

	/* affichage ligne 4 du mois*/
	echo "<tr>\n";
	for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++)
	{
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==28-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==29-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$i</td>";
		}
		else
		{
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	echo "</tr>\n";

	/* affichage ligne 5 du mois (peut etre la derniere ligne) */
	echo "<tr>\n";
	for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++)
	{
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==35-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==36-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$i</td>";
		}
		else
		{
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	for($i; $i<36-$first_jour_mois_rang+1; $i++)
	{
		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé
		if( (($i==35-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==36-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
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
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==42-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==43-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$i</td>";
		}
		else
		{
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	for($i; $i<43-$first_jour_mois_rang+1; $i++)
	{
		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé
		if( (($i==42-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==43-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie2\">-</td>";
	}
	echo "</tr>\n";

	echo "</table>\n";
}

function atic_affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j, $DEBUG=FALSE)
{
	$bgcolor=$_SESSION['config']['semaine_bgcolor'];
	if(($val_matin=="Y")&&($val_aprem=="Y"))
	{
		$bgcolor=$_SESSION['config']['temps_partiel_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$j<input type=\"radio\" name=\"new_debut\" value=\"$year-$mois-$j-j\"></td>";
	}
	elseif(($val_matin=="Y")&&($val_aprem=="N"))
	{
		$bgcolor=$_SESSION['config']['temps_partiel_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-day_semaine_rtt_am_travail_pm_w35\">$j<input type=\"radio\" name=\"new_debut\" value=\"$year-$mois-$j-a\"></td>";
	}
	elseif(($val_matin=="N")&&($val_aprem=="Y"))
	{
		$bgcolor=$_SESSION['config']['temps_partiel_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-day_semaine_travail_am_rtt_pm_w35\">$j<input type=\"radio\" name=\"new_debut\" value=\"$year-$mois-$j-p\"></td>";
	}
	else
	{
		echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$j</td>";
	}
}

// affichage du calendrier du mois avec les case à cocher sur les jour de présence
function  atic_affiche_calendrier_saisie_jour_presence($user_login, $year, $mois, $mysql_link, $DEBUG=FALSE)
{
	$jour_today=date("j");
	$jour_today_name=date("D");

	$first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
//	$mois_name=date("F", $first_jour_mois_timestamp);
	$mois_name=date_fr("F", $first_jour_mois_timestamp);
	//$first_jour_mois_name=date("D", $first_jour_mois_timestamp);
	$first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	if($first_jour_mois_rang==0)
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)

	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"250\" bgcolor=\"".$_SESSION['config']['semaine_bgcolor']."\">\n";
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
		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé
		if( (($i==6)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==7)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie2\">-</td>";
	}
	// affichage des cellules cochables du 1 du mois à la fin de la ligne ...
	for($i=$first_jour_mois_rang; $i<8; $i++)
	{
		$j=$i-$first_jour_mois_rang+1 ;
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$j,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==6)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==7)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor width=\"14%\" class=\"cal-saisie\">$j</td>";
		}
		else
		{
			$j_timestamp=mktime (0,0,0,$mois,$j,$year);
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j, $DEBUG);
		}
	}
	echo "</tr>\n";

	/* affichage ligne 2 du mois*/
	echo "<tr>\n";
	for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++)
	{
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==14-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==15-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor width=\"14%\" class=\"cal-saisie\">$i</td>";
		}
		else
		{
			$j_timestamp=mktime (0,0,0,$mois,$i,$year);
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	echo "</tr>\n";

	/* affichage ligne 3 du mois*/
	echo "<tr>\n";
	for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++)
	{
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==21-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==22-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor width=\"14%\" class=\"cal-saisie\">$i</td>";
		}
		else
		{
			$j_timestamp=mktime (0,0,0,$mois,$i,$year);
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	echo "</tr>\n";

	/* affichage ligne 4 du mois*/
	echo "<tr>\n";
	for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++)
	{
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==28-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==29-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor width=\"14%\" class=\"cal-saisie\">$i</td>";
		}
		else
		{
			$j_timestamp=mktime (0,0,0,$mois,$i,$year);
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	echo "</tr>\n";

	/* affichage ligne 5 du mois (peut etre la derniere ligne)*/
	echo "<tr>\n";
	for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++)
	{
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==35-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==36-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor width=\"14%\" class=\"cal-saisie\">$i</td>";
		}
		else
		{
			$j_timestamp=mktime (0,0,0,$mois,$i,$year);
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	for($i; $i<36-$first_jour_mois_rang+1; $i++)
	{
		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé
		if( (($i==35-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==36-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
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
		$val_matin="";
		$val_aprem="";
		$j_timestamp=mktime (0,0,0,$mois,$i,$year);

		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé ou jour chomé
		if( (($i==42-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==43-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE))
			|| (est_chome($j_timestamp)==TRUE) || (est_ferme($j_timestamp)==TRUE) )
		{
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			echo "<td bgcolor=$bgcolor width=\"14%\" class=\"cal-saisie\">$i</td>";
		}
		else
		{
			$j_timestamp=mktime (0,0,0,$mois,$i,$year);
			recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);
			affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i, $DEBUG);
		}
	}
	for($i; $i<43-$first_jour_mois_rang+1; $i++)
	{
		// si on est samedi et sam non travaillé ou dimanche et dim non travaillé
		if( (($i==42-$first_jour_mois_rang)&&($_SESSION['config']['samedi_travail']==FALSE))
			|| (($i==43-$first_jour_mois_rang)&&($_SESSION['config']['dimanche_travail']==FALSE)) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie2\">-</td>";
	}
	echo "</tr>\n";

	echo "</table>\n";
}


function atic_affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j, $DEBUG=FALSE)
{
	$bgcolor=$_SESSION['config']['semaine_bgcolor'];
	if(($val_matin=="Y")&&($val_aprem=="Y"))  // rtt le matin et l'apres midi !
	{
		$bgcolor=$_SESSION['config']['temps_partiel_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$j</td>";
	}
	elseif(($val_matin=="Y")&&($val_aprem=="N"))
	{
		$bgcolor=$_SESSION['config']['temps_partiel_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-day_semaine_rtt_am_travail_pm_w35\">$j<input type=\"radio\" name=\"new_fin\" value=\"$year-$mois-$j-p\"></td>";
	}
	elseif(($val_matin=="N")&&($val_aprem=="Y"))
	{
		$bgcolor=$_SESSION['config']['temps_partiel_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"cal-day_semaine_travail_am_rtt_pm_w35\">$j<input type=\"radio\" name=\"new_fin\" value=\"$year-$mois-$j-a\"></td>";
	}
	else
	{
		echo "<td bgcolor=$bgcolor class=\"cal-saisie\">$j<input type=\"radio\" name=\"new_fin\" value=\"$year-$mois-$j-j\"></td>";
	}
}

function get_current_grille_rtt($u_login_to_update, $mysql_link, $DEBUG=FALSE)
/* force la correction du bug apparu en v3rc17 : dernier enregistrement cree
   sans date de fin a 9999-12-31 */ 
{
  $sendoftime = "9999-12-31" ;
  $tab_grille=array();
  /* on recherche le dernier enreg par date_de_fin */ 
  $sql1 = "SELECT * FROM conges_artt WHERE a_login='$u_login_to_update' ORDER BY a_date_fin_grille DESC"; 
  $ReqLog1 = requete_mysql($sql1, $mysql_link, "get_current_grille_rtt", $DEBUG);
  $resultat1 = mysql_fetch_array($ReqLog1) ; 

  if ( $resultat1 ) { 
    if ($resultat1['a_date_fin_grille'] != $sendoftime ) { 
      /* on reecrit la date de fin correct */
      $sql3correctdate = "UPDATE conges_artt SET a_date_fin_grille='$sendoftime' 
WHERE a_login='$u_login_to_update' AND a_date_fin_grille='".$resultat1['a_date_fin_grille']."' ; " ;
      
      $result3 = requete_mysql($sql3correctdate, $mysql_link, "commit_update", $DEBUG);
      $result3commit = requete_mysql("commit ; ", $mysql_link, "commit_update", $DEBUG);
      log_action(0, "", $u_login_to_update,"get_current_grille_rtt correct", 
                 $mysql_link, $DEBUG);
    }
    /* ds ts les cas on charge tab_grille avec cet enregistrement */ 
    $tab_grille['sem_imp_lu_am'] = $resultat1['sem_imp_lu_am'] ;
    $tab_grille['sem_imp_lu_pm'] = $resultat1['sem_imp_lu_pm'] ;
    $tab_grille['sem_imp_ma_am'] = $resultat1['sem_imp_ma_am'] ;
    $tab_grille['sem_imp_ma_pm'] = $resultat1['sem_imp_ma_pm'] ;
    $tab_grille['sem_imp_me_am'] = $resultat1['sem_imp_me_am'] ;
    $tab_grille['sem_imp_me_pm'] = $resultat1['sem_imp_me_pm'] ;
    $tab_grille['sem_imp_je_am'] = $resultat1['sem_imp_je_am'] ;
    $tab_grille['sem_imp_je_pm'] = $resultat1['sem_imp_je_pm'] ;
    $tab_grille['sem_imp_ve_am'] = $resultat1['sem_imp_ve_am'] ;
    $tab_grille['sem_imp_ve_pm'] = $resultat1['sem_imp_ve_pm'] ;
    $tab_grille['sem_imp_sa_am'] = $resultat1['sem_imp_sa_am'] ;
    $tab_grille['sem_imp_sa_pm'] = $resultat1['sem_imp_sa_pm'] ;
    $tab_grille['sem_imp_di_am'] = $resultat1['sem_imp_di_am'] ;
    $tab_grille['sem_imp_di_pm'] = $resultat1['sem_imp_di_pm'] ;

    $tab_grille['sem_p_lu_am'] = $resultat1['sem_p_lu_am'] ;
    $tab_grille['sem_p_lu_pm'] = $resultat1['sem_p_lu_pm'] ;
    $tab_grille['sem_p_ma_am'] = $resultat1['sem_p_ma_am'] ;
    $tab_grille['sem_p_ma_pm'] = $resultat1['sem_p_ma_pm'] ;
    $tab_grille['sem_p_me_am'] = $resultat1['sem_p_me_am'] ;
    $tab_grille['sem_p_me_pm'] = $resultat1['sem_p_me_pm'] ;
    $tab_grille['sem_p_je_am'] = $resultat1['sem_p_je_am'] ;
    $tab_grille['sem_p_je_pm'] = $resultat1['sem_p_je_pm'] ;
    $tab_grille['sem_p_ve_am'] = $resultat1['sem_p_ve_am'] ;
    $tab_grille['sem_p_ve_pm'] = $resultat1['sem_p_ve_pm'] ;
    $tab_grille['sem_p_sa_am'] = $resultat1['sem_p_sa_am'] ;
    $tab_grille['sem_p_sa_pm'] = $resultat1['sem_p_sa_pm'] ;
    $tab_grille['sem_p_di_am'] = $resultat1['sem_p_di_am'] ;
    $tab_grille['sem_p_di_pm'] = $resultat1['sem_p_di_pm'] ;

    $tab_grille['a_date_debut_grille'] = $resultat1['a_date_debut_grille'] ;
    $tab_grille['a_date_fin_grille'] = $sendoftime ; /* whatever */
  } else { /* resultat1 est vide */ 
    error_log("get_current_grille_rtt resultat1vide"); 
  }

  if($DEBUG==TRUE)	{
      echo "get_current_grille_rtt :<br>\n";
      print_r($tab_grille);
      echo "<br>\n";
  }

  return $tab_grille;
}

// saisie de la grille des jours d'abscence ARTT ou temps partiel:
function saisie_jours_absence_temps_partiel($login, $mysql_link, $DEBUG=FALSE)
{
  global $aday,$a30day ; 
	/* initialisation des variables **************/
	$checked_option_sem_imp_lu_am="";
	$checked_option_sem_imp_lu_pm="";
	$checked_option_sem_imp_ma_am="";
	$checked_option_sem_imp_ma_pm="";
	$checked_option_sem_imp_me_am="";
	$checked_option_sem_imp_me_pm="";
	$checked_option_sem_imp_je_am="";
	$checked_option_sem_imp_je_pm="";
	$checked_option_sem_imp_ve_am="";
	$checked_option_sem_imp_ve_pm="";
	$checked_option_sem_imp_sa_am="";
	$checked_option_sem_imp_sa_pm="";
	$checked_option_sem_imp_di_am="";
	$checked_option_sem_imp_di_pm="";

	$checked_option_sem_p_lu_am="";
	$checked_option_sem_p_lu_pm="";
	$checked_option_sem_p_ma_am="";
	$checked_option_sem_p_ma_pm="";
	$checked_option_sem_p_me_am="";
	$checked_option_sem_p_me_pm="";
	$checked_option_sem_p_je_am="";
	$checked_option_sem_p_je_pm="";
	$checked_option_sem_p_ve_am="";
	$checked_option_sem_p_ve_pm="";
	$checked_option_sem_p_sa_am="";
	$checked_option_sem_p_sa_pm="";
	$checked_option_sem_p_di_am="";
	$checked_option_sem_p_di_pm="";
	/*********************************************/

	// recup des données de la dernière table artt du user :
    $resultat1 = get_current_grille_rtt($login, $mysql_link, $DEBUG) ; 
	// $sql1 = "SELECT * FROM conges_artt WHERE a_login='$login' AND a_date_fin_grille='9999-12-31' "  ;
	// $ReqLog1 = requete_mysql($sql1, $mysql_link, "saisie_jours_absence_temps_partiel", $DEBUG);

	// while ($resultat1 = mysql_fetch_array($ReqLog1)) {
    if($resultat1['sem_imp_lu_am']=="Y") $checked_option_sem_imp_lu_am=" checked";
    if($resultat1['sem_imp_lu_pm']=="Y") $checked_option_sem_imp_lu_pm=" checked";
    if($resultat1['sem_imp_ma_am']=="Y") $checked_option_sem_imp_ma_am=" checked";
    if($resultat1['sem_imp_ma_pm']=="Y") $checked_option_sem_imp_ma_pm=" checked";
    if($resultat1['sem_imp_me_am']=="Y") $checked_option_sem_imp_me_am=" checked";
    if($resultat1['sem_imp_me_pm']=="Y") $checked_option_sem_imp_me_pm=" checked";
    if($resultat1['sem_imp_je_am']=="Y") $checked_option_sem_imp_je_am=" checked";
    if($resultat1['sem_imp_je_pm']=="Y") $checked_option_sem_imp_je_pm=" checked";
    if($resultat1['sem_imp_ve_am']=="Y") $checked_option_sem_imp_ve_am=" checked";
    if($resultat1['sem_imp_ve_pm']=="Y") $checked_option_sem_imp_ve_pm=" checked";
    if($resultat1['sem_imp_sa_am']=="Y") $checked_option_sem_imp_sa_am=" checked";
    if($resultat1['sem_imp_sa_pm']=="Y") $checked_option_sem_imp_sa_pm=" checked";
    if($resultat1['sem_imp_di_am']=="Y") $checked_option_sem_imp_di_am=" checked";
    if($resultat1['sem_imp_di_pm']=="Y") $checked_option_sem_imp_di_pm=" checked";

    if($resultat1['sem_p_lu_am']=="Y") $checked_option_sem_p_lu_am=" checked";
    if($resultat1['sem_p_lu_pm']=="Y") $checked_option_sem_p_lu_pm=" checked";
    if($resultat1['sem_p_ma_am']=="Y") $checked_option_sem_p_ma_am=" checked";
    if($resultat1['sem_p_ma_pm']=="Y") $checked_option_sem_p_ma_pm=" checked";
    if($resultat1['sem_p_me_am']=="Y") $checked_option_sem_p_me_am=" checked";
    if($resultat1['sem_p_me_pm']=="Y") $checked_option_sem_p_me_pm=" checked";
    if($resultat1['sem_p_je_am']=="Y") $checked_option_sem_p_je_am=" checked";
    if($resultat1['sem_p_je_pm']=="Y") $checked_option_sem_p_je_pm=" checked";
    if($resultat1['sem_p_ve_am']=="Y") $checked_option_sem_p_ve_am=" checked";
    if($resultat1['sem_p_ve_pm']=="Y") $checked_option_sem_p_ve_pm=" checked";
    if($resultat1['sem_p_sa_am']=="Y") $checked_option_sem_p_sa_am=" checked";
    if($resultat1['sem_p_sa_pm']=="Y") $checked_option_sem_p_sa_pm=" checked";
    if($resultat1['sem_p_di_am']=="Y") $checked_option_sem_p_di_am=" checked";
    if($resultat1['sem_p_di_pm']=="Y") $checked_option_sem_p_di_pm=" checked";
    $date_deb_grille=$resultat1['a_date_debut_grille'];
    $date_fin_grille=$resultat1['a_date_fin_grille'];

    /* _admin_mod_artt_ : determination plage saisie de date de debut 
       si saisie initiale : mindate est debut annee courante 
       sinon                mindate est ddebut_grille_encours + 1 j 

       maxdate est date du jour + 30 jours permettant anticiper */

    if ($date_deb_grille) { 
      $ddeb_grille = new DateTime($date_deb_grille); 
      $dmindate = $ddeb_grille ;   $dmindate->add($aday);
      $smindate = $dmindate->format('Y-m-d') ; 
    } else { 
      $ltoday = conges_get_date(False); 
      $smindate = sprintf("%02d-%02d-%02d",$ltoday['year'],1,1) ; 
    }
    $dmaxdate = new DateTime(conges_get_date_fmt1(False)) ; 
    $dmaxdate->add($a30day);
    
    $oconfigartt = array("user" => $login , 
                         "date_deb_grille_actu" => $date_deb_grille ,
                         "smindate" => $smindate , 
                         "smaxdate" => $dmaxdate->format('Y-m-d')
                         );

    echo "<div id=\"oconfigartt\" class=\"tech\" > \n"; /*  */
    echo json_encode($oconfigartt);
    echo "</div>\n"; 

    //    echo "<p>_admin_mod_artt: restructurer IHM </p>" ;
	echo "<h4>".$_SESSION['lang']['admin_temps_partiel_titre']." :</h4>\n";
	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
    /*	echo "<tr align=\"center\">\n";
	echo "<td colspan=\"3\">\n";
    echo $_SESSION['lang']['admin_temps_partiel_date_valid_actuel']." : ".$date_deb_grille ; 
    echo "</td>\n";
	echo "</tr>\n"; */ 
	echo "<tr valign=\"top\">\n";
	echo "<td align=\"left\" >\n"; // cell des selecteur de djlp 
		//tableau semaines impaires
		echo "<b><u>".$_SESSION['lang']['admin_temps_partiel_sem_impaires']." :</u></b><br>\n";
		$tab_checkbox_sem_imp=array();
		$imp_lu_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_lu_am]\" value=\"Y\" $checked_option_sem_imp_lu_am>";
		$imp_lu_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_lu_pm]\" value=\"Y\" $checked_option_sem_imp_lu_pm>";
		$imp_ma_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_ma_am]\" value=\"Y\" $checked_option_sem_imp_ma_am>";
		$imp_ma_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_ma_pm]\" value=\"Y\" $checked_option_sem_imp_ma_pm>";
		$imp_me_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_me_am]\" value=\"Y\" $checked_option_sem_imp_me_am>";
		$imp_me_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_me_pm]\" value=\"Y\" $checked_option_sem_imp_me_pm>";
		$imp_je_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_je_am]\" value=\"Y\" $checked_option_sem_imp_je_am>";
		$imp_je_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_je_pm]\" value=\"Y\" $checked_option_sem_imp_je_pm>";
		$imp_ve_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_ve_am]\" value=\"Y\" $checked_option_sem_imp_ve_am>";
		$imp_ve_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_ve_pm]\" value=\"Y\" $checked_option_sem_imp_ve_pm>";
		if($_SESSION['config']['samedi_travail']==TRUE)
		{
			$imp_sa_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_sa_am]\" value=\"Y\" $checked_option_sem_imp_sa_am>";
			$imp_sa_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_sa_pm]\" value=\"Y\" $checked_option_sem_imp_sa_pm>";
		}
		if($_SESSION['config']['dimanche_travail']==TRUE)
		{
			$imp_di_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_di_am]\" value=\"Y\" $checked_option_sem_imp_di_am>";
			$imp_di_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_imp[sem_imp_di_pm]\" value=\"Y\" $checked_option_sem_imp_di_pm>";
		}

		echo "<table cellpadding=\"1\" class=\"tablo\">\n";
		echo "<tr align=\"center\">\n";
			echo "<td></td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['lundi']."</td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['mardi']."</td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['mercredi']."</td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['jeudi']."</td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['vendredi']."</td>\n";
			if($_SESSION['config']['samedi_travail']==TRUE)
				echo "<td class=\"histo\">".$_SESSION['lang']['samedi']."</td>\n";
			if($_SESSION['config']['dimanche_travail']==TRUE)
				echo "<td class=\"histo\">".$_SESSION['lang']['dimanche']."</td>\n";
		echo "</tr>\n";
		echo "<tr align=\"center\">\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['admin_temps_partiel_am']."</td>\n";
			echo "<td class=\"histo\">$imp_lu_am</td>\n";
			echo "<td class=\"histo\">$imp_ma_am</td>\n";
			echo "<td class=\"histo\">$imp_me_am</td>\n";
			echo "<td class=\"histo\">$imp_je_am</td>\n";
			echo "<td class=\"histo\">$imp_ve_am</td>\n";
			if($_SESSION['config']['samedi_travail']==TRUE)
				echo "<td class=\"histo\">$imp_sa_am</td>\n";
			if($_SESSION['config']['dimanche_travail']==TRUE)
				echo "<td class=\"histo\">$imp_di_am</td>\n";
		echo "</tr>\n";
		echo "<tr align=\"center\">\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['admin_temps_partiel_pm']."</td>\n";
			echo "<td class=\"histo\">$imp_lu_pm</td>\n";
			echo "<td class=\"histo\">$imp_ma_pm</td>\n";
			echo "<td class=\"histo\">$imp_me_pm</td>\n";
			echo "<td class=\"histo\">$imp_je_pm</td>\n";
			echo "<td class=\"histo\">$imp_ve_pm</td>\n";
			if($_SESSION['config']['samedi_travail']==TRUE)
				echo "<td class=\"histo\">$imp_sa_pm</td>\n";
			if($_SESSION['config']['dimanche_travail']==TRUE)
				echo "<td class=\"histo\">$imp_di_pm</td>\n";
		echo "</tr>\n";
		echo "</table>\n";

	echo "</td>\n";
	echo " <td><img src=\"../img/shim.gif\" width=\"15\" height=\"2\" border=\"0\" vspace=\"0\" hspace=\"0\"></td>\n";
	echo " <td>\n";

		//tableau semaines paires
		echo "<b><u>".$_SESSION['lang']['admin_temps_partiel_sem_paires'].":</u></b><br>\n";
		$tab_checkbox_sem_p=array();
		$p_lu_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_lu_am]\" value=\"Y\" $checked_option_sem_p_lu_am>";
		$p_lu_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_lu_pm]\" value=\"Y\" $checked_option_sem_p_lu_pm>";
		$p_ma_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_ma_am]\" value=\"Y\" $checked_option_sem_p_ma_am>";
		$p_ma_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_ma_pm]\" value=\"Y\" $checked_option_sem_p_ma_pm>";
		$p_me_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_me_am]\" value=\"Y\" $checked_option_sem_p_me_am>";
		$p_me_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_me_pm]\" value=\"Y\" $checked_option_sem_p_me_pm>";
		$p_je_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_je_am]\" value=\"Y\" $checked_option_sem_p_je_am>";
		$p_je_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_je_pm]\" value=\"Y\" $checked_option_sem_p_je_pm>";
		$p_ve_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_ve_am]\" value=\"Y\" $checked_option_sem_p_ve_am>";
		$p_ve_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_ve_pm]\" value=\"Y\" $checked_option_sem_p_ve_pm>";
		$p_sa_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_sa_am]\" value=\"Y\" $checked_option_sem_p_sa_am>";
		$p_sa_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_sa_pm]\" value=\"Y\" $checked_option_sem_p_sa_pm>";
		$p_di_am="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_di_am]\" value=\"Y\" $checked_option_sem_p_di_am>";
		$p_di_pm="<input type=\"checkbox\" name=\"tab_checkbox_sem_p[sem_p_di_pm]\" value=\"Y\" $checked_option_sem_p_di_pm>";

		echo "<table cellpadding=\"1\"  class=\"tablo\">\n";
		echo "<tr align=\"center\">\n";
			echo "<td></td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['lundi']."</td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['mardi']."</td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['mercredi']."</td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['jeudi']."</td>\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['vendredi']."</td>\n";
			if($_SESSION['config']['samedi_travail']==TRUE)
				echo "<td class=\"histo\">".$_SESSION['lang']['samedi']."</td>\n";
			if($_SESSION['config']['dimanche_travail']==TRUE)
				echo "<td class=\"histo\">".$_SESSION['lang']['dimanche']."</td>\n";
		echo "</tr>\n";
		echo "<tr align=\"center\">\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['admin_temps_partiel_am']."</td>\n";
			echo "<td class=\"histo\">$p_lu_am</td>\n";
			echo "<td class=\"histo\">$p_ma_am</td>\n";
			echo "<td class=\"histo\">$p_me_am</td>\n";
			echo "<td class=\"histo\">$p_je_am</td>\n";
			echo "<td class=\"histo\">$p_ve_am</td>\n";
			if($_SESSION['config']['samedi_travail']==TRUE)
				echo "<td class=\"histo\">$p_sa_am</td>\n";
			if($_SESSION['config']['dimanche_travail']==TRUE)
				echo "<td class=\"histo\">$p_di_am</td>\n";
		echo "</tr>\n";
		echo "<tr align=\"center\">\n";
			echo "<td class=\"histo\">".$_SESSION['lang']['admin_temps_partiel_pm']."</td>\n";
			echo "<td class=\"histo\">$p_lu_pm</td>\n";
			echo "<td class=\"histo\">$p_ma_pm</td>\n";
			echo "<td class=\"histo\">$p_me_pm</td>\n";
			echo "<td class=\"histo\">$p_je_pm</td>\n";
			echo "<td class=\"histo\">$p_ve_pm</td>\n";
			if($_SESSION['config']['samedi_travail']==TRUE)
				echo "<td class=\"histo\">$p_sa_pm</td>\n";
			if($_SESSION['config']['dimanche_travail']==TRUE)
				echo "<td class=\"histo\">$p_di_pm</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
        echo "</td>\n"; // fin cell des selecteur de djlp 
    echo "<td>&nbsp;&nbsp;</td>\n";    
    echo "<td>&nbsp;&nbsp;</td>\n";    
	echo "<td  align=\"right\">\n";
    echo "<div>". $_SESSION['lang']['admin_temps_partiel_date_valid_actuel']." : ".$date_deb_grille."<br><br>" ; 
    echo $_SESSION['lang']['admin_temps_partiel_date_valid']."<br><br></div>" ;

    echo "</td>\n";

	echo "</tr>\n";
    /* 	retrait ancien selecteur de date de début  */

    /* _admin_mod_artt_ */
    echo "<tr align=\"center\">\n";
    echo "<td></td>\n";
    echo "<td></td>\n";
    echo "<td></td>\n";
    echo "<td></td>\n";
    echo "<td></td>\n";
	echo "<td>\n";
    echo "<input type=\"text\" id=\"newschemed\" name=\"newschemed\" size=\"10\" readonly style=\"text-align:center;\"/><br>\n"; 
    echo "<div id=\"dpickernewscheme\"></div>\n";  
	echo "</td></tr>\n"; 
	echo "</table>\n";

}


// retourne le nom du jour de la semaine en francais sur 2 caracteres
function get_j_name_fr_2c($timestamp)
{
	$jour_name_fr_2c="";

	$jour_num=date("w", $timestamp);
	switch($jour_num) {
	 	case 1:
			$jour_name_fr_2c = "lu" ;
			break;
	 	case 2:
			$jour_name_fr_2c = "ma" ;
			break;
	 	case 3:
			$jour_name_fr_2c = "me" ;
			break;
	 	case 4:
			$jour_name_fr_2c = "je" ;
			break;
	 	case 5:
			$jour_name_fr_2c = "ve" ;
			break;
	 	case 6:
			$jour_name_fr_2c = "sa" ;
			break;
	 	case 0:
			$jour_name_fr_2c = "di" ;
			break;
		default:
			$jour_name_fr_2c=FALSE;
	}

	return $jour_name_fr_2c;
}


// retourne le nom du jour de la semaine dans la langue choisie sur 2 caracteres
function get_j_name_lang_2c($timestamp)
{
	$jour_name_fr_2c="";

	$jour_num=date("w", $timestamp);
	switch($jour_num) {
	 	case 1:
			$jour_name_fr_2c = $_SESSION['lang']['lundi_2c'] ;
			break;
	 	case 2:
			$jour_name_fr_2c = $_SESSION['lang']['mardi_2c'] ;
			break;
	 	case 3:
			$jour_name_fr_2c = $_SESSION['lang']['mercredi_2c'] ;
			break;
	 	case 4:
			$jour_name_fr_2c = $_SESSION['lang']['jeudi_2c'] ;
			break;
	 	case 5:
			$jour_name_fr_2c = $_SESSION['lang']['vendredi_2c'] ;
			break;
	 	case 6:
			$jour_name_fr_2c = $_SESSION['lang']['samedi_2c'] ;
			break;
	 	case 0:
			$jour_name_fr_2c = $_SESSION['lang']['dimanche_2c'] ;
			break;
		default:
			$jour_name_fr_2c=FALSE;
	}

	return $jour_name_fr_2c;
}

//affiche le formulaire de saisie d'une nouvelle demande de conges
// _jqcal mode 
function saisie_nouveau_conges($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet, $by="user", $mysql_link, $DEBUG=FALSE)
{
//$DEBUG=TRUE;
  /*   $oconfig = array("by" => utf8_encode($by), 
                   "msg_saisie_deb" => utf8_encode($_SESSION['lang']['export_cal_saisir_debut']) ,
                   "msg_saisie_fin" => utf8_encode($_SESSION['lang']['export_cal_saisir_fin']) ,
                   "msg_w_date_anterieur" => utf8_encode($_SESSION['lang']['calcul_nb_jours_commentaire_bad_date']) ,
                   "msg_w_ampm"=> utf8_encode($_SESSION['lang']['calcul_nb_jours_commentaire_bad_date2']) ,
                   "msg_ok_congeavalider"=> utf8_encode($_SESSION['lang']['divers_valider']));  */
  $oconfig = array("by" => $by, 
                   "msg_saisie_deb" => $_SESSION['lang']['export_cal_saisir_debut'] ,
                   "msg_saisie_fin" => $_SESSION['lang']['export_cal_saisir_fin'] ,
                   "msg_w_date_anterieur" => $_SESSION['lang']['calcul_nb_jours_commentaire_bad_date'] ,
                   "msg_w_ampm"=> $_SESSION['lang']['calcul_nb_jours_commentaire_bad_date2'] ,
                   "msg_ok_congeavalider"=> $_SESSION['lang']['divers_valider']); 

	if($DEBUG==TRUE) { echo "user_login = $user_login, year_calendrier_saisie_debut = $year_calendrier_saisie_debut, mois_calendrier_saisie_debut = $mois_calendrier_saisie_debut, year_calendrier_saisie_fin = $year_calendrier_saisie_fin, mois_calendrier_saisie_fin = $mois_calendrier_saisie_fin, onglet = $onglet<br>\n";}

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	$mois_calendrier_saisie_debut_prec=0; $year_calendrier_saisie_debut_prec=0;
	$mois_calendrier_saisie_debut_suiv=0; $year_calendrier_saisie_debut_suiv=0;
	$mois_calendrier_saisie_fin_prec=0; $year_calendrier_saisie_fin_prec=0;
	$mois_calendrier_saisie_fin_suiv=0; $year_calendrier_saisie_fin_suiv=0;

	init_tab_jours_fermeture($user_login, $mysql_link);
    // _jqcal obtenir les periodes de conges pour l'agent (jours fermetures + conges valide et en demande) ; 
    // _passer ces donnees ds le formulaire ; 
    echo "<div id=\"oconfig\" class=\"tech\"> \n"; //  
    echo json_encode($oconfig);
    echo "</div>\n"; 

    echo "<div id=\"vacperiod\" class=\"tech\" > \n"; // 
    // echo "<div id=\"vacperiod\" style=\"display: yes;\"> \n";

    $ldateframe = get_frame_date("conge",$by) ;
    $vperiod = get_vperiod($user_login, $mysql_link,$ldateframe[0],$ldateframe[1]);

    echo json_encode($vperiod);
    echo "</div>\n"; 

    echo "<form action=\"$PHP_SELF?session=$session&onglet=$onglet\" method=\"POST\">\n" ;
    /* these data are here to be updated by javascript and passed as input 
       for further processing when new_demande is recorded */ 
    echo "<div id=\"myform\" class=\"tech\"> \n"; // style=\"display: none;\"
    echo "<input type=\"text\" readonly name=\"login_user\" value=\"$user_login\">\n";  // type=\"hidden\" 
    echo "<input type=\"text\" readonly name=\"session\" value=\"$session\">\n";
    echo "<input readonly name=\"user_login\" value=\"$user_login\">\n";
    echo "<input readonly name=\"new_demande_conges\" value=1>\n";
    /* les valeurs prises en format yyyy-mm-dd pour enregistrer en base le conges */ 
    echo "<input type=\"text\" id=\"new_debut\" name=\"new_debut\">\n" ; 
    echo "<input type=\"text\" id=\"new_fin\" name=\"new_fin\">\n" ; 
    echo "</div>\n" ; 

    echo "<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\">\n";
    echo "<tr align=\"center\">\n";
    echo "<td>\n";
    echo "<table cellpadding=\"3\" cellspacing=\"3\" border=\"0\">\n";

    echo "<tr>\n";
    echo "<td colspan=\"6\" id=\"dpicker_message_container\" class=\"ui-fbmessage\">\n";
    //    echo "<div id=\"dpicker_message\" class=\"ui-fbmessage\">dpicker_message</div>\n";
    echo "<div id=\"dpicker_message\">dpicker_message</div>\n";
    echo "</td>" ; 
    /*     echo "<td colspan=\"6\" id=\"dpicker_message\" class=\"ui-fbmessage\">dpicker_message</td>\n"; */

    echo "</tr>\n";
    echo "</tr>\n";

    echo "<tr align=\"center\">\n";
    echo "<td>\n";
    //    echo "<fieldset class=\"cal_saisie\">\n";


    // echo "<legend>date début / fin </legend> \n";
    //    echo "<table cellpadding=\"5\" cellspacing=\"5\" border=\"1\">\n";
    echo "<table border=\"0\">\n";
    echo "<tr valign=\"top\">\n";  // tick box 
    // displaying by default checked a checkbox to exclude week-end of selection 
    echo "<td colspan=3>\n"; // style=\"display: none;\"
    // echo "<div> \n"; 
    echo "<div class=\"module\">
      <div class=\"caption\">
       <img src=\"../img/rollup.gif\" alt=\"rollup\" title=\"Déroule l'aide sur les sélecteurs de date\"/>&nbsp;&nbsp;Aide / sélecteur de date
        
      </div>
      <div class=\"body\" style=\"display: none;\">
        <img src=\"../img/legend_2.png\"/>
      </div>
    </div>" ; 
    if (isset($_SESSION['config']['inclus-selecteur-we'])) { 
      if ($_SESSION['config']['inclus-selecteur-we']=="TRUE") { 
        echo "<input id=\"weexc\" type=\"checkbox\" value=\"weexc_t\" checked >"
          .$_SESSION['lang']['week-end_exclus']."</input><br>";
      } else { 
        echo "<input disabled id=\"weexc\" type=\"checkbox\" value=\"weexc_t\" checked >"
          .$_SESSION['lang']['week-end_exclus']."</input><br>";
      }
    }
    echo "</td>\n"; 
    echo "</tr>\n";
    // 250 px to reserve space when datapicker resizes
    echo "<tr align=\"center\" valign=\"top\">\n"; 
    echo "<td>date début</td><td></td><td>date fin</td>\n" ; 
    echo "</tr>\n"; 
    echo "<tr align=\"center\" valign=\"top\" style=\"height:236px\">\n"; 

    echo "<td width=\"30\">\n";
    echo "<div id=\"dpickerstart\"></div>\n";

    echo "</td>\n";
    echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n"; // to enlarge space between datepicker

    echo "<td>\n";
    echo "<div id=\"dpickerend\"></div>\n";
    echo "</td>\n";

    echo "</tr>\n";

    echo "<tr align=\"center\" valign=\"top\">\n";
    echo "<td>\n";
    // affichage date et saisie am/pm 

    /* boutons radio matin ou après midi */
    /* echo "<input type=\"text\" id=\"dpickerstart_val\" size=\"20\" readonly /><br>\n"; */ 
    echo "<input type=\"text\" id=\"new_debut_disp\" name=\"new_debut_disp\" size=\"20\" readonly style=\"text-align:center;\"/><br>\n";
    echo "<input type=\"radio\" name=\"new_demi_jour_deb\" id=\"dppart_startam\" ";
    echo "value=\"am\" checked><b><u>".$_SESSION['lang']['form_am']."</u></b><br>\n";
    
    echo "<input type=\"radio\" name=\"new_demi_jour_deb\" id=\"dppart_startpm\"";
    echo "value=\"pm\"><b><u>".$_SESSION['lang']['form_pm']."</u></b><br>\n";

    echo "</td>\n";
    echo "<td></td>\n"; // to enlarge space between datepicker
    echo "<td>\n";
    // affichage date et saisie am/pm 
    /* boutons radio matin après midi */
    /* echo "<input type=\"text\" id=\"dpickerend_val\" size=\"20\" readonly /><br>\n"; */ 
    echo "<input type=\"text\" id=\"new_fin_disp\" name=\"new_fin_disp\" size=\"20\" readonly style=\"text-align:center;\"/><br>\n";
    echo "<input type=\"radio\" name=\"new_demi_jour_fin\" id=\"dppart_endam\" ";
    echo "value=\"am\"><b><u>".$_SESSION['lang']['form_am']."</u></b><br>\n";
    
    echo "<input type=\"radio\" name=\"new_demi_jour_fin\"  id=\"dppart_endpm\"  ";
    echo "value=\"pm\" checked><b><u>".$_SESSION['lang']['form_pm']."</u></b><br><br>\n";
    echo "</td>\n";
    echo "</table>\n";
    //    echo "</fieldset>\n";

    echo "</td>\n";   // fin selecteur de date 
    echo "<td>&nbsp;&nbsp;&nbsp;</td>\n"; // to enlarge space 

    // debut selecteur de conges 
    /*****************/
    /* boutons radio */
    /*****************/
    // recup d tableau des types de conges
    $tab_type_conges=recup_tableau_types_conges($mysql_link, $DEBUG);
    $tab_type_conges_ordinaires=recup_tableau_types_conges_ordinaires($mysql_link, $DEBUG);//modif du 23 nov2012

    // recup du tableau des types d'absence
    $tab_type_absence=recup_tableau_types_absence($mysql_link, $DEBUG);

    // recup du tableau des types de conges exceptionnels
    $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($mysql_link, $DEBUG);

    echo "<td align=\"left\" valign=\"top\">\n";
    // si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
    // OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
    if( (($_SESSION['config']['user_saisie_demande']==TRUE)&&($user_login==$_SESSION['userlogin'])) ||
        (($_SESSION['config']['user_saisie_demande']==TRUE)&&($user_login!=$_SESSION['userlogin'])))
      if(is_gest($_SESSION['userlogin'], $mysql_link, $DEBUG))// modif 16 nov 2012
        {
          // congés
          echo "<b><i><u>".$_SESSION['lang']['divers_conges']." :</u></i></b><br>\n";
          foreach($tab_type_conges as $id => $libelle)
            {
              if($id==1)
                echo "<input type=\"radio\" name=\"new_type\" value=\"$id\" checked> $libelle<br>\n";
              else
                echo "<input type=\"radio\" name=\"new_type\" value=\"$id\"> $libelle<br>\n";
            }
        }
      else
        {
          // congés
          echo "<b><i><u>".$_SESSION['lang']['divers_conges']." :</u></i></b><br>\n";
          foreach($tab_type_conges_ordinaires as $id => $libelle)
            {
              if($id==1)
                echo "<input type=\"radio\" name=\"new_type\" value=\"$id\" checked> $libelle<br>\n";
              else
                echo "<input type=\"radio\" name=\"new_type\" value=\"$id\"> $libelle<br>\n";
            }
        }
    // si le user a droit de saisir une mission ET si on est PAS dans une fenetre de responsable
    // OU si le resp a droit de saisir une mission ET si on est PAS dans une fenetre d'utilisateur
    // OU si le resp a droit de saisir une mission ET si le resp est resp de lui meme
    if( (($_SESSION['config']['user_saisie_mission']==TRUE)&&($user_login==$_SESSION['userlogin'])) ||
        (($_SESSION['config']['resp_saisie_mission']==TRUE)&&($user_login!=$_SESSION['userlogin'])) ||
        (($_SESSION['config']['resp_saisie_mission']==TRUE)&&(is_resp_of_user($_SESSION['userlogin'], $user_login, $mysql_link, $DEBUG)==TRUE)) )
      {
        echo "<br>\n";
        // absences
        echo "<b><i><u>".$_SESSION['lang']['divers_absences']." :</u></i></b><br>\n";
        foreach($tab_type_absence as $id => $libelle)
          {
            echo "<input type=\"radio\" name=\"new_type\" value=\"$id\"> $libelle<br>\n";
          }
      }
    // si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
    // OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
    if( ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) && (
                                                                        (($_SESSION['config']['user_saisie_demande']==TRUE)&&($user_login==$_SESSION['userlogin'])) ||
                                                                        (($_SESSION['config']['user_saisie_demande']==TRUE)&&($user_login!=$_SESSION['userlogin'])) ) )
      {
        echo "<br>\n";
        // congés exceptionnels
        echo "<b><i><u>".$_SESSION['lang']['divers_conges_exceptionnels']." :</u></i></b><br>\n";
        foreach($tab_type_conges_exceptionnels as $id => $libelle)
          {
            if($id==1)
              echo "<input type=\"radio\" name=\"new_type\" value=\"$id\" checked> $libelle<br>\n";
            else
              echo "<input type=\"radio\" name=\"new_type\" value=\"$id\"> $libelle<br>\n";
          }
      }

    echo "</td>\n";  // fin selecteur de conges 
    echo "<td>&nbsp;&nbsp;&nbsp;</td>\n"; // to enlarge space 

    echo "<td valign=\"top\">\n"; // debut nombre de conges , solde / nouveau solde 
    echo "<table cellpadding=\"2\" cellspacing=\"3\" border=\"0\" >\n";
//					echo "<input type=\"hidden\" name=\"login_user\" value=\"".$_SESSION['userlogin']."\">\n";
    // bouton "compter les jours" TOUJOURS FALSE
    if($_SESSION['config']['affiche_bouton_calcul_nb_jours_pris']==TRUE && False ) {
        echo "<tr><td colspan=\"2\">\n";
        echo "<input type=\"button\" onclick=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;\" value=\"".$_SESSION['lang']['saisie_conges_compter_jours']."\">\n";
        echo "</td></tr>\n";
    };
    // style=\"background-color: #D4D4D4; \" readonly=\"readonly\"
    // zone de texte en readonly et grisée TOUJOURS TRUE
    if($_SESSION['config']['disable_saise_champ_nb_jours_pris']==TRUE || True) { 
      $text_nb_jours="<input type=\"text\" name=\"new_nb_jours\" size=\"10\" maxlength=\"30\" value=\"\" readonly>" ;
      $solde_nouv="<input type=\"text\" name=\"solde_nouv\" size=\"10\" maxlength=\"30\" value=\"\" readonly>" ;
    } else {
      $text_nb_jours="<input type=\"text\" name=\"new_nb_jours\" size=\"10\" maxlength=\"30\" value=\"\">" ;
    }

    $text_commentaire="<input type=\"text\" name=\"new_comment\" size=\"50\" maxlength=\"50\" value=\"\">" ;
    // zones de texte  align=\"center\"
    echo "<tr><td>".$_SESSION['lang']['saisie_conges_nb_jours']."</td>";
    echo "<td>".$_SESSION['lang']['saisie_conges_solde_futur']."</td></tr>\n";  
    echo "<tr>";
    echo "<td>$text_nb_jours</td>";
    echo "<td>$solde_nouv</td></tr>\n";
    
    echo "<tr><td colspan=2 >".$_SESSION['lang']['divers_comment_maj_1']."</td></tr>\n";
    echo "<td colspan=2>$text_commentaire</td>\n";
    echo "</tr>\n";
    //     echo "<tr align=\"center\"><td><img src=\"../img/shim.gif\" width=\"15\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\"></td><td></td></tr>\n";


    echo "<tr style=\"height:200px\" valign=bottom>\n"; // align=\"center\"
    echo "<td>\n";
    // boutons du formulaire
    echo "<input disabled  id=\"but_submit\" type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">";
    echo "</td>\n";
    echo "<td align=right>\n";
    echo "<input id=\"but_reset\" type=\"reset\" value=\"".$_SESSION['lang']['form_cancel']."\">\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";

    echo "</td>\n";

    echo "</tr>\n";
    echo "</table>\n";  
    echo "</tr>\n";
    echo "</table>\n";  

    echo "</form>\n" ;
} // fin saisie_nouveau_conges

function saisie_echange_rtt($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet, $by="user", $mysql_link, $DEBUG=FALSE)
{
//$DEBUG=TRUE;
	if($DEBUG==TRUE) { echo "user_login = $user_login, year_calendrier_saisie_debut = $year_calendrier_saisie_debut, mois_calendrier_saisie_debut = $mois_calendrier_saisie_debut, year_calendrier_saisie_fin = $year_calendrier_saisie_fin, mois_calendrier_saisie_fin = $mois_calendrier_saisie_fin, onglet = $onglet<br>\n";}

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
    $oconfig = array(
       "msg_saisie_deb" => $_SESSION['lang']['echange_rtt_saisir_adeplacer'] ,
       "msg_saisie_fin" => $_SESSION['lang']['echange_rtt_saisir_souhaite'] ,
       "msg_w_ampm"=> $_SESSION['lang']['calcul_nb_jours_commentaire_bad_date2'] ,
       "msg_ok_congeavalider"=> $_SESSION['lang']['divers_valider']); 

    echo "<div id=\"oconfig\" class=\"tech\" > \n"; //  
    echo json_encode($oconfig);
    echo "</div>\n"; 

    echo "<div id=\"vacperiod\" class=\"tech\"  > \n"; //style=\"display: none;\"
    $ldateframe = get_frame_date("artt",$by) ;
    $vperiod = get_vperiod($user_login, $mysql_link,$ldateframe[0],$ldateframe[1]);
 
    echo json_encode($vperiod);
    echo "</div>\n"; 

	echo "<form action=\"$PHP_SELF?session=$session&&onglet=$onglet\" method=\"POST\">\n" ;
    /* these data are here to be updated by javascript and passed as input 
       for further processing when new_demande is recorded */ 
    echo "<div id=\"myform\" class=\"tech\"> \n"; // style=\"display: none;\"
    echo "<input type=\"hidden\" name=\"new_echange_rtt\" value=1>\n";
    echo "<input type=\"text\" readonly name=\"login_user\" value=\"$user_login\">\n";  // type=\"hidden\" 
    echo "<input type=\"text\" readonly name=\"session\" value=\"$session\">\n";
    echo "<input readonly name=\"user_login\" value=\"$user_login\">\n";
    /* les valeurs prises en format yyyy-mm-dd pour enregistrer en base le conges */ 
    echo "<input type=\"text\" id=\"new_debut\" name=\"new_debut\">\n" ; 
    echo "<input type=\"text\" id=\"dmoved\" name=\"dmoved\">\n" ; 
    echo "<input type=\"text\" id=\"new_fin\" name=\"new_fin\">\n" ; 
    echo "<input type=\"text\" id=\"dchosen\" name=\"dchosen\">\n" ; 
    
    echo "</div>\n" ; 

    echo "<table cellpadding=\"0\" cellspacing=\"5\" border=\"0\" width=\"90%\" >\n";

    echo "<tr>\n";
    echo "<td colspan=4 id=\"dpicker_message_container\" class=\"ui-fbmessage\">\n";
    echo "<div id=\"dpicker_message\">dpicker_message</div>\n";
    echo "</td>" ; 
    echo "</tr>\n";

    echo "<tr align=\"center\" style=\"height:248px\">\n";

    // cellule 1 : calendrier de saisie du jour a deplacer 
    echo "<td align=\"center\" valign=\"top\" >\n"; // width=\"30\" 
    echo $_SESSION['lang']['saisie_echange_titre_calendrier_1']."\n"; 
    echo "<div id=\"dpickermoved\"></div>\n";
    /* echo "<input type=\"text\" id=\"dmoved\" name=\"dmoved\" size=\"12\" readonly /><br>\n"; //
       echo "<input type=\"text\" id=\"new_debut\" name=\"new_debut\" size=\"12\" readonly />\n"; // conserve new_debut  */ 

    echo "</td>\n";
    
    //
    echo "<td width=\"35%\" >\n";
    echo "<input type=\"radio\" name=\"moment_absence_ordinaire\" id=\"dppart_moved_am\" value=\"a\">";
    echo "<b><u>".$_SESSION['lang']['form_am']."</u></b>";
    echo "<input type=\"radio\" name=\"moment_absence_souhaitee\" id=\"dppart_chosen_am\" value=\"a\"><br><br>\n";

    echo "<input type=\"radio\" name=\"moment_absence_ordinaire\" id=\"dppart_moved_pm\" value=\"p\">";
    echo "<b><u>".$_SESSION['lang']['form_pm']."</u></b>";
    echo "<input type=\"radio\" name=\"moment_absence_souhaitee\" id=\"dppart_chosen_pm\" value=\"p\"><br><br>\n";

    echo "<input type=\"radio\" name=\"moment_absence_ordinaire\" id=\"dppart_moved_day\" value=\"j\" >"; //  checked
    echo "<b><u>".$_SESSION['lang']['form_day']."</u></b>";
    echo "<input type=\"radio\" name=\"moment_absence_souhaitee\" id=\"dppart_chosen_day\" value=\"j\" >\n"; //  checked
    echo "</td>\n";

    // cellule 1 : calendrier de saisie du jour souhaite
    echo "<td  align=\"center\" valign=\"top\" >\n";
    echo $_SESSION['lang']['saisie_echange_titre_calendrier_2']."\n"; 
    echo "<div id=\"dpickerchosen\"></div>\n";
    /*     echo "<input type=\"text\" id=\"dchosen\" name=\"dchosen\" size=\"12\" readonly /><br>\n";// conserve new_fin
           echo "<input type=\"text\" id=\"new_fin\" name=\"new_fin\" size=\"12\" readonly />\n";// conserve new_fin  */ 
    echo "</td>\n";

    // cellule 1 : champs texte et boutons (valider/cancel)
    echo "<td width=50%>\n";

    /***  formulaire ***/
    echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" >\n";
    echo "<tr align=\"left\">\n";
    echo "<td colspan=2><b>".$_SESSION['lang']['divers_comment_maj_1']." : </b><br>\n"; 
    echo "<input type=\"text\" name=\"new_comment\" size=50 maxlength=\"50\" value=\"\">\n";
    echo "</td>\n";

    echo "</tr>\n";
    echo "<tr align=\"center\">\n";
    echo "<td colspan=2><img src=\"../img/shim.gif\" width=\"15\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\"></td>\n";
    echo "</tr>\n";
    echo "<tr align=\"center\">\n";
    echo "<td>\n";
    echo "<input disabled  id=\"but_submit\" type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">";
    echo "</td>\n"; 
    echo "<td align=right>\n";
    echo "<input type=\"reset\" id=\"but_reset\" value=\"".$_SESSION['lang']['form_cancel']."\">\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
    /* 
    echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
    echo "<input type=\"hidden\" name=\"new_echange_rtt\" value=1>\n"; */ 

    echo "</td>\n";
    echo "</tr>\n";
    echo "<tr align=\"center\">\n";
    echo "<td>\n";
    echo "<input type=\"text\" id=\"new_debut_disp\" name=\"new_debut_disp\" size=\"20\" readonly style=\"text-align:center;\"/><br>\n";

    echo "</td>\n";
    echo "<td></td>\n"; // correspond a colonne choix demi-journee 
    echo "<td>\n";
    echo "<input type=\"text\" id=\"new_fin_disp\" name=\"new_fin_disp\" size=\"20\" readonly style=\"text-align:center;\"/><br>\n";

    echo "</td>\n";

    echo "</tr>\n";

    echo "</table>\n";
    echo "</form>\n" ;

}

//affiche le formulaire de saisie d'une nouvelle demande de conges
// mise en old dans le cadre du chantier _jqcal
function atic_saisie_nouveau_conges($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet, $mysql_link, $DEBUG=FALSE)
{
//$DEBUG=TRUE;
	if($DEBUG==TRUE) { echo "user_login = $user_login, year_calendrier_saisie_debut = $year_calendrier_saisie_debut, mois_calendrier_saisie_debut = $mois_calendrier_saisie_debut, year_calendrier_saisie_fin = $year_calendrier_saisie_fin, mois_calendrier_saisie_fin = $mois_calendrier_saisie_fin, onglet = $onglet<br>\n";}

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	$mois_calendrier_saisie_debut_prec=0; $year_calendrier_saisie_debut_prec=0;
	$mois_calendrier_saisie_debut_suiv=0; $year_calendrier_saisie_debut_suiv=0;
	$mois_calendrier_saisie_fin_prec=0; $year_calendrier_saisie_fin_prec=0;
	$mois_calendrier_saisie_fin_suiv=0; $year_calendrier_saisie_fin_suiv=0;

	init_tab_jours_fermeture($user_login, $mysql_link);

//		echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n" ;
		echo "<form action=\"$PHP_SELF?session=$session&onglet=$onglet\" method=\"POST\">\n" ;
//		echo "<form action=\"$PHP_SELF?session=$session&login_user=$user_login\" method=\"POST\">\n" ;
		// il faut indiquer le champ de formulaire "login_user" car il est récupéré par le javascript qui apelle le calcul automatique.
//		echo "<input type=\"hidden\" name=\"login_user\" value=\"$user_login\">\n";

			echo "<table cellpadding=\"0\" cellspacing=\"5\" border=\"0\">\n";
			echo "<tr align=\"center\">\n";
			echo "<td>\n";
				echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
				echo "<tr align=\"center\">\n";
					echo "<td>\n";
					echo "<fieldset class=\"cal_saisie\">\n";
						echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
						echo "<tr align=\"center\">\n";
							echo "<td>\n";
								/******************************************************************/
								// affichage du calendrier de saisie de la date de DEBUT de congès
								/******************************************************************/
								echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"250\" border=\"0\">\n";
								echo "<tr>\n";
									init_var_navigation_mois_year($mois_calendrier_saisie_debut, $year_calendrier_saisie_debut,
												$mois_calendrier_saisie_debut_prec, $year_calendrier_saisie_debut_prec,
												$mois_calendrier_saisie_debut_suiv, $year_calendrier_saisie_debut_suiv,
												$mois_calendrier_saisie_fin, $year_calendrier_saisie_fin,
												$mois_calendrier_saisie_fin_prec, $year_calendrier_saisie_fin_prec,
												$mois_calendrier_saisie_fin_suiv, $year_calendrier_saisie_fin_suiv );

								// affichage des boutons de défilement
								// recul du mois saisie début
								echo "<td align=\"center\" class=\"big\">\n";
								echo "<a href=\"$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_debut_prec&mois_calendrier_saisie_debut=$mois_calendrier_saisie_debut_prec&year_calendrier_saisie_fin=$year_calendrier_saisie_fin&mois_calendrier_saisie_fin=$mois_calendrier_saisie_fin&user_login=$user_login&onglet=$onglet\">\n";
								echo " <img src=\"../img/simfirs.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".$_SESSION['lang']['divers_mois_precedent']."\" title=\"".$_SESSION['lang']['divers_mois_precedent']."\"> \n";
								echo "</a>\n";
								echo "</td>\n";

								echo "<td align=\"center\" class=\"big\">".$_SESSION['lang']['divers_debut_maj']." :</td>\n";

								// affichage des boutons de défilement
								// avance du mois saisie début
								// si le mois de saisie fin est antérieur ou égal au mois de saisie début, on avance les 2 , sinon on avance que le mois de saisie début
								if( (($year_calendrier_saisie_debut_suiv==$year_calendrier_saisie_fin) && ($mois_calendrier_saisie_debut_suiv>=$mois_calendrier_saisie_fin))
								    || ($year_calendrier_saisie_debut_suiv>$year_calendrier_saisie_fin)  )
									$lien_mois_debut_suivant = "$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_debut_suiv&mois_calendrier_saisie_debut=$mois_calendrier_saisie_debut_suiv&year_calendrier_saisie_fin=$year_calendrier_saisie_debut_suiv&mois_calendrier_saisie_fin=$mois_calendrier_saisie_debut_suiv&user_login=$user_login&onglet=$onglet" ;
								else
									$lien_mois_debut_suivant = "$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_debut_suiv&mois_calendrier_saisie_debut=$mois_calendrier_saisie_debut_suiv&year_calendrier_saisie_fin=$year_calendrier_saisie_fin&mois_calendrier_saisie_fin=$mois_calendrier_saisie_fin&user_login=$user_login&onglet=$onglet" ;
								echo "<td align=\"center\" class=\"big\">\n";
								echo "<a href=\"$lien_mois_debut_suivant\">\n";
								echo " <img src=\"../img/simlast.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".$_SESSION['lang']['divers_mois_suivant']."\" title=\"".$_SESSION['lang']['divers_mois_suivant']."\"> \n";
								echo "</a>\n";
								echo "</td>\n";


								echo "</tr>\n";
								echo "</table>\n";
								/*** calendrier saisie date debut ***/
								affiche_calendrier_saisie_date_debut($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $mysql_link, $DEBUG);
							echo "</td>\n";
							/**************************************************/
							/* cellule 2 : boutons radio matin ou après midi */
							echo "<td align=\"left\">\n";
								echo "<input type=\"radio\" name=\"new_demi_jour_deb\" ";
								if($_SESSION['config']['rempli_auto_champ_nb_jours_pris']==TRUE)
								{
									// attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
									if( (isset($_SERVER["HTTP_USER_AGENT"])) && (stristr($_SERVER["HTTP_USER_AGENT"], "MSIE 6")!=FALSE) )
										echo "onClick=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return true;\"" ;
									else
										echo "onChange=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;\"" ;
								}
								echo "value=\"am\" checked><b><u>".$_SESSION['lang']['form_am']."</u></b><br><br>\n";

								echo "<input type=\"radio\" name=\"new_demi_jour_deb\" ";
								if($_SESSION['config']['rempli_auto_champ_nb_jours_pris']==TRUE)
								{
									if( (isset($_SERVER["HTTP_USER_AGENT"])) && (stristr($_SERVER["HTTP_USER_AGENT"], "MSIE 6")!=FALSE) )
										echo "onClick=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return true;\"" ;
									else
										echo "onChange=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;\"" ;
								}
								echo "value=\"pm\"><b><u>".$_SESSION['lang']['form_pm']."</u></b><br><br>\n";
							echo "</td>\n";
							/**************************************************/
						echo "</tr>\n";
						echo "</table>\n";
					echo "</fieldset>\n";
					echo "</td>\n";
				echo "</tr>\n";
				echo "<tr align=\"center\">\n";
					echo "<td><img src=\"../img/shim.gif\" width=\"15\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\"></td>\n";
				echo "</tr>\n";
				echo "<tr align=\"center\">\n";
					echo "<td>\n";
					echo "<fieldset class=\"cal_saisie\">\n";
						echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
						echo "<tr align=\"center\">\n";
							echo "<td>\n";
								/******************************************************************/
								// affichage du calendrier de saisie de la date de FIN de congès
								/******************************************************************/
								echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"250\" border=\"0\">\n";
								echo "<tr>\n";
									if($mois_calendrier_saisie_fin==1) $mois_calendrier_saisie_fin_prec=12; else $mois_calendrier_saisie_fin_prec=$mois_calendrier_saisie_fin-1 ;
									if($mois_calendrier_saisie_fin==12) $mois_calendrier_saisie_fin_suiv=1; else $mois_calendrier_saisie_fin_suiv=$mois_calendrier_saisie_fin+1 ;

								// affichage des boutons de défilement
								// recul du mois saisie fin
								// si le mois de saisie fin est antérieur ou égal au mois de saisie début, on recule les 2 , sinon on recule que le mois de saisie fin
								if( (($year_calendrier_saisie_debut==$year_calendrier_saisie_fin_prec) && ($mois_calendrier_saisie_debut>=$mois_calendrier_saisie_fin_prec))
								    || ($year_calendrier_saisie_debut>$year_calendrier_saisie_fin_prec) )
								    $lien_mois_fin_precedent = "$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_fin_prec&mois_calendrier_saisie_debut=$mois_calendrier_saisie_fin_prec&year_calendrier_saisie_fin=$year_calendrier_saisie_fin_prec&mois_calendrier_saisie_fin=$mois_calendrier_saisie_fin_prec&user_login=$user_login&onglet=$onglet" ;
								else
									$lien_mois_fin_precedent = "$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_debut&mois_calendrier_saisie_debut=$mois_calendrier_saisie_debut&year_calendrier_saisie_fin=$year_calendrier_saisie_fin_prec&mois_calendrier_saisie_fin=$mois_calendrier_saisie_fin_prec&user_login=$user_login&onglet=$onglet" ;
								echo "<td align=\"center\" class=\"big\">\n";
								echo "<a href=\"$lien_mois_fin_precedent\">\n";
								echo " <img src=\"../img/simfirs.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".$_SESSION['lang']['divers_mois_precedent']."\" title=\"".$_SESSION['lang']['divers_mois_precedent']."\">\n";
								echo " </a>\n";
								echo "</td>\n";

								echo "<td align=\"center\" class=\"big\">".$_SESSION['lang']['divers_fin_maj']." :</td>\n";

								// affichage des boutons de défilement
								// avance du mois saisie fin
								echo "<td align=\"center\" class=\"big\">\n";
								echo "<a href=\"$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_debut&mois_calendrier_saisie_debut=$mois_calendrier_saisie_debut&year_calendrier_saisie_fin=$year_calendrier_saisie_fin_suiv&mois_calendrier_saisie_fin=$mois_calendrier_saisie_fin_suiv&user_login=$user_login&onglet=$onglet\">\n";
								echo " <img src=\"../img/simlast.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".$_SESSION['lang']['divers_mois_suivant']."\" title=\"".$_SESSION['lang']['divers_mois_suivant']."\"> \n";
								echo "</a>\n";
								echo "</td>\n";
								echo "</tr>\n";
								echo "</table>\n";
								/*** calendrier saisie date fin ***/
								affiche_calendrier_saisie_date_fin($user_login, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $mysql_link, $DEBUG);
							echo "</td>\n";
							/**************************************************/
							/* cellule 2 : boutons radio matin ou après midi */
							echo "<td align=\"left\">\n";
								echo "<input type=\"radio\" name=\"new_demi_jour_fin\" ";
								if($_SESSION['config']['rempli_auto_champ_nb_jours_pris']==TRUE)
								{
									// attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
									if( (isset($_SERVER["HTTP_USER_AGENT"])) && (stristr($_SERVER["HTTP_USER_AGENT"], "MSIE 6")!=FALSE) )
										echo "onClick=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return true;\"" ;
									else
										echo "onChange=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;\"" ;
								}
								echo "value=\"am\"><b><u>".$_SESSION['lang']['form_am']."</u></b><br><br>\n";

								echo "<input type=\"radio\" name=\"new_demi_jour_fin\"  ";
								if($_SESSION['config']['rempli_auto_champ_nb_jours_pris']==TRUE)
								{
									if( (isset($_SERVER["HTTP_USER_AGENT"])) && (stristr($_SERVER["HTTP_USER_AGENT"], "MSIE 6")!=FALSE) )
										echo "onClick=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return true;\"" ;
									else
										echo "onChange=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;\"" ;
								}
								echo "value=\"pm\" checked><b><u>".$_SESSION['lang']['form_pm']."</u></b><br><br>\n";
							echo "</td>\n";
							/**************************************************/
						echo "</tr>\n";
						echo "</table>\n";
					echo "</fieldset>\n";
					echo "</td>\n";
				echo "</tr>\n";
				echo "</table>\n";
			echo "</td>\n";
			echo "<td><img src=\"../img/shim.gif\" width=\"15\" height=\"2\" border=\"0\" vspace=\"0\" hspace=\"0\"></td>\n";
			echo "<td>\n";

				/*******************/
				/*   formulaire    */
				/*******************/
				echo "<table cellpadding=\"0\" cellspacing=\"2\" border=\"0\" >\n";
				echo "<tr>\n";
				echo "<td valign=\"top\">\n";
					echo "<table cellpadding=\"2\" cellspacing=\"3\" border=\"0\" >\n";
//					echo "<input type=\"hidden\" name=\"login_user\" value=\"".$_SESSION['userlogin']."\">\n";
					echo "<input type=\"hidden\" name=\"login_user\" value=\"$user_login\">\n";
					echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
					// bouton "compter les jours"
					if($_SESSION['config']['affiche_bouton_calcul_nb_jours_pris']==TRUE)
					{
						echo "<tr><td colspan=\"2\">\n";
							echo "<input type=\"button\" onclick=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;\" value=\"".$_SESSION['lang']['saisie_conges_compter_jours']."\">\n";
						echo "</td></tr>\n";
					}
					// zones de texte
					echo "<tr align=\"center\"><td><b>".$_SESSION['lang']['saisie_conges_nb_jours']."</b></td><td><b>".$_SESSION['lang']['divers_comment_maj_1']."</b></td></tr>\n";

					if($_SESSION['config']['disable_saise_champ_nb_jours_pris']==TRUE)  // zone de texte en readonly et grisée
						$text_nb_jours="<input type=\"text\" name=\"new_nb_jours\" size=\"10\" maxlength=\"30\" value=\"\" style=\"background-color: #D4D4D4; \" readonly=\"readonly\">" ;
					else
						$text_nb_jours="<input type=\"text\" name=\"new_nb_jours\" size=\"10\" maxlength=\"30\" value=\"\">" ;

					$text_commentaire="<input type=\"text\" name=\"new_comment\" size=\"25\" maxlength=\"30\" value=\"\">" ;
					echo "<tr align=\"center\">\n";
					echo "<td>$text_nb_jours</td><td>$text_commentaire</td>\n";
					echo "</tr>\n";
					echo "<tr align=\"center\"><td><img src=\"../img/shim.gif\" width=\"15\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\"></td><td></td></tr>\n";
					echo "<tr align=\"center\">\n";
					echo "<td colspan=2>\n";
						echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
						echo "<input type=\"hidden\" name=\"new_demande_conges\" value=1>\n";
						// boutons du formulaire
						echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">   <input type=\"reset\" value=\"".$_SESSION['lang']['form_cancel']."\">\n";
					echo "</td>\n";
					echo "</tr>\n";
					echo "</table>\n";

				echo "</td>\n";
				/*****************/
				/* boutons radio */
				/*****************/
				// recup d tableau des types de conges
				$tab_type_conges=recup_tableau_types_conges($mysql_link, $DEBUG);
                                $tab_type_conges_ordinaires=recup_tableau_types_conges_ordinaires($mysql_link, $DEBUG);//modif du 23 nov2012

				// recup du tableau des types d'absence
				$tab_type_absence=recup_tableau_types_absence($mysql_link, $DEBUG);

				// recup du tableau des types de conges exceptionnels
				$tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($mysql_link, $DEBUG);

				echo "<td align=\"left\" valign=\"top\">\n";
				// si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
				// OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
				if( (($_SESSION['config']['user_saisie_demande']==TRUE)&&($user_login==$_SESSION['userlogin'])) ||
			        (($_SESSION['config']['user_saisie_demande']==TRUE)&&($user_login!=$_SESSION['userlogin'])))
				 if(is_gest($_SESSION['userlogin'], $mysql_link, $DEBUG))// modif 16 nov 2012
				  {
					// congés
					echo "<b><i><u>".$_SESSION['lang']['divers_conges']." :</u></i></b><br>\n";
					foreach($tab_type_conges as $id => $libelle)
					{
						if($id==1)
							echo "<input type=\"radio\" name=\"new_type\" value=\"$id\" checked> $libelle<br>\n";
						else
							echo "<input type=\"radio\" name=\"new_type\" value=\"$id\"> $libelle<br>\n";
					}
				 }
				else
				{
					// congés
					echo "<b><i><u>".$_SESSION['lang']['divers_conges']." :</u></i></b><br>\n";
					foreach($tab_type_conges_ordinaires as $id => $libelle)
					{
						if($id==1)
							echo "<input type=\"radio\" name=\"new_type\" value=\"$id\" checked> $libelle<br>\n";
						else
							echo "<input type=\"radio\" name=\"new_type\" value=\"$id\"> $libelle<br>\n";
					}
				}
				// si le user a droit de saisir une mission ET si on est PAS dans une fenetre de responsable
				// OU si le resp a droit de saisir une mission ET si on est PAS dans une fenetre d'utilisateur
				// OU si le resp a droit de saisir une mission ET si le resp est resp de lui meme
				if( (($_SESSION['config']['user_saisie_mission']==TRUE)&&($user_login==$_SESSION['userlogin'])) ||
				    (($_SESSION['config']['resp_saisie_mission']==TRUE)&&($user_login!=$_SESSION['userlogin'])) ||
				    (($_SESSION['config']['resp_saisie_mission']==TRUE)&&(is_resp_of_user($_SESSION['userlogin'], $user_login, $mysql_link, $DEBUG)==TRUE)) )
				{
					echo "<br>\n";
					// absences
					echo "<b><i><u>".$_SESSION['lang']['divers_absences']." :</u></i></b><br>\n";
					foreach($tab_type_absence as $id => $libelle)
					{
						echo "<input type=\"radio\" name=\"new_type\" value=\"$id\"> $libelle<br>\n";
					}
				}
				// si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
				// OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
				if( ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) && (
				    (($_SESSION['config']['user_saisie_demande']==TRUE)&&($user_login==$_SESSION['userlogin'])) ||
				    (($_SESSION['config']['user_saisie_demande']==TRUE)&&($user_login!=$_SESSION['userlogin'])) ) )
				{
					echo "<br>\n";
					// congés exceptionnels
					echo "<b><i><u>".$_SESSION['lang']['divers_conges_exceptionnels']." :</u></i></b><br>\n";
					foreach($tab_type_conges_exceptionnels as $id => $libelle)
					{
						if($id==1)
							echo "<input type=\"radio\" name=\"new_type\" value=\"$id\" checked> $libelle<br>\n";
						else
							echo "<input type=\"radio\" name=\"new_type\" value=\"$id\"> $libelle<br>\n";
					}
				}

			echo "</td>\n";
			echo "</tr>\n";
			echo "</table>\n";

		echo "</form>\n" ;
}


//affiche le formulaire d'échange d'un jour de rtt-temps partiel / jour travaillé
function atic_saisie_echange_rtt($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	$mois_calendrier_saisie_debut_prec=0; $year_calendrier_saisie_debut_prec=0;
	$mois_calendrier_saisie_debut_suiv=0; $year_calendrier_saisie_debut_suiv=0;
	$mois_calendrier_saisie_fin_prec=0; $year_calendrier_saisie_fin_prec=0;
	$mois_calendrier_saisie_fin_suiv=0; $year_calendrier_saisie_fin_suiv=0;

	if($DEBUG==TRUE) { echo "param = $user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin <br>\n" ; }

	echo "<form action=\"$PHP_SELF?session=$session&&onglet=$onglet\" method=\"POST\">\n" ;

			echo "<table cellpadding=\"0\" cellspacing=\"5\" border=\"0\">\n";
			echo "<tr align=\"center\">\n";

			// cellule 1 : calendrier de saisie du jour d'absence
			echo "<td>\n";
				echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"250\">\n";
				echo "<tr>\n";
					init_var_navigation_mois_year($mois_calendrier_saisie_debut, $year_calendrier_saisie_debut,
								$mois_calendrier_saisie_debut_prec, $year_calendrier_saisie_debut_prec,
								$mois_calendrier_saisie_debut_suiv, $year_calendrier_saisie_debut_suiv,
								$mois_calendrier_saisie_fin, $year_calendrier_saisie_fin,
								$mois_calendrier_saisie_fin_prec, $year_calendrier_saisie_fin_prec,
								$mois_calendrier_saisie_fin_suiv, $year_calendrier_saisie_fin_suiv );

					// affichage des boutons de défilement
					// recul du mois saisie debut
					echo "<td align=\"center\" class=\"big\">\n";
					echo "<a href=\"$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_debut_prec&mois_calendrier_saisie_debut=$mois_calendrier_saisie_debut_prec&year_calendrier_saisie_fin=$year_calendrier_saisie_fin&mois_calendrier_saisie_fin=$mois_calendrier_saisie_fin&user_login=$user_login&onglet=$onglet\">\n";
					echo " <img src=\"../img/simfirs.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".$_SESSION['lang']['divers_mois_precedent']."\" title=\"".$_SESSION['lang']['divers_mois_precedent']."\"> \n";
					echo "</a>\n";
					echo "</td>\n";

					// titre du calendrier de saisie du jour d'absence
					echo "<td align=\"center\" class=\"big\">".$_SESSION['lang']['saisie_echange_titre_calendrier_1']." :</td>\n";

					// affichage des boutons de défilement
					// avance du mois saisie debut
					echo "<td align=\"center\" class=\"big\">\n";
					echo "<a href=\"$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_debut_suiv&mois_calendrier_saisie_debut=$mois_calendrier_saisie_debut_suiv&year_calendrier_saisie_fin=$year_calendrier_saisie_fin&mois_calendrier_saisie_fin=$mois_calendrier_saisie_fin&user_login=$user_login&onglet=$onglet\">\n";
					echo " <img src=\"../img/simlast.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".$_SESSION['lang']['divers_mois_suivant']."\" title=\"".$_SESSION['lang']['divers_mois_suivant']."\"> \n";
					echo "</a>\n";
					echo "</td>\n";
				echo "</tr>\n";
				echo "</table>\n";
				//*** calendrier saisie date debut ***/
				affiche_calendrier_saisie_jour_absence($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $mysql_link);
			echo "</td>\n";

			// cellule 2 : boutons radio 1/2 journée ou jour complet
			echo "<td>\n";
				echo "<input type=\"radio\" name=\"moment_absence_ordinaire\" value=\"a\"><b><u>".$_SESSION['lang']['form_am']."</u></b><input type=\"radio\" name=\"moment_absence_souhaitee\" value=\"a\"><br><br>\n";
				echo "<input type=\"radio\" name=\"moment_absence_ordinaire\" value=\"p\"><b><u>".$_SESSION['lang']['form_pm']."</u></b><input type=\"radio\" name=\"moment_absence_souhaitee\" value=\"p\"><br><br>\n";
				echo "<input type=\"radio\" name=\"moment_absence_ordinaire\" value=\"j\" checked><b><u>".$_SESSION['lang']['form_day']."</u></b><input type=\"radio\" name=\"moment_absence_souhaitee\" value=\"j\" checked><br>\n";
			echo "</td>\n";

			// cellule 3 : calendrier de saisie du jour d'absence
			echo "<td>\n";
				echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"250\">\n";
				echo "<tr>\n";
					if($mois_calendrier_saisie_fin==1) $mois_calendrier_saisie_fin_prec=12; else $mois_calendrier_saisie_fin_prec=$mois_calendrier_saisie_fin-1 ;
					if($mois_calendrier_saisie_fin==12) $mois_calendrier_saisie_fin_suiv=1; else $mois_calendrier_saisie_fin_suiv=$mois_calendrier_saisie_fin+1 ;

					// affichage des boutons de défilement
					// recul du mois saisie fin
					echo "<td align=\"center\" class=\"big\">\n";
					echo "<a href=\"$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_debut&mois_calendrier_saisie_debut=$mois_calendrier_saisie_debut&year_calendrier_saisie_fin=$year_calendrier_saisie_fin_prec&mois_calendrier_saisie_fin=$mois_calendrier_saisie_fin_prec&user_login=$user_login&onglet=$onglet\">\n";
					echo " <img src=\"../img/simfirs.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".$_SESSION['lang']['divers_mois_precedent']."\" title=\"".$_SESSION['lang']['divers_mois_precedent']."\"> \n";
					echo "</a>\n";
					echo "</td>\n";

					// titre du ecalendrier de saisie du jour d'absence
					echo "<td align=\"center\" class=\"big\">".$_SESSION['lang']['saisie_echange_titre_calendrier_2']." :</td>\n";

					// affichage des boutons de défilement
					// avance du mois saisie fin
					echo "<td align=\"center\" class=\"big\">\n";
					echo "<a href=\"$PHP_SELF?session=$session&year_calendrier_saisie_debut=$year_calendrier_saisie_debut&mois_calendrier_saisie_debut=$mois_calendrier_saisie_debut&year_calendrier_saisie_fin=$year_calendrier_saisie_fin_suiv&mois_calendrier_saisie_fin=$mois_calendrier_saisie_fin_suiv&user_login=$user_login&onglet=$onglet\">\n";
					echo " <img src=\"../img/simlast.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".$_SESSION['lang']['divers_mois_suivant']."\" title=\"".$_SESSION['lang']['divers_mois_suivant']."\"> \n";
					echo "</a>\n";
					echo "</td>\n";
				echo "</tr>\n";
				echo "</table>\n";

				//*** calendrier saisie date fin ***/
				affiche_calendrier_saisie_jour_presence($user_login, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $mysql_link);
			echo "</td>\n";

			echo "</tr>\n";
			echo "<tr align=\"center\">\n";

			// cellule 1 : champs texte et boutons (valider/cancel)
			echo "<td colspan=3>\n";

				/***  formulaire ***/
					echo "<table cellpadding=\"2\" cellspacing=\"3\" border=\"0\" >\n";
					echo "<tr align=\"center\">\n";
						echo "<td><b>".$_SESSION['lang']['divers_comment_maj_1']." : </b></td>\n";
						$text_commentaire="<input type=\"text\" name=\"new_comment\" size=\"25\" maxlength=\"30\" value=\"\">" ;
						echo "<td>$text_commentaire</td>\n";
					echo "</tr>\n";
					echo "<tr align=\"center\">\n";
						echo "<td colspan=2><img src=\"../img/shim.gif\" width=\"15\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\"></td>\n";
					echo "</tr>\n";
					echo "<tr align=\"center\">\n";
						echo "<td colspan=2>\n";
							echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\">\n";
							echo "<input type=\"hidden\" name=\"new_echange_rtt\" value=1>\n";
							echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">   <input type=\"reset\" value=\"".$_SESSION['lang']['form_cancel']."\">\n";
						echo "</td>\n";
					echo "</tr>\n";
					echo "</table>\n";


			echo "</td>\n";
			echo "</tr>\n";
			echo "</table>\n";

		echo "</form>\n" ;
}


// initialisation des variables pour la mavigation mois précédent / mois suivant
// certains arguments sont passés par référence (avec &) car on change leur valeur
function init_var_navigation_mois_year(	$mois_calendrier_saisie_debut, $year_calendrier_saisie_debut,
								&$mois_calendrier_saisie_debut_prec, &$year_calendrier_saisie_debut_prec,
								&$mois_calendrier_saisie_debut_suiv, &$year_calendrier_saisie_debut_suiv,
								$mois_calendrier_saisie_fin, $year_calendrier_saisie_fin,
								&$mois_calendrier_saisie_fin_prec, &$year_calendrier_saisie_fin_prec,
								&$mois_calendrier_saisie_fin_suiv, &$year_calendrier_saisie_fin_suiv )
{
	if($mois_calendrier_saisie_debut==1) {
		$mois_calendrier_saisie_debut_prec=12;
		$year_calendrier_saisie_debut_prec=$year_calendrier_saisie_debut-1 ;
	}
	else {
		$mois_calendrier_saisie_debut_prec=$mois_calendrier_saisie_debut-1 ;
		$year_calendrier_saisie_debut_prec=$year_calendrier_saisie_debut ;
	}
	if($mois_calendrier_saisie_debut==12) {
		$mois_calendrier_saisie_debut_suiv=1;
		$year_calendrier_saisie_debut_suiv=$year_calendrier_saisie_debut+1 ;
	}
	else {
		$mois_calendrier_saisie_debut_suiv=$mois_calendrier_saisie_debut+1 ;
		$year_calendrier_saisie_debut_suiv=$year_calendrier_saisie_debut ;
	}

	if($mois_calendrier_saisie_fin==1) {
		$mois_calendrier_saisie_fin_prec=12;
		$year_calendrier_saisie_fin_prec=$year_calendrier_saisie_fin-1 ;
	}
	else {
		$mois_calendrier_saisie_fin_prec=$mois_calendrier_saisie_fin-1 ;
		$year_calendrier_saisie_fin_prec=$year_calendrier_saisie_fin ;
	}
	if($mois_calendrier_saisie_fin==12) {
		$mois_calendrier_saisie_fin_suiv=1;
		$year_calendrier_saisie_fin_suiv=$year_calendrier_saisie_fin+1 ;
	}
	else {
		$mois_calendrier_saisie_fin_suiv=$mois_calendrier_saisie_fin+1 ;
		$year_calendrier_saisie_fin_suiv=$year_calendrier_saisie_fin ;
	}
}


// affiche une chaine représentant un decimal sans 0 à la fin ...
// (un point separe les unités et les decimales et on ne considere que 2 decimales !!!)
// ex : 10.00 devient 10  , 5.50 devient 5.5  , et 3.05 reste 3.05
function affiche_decimal($str, $DEBUG=FALSE)
{
	$champs=explode(".", $str);
	$int=$champs[0];
	$decimal="00";
	if (count($champs)>1)
		$decimal = $champs[1];
	//$decimal=$champs[1];

	if($decimal=="00")
		return $int ;
	elseif (ereg("([0-9])([1-9])", $decimal))
		return $str;
	elseif (ereg("([0-9])0", $decimal, $regs))
		return $int.".".$regs[1] ;
	else {
		echo "ERREUR: affiche_decimal($str) : $str n'a pas le format attendu !!!!<br>\n";
		exit;
	}
}


// verif validité des valeurs saisies lors d'une demande de congespar un user ou d'une saisie de conges par le responsable
//  (attention : le $new_nb_jours est passé par référence car on le modifie si besoin)
function verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, &$new_nb_jours, $new_comment, $DEBUG=FALSE)
{
	$verif=TRUE ;

	// leur champs doivent etre renseignés dans le formulaire
	if( ($new_debut=="") || ($new_fin=="") || ($new_nb_jours=="") ) {
		echo "<br>".$_SESSION['lang']['verif_saisie_erreur_valeur_manque']."<br>\n";
		$verif=FALSE ;
	}

	if ( !ereg( "([0-9]+)([\.\,]*[0-9]{1,2})*", $new_nb_jours) ) {
		echo "<br>".$_SESSION['lang']['verif_saisie_erreur_nb_jours_bad']."<br>\n";
		$verif=FALSE ;
	}
	else {
		if( ereg( "([0-9]+)\,([0-9]{1,2})", $new_nb_jours, $reg) )
			$new_nb_jours=$reg[1].".".$reg[2];    // on remplace la virgule par un point pour les décimaux
	}

	// si la date de fin est antéreieure à la date debut
	if(strnatcmp($new_debut, $new_fin)>0) {
		echo "<br>".$_SESSION['lang']['verif_saisie_erreur_fin_avant_debut']."<br>\n";
		$verif=FALSE ;
	}

	// si la date debut et fin = même jour mais début=après midi et fin=matin !!
	if((strnatcmp($new_debut, $new_fin)==0)&&($new_demi_jour_deb=="pm")&&($new_demi_jour_fin=="am") ) {
		echo "<br>".$_SESSION['lang']['verif_saisie_erreur_debut_apres_fin']."<br>\n";
		$verif=FALSE ;
	}

	return $verif;
}


// renvoit la couleur de fond du jour indiquï¿½par le timestamp
// (une couleur pour les jours de semaine et une pour les jours de week end)
function get_bgcolor_of_the_day_in_the_week($timestamp_du_jour)
{
	$j_name=date("D", $timestamp_du_jour);

	if(($j_name=="Sat") || ($j_name=="Sun"))
		return $_SESSION['config']['week_end_bgcolor'];
	else
		return $_SESSION['config']['semaine_bgcolor'];

}


// renvoit la class de cellule du jour indiquée par le timestamp
// (une classe pour les jours de semaine et une pour les jours de week end)
function get_td_class_of_the_day_in_the_week($timestamp_du_jour)
{
	$j_name=date("D", $timestamp_du_jour);
	$j_date=date("Y-m-d", $timestamp_du_jour);

	if( (($j_name=="Sat")&&($_SESSION['config']['samedi_travail']==FALSE))
	|| (($j_name=="Sun")&&($_SESSION['config']['dimanche_travail']==FALSE))
	|| (est_chome($timestamp_du_jour)==TRUE) || (est_ferme($timestamp_du_jour)==TRUE) )
		return "weekend";
	else
		return "semaine";
}


//
// affichage bouton de déconnexion
function   bouton_deconnexion($DEBUG=FALSE)
{
   $session=session_id();

	echo "<a href=\"../deconnexion.php?session=$session\" target=\"_top\">" .
			"<img src=\"../img/exit.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['button_deconnect']."\" alt=\"".$_SESSION['lang']['button_deconnect']."\">" .
			"</a> ".$_SESSION['lang']['button_deconnect']."\n";

}

// affichage bouton actualiser la page
function bouton_actualiser($onglet, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	echo "<a href=\"$PHP_SELF?session=$session&onglet=$onglet\">\n";
	echo "<img src=\"../img/reload_page.png\" width=\"22\" height=\"22\" border=\"0\" title=\"".$_SESSION['lang']['button_refresh']."\" alt=\"".$_SESSION['lang']['button_refresh']."\">\n";
	echo "</a> ".$_SESSION['lang']['button_refresh']."\n";
}



// recup des infos ARTT ou Temps Partiel :
// attention : les param $val_matin et $val_aprem sont passées par référence (avec &) car on change leur valeur
function recup_infos_artt_du_jour($sql_login, $j_timestamp, &$val_matin, &$val_aprem, $mysql_link, $DEBUG=FALSE)
{
	$num_semaine=date("W", $j_timestamp);
	$jour_name_fr_2c=get_j_name_fr_2c($j_timestamp); // nom du jour de la semaine en francais sur 2 caracteres

	// on ne cherche pas d'artt les samedis ou dimanches quand il ne sont pas travaillés (cf config de php_conges)
	if( (($jour_name_fr_2c=="sa")&&($_SESSION['config']['samedi_travail']==FALSE)) || (($jour_name_fr_2c=="di")&&($_SESSION['config']['dimanche_travail']==FALSE)) )
	{
		// on ne cherche pas d'artt les samedis ou dimanches quand ils ne sont pas travaillés
	}
	else
	{
		// verif si le jour fait l'objet d'un echange ....
		$date_j=date("Y-m-d", $j_timestamp);
		$sql_echange_rtt="SELECT e_absence FROM conges_echange_rtt WHERE e_login='$sql_login' AND e_date_jour='$date_j' ";
		$res_echange_rtt = requete_mysql($sql_echange_rtt, $mysql_link, "recup_infos_artt_du_jour", $DEBUG);

		$num_echange_rtt = mysql_num_rows($res_echange_rtt);
		// si le jour est l'objet d'un echange, on tient compte de l'échange
		if($num_echange_rtt!=0)
		{
			$result_echange_rtt = mysql_fetch_array($res_echange_rtt);
			if ($result_echange_rtt["e_absence"]=='J') // jour entier
			{
				$val_matin='Y';
				$val_aprem='Y';
			}
			elseif ($result_echange_rtt["e_absence"]=='M') // matin
			{
				$val_matin='Y';
				$val_aprem='N';
			}
			elseif ($result_echange_rtt["e_absence"]=='A') // apres-midi
			{
				$val_matin='N';
				$val_aprem='Y';
			}
			else
			{
				$val_matin='N';
				$val_aprem='N';
			}
		}
		// sinon, on lit la table conges_artt normalement
		else
		{
			$parite_semaine=($num_semaine % 2);   //(modulo) =1 si sem impaire, =o si sem paire
			if ($parite_semaine==0) $par_sem="p"; else $par_sem="imp" ;

			//on calcule la key du tableau $result_artt qui correspond au jour j que l'on est en train d'afficher
			$key_artt_matin="sem_".$par_sem."_".$jour_name_fr_2c."_am" ;
			$key_artt_aprem="sem_".$par_sem."_".$jour_name_fr_2c."_pm" ;

			// recup des ARTT et temps-partiels du user
			$sql_artt="SELECT $key_artt_matin, $key_artt_aprem FROM conges_artt
						WHERE a_login='$sql_login' AND a_date_debut_grille<='$date_j' AND a_date_fin_grille>='$date_j' ";
			$res_artt = requete_mysql($sql_artt, $mysql_link, "recup_infos_artt_du_jour", $DEBUG);
			$result_artt = mysql_fetch_array($res_artt);
			if($result_artt["$key_artt_matin"]=='Y')
				$val_matin='Y';
			else
				$val_matin='N';
			if($result_artt["$key_artt_aprem"]=='Y')
				$val_aprem='Y';
			else
				$val_aprem='N';
		}
	}
}


// recup des infos ARTT ou Temps Partiel :
// attention : les param $val_matin et $val_aprem sont passées par référence (avec &) car on change leur valeur
function recup_infos_artt_du_jour_from_tab($sql_login, $j_timestamp, &$val_matin, &$val_aprem, $tab_rtt_echange, $tab_rtt_planifiees, $mysql_link, $DEBUG=FALSE)
{

	//$tab_rtt_echange  //tableau indexé dont la clé est la date sous forme yyyy-mm-dd
						//il contient pour chaque clé (chaque jour): un tableau indéxé ($tab_jour_rtt_echange) (clé= login)
						// qui contient lui même un tableau ($tab_echange) contenant les infos des echanges de rtt pour ce
						// jour et ce login (valeur du matin + valeur de l'apres midi ('Y' si rtt, 'N' sinon) )
	//$tab_rtt_planifiees  //tableau indexé dont la clé est le login_user
					// il contient pour chaque clé login : un tableau ($tab_user_grille) indexé dont la
					// clé est la date_fin_grille.
					// qui contient lui meme pour chaque clé : un tableau ($tab_user_rtt) qui contient enfin
					// les infos pour le matin et l'après midi ('Y' si rtt, 'N' sinon) sur 2 semaines
					// ( du sem_imp_lu_am au sem_p_ve_pm ) + la date de début et de fin de la grille

	$num_semaine=date("W", $j_timestamp);
	$jour_name_fr_2c=get_j_name_fr_2c($j_timestamp); // nom du jour de la semaine en francais sur 2 caracteres

	// on ne cherche pas d'artt les samedis ou dimanches quand il ne sont pas travaillés (cf config de php_conges)
	if( (($jour_name_fr_2c=="sa")&&($_SESSION['config']['samedi_travail']==FALSE)) || (($jour_name_fr_2c=="di")&&($_SESSION['config']['dimanche_travail']==FALSE)) )
	{
		// on ne cherche pas d'artt les samedis ou dimanches quand il ne sont pas travaillés
	}
	else
	{
		// verif si le jour fait l'objet d'un echange ....
		$date_j=date("Y-m-d", $j_timestamp);
		//echo "$date_j<br>\n";
		// si le jour est l'objet d'un echange, on tient compte de l'échange
		$tab_day=$tab_rtt_echange[$date_j];  // on recup le tableau du jour
		if(array_key_exists($sql_login, $tab_day))   // si la periode correspond au user que l'on est en train de traiter
		{
			$val_matin=$tab_day[$sql_login]["val_matin"];
			$val_aprem=$tab_day[$sql_login]["val_aprem"];
		}
		// sinon, on lit la table conges_artt normalement
		else
		{
			$parite_semaine=($num_semaine % 2);   //(modulo) =1 si sem impaire, =o si sem paire
			if ($parite_semaine==0) $par_sem="p"; else $par_sem="imp" ;

			//on calcule la key du tableau $result_artt qui correspond au jour j que l'on est en train d'afficher
			$key_artt_matin="sem_".$par_sem."_".$jour_name_fr_2c."_am" ;
			$key_artt_aprem="sem_".$par_sem."_".$jour_name_fr_2c."_pm" ;

			// recup des ARTT et temps-partiels du user :
			// recup des grille du user
			$tab_grille_user=$tab_rtt_planifiees[$sql_login];
			// parcours du tableau des grille pour trouver la key qui correspond à la bonne période
			foreach ($tab_grille_user as $key => $value) {
				if( ($date_j>=$value["date_debut_grille"]) && ($date_j<=$value["date_fin_grille"]) ) // date_jour comprise entre date_deb_grille et date_fin grille
				{
					$val_matin=$value[$key_artt_matin];
					$val_aprem=$value[$key_artt_aprem];
					//echo "$sql_login : ".$value["login"]."<br>\n";
				}
				else
				{
					// ne fait rien
				}
			}
		}
	}
}




// verif validité d'un nombre saisi (decimal ou non)
//  (attention : le $nombre est passé par référence car on le modifie si besoin)
function verif_saisie_decimal(&$nombre, $DEBUG=FALSE)
{
	$verif=TRUE ;

	if ( !ereg( "^-?([0-9]+)([\.\,]?[0-9]?[0-9]?)", $nombre) )
	{
		echo "<br>".$_SESSION['lang']['verif_saisie_erreur_nb_bad']." ($nombre)<br>\n";
		$verif=FALSE ;
	}
	else
	{
		if( ereg( "^([0-9]+)\,([0-9]{1,2})", $nombre, $reg) )
			$nombre=$reg[1].".".$reg[2];    // on remplace la virgule par un point pour les décimaux
		elseif( ereg( "^-([0-9]+)\,([0-9]{1,2})", $nombre, $reg) )
			$nombre="-".$reg[1].".".$reg[2];    // on remplace la virgule par un point pour les décimaux
	}

	return $verif;
}



// donne la date en francais (dans la langue voulue)(meme formats que la fonction PHP date() cf manuel php)
function date_fr($code, $timestmp)
{
	$les_mois_longs  = array("pas_de_zero", $_SESSION['lang']['janvier'], $_SESSION['lang']['fevrier'], $_SESSION['lang']['mars'], $_SESSION['lang']['avril'],
								$_SESSION['lang']['mai'], $_SESSION['lang']['juin'], $_SESSION['lang']['juillet'], $_SESSION['lang']['aout'],
								$_SESSION['lang']['septembre'], $_SESSION['lang']['octobre'], $_SESSION['lang']['novembre'], $_SESSION['lang']['decembre']);

	$les_jours_longs  = array($_SESSION['lang']['dimanche'], $_SESSION['lang']['lundi'], $_SESSION['lang']['mardi'], $_SESSION['lang']['mercredi'],
								$_SESSION['lang']['jeudi'], $_SESSION['lang']['vendredi'], $_SESSION['lang']['samedi']);
	$les_jours_courts = array($_SESSION['lang']['dimanche_short'], $_SESSION['lang']['lundi_short'], $_SESSION['lang']['mardi_short'],
								$_SESSION['lang']['mercredi_short'], $_SESSION['lang']['jeudi_short'], $_SESSION['lang']['vendredi_short'], $_SESSION['lang']['samedi_short']);

	switch ($code) {
		case "F":
			$tmp = date("n", $timestmp);
			return $les_mois_longs[$tmp];
			break;

		case "l":
			$tmp = date("w", $timestmp);
			return $les_jours_longs[$tmp];
			break;

		case "D":
			$tmp = date("w", $timestmp);
			return $les_jours_courts[$tmp];
			break;

		default:
			return date($code, $timestmp);
			break;
	}
}



// envoi d'un message d'avertissement
// parametre 1=login de l'expéditeur
// parametre 2=login du destnataire (ou ":responsable:" si envoi au(x) responsable(s))
// parametre 3= absence concernée ATTENTION : _dpa  
// soit le numero de periode (conges_periodes) ou array(deb,fin) de l'echange
// d'absence 
// parametre 4=objet du message (cf table conges_mail pour les diff valeurs possibles)
function alerte_mail($login_expediteur, $destinataire, $operiode, $objet, $mysql_link, $DEBUG=FALSE)
{
  error_log("alerte_mail:".$login_expediteur."-".$destinataire."-".print_r($operiode,True)."-".$objet);
  //  $DEBUG=TRUE;
  if ($DEBUG) {  // _dpa 
    if (has_int_calendar_mode($destinataire, $mysql_link)) {
      $hintcalendar = "intcalendar" ;
    } else {
      $hintcalendar = "nointcalendar" ;
    };
    echo "<pre>".$login_expediteur." ".$destinataire." ". $operiode." ". $objet.
      " ".$hintcalendar. "<br> " ;
    print_r($operiode) ; 
    echo "</pre>";  
  };
	$phpmailer_filename = $_SESSION['config']['php_conges_phpmailer_include_path']."/phpmailer/class.phpmailer.php";
	// verif si la librairie phpmailer est présente
	if(!is_readable($phpmailer_filename))
	{
		echo $_SESSION['lang']['phpmailer_not_valid']."<br> !";
	}
	else
	{
		require_once($phpmailer_filename);	// ajout de la classe phpmailer


		/*********************************************/
		// recup des infos concernant l'expéditeur ....
		$mail_array=find_email_adress_for_user($login_expediteur, $mysql_link, $DEBUG);
		$mail_sender_name = $mail_array[0];
		$mail_sender_addr = $mail_array[1];

		/*********************************************/
		// recherche des infos concernant le destinataire ...
		// recherche du login du (des) destinataire(s) dans la base
		$dest_mail="";
		if($destinataire==":responsable:")  // c'est un message au responsable
		{
//			$tab_resp=array();
//			get_tab_resp_du_user($login_expediteur, $tab_resp, $mysql_link, $DEBUG);
			$tab_resp=get_tab_resp_du_user($login_expediteur, $mysql_link, $DEBUG);
			if( $DEBUG==TRUE ) { echo "tab_resp :<br>"; print_r($tab_resp); echo "<br>\n"; }

			foreach($tab_resp as $item_login => $item_presence)
			{
				// recherche de l'adresse mail du (des) responsable(s) :
              $mail_array_dest=find_email_adress_for_user($item_login, $mysql_link, $DEBUG);
              $mail_dest_name = $mail_array_dest[0];
              $mail_dest_addr = $mail_array_dest[1];
              if( $DEBUG==TRUE )
                echo "TO = $mail_dest_addr<br>\n";

              if($mail_dest_addr=="") {
                echo "<b>ERROR : $mail_dest_name : no mail address !</b><br>\n";
              } else {
                  // on change l'objet si c'est un "new_demande" à un resp absent et qu'on gere les absence de resp !
                if( ($_SESSION['config']['gestion_cas_absence_responsable']==TRUE) && 
                      ($item_presence=="absent") && ($objet=="new_demande") ) {  
                    // $objet="new_demande" bug1
                  $new_objet="new_demande_resp_absent";
                } else { 
                  $new_objet=$objet;
                }; 

                  // _dpa_dpa  construct_and_send_mail($new_objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $operiode, $destinataire, $mysql_link, $DEBUG);
                construct_and_send_mail(
                    $new_objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, 
                    $mail_dest_addr, $operiode, $login_expediteur, $mysql_link, $DEBUG );
              }
			}
            // ac3 : _todo : forge un log pour suivre workflow user - > responsable
            if ($objet=="new_demande") { 
              $sccoment = "a valider par: " ; 
              $l_resp = array_keys($tab_resp) ;
              sort($l_resp); 
              $sccoment .= join(',', $l_resp) ; 
              log_action(0, "demande", $login_expediteur, $sccoment, $mysql_link, $DEBUG);
            };
		}
		else   // c'est un message du responsable à un user
		{
			$dest_login = $destinataire ;
			$mail_array_dest=find_email_adress_for_user($dest_login, $mysql_link, $DEBUG);
			$mail_dest_name = $mail_array_dest[0];
			$mail_dest_addr = $mail_array_dest[1];
			if( $DEBUG==TRUE )
				echo "TO = $mail_dest_addr<br>\n";

			if($mail_dest_addr=="")
				echo "<b>ERROR : $mail_dest_name : no mail address !</b><br>\n";
			else
				construct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $operiode, $destinataire, $mysql_link, $DEBUG);

			/****************************/
			if($objet=="valid_conges")  // c'est un mail de première validation de demande : il faut faire une copie au(x) grand(s) responsable(s)
			{
				// on recup la liste des grands resp du user
				$tab_grd_resp=array();
				get_tab_grd_resp_du_user($dest_login, $tab_grd_resp, $mysql_link, $DEBUG);
				if( $DEBUG==TRUE ) { echo "tab_grd_resp :<br>"; print_r($tab_grd_resp); echo "<br>\n"; }

				if(count($tab_grd_resp)!=0)  // si tableau n'est pas vide
				{
					foreach($tab_grd_resp as $item_login)
					{
						// recherche de l'adresse mail du (des) responsable(s) :
						$mail_array_dest=find_email_adress_for_user($item_login,  $mysql_link, $DEBUG);
						$mail_dest_name = $mail_array_dest[0];
						$mail_dest_addr = $mail_array_dest[1];
						if( $DEBUG==TRUE )
							echo "TO = $mail_dest_addr<br>\n";

						if($mail_dest_addr=="")
							echo "<b>ERROR : $mail_dest_name : no mail address !</b><br>\n";
						else
							construct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $operiode, $destinataire, $mysql_link, $DEBUG);
					}
				}
			}
		}
	}
}

// construit et envoie le mail
// _intagenda : la signature change ; subjectlogin = compte sujet du changement 
function construct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode, $subjectlogin, $mysql_link, $DEBUG=FALSE)
{
  if( $DEBUG==TRUE ) {echo "construct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode, $subjectlogin, )<br>\n";}

  // _intagenda 
  if (array_key_exists('calendar_tag', $_SESSION['config'])) {
    $calendartag = $_SESSION['config']['calendar_tag'] ;
  } else {
    // par default 
    $calendartag = "sigp-conges"; 
  } ;

  /*********************************************/
  // init du mail
  $mail = new PHPMailer();
  if($_SESSION['config']['serveur_smtp']=="")
    {
			if(file_exists("/usr/sbin/sendmail"))
				$mail->IsSendmail();   // send message using the $Sendmail program
			elseif(file_exists("/var/qmail/bin/sendmail"))
				$mail->IsQmail();      // send message using the qmail MTA
			else
				$mail->IsMail();       // send message using PHP mail() function
		}
		else
		{
			$mail->IsSMTP();
			$mail->Host = $_SESSION['config']['serveur_smtp'];
		}

		// initialisation du langage utilisé par php_mailer
		$mail->SetLanguage("fr", $_SESSION['config']['php_conges_phpmailer_include_path']."/phpmailer/language/");

		if( $DEBUG==TRUE )
			echo "FROM = $mail_sender_name : $mail_sender_addr<br>\n";

		$mail->FromName = $mail_sender_name;
		$mail->From = $mail_sender_addr;

		$mail->AddAddress($mail_dest_addr);

        $labs_as_calendar = array("cal_content","cal_name"); 
        // default value for $labs_as_calendar to avoid error  

		/*********************************************/
		// recup des infos de l'absence  ou echange de rtt
        // if ($objet == "echange_absence_rtt")  {  alors $num_periode est un array 
        if (is_array($num_periode)) { // bug1
          // on doit definir $sql_nb_jours,$sql_date_deb,$sql_date_fin,$sql_commentaire,$sql_type_absence
          $sql_nb_jours = "" ;
          $sql_date_deb = "" ;
          $sql_date_fin = "" ;
          $sql_commentaire = "" ; 
          $sql_type_absence  = "echange_artt" ;
          $select_c_echanges_rtt = "select e_login, e_date_jour, e_absence, e_presence, e_comment from conges_echange_rtt where e_login = '$subjectlogin' and e_date_jour = " ;

          $req_cer_fin = requete_mysql($select_c_echanges_rtt . "'".$num_periode['fin']."'",
                                       $mysql_link, "construct_and_send_mail", $DEBUG);
          $res_cer_fin = mysql_fetch_array($req_cer_fin);  // doit exister ! 
          // echo "res_cer_fin = <br>" ;
          // print_r($res_cer_fin); 
          $sql_commentaire = $res_cer_fin["e_comment"] ; 
          $sql_date_fin = join( " / ", explode("-", $num_periode['fin'] ));
          $fin_absence = $res_cer_fin["e_absence"] ;
          // echo "fin_absence =" . $fin_absence ."<br>" ; 
          if ($fin_absence == "M") {
            $sql_date_fin .= " (am)" ;
            $sql_nb_jours = 0.5 ; 
          } elseif ($fin_absence == "A") {
            $sql_date_fin .= " (pm)" ;
            $sql_nb_jours = 0.5 ; 
          } elseif ($fin_absence == "J") {
            $sql_nb_jours = 1.0 ;             
          } else {
            // print erreur 
            error_log("fonction_conges::construct_and_send_mail type absence artt indefinie ".
                      $fin_absence); 
          };
          // _todo 
          $req_cer_deb = requete_mysql($select_c_echanges_rtt . "'".$num_periode['deb']."'",
                                       $mysql_link, "construct_and_send_mail", $DEBUG);
          $res_cer_deb = mysql_fetch_array($req_cer_deb);
          $sql_date_deb = join( " / ", explode("-", $num_periode['deb'] ));
          $deb_presence = $res_cer_deb["e_presence"] ;
          if ($deb_presence == "M") {
            $sql_date_deb .= " (am)" ;
          } elseif ($deb_presence == "A") {
            $sql_date_deb .= " (pm)" ;
          } elseif ($deb_presence == "J") {
          } else {
            // print erreur 
            error_log("fonction_conges::construct_and_send_mail type absence artt indefinie ".
                      $deb_presence); 
          };
          

        } else {    // ! is_array($num_periode) $num_periode est numerique 
          // _dpa_opti : on decompose la requete versus jointure 
          $select_abs_not_used ="SELECT conges_periode.p_login, 
                        conges_periode.p_date_deb,
						conges_periode.p_demi_jour_deb,
						conges_periode.p_date_fin,
						conges_periode.p_demi_jour_fin,
						conges_periode.p_nb_jours,
						conges_periode.p_commentaire,
						conges_type_absence.ta_libelle
					FROM   conges_periode, conges_type_absence
					WHERE  conges_periode.p_num=$num_periode
							AND    conges_periode.p_type = conges_type_absence.ta_id";
          $select_abs  ="SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin,
						p_nb_jours,	p_commentaire, p_type
					FROM   conges_periode
					WHERE  p_num=$num_periode ;" ;          
          $res_abs = requete_mysql($select_abs, $mysql_link, "construct_and_send_mail", $DEBUG);
          $rec_abs = mysql_fetch_array($res_abs);
          $p_type = $rec_abs["p_type"];

          $select_typea = "SELECT  ta_libelle FROM  conges_type_absence WHERE ta_id = $p_type ;" ; 
          $res_typabs = requete_mysql($select_typea, $mysql_link, "construct_and_send_mail", $DEBUG);
          $rec_typabs = mysql_fetch_array($res_typabs);

          $tab_date_deb= explode("-", $rec_abs["p_date_deb"]);
          // affiche : "23 / 01 / 2008 (am)"
          $sql_date_deb = $tab_date_deb[2]." / ".$tab_date_deb[1]." / ".$tab_date_deb[0]." (".$rec_abs["p_demi_jour_deb"].")" ;
          $tab_date_fin= explode("-", $rec_abs["p_date_fin"]);
          // affiche : "23 / 01 / 2008 (am)"
          $sql_date_fin = $tab_date_fin[2]." / ".$tab_date_fin[1]." / ".$tab_date_fin[0]." (".$rec_abs["p_demi_jour_fin"].")" ;
          $sql_nb_jours = $rec_abs["p_nb_jours"];
          $sql_commentaire = $rec_abs["p_commentaire"];
          $sql_type_absence = $rec_typabs["ta_libelle"];
          // _intcalendar : process d'un calendar item uniquement 
          // si objet = accept_conges ou annul_conges
          // labs_as_calendar array of (calendar_item as ical/ics string , calendar_item name)
          if (has_int_calendar_mode($subjectlogin,$mysql_link)) {
            if (($objet=="accept_conges") || ($objet=="annul_conges"))  {
              $applicationtag = $_SESSION['config']['installed_version'] ; 
              // for test only 
              $ulogin = $rec_abs["p_login"]; // for test "didier.pavet" ; 
              $umailaddr = $mail_dest_addr; //  for test "didier.pavet@aviation-civile.gouv.fr"
              if ($objet=="accept_conges") {
                $method = "REQUEST" ; 
              } elseif ($objet=="annul_conges") {
                $method = "CANCEL" ;
              } else {
                // error 
              }
              //  $calendartag by default "sigp-conges"
              $labs_as_calendar = abs2calendar_item( 
                    $calendartag,$ulogin, $umailaddr, $method, $num_periode,
                    $rec_abs["p_date_deb"],$rec_abs["p_demi_jour_deb"],
                    $rec_abs["p_date_fin"],$rec_abs["p_demi_jour_fin"],
                    $sql_nb_jours, $sql_commentaire, $sql_type_absence) ; 
            }; 
          }; 
        }; 
		/*********************************************/
		// construction des sujets et corps des messages
		if($objet=="valid_conges")
		{
			$key1="mail_prem_valid_conges_sujet" ;
			$key2="mail_prem_valid_conges_contenu" ;
		}
		elseif($objet=="accept_conges")
		{
			$key1="mail_valid_conges_sujet" ;
			$key2="mail_valid_conges_contenu" ;
		}
		elseif($objet=="new_demande_resp_absent")
		{
			$key1="mail_new_demande_resp_absent_sujet" ;
			$key2="mail_new_demande_resp_absent_contenu" ;
		}
		elseif($objet=="echange_absence_rtt")          // modif SA
		{
			$key1="mail_echange_rtt_sujet" ;
			$key2="mail_echange_rtt_contenu" ;
		} // modif SA

		else  // $objet== "refus_conges" ou "new_demande" ou "annul_conges"
		{
			$key1="mail_".$objet."_sujet" ;
			$key2="mail_".$objet."_contenu" ;
		}
        // error_log("_dpael session config is".$_SESSION['config']); 
		$sujet = $_SESSION['config'][$key1];
		$contenu = $_SESSION['config'][$key2];
		$contenu = str_replace("__URL_ACCUEIL_CONGES__", $_SESSION['config']['URL_ACCUEIL_CONGES'], $contenu);
		$contenu = str_replace("__SENDER_NAME__", $mail_sender_name, $contenu);
		$contenu = str_replace("__DESTINATION_NAME__", $mail_dest_name, $contenu);
		$contenu = str_replace("__NB_OF_DAY__", $sql_nb_jours, $contenu);
		$contenu = str_replace("__DATE_DEBUT__", $sql_date_deb, $contenu);
		$contenu = str_replace("__DATE_FIN__", $sql_date_fin, $contenu);
		$contenu = str_replace("__RETOUR_LIGNE__", "\r\n", $contenu);
		$contenu = str_replace("__COMMENT__", $sql_commentaire, $contenu);
		$contenu = str_replace("__TYPE_ABSENCE__", $sql_type_absence, $contenu);
        // _intcalendar 
        if (has_int_calendar_mode($subjectlogin,$mysql_link)) {
          $contenu = str_replace("__ABS_AS_CALITEM__", $labs_as_calendar[0], $contenu);
        };
		// construction du corps du mail
		$mail->Subject  =  $sujet ;
		$mail->Body     =  $contenu ; 

        // _intcalendar 
        if (has_int_calendar_mode($subjectlogin,$mysql_link)) {
          if (($objet=="accept_conges") || ($objet=="annul_conges"))  {
            // $mail->Body .=  "\n\n" . $labs_as_calendar[0] ;
            $mail->AddStringAttachment($labs_as_calendar[0],$labs_as_calendar[1],
                                       $encoding = "quoted-printable",
                                       $type = "text/calendar"); // "8bit" ,  
            // par defaut $encoding = "base64", $type = "application/octet-stream"
            // convergence ne reconnait pas en mode base64 
          }; 
        }; 
		/*********************************************/
		// ENVOI du mail
		if( $DEBUG==TRUE )
		{
			echo "SUBJECT = ".$sujet."<br>\n";
			echo "CONTENU = ".$mail->FromName." ".$contenu."<br>\n";
		}
		else
		{
			if(count($mail->to)==0)
			{
				echo "<b>ERROR : No recipient address for the message!</b><br>\n";
				echo "<b>Message was not sent </b><br>";
			}
			else
			{
				if(!$mail->Send())
				{
					echo "<b>Message was not sent </b><br>";
					echo "<b>Mailer Error: " . $mail->ErrorInfo."</b><br>";
				}
			}
		}
}

// recuperation du mail d'un user
// renvoit un tableau a 2 valeurs : prenom+nom et email
// _dpa : changement signature ajout lien sur database $mysql_lk
function find_email_adress_for_user($login, $mysql_lk, $DEBUG=FALSE)
{
	$found_mail=array();

	if($_SESSION['config']['where_to_find_user_email']=="ldap") // recherche du mail du user dans un annuaire LDAP
	{
//		include('config_ldap.php');
		// cnx à l'annuaire ldap :
		$ds = ldap_connect($_SESSION['config']['ldap_server']);
		if($_SESSION['config']['ldap_protocol_version'] != 0)
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $_SESSION['config']['ldap_protocol_version']) ;
		if ($_SESSION['config']['ldap_user'] == "")
		     $bound = ldap_bind($ds);
		else $bound = ldap_bind($ds, $_SESSION['config']['ldap_user'], $_SESSION['config']['ldap_pass']);

		// recherche des entrées correspondantes au "login" passé en paramètre :
		$filter = "(".$_SESSION['config']['ldap_login']."=".$login.")";

		$sr   = ldap_search($ds, $_SESSION['config']['searchdn'], $filter);
		$data = ldap_get_entries($ds,$sr);

		foreach ($data as $info)
		{
			$found_mail=array();
			// On récupère le nom et le mail de la personne.
			// Utilisation de la fonction utf8_decode pour corriger les caractères accentués
			// (qnd les noms ou prénoms ont des accents, "ç", ...

			// Les champs LDAP utilisés, bien que censés être uniformes, sont ceux d'un AD 2003.
			$ldap_prenom = $_SESSION['config']['ldap_prenom'];
			$ldap_nom    = $_SESSION['config']['ldap_nom'];
			$ldap_mail   = $_SESSION['config']['ldap_mail'];
			$nom = utf8_decode($info[$ldap_prenom][0])." ".strtoupper(utf8_decode($info[$ldap_nom][0])) ;
			$addr = $info[$ldap_mail][0] ;
			array_push($found_mail, $nom) ;
			array_push($found_mail, $addr) ;
		}
	}
	elseif($_SESSION['config']['where_to_find_user_email']=="dbconges") // recherche du mail du user dans la base db_conges
	{
	if ($mysql_lk) {
        $mysql_link = $mysql_lk ; 
      } else {
		$mysql_link = connexion_mysql();
     	     };
		
	$req = "SELECT u_nom, u_prenom, u_email FROM conges_users WHERE u_login='$login' ";
	$res = requete_mysql($req, $mysql_link, "find_email_adress_for_user", $DEBUG);
	$rec = mysql_fetch_array($res);

	$sql_nom = $rec["u_nom"];
	$sql_prenom = $rec["u_prenom"];
	$sql_email = $rec["u_email"];

		array_push($found_mail, $sql_prenom." ".strtoupper($sql_nom)) ;
		array_push($found_mail, $sql_email) ;

	}
	else
	{
		return FALSE;
	}
	return $found_mail ;
}


/**************************************************/
/* recup des échanges de rtt de chaque jour du mois pour tous les users et stockage dans 1 tableau de tableaux */
/**************************************************/
function recup_tableau_rtt_echange($mois, $first_jour, $year, $mysql_link, $DEBUG=FALSE)
{
	$tab_rtt_echange=array();  //tableau indexé dont la clé est la date sous forme yyyy-mm-dd
						//il contient pour chaque clé (chaque jour): un tableau indéxé ($tab_jour_rtt_echange) (clé= login)
						// qui contient lui même un tableau ($tab_echange) contenant les infos des echanges de rtt pour ce
						// jour et ce login (valeur du matin + valeur de l'apres midi ('Y' si rtt, 'N' sinon) )

	// construction du tableau $tab_rtt_echange:

	// pour chaque jour : (du premier jour demandé à la fin du mois ...)
	for($j=$first_jour; checkdate($mois, $j, $year); $j++)
	{
		$j_timestamp=mktime (0,0,0,$mois, $j, $year);
		$date_j=date("Y-m-d", $j_timestamp);
		$tab_jour_rtt_echange=array();

		$sql_echange_rtt="SELECT e_login, e_absence FROM conges_echange_rtt WHERE e_date_jour='$date_j' ";
		$res_echange_rtt = requete_mysql($sql_echange_rtt, $mysql_link, "recup_tableau_rtt_echange", $DEBUG);

		$num_echange_rtt = mysql_num_rows($res_echange_rtt);
		// si le jour est l'objet d'un echange, on tient compte de l'échange
		if($num_echange_rtt!=0)
		{
			while($result_echange_rtt = mysql_fetch_array($res_echange_rtt))
			{
				$tab_echange=array();
				$login=$result_echange_rtt["e_login"];
				if ($result_echange_rtt["e_absence"]=='J') // jour entier
				{
					$tab_echange["val_matin"]='Y';
					$tab_echange["val_aprem"]='Y';
				}
				elseif ($result_echange_rtt["e_absence"]=='M') // matin
				{
					$tab_echange["val_matin"]='Y';
					$tab_echange["val_aprem"]='N';
				}
				elseif ($result_echange_rtt["e_absence"]=='A') // apres-midi
				{
					$tab_echange["val_matin"]='N';
					$tab_echange["val_aprem"]='Y';
				}
				else
				{
					$tab_echange["val_matin"]='N';
					$tab_echange["val_aprem"]='N';
				}
				$tab_jour_rtt_echange[$login]=$tab_echange;
			}
		}
		$tab_rtt_echange[$date_j]=$tab_jour_rtt_echange;
	}

	// si le premier jour demandé n'est pas le 1ier du mois , on va jusqu'à la meme date le mois suivant :
	if($first_jour!=1) {
		// pour chaque jour jusqu'a la date voulue : (meme num de jour le mois suivant)
		for($j=1; $j<$first_jour; $j++)
		{
			$j_timestamp=mktime (0,0,0,$mois+1, $j, $year);
			$date_j=date("Y-m-d", $j_timestamp);
			$tab_jour_rtt_echange=array();

			$sql_echange_rtt="SELECT e_login, e_absence FROM conges_echange_rtt WHERE e_date_jour='$date_j' ";
			$res_echange_rtt = requete_mysql($sql_echange_rtt, $mysql_link, "recup_tableau_rtt_echange", $DEBUG);
			$num_echange_rtt = mysql_num_rows($res_echange_rtt);
			// si le jour est l'objet d'un echange, on tient compte de l'échange
			if($num_echange_rtt!=0)
			{
				while($result_echange_rtt = mysql_fetch_array($res_echange_rtt))
				{
					$tab_echange=array();
					$login=$result_echange_rtt["e_login"];
					if ($result_echange_rtt["e_absence"]=='J') // jour entier
					{
						$tab_echange["val_matin"]='Y';
						$tab_echange["val_aprem"]='Y';
					}
					elseif ($result_echange_rtt["e_absence"]=='M') // matin
					{
						$tab_echange["val_matin"]='Y';
						$tab_echange["val_aprem"]='N';
					}
					elseif ($result_echange_rtt["e_absence"]=='A') // apres-midi
					{
						$tab_echange["val_matin"]='N';
						$tab_echange["val_aprem"]='Y';
					}
					else
					{
						$tab_echange["val_matin"]='N';
						$tab_echange["val_aprem"]='N';
					}
					$tab_jour_rtt_echange[$login]=$tab_echange;
				}
			}
			$tab_rtt_echange[$date_j]=$tab_jour_rtt_echange;
		}
	}
	return $tab_rtt_echange;
}



/**************************************************/
/* recup dans un tableau des rtt planifiées  pour tous les users */
/**************************************************/
function recup_tableau_rtt_planifiees($mois, $first_jour, $year, $mysql_link, $DEBUG=FALSE)
{
	$tab_rtt_planifiees=array();  //tableau indexé dont la clé est le login_user
					// il contient pour chaque clé login : un tableau ($tab_user_grille) indexé dont la
					// clé est la date_fin_grille.
					// qui contient lui meme pour chaque clé : un tableau ($tab_user_rtt) qui contient enfin
					// les infos pour le matin et l'après midi ('Y' si rtt, 'N' sinon) sur 2 semaines
					// ( du sem_imp_lu_am au sem_p_ve_pm ) + la date de début et de fin de la grille

	$tab_user_grille=array();
	$tab_user_rtt=array();

	// construction du tableau $tab_rtt_planifie:
	$req_artt_login="SELECT DISTINCT(a_login) FROM conges_artt ";
	$res_artt_login = requete_mysql($req_artt_login, $mysql_link, "recup_tableau_rtt_planifiees", $DEBUG);

	//$num_artt_login = mysql_num_rows($res_artt_login);
	while($result_artt_login = mysql_fetch_array($res_artt_login)) // pour chaque login trouvé
	{
		$sql_artt_login=$result_artt_login["a_login"];
		$tab_user_grille=array();

		$req_artt = "SELECT * FROM conges_artt WHERE a_login='$sql_artt_login' ";
		$res_artt = requete_mysql($req_artt, $mysql_link, "recup_tableau_rtt_planifiees", $DEBUG);

		$num_artt = mysql_num_rows($res_artt);
		while($result_artt = mysql_fetch_array($res_artt))
		{
			$tab_user_rtt=array();
			$sql_date_fin_grille=$result_artt["a_date_fin_grille"];
			$key_grille=$sql_date_fin_grille ;

			$tab_user_rtt["login"]=$sql_artt_login;

			$tab_user_rtt["sem_imp_lu_am"]=$result_artt["sem_imp_lu_am"];
			$tab_user_rtt["sem_imp_lu_pm"]=$result_artt["sem_imp_lu_pm"];
			$tab_user_rtt["sem_imp_ma_am"]=$result_artt["sem_imp_ma_am"];
			$tab_user_rtt["sem_imp_ma_pm"]=$result_artt["sem_imp_ma_pm"];
			$tab_user_rtt["sem_imp_me_am"]=$result_artt["sem_imp_me_am"];
			$tab_user_rtt["sem_imp_me_pm"]=$result_artt["sem_imp_me_pm"];
			$tab_user_rtt["sem_imp_je_am"]=$result_artt["sem_imp_je_am"];
			$tab_user_rtt["sem_imp_je_pm"]=$result_artt["sem_imp_je_pm"];
			$tab_user_rtt["sem_imp_ve_am"]=$result_artt["sem_imp_ve_am"];
			$tab_user_rtt["sem_imp_ve_pm"]=$result_artt["sem_imp_ve_pm"];
			$tab_user_rtt["sem_imp_sa_am"]=$result_artt["sem_imp_sa_am"];
			$tab_user_rtt["sem_imp_sa_pm"]=$result_artt["sem_imp_sa_pm"];
			$tab_user_rtt["sem_imp_di_am"]=$result_artt["sem_imp_di_am"];
			$tab_user_rtt["sem_imp_di_pm"]=$result_artt["sem_imp_di_pm"];
			$tab_user_rtt["sem_p_lu_am"]=$result_artt["sem_p_lu_am"];
			$tab_user_rtt["sem_p_lu_pm"]=$result_artt["sem_p_lu_pm"];
			$tab_user_rtt["sem_p_ma_am"]=$result_artt["sem_p_ma_am"];
			$tab_user_rtt["sem_p_ma_pm"]=$result_artt["sem_p_ma_pm"];
			$tab_user_rtt["sem_p_me_am"]=$result_artt["sem_p_me_am"];
			$tab_user_rtt["sem_p_me_pm"]=$result_artt["sem_p_me_pm"];
			$tab_user_rtt["sem_p_je_am"]=$result_artt["sem_p_je_am"];
			$tab_user_rtt["sem_p_je_pm"]=$result_artt["sem_p_je_pm"];
			$tab_user_rtt["sem_p_ve_am"]=$result_artt["sem_p_ve_am"];
			$tab_user_rtt["sem_p_ve_pm"]=$result_artt["sem_p_ve_pm"];
			$tab_user_rtt["sem_p_sa_am"]=$result_artt["sem_p_sa_am"];
			$tab_user_rtt["sem_p_sa_pm"]=$result_artt["sem_p_sa_pm"];
			$tab_user_rtt["sem_p_di_am"]=$result_artt["sem_p_di_am"];
			$tab_user_rtt["sem_p_di_pm"]=$result_artt["sem_p_di_pm"];

			$tab_user_rtt["date_debut_grille"]=$result_artt["a_date_debut_grille"];
			$tab_user_rtt["date_fin_grille"]=$result_artt["a_date_fin_grille"];

			$tab_user_grille[$key_grille]=$tab_user_rtt;
		}
		$tab_rtt_planifiees[$sql_artt_login]=$tab_user_grille;
	}
	return $tab_rtt_planifiees;
}


// affiche une liste déroulante des jours du mois : la variable est $new_jour
function affiche_selection_new_jour($default)
{
	echo "<select name=\"new_jour\" >\n";
	for($i=1; $i<10; $i++)
	{
		if($default=="0$i")
			echo "<option value=\"0$i\" selected >0$i</option>\n";
		else
			echo "<option value=\"0$i\">0$i</option>\n";
	}
	for($i=10; $i<32; $i++)
	{
		if($default=="$i")
			echo "<option value=\"$i\" selected >$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	echo "</select>\n";
}

// affiche une liste déroulante des mois de l'année : la variable est $new_mois
function affiche_selection_new_mois($default)
{
	echo "<select name=\"new_mois\" >\n";
	for($i=1; $i<10; $i++)
	{
		echo "$default : $i<br>\n";
		if($default=="0$i")
			echo "<option value=\"0$i\" selected >0$i</option>\n";
		else
			echo "<option value=\"0$i\">0$i</option>\n";
	}
	for($i=10; $i<13; $i++)
	{
		if($default=="$i")
			echo "<option value=\"$i\" selected >$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	echo "</select>\n";
}

// affiche une liste déroulante d'année : la variable est $new_year
function affiche_selection_new_year($an_debut, $an_fin, $default)
{
	echo "<select name=\"new_year\" >\n";
	for($i=$an_debut; $i<$an_fin+1; $i++)
	{
		if($default=="$i")
			echo "<option value=\"$i\" selected >$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	echo "</select>\n";
}

// met la date aaaa-mm-jj dans le format jj-mm-aaaa
function eng_date_to_fr($une_date, $DEBUG=FALSE)
{
 return substr($une_date, 8)."-".substr($une_date, 5, 2)."-".substr($une_date, 0, 4);

}

// met la date jj-mm-aaaa dans le format aaaa-mm-jj
function fr_date_to_eng($une_date)
{
 return substr($une_date, 6)."-".substr($une_date, 3, 2)."-".substr($une_date, 0, 2);

}


// affichage de la cellule correspondant au jour dans les calendrier de saisie (demande de conges, etc ...)
function affiche_cellule_jour_cal_saisie($login, $j_timestamp, $td_second_class, $result, $mysql_link, $DEBUG=FALSE)
{
	$session=session_id();

	//echo "$j_timestamp, $year, $mois, $j, $td_second_class<br>\n";
	$date_j=date("Y-m-d", $j_timestamp);
	$j=date("d", $j_timestamp);

	$class_am="travail_am";
	$class_pm="travail_pm";

	$val_matin="";
	$val_aprem="";
	// recup des infos ARTT ou Temps Partiel :
	// la fonction suivante change les valeurs de $val_matin $val_aprem ....
	recup_infos_artt_du_jour($login, $j_timestamp, $val_matin, $val_aprem, $mysql_link, $DEBUG);

	//## AFICHAGE ##
	if($val_matin=="Y")
	{
		$class_am="rtt_am";
	}
	if($val_aprem=="Y")
	{
		$class_pm = "rtt_pm";
	}


	$jour_today=date("j");
	$mois_today=date("m");
	$year_today=date("Y");
	$timestamp_today = mktime (0,0,0,$mois_today,$jour_today,$year_today);
	// si la saisie de conges pour une periode passée est interdite : pas de case à cocher dans les dates avant aujourd'hui
	if( ($_SESSION['config']['interdit_saisie_periode_date_passee']==TRUE) && ($j_timestamp<$timestamp_today) )
		echo "<td  class=\"cal-saisie $td_second_class $class_am $class_pm\">$j</td>";
	else
	{
		// Si le client est sous IE, on affiche pas les jours de rtt (car IE ne gère pas les appels standart de classe de feuille e style)
		if( (isset($_SERVER["HTTP_USER_AGENT"])) && (stristr($_SERVER["HTTP_USER_AGENT"], "MSIE")!=FALSE) )
		{
			echo "<td  class=\"cal-saisie\">$j<input type=\"radio\" name=\"$result\" ";
			if($_SESSION['config']['rempli_auto_champ_nb_jours_pris']==TRUE)
			{
				// attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
				if( (isset($_SERVER["HTTP_USER_AGENT"])) && (stristr($_SERVER["HTTP_USER_AGENT"], "MSIE 6")!=FALSE) )
					echo "onClick=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return true;\"" ;
				else
					echo "onChange=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;\"" ;
			}
			echo " value=\"$date_j\"></td>";
		}
		else
		{
			echo "<td  class=\"cal-saisie $td_second_class $class_am $class_pm\">$j<input type=\"radio\" name=\"$result\" ";
			if($_SESSION['config']['rempli_auto_champ_nb_jours_pris']==TRUE)
			{
				echo "onChange=\"compter_jours(new_debut, new_fin, login_user, new_demi_jour_deb, new_demi_jour_fin);return false;\"" ;
			}
			echo " value=\"$date_j\"></td>";
		}
	}

}



// recup du nom d'un groupe grace à son group_id
function get_group_name_from_id($groupe_id, $mysql_link, $DEBUG=FALSE)
{
		$req_name="SELECT g_groupename FROM conges_groupe WHERE g_gid=$groupe_id ";
		$ReqLog_name = requete_mysql($req_name, $mysql_link, "ajout_global_groupe", $DEBUG);

		$resultat_name = mysql_fetch_array($ReqLog_name);
		return $resultat_name["g_groupename"];

}

// recup de la liste de TOUS les users dont $resp_login est responsable
// (prend en compte le resp direct, les groupes, le reps virtuel, etc ...)
// renvoit une liste de login entre quotes et séparés par des virgules

// <cedre::dev::dpa> : partiellementproposition receriture pour gerer le pb de récursion
// re-structure en 2 morceaux


// une facade traduit en string le resultat de la fonction
function get_list_all_users_du_resp($resp_login, $mysql_link, $DEBUG=FALSE)
{
  global $g_users ;
  global $o_users ; //
  global $glau_cpt ;
  // echo "get_list_all_users_du_resp " . $resp_login . "<br>" ;
  $g_users = array() ; // init var global peuple comme suit ['nom.prenom'] = "'login'"
  $o_users = array() ; // init var global
  $glau_cpt = 0;
  $g_users_n0 = get_list_all_users_du_resp_as_list($resp_login, $mysql_link, $DEBUG) ;
  ksort($g_users);  // tri des elements sur la cle donc nom.prenom

  if($DEBUG==TRUE) {
    echo "__gusers__ <br> " ;
    print_r($g_users);
  };
  return join(", ", $g_users) ;
}

// get_list_all_users_du_resp_as_list  : v2
// retourne une liste d'utilisateurs uniques qui est ajoutée
// $g_users = structure [nom.prenom] => 'login' ;
// CARE: cette fonction est recursive comme la fonction d'origine
//       mais avec protection

function get_list_all_users_du_resp_as_list($resp_login, $mysql_link, $DEBUG=FALSE)
{
  global $g_users ;
  global $glau_cpt ; // tech : compteur pour voir les re-entrances
  global $o_users ;  // liste des utilisateurs deja visités
  ++$glau_cpt ;
  if($DEBUG==TRUE) {
    echo "get_list_all_users_du_resp_as_list : " . $glau_cpt."<br>" ;
  };
  $o_users[$resp_login] = $resp_login ; // memo de l'appel deja fait pas de cote

  $l_users = array() ; // local ; demmare a vide a chaque appel fonction
    if($_SESSION['config']['autovalidation_conges_par_responsable']==FALSE)
	$sql="SELECT DISTINCT(u_login),u_nom,u_prenom FROM conges_users WHERE u_login!='conges' AND u_login!='admin' AND u_login!='$resp_login' "; // modif du 23 oct 2012
	else
	$sql="SELECT DISTINCT(u_login),u_nom,u_prenom FROM conges_users WHERE u_login!='conges' AND u_login!='admin'"; // modif du 26 oct 2012	
	// si resp virtuel, on renvoit tout le monde, sinon, seulement ceux dont on est responsables
	if($_SESSION['config']['responsable_virtuel']==FALSE)
	{
		$sql = $sql." AND  ( u_resp_login='$resp_login' " ;
		if($_SESSION['config']['gestion_groupes'] == TRUE)
		{
			$list_users_group=get_list_users_des_groupes_du_resp($resp_login, $mysql_link, $DEBUG);
			if($list_users_group!="")
				$sql=$sql." OR u_login IN ($list_users_group) ";
		}

		$sql=$sql." ) " ;
	}
	# $sql = $sql. " ORDER BY u_nom " ;

	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_all_users_du_resp", $DEBUG);

	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
      // on peuple la liste locale : ['nom.prenom'] = "'login'"
      // echo "<pre>__res ". $resultat1["u_nom"].".".$resultat1["u_prenom"]. "</pre>" ;
      $l_users[$resultat1["u_nom"].".".$resultat1["u_prenom"]] = "'".$resultat1["u_login"]."'" ;
	}
    // on ajoute cette liste à la liste globale
    //  foreach (array_expression as $key => $value)
    foreach ($l_users as $ukey => $uvalue ) {
      if (!array_key_exists($ukey,$g_users)) {
        $g_users[$ukey] = $uvalue ;
      };
    }
	/************************************/
	// gestion des absence des responsables :
	// on recup la liste des users des resp absents, dont $resp_login est responsable
	if($_SESSION['config']['gestion_cas_absence_responsable']==TRUE)
	{
		// recup liste des resp absents, dont $resp_login est responsable
		$sql_2="SELECT DISTINCT(u_login) FROM conges_users WHERE u_is_resp='Y' AND u_login!='$resp_login' AND u_login!='conges' AND u_login!='admin' ";
		// si resp virtuel, on renvoit tout le monde, sinon, seulement ceux dont on est responsables
		if($_SESSION['config']['responsable_virtuel']==FALSE)
		{
			$sql_2 = $sql_2." AND  ( u_resp_login='$resp_login' " ;
			if($_SESSION['config']['gestion_groupes'] == TRUE)
			{
				$list_users_group=get_list_users_des_groupes_du_resp($resp_login, $mysql_link, $DEBUG);
				if($list_users_group!="")
					$sql_2=$sql_2." OR u_login IN ($list_users_group) ";
			}
			$sql_2=$sql_2." ) " ;
		}
		// $sql_2 = $sql_2." ORDER BY u_nom " ;

		$ReqLog_2 = requete_mysql($sql_2, $mysql_link, "get_list_all_users_du_resp", $DEBUG);

		// on va verifier si les resp récupérés sont absents (si oui, c'est $resp_login qui traite leurs users
		while ($resultat_2 = mysql_fetch_array($ReqLog_2))
		{
			$current_resp=$resultat_2["u_login"];
			// verif dans la base si le current_resp est absent :
			$req = "SELECT p_num
                                     FROM conges_periode
                                     WHERE p_login = '$current_resp'
                                     AND p_etat = 'ok'
                                     AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW())
                                     AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())";
			$ReqLog_3 = requete_mysql($req, $mysql_link, "get_list_all_users_du_resp", $DEBUG);

			// si le current resp est absent : on recup la liste de ses users pour les traiter .....
			if (mysql_num_rows($ReqLog_3)!=0)
			{   /*
				if($list_users=="")
					$list_users=get_list_all_users_du_resp($current_resp, $mysql_link, $DEBUG);
				else
                   test à faire pour voir si $current_resp n'est pas deja ds $list_users

					$list_users=$list_users.", ".get_list_all_users_du_resp($current_resp, $mysql_link, $DEBUG);
                */
              // CARE : gestion recursive
              //        on re-appelle get_list_all_users_du_resp_as_list si
              // $current_resp n'appartient pas à $o_users
              if (!array_key_exists($current_resp, $o_users)) {
                $cur_resp_list =
                  get_list_all_users_du_resp_as_list($current_resp, $mysql_link,
                                                     $DEBUG);
                foreach ($cur_resp_list as $ukey => $uvalue ) {
                  if (!array_key_exists($ukey, $g_users)) {
                    $g_users[$ukey] = $uvalue ;
                  };
                }

              }
              else {  // pour trace permet de détecter les donnees qui conduisent à
                      // re-entrance
                if($DEBUG==TRUE) {
                  echo "__recur ". $current_resp ." deja ds la liste. <br>" ;
                };
              }
            }
		}

	}
	// FIN gestion des absence des responsables :
	/************************************/

	if($DEBUG==TRUE) {
      echo "<pre> __l_users " ;
      print_r($l_users) ;
      echo "</pre>" ;
    };
    --$glau_cpt ;
	return $l_users;
}
// get_list_all_users_du_resp_as_list_v1 :
// la fonction qui fait le travail retourne une liste d'utilisateurs uniques que
// l'on rajoute à la liste $g_users
// CARE: cette fonction est recursive comme la fonction d'origine
//       mais avec protection

function get_list_all_users_du_resp_as_list_v1($resp_login, $mysql_link, $DEBUG=FALSE)
{
  global $g_users ;
  global $glau_cpt ; // tech : compteur pour voir les re-entrances
  global $o_users ;  // liste des utilisateurs deja visités
  ++$glau_cpt ;
  if($DEBUG==TRUE) {
    echo "get_list_all_users_du_resp_as_list : " . $glau_cpt."<br>" ;
  };
  array_push($o_users, $resp_login); // pas de cote

  $l_users = array() ; // local ; demmare a vide a chaque appel fonction
    if($_SESSION['config']['autovalidation_conges_par_responsable']==FALSE)
	$sql="SELECT DISTINCT(u_login),u_nom,u_prenom FROM conges_users WHERE u_login!='conges' AND u_login!='admin' AND u_login!='$resp_login' "; // modif du 23 oct 2012
	else
	$sql="SELECT DISTINCT(u_login),u_nom,u_prenom FROM conges_users WHERE u_login!='conges' AND u_login!='admin'"; // modif du 26 oct 2012	
	// si resp virtuel, on renvoit tout le monde, sinon, seulement ceux dont on est responsables
	if($_SESSION['config']['responsable_virtuel']==FALSE)
	{
		$sql = $sql." AND  ( u_resp_login='$resp_login' " ;
		if($_SESSION['config']['gestion_groupes'] == TRUE)
		{
			$list_users_group=get_list_users_des_groupes_du_resp($resp_login, $mysql_link, $DEBUG);
			if($list_users_group!="")
				$sql=$sql." OR u_login IN ($list_users_group) ";
		}

		$sql=$sql." ) " ;
	}
	$sql = $sql." ORDER BY u_nom " ;

	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_all_users_du_resp", $DEBUG);

	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$current_login=$resultat1["u_login"];
		/* if($list_users=="")
			$list_users="'$current_login'";
		else
        $list_users=$list_users.", '$current_login'"; */
        array_push($l_users, "'$current_login'") ;
	}
    foreach ($l_users as $auser ) {
      if (!in_array($auser, $g_users)) {
        // add
        array_push($g_users, $auser) ;
      };
    }
	/************************************/
	// gestion des absence des responsables :
	// on recup la liste des users des resp absents, dont $resp_login est responsable
	if($_SESSION['config']['gestion_cas_absence_responsable']==TRUE)
	{
		// recup liste des resp absents, dont $resp_login est responsable
		$sql_2="SELECT DISTINCT(u_login) FROM conges_users WHERE u_is_resp='Y' AND u_login!='$resp_login' AND u_login!='conges' AND u_login!='admin' ";
		// si resp virtuel, on renvoit tout le monde, sinon, seulement ceux dont on est responsables
		if($_SESSION['config']['responsable_virtuel']==FALSE)
		{
			$sql_2 = $sql_2." AND  ( u_resp_login='$resp_login' " ;
			if($_SESSION['config']['gestion_groupes'] == TRUE)
			{
				$list_users_group=get_list_users_des_groupes_du_resp($resp_login, $mysql_link, $DEBUG);
				if($list_users_group!="")
					$sql_2=$sql_2." OR u_login IN ($list_users_group) ";
			}
			$sql_2=$sql_2." ) " ;
		}
		$sql_2 = $sql_2." ORDER BY u_nom " ;

		$ReqLog_2 = requete_mysql($sql_2, $mysql_link, "get_list_all_users_du_resp", $DEBUG);

		// on va verifier si les resp récupérés sont absents (si oui, c'est $resp_login qui traite leurs users
		while ($resultat_2 = mysql_fetch_array($ReqLog_2))
		{
			$current_resp=$resultat_2["u_login"];
			// verif dans la base si le current_resp est absent :
			$req = "SELECT p_num
                                     FROM conges_periode
                                     WHERE p_login = '$current_resp'
                                     AND p_etat = 'ok'
                                     AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW())
                                     AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())";
			$ReqLog_3 = requete_mysql($req, $mysql_link, "get_list_all_users_du_resp", $DEBUG);

			// si le current resp est absent : on recup la liste de ses users pour les traiter .....
			if (mysql_num_rows($ReqLog_3)!=0)
			{   /*
				if($list_users=="")
					$list_users=get_list_all_users_du_resp($current_resp, $mysql_link, $DEBUG);
				else
                   test à faire pour voir si $current_resp n'est pas deja ds $list_users

					$list_users=$list_users.", ".get_list_all_users_du_resp($current_resp, $mysql_link, $DEBUG);
                */
              // CARE : gestion recursive
              //        on re-appelle get_list_all_users_du_resp_as_list si
              // $current_resp n'appartient pas à $o_users
              if (!in_array($current_resp, $o_users)) {
                $cur_resp_list =
                  get_list_all_users_du_resp_as_list_v1($current_resp, $mysql_link,
                                                     $DEBUG);
                foreach ($cur_resp_list as $aresp ) {
                  if (!in_array($aresp, $g_users)) {
                    // add
                    array_push($g_users, $aresp) ;
                  };
                }
              }
              else {  // pour trace permet de détecter les donnees qui conduisent à
                      // re-entrance
                if($DEBUG==TRUE) {
                  echo "__recur ". $current_resp ." deja ds la liste. <br>" ;
                };
              }
            }
		}

	}
	// FIN gestion des absence des responsables :
	/************************************/

	if($DEBUG==TRUE) { echo "list_users = $list_users<br>\n" ;}
    // echo "<pre>" . print_r($l_users) . "</pre>" ;
    --$glau_cpt ;
	return $l_users;
}
// </cedre::dev::dpa>

// recup de la liste de TOUS les users dont $resp_login est responsable
// (prend en compte le resp direct, les groupes, le reps virtuel, etc ...)
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_all_users_du_resp_v142($resp_login, $mysql_link, $DEBUG=FALSE)
{

	$list_users="";

	$sql="SELECT DISTINCT(u_login) FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ";

	// si resp virtuel, on renvoit tout le monde, sinon, seulement ceux dont on est responsables
	if($_SESSION['config']['responsable_virtuel']==FALSE)
	{
		$sql = $sql." AND  ( u_resp_login='$resp_login' " ;
		if($_SESSION['config']['gestion_groupes'] == TRUE)
		{
			$list_users_group=get_list_users_des_groupes_du_resp($resp_login, $mysql_link, $DEBUG);
			if($list_users_group!="")
				$sql=$sql." OR u_login IN ($list_users_group) ";
		}

		$sql=$sql." ) " ;
	}
	$sql = $sql." ORDER BY u_nom " ;

	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_all_users_du_resp", $DEBUG);

	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$current_login=$resultat1["u_login"];
		if($list_users=="")
			$list_users="'$current_login'";
		else
			$list_users=$list_users.", '$current_login'";
	}

	/************************************/
	// gestion des absence des responsables :
	// on recup la liste des users des resp absents, dont $resp_login est responsable
	if($_SESSION['config']['gestion_cas_absence_responsable']==TRUE)
	{
		// recup liste des resp absents, dont $resp_login est responsable
		$sql_2="SELECT DISTINCT(u_login) FROM conges_users WHERE u_is_resp='Y' AND u_login!='$resp_login' AND u_login!='conges' AND u_login!='admin' ";
		// si resp virtuel, on renvoit tout le monde, sinon, seulement ceux dont on est responsables
		if($_SESSION['config']['responsable_virtuel']==FALSE)
		{
			$sql_2 = $sql_2." AND  ( u_resp_login='$resp_login' " ;
			if($_SESSION['config']['gestion_groupes'] == TRUE)
			{
				$list_users_group=get_list_users_des_groupes_du_resp($resp_login, $mysql_link, $DEBUG);
				if($list_users_group!="")
					$sql_2=$sql_2." OR u_login IN ($list_users_group) ";
			}
			$sql_2=$sql_2." ) " ;
		}
		$sql_2 = $sql_2." ORDER BY u_nom " ;

		$ReqLog_2 = requete_mysql($sql_2, $mysql_link, "get_list_all_users_du_resp", $DEBUG);

		// on va verifier si les resp récupérés sont absents (si oui, c'est $resp_login qui traite leurs users
		while ($resultat_2 = mysql_fetch_array($ReqLog_2))
		{
			$current_resp=$resultat_2["u_login"];
			// verif dans la base si le current_resp est absent :
			$req = "SELECT p_num
                                     FROM conges_periode
                                     WHERE p_login = '$current_resp'
                                     AND p_etat = 'ok'
                                     AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW())
                                     AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())";
			$ReqLog_3 = requete_mysql($req, $mysql_link, "get_list_all_users_du_resp", $DEBUG);

			// si le current resp est absent : on recup la liste de ses users pour les traiter .....
			if (mysql_num_rows($ReqLog_3)!=0)
			{
				if($list_users=="")
					$list_users=get_list_all_users_du_resp_v142($current_resp, $mysql_link, $DEBUG);
				else
                  // test à faire pour voir si $current_resp n'est pas deja ds $list_users

					$list_users=$list_users.", ".get_list_all_users_du_resp_v142($current_resp, $mysql_link, $DEBUG);
			}
		}

	}
	// FIN gestion des absence des responsables :
	/************************************/

	if($DEBUG==TRUE) { echo "list_users = $list_users<br>\n" ;}

	return $list_users;
}


// recup de la liste des users d'un groupe donné
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_users_du_groupe($group_id, $mysql_link, $DEBUG=FALSE)
{
  // echo "get_list_users_du_groupe ". $group_id."<br>" ;
	$list_users="";
    /* _ac3 : uniquement les liens de gu_nature = 'membre' */   
	$sql="SELECT DISTINCT(gu_login) FROM conges_groupe_users WHERE gu_gid = $group_id and gu_nature='membre' ORDER BY gu_login ";
	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_users_du_groupe", $DEBUG);

	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$current_login=$resultat1["gu_login"];
		if($list_users=="")
			$list_users="'$current_login'";
		else
			$list_users=$list_users.", '$current_login'";
	}

	if($DEBUG==TRUE) { echo "list_users = $list_users<br>\n" ;}
    // echo "list_users = $list_users<br>\n" ;
	return $list_users;

}

// recup de la liste des users des groupes dont $resp_login est responsable
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_users_des_groupes_du_resp($resp_login, $mysql_link, $DEBUG=FALSE)
{
  // echo "get_list_users_des_groupes_du_resp ". $resp_login."<br>" ;
	$list_users_des_groupes_du_resp="";

	$list_groups=get_list_groupes_du_resp($resp_login, $mysql_link, $DEBUG);
	if($list_groups!="") // si $resp_login est responsable d'au moins un groupe
	{   /* _ac3 : uniquement les liens de gu_nature = 'membre' */ 
		$sql="SELECT DISTINCT(gu_login) FROM conges_groupe_users WHERE gu_gid IN ($list_groups) and gu_nature='membre' ORDER BY gu_login ";
		$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_users_des_groupes_du_resp", $DEBUG);

		while ($resultat1 = mysql_fetch_array($ReqLog1))
		{
			$current_login=$resultat1["gu_login"];
			if($list_users_des_groupes_du_resp=="")
				$list_users_des_groupes_du_resp="'$current_login'";
			else
				$list_users_des_groupes_du_resp=$list_users_des_groupes_du_resp.", '$current_login'";
		}
	}
	if($DEBUG==TRUE) { echo "list_users_des_groupes_du_resp = $list_users_des_groupes_du_resp<br>\n" ;}

	return $list_users_des_groupes_du_resp;

}

// recup de la liste des groupes dont $resp_login est responsable
// renvoit une liste de group_id séparés par des virgules
function get_list_groupes_du_resp($resp_login, $mysql_link, $DEBUG=FALSE)
{
	$list_group="";

	$sql="SELECT gr_gid FROM conges_groupe_resp WHERE gr_login='$resp_login' ORDER BY gr_gid";
	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_groupes_du_resp", $DEBUG);

	if(mysql_num_rows($ReqLog1)!=0)
	{
		while ($resultat1 = mysql_fetch_array($ReqLog1))
		{
			$current_group=$resultat1["gr_gid"];
			if($list_group=="")
				$list_group="$current_group";
			else
				$list_group=$list_group.", $current_group";
		}
	}
	if($DEBUG==TRUE) { echo "list_group = $list_group<br>\n" ;}

	return $list_group;
}

// recup de la liste des groupes dont $resp_login est grandresponsable
// renvoit une liste de group_id séparés par des virgules
function get_list_groupes_du_grand_resp($resp_login, $mysql_link, $DEBUG=FALSE)
{
	$list_group="";

	$sql="SELECT ggr_gid FROM conges_groupe_grd_resp WHERE ggr_login='$resp_login' ORDER BY ggr_gid";
	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_groupes_du_grand_resp", $DEBUG);

	if(mysql_num_rows($ReqLog1)!=0)
	{
		while ($resultat1 = mysql_fetch_array($ReqLog1))
		{
			$current_group=$resultat1["ggr_gid"];
			if($list_group=="")
				$list_group="$current_group";
			else
				$list_group=$list_group.", $current_group";
		}
	}
	if($DEBUG==TRUE) { echo "list_group = $list_group<br>\n" ;}

	return $list_group;
}

// recup de la liste des groupes à double validation
// renvoit une liste de gid séparés par des virgules
function get_list_groupes_double_valid($mysql_link, $DEBUG=FALSE)
{
	$list_groupes_double_valid="";

	$sql="SELECT g_gid FROM conges_groupe WHERE g_double_valid='Y' ORDER BY g_gid ";
	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_groupes_double_valid_du_resp", $DEBUG);

	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$current_gid=$resultat1["g_gid"];
		if($list_groupes_double_valid=="")
			$list_groupes_double_valid="$current_gid";
		else
			$list_groupes_double_valid=$list_groupes_double_valid.", $current_gid";
	}

	if($DEBUG==TRUE) { echo "list_groupes_double_valid = $list_groupes_double_valid<br>\n" ;}

	return $list_groupes_double_valid;

}

// recup de la liste des groupes à double validation, dont $resp_login est responsable
// renvoit une liste de gid séparés par des virgules
function get_list_groupes_double_valid_du_resp($resp_login, $mysql_link, $DEBUG=FALSE)
{
	$list_groupes_double_valid_du_resp="";

	$list_groups=get_list_groupes_du_resp($resp_login, $mysql_link, $DEBUG);
	if($list_groups!="") // si $resp_login est responsable d'au moins un groupe
	{
		$sql="SELECT DISTINCT(g_gid) FROM conges_groupe WHERE g_double_valid='Y' AND g_gid IN ($list_groups) ORDER BY g_gid ";
		$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_groupes_double_valid_du_resp", $DEBUG);

		while ($resultat1 = mysql_fetch_array($ReqLog1))
		{
			$current_gid=$resultat1["g_gid"];
			if($list_groupes_double_valid_du_resp=="")
				$list_groupes_double_valid_du_resp="$current_gid";
			else
				$list_groupes_double_valid_du_resp=$list_groupes_double_valid_du_resp.", $current_gid";
		}
	}
	if($DEBUG==TRUE) { echo "list_groupes_double_valid_du_resp = $list_groupes_double_valid_du_resp<br>\n" ;}

	return $list_groupes_double_valid_du_resp;

}

// recup de la liste des groupes à double validation, dont $resp_login est GRAND responsable
// renvoit une liste de gid séparés par des virgules
function get_list_groupes_double_valid_du_grand_resp($resp_login, $mysql_link, $DEBUG=FALSE)
{
	$list_groupes_double_valid_du_grand_resp="";

	$sql="SELECT DISTINCT(ggr_gid) FROM conges_groupe_grd_resp WHERE ggr_login='$resp_login' ORDER BY ggr_gid ";
	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_groupes_double_valid_du_grand_resp", $DEBUG);

	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$current_gid=$resultat1["ggr_gid"];
		if($list_groupes_double_valid_du_grand_resp=="")
			$list_groupes_double_valid_du_grand_resp="$current_gid";
		else
			$list_groupes_double_valid_du_grand_resp=$list_groupes_double_valid_du_grand_resp.", $current_gid";
	}
	if($DEBUG==TRUE) { echo "list_groupes_double_valid_du_grand_resp = $list_groupes_double_valid_du_grand_resp<br>\n" ;}

	return $list_groupes_double_valid_du_grand_resp;

}

// recup de la liste des users des groupes dont $user_login est :
// membre par defaut ou visiteur si "" 
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_users_des_groupes_du_user($user_login,$snature="membre",$mysql_link, $DEBUG=FALSE)
{
	$list_users="";

	$list_groups=get_list_groupes_du_user($user_login,$snature, $mysql_link, $DEBUG);
	if($list_groups!="") // si $user_login est membre d'au moins un groupe
	{   /* _ac3 : uniquement les liens de gu_nature = 'membre' */
		$sql="SELECT DISTINCT(gu_login) FROM conges_groupe_users WHERE gu_gid IN ($list_groups) and gu_nature='membre' ORDER BY gu_login ";
		$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_users_des_groupes_du_user", $DEBUG);

		while ($resultat1 = mysql_fetch_array($ReqLog1))
		{
			$current_login=$resultat1["gu_login"];
			if($list_users=="")
				$list_users="'$current_login'";
			else
				$list_users=$list_users.", '$current_login'";
		}
	}
	return $list_users;

}

// recup de la liste des groupes dont $resp_login est $snature 
// _adaptation a conges4acv3 
// renvoit une liste de group_id séparés par des virgules
function get_list_groupes_du_user($user_login,$snature="membre",$mysql_link, $DEBUG=FALSE)
{
	$list_group="";
    if  ($snature == "") {
      $swhere = "" ; 
    } else {
      $swhere = "AND gu_nature='".$snature."'" ; 
    }
	$sql="SELECT gu_gid FROM conges_groupe_users WHERE gu_login='$user_login' ".$swhere. " ORDER BY gu_gid";
	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_groupes_du_user", $DEBUG);

	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$current_group=$resultat1["gu_gid"];
		if($list_group=="")
			$list_group="$current_group";
		else
			$list_group=$list_group.", $current_group";
	}
	return $list_group;
}


// recup de la liste de TOUS les users (sauf "conges" et "admin"
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_all_users($mysql_link, $DEBUG=FALSE)
{

	$list_users="";

	$sql="SELECT DISTINCT(u_login) FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_login " ;

	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_all_users", $DEBUG);

	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$current_login=$resultat1["u_login"];
		if($list_users=="")
			$list_users="'$current_login'";
		else
			$list_users=$list_users.", '$current_login'";
	}

	if($DEBUG==TRUE) { echo "list_users = $list_users<br>\n" ;}

	return $list_users;
}


// recup de la liste des groupes (tous)
// renvoit une liste de group_id séparés par des virgules
function get_list_all_groupes($mysql_link, $DEBUG=FALSE)
{
	$list_group="";

	// on select dans conges_groupe_users pour ne récupérer QUE les groupes qui ont des users !!
	$sql="SELECT DISTINCT(gu_gid) FROM conges_groupe_users ORDER BY gu_gid";
	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_list_all_groupes", $DEBUG);

	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$current_group=$resultat1["gu_gid"];
		if($list_group=="")
			$list_group="$current_group";
		else
			$list_group=$list_group.", $current_group";
	}
	return $list_group;
}


// construit le tableau des responsables d'un user
// le login du user est passé en paramêtre ainsi que le tableau (vide) des resp
//renvoit un tableau indexé de resp_login => "absent" ou "present"
function get_tab_resp_du_user($user_login, $mysql_link, $DEBUG=FALSE)
{
	$tab_resp=array();
	if($_SESSION['config']['responsable_virtuel']==TRUE)
	{
		$tab_resp["conges"]="present";
	}
	else
	{
		if($DEBUG==TRUE) {echo ">> RECHERCHE des RESPONSABLES de : $user_login<br>\n";}
		// recup du resp indiqué dans la table users (sauf s'il est resp de lui meme)
		$req = "SELECT u_resp_login FROM conges_users WHERE u_login='$user_login' ";
		$res = requete_mysql($req, $mysql_link, "get_tab_resp_du_user", $DEBUG);

		$rec = mysql_fetch_array($res);
		$tab_resp[$rec['u_resp_login']]="present";

		// recup des resp des groupes du user
		if($_SESSION['config']['gestion_groupes']==TRUE)
		{
          $list_groups=get_list_groupes_du_user($user_login,"membre",$mysql_link,$DEBUG);
			if($list_groups!="")
			{
				$tab_gid=explode(",", $list_groups);
				foreach($tab_gid as $gid)
				{
					$gid=trim($gid);
					$sql="SELECT gr_login FROM conges_groupe_resp WHERE gr_gid=$gid AND gr_login!='$user_login' ";
					$ReqLog1 = requete_mysql($sql, $mysql_link, "get_tab_resp_du_user", $DEBUG);

					while ($resultat1 = mysql_fetch_array($ReqLog1))
					{
						//attention à ne pas mettre 2 fois le meme resp dans le tableau
						if (in_array($resultat1["gr_login"], $tab_resp)==FALSE)
//							$tab_resp[]=$resultat1["gr_login"];
							$tab_resp[$resultat1["gr_login"]]="present";
					}
				}
			}
		}
		if($DEBUG==TRUE) {echo "tab_resp intermediaire =\n"; print_r($tab_resp); echo "<br>\n";}

		/************************************/
		// gestion des absence des responsables :
		// on verifie que les resp sont présents, si tous absent, on cherhe les resp des resp, et ainsi de suite ....
		if($_SESSION['config']['gestion_cas_absence_responsable']==TRUE)
		{
			if($DEBUG==TRUE) {echo "gestion des absence des responsables<br>\n"; }

			// on va verifier si les resp récupérés sont absents
			$nb_present=count($tab_resp);
			foreach ($tab_resp as $current_resp => $presence )
			{
				// verif dans la base si le current_resp est absent :
				$req = "SELECT p_num
	                                     FROM conges_periode
	                                     WHERE p_login = '$current_resp'
	                                     AND p_etat = 'ok'
	                                     AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW())
	                                     AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())";
				$ReqLog_3 = requete_mysql($req, $mysql_link, "get_tab_resp_du_user", $DEBUG);
				if(mysql_num_rows($ReqLog_3)!=0)
				{
					$nb_present=$nb_present-1;
					$tab_resp[$current_resp]="absent";
				}
			}

			//si aucun resp present on recupere les resp du resp
			if($nb_present==0)
			{
				$new_tab_resp=array();
				if($DEBUG==TRUE) { echo "zero resp présent<br>\n"; }
				foreach ($tab_resp as $current_resp => $presence)
				{
					// attention ,on evite le cas ou le user est son propre resp (sinon on boucle infiniment)
					if($current_resp != $user_login)
						$new_tab_resp = array_merge  ( $new_tab_resp , get_tab_resp_du_user($current_resp, $mysql_link, $DEBUG));
				}
				$tab_resp = array_merge  ( $tab_resp, $new_tab_resp);
			}

		}
		// FIN gestion des absence des responsables :
		/************************************/
	}

	if($DEBUG==TRUE) {echo "return tab_resp =\n"; print_r($tab_resp); echo "<br>\n";}
	return $tab_resp;
}


// construit le tableau des grands responsables d'un user
// (tab des grd resp des groupes à double_valid dont le user fait partie
// le login du user est passé en paramêtre ainsi que le tableau (vide) des resp
function get_tab_grd_resp_du_user($user_login, &$tab_grd_resp, $mysql_link, $DEBUG=FALSE)
{

	// recup des resp des groupes du user
	if($_SESSION['config']['gestion_groupes']==TRUE)
	{
      $list_groups=get_list_groupes_du_user($user_login,"membre", $mysql_link, $DEBUG);
		if($DEBUG==TRUE) { echo "list_groups : <br>$list_groups<br>\n"; }

		if($list_groups!="")
		{
			$tab_gid=explode(",", $list_groups);
			foreach($tab_gid as $gid)
			{
				$gid=trim($gid);
				$sql="SELECT ggr_login FROM conges_groupe_grd_resp WHERE ggr_gid=$gid ";
				$ReqLog1 = requete_mysql($sql, $mysql_link, "get_tab_grd_resp_du_user", $DEBUG);

				while ($resultat1 = mysql_fetch_array($ReqLog1))
				{
					//attention à ne pas mettre 2 fois le meme resp dans le tableau
					if (in_array($resultat1["ggr_login"], $tab_grd_resp)==FALSE)
						$tab_grd_resp[]=$resultat1["ggr_login"];
				}
			}
		}
	}
}


function valid_ldap_user($username, $DEBUG=FALSE)
{
/* fonction utilisée avec le mode d'authentification ldap.
   En effet, si un utilisateur (enregistré dans le ldap) tente de se
connecter alors qu'il n'a pas de compte dans
   la base, il n'y a aucun message qui lui indique !

   Retourne TRUE, si tout est ok... ($username dans la table conges_users)
   False, sinon

*/
	// connexion MySQL + selection de la database sur le serveur
	$mysql_link=connexion_mysql();

	$req = "SELECT COUNT(*) FROM conges_users WHERE u_login='$username'";
	$res = requete_mysql($req, $mysql_link, "valid_ldap_user", $DEBUG);
	$cpt = mysql_fetch_array($res);
	$cpt = $cpt[0];

	if ($cpt == 0)
		return FALSE;
	else
		return TRUE;

}


// verifie si un user est responasble ou pas
// renvoit TRUE si le login est responsable dans la table conges_users, FALSE sinon.
function is_resp($login, $mysql_link, $DEBUG=FALSE)
{
	// recup de qq infos sur le user
	$select_info="SELECT u_is_resp FROM conges_users WHERE u_login='$login' ";
	$ReqLog_info = requete_mysql($select_info, $mysql_link, "is_resp", $DEBUG);

	$resultat_info = mysql_fetch_array($ReqLog_info);
	$sql_is_resp=$resultat_info["u_is_resp"];

	if($sql_is_resp=='Y')
		return TRUE;
	else
		return FALSE;
}
//modif du 13 nov 2012
// verifie si un user est gestionnaire ou pas
// renvoit TRUE si le login est gestionnaire dans la table conges_users, FALSE sinon.
function is_gest($login, $mysql_link, $DEBUG=FALSE)
{
	// recup de qq infos sur le user
	$select_info="SELECT u_is_gest FROM conges_users WHERE u_login='$login' ";
	$ReqLog_info = requete_mysql($select_info, $mysql_link, "is_gest", $DEBUG);

	$resultat_info = mysql_fetch_array($ReqLog_info);
	$sql_is_gest=$resultat_info["u_is_gest"];

	if($sql_is_gest=='Y')
		return TRUE;
	else
		return FALSE;
}

//fin modif

// verifie si un user est responasble d'un secon user
// renvoit TRUE si le $resp_login est responsable du $user_login, FALSE sinon.
function is_resp_of_user($resp_login, $user_login, $mysql_link, $DEBUG=FALSE)
{
	// recup de qq infos sur le user
	$select_info="SELECT u_resp_login FROM conges_users WHERE u_login='$user_login' ";
	$ReqLog_info = requete_mysql($select_info, $mysql_link, "is_resp_of_user", $DEBUG);

	$resultat_info = mysql_fetch_array($ReqLog_info);
	$sql_resp_login=$resultat_info["u_resp_login"];

	if($resp_login==$sql_resp_login)
		return TRUE;
	else
		return FALSE;

}



// verifie si un user est administrateur ou pas
// renvoit TRUE si le login est administrateur dans la table conges_users, FALSE sinon.
function is_admin($login, $mysql_link, $DEBUG=FALSE)
{
	// recup de qq infos sur le user
	$select_info="SELECT u_is_admin FROM conges_users WHERE u_login='$login' ";
	$ReqLog_info = requete_mysql($select_info, $mysql_link, "is_admin", $DEBUG);

	$resultat_info = mysql_fetch_array($ReqLog_info);
	$sql_is_admin=$resultat_info["u_is_admin"];

	if($sql_is_admin=='Y')
		return TRUE;
	else
		return FALSE;
}


// verifie si un administrateur est responsable de users ou pas
// renvoit TRUE si le login est responsable dans la table conges_users, FALSE sinon.
function admin_is_responsable($login, $mysql_link, $DEBUG=FALSE)
{
	// recup de qq infos sur le responsable
	$select_info="SELECT u_is_resp FROM conges_users WHERE u_login='$login' ";
	$ReqLog_info = requete_mysql($select_info, $mysql_link, "admin_is_responsable", $DEBUG);

	$resultat_info = mysql_fetch_array($ReqLog_info);
	$sql_is_resp=$resultat_info["u_is_resp"];

	if($sql_is_resp=='Y')
		return TRUE;
	else
		return FALSE;
}



// on insert une nouvelle periode dans la table periode
// retourne le num d'auto_incremente (p_num) ou 0 en cas l'erreur
function insert_dans_periode($login, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin, $nb_jours, $commentaire, $id_type_abs, $etat, $id_fermeture, $mysql_link, $DEBUG=FALSE)
{
	// Récupération du + grand p_num (+ grand numero identifiant de conges)
	$sql1 = "SELECT max(p_num) FROM conges_periode" ;
	$ReqLog1 = requete_mysql($sql1, $mysql_link, "insert_dans_periode", $DEBUG);
	if(mysql_result($ReqLog1, 0))
		$num_new_demande = mysql_result($ReqLog1, 0)+1;
	else
		$num_new_demande = 1;  // la table est vide ! le premier num sera 1
    /* _protectsql_ dpa */ 
    $commentaire = mysql_escape_string($commentaire);

	$sql2 = "INSERT INTO conges_periode
			SET p_login='$login',
			p_date_deb='$date_deb', p_demi_jour_deb='$demi_jour_deb',
			p_date_fin='$date_fin', p_demi_jour_fin='$demi_jour_fin',
			p_nb_jours='$nb_jours', p_commentaire='$commentaire',
			p_type='$id_type_abs', p_etat='$etat', ";
	if($id_fermeture!=0)
		$sql2 = $sql2." p_fermeture_id='$id_fermeture' ," ;
	if($etat=="demande")
		$sql2 = $sql2." p_date_demande=NOW() ," ;
	else
		$sql2 = $sql2." p_date_traitement=NOW() ," ;

	$sql2 = $sql2." p_num='$num_new_demande' " ;

	$result = requete_mysql($sql2, $mysql_link, "insert_dans_periode", $DEBUG);

	if($id_fermeture!=0)
		$comment_log = "saisie de fermeture num $num_new_demande (type $id_type_abs) pour $login ($nb_jours jours) (de $date_deb $demi_jour_deb à $date_fin $demi_jour_fin)";
	elseif($etat=="demande")
		$comment_log = "demande de conges num $num_new_demande (type $id_type_abs) pour $login ($nb_jours jours) (de $date_deb $demi_jour_deb à $date_fin $demi_jour_fin)";
	else
		$comment_log = "saisie de conges num $num_new_demande (type $id_type_abs) pour $login ($nb_jours jours) (de $date_deb $demi_jour_deb à $date_fin $demi_jour_fin)";
	log_action($num_new_demande, $etat, $login, $comment_log, $mysql_link, $DEBUG);

	if($result==TRUE)
		return $num_new_demande;
	else
		return 0;
}


// remplit le tableau global des jours feries a partir de la database
function init_tab_jours_feries($mysql_link, $DEBUG=FALSE)
{
	$_SESSION["tab_j_feries"]=array();

	$sql_select="SELECT jf_date FROM conges_jours_feries ";
	$res_select = requete_mysql($sql_select, $mysql_link, "init_tab_jours_feries", $DEBUG);

	while( $row = mysql_fetch_array($res_select))
	{
		$_SESSION["tab_j_feries"][]=$row["jf_date"];
	}
}

// renvoit TRUE si le jour est chomé (férié), sinon FALSE (verifie dans le tableau global $_SESSION["tab_j_feries"]
function est_chome($timestamp)
{
	$j_date=date("Y-m-d", $timestamp);
	if(isset($_SESSION["tab_j_feries"]))
		return in_array($j_date, $_SESSION["tab_j_feries"]);
	else
		return FALSE;
}


// initialise le tableau des variables de config (renvoit un tableau)
function init_config_tab($DEBUG=FALSE)
{
	include 'dbconnect.php';
	include 'version.php';
	include 'config_ldap.php';
	include 'config_CAS.php';
	$tab =array();


	/******************************************/
	//  recup des variables du SERVEUR
	if(substr(dirname ($_SERVER["SCRIPT_FILENAME"]), -7, 7) == "install")   // si on est dans le repertoire install
		$config_php_conges_document_root = substr(dirname ($_SERVER["SCRIPT_FILENAME"]), 0, strlen(dirname ($_SERVER["SCRIPT_FILENAME"]))-8) ;
	elseif(substr(dirname ($_SERVER["SCRIPT_FILENAME"]), -6, 6) == "config") // si on est dans le repertoire config
		$config_php_conges_document_root = substr(dirname ($_SERVER["SCRIPT_FILENAME"]), 0, strlen(dirname ($_SERVER["SCRIPT_FILENAME"]))-7) ;
	else
		$config_php_conges_document_root = dirname ($_SERVER["SCRIPT_FILENAME"]) ;
    // _dpa_ conservation de exemple /var/www/conges/ac3rc11
    $tab['php_conges_document_root'] = $config_php_conges_document_root ; 
    $tab['script_filename'] = $_SERVER["SCRIPT_FILENAME"] ; 
	$tab['php_conges_include_path']=$config_php_conges_document_root."/INCLUDE.PHP" ;
    // _dpa pour eviter les warning sur les directives include en erreur 
    //      du aux remontee de chemin hazardeux  
    set_include_path(get_include_path() . PATH_SEPARATOR . $config_php_conges_document_root);

	/******************************************/
	//  recup des variables de version.php
	if(isset($config_php_conges_version)) {$tab['php_conges_version']=$config_php_conges_version ;}
	if(isset($config_url_site_web_php_conges)) {$tab['url_site_web_php_conges']=$config_url_site_web_php_conges ;}

	/******************************************/
	//  recup des variables de dbconnect.php
	if(isset($mysql_serveur)) {$tab['mysql_serveur']=$mysql_serveur ;}
	if(isset($mysql_user)) {$tab['mysql_user']=$mysql_user ;}
	if(isset($mysql_pass)) {$tab['mysql_pass']=$mysql_pass ;}
	if(isset($mysql_database)) {$tab['mysql_database']=$mysql_database  ;}
	if(isset($mysql_charset)) {$tab['mysql_charset']=$mysql_charset  ;}

	/******************************************/
	//  recup des variables de la table conges_config
	$mysql_link = MYSQL_CONNECT($mysql_serveur,$mysql_user,$mysql_pass);
	if (! $mysql_link)
	{
		die("connexion_mysql() : Impossible de se connecter au serveur ".mysql_error($mysql_link));
	}
    // _ac3 
    if ( ! mysql_set_charset($mysql_charset,$mysql_link) ) {
      die("mysql_set_charset() : set_charset ".$mysql_charset.
          " en erreur ". mysql_error($mysql_link));
    };

	$dbselect   = mysql_select_db($mysql_database,$mysql_link);
	if (! $dbselect)
	{
		die("connexion_mysql() : Impossible de se connecter à la base de données ".mysql_error($mysql_link));
	}

	$sql = "SELECT conf_nom, conf_valeur, conf_type FROM conges_config";
	$req = mysql_query($sql) or die ("ERREUR : mysql_query --> $sql ".mysql_error($mysql_link));

	while ($data = mysql_fetch_array($req))
	{
		$key=$data[0];
		$value=$data[1];
		$type=$data[2];

		if($value == "FALSE")
		{
			$value = FALSE;
		}
		elseif($value == "TRUE")
		{
			$value = TRUE;
		}
		elseif($type=="path")
		{
          /* dgac/dpa 
             si la valeur commence par "/" 
                alors c'est un chemin absolu laissé en l'état
             sinon c'est un chemin relatif : on prefixe par 
                $config_php_conges_document_root */
          if ($value[0] != '/') {  
			$value = $config_php_conges_document_root."/".$value ;
          }
		}

		$tab[$key] = $value;
	}

	/******************************************/
	// recup du nom du fichier de langue ...
	// on verifie si on est dans le répertoire "install" ou ailleurs ...
	$script_path = dirname ($_SERVER['SCRIPT_NAME']);

	inculde_lang_file($tab['lang'], $DEBUG);


	/******************************************/
	//  recup des mails dans  la table conges_mail
	$sql_mail = "SELECT mail_nom, mail_subject, mail_body FROM conges_mail";
	$req_mail = mysql_query($sql_mail) or die ("ERREUR : mysql_query --> $sql_mail ".mysql_error($mysql_link));

	while ($data_mail = mysql_fetch_array($req_mail))
	{
		$mail_nom=$data_mail[0];
		$key1=$mail_nom."_sujet";
		$key2=$mail_nom."_contenu";
		$sujet=$data_mail[1];
		$corps=$data_mail[2];

		$tab[$key1]=$sujet ;
		$tab[$key2]=$corps ;
	}

	/******************************************/
	//  config_ldap.php
	if(isset($config_ldap_server)) {$tab['ldap_server']=$config_ldap_server ;}
	if(isset($config_ldap_protocol_version)) {$tab['ldap_protocol_version']=$config_ldap_protocol_version ;}
		else {$tab['ldap_protocol_version']=0 ;}
	if(isset($config_ldap_bupsvr)) {$tab['ldap_bupsvr']=$config_ldap_bupsvr ;}
	if(isset($config_basedn)) {$tab['basedn']=$config_basedn ;}
	if(isset($config_ldap_user)) {$tab['ldap_user']=$config_ldap_user ;}
	if(isset($config_ldap_pass)) {$tab['ldap_pass']=$config_ldap_pass ;}
	if(isset($config_searchdn)) {$tab['searchdn']=$config_searchdn ;}

	if(isset($config_ldap_prenom)) {$tab['ldap_prenom']=$config_ldap_prenom ;}
	if(isset($config_ldap_nom)) {$tab['ldap_nom']=$config_ldap_nom ;}
	if(isset($config_ldap_mail)) {$tab['ldap_mail']=$config_ldap_mail ;}
	if(isset($config_ldap_login)) {$tab['ldap_login']=$config_ldap_login ;}
	if(isset($config_ldap_nomaff)) {$tab['ldap_nomaff']=$config_ldap_nomaff ;}
	if(isset($config_ldap_filtre)) {$tab['ldap_filtre']=$config_ldap_filtre ;}
	if(isset($config_ldap_filrech)) {$tab['ldap_filrech']=$config_ldap_filrech ;}

	if(isset($config_ldap_filtre_complet)) {$tab['ldap_filtre_complet']=$config_ldap_filtre_complet ;}

	/******************************************/
	//  config_CAS.php
	if(isset($config_CAS_host)) {$tab['CAS_host']=$config_CAS_host ;}
	if(isset($config_CAS_portNumber)) {$tab['CAS_portNumber']=$config_CAS_portNumber ;}
	if(isset($config_CAS_URI)) {$tab['CAS_URI']=$config_CAS_URI ;}


	/******************************************/
	//  recup de qq infos sur le user
	if(isset($_SESSION['userlogin']))
	{
		$sql_user = "SELECT u_is_resp, u_is_admin FROM conges_users WHERE u_login='".$_SESSION['userlogin']."' ";
		$req_user = mysql_query($sql_user) or die ("ERREUR : mysql_query --> $sql_user ".mysql_error($mysql_link));

		if($data_user = mysql_fetch_array($req_user))
		{
			$_SESSION['is_resp']=$data_user[0] ;
			$_SESSION['is_admin']=$data_user[1] ;
		}
	}

	/******************************************/

	return $tab;
}




// Récupère le contenu d une variable $_GET / $_POST
function getpost_variable($variable, $default="")
{
   $valeur = (isset($_POST[$variable]) ? $_POST[$variable]  : (isset($_GET[$variable]) ? $_GET[$variable]   : $default));

   return   $valeur;
}


// recup TRUE si le user a "u_see_all" à 'Y' dans la table users, FALSE sinon
function get_user_see_all($login, $mysql_link, $DEBUG=FALSE)
{
	$sql="SELECT u_see_all FROM conges_users WHERE u_login='$login' ";
	$ReqLog1 = requete_mysql($sql, $mysql_link, "get_user_see_all", $DEBUG);

	if($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$see_all=$resultat1["u_see_all"];
		if($see_all=="Y")
			return TRUE;
		else
			return FALSE;
	}
	else
		return FALSE;
}


// recup dans un tableau des types de conges
function recup_tableau_types_conges($mysql_link, $DEBUG=FALSE)
{
	$tab=array();
	$sql_cong="SELECT ta_id, ta_libelle, ta_type FROM conges_type_absence WHERE ta_type='conges' ORDER BY ta_id";
	$ReqLog_cong = requete_mysql($sql_cong, $mysql_link, "recup_tableau_types_conges", $DEBUG);

	while ($resultat_cong = mysql_fetch_array($ReqLog_cong))
	{
		$id=(int)$resultat_cong['ta_id'];
		$tab[$id]= $resultat_cong['ta_libelle'];
	}
	return $tab;
}
function recup_tableau_types_conges_ordinaires($mysql_link, $DEBUG=FALSE) //modif du 14 nov 2012
{

	$tab=array();
	$sql_cong="SELECT ta_id, ta_libelle, ta_type FROM conges_type_absence WHERE (ta_type='conges' AND ta_libelle!='compte épargne temps')ORDER BY ta_id";
	$ReqLog_cong = requete_mysql($sql_cong, $mysql_link, "recup_tableau_types_conges_ordinaires", $DEBUG);

	while ($resultat_cong = mysql_fetch_array($ReqLog_cong))
	{
		$id=(int)$resultat_cong['ta_id'];
		$tab[$id]= $resultat_cong['ta_libelle'];
	}
	return $tab;
}// fin modif 14 nov 2012

// recup dans un tableau des types d'absence
function recup_tableau_types_absence($mysql_link, $DEBUG=FALSE)
{
	$tab=array();
	$sql_abs="SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type='absences' ORDER BY ta_id";
	$ReqLog_abs = requete_mysql($sql_abs, $mysql_link, "recup_tableau_types_absence", $DEBUG);

	while ($resultat_abs = mysql_fetch_array($ReqLog_abs))
	{
		$id=$resultat_abs['ta_id'];
		$tab[$id]= $resultat_abs['ta_libelle'];
	}
	return $tab;
}

// recup dans un tableau des types de conges exceptionnels
function recup_tableau_types_conges_exceptionnels($mysql_link, $DEBUG=FALSE)
{
	$tab=array();
	$sql_abs="SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type='conges_exceptionnels' ";
	$ReqLog_abs = requete_mysql($sql_abs, $mysql_link, "recup_tableau_types_conges_exceptionnels", $DEBUG);

	while ($resultat_abs = mysql_fetch_array($ReqLog_abs))
	{
		$id=$resultat_abs['ta_id'];
		$tab[$id]= $resultat_abs['ta_libelle'];
	}
	return $tab;
}

// recup dans un tableau de tableau les infos des types de conges et absences
function recup_tableau_tout_types_abs($mysql_link, $DEBUG=FALSE)
{
	$tab=array();
	if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) // on prend tout les types de conges
		$sql_cong="SELECT ta_id, ta_type, ta_libelle, ta_short_libelle FROM conges_type_absence ";
	else // on prend tout les types de conges SAUF les conges exceptionnels
		$sql_cong="SELECT ta_id, ta_type, ta_libelle, ta_short_libelle FROM conges_type_absence WHERE conges_type_absence.ta_type != 'conges_exceptionnels' ";

	$ReqLog_cong = requete_mysql($sql_cong, $mysql_link, "recup_tableau_tout_types_abs", $DEBUG);

	while ($resultat_cong = mysql_fetch_array($ReqLog_cong))
	{
		$tab_2=array();
		$id=$resultat_cong['ta_id'];
		$tab_2['type']=$resultat_cong['ta_type'];
		$tab_2['libelle']= $resultat_cong['ta_libelle'];
		$tab_2['short_libelle']= $resultat_cong['ta_short_libelle'];
		$tab[$id]=$tab_2;
	}
	return $tab;
}


// renvoit le type d'absence (conges ou absence) d'une absence
function get_type_abs($_type_abs_id, $mysql_link, $DEBUG=FALSE)
{
	$sql_abs="SELECT ta_type FROM conges_type_absence WHERE ta_id='$_type_abs_id' ";
	$ReqLog_abs = requete_mysql($sql_abs, $mysql_link, "get_type_abs", $DEBUG);

	if($resultat_abs = mysql_fetch_array($ReqLog_abs))
		return $resultat_abs['ta_type'];
	else
		return "" ;
}

// renvoit le libelle d'une absence (conges ou absence) d'une absence
function get_libelle_abs($_type_abs_id, $mysql_link, $DEBUG=FALSE)
{
	$sql_abs="SELECT ta_libelle FROM conges_type_absence WHERE ta_id='$_type_abs_id' ";
	$ReqLog_abs = requete_mysql($sql_abs, $mysql_link, "get_libelle_abs", $DEBUG);
	if($resultat_abs = mysql_fetch_array($ReqLog_abs))
		return $resultat_abs['ta_libelle'];
	else
		return "" ;
}


// recup dans un tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
function recup_tableau_conges_for_user($login, $mysql_link, $DEBUG=FALSE)
{
	// on pourrait tout faire en un seule select, mais cela bug si on change la prise en charge des conges exceptionnels en cours d'utilisation ...

	if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) // on prend tout les types de conges
		$sql_bilan = "SELECT ta_libelle, su_nb_an, su_solde FROM conges_solde_user, conges_type_absence WHERE conges_type_absence.ta_id = conges_solde_user.su_abs_id AND su_login = '$login' ORDER BY su_abs_id ASC";
	else // on prend tout les types de conges SAUF les conges exceptionnels
		$sql_bilan = "SELECT ta_libelle, su_nb_an, su_solde FROM conges_solde_user, conges_type_absence WHERE conges_type_absence.ta_type != 'conges_exceptionnels' AND conges_type_absence.ta_id = conges_solde_user.su_abs_id AND su_login = '$login' ORDER BY su_abs_id ASC";


	$ReqLog_bilan = requete_mysql($sql_bilan, $mysql_link, "recup_tableau_types_conges", $DEBUG);

	$count_num_bilan = mysql_num_rows($ReqLog_bilan);
	$tab_cong_user=array();
	while ($resultat_bilan = mysql_fetch_array($ReqLog_bilan))
	{
		$tab=array();
		$sql_id=$resultat_bilan["ta_libelle"];
		$tab['nb_an']=affiche_decimal($resultat_bilan["su_nb_an"]);
		$tab['solde']=affiche_decimal($resultat_bilan["su_solde"]);
		$tab_cong_user[$sql_id]=$tab;
	}

	return $tab_cong_user;
}


// affichage du tableau récapitulatif des solde de congés d'un user
function affiche_tableau_bilan_conges_user($login, $mysql_link, $DEBUG=FALSE)
{
	$sql_1 = "SELECT u_quotite FROM conges_users where u_login = '$login' ";
	$ReqLog_1 = requete_mysql($sql_1, $mysql_link, "affiche_tableau_bilan_conges_user", $DEBUG) ;

	$resultat_1 = mysql_fetch_array($ReqLog_1);
	$sql_quotite=$resultat_1["u_quotite"];


	// recup dans un tableau de tableaux les nb et soldes de conges d'un user
	$tab_cong_user = recup_tableau_conges_for_user($login, $mysql_link, $DEBUG);
	if($DEBUG==TRUE) { echo"tab_cong_user =<br>\n"; print_r($tab_cong_user); echo "<br>\n"; }

	// recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
	if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE)
	{
	  $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($mysql_link, $DEBUG);
	}
	if($DEBUG==TRUE)
	{
	  echo"tab_type_conges_exceptionnels =<br>\n";
	  print_r($tab_type_conges_exceptionnels);
	  echo "<br>\n";
	}

	// taille du tableau à afficher
	$taille_tableau_bilan=100 + (150 * count($tab_cong_user));

	echo "<table cellpadding=\"2\" width=\"$taille_tableau_bilan\" class=\"tablo\">\n";
	echo "<tr align=\"center\"><td class=\"titre\">".$_SESSION['lang']['divers_quotite']."</td>" ;
/*	foreach($tab_cong_user as $id => $val)
	  if (($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) && ($id == "récupération")) {
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_solde_maj']." ".$id."</td>" ;
	  } else {
		echo "<td class=\"titre\">".$id."/ ".$_SESSION['lang']['divers_an_maj']."</td><td class=\"titre\">".$_SESSION['lang']['divers_solde_maj']." ".$id."</td>" ;
	  }
*/

    foreach($tab_cong_user as $id => $val)
	{
	  if (($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) && ((in_array($id,$tab_type_conges_exceptionnels))))
	  {
	    echo "<td class=\"titre\">".$_SESSION['lang']['divers_solde_maj']." ".$id."</td>" ;
	  }
	  else
	  {
	    echo "<td class=\"titre\">".$id."/ ".$_SESSION['lang']['divers_an_maj']."</td><td class=\"titre\">".$_SESSION['lang']['divers_solde_maj']." ".$id."</td>" ;
 	  }
	}
	echo "</tr>\n";

	echo "<tr align=\"center\">\n";
	echo "<td>$sql_quotite%</td>\n";
	foreach($tab_cong_user as $id => $val)
/*	  if (($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) && ($id == "récupération")) {
		echo "<td bgcolor=\"#FF9191\"><b>".$val['solde']."</b></td>\n";
	  } else {
		echo "<td><b>".$val['nb_an']."</b></td><td bgcolor=\"#FF9191\"><b>".$val['solde']."</b></td>\n";
	  }
*/ 
	{
	  if (($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) && ((in_array($id,$tab_type_conges_exceptionnels))))
	  {
	    echo "<td bgcolor=\"#FF9191\"><b>".$val['solde']."</b></td>\n";
	  }
	  else
	  {
        echo "<td><b>".$val['nb_an']."</b></td><td bgcolor=\"#FF9191\"><b>".$val['solde']."</b></td>\n";
 
 	  }
	}

	echo "</tr>\n";
	echo "</table>\n";
}


// renvoit un tableau de tableau contenant les informations du user
// renvoit FALSE si erreur
function recup_infos_du_user($login, $list_groups_double_valid, $mysql_link, $DEBUG=FALSE)
{
	$tab=array();

	$sql = "SELECT u_login, u_nom, u_prenom, u_is_resp, u_resp_login, u_is_admin, u_see_all, u_passwd, u_quotite, u_email, u_has_int_calendar, u_is_gest FROM conges_users " .
			"WHERE u_login='$login' ";

	$ReqLog = requete_mysql($sql, $mysql_link, "recup_infos_du_user", $DEBUG) ;

	if($resultat = mysql_fetch_array($ReqLog))
	{
      //      print_r($resultat);
		$tab_user=array();
		$tab_user['login']=$resultat["u_login"];;
		$tab_user['nom']=$resultat["u_nom"];
		$tab_user['prenom']=$resultat["u_prenom"];
		$tab_user['is_resp']=$resultat["u_is_resp"];
		$tab_user['resp_login']=$resultat["u_resp_login"];
		$tab_user['is_admin']=$resultat["u_is_admin"];
		$tab_user['see_all']=$resultat["u_see_all"];
		$tab_user['passwd']=$resultat["u_passwd"];
		$tab_user['quotite']=$resultat["u_quotite"];
		$tab_user['email']=$resultat["u_email"];
		$tab_user['has_int_calendar']=$resultat["u_has_int_calendar"];
	        $tab_user['is_gest']=$resultat["u_is_gest"];
		$tab_user['conges']=recup_tableau_conges_for_user($login, $mysql_link, $DEBUG);

		$tab_user['double_valid'] = "N";

		// on regarde ici si le user est dans un groupe qui fait l'objet d'une double validation
		if($_SESSION['config']['double_validation_conges']==TRUE)
		{
//			$list_groups_double_valid=get_list_groupes_double_valid_du_resp($login, $mysql_link, $DEBUG);

			if($list_groups_double_valid!="") // si $resp_login est responsable d'au moins un groupe a double validation
			{
				$sql="SELECT gu_login FROM conges_groupe_users WHERE gu_login='$login' AND gu_gid IN ($list_groups_double_valid) ORDER BY gu_login ";
				$ReqLog1 = requete_mysql($sql, $mysql_link, "recup_infos_du_user", $DEBUG);

				if(mysql_num_rows($ReqLog1)!=0)
					$tab_user['double_valid'] = "Y";
			}
		}
		return $tab_user ;
	}
	else
		return FALSE;
}


// renvoit un tableau de tableau contenant les informations de tous les users
function recup_infos_all_users($mysql_link, $DEBUG=FALSE)
{
	$tab=array();

	$list_groupes_double_validation=get_list_groupes_double_valid($mysql_link, $DEBUG);
	if($DEBUG==TRUE) { echo "list_groupes_double_validation :<br>\n"; print_r($list_groupes_double_validation); echo "<br><br>\n";}

	//$sql = "SELECT u_login FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_login";
	$sql = "SELECT u_login FROM conges_users WHERE u_login!='conges' AND u_login!='admin' ORDER BY u_nom";

	$ReqLog = requete_mysql($sql, $mysql_link, "recup_infos_all_users", $DEBUG) ;

	while ($resultat = mysql_fetch_array($ReqLog))
	{
		$tab_user=array();
		$sql_login=$resultat["u_login"];

		$tab[$sql_login] = recup_infos_du_user($sql_login, $list_groupes_double_validation, $mysql_link, $DEBUG);
	}

	return $tab ;
}


// renvoit un tableau de tableau contenant les informations de tous les users dont $login est responsable
function recup_infos_all_users_du_resp($login, $mysql_link, $DEBUG=FALSE)
{
	$tab=array();

	// recup de la liste de tous les users du resp ...
	$list_all_users_du_resp = get_list_all_users_du_resp($login, $mysql_link, $DEBUG);
	if($DEBUG==TRUE) { echo "list_all_users_du_resp :<br>\n"; print_r($list_all_users_du_resp); echo "<br><br>\n";}

	// recup de la liste des groupes à double validation, dont $login est responsable
	// (servira à dire pour chaque user s'il est dans un de ces groupe ou non , donc s'il fait l'objet d'une double valid ou non )
	$list_groups_double_valid_du_resp=get_list_groupes_double_valid_du_resp($login, $mysql_link, $DEBUG);
	if($DEBUG==TRUE) { echo "list_groups_double_valid :<br>\n"; print_r($list_groups_double_valid_du_resp); echo "<br><br>\n";}

	if(strlen($list_all_users_du_resp)!=0)
	{
		$tab_users_du_resp=explode(",", $list_all_users_du_resp);
		foreach($tab_users_du_resp as $current_login)
		{
			$current_login = trim($current_login);
			$current_login = trim($current_login, "\'");  // on enleve les quotes qui ont été ajouté lors de la creation de la liste

			$tab[$current_login] = recup_infos_du_user($current_login, $list_groups_double_valid_du_resp, $mysql_link, $DEBUG);
		}
	}

	return $tab ;
}


// renvoit un tableau de tableau contenant les informations de tous les users dont $login est GRAND responsable
function recup_infos_all_users_du_grand_resp($login, $mysql_link, $DEBUG=FALSE)
{
	$tab=array();

	$list_groups_double_valid=get_list_groupes_double_valid_du_grand_resp($login, $mysql_link, $DEBUG);
	if($DEBUG==TRUE) { echo "list_groups_double_valid :<br>\n"; print_r($list_groups_double_valid); echo "<br><br>\n";}

	if($list_groups_double_valid!="")
	{
		// recup de la liste des users des groupes de la liste $list_groups_double_valid
		$sql_users = "SELECT DISTINCT(gu_login) FROM conges_groupe_users, conges_users WHERE gu_gid IN ($list_groups_double_valid) AND gu_login=u_login ORDER BY u_nom; ";
		$ReqLog_users = requete_mysql($sql_users, $mysql_link, "recup_infos_all_users_du_grand_resp", $DEBUG) ;

		$list_all_users_dbl_valid="";
		while ($resultat_users = mysql_fetch_array($ReqLog_users))
		{
			$current_login=$resultat_users["gu_login"];
			if($list_all_users_dbl_valid=="")
				$list_all_users_dbl_valid="'$current_login'";
			else
				$list_all_users_dbl_valid=$list_all_users_dbl_valid.", '$current_login'";
		}

		if($list_all_users_dbl_valid!="")
		{
			$tab_users_du_resp=explode(",", $list_all_users_dbl_valid);
			foreach($tab_users_du_resp as $current_login)
			{
				$current_login = trim($current_login);
				$current_login = trim($current_login, "\'");  // on enleve les qote qui on été ajouté lors de la creation de la liste

				$tab[$current_login] = recup_infos_du_user($current_login, $list_groups_double_valid, $mysql_link, $DEBUG);
			}
		} //if($list_all_users_dbl_valid!="")
	} //if($list_all_users_dbl_valid!="")

	return $tab ;
}


//
// cree un tableau à partir des valeurs du enum(...) d'un champ mysql (cf structure des tables)
//    $table         = nom de la table sql
//    $column        = nom du champ sql
function get_tab_from_mysql_enum_field($table, $column, $mysql_link, $DEBUG=FALSE)
{

   $tab=array();
   $req_enum = "DESCRIBE $table $column";
   $res_enum = requete_mysql($req_enum, $mysql_link, "affiche_select_from_mysql_enum_field", $DEBUG);

   while ($row_enum = mysql_fetch_array($res_enum))
   {
      $sql_type=$row_enum['Type'];
      // exemple : enum('autre','labo','fonction','personne','web', ....
      //echo "$sql_type<br>\n";
      $liste_enum = strstr($sql_type, '(');
      $liste_enum = substr($liste_enum, 1);    // on vire le premier caractere
      $liste_enum = substr($liste_enum, 0, strlen($liste_enum)-1);    // on vire le dernier caractere
      //echo "$liste_enum<br>\n";
      $option = strtok($liste_enum,"','");
      while ($option)
      {
         $tab[]=$option;
         $option = strtok("','");
      }
   }

   return $tab;
}



//
// Affichage d'un SELECT de formulaire web à partir des valeurs du enum(...) d'un champ mysql (cf structure des tables)
//    $table         = nom de la table sql
//    $column        = nom du champ sql
//    $form_field    = nom du champ du formulaire (variable dont on va exploiter la valeur)
//    $default_value = valeur par défaut
//    $style         = style à appliquer à la liste
function affiche_select_from_mysql_enum_field($table, $column, $form_field, $default_value, $style, $mysql_link, $DEBUG=FALSE)
{

   $tab_enum = get_tab_from_mysql_enum_field($table, $column, $mysql_link, $DEBUG);

   if ($style  == "")
   {
      echo "<SELECT NAME=$form_field>\n";
   }
   else
   {
      echo "<SELECT NAME=$form_field STYLE=\"$style\">\n";
   }

   foreach($tab_enum as $option)
   {
         if($option==$default_value)
            echo "<OPTION selected>$option</OPTION>\n";
         else
            echo "<OPTION>$option</OPTION>\n";
   }

   echo "</SELECT>\n";

}


// recup l'id de la derniere absence (le max puisque c'est un auto incrément)
function get_last_absence_id($mysql_link, $DEBUG=FALSE)
{
   $req_1="SELECT MAX(ta_id) FROM conges_type_absence ";
   $res_1 = requete_mysql($req_1, $mysql_link, "get_last_absence_id", $DEBUG);
   $row_1 = mysql_fetch_row($res_1);
   if(!$row_1)
      return 0;     // si la table est vide, on renvoit 0
   else
      return $row_1[0];

}



// execute sequentiellement les requètes d'un fichier .sql
function execute_sql_file($file, $mysql_link, $DEBUG=FALSE)
{
	// lecture du fichier SQL
	// et execution de chaque ligne ....
	$lines = file ($file);
	$sql_requete="";
	foreach ($lines as $line_num => $line)
	{
		$line=trim($line);
	    if( (substr($line, 0, 1)!="#") && ($line!="") )  //on ne prend pas les lignes de commentaire
	    {
			$sql_requete = $sql_requete.$line ;
			if(substr($sql_requete, -1, 1)==";") // alors la requete est finie !
			{
				if($DEBUG==TRUE)
					echo "$sql_requete<br>\n";
				$result = requete_mysql($sql_requete, $mysql_link, "execute_sql_file", $DEBUG);
				$sql_requete="";
			}
	    }
	}
	return TRUE;

}


function affiche_bouton_retour($session, $DEBUG=FALSE)
{
	// Bouton de retour : différent suivant si on vient des pages d'install ou de l'appli
	// $_SESSION['from_config'] est initialisée dans install/index
	if(isset($_SESSION['from_config']) && ($_SESSION['from_config']==TRUE))
		echo "<center><a href=\"".$_SESSION['config']['URL_ACCUEIL_CONGES']."/config/?session=$session\">".$_SESSION['lang']['form_retour']."</a></center>\n";
	else
	{
		echo "<form action=\"\" method=\"POST\">\n";
		echo "<center><input type=\"button\" value=\"".$_SESSION['lang']['form_close_window']."\" onClick=\"javascript:window.close();\"></center>\n";
		echo "</form>\n";
	}
}





// verif des droits du user à afficher la page qu'il demande (pour éviter les hacks par bricolage d'URL)
// verif_droits_user($session, "is_admin", $DEBUG);
function verif_droits_user($session, $niveau_droits, $DEBUG=FALSE)
{
	if($DEBUG==TRUE) { print_r($_SESSION); echo "<br><br>\n"; }

	// verif si $_SESSION['is_admin'] ou $_SESSION['is_resp'] =="N"
	if($_SESSION[$niveau_droits]=="N")
	{
		// on recupere les variable utiles pour le suite :
		$url_accueil_conges = $_SESSION['config']['URL_ACCUEIL_CONGES'] ;

		$lang_divers_acces_page_interdit = $_SESSION['lang']['divers_acces_page_interdit'];
		$lang_divers_user_disconnected   = $_SESSION['lang']['divers_user_disconnected'];
		$lang_divers_veuillez            = $_SESSION['lang']['divers_veuillez'];
		$lang_divers_vous_authentifier   = $_SESSION['lang']['divers_vous_authentifier'];

		// on delete la session et on renvoit sur l'authentification (page d'accueil)
		session_delete($session);

		// message d'erreur !
		echo "<center>\n";
		echo "<font color=\"red\">$lang_divers_acces_page_interdit</font><br>$lang_divers_user_disconnected<br>\n";
		echo "$lang_divers_veuillez <a href='$url_accueil_conges/index.php' target='_top'> $lang_divers_vous_authentifier .</a>\n";
		echo "</center>\n";

		exit;
	}

}


// on lit le contenu du répertoire lang et on parse les nom de ficher (ex lang_fr_francais.php)
function affiche_select_from_lang_directory($select_name="lang")
{
	if(is_dir("lang"))
		$lang_dir = "lang/";
	elseif(is_dir("../install/lang"))
		$lang_dir = "../install/lang/";
	else
		$lang_dir = "install/lang/";


	$php_vers= (int) substr(phpversion(), 0, 1); // recup version de php
	if($php_vers>=5)
	{
		$tab_files = scandir($lang_dir);
	}
	else
	{
		$dh  = opendir($lang_dir);
		while (false !== ($filename = readdir($dh)))
		{
		    $tab_files[] = $filename;
		}
		sort($tab_files);
	}

	echo "<select name=$select_name>\n";

//	if($DEBUG==TRUE) { print_r($tab_files); echo "<br>\n"; }
	foreach($tab_files as $file)
	{
		if(eregi("^lang_.+_.+.php$", $file))
		{
			$chaine_1=explode(".", $file);
			$chaine_2=explode("_", $chaine_1[0]);
			if($chaine_2[1]=="fr")
				echo "<option value=\"".$chaine_2[1]."\" selected >".$chaine_2[1]." / ".$chaine_2[2]."</option>\n";
			else
				echo "<option value=\"".$chaine_2[1]."\">".$chaine_2[1]." / ".$chaine_2[2]."</option>\n";
		}
	}
	echo "</select>\n";
}

// on insert les logs des periodes de conges
// retourne TRUE ou FALSE
function log_action($num_periode, $etat_periode, $login_pour, $comment, $mysql_link, $DEBUG=FALSE)
{
  if(isset($_SESSION['userlogin'])) {
      $user = $_SESSION['userlogin'] ;
  }	else {
      $user = "inconnu";
  }
  $comment = mysql_escape_string($comment);

  $sql = "INSERT INTO conges_logs
		SET log_p_num='$num_periode',
			log_user_login_par='$user',
			log_user_login_pour='$login_pour',
			log_etat='$etat_periode',
			log_comment='$comment',
			log_date=NOW() " ;
  $result = requete_mysql($sql, $mysql_link, "log", $DEBUG);

  return $result;
}



// fonction qui determine le fichier de langue et fait un include de ce fichier quelque soit l'endroit où on se trouve (chemin)
// le parametre $lang est "fr" ou "es", etc ...
// on utilise plus la fonction php "glob()" qui ne fonctionne pas chez certains hebergeurs (free.fr)
function inculde_lang_file($lang, $DEBUG=FALSE)
{
	if($DEBUG==TRUE) { echo "fonction inculde_lang_file() : lang : $file<br> \n";}

	// test si on est dans "install"
	if(is_dir("lang"))  // test si on est dans "install"
		$dir= "lang/";
	elseif(is_dir("install/lang"))   // test si on est à la racine
		$dir= "install/lang/";
	else    // alors on est dans un autre répertoire ...
		$dir= "../install/lang/";


    if($dh=opendir($dir))
    {
	    while(($file = readdir($dh)) !== false)
	    {
			if($DEBUG==TRUE) { echo "file : $file<br> \n";}
	        if(ereg("lang_".$lang."_.+.php", $file))
	        {
	        	$lang_file=$dir.$file;
	        }
	    }
    }
    if($DEBUG==TRUE) { echo "lang_file : $lang_file<br> \n";}
    include($lang_file);
}


// remplit le tableau global des jours feries a partir de la database
function init_tab_jours_fermeture($user, $mysql_link, $DEBUG=FALSE)
{
	$_SESSION["tab_j_fermeture"]=array();

    /* 	$sql_select="SELECT DISTINCT jf_date FROM conges_jours_fermeture, conges_groupe_users WHERE gu_login='$user' AND gu_gid=jf_gid"; */
  /* selection des jours de fermeture pour tous et jours de fermeture pour les groupes dont 
     le user est membre */ 
    $sql_select="SELECT DISTINCT jf_date FROM conges_jours_fermeture, conges_groupe_users WHERE (gu_login='$user' AND conges_groupe_users.gu_gid=conges_jours_fermeture.jf_gid AND conges_groupe_users.gu_nature='membre') OR 
conges_jours_fermeture.jf_gid = 0";
	$res_select = requete_mysql($sql_select, $mysql_link, "init_tab_jours_fermeture", $DEBUG);

	while( $row = mysql_fetch_array($res_select))
	{
		$_SESSION["tab_j_fermeture"][]=$row["jf_date"];
	}
}

// renvoit TRUE si le jour est fermé (fermeture), sinon FALSE (verifie dans le tableau global $_SESSION["tab_j_fermeture"]
function est_ferme($timestamp)
{
	$j_date=date("Y-m-d", $timestamp);
	if(isset($_SESSION["tab_j_fermeture"]))
		return in_array($j_date, $_SESSION["tab_j_fermeture"]);
	else
		return FALSE;
}



// verifie si un user est absent ou pas
// renvoit TRUE si le login est absent, FALSE sinon.
function is_absent($login, $mysql_link, $DEBUG=FALSE)
{
	$req = "SELECT p_login FROM conges_periode
				WHERE p_login=' $login'
						AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW())
						AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())";
	$res = requete_mysql($req, $mysql_link, "is_absent", $DEBUG);
	if(mysql_num_rows($res)==0)
		return TRUE;
	else
		return FALSE;
}

//fonction qui test si l'utilisateur est dans un groupe
// renvoit TRUE ou FALSE
function is_user_in_group($login, $mysql_link, $DEBUG=FALSE)
{
    $sql="SELECT * FROM conges_groupe_users WHERE gu_login LIKE '$login'";
    $ReqLog1 = requete_mysql($sql, $mysql_link, "?", $DEBUG); // COMMENTAIRE  A REFAIRE

    if(mysql_num_rows($ReqLog1)!=0)
        return TRUE;
    else
        return FALSE;
}

// _intcalendar 
// boolean fonction : retourne true si le mode integration conge calendar est valide
// false sinon  
function has_int_calendar_mode($ulogin,$dblink) 
{
  // echo "<pre>has_int_calendar_mode ".$_SESSION['config']['int_calendar']."</pre>" ; 
  if (array_key_exists('int_calendar', $_SESSION['config'])) {
    if  ($_SESSION['config']['int_calendar'] == "1") {
      $sql_req_hasintcalendar = "select u_has_int_calendar from conges_users where u_login='".$ulogin."'" ;
      // echo "<pre>".$sql_req_hasintcalendar."</pre>" ; 
      $req_hic = mysql_query($sql_req_hasintcalendar, $dblink) ;
      if ($req_hic) {
        $lus = mysql_fetch_row($req_hic) ;
        $hasintcalendar = $lus[0] ; 
        if ($hasintcalendar == 'Y') {
          return TRUE ; 
        } else {
          return FALSE ; 
        }
      } else {
      return FALSE ; 
      }
    } else {
      return FALSE ; 
    }
  } else {
    return FALSE ; 
  }
}

// labs_as_calendar function qui retourne un array
// (calendar_item as ical/ics string , calendar_item name)  
// a partir des donnees de base d'une absence 
// $method = "REQUEST" ou "CANCEL" 
function abs2calendar_item($applitag,$subjectlogin,$dest_email,$method,$nperiode,
               $pdate_deb,$p_demi_jour_deb,
               $pdate_fin,$p_demi_jour_fin,$nb_jours,$commentaire,$type_absence)
{
  // dummy code 
  // $cid_string = implode(", ", 
  //      array($applitag, $dest_login,$dest_email, 
  //            $nperiode,dateconge2ics_deb($pdate_deb,$p_demi_jour_deb),
  //            dateconge2ics_fin($pdate_fin,$p_demi_jour_fin),
  //            $nb_jours,$commentaire,$type_absence) );
  // $cid_name = "calendar_item" ; 
  // return array ($cid_string, $cid_name); 

  // corriger erreur $dest_email > < en debut ou fin ; 
  /* date_default_timezone_get() n'existe que depuis PHP 5.1
  if(function_exists("date_default_timezone_get"))   
    $DTSTAMP=date("Ymd").date_default_timezone_get();
  else
    $DTSTAMP=date("Ymd")."T142816Z";    // copier depuis un fichier ical
  */
  // ac v3 (suite a erreur leve sur client leger) 
  // $DTSTAMP=str_replace('-','',date("c")) ;  
  // 20040212T15:19:21+00:00 
  // $DTSTAMP = date("Ymd")."T".date("His").date("P") ; 
  $DTSTAMP = date("Ymd")."T".gmdate("His")."Z" ; 
  // DTSTAMP:20020603T221548Z
  $ci_id = $subjectlogin."-".$nperiode ; 
  $uid   = $applitag."-".$ci_id ;  

  // <<echappement>> des caracteres : en - ds les champs $applitag, $type_absence
  // $commentaire
  $applitag = str_replace(":", "-", $applitag);
  $type_absence = str_replace(":", "-", $type_absence);
  $commentaire = str_replace(":", "-", $commentaire);
  // construction calendar item 
  $ci_string = "BEGIN:VCALENDAR\n" ;
  $ci_string .= "PRODID:-//".$applitag."\n" ; 
  $ci_string .= "VERSION:2.0\n\n" ; 
  $ci_string .= "BEGIN:VEVENT\n" ;
  $ci_string .= "DTSTAMP:".$DTSTAMP."\n" ; 
  $ci_string .= "ORGANIZER;CN=".$subjectlogin.":MAILTO:".$dest_email."\n" ;
  $ci_string .= "CREATED:".$DTSTAMP."\n" ;
  $ci_string .= "UID:".$uid."\n" ;
  $ci_string .= "SEQUENCE:0\n" ;
  $ci_string .= "LAST-MODIFIED:".$DTSTAMP."\n" ; 
  $ci_string .= "DESCRIPTION:" ; 
  if ($method == "CANCEL") {
    $ci_string .= " (annulation) " ; // prefixe annulation ds la description 
  };
  $ci_string .= $commentaire."\n" ; 
  // ligne suivant commence par " " , est donc une continuation de DESCRIPTION:
  $ci_string .= " ". "- conges type (".$type_absence.") - uid(".$uid.")\n" ;
  $ci_string .= "SUMMARY:";
  if ($method == "CANCEL") {
    $ci_string .= " (annulation) " ; // prefixe annulation ds le titre
  };
  $ci_string .= $commentaire." (".$type_absence.")\n" ;
  $ci_string .= "CLASS:PUBLIC\n" ;
  $ci_string .= "PRIORITY:1\n" ;
  if ($method == "REQUEST") { 
    $ci_string .= "METHOD:REQUEST\n" ;
    $ci_string .= "STATUS:CONFIRMED\n" ;
  } elseif ($method == "CANCEL") {
    $ci_string .= "METHOD:CANCEL\n" ;
    $ci_string .= "STATUS:CANCELLED\n" ;
  } else {
    // cas d'erreur a traiter 
  }   
  $ci_string .= "DTSTART:".dateconge2ics_deb($pdate_deb,$p_demi_jour_deb)."\n" ;
  $ci_string .= "DTEND:".dateconge2ics_fin($pdate_fin,$p_demi_jour_fin)."\n" ;
  $ci_string .= "TRANSP:OPAQUE\n" ;
  $ci_string .= "END:VEVENT\n\n" ;
  $ci_string .= "END:VCALENDAR\n" ;
  // todo 
  // STATUS:CONFIRMED   CATEGORIES:Vacation
  $ci_name = $ci_id . ".ics" ; 
  if(FALSE){
	echo"<pre>abs2calendar_item: string=".$ci_string."</pre>";
	echo"<pre>name=".$ci_name."</pre>";
        };
  return array($ci_string, $ci_name); 

}
// convertit 2012-09-07, am,   en 20120907T090000 
// dateconge2ics_deb pour une date de debut de demi-journee 
// dateconge2ics_fin pour une date de fin de demi-journee 
function dateconge2ics_deb($datec, $ampm) 
{
  //  $ldatec = explode("-", $datec) ;
  // $sdate = join("", $ldatec);
  if ($ampm == "am") {
    $shour = $_SESSION['config']['hdeb_periode_am'] ; // "090000" 
  } elseif ($ampm == "pm") {
    $shour = $_SESSION['config']['hdeb_periode_pm'] ; // "140000" 
  } else {
    // cas erreur 
    return "errordate" ; 
  }; 
  return date2ics($datec) . "T". $shour ; 
}

function dateconge2ics_fin($datec, $ampm) 
{
  //  $ldatec = explode("-", $datec) ;
  // $sdate = join("", $ldatec);
  if ($ampm == "am") {
    $shour = $_SESSION['config']['hfin_periode_am'] ; // "130000"
  } elseif ($ampm == "pm") {
    $shour = $_SESSION['config']['hfin_periode_pm'] ; // "180000" 
  } else {
    // cas erreur 
    return "errordate" ; 
  }; 
  return date2ics($datec) . "T". $shour ; 
}

function date2ics($datec) 
{
  return str_replace('-','',$datec) ;
}

// moteur pour utilisation en web service 
// retourne booleen + $comment comme message si depassement du solde + 
// $demencours $soldeuser  
function verif_solde_user_engine($user_login, $by="user",$is_solde_tjs_pos,
                                 $type_conges,$nb_jours,&$comment,&$demencours,&$soldeuser,
                                 $mysql_link, $DEBUG=FALSE)
{
	$verif = TRUE;
    $soldeuser = -1 ; // sera retourne pour le cas d'1 absence 
	/* on ne controle le solde que si  absences de type conges 
       (conges avec solde annuel) */ 
    // error_log("_vsue ".$type_conges); 
	if ( get_type_abs($type_conges, $mysql_link, $DEBUG)=="conges" )
	{
		// recup du solde de conges type $type_conges login $user_login
		$select_solde="SELECT su_solde FROM conges_solde_user 
WHERE su_login='$user_login' AND su_abs_id=$type_conges " ;
		$ReqLog_solde_conges = requete_mysql($select_solde, $mysql_link, 
                                             "verif_solde_user", $DEBUG);

		$resultat_solde = mysql_fetch_array($ReqLog_solde_conges);
		$sql_solde_user = $resultat_solde["su_solde"];
        $soldeuser = $sql_solde_user ; 
  /* recup du nombre de jours de conges type $type_conges login $user_login 
     qui sont à valider par son resp ou le grd resp  */ 
		$select_solde_a_valider="SELECT SUM(p_nb_jours) FROM conges_periode 
WHERE p_login='$user_login' AND p_type=$type_conges AND (p_etat='demande' OR  
p_etat='valid') " ;
		$ReqLog_solde_conges_a_valider = requete_mysql(
            $select_solde_a_valider, $mysql_link, "verif_solde_user", $DEBUG);

		$resultat_solde_a_valider = mysql_fetch_array($ReqLog_solde_conges_a_valider);
		$sql_solde_user_a_valider = $resultat_solde_a_valider["SUM(p_nb_jours)"];
		if ($sql_solde_user_a_valider == NULL ) {
			$sql_solde_user_a_valider = 0;
        }
        $demencours = $sql_solde_user_a_valider ;

		// vérification du solde de jours de type $type_conges
		if ($sql_solde_user < $nb_jours+$sql_solde_user_a_valider)
		{  
          $comment = $_SESSION['lang']['verif_solde_erreur_part_1']." (". (float)$nb_jours .") ".
$_SESSION['lang']['verif_solde_erreur_part_2']." (". (float)$sql_solde_user .") ".
$_SESSION['lang']['verif_solde_erreur_part_3']." (" . (float)$sql_solde_user_a_valider . "))";

          if ( $by=="user" && $is_solde_tjs_pos ) { 
            /* depassement interdit uniquement si   
               - modif par user (si saisie par resp pas de controle)
               - si booleen ['config']['solde_toujours_positif'] est vrai */ 

            $verif = FALSE;
          } else {
            $comment = "warning(".$comment.")" ; 
          }
		}
	}

	return $verif;
}
// typical usage referer2rootpath($_SERVER sur 
// [REQUEST_SCHEME] http 
// [SERVER_NAME]    forge-dev2.sigp.aviation-civile.gouv.fr 
// [REQUEST_URI]    /conges/ac3rc11/index.php
// pour obtenir http://forge-dev2.sigp.aviation-civile.gouv.fr/conges/ac3rc11/

function referer2rootpath($request_scheme,$server_addr,$request_uri) {
  $rootpath = "" ; 
  $lruri = explode("/",$request_uri);
  if (end($lruri) == "index.php") { 
      array_pop($lruri) ;
      array_push($lruri,"") ; // trailing slash 
    };
  $rootpath = $request_scheme."://".$server_addr.implode("/",$lruri);
  return $rootpath ; 
}
function attic_referer2rootpath_v1($http_referer) {
  $rootpath = "" ; 
  if (preg_match('/^([^:]+):\/\/(.+)$/',$http_referer,$match) ) {
    $rootpath = $match[1]."://" ; 
    $luri = explode("/",$match[2]);
    if (end($luri) == "index.php") { 
      array_pop($luri) ;
      array_push($luri,"") ; // trailing slash 
    };
    $rootpath .= implode("/",$luri); 
  }
  // $slast = $luri[sizeof($luri) - 1 ] ; // avant dernier = dernier repertoire
  // $lret = array($rootpath,$slast) ;  
  return $rootpath ; 
}

// fonction de gestion des periodes de conges 
// outil trace 
/*    echo "<pre>\n"; 
    print_r();
    echo "</pre>\n";   */ 


function dayindateinterval($number) {
  $di = new DateInterval('P'.$number.'D');
  /*  echo "<pre>\n" ;  
  print_r($di) ; 
  echo "</pre>\n" ; */ 
  return $di ; 
}
/* get_existing_type extrait la demi-journee et le type de conge 
 pour la demie-journee opposee à  $opppartday */ 
function get_existing_type($etype,$opppartday) {
  $letype = explode('-',$etype);
  // on recherche les z-daypart1-type1-daypart2-type2
  if (count($letype)>=5) { 
    if ($opppartday == "am") { 
      $partday = "pm" ;
      $isearch = 4 ; 
    } else if ($opppartday == "pm") { 
      $partday = "am" ;
      $isearch = 2 ; 
    } else { // error
      error_log("get_existing_type: wrong opppartday"); 
      return NULL ;
    } 
    $lret = array($partday,$letype[$isearch]); 
    return $lret ; 
  } else {  
    return NULL ; 
  }
} 


// retourne infos vacancy period , necessaire pour parametrer affichage
// des jquery::datepicker via fonction javascript : highlightDays
function get_vperiod($user, $mysql_link, $dmindate, $dmaxdate, $DEBUG=FALSE)
// function get_vperiod($user, $mysql_link, $DEBUG=FALSE)
{
  global $aday,$tartt,$tjlppartday;
  // _dpa_date : faire appel a conges_get_date()
  $vper = array(
       "currentdate" => conges_get_date_fmt1(), 
       "mindate" => $dmindate->format('Y-m-d'),
       "maxdate" => $dmaxdate->format('Y-m-d'),
       "period" => array(),
       "weekend"=> array( "days"=> array(0,6),  "legend"=>$_SESSION['lang']['saisie_conges_lc_weekend'] )); 
  /* example */ 
  $vper_2 = array(
   "mindate" => "2014-11-01",
   "maxdate" => "2015-02-28",
   "period" => array( 
        "yyyy-mm-dd" => array(
           "type" => "" , 
/* parmi 
z-da-pubhol, z-da-we , z-da-offval , z-da-offdem , z-da-jlp ,
z-am--pm-offval, z-am--pm-offdem, z-am--pm-jl, 
z-am-offval-pm-,  z-am-offval-pm-offdem ,  z-am-offval-pm-jl ,
z-am-offdem-pm-,  z-am-offdem-pm-offval , z-am-offdem-pm-jl ,
z-am-jl-pm- , z-am-jl-pm-offva ,  z-am-jl-pm-offdem ,
*/
           "legend" => "legend" )
                      )
                  );            

/* tables visitees 
   conges_jours_feries   
   conges_jours_fermeture, conges_groupe_users  
   conges_artt pour programmation récurrente 
   conges_echange_rtt pour ajustement / programmation 
   conges_periode pour conges valid et dema 
   on collecte l'information du moins important au plus important, 
   le dernier ecrit faisant fois ; $vper['period'] collecte cela 
*/ 
  // $lrvp = get_recurrentvacancy($user, $mysql_link, $dmindate, $dmaxdate) ; 
  $lrvp = get_recurrentvacancy_with_update($user, $mysql_link, $dmindate, $dmaxdate) ; 

  // peuplement de vper avec lrvp en faisant bonne association 
  foreach($lrvp as $lday => $lval) {
    //     echo "lday=".$lday."lval=".$lval."\n" ; 
    $vper['period'][$lday] = array(
        "type" => $tartt[$lval],
        "legend" => $_SESSION['lang']['saisie_conges_lc_jlp'] ); 
  }
  /* selection des jours de fermeture pour tous et jours de fermeture pour les groupes dont 
     le user est membre */ 
  $cgfe_select = "SELECT DISTINCT jf_date FROM conges_jours_fermeture, conges_groupe_users 
WHERE (gu_login='$user' AND conges_groupe_users.gu_gid=conges_jours_fermeture.jf_gid AND conges_groupe_users.gu_nature='membre') OR 
conges_jours_fermeture.jf_gid = 0";
  $cgfe_req = requete_mysql($cgfe_select, $mysql_link, "get_vperiod", $DEBUG);

  while( $row = mysql_fetch_array($cgfe_req) ) {
    $ddatej = new DateTime($row['jf_date']) ; 
    if (($ddatej >= $dmindate) && ($ddatej <= $dmaxdate)) {
      $sdatej = $ddatej->format('Y-m-d') ;
      $vper['period'][$sdatej] = array(
           "type" => "z-da-pubhol" ,
           "legend" => $_SESSION['lang']['saisie_conges_lc_chome'] ); 
    };
  }
  $cgf_select = "SELECT jf_date FROM conges_jours_feries" ; 
  $cgf_req = requete_mysql($cgf_select, $mysql_link, "get_vperiod", $DEBUG);

  while( $row = mysql_fetch_array($cgf_req) ) {
    $ddatej = new DateTime($row['jf_date']) ; 
    if (($ddatej >= $dmindate) && ($ddatej <= $dmaxdate)) {
      $sdatej = $ddatej->format('Y-m-d') ;
      $vper['period'][$sdatej] = array(
           "type" => "z-da-pubhol" ,
           "legend" => $_SESSION['lang']['saisie_conges_lc_chome'] ); 
    };
  }

  $cp_select = "SELECT p_date_deb,p_demi_jour_deb,p_date_fin,p_demi_jour_fin,p_etat,p_type,p_commentaire FROM conges_periode WHERE p_login='$user'" ; 
  $cp_req = requete_mysql($cp_select, $mysql_link, "get_vperiod", $DEBUG);

  while( $row = mysql_fetch_array($cp_req) ) {
    $dp_date_deb = new DateTime($row['p_date_deb']) ; 
    if (($dp_date_deb >= $dmindate) && ($dp_date_deb <= $dmaxdate)) {
      // on ne traite que les valide et etat demande 
      if ($row['p_etat']=="ok") {
        $tetat="offval" ; 
      } else if ($row['p_etat']=="demande") {
        $tetat="offdem" ; 
      } else {
        continue ; // iter sur while( $row = ...
      }; 
      // error_log("get_vperiod:case1");

      $dp_date_fin = new DateTime($row['p_date_fin']) ;
      $dp_date_cur = clone($dp_date_deb) ; // on cree un autre objet 
      $tetatstable = $tetat ; 
      while ($dp_date_cur <= $dp_date_fin) {
        $tetat = $tetatstable ; 
        $partday = "da" ; 
        $zstate = "" ; 
        if ($dp_date_cur == $dp_date_deb) {
          if ($row['p_demi_jour_deb'] == "pm") {
            $partday = "pm" ; 
          }
        }
        if ($dp_date_cur == $dp_date_fin) { 
          if ($row['p_demi_jour_fin'] == "am") {
            $partday = "am" ; 
          }
        }
        if ($partday == "da") { // fullday 
          $zstate = 'z-da-'.$tetat ;
        } else { // "am" or "pm" 
          // error_log("get_vperiod:case2");
          if (isset($vper["period"][$dp_date_cur->format('Y-m-d')])) { 
            // ce jour est deja defini avec un $existingtype
            $existingtype = $vper["period"][$dp_date_cur->format('Y-m-d')]["type"];
            $lexistingpart = get_existing_type($existingtype,$partday) ; 

            if ($lexistingpart) { // not NULL  
              if (($partday == "am") && ($lexistingpart[0] == "pm")) {
                if ($tetat == $lexistingpart[1]) { 
                  $zstate = 'z-da-'.$tetat ; 
                } else {
                  $zstate = 'z-am-'.$tetat.'-pm-'. $lexistingpart[1] ; 
                }
              } else if (($partday == "pm") && ($lexistingpart[0] == "am")) {
                if ($tetat == $lexistingpart[1]) { 
                  $zstate = 'z-da-'.$tetat ; 
                } else {
                  $zstate = 'z-am-'.$lexistingpart[1].'-pm-'.$tetat ;
                }
              }
            }
          } // end if  isset($vper["period"][$dp_date_cur->format('Y-m-d')])
          if ($zstate == "") { 
            // pas d'existence de jour ou sans correspondence $lexistingpart est NULL
            if ($partday == "am") {
              $zstate = 'z-am-'.$tetat.'-pm-' ;
            } else if ($partday == "pm") {
              $zstate = 'z-am--pm-'.$tetat ;
            } else { // error 
              $zstate = 'z-wrong' ; 
            }
          }
        }

        $vper["period"][$dp_date_cur->format('Y-m-d')] = 
          array(
                "type" => $zstate,
                "legend" => $row['p_commentaire'] ); 
        $dp_date_cur->add($aday) ; 
        // error_log("get_vperiod:case3:".$dp_date_cur->format('Y-m-d')."-". $zstate."-".$row['p_commentaire']);
      } // end while ($dp_date_cur <= $dp_date_fin)
    }
  } // while $row = mysql_fetch_array($cp_req)
  
  return $vper ; // return $vper_default ;  
}

function get_holidays_frame_date_unused() 
{
  $now = new DateTime();
  $year_cur = $now->format('Y') ;
  $year_next = $year_cur + 1 ; 
  $mindate = new DateTime($year_cur."-01-01");  // 1er annee courante 
  $maxdate = new DateTime($year_next."-01-31"); // 31 janvier annee suivante 
  return array($mindate,$maxdate); 
}

function get_frame_date_v1($mode="conge") 
{
  $lnow = conges_get_date();
  $year_cur = $lnow['year'] ; 
  $year_start = $year_cur ; 
  $year_next = $year_cur + 1 ; 
  if ($mode == "artt") {
    $lmonth = intval($_SESSION['config']['rtt_retroactivite'])  ; // 2 mois 
    $month_cur = $lnow['mon'] ;
    // error_log("get_frame_date:".$year_cur.$month_cur); 
    $month_ant = $month_cur - $lmonth ; 
    if ($month_ant < 0 ) {
      $month_ant += 12 ;
      $year_start -= 1 ; 
    };
    $smindate = sprintf("%02d",$month_ant)."-01" ; 
  } else { // defaut  
    $smindate = $_SESSION['config']['moisjour-debutannee'] ; // "01-01" ; 1er janv annee courante 
  };
  $mindate = new DateTime($year_start . "-" . $smindate );  

  $smaxdate = $_SESSION['config']['moisjour-finannee'] ; // "01-31" ; 31 janvier , suppose anne suivante 
  $maxdate = new DateTime($year_next."-".$smaxdate); // 31 janvier annee suivante 
  $lframe = array($mindate,$maxdate) ; 
  // error_log("get_frame_date".print_r($lframe,True)); 
  return $lframe ; 
}
// en fonction de la date courante, parametre 
// retourne: (datemin , datemax) pour selectionner un conges 
// date en format DateTime 
// $mode="conge" or "artt" 
// _todo ; mode artt traiter limite ante-activite 
function get_frame_date($mode="conge",$by="user") 
{
  global $aday ;
  $lnow = conges_get_date();
  $year_cur = $lnow['year'] ; 
  $yearmin_start = $year_cur ; 
  $year_next = $year_cur + 1 ; 
  if ($mode == "artt") {
    $cf_retro = 'rtt_retroactivite' ;
  } else {
    $cf_retro = 'conge_retroactivite' ;
  }
  if (isset($_SESSION['config'][$cf_retro]) ) { 
    if ($_SESSION['config'][$cf_retro] != "") { 
      $lretromonth = intval($_SESSION['config'][$cf_retro])  ; // exemple 2 mois 
    } else { 
      $lretromonth = -1 ;
    }
  } else {
    $lretromonth = -1 ;
  }
  /* valeur par defaut pouvant etre ecrase suivant retroactivite */ 
  $smindate = $_SESSION['config']['moisjour-debutannee'] ; // "01-01" ; 1er janv annee courante 

  // en mode user et une retroactivite max est defini 
  if  ( $by=="resp" ) { // mode responsable 
    $yearmin_start -= 1 ;  // le responsable peut saisir sur année n-1 
    // attention pb _dirtyweeknumber_ sur année comme 15/16 
  } else if ( $by=="user" && $lretromonth >= 0 ) { 
    $month_cur = $lnow['mon'] ;
    // error_log("get_frame_date:".$year_cur.$month_cur); 
    $month_retro = $month_cur - $lretromonth ; 
    if ($month_retro < 0 ) {
      $month_retro += 12 ;
      $yearmin_start -= 1 ; 
    };
    $smindate = sprintf("%02d",$month_retro)."-01" ; 
  }
  $mindate = new DateTime($yearmin_start . "-" . $smindate );  
  /* determination date maximale */ 
  $smaxdate = "" ; 
  if ($mode == "artt") {
    if (isset($_SESSION['config']["rtt_anteactivite"]) ) { 
      $lantemonth = intval($_SESSION['config']["rtt_anteactivite"])  ; // exemple 2 mois 
      // error_log("get_frame_date lantem ". $lantemonth);
    } else { 
      $lantemonth = -1 ;  /* pas de limite ante-activite */ 
    }
    if ($lantemonth >= 0 ) { 
      $yearmax_start = $year_cur ; 
      $month_ante = $month_cur + $lantemonth ; 
      if ($month_ante < 12) { 
        $month_ante += 1 ; 
        $smaxdate = sprintf("%d-%02d-01",$yearmax_start, $month_ante) ; 
        $maxdate = new DateTime($smaxdate);
        /* si datecourante est 2016-03-26 et $lantemonth = 2 alors 
           $smaxdate = 2016-06-01  */ 
      }
    }
  }
  // si smaxdate n'a pas été déterminée alors on force au 31-01 annee suivante 
  if ($smaxdate == "") {     // "02-01" ; 01 Fevrier, suppose anne suivante 
    $smaxdate = sprintf("%d-%s",$year_next, $_SESSION['config']['moisjour-finannee']) ;
    $maxdate = new DateTime($smaxdate); // 31 janvier annee suivante 
  }
  $maxdate->sub($aday) ; /* astuce soit le 31/01 annee suivante 
       soit le dernier jour du mois précédent*/

  $lframe = array($mindate,$maxdate) ; 
  // error_log("get_frame_date".print_r($lframe,True)); 
  return $lframe ; 
}

// _dpa  get_recurrentvacancy v2 qui marche mais reputee COMPLEX 
// <_dirtyweeknumber_>
// cette fonction est basee sur un calcul de schema recurrent bi-hebdo or 
// ce schema est mis en defaut sur annee type 2016 ou on quitte un semaine impaire
// pour reprendre une semaine impaire 
// necessaire d'apporter un correctif 
// </_dirtyweeknumber_>
function get_recurrentvacancy($user, $mysql_link, $dmindate, $dmaxdate, $DEBUG=FALSE)
{
  global $aday ; 
  global $afourteen ; 
  global $dendoftime ; 
  // on batit sur la periode $dmindate, $dmaxdate la liste des dates chomees
  // (ddate,'d' ou 'a' ou 'p') pour day,am,pm TEL QUE PROGRAMMEE  
  $lvacperiod = array() ; 
  $lrecscheme = get_recurrentscheme($user, $mysql_link) ; 
  
  if (sizeof($lrecscheme) == 0 ) { // cas ou schema non rempli 
    return $lvacperiod ; 
  };
  // <COMPLEX_PART> 
  // recherche operationnelle comme suit 
  $dcurrent = clone($dmindate) ; 

  $bneednewscheme = 1 ; 
  $fourteeniter = 0 ; 
  while ($dcurrent < $dmaxdate) {
    // echo "dcurrent=".$dcurrent->format('Y-m-d')." fourteeniter=".$fourteeniter."\n";  
    if ($bneednewscheme) {
      $bneednewscheme = 0 ; 
      $fourteeniter = 0 ; 
      $curscheme = get_currentshiftedscheme($lrecscheme,$dcurrent); 
      /* echo "<pre>\n" ;
      print_r($dcurrent); 
      print_r($curscheme); 
      echo "</pre>\n" ;   */ 
      
      $dbasedate = clone($dcurrent) ; // on conserve datedebase $dbasedate
      // (datedeb,9999-12-31,(4=>'p',11=>'p'))
      $dschemdeb = $curscheme[0] ;
      $dschemfin = $curscheme[1];
      $ascheme = $curscheme[2] ; // 3ieme item ; 
      if (sizeof($ascheme) == 0 ) { // schema vide 
        if ($dschemfin == $dendoftime) { // le dernier 
          break ; 
        } else { // provoque passage au schema suivant 
          $dcurrent = clone($dschemfin);
          $dcurrent->add($aday);
          $bneednewscheme = 1 ;
          continue ; // on iter sur while 
        };
      };
    };
    foreach($ascheme as $kx => $kval ) {
      $dcurrent = clone($dbasedate) ; // on CLONE objet date de base  
      // _dpa $dcurrent est bien un nouvel objet pour chaque date
      $dcurrent->add(dayindateinterval($kx + $fourteeniter*$afourteen)); 

      if ($dcurrent <= $dschemfin) { // reste dans le cadre 
        $lvacperiod[$dcurrent->format('Y-m-d')] = $kval ; // care index par date 
      } else { // la date sort de la validite du scheme 
        $bneednewscheme = 1 ; 
        $dcurrent = clone($dschemfin);
        $dcurrent->add($aday);
        break ; 
      }
    }
    $fourteeniter++ ; 
  }
  // </COMPLEX_PART>
  return $lvacperiod; 
} 

function get_recurrentvacancy_with_update($user, $mysql_link, $dmindate, $dmaxdate, $DEBUG=FALSE)
{
  // error_log("get_recurrentvacancy_with_update".$user."-".$dmindate->format('Y-m-d')."-".$dmaxdate->format('Y-m-d')); 
  $lvacperiod = get_recurrentvacancy($user, $mysql_link, $dmindate, $dmaxdate, $DEBUG) ;
  $cer_select = "SELECT * FROM conges_echange_rtt WHERE e_login='$user'" ;
  // _dpatodo
  $cer_req = requete_mysql($cer_select, $mysql_link, "get_recurrentvacancy_with_update", $DEBUG);

  while( $row = mysql_fetch_array($cer_req) ) {
    $ddatej = new DateTime($row['e_date_jour']) ; 
    if (($ddatej >= $dmindate) && ($ddatej <= $dmaxdate)) {
      $sdatej = $ddatej->format('Y-m-d') ;
      // on se fie à information e_absence 
      $e_abs = $row['e_absence'] ; 
      if ($e_abs == "N") { // jlp n'existe plus 
        if (array_key_exists($sdatej,$lvacperiod)) {
          unset($lvacperiod[$sdatej]); 
        } else {
          error_log("fonction_conges::get_recurrentvacancy_with_update vacancy which should be present in recurrent ".$sdatej); 
        }
      } else if ($e_abs == "J") { // jlp deplace a cette date que l'on cree 
        $lvacperiod[$sdatej] = 'd' ; 
      } else if ($e_abs == "A") { // djlp restant a cette date que l'on met a jour
        $lvacperiod[$sdatej] = 'p' ; 
      } else if ($e_abs == "M") { // djlp restant a cette date que l'on met a jour
        $lvacperiod[$sdatej] = 'a' ; 
      }
    };
  }
  return $lvacperiod; 
} 

// date2vacperiodidx retourne pour une date donnee l'index entre 0 et 13 
// correspondant a la logique de programmation bihebdomadaire conforme à
// ISO-8601 
// 0 = lundi sem impair 13 = dim sem pair 

function date2vacperiodidx($ddate) {
  global $aweek ; 
  global $afourteen ; 
  $nweek = $ddate->format('W') ; /* ISO-8601 du num de semaine */
  if ($nweek == 53) {  // _dirtyweeknumber_ 
    $nweek = 0 ;  
  }
  $jweek = $ddate->format('w') ; /* num jour ds semaine 0:di, 6:sa */ 
  $jweek_rs =  ( $jweek + 6 ) % $aweek ; /* num jour ds semaine 0:lu, 6:di */

  $modweek = ( $nweek + 1 ) % 2 ; /* care: 0 pour sem impair, 1 pour sem paire */
  /* si dim sem pair alors 6 + 1 x 7 % 14 vaut 13  
     si ve sem impair alors 5  + 0 x 7 % 14 vaut 5 */ 
  $idx = ($jweek_rs   + $modweek * $aweek  ) % $afourteen ;
  return $idx ; 
}

// retourne le schema courant applicable (ou le dernier ou vide)
// correctement reindexe relativement a la date fournie (shifted) 
function get_currentshiftedscheme($lrecscheme,$ddate) {
  global $afourteen ; 

  $isch = 0 ;
  $lssch = array(); 
  $nsizelsch = sizeof($lrecscheme) ; 
  if ($nsizelsch == 0) 
    return $lssch ; 
  while ($isch < $nsizelsch ) {
    $df = $lrecscheme[$isch][1];
    if ($ddate <= $df) {
      break ;
    }
    $isch++ ; 
  };
  $lsch = $lrecscheme[$isch] ; 

  // recale / idx le schema 
  $idx = date2vacperiodidx($ddate) ; // si idx vaut 5 
  foreach($lsch[2] as $jx => $jval ) {
    if ($idx <= $jx ) {
      $lssch[$jx - $idx ] = $jval ; 
    } else {
      $lssch[$afourteen + $jx - $idx ] = $jval ; 
    }
  }
  $lsch[2] = $lssch ; 
  return $lsch ; 
}

// retourne les schemas de vacance recurrente sous la forme 
// ((datedeb,datefin,(4=>'p',11=>'p'),   
//  (datedeb,9999-12-31,(2=>'d') )
// indice du jour de 0 à 13;  0 = 1er Lundi impaire
function get_recurrentscheme($user, $mysql_link, $DEBUG=FALSE)
{
  // _dpaopti : voir si besoin de gerer un cache conservé dans la session 

  $ca_select = "SELECT * FROM conges_artt WHERE a_login='$user'
ORDER BY a_date_debut_grille ASC"; 
  $ca_req = requete_mysql($ca_select, $mysql_link, "get_recurrentscheme", $DEBUG);
  $lrec_scheme = array(); 
  while( $row = mysql_fetch_array($ca_req) ) {
    /* echo "<pre>\n" ;  
    print_r($row); 
    echo "</pre>\n" ;
    continue ;  */ 
    $lscheme = array(); 

    $dgrilledeb = new DateTime($row['a_date_debut_grille']);
    $dgrillefin = new DateTime($row['a_date_fin_grille']); 
    $rec_scheme = array($dgrilledeb,$dgrillefin);
    array_shift($row) ; // 
    $irow = 0 ; 
    $isch = 0 ; 
    while ($irow < 28) { // on balaie les 28 prochaines valeurs, 2 par 2 
      if (($row[$irow] == "Y") && ($row[$irow+1] == "Y")) {
        $lscheme[$isch] = 'd' ; // day 
      } else if ($row[$irow] == "Y") { 
        $lscheme[$isch] = 'a' ; // am 
      } else if ($row[$irow+1] == "Y") { 
        $lscheme[$isch] = 'p' ; // pm 
      }
      $isch =  $isch + 1 ; 
      $irow  =  $irow  + 2 ; 
    } 
    array_push($rec_scheme,$lscheme);
    array_push($lrec_scheme,$rec_scheme); 
  } 
  // error_log("get_recurrentscheme ". print_r($lrec_scheme,True)); 
  return $lrec_scheme ;  
}

function attic_correct_artt_scheme_v0($user, $mysql_link, $DEBUG=FALSE)
{
  /* _dpa cette fonction existe uniquement pour rétablir les données conges_artt 
     corrompues par appli conges4ac jusque v2 ; elle ajoute un enregistrement final
     avec la dernière date force a 9999-12-31 comme requis 
     voir également  admin_modif_user.php::commit_update 
  */
  global $sendoftime,$aday ; 
  $ca_select = "SELECT * FROM conges_artt WHERE a_login='$user'
ORDER BY a_date_debut_grille ASC"; 
  $ca_req = requete_mysql($ca_select, $mysql_link, "correct_artt_scheme", $DEBUG);
  $lrec_scheme = array(); 
  while( $row = mysql_fetch_array($ca_req) ) {
    array_push($lrec_scheme, $row);
  }
  $nrow = sizeof($lrec_scheme);
  /*  echo "<pre>"; 
  print_r($lrec_scheme[$nrow -1]) ;
  echo "</pre>"; */
  $lastrow = $nrow -1 ; 
  if ($lrec_scheme[$lastrow]['a_date_fin_grille'] != $sendoftime) {
    $dnewdeb = new DateTime($lrec_scheme[$lastrow]['a_date_fin_grille']); 
    $dnewdeb->add($aday);

    $sql3n = "INSERT INTO conges_artt (a_login, a_date_debut_grille, a_date_fin_grille  )
						VALUES ('$user', '".$dnewdeb->format("Y-m-d")."', '$sendoftime') " ;
    /* echo "<pre>"; 
    print_r($sql3n) ; 
    echo "</pre>"; */
    $result3n = requete_mysql($sql3n, $mysql_link, "commit_update", $DEBUG);
    $result2commit = requete_mysql("commit ; ", $mysql_link, "commit_update", $DEBUG);
    log_action(0, "", $user, "correct_artt_scheme", $mysql_link, $DEBUG);
  };
}
function attic_correct_artt_scheme_v1($user, $mysql_link, $DEBUG=FALSE)
{
  /* _dpa cette fonction existe uniquement pour rétablir les données conges_artt 
     corrompues par appli conges4ac jusque v2 ; elle retablit la sequence comme suit :
     deb1, deb2 - 1j, 
     deb2, deb3 - 1j, 
     debn, 9999-12-31 
     voir également  admin_modif_user.php::commit_update 
  */
  global $sendoftime,$aday ; 
  $ca_select = "SELECT * FROM conges_artt WHERE a_login='$user'
ORDER BY a_date_debut_grille ASC"; 
  $ca_req = requete_mysql($ca_select, $mysql_link, "correct_artt_scheme", $DEBUG);
  $lrec_scheme = array(); 
  while( $row = mysql_fetch_array($ca_req) ) {
    array_push($lrec_scheme, $row);
  }
  $nrow = sizeof($lrec_scheme);
  if ($nrow > 0) { // schemas artt existe 

      $lastrow = $nrow -1 ; 
      if ($lrec_scheme[$lastrow]['a_date_fin_grille'] != $sendoftime) {
        /* les donnees sont corrompues ; on corrige au mieux */ 
        $indrow = 0 ; 
        /* pour tous les rows sauf le dernier */ 
        while ($indrow < $lastrow ) { 
          $dactufin = $lrec_scheme[$indrow]['a_date_fin_grille']; 
          $dnewfin = new DateTime($lrec_scheme[$indrow + 1 ]['a_date_debut_grille']); 
          $dnewfin->sub($aday);
          $sql3n = "UPDATE conges_artt SET a_date_fin_grille='".$dnewfin->format("Y-m-d").
            "' WHERE a_login='$user' AND a_date_fin_grille='$dactufin' ; " ;
          $result3n = requete_mysql($sql3n, $mysql_link, "commit_update", $DEBUG);
          $indrow += 1 ; 
        }
        $dactufin = $lrec_scheme[$lastrow]['a_date_fin_grille']; 
        $sql3last = "UPDATE conges_artt SET a_date_fin_grille='$sendoftime' WHERE a_login='$user' AND a_date_fin_grille='$dactufin' ; " ;
        $result3last = requete_mysql($sql3last, $mysql_link, "commit_update", $DEBUG);
        log_action(0, "", $user, "correct_artt_scheme", $mysql_link, $DEBUG);

      };
  } else { // pas de schemas artt creation d'1 par defaut 
    $stoday = conges_get_date_fmt1(False) ; 
    $sql3ins = "INSERT INTO conges_artt (a_login, a_date_debut_grille, a_date_fin_grille )
						VALUES ('$user', '$stoday', '$sendoftime') " ;
    $result3ins = requete_mysql($sql3ins, $mysql_link, "commit_update", $DEBUG);
    log_action(0, "", $user, "correct_artt_scheme init", $mysql_link, $DEBUG);
  }
}
function attic_correct_artt_scheme($user, $mysql_link, $DEBUG=FALSE)
{
  /* _dpa cette fonction existe uniquement pour rétablir les données conges_artt 
     corrompues par appli conges4ac jusque v2 ; elle retablit la sequence comme suit :
     deb1, deb2 - 1j, 
     deb2, deb3 - 1j, 
     debn, finn ,
     finn + 1j, 9999-12-31      --  ce schema est vide  sans jour programme 
     voir également  admin_modif_user.php::commit_update 
  */
  global $sendoftime,$aday ; 
  $ca_select = "SELECT * FROM conges_artt WHERE a_login='$user'
ORDER BY a_date_debut_grille ASC"; 
  $ca_req = requete_mysql($ca_select, $mysql_link, "correct_artt_scheme", $DEBUG);
  $lrec_scheme = array(); 
  while( $row = mysql_fetch_array($ca_req) ) {
    array_push($lrec_scheme, $row);
  }
  $nrow = sizeof($lrec_scheme);
  if ($nrow > 0) { // schemas artt existe 

      $lastrow = $nrow -1 ; 
      if ($lrec_scheme[$lastrow]['a_date_fin_grille'] != $sendoftime) {
        /* les donnees sont corrompues ; on corrige au mieux */ 
        $indrow = $lastrow ; 
        /* on passe en revue les rows en commençant par le dernier */ 
        while ($indrow >= 0 ) {
          $sactufin = $lrec_scheme[$indrow]['a_date_fin_grille']; 
          $sactudeb = $lrec_scheme[$indrow]['a_date_debut_grille']; 
          if ($indrow == $lastrow) { 
            $dactudeb = new DateTime($sactudeb);
            $dactufin = new DateTime($sactufin);
            if ($dactudeb < $dactufin) {  /* ds ce cas on ajoute un schemas vide */ 
              $snewfin = $sendoftime ; 
              $dnewdeb = clone($dactufin); /* new DateTime($lrec_scheme[$indrow]['a_date_fin_grille']); */ 
              $dnewdeb->add($aday);
              $snewdeb = $dnewdeb->format("Y-m-d") ; 
              $sql3n = "INSERT INTO conges_artt (a_login, a_date_debut_grille, a_date_fin_grille )
						VALUES ('$user', '$snewdeb', '$sendoftime') ; " ;
            } else { /* cas parti ou date_fin anterieur date_deb 
                      on force alors date_de_fin = end_of_time */
              $sql3n = "UPDATE conges_artt SET a_date_fin_grille='$sendoftime' 
WHERE a_login='$user' AND a_date_fin_grille='$sactufin' ; " ;
            }
          } else { // on utilise la date de debut (suivant) - 1 j 
            $dnewfin = new DateTime($lrec_scheme[$indrow + 1 ]['a_date_debut_grille']); 
            $dnewfin->sub($aday);
            $snewfin = $dnewfin->format("Y-m-d") ; 
            $sql3n = "UPDATE conges_artt SET a_date_fin_grille='$snewfin' 
WHERE a_login='$user' AND a_date_fin_grille='$sactufin' ; " ;
          }

          $result3n = requete_mysql($sql3n, $mysql_link, "commit_update", $DEBUG);
          $indrow -= 1 ; 
        }
        log_action(0, "", $user, "correct_artt_scheme", $mysql_link, $DEBUG);

      };
  } else { // pas de schemas artt creation d'1 par defaut 
    $stoday = conges_get_date_fmt1(False) ; 
    $sql3ins = "INSERT INTO conges_artt (a_login, a_date_debut_grille, a_date_fin_grille )
						VALUES ('$user', '$stoday', '$sendoftime') ; " ;
    $result3ins = requete_mysql($sql3ins, $mysql_link, "commit_update", $DEBUG);
    log_action(0, "", $user, "correct_artt_scheme init", $mysql_link, $DEBUG);
  }
}
/* _dpa_todo: 
   delivre date du jour / heure du jour soit réelle , soit impose par config 
   sous la forme d'un array comparable au resultat de getdate(), ie.
   'year','mon','mday','hours','minutes','seconds','mode'
   mode = 0 ; heure systeme vrai 
   mode = 1 ; heure forcee par configuration 
*/
function conges_get_date($DEBUG=FALSE) {
  $lmydate = array();
  $lnow = getdate(); 
  $lmydate['mode'] = 0 ;
  /* we keep real time hour */ 
  $lmydate['hours']   =  $lnow['hours']   ;
  $lmydate['minutes'] =  $lnow['minutes'] ;
  $lmydate['seconds'] =  $lnow['seconds'] ;

  if (array_key_exists('date-forcee', $_SESSION['config'])) {
    if  ($_SESSION['config']['date-forcee'] != "" ) {
      /* au format 2015-01-01 */ 
      $ldatef = explode("-",$_SESSION['config']['date-forcee']);
      $lmydate['mode'] = 1 ; 
      $lmydate['year'] = intval($ldatef[0]) ;
      $lmydate['mon']  = intval($ldatef[1]) ; 
      $lmydate['mday'] = intval($ldatef[2]) ; 
    }
  } 
  if ($lmydate['mode'] == 0) {
      $lmydate['year'] = $lnow['year'] ;
      $lmydate['mon']  = $lnow['mon']  ;
      $lmydate['mday'] = $lnow['mday'] ;
  }
  return $lmydate ; 
}
function conges_get_date_fmt1($DEBUG=FALSE) {
  $lmydate = conges_get_date($DEBUG); 
  return sprintf("%02d-%02d-%02d",$lmydate['year'],$lmydate['mon'],$lmydate['mday']); 
}




/* __attic */ 
function get_recurrentvacancy_1($user, $mysql_link, $dmindate, $dmaxdate, $DEBUG=FALSE)
{
  global $afourteen ; 
  // on batit sur la periode $dmindate, $dmaxdate la liste des dates chomees
  // (ddate,'d' ou 'a' ou 'p') pour day,am,pm TEL QUE PROGRAMMEE  
  $lvacperiod = array() ; 
  $lrecscheme = get_recurrentscheme($user, $mysql_link) ; 
  // recherche operationnelle comme suit 
  $dcurrent = $dmindate ; 

  $curscheme = get_currentscheme($lrecscheme,$dcurrent); // (datedeb,9999-12-31,(4=>'p',11=>'p'))
  $ascheme = $curscheme[2] ; // 3ieme item ; 

  if (sizeof($ascheme) == 0 ) { // cas ou le schema est vide 
    return $lvacperiod ; 
  };

  $ashiftedscheme = array() ; // recale / idx le schema 
  // $lqueu = array(); 
  $idx = date2vacperiodidx($dcurrent) ; // si idx vaut 5 
  foreach($ascheme as $jx => $jval ) {
    if ($idx <= $jx ) {
      $ashiftedscheme[$jx - $idx ] = $jval ; 
    } else {
      $ashiftedscheme[$afourteen + $jx - $idx ] = $jval ; 
    }
  } 
  // array_push($ashiftedscheme,$lqueu) ; // fusion des 2 nouveau schema : 6=>'p', 13=>'p' 
  // return $ashiftedscheme ; 

  $lsscheme = sizeof($ashiftedscheme); 
  // _dpa en chantier 
  $fourteeniter = 0 ; 
  while ($dcurrent < $dmaxdate) { 
    foreach($ashiftedscheme as $kx => $kval ) {
      $dcurrent = clone($dmindate) ; // on CLONE objet date de depart 
      // _dpa $dcurrent est bien un nouvel objet pour chaque date
      $dcurrent->add(dayindateinterval($kx + $fourteeniter*$afourteen)) ; 
      // array_push($lvacperiod, array($dcurrent,$kval));
      $lvacperiod[$dcurrent->format('Y-m-d')] = $kval ; // care index par date  

    }
    $fourteeniter++ ; 
  }
  return $lvacperiod; 
} 
// _dpa_refactor: a traiter comme un objet
function get_currentscheme($lrecscheme,$ddate) {
  // provisoirement le dernier
  $ilast = sizeof($lrecscheme) - 1 ; 
  return $lrecscheme[$ilast] ; 
}

/* echo "<pre>"; 
     print_r($sql3n) ; 
     echo "</pre>"; */
?>
