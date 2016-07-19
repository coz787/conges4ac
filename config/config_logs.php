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
$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : "") ) ;

include("../config_ldap.php");
include("../fonctions_conges.php") ;
include("../INCLUDE.PHP/fonction.php");
if(!isset($_SESSION['config']))
	$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
include("../INCLUDE.PHP/session.php");
//include("fonctions_install.php") ;


$DEBUG = FALSE ;
//$DEBUG = TRUE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);

if($DEBUG==TRUE) { echo "SESSION = "; print_r($_SESSION); echo "<br>\n";}


	/*** initialisation des variables ***/
	/************************************/

	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$action         = getpost_variable("action", "") ;
	$login_par      = getpost_variable("login_par", "") ;

	/*************************************/

	//connexion mysql
	$mysql_link = connexion_mysql() ;

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
	echo "<link href=\"../".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
	echo "<TITLE> CONGES : Configuration </TITLE>\n";
	echo "</head>\n";


	if($action=="suppr_logs")
		confirmer_vider_table_logs($mysql_link, $session, $DEBUG);
	elseif($action=="commit_suppr_logs")
		commit_vider_table_logs($mysql_link, $session, $DEBUG);
	else
		affichage($login_par, $mysql_link, $session, $DEBUG);


	echo "</body>";
	echo "</html>";

	mysql_close($mysql_link);



/**************************************************************************************/
/**********  FONCTIONS  ***************************************************************/


function affichage($login_par, $mysql_link, $session, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	echo "<body text=\"#000000\" bgcolor=".$_SESSION['config']['bgcolor']." link=\"#000080\" vlink=\"#800080\" alink=\"#FF0000\" >\n";

	/**************************************/
	// affichage du titre
	echo "<br><center><H1><img src=\"../img/tux_config_32x32.png\" width=\"32\" height=\"32\" border=\"0\" title=\"".$_SESSION['lang']['config_logs_titre_2']."\" alt=\"".$_SESSION['lang']['config_logs_titre_2']."\"> ".$_SESSION['lang']['config_logs_titre_1']."</H1></center>\n";
	echo "<br>\n";
	/**************************************/

	affiche_bouton_retour($session);

	//requête qui récupère les logs
	$sql1 = "SELECT log_user_login_par, log_user_login_pour, log_etat, log_comment, log_date FROM conges_logs ";
	if($login_par!="")
		$sql1 = $sql1." WHERE log_user_login_par = '$login_par' ";
	$sql1 = $sql1." ORDER BY log_date";

	$ReqLog1 = requete_mysql($sql1, $mysql_link, "affichage", $DEBUG);

	if(mysql_num_rows($ReqLog1)!=0)
	{
		echo "<center>\n";

		if($session=="")
			echo "<form action=\"$PHP_SELF\" method=\"POST\"> \n";
		else
			echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\"> \n";

		echo "<br>\n";
		echo "<table align=\"center\">\n";

		echo "<tr><td class=\"histo\" colspan=\"5\">".$_SESSION['lang']['voir_les_logs_par']."</td>";
		if($login_par!="")
			echo "<tr><td class=\"histo\" colspan=\"5\">".$_SESSION['lang']['voir_tous_les_logs']." <a href=\"$PHP_SELF?session=$session\">".$_SESSION['lang']['voir_tous_les_logs']."</a></td>";
		echo "<tr><td class=\"histo\" colspan=\"5\">&nbsp;</td>";

		// titres
		echo "<tr>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_date_maj_1']."</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_fait_par_maj_1']."</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_pour_maj_1']."</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_comment_maj_1']."</td>\n";
		echo "<td class=\"titre\">".$_SESSION['lang']['divers_etat_maj_1']."</td>\n";
		echo "</tr>\n";

		// affichage des logs
		while ($data = mysql_fetch_array($ReqLog1))
		{
			$log_login_par = $data['log_user_login_par'];
			$log_login_pour = $data['log_user_login_pour'];
			$log_log_etat = $data['log_etat'];
			$log_log_comment = $data['log_comment'];
			$log_log_date = $data['log_date'];

			echo "<tr>\n";
			echo "<td class=\"histo\">$log_log_date</td>\n";
			echo "<td class=\"histo\"><a href=\"$PHP_SELF?session=$session&login_par=$log_login_par\"><b>$log_login_par</b></a></td>\n";
			echo "<td class=\"histo\">$log_login_pour</td>\n";
			echo "<td class=\"histo\">$log_log_comment</td>\n";
			echo "<td class=\"histo\">$log_log_etat</td>\n";
			echo "</tr>\n";
		}

		echo "</table>\n";

		// affichage du bouton pour vider les logs
		echo "<input type=\"hidden\" name=\"action\" value=\"suppr_logs\">\n";
		echo "<input type=\"submit\"  value=\"".$_SESSION['lang']['form_delete_logs']."\"><br>";
		echo "</form>\n";
		echo "</center>\n";
	}
	else
		echo $_SESSION['lang']['no_logs_in_db']."><br>";


	echo "<br>\n";
	affiche_bouton_retour($session);
	echo "<br><br>\n";

}


function confirmer_vider_table_logs($mysql_link, $session, $DEBUG=FALSE)
{
//$DEBUG=TRUE;
	$PHP_SELF=$_SERVER['PHP_SELF'];

	echo "<center>\n";
	echo "<br><h2>".$_SESSION['lang']['confirm_vider_logs']."</h2><br>\n";

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n"  ;
	echo "<input type=\"hidden\" name=\"action\" value=\"commit_suppr_logs\">\n";
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_delete_logs']."\">\n";
	echo "</form>\n" ;

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n"  ;
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_cancel']."\">\n";
	echo "</form>\n" ;
	echo "</center>\n";

}

function commit_vider_table_logs($mysql_link, $session, $DEBUG=FALSE)
{
//$DEBUG=TRUE;
	$PHP_SELF=$_SERVER['PHP_SELF'];

	$sql_delete="TRUNCATE TABLE conges_logs ";
	$ReqLog_delete = requete_mysql($sql_delete, $mysql_link, "supprim_logs", $DEBUG);

	// ecriture de cette action dans les logs
	$comment_log = "effacement des logs de php_conges ";
	log_action(0, "", "", $comment_log, $mysql_link, $DEBUG);

	echo "<span class = \"messages\">".$_SESSION['lang']['form_modif_ok']."</span><br>";
	if($session=="")
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL=$PHP_SELF?\">";
	else
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL=$PHP_SELF?session=$session\">";


}



?>
