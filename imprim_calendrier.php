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
include("INCLUDE.PHP/session.php");

$DEBUG=FALSE;
//$DEBUG=TRUE


	/*** initialisation des variables ***/
	$session=session_id();
	/************************************/

	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET	/ POST
	$action     = getpost_variable("action") ;
	$new_mois = getpost_variable("new_mois", date("m")) ;
	$new_year = getpost_variable("new_year", date("Y")) ;
	/*************************************/


	form_saisie($action, $new_mois, $new_year, $DEBUG);


/*******************************************************************************/
/**********  FONCTIONS  ********************************************************/

function form_saisie($action, $new_mois, $new_year, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	include("fonctions_javascript.php") ;

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";

	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
	echo "<link href=\"../".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
	echo "<title>PHP_CONGES : </title>\n";
	echo "</head>\n";
	echo "<body>\n";

	if($action=="imprim")
		ouvre_calendrier($new_mois, $new_year, $DEBUG);

	echo "<center>\n";
	echo "<h3>".$_SESSION['lang']['imprim_calendrier_titre']."</h3>\n";

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<table>\n";
	// choix du mois et annee
	echo "<tr>\n";
		echo "<td align=\"center\">\n";
		echo "<b>".$_SESSION['lang']['divers_mois']." : </b>\n";
		$mois_default=date("m");
		affiche_selection_new_mois($mois_default);  // la variable est $new_mois
		echo "</td>\n";
		echo "<td align=\"center\">\n";
		echo "<b>".$_SESSION['lang']['divers_annee']." : </b>\n";
		$year_default=date("Y");
		affiche_selection_new_year($year_default-5, $year_default+5, $year_default );  // la variable est $new_year
		echo "</td>\n";
	echo "</tr>\n";
	// ligne vide
	echo "<tr>\n";
		echo "<td colspan=\"2\">&nbsp;\n";
		echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"hidden\" name=\"action\" value=\"imprim\">\n";
	echo "	<input type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"button\" value=\"".$_SESSION['lang']['form_close_window']."\" onClick=\"javascript:window.close();\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";

	echo "</center>\n";
	echo "</body>\n";
	echo "</html>\n";

}

function ouvre_calendrier($mois, $year, $DEBUG=FALSE)
{
	$session=session_id();

	echo "<script language=\"javascript\">\n";
	echo "OpenPopUp('calendrier.php?session=$session&printable=1&mois=$mois&year=$year','calendrier',850,600);\n";
	echo "</script>\n";
}
?>
