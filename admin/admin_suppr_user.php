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


$DEBUG=FALSE;
//$DEBUG=TRUE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);



echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
echo "<html>\n";
echo "<head>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
echo "<link href=\"../".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
echo "<TITLE> ".$_SESSION['config']['titre_application'].$_SESSION['config']['titre_admin_index']." </TITLE>\n";
echo "</head>\n";


	$bgimage=$_SESSION['config']['URL_ACCUEIL_CONGES']."/".$_SESSION['config']['bgimage'];
	echo "<body text=\"#000000\" bgcolor=".$_SESSION['config']['bgcolor']." link=\"#000080\" vlink=\"#800080\" alink=\"#FF0000\" background=\"$bgimage\">\n";
	echo "<CENTER>\n";

	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$u_login = getpost_variable("u_login") ;
	$u_login_to_delete = getpost_variable("u_login_to_delete") ;
	/*************************************/

	// TITRE
	if($u_login!="")
		$login_titre = $u_login;
	elseif($u_login_to_delete!="")
		$login_titre = $u_login_to_delete;

	echo "<H1>".$_SESSION['lang']['admin_suppr_user_titre']." : $login_titre .</H1>\n\n";


	//connexion mysql
	$mysql_link = connexion_mysql() ;

	if($u_login!="")
	{
		confirmer($u_login, $mysql_link, $DEBUG);
	}
	elseif($u_login_to_delete!="")
	{
		suppression($u_login_to_delete, $mysql_link, $DEBUG);
	}
	else
	{
		// renvoit sur la page principale .
		header("Location: admin_index.php?session=$session&onglet=admin-users");
	}

	mysqli_close($mysql_link);

echo "<hr align=\"center\" size=\"2\" width=\"90%\">\n";

echo "</CENTER>\n";
echo "</body>\n";
echo "</html>\n";



/**************************************************************************************/
/**********  FONCTIONS  ***************************************************************/

function confirmer($u_login, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	/****************************/
	/* Etat Utilisateur en cours */
	/*****************************/
	// AFFICHAGE TABLEAU
	echo "<form action=\"$PHP_SELF?session=$session&u_login_to_delete=$u_login\" method=\"POST\">\n"  ;
	echo "<table cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n";
	echo "<tr align=\"center\">\n";
	echo "<td class=\"histo\">".$_SESSION['lang']['divers_login_maj_1']."</td>\n";
	echo "<td class=\"histo\">".$_SESSION['lang']['divers_nom_maj_1']."</td>\n";
	echo "<td class=\"histo\">".$_SESSION['lang']['divers_prenom_maj_1']."</td>\n";
	echo "</tr>\n";

	// Récupération des informations
	$sql1 = "SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login = '$u_login' "  ;
	$ReqLog1 = requete_mysql($sql1, $mysql_link, "confirmer", $DEBUG);

	echo "<tr align=\"center\">\n";
	while ($resultat1 = mysqli_fetch_array($ReqLog1))
	{
		echo "<td class=\"histo\">".$resultat1["u_login"]."</td>\n";
		echo "<td class=\"histo\">".$resultat1["u_nom"]."</td>\n";
		echo "<td class=\"histo\">".$resultat1["u_prenom"]."</td>\n";
	}
	echo "</tr>\n";
	echo "</table><br>\n\n";
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_supprim']."\">\n";
	echo "</form>\n" ;

	echo "<form action=\"admin_index.php?session=$session&onglet=admin-users\" method=\"POST\">\n"  ;
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_cancel']."\">\n";
	echo "</form>\n" ;

}

function suppression($u_login_to_delete, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	//echo($u_login_to_delete."---".$u_login_to_delete."<br>");

	$sql1 = "DELETE FROM conges_users WHERE u_login = '$u_login_to_delete' " ;
	$result = requete_mysql($sql1, $mysql_link, "suppression", $DEBUG);

	$sql2 = "DELETE FROM conges_periode WHERE p_login = '$u_login_to_delete' " ;
	$result2 = requete_mysql($sql2, $mysql_link, "suppression", $DEBUG);

	$sql3 = "DELETE FROM conges_artt WHERE a_login = '$u_login_to_delete' " ;
	$result3 = requete_mysql($sql3, $mysql_link, "suppression", $DEBUG);

	$sql4 = "DELETE FROM conges_echange_rtt WHERE e_login = '$u_login_to_delete' " ;
	$result4 = requete_mysql($sql4, $mysql_link, "suppression", $DEBUG);

	$sql5 = "DELETE FROM conges_groupe_resp WHERE gr_login = '$u_login_to_delete' " ;
	$result5 = requete_mysql($sql5, $mysql_link, "suppression", $DEBUG);

	$sql6 = "DELETE FROM conges_groupe_users WHERE gu_login = '$u_login_to_delete' " ;
	$result6 = requete_mysql($sql6, $mysql_link, "suppression", $DEBUG);

	$sql7 = "DELETE FROM conges_solde_user WHERE su_login = '$u_login_to_delete' " ;
	$result7 = requete_mysql($sql7, $mysql_link, "suppression", $DEBUG);


	$comment_log = "suppression_user ($u_login_to_delete)";
	log_action(0, "", $u_login_to_delete, $comment_log, $mysql_link, $DEBUG);

	if($result==TRUE)
		echo $_SESSION['lang']['form_modif_ok']." !<br><br> \n" ;
	else
		echo $_SESSION['lang']['form_modif_not_ok']." !<br><br> \n";

	if($DEBUG==TRUE)
	{
		echo "<a href=\"admin_index.php?session=$session&onglet=admin-users\">".$_SESSION['lang']['form_retour']."</a>\n" ;
	}
	else
	{
		/* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=admin_index.php?session=$session&onglet=admin-users\">";
	}

}

?>
