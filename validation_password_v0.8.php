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

include("fonctions_conges.php") ;
$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
include("INCLUDE.PHP/fonction.php");


//
// MAIN
//

   if(isset($_POST['username'])) { $username=$_POST['username']; }
   if(isset($_POST['password'])) { $password=$_POST['password']; }
   
   if (($username == "") || ($password == ""))
   {
	echo "<html>\n<head>\n";
	echo "<link href=\"".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
	echo "<TITLE> PHP_CONGES : Validation de votre Mot de Passe : </TITLE>\n</head>\n";
	echo "<body class=\"login\">\n";
	saisie_user_password($username,$password);
	echo "</body>\n</html>\n";
	exit;
   }
   else
   {
      //  AUTHENTIFICATION :

      // le user doit etre authentifié dans la table conges (login + passwd) 
	 
      // si on a trouve personne qui correspond au couple user/password
      if (old_autentification_passwd_conges($username,$password) != $username)
      {
		echo "<html>\n<head>\n";
		echo "<link href=\"".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
		echo "<TITLE> PHP_CONGES : Validation de votre Mot de Passe :</TITLE>\n</head>\n";
		echo "<body class=\"login\">\n";
		echo "<CENTER>\n";
		echo "<H3>ERREUR : Nom d'utilisateur et/ou mot de passe incorrect !!!</H3><BR>\n";
		echo "</CENTER>\n";
		saisie_user_password($username,$password);
		echo "</body>\n</html>\n";
		exit;
      }

      if ((old_autentification_passwd_conges($username,$password) == $username) && ($username != ""))
      {
         update_password($username,$password);
      }
   }


/********************************************************************************************************/
/***   FONCTIONS   ***/
/********************************************************************************************************/

//
// formulaire de saisie du user/password
//
function saisie_user_password($username,$password)
{
   $PHP_SELF=$_SERVER['PHP_SELF'];

   echo "<CENTER>\n";

	echo "<table>\n";
	echo "<tr><td align=\"center\">\n";
			echo "<a href=\"".$_SESSION['config']['lien_img_login']."\" target=\"_parent\">";
			echo "<img src=\"".$_SESSION['config']['img_login']."\" alt=\"".$_SESSION['config']['texte_img_login']."\" title=\"".$_SESSION['config']['texte_img_login']."\"/>";
			echo "</a>";
			echo "<br><br><br>\n";
	echo "</td></tr>\n";
	
	echo "<tr><td align=\"center\">\n";
		echo "<FORM METHOD='post' ACTION='$PHP_SELF'>\n";
		
		echo "<fieldset class=\"boxlogin\">\n";
		echo "<legend class=\"boxlogin\">PHP_CONGES : Validation de votre Mot de Passe :</legend>\n";
//		echo "<TABLE BGCOLOR='#dcdcdc' BORDER='1'>\n";
		echo "<TABLE class=\"ident\">\n";
		echo "<TR>\n";
		echo "	<TD class=\"login\">Login :</TD>\n";
		echo "	<TD><INPUT TYPE='text'     NAME='username' SIZE=32  VALUE='$username'></TD>\n";
		echo "</TR>\n";
		echo "<TR>\n";
		echo "	<TD class=\"login\">Mot de Passe :</TD>\n";
		echo "	<TD><INPUT TYPE='password' NAME='password' SIZE=32  VALUE='$password'></TD>\n";
		echo "</TR>\n";
		echo "</TABLE>\n";
		echo "</fieldset>\n";
		
		echo "<TABLE BORDER='0'>\n";
		echo "<TR>\n";
		echo "	<TD COLSPAN='2'><CENTER><INPUT TYPE='submit' VALUE='Valider'></CENTER></TD>\n";
		echo "</TR>\n";
		echo "</TABLE>\n";
		echo "</FORM>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	
	echo "<table width=\"100%\">\n";
	echo "<tr><td align=\"right\">\n";
			echo "<br><br>";
			echo "<a href=\"".$_SESSION['config']['url_site_web_php_conges']."/\">PHP_CONGES v ".$_SESSION['config']['php_conges_version']."</a>\n";
	echo "</td></tr>\n";
	echo "</table>\n";

   echo "</CENTER>\n";
}

//
// autentifie un user dans le base mysql avec son login et son passwd (avec cryptage mysql)
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
//
function old_autentification_passwd_conges($username,$password)
{
	// connexion MySQL + selection de la database sur le serveur
	$mysql_link=connexion_mysql();

	$username_password_ok="";
	
	$req_conges="SELECT u_passwd   FROM conges_users   WHERE u_login='$username' AND u_passwd=password('$password') " ;
	$res_conges = mysql_query($req_conges,$mysql_link) or die("autentification_passwd_conges() : Erreur ".mysql_error());
	$num_row_conges = mysql_num_rows($res_conges);
	if ($num_row_conges !=0)
	{
		$username_password_ok=$username;
	}
	
	mysql_close($mysql_link);

	return   $username_password_ok;
}


// change dans la database db_conges le mot de passe du user (passe du cryptage mysql au crptage md5)
function update_password($username,$password)
{
	//connexion mysql
	$mysql_link = connexion_mysql() ;
	
	$passwd_md5=md5($password);
	$sql1 = "UPDATE conges_users  SET u_passwd='$passwd_md5' WHERE u_login='$username'" ;
	$result = mysql_query($sql1, $mysql_link) or die("ERREUR : mysql_query : ".$sql1." --> ".mysql_error());

	if($result==TRUE)
		printf("<center> Changements pris en compte avec succes !<br><br> </center>\n");
	else
		printf("<center> ERREUR ! Changements NON pris en compte !<br><br> </center>\n");

	mysql_close($mysql_link);
	
	/* APPEL D'UNE AUTRE PAGE */
	echo "<center><a href=\"".$_SESSION['config']['URL_ACCUEIL_CONGES']."/\">aller à PHP_CONGES ...</a><center>\n";
}


?>
