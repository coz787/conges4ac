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
$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : "") ) ;


//$DEBUG = TRUE ;
$DEBUG = FALSE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);



	/*** initialisation des variables ***/
	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$action = getpost_variable("action") ;
	$tab_new_values = getpost_variable("tab_new_values");

	/*************************************/

	if($DEBUG)
	{
		print_r($tab_new_values); echo "<br>\n";
		echo "$action<br>\n";
	}

	//connexion mysql
	$mysql_link = connexion_mysql() ;

	// affichage début page
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
	echo "<link href=\"../".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
	echo "<TITLE> CONGES : Configuration </TITLE>\n";
	echo "</head>\n";

	echo "<body text=\"#000000\" bgcolor=".$_SESSION['config']['bgcolor']." link=\"#000080\" vlink=\"#800080\" alink=\"#FF0000\">\n";
	echo "<center>";

	/*********************************/
	/*********************************/

	if($action=="modif")
		commit_modif($tab_new_values, $mysql_link, $session, $DEBUG);

	affichage($tab_new_values, $mysql_link, $session, $DEBUG);

	/*********************************/
	/*********************************/

	// affichage fin page
	echo "</center>";
	echo "</body>";
	echo "</html>";

	mysqli_close($mysql_link);




/**************************************************************************************/
/**********  FONCTIONS  ***************************************************************/


function affichage($tab_new_values, $mysql_link, $session, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	if($session=="")
		$URL = "$PHP_SELF";
	else
		$URL = "$PHP_SELF?session=$session";

	/**************************************/
	// affichage du titre
	echo "<H1> ".$_SESSION['lang']['config_mail_titre']."</H1>\n";
	echo "<i> ".$_SESSION['lang']['config_mail_alerte_config']."</i>\n";
	echo "<br><br>\n";
	/**************************************/

	affiche_bouton_retour($session);

	// affichage de la liste des type d'absence existants

	//requête qui récupère les informations de la table conges_type_absence
	$sql1 = "SELECT * FROM conges_mail ";
	$ReqLog1 = requete_mysql($sql1, $mysql_link, "affichage", $DEBUG);

	echo "    <form action=\"$URL\" method=\"POST\"> \n";
	while ($data = mysqli_fetch_array($ReqLog1))
	{
	 	$mail_nom = stripslashes($data['mail_nom']);
		$mail_subject = stripslashes($data['mail_subject']);
		$mail_body = stripslashes($data['mail_body']);

		$legend =$mail_nom ;
		$key = $mail_nom."_commentaire";
        if ( array_key_exists($key, $_SESSION['lang']) ) { 
          $comment = $_SESSION['lang'][$key] ; 
        } else {
          $comment = ""; 
          error_log("config_mail.php:warning ".$key." does not exist in language definition"); 
        }

		echo "<br>\n";
		echo "<table>\n";
		echo "<tr><td>\n";
		echo "    <fieldset class=\"cal_saisie\">\n";
		echo "    <legend class=\"boxlogin\">$legend</legend>\n";
		echo "    <i>$comment</i><br><br>\n";
		echo "    <table cellpadding=\"2\" class=\"tablo-config\" >\n";
		echo "    <tr>\n";
		echo "    	<td class=\"config\" valign=\"top\"><b>".$_SESSION['lang']['config_mail_subject']."</b></td>\n";
		echo "    	<td class=\"config\"><input type=\"text\" size=\"80\" name=\"tab_new_values[$mail_nom][subject]\" value=\"$mail_subject\"></td>\n";
		echo "    </tr>\n";
		echo "    <tr>\n";
		echo "    	<td class=\"config\" valign=\"top\"><b>".$_SESSION['lang']['config_mail_body']."</b></td>\n";
		echo "    	<td class=\"config\"><textarea rows=\"6\" cols=\"80\" name=\"tab_new_values[$mail_nom][body]\" value=\"$mail_body\">$mail_body</textarea></td>\n";
		echo "    </tr>\n";
		echo "    <tr>\n";
		echo "    	<td class=\"config\">&nbsp;</td>\n";
		echo "    	<td class=\"config\">\n";
		echo "    		<i>".$_SESSION['lang']['mail_remplace_url_accueil_comment']."<br>\n";
		echo "    		".$_SESSION['lang']['mail_remplace_sender_name_comment']."<br>\n";
		echo "    		".$_SESSION['lang']['mail_remplace_destination_name_comment']."<br>\n";
		echo "    		".$_SESSION['lang']['mail_remplace_nb_jours']."<br>\n";
		echo "    		".$_SESSION['lang']['mail_remplace_date_debut']."<br>\n";
		echo "    		".$_SESSION['lang']['mail_remplace_date_fin']."<br>\n";
		echo "    		".$_SESSION['lang']['mail_remplace_commentaire']."<br>\n";
		echo "    		".$_SESSION['lang']['mail_remplace_type_absence']."<br>\n";
		echo "    		".$_SESSION['lang']['mail_remplace_retour_ligne_comment']."</i>\n";
		echo "    	</td>\n";
		echo "    </tr>\n";

		echo "    </table>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
	}

	echo "    <input type=\"hidden\" name=\"action\" value=\"modif\">\n";
	echo "    <input type=\"submit\"  value=\"".$_SESSION['lang']['form_save_modif']."\"><br>\n";
	echo "    </form>\n";

	// Bouton de retour : différent suivant si on vient des pages d'install ou de l'appli
	echo "<br><br>\n";
	affiche_bouton_retour($session);

}

function commit_modif($tab_new_values, $mysql_link, $session, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	if($session=="")
		$URL = "$PHP_SELF";
	else
		$URL = "$PHP_SELF?session=$session";


	// update de la table
	foreach($tab_new_values as $nom_mail => $tab_mail)
	{
		$subject = addslashes($tab_mail['subject']);
		$body = addslashes($tab_mail['body']) ;
		$req_update="UPDATE conges_mail SET mail_subject='$subject', mail_body='$body' WHERE mail_nom='$nom_mail' ";
		$result1 = requete_mysql($req_update, $mysql_link, "commit_modif", $DEBUG);
	}
	echo "<span class = \"messages\">".$_SESSION['lang']['form_modif_ok']."</span><br>";

	$comment_log = "configuration des mails d\'alerte";
	log_action(0, "", "", $comment_log, $mysql_link, $DEBUG);

	if($DEBUG==TRUE)
		echo "<a href=\"$URL\" method=\"POST\">".$_SESSION['lang']['form_retour']."</a><br>\n" ;
	else
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$URL\">";

}

?>
