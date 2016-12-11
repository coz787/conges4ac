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
if(phpversion() > "5.1.2")
{
  // error_log("_gip_".get_include_path()); 
  // _dpa on test inclusion sur trois niveau . .. ../.. 
  
  if(file_exists("controle_ids.php"))  // test si on est appelé de la racine
    include("controle_ids.php") ;
  elseif (file_exists("../controle_ids.php"))
    include("../controle_ids.php") ;
  else 
    include("../../controle_ids.php") ;
}
/*===============================================================================*/
/*===============================================================================*/

// connexion MySQL + selection de la database sur le serveur
//function  connexion_mysql()

// AFFICHAGE de la requete SQL   si debug == TRUE
// EXECUTION de la requete SQL   si debug != TRUE
//function requete_mysql($requete,$mysql_link,$fonction_name,$debug="FALSE")

// indique (TRUE / FALSE) si une session est valide (par / au temps de connexion)
//function session_is_valid($session)

// cree la session et renvoie son identifiant
//function session_create($username)

// mise a jour d'une session
//function session_update($session)

// destruction d'une session
//function session_delete($session)

// destruction des sessions inactives (c.a.d. dont le temps de connexion est dépassé)
//function delete_expired_session()

// formulaire de saisie du user/password
//function session_saisie_user_password($erreur)

// autentifie un user dans le base mysql avec son login et son passwd conges :
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
//function autentification_passwd_conges($username,$password)

// supprime les accents d'une chaine de caracteres
//function supprime_accent($string)

// genère le mot de passe MySQL à partir du mot de passe en clair
//function make_mysql_password($password)

// authentification du login/passwd sur un serveur LDAP
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
//function authentification_ldap_conges($username,$password)

// Authentifie l'utilisateur auprès du serveur CAS, puis auprès de la base de donnée.
// Si le login qui a permis d'authentifier l'utilisateur auprès du serveur
//	CAS existe en tant que login d'une entrée de la table conges_user, alors
//	l'authentification est réussie et passwCAS renvoi le nom de l'utilisateur, "" sinon.
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
//function authentification_passwd_conges_CAS()

/*===============================================================================*/
/*===============================================================================*/

//
// connexion MySQL + selection de la database sur le serveur
//
function  connexion_mysql()
{
   $mysql_link = MYSQL_CONNECT($_SESSION['config']['mysql_serveur'],$_SESSION['config']['mysql_user'],$_SESSION['config']['mysql_pass']);
   if (! $mysql_link)
   {
      die("connexion_mysql() : ".$_SESSION['lang']['mysql_srv_connect_failed']."<br>\n".mysql_error());
   }
    // _ac3 
    if ( ! mysql_set_charset($_SESSION['config']['mysql_charset'],$mysql_link) ) {
      die("mysql_set_charset() : set_charset ".$_SESSION['config']['mysql_charset'].
          " en erreur ". mysql_error($mysql_link));
    };
   
   $dbselect   = mysql_select_db($_SESSION['config']['mysql_database'],$mysql_link);
   if (! $dbselect)
   {
      die("connexion_mysql() : ".$_SESSION['lang']['mysql_db_connect_failed']."<br>\n".mysql_error());
   }

   return $mysql_link;
}


//
// AFFICHAGE de la requete SQL   si debug == TRUE
// EXECUTION de la requete SQL   si debug != TRUE
//
function    requete_mysql($requete,$mysql_link,$fonction_name,$debug=FALSE)
{
   //
   // PARAMETRES :
   //  - $requete          : requete SQL a executer
   //  - $mysql_link       : handler MySQL
   //  - $fonction_name    : nom de la fonction qui a appele "requete_mysql"  <==> BLABLA à afficher
   //  - $debug            : si == TRUE, afficher requete SINON executer requete
   //

   if ($debug != TRUE)
   {
      $res = mysql_query($requete,$mysql_link)   or die("$fonction_name() : Erreur :<br>\n'" . mysql_error($mysql_link) . "'<br>\n sur '$requete' <BR>");
   }
   else
   {
      echo "DEBUG : $fonction_name() : requete='$requete'<BR>\n";
      if(eregi("^.*SELECT.+FROM.+$", $requete))
      {
         $res = mysql_query($requete,$mysql_link)   or die("$fonction_name() : Erreur :<br>\n'" . mysql_error($mysql_link) . "'<br>\n sur '$requete' <BR>");
      	 echo "requete executée ...<BR>\n";
      }
      elseif(eregi("^.*DESCRIBE.*$",$requete))
      {
         $res = mysql_query($requete,$mysql_link)   or die("$fonction_name() : Erreur :<br>\n'" . mysql_error($mysql_link) . "'<br>\n sur '$requete' <BR>");
      	 echo "requete non executée ...<BR>\n";
      }
      else
      {
      	 echo "requete non reconnue ; non executée ...<BR>\n";
         $res = TRUE;
      }
   }

   return   $res;
}

//
// indique (TRUE / FALSE) si une session est valide (par / au temps de connexion)
//
function session_is_valid($session)
{
   // ATTENTION:  on fixe l'id de session comme nom de session pour que , sur un meme pc, on puisse se loguer sous 2 users à la fois
   if (session_id() == "")
   {
      session_name($session);
      session_start();
   }
   $is_valid=FALSE;
   // pour eviter risque d'utilisation croisee des applications sur un meme site
   // first we compare $_SERVER["SCRIPT_FILENAME"] to $_SESSION['config']['php_conges_document_root']
   $ssfn = $_SERVER["SCRIPT_FILENAME"] ;
   $spdc = $_SESSION['config']['php_conges_document_root'] ; 
   $lsfn = explode("/",$ssfn);
   $idx = 0 ; 
   while ($idx <= 2) { // poping at max 2 element  
     $sexsfn = implode("/",$lsfn) ; // reassemble of the path 
     if ($sexsfn == $spdc) { 
       error_log("session_is_valid: ".$sexsfn." ".$spdc);
       break ; // OK : good php_conges_document_root
     } 
     array_pop($lsfn); // we pop last element and loop 
     $idx++ ; 
   }
   if ( $idx > 2 ) { 
     /* we do not find the correct php_conges_document_root 
        so we return FALSE */ 
     return FALSE ; 
   };
     
   if( (isset($_SESSION['timestamp_last'])) && (isset($_SESSION['config'])) ) {
     $difference = time() - $_SESSION['timestamp_last'];

     if ( ($session==session_id()) && ($difference < $_SESSION['config']['duree_session']) ) {
       $is_valid=TRUE;
     }
	}
   /* error_log("session_is_valid: ".$difference."-".$_SESSION['config']['duree_session']."-".$is_valid) ;
    error_log("session_is_valid: ".$_SESSION['config']['script_filename']);
    error_log("session_is_valid: SCRIPT_FILENAME: ".$_SERVER["SCRIPT_FILENAME"]); 
    error_log("session_is_valid: script_filename: ".$_SESSION['config']['script_filename']); 
    error_log("session_is_valid: php_conges_document_root: ".
    $_SESSION['config']['php_conges_document_root']); */
   
   return $is_valid;
}



//
// cree la session et renvoie son identifiant
//
function session_create($username)
{
	if ($username != "")
	{
      // $session = "phpconges".md5(uniqid(rand()));
      $session = "c4ac-".md5(uniqid(rand()));
      session_name($session);
      session_id($session);

      session_start();
      $_SESSION['userlogin']=$username;
      $maintenant=time();
      $_SESSION['timestamp_start']=$maintenant;
      $_SESSION['timestamp_last']=$maintenant;
      if (function_exists('init_config_tab')) {
        $_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
      };		//$session=session_id();

	}
	else
	{
		$session="";
	}

	return   $session;
}



//
// mise a jour d'une session
//
function session_update($session)
{
   if ($session != "")
   {
		$maintenant=time();
		$_SESSION['timestamp_last']=$maintenant;
   }
}



//
// destruction d'une session
//
function session_delete($session)
{
   if ($session != "")
   {
	 unset($_SESSION['userlogin']);
	 unset($_SESSION['timestamp_start']);
	 unset($_SESSION['timestamp_last']);
	 unset($_SESSION['tab_j_feries']);
	 unset($_SESSION['config']);
	 session_destroy();
   }
}



//
// formulaire de saisie du user/password
//
function session_saisie_user_password($erreur, $session_username, $session_password)
{
   $PHP_SELF=$_SERVER['PHP_SELF'];

	$config_lien_img_login          =$_SESSION['config']['lien_img_login'];
	$config_img_login               =$_SESSION['config']['img_login'];
	$config_texte_img_login         =$_SESSION['config']['texte_img_login'];
	$config_texte_page_login        =$_SESSION['config']['texte_page_login'];
//	$config_php_conges_version      =$_SESSION['config']['php_conges_version'];
	$config_php_conges_version      =$_SESSION['config']['installed_version'];
    if ( array_key_exists('installed_state', $_SESSION['config']) ) {
      $config_php_conges_state      =$_SESSION['config']['installed_state'];
    } else {
      $config_php_conges_state      = "--" ; 
    }

	$config_url_site_web_php_conges =$_SESSION['config']['url_site_web_php_conges'];
	$config_stylesheet_file         =$_SESSION['config']['stylesheet_file'];

	// verif si on est dans le repertoire install
	if(substr(dirname ($_SERVER["SCRIPT_FILENAME"]), -6, 6) == "config")   // verif si on est dans le repertoire install
		$config_dir=TRUE;
	else
		$config_dir=FALSE;


	echo "<html>\n<head>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
	echo "<link href=\"".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
	echo "<TITLE>".$_SESSION['config']['titre_application'].": </TITLE>\n";   // ".$_SESSION['config']['titre_application']."
	// test que le navigateur accepte les cookies et le javascript
	echo "<script language=\"JavaScript\" type=\"text/javascript\">\n";
	echo "<!--\n";
	echo "// Les cookies sont obligatoires\n";
	echo "if (! navigator.cookieEnabled) {\n";
	echo "	document.write(\"<font color='#FF0000'><br><br><center>".$_SESSION['lang']['cookies_obligatoires']."</center></font><br><br>\");\n";
	echo "}\n";
	echo "//-->\n";
	echo "</script>\n";
	echo "<noscript>\n";
	echo "		<font color=\"#FF0000\"><br><br><center>".$_SESSION['lang']['javascript_obligatoires']."</center></font><br><br>\n";
	echo "</noscript>\n";

	echo "</head>\n";
	echo "<body class=\"login\">\n";

	echo "<CENTER>\n";
	if($erreur=="login_passwd_incorrect") 
		echo "<H3>".$_SESSION['lang']['login_passwd_incorrect']."</H3><BR>\n";
	elseif($erreur=="login_non_connu")
		echo "<H3>".$_SESSION['lang']['login_non_connu']."</H3><BR>\n";
	echo "</CENTER>\n";

	echo "<CENTER>\n";
	echo "<table>\n";

	if(!$config_dir) // si on est dans le repertoire config on affiche pas les liens
	{
		echo "<tr><td align=\"center\">\n";
			echo "<a href=\"$config_lien_img_login\" target=\"_parent\">";
			echo "<img src=\"$config_img_login\" alt=\"$config_texte_img_login\" title=\"$config_texte_img_login\"/>";
			echo "</a>";
			if($config_texte_page_login != "")
			{
				echo "<br><br>\n";
				echo "</td></tr>\n";
				echo "<tr><td align=\"center\">\n";
					echo "<h1>$config_texte_page_login</h1>";
			}
			echo "<br><br><br>\n";
		echo "</td></tr>\n";
	}

	echo "<tr><td align=\"center\">\n";
		echo "<FORM METHOD='post' ACTION='$PHP_SELF'>\n";

		echo "<fieldset class=\"boxlogin\">\n";
		echo "<legend class=\"boxlogin\">".$_SESSION['lang']['login_fieldset']."</legend>\n";
		echo "<TABLE class=\"ident\">\n";
		echo "<TR>\n";
		echo "	<TD class=\"login\">".$_SESSION['lang']['divers_login_maj_1']." :</TD>\n";
		echo "	<TD><INPUT TYPE='text'     NAME='session_username' SIZE=32  VALUE='$session_username'></TD>\n";
		echo "</TR>\n";
		echo "<TR>\n";
		echo "	<TD class=\"login\">".$_SESSION['lang']['password']." :</TD>\n";
		echo "	<TD><INPUT TYPE='password' NAME='session_password' SIZE=32  VALUE='$session_password'></TD>\n";
		echo "</TR>\n";
		echo "</TABLE>\n";
		echo "</fieldset>\n";

		echo "<TABLE BORDER='0'>\n";
		echo "<TR>\n";
		echo "	<TD COLSPAN='2'><CENTER><INPUT TYPE='submit' VALUE='".$_SESSION['lang']['form_submit']."'></CENTER></TD>\n";
		echo "</TR>\n";
		echo "</TABLE>\n";
		echo "</FORM>\n";
	echo "</td></tr>\n";

	if( (!$config_dir) && ($_SESSION['config']['consult_calendrier_sans_auth']==TRUE) ) // si on est pas dans le repertoire config ET acces calendrier sans login actif
	{
		echo "<tr><td align=\"center\">\n";
		echo "<a href=\"calendrier.php\">" .
				"<img src=\"img/1day.png\" width=\"24\" height=\"24\" border=\"0\" title=\"".$_SESSION['lang']['button_calendar']."\" alt=\"".$_SESSION['lang']['button_calendar']."\">" .
				" ".$_SESSION['lang']['button_calendar']."</a>\n";
		echo "</td></tr>\n";
	}
	echo "</table>\n";

	if(!$config_dir) // si on est dans le repertoire config on affiche pas les liens
	{
      // dpa affichage d'un message de service 

        if ( array_key_exists('message_for_all', $_SESSION['config']) ) {
          $config_message_for_all = $_SESSION['config']['message_for_all'] ;
        };
        if (isset($config_message_for_all) && $config_message_for_all != "" ) {
          echo "<em>".$config_message_for_all."</em>" ;
        };        


		echo "<table width=\"100%\">\n";
		if( (isset($_SERVER["HTTP_USER_AGENT"])) && (stristr($_SERVER["HTTP_USER_AGENT"], "MSIE")!=FALSE) )
		{
			echo "<tr><td align=\"center\">";
			echo "<img src=\"img/attention.png\" width=\"22\" height=\"22\" border=\"0\"/>";
			echo "&nbsp;".$_SESSION['lang']['msie_alert'] ;
			echo "</td></tr>\n";
		}
		echo "<tr>\n"; 

  // dpa : ajout url sur espace projet conges sous BV 
  // todo : rendre cela parametrable via une variable comme $config_url_conges_assistance
  //        pour eviter de reoucher le code si la page change 
        if ( array_key_exists('url_conges_assistance', $_SESSION['config']) ) {
          $config_url_conges_assistance = $_SESSION['config']['url_conges_assistance'] ; 
        };
        if (isset($config_url_conges_assistance)) {
          echo "<td align=\"left\">\n";
          echo "<br><br>";
          echo "<a TARGET=\"_blank\" href=\"".$config_url_conges_assistance ."\">DGAC/BV::espace projet conges</a>\n" ;
          // http://portail-dgac.aviation-civile.gouv.fr/portal/server.pt/gateway/PTARGS_0_57406_222_1854_57335_43/http%3B/collaboration%3B11930/collab/do/project/overview;jsessionid=4E3E4AAF0FD7F577894BABDB1C8725FC?projID=52894 
          echo "</td>\n";
        };        
        echo "<td align=\"right\">\n";
			echo "<br><br>";
			echo "<a href=\"$config_url_site_web_php_conges/\">PHP_CONGES v $config_php_conges_version $config_php_conges_state</a>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
	}

	echo "</CENTER>\n";
	echo "</body>\n</html>\n";
}



//
// autentifie un user dans le base mysql avec son login et son passwd conges :
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
//
function autentification_passwd_conges($username,$password)
{
	// connexion MySQL + selection de la database sur le serveur
	$mysql_link=connexion_mysql();

	$username_password_ok="";

	$password_md5=md5($password);
//	$req_conges="SELECT u_passwd   FROM conges_users   WHERE u_login='$username' AND u_passwd='$password_md5' " ;
	// on conserve le double mode d'autentificatio (nouveau cryptage (md5) ou ancien cryptage (mysql))
	$req_conges="SELECT u_passwd   FROM conges_users   WHERE u_login='$username' AND ( u_passwd='$password_md5' OR u_passwd=PASSWORD('$password') ) " ;
	// $req_conges="SELECT u_passwd   FROM conges_users   WHERE u_login='$username' AND ( u_passwd='$password_md5' OR u_passwd=PASSWORD('".$password."') ) " ;

	$res_conges = mysql_query($req_conges,$mysql_link) or die("autentification_passwd_conges() : Erreur ".mysql_error());
	$num_row_conges = mysql_num_rows($res_conges);
	if ($num_row_conges !=0)
	{
		$username_password_ok=$username;
	}

	return   $username_password_ok;
}



//
// supprime les accents d'une chaine de caracteres
//
function supprime_accent($string)
{
   $string_noaccent = $string;
   $string_noaccent = strtr($string_noaccent, "áàâäãéèêëíìîïóòôöõúùûüýÿçñ", "aaaaaeeeeiiiiooooouuuuyycn");
   $string_noaccent = strtr($string_noaccent, "ÁÀÂÄÃÉÈÊËÍÌÎÏÓÒÔÖÕÚÙÛÜÝYÇÑ", "AAAAAEEEEIIIIOOOOOUUUUYYCN");

   return $string_noaccent;
}


//
// genère le mot de passe MySQL à partir du mot de passe en clair
//
function make_mysql_password($password)
{
   // connexion MySQL + selection de la database sur le serveur
   $mysql_link=connexion_mysql();


   $mysqlpassword="";

   $req_password = "SELECT OLD_PASSWORD('$password'),PASSWORD('$password')";
   $res_password = mysql_query($req_password,$mysql_link) or die("make_mysql_password() : Erreur ".mysql_error());

   if ($row_password = mysql_fetch_array($res_password))  /* si un enregistrement deja dans la table unix */
   {
      $mysql_old_password=$row_password["OLD_PASSWORD('$password')"];
      $mysql_new_password=$row_password["PASSWORD('$password')"];

      //
      // ATTENTION : selon la version de MySQL ...
      //
      $mysqlpassword=$mysql_old_password;
   }

   return $mysqlpassword;
}

// authentification du login/passwd sur un serveur LDAP
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
//
function authentification_ldap_conges($username,$password)
{
   require_once ($_SESSION['config']['php_conges_authldap_include_path']."/authLDAP.php");

   $username_password_ok="";

   $a = new authLDAP();
   //$a->DEBUG = 1;
   $a->bind($username,$password);
   if ($a->is_authentificated())
   {
 	$username_password_ok=$username;
  }

   return   $username_password_ok;
}

// is_valid_login : verifie si un login existe dans l'annuaire 
function is_valid_login($username)
{
   require_once ($_SESSION['config']['php_conges_authldap_include_path']."/authLDAP.php");
   $oldap = new authLDAP();
   return $oldap->is_login($username) ; 
}

// Authentifie l'utilisateur auprès du serveur CAS, puis auprès de la base de donnée.
// Si le login qui a permis d'authentifier l'utilisateur auprès du serveur
//	CAS existe en tant que login d'une entrée de la table conges_user, alors
//	l'authentification est réussie et passwCAS renvoi le nom de l'utilisateur, "" sinon.
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
function authentification_passwd_conges_CAS()
{
	// import de la librairie CAS
	include_once($_SESSION['config']['php_conges_cas_include_path']."/CAS/CAS.php");
	// import des paramètres du serveur CAS

	$config_CAS_host       =$_SESSION['config']['CAS_host'];
	$config_CAS_portNumber =$_SESSION['config']['CAS_portNumber'];
	$config_CAS_URI        =$_SESSION['config']['CAS_URI'];

	global $connexionCAS;
	global $logoutCas;

	$username_password_ok="";

	phpCAS::setDebug();

	// initialisation phpCAS
	if($connexionCAS!="active")
	{
		$CASCnx = phpCAS::client(CAS_VERSION_2_0,$config_CAS_host,$config_CAS_portNumber,$config_CAS_URI);
		$connexionCAS = "active";

	}

	if($logoutCas==1)
	{
		phpCAS::logout();
	}

	// authentificationCAS (redirection vers la page d'authentification de CAS)
	phpCAS::forceAuthentication();

	//L'utilisateur a été correctement identifié

	$usernameCAS = phpCAS::getUser();
	session_create($usernameCAS);
	$mysql_link =connexion_mysql();

	//ON VERIFIE ICI QUE L'UTILISATEUR EST DEJA ENREGISTRE SOUS DBCONGES
	$req_conges = "SELECT u_login FROM conges_users WHERE u_login='$usernameCAS'";
	$res_conges = mysql_query($req_conges,$mysql_link) or die("authentification_passwd_conges_CAS() : Erreur :<br>\n$req_conges<br>\n".mysql_error());
	$num_row_conges = mysql_num_rows($res_conges);
	if($num_row_conges !=0)
		$username_password_ok = $usernameCAS;

	return $username_password_ok;
}


?>
