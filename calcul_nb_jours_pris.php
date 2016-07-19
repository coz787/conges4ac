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
include("fonctions_calcul.php");


$DEBUG=FALSE;
//$DEBUG=TRUE;


	/*** initialisation des variables ***/
    $session=session_id();
	$user="";
	$date_debut="";
	$date_fin="";
	/************************************/

	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET	/ POST
	$user       = getpost_variable("user") ;
	$date_debut = getpost_variable("date_debut") ;
	$date_fin   = getpost_variable("date_fin") ;
	$opt_debut  = getpost_variable("opt_debut") ;
	$opt_fin    = getpost_variable("opt_fin") ;
	/*************************************/

// ATTENTION ne pas mettre cet appel avant les include car plantage sous windows !!!
?>
<script language="javascript">
function envoi(valeur)
{window.opener.document.forms[0].new_nb_jours.value=valeur}
</Script>
<?php

	if( ($user!="") && ($date_debut!="") && ($date_fin!="") && ($opt_debut!="") && ($opt_fin!="") )
		affichage($user, $date_debut, $date_fin, $opt_debut, $opt_fin, $DEBUG);
	else
		/* APPEL D'UNE AUTRE PAGE immediat */
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=user_index.php?session=$session&onglet=nouvelle_absence\">";
		//echo " $user, $date_debut, $date_fin, $opt_debut, $opt_fin <br>\n";




/**********  FONCTIONS  ****************************************/

function affichage($user, $date_debut, $date_fin, $opt_debut, $opt_fin, $DEBUG=FALSE)
{
	if($DEBUG==TRUE) { echo "user = $user, date_debut = $date_debut, date_fin = $date_fin, opt_debut = $opt_debut, opt_fin = $opt_fin<br>\n";}

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	$comment="&nbsp;" ;

	//connexion mysql
	$mysql_link = connexion_mysql() ;

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";

	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
	echo "<link href=\"".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
	echo "<title>PHP_CONGES : </title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<center>\n";
	echo "<h1>$user</h1>\n";

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr>\n";
	// calcul :
	$nb_jours=compter($user, $date_debut, $date_fin, $opt_debut, $opt_fin,-1,$comment, $mysql_link, $DEBUG);
	echo "<td align=\"center\"><h2>".$_SESSION['lang']['calcul_nb_jours_nb_jours']." <b>$nb_jours</b></h2></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align=\"center\"><i><font color=\"red\">$comment<font/></i></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align=\"center\"><i>".$_SESSION['lang']['calcul_nb_jours_reportez']." \"".$_SESSION['lang']['saisie_conges_nb_jours']."\" ".$_SESSION['lang']['calcul_nb_jours_form'].".</i></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align=\"center\">&nbsp;</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align=\"center\">\n";
	echo "	<input type=\"button\" value=\"".$_SESSION['lang']['form_close_window']."\" onClick=\"javascript:window.close();\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";

	mysql_close($mysql_link);

	if($_SESSION['config']['rempli_auto_champ_nb_jours_pris']==TRUE)
	{
	 if($nb_jours >0) //modif ajout du 25 oct 2012
		{
		if( ($comment=="&nbsp;") && ($DEBUG==FALSE) )
			echo "<script>envoi($nb_jours); window.close()</script>";
		else
			echo "<script>envoi($nb_jours)</script>";
		}
	else
	echo "<td  align=\"center\"><h1><font color=\"red\">période de congé déjà utilisée<br> fermez cette fenestre <br\>et choisissez d'autres dates<br>\n ou cliquez sur le bouton abandonner !!!<font/></h1></td>\n"; // modif  ajout 25 oct2012 
	}
	echo "</center>\n";
	echo "</body>\n";
	echo "</html>\n";

}


?>
