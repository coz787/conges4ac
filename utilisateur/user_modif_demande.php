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


echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
echo "<html>\n";
echo "<head>\n";

	echo "<TITLE>".$_SESSION['config']['titre_application']." : ".$_SESSION['lang']['user']." ".$_SESSION['userlogin']."</TITLE>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
	echo "<link href=\"../".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
	echo "</head>\n";

	$bgimage=$_SESSION['config']['URL_ACCUEIL_CONGES']."/".$_SESSION['config']['bgimage'];
	echo "<body text=\"#000000\" bgcolor=".$_SESSION['config']['bgcolor']." link=\"#000080\" vlink=\"#800080\" alink=\"#FF0000\" background=\"$bgimage\">\n";
	echo "<CENTER>\n";

	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$p_num             = getpost_variable("p_num");
	$onglet            = getpost_variable("onglet");
	$p_num_to_update   = getpost_variable("p_num_to_update");
	$p_etat			   = getpost_variable("p_etat");
	$new_debut         = getpost_variable("new_debut");
	$new_demi_jour_deb = getpost_variable("new_demi_jour_deb");
	$new_fin           = getpost_variable("new_fin");
	$new_demi_jour_fin = getpost_variable("new_demi_jour_fin");
	$new_nb_jours      = getpost_variable("new_nb_jours");
	$new_comment       = getpost_variable("new_comment");
	/*************************************/

	// TITRE
	echo "<H1>".$_SESSION['lang']['user_modif_demande_titre']."</H1>\n\n";
	echo "<br><br>\n";

	if($p_num!="")
	{
		confirmer($p_num, $onglet, $DEBUG);
	}
	else
	{
		if($p_num_to_update!="")
		{
			modifier($p_num_to_update, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $p_etat, $onglet, $DEBUG);
		}
		else
		{
			// renvoit sur la page principale .
			header("Location: user_index.php");
		}
	}

echo "<hr align=\"center\" size=\"2\" width=\"90%\">\n";

echo "</CENTER>\n";
echo "</body>\n";
echo "</html>\n";

/********************************************************************************************************/
/********************************************************************************************************/
/********************************************************************************************************/
function confirmer($p_num, $onglet, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	//connexion mysql
	$mysql_link = connexion_mysql() ;

	// Récupération des informations
	$sql1 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_etat, p_num FROM conges_periode where p_num = ".$p_num  ;
	$ReqLog1 = requete_mysql($sql1, $mysql_link, "confirmer", $DEBUG) ;

	// AFFICHAGE TABLEAU

	echo "<form action=\"$PHP_SELF\" method=\"POST\">\n" ;
	echo "<table cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n" ;
	// affichage première ligne : titres
	echo "<tr align=\"center\">\n";
	echo "<td class=\"titre\">".$_SESSION['lang']['divers_debut_maj_1']."</td>\n";
	echo "<td class=\"titre\">".$_SESSION['lang']['divers_fin_maj_1']."</td>\n";
	echo "<td class=\"titre\">".$_SESSION['lang']['divers_nb_jours_maj_1']."</td>\n";
	echo "<td class=\"titre\">".$_SESSION['lang']['divers_comment_maj_1']."</td>\n";
	echo "</tr>\n" ;
	// affichage 2ieme ligne : valeurs actuelles
	echo "<tr align=\"center\">\n" ;
	while ($resultat1 = mysql_fetch_array($ReqLog1))
	{
		$sql_date_deb=eng_date_to_fr($resultat1["p_date_deb"]);
		$sql_demi_jour_deb = $resultat1["p_demi_jour_deb"];
		if($sql_demi_jour_deb=="am")
			$demi_j_deb=$_SESSION['lang']['divers_am_short'];
		else
			$demi_j_deb=$_SESSION['lang']['divers_pm_short'];
		$sql_date_fin=eng_date_to_fr($resultat1["p_date_fin"]);
		$sql_demi_jour_fin = $resultat1["p_demi_jour_fin"];
		if($sql_demi_jour_fin=="am")
			$demi_j_fin=$_SESSION['lang']['divers_am_short'];
		else
			$demi_j_fin=$_SESSION['lang']['divers_pm_short'];
		$sql_nb_jours=$resultat1["p_nb_jours"];
		$aff_nb_jours=affiche_decimal($sql_nb_jours);
		$sql_commentaire=$resultat1["p_commentaire"];
		$sql_etat=$resultat1["p_etat"];

		echo "<td class=\"histo\">$sql_date_deb _ $demi_j_deb</td><td class=\"histo\">$sql_date_fin _ $demi_j_fin</td><td class=\"histo\">$aff_nb_jours</td><td class=\"histo\">$sql_commentaire</td>\n" ;

		$text_debut="<input type=\"text\" name=\"new_debut\" size=\"10\" maxlength=\"30\" value=\"$sql_date_deb\">" ;
		if($sql_demi_jour_deb=="am")
		{
			$radio_deb_am="<input type=\"radio\" name=\"new_demi_jour_deb\" value=\"am\" checked>".$_SESSION['lang']['form_am'];
			$radio_deb_pm="<input type=\"radio\" name=\"new_demi_jour_deb\" value=\"pm\">".$_SESSION['lang']['form_pm'];
		}
		else
		{
			$radio_deb_am="<input type=\"radio\" name=\"new_demi_jour_deb\" value=\"am\">".$_SESSION['lang']['form_am'];
			$radio_deb_pm="<input type=\"radio\" name=\"new_demi_jour_deb\" value=\"pm\" checked>".$_SESSION['lang']['form_pm'];
		}
		$text_fin="<input type=\"text\" name=\"new_fin\" size=\"10\" maxlength=\"30\" value=\"$sql_date_fin\">" ;
		if($sql_demi_jour_fin=="am")
		{
			$radio_fin_am="<input type=\"radio\" name=\"new_demi_jour_fin\" value=\"am\" checked>".$_SESSION['lang']['form_am'];
			$radio_fin_pm="<input type=\"radio\" name=\"new_demi_jour_fin\" value=\"pm\">".$_SESSION['lang']['form_pm'];
		}
		else
		{
			$radio_fin_am="<input type=\"radio\" name=\"new_demi_jour_fin\" value=\"am\">".$_SESSION['lang']['form_am'];
			$radio_fin_pm="<input type=\"radio\" name=\"new_demi_jour_fin\" value=\"pm\" checked>".$_SESSION['lang']['form_pm'];
		}
		$text_nb_jours="<input type=\"text\" name=\"new_nb_jours\" size=\"5\" maxlength=\"30\" value=\"$sql_nb_jours\">" ;
		$text_commentaire="<input type=\"text\" name=\"new_comment\" size=\"15\" maxlength=\"30\" value=\"$sql_commentaire\">" ;
	}
	echo "</tr>\n";

	// affichage 3ieme ligne : saisie des nouvelles valeurs
	echo "<tr align=\"center\">\n" ;
	echo "<td class=\"histo\">$text_debut<br>$radio_deb_am / $radio_deb_pm</td><td class=\"histo\">$text_fin<br>$radio_fin_am / $radio_fin_pm</td><td class=\"histo\">$text_nb_jours</td><td class=\"histo\">$text_commentaire</td>\n" ;
	echo "</tr>\n" ;

	echo "</table><br>\n\n" ;
	echo "<input type=\"hidden\" name=\"p_num_to_update\" value=\"$p_num\">\n" ;
	echo "<input type=\"hidden\" name=\"p_etat\" value=\"$sql_etat\">\n" ;
	echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n" ;
	echo "<input type=\"hidden\" name=\"onglet\" value=\"$onglet\">\n" ;
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">\n" ;
	echo "</form>\n" ;

	echo "<form action=\"user_index.php?session=$session&onglet=$onglet\" method=\"POST\">\n" ;
	echo "<input type=\"submit\" value=\"".$_SESSION['lang']['form_cancel']."\">\n" ;
	echo "</form>\n" ;

	mysql_close($mysql_link);
}


function modifier($p_num_to_update, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $p_etat, $onglet, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;

	echo($new_debut." / ".$new_demi_jour_deb."---".$new_fin." / ".$new_demi_jour_fin."---".$new_nb_jours."---".$new_comment."<br>");

	//connexion mysql
	$mysql_link = connexion_mysql() ;

    /* _protectsql_ dpa */ 
    $new_comment = mysqli_real_escape_string($mysql_link, $new_comment);

	$sql1 = "UPDATE conges_periode
		SET p_date_deb=STR_TO_DATE('$new_debut', '%d-%m-%Y'), p_demi_jour_deb='$new_demi_jour_deb', p_date_fin=STR_TO_DATE('$new_fin', '%d-%m-%Y'), p_demi_jour_fin='$new_demi_jour_fin', p_nb_jours='$new_nb_jours', p_commentaire='$new_comment', ";
	if($p_etat=="demande")
		 $sql1 = $sql1." p_date_demande=NOW() ";
	else
		 $sql1 = $sql1." p_date_traitement=NOW() ";
	$sql1 = $sql1."	WHERE p_num='$p_num_to_update' AND p_login='".$_SESSION['userlogin']."' ;" ;

	$result = requete_mysql($sql1, $mysql_link, "modifier", $DEBUG) ;

	$comment_log = "modification de demande num $p_num_to_update ($new_nb_jours jour(s)) ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
	log_action($p_num_to_update, "$p_etat", $_SESSION['userlogin'], $comment_log, $mysql_link, $DEBUG);

	mysql_close($mysql_link);

	echo $_SESSION['lang']['form_modif_ok']."<br><br> \n" ;
	/* APPEL D'UNE AUTRE PAGE */
	echo " <form action=\"user_index.php?session=$session&onglet=$onglet\" method=\"POST\"> \n" ;
	echo " <input type=\"submit\" value=\"".$_SESSION['lang']['form_submit']."\">\n" ;
	echo " </form> \n" ;

}
?>
