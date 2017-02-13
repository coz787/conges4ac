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
	if(file_exists("controle_ids.php"))  // test si on est appelé de la racine
		include("controle_ids.php") ;
	else
		include("../controle_ids.php") ;
}
error_log("request_uri is ".$_SERVER["REQUEST_URI"]); 
//
// MAIN

//

/*** initialisation des variables ***/
$session_username="";
$session_password="";
/************************************/

//
// recup du num  de session (mais on ne sais pas s'il est passé en GET ou POST
$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : "") ) ;

if (isset($_GET['websso']) ) { 
  $gwebsso = strtolower($_GET['websso']) ; 
} else { 
  $gwebsso = "yes" ; // default 
}

$luri = explode("/",$_SERVER["REQUEST_URI"]);
$urin = array_pop($luri); 
$urin_1 = array_pop($luri); 
error_log("urin_1 is ".$urin_1);

/* websso  vrai alors on utilise la configuration, 
           faux alors on bypass principe websso */ 
if (in_array($urin_1, ["config"])) { 
  $bwebsso = FALSE ; 
} else { 
  if ( $gwebsso == "no")  { 
    $bwebsso = FALSE ; 
  } else {
    $bwebsso = TRUE  ; 
  }
}

$DEBUG=FALSE;
//$DEBUG=TRUE;

if($DEBUG==TRUE) { print_r($_SESSION); echo "<br><br>\n"; }

if ($session != "") //  UNE SESSION EXISTE
{
	if($DEBUG==TRUE) { echo "session = $session<br><br>\n"; }
	
	if(session_is_valid($session) == TRUE)
	{
		session_update($session);
	}
	else
	{
		session_delete($session);
		$session="";
		$session_username="";
		$session_password="";
		$_SESSION['config']=init_config_tab();  // on recrée le tableau de config pour l'url du lien
		
//		echo "<center>\n";
//		echo "Pas de session ouverte<br>\n";
//		echo "Veuillez <a href='".$_SESSION['config']['URL_ACCUEIL_CONGES']."/index.php' target='_top'> vous authentifier</a>\n";
//		echo "</center>\n";
		echo "<center>\n";
		echo $_SESSION['lang']['session_pas_session_ouverte']."<br>\n";
		echo $_SESSION['lang']['divers_veuillez']." <a href='".$_SESSION['config']['URL_ACCUEIL_CONGES']."/index.php' target='_top'> ".$_SESSION['lang']['divers_vous_authentifier']."</a>\n";
		echo "</center>\n";

		exit;
	}
}
else    //  PAS DE SESSION   ($session == "")
{
	if($DEBUG==TRUE) { echo "session = chaine VIDE!<br><br>\n"; }

	if(isset($_POST['session_username'])) { $session_username=$_POST['session_username']; }
	if(isset($_POST['session_password'])) { 
      $session_password=$_POST['session_password'];
      //if (!get_magic_quotes_gpc()) {
      //  $session_password = addslashes($session_password);
      //};
    }
    // error_log("SPSP: ". $session_password) ; 
	if ( ($_SESSION['config']['how_to_connect_user'] == "CAS") && 
         ($session_username != "admin")  && ($bwebsso)          )
	{
		if($DEBUG==TRUE) { echo "autentification CAS !<br><br>\n"; }
		
		$usernameCAS = authentification_passwd_conges_CAS();
		if($usernameCAS != "")
		{
			if(session_id()!="")
				session_destroy();
			
			// on initialise la nouvelle session
			session_create($usernameCAS);

			// on log la connexion du user 
			if(!isset($mysql_link))
			{
				//connexion mysql
				$mysql_link = connexion_mysql() ;
					
				$comment_log = "Connexion de $session_username";
				log_action(0, "", $usernameCAS, $comment_log, $mysql_link, $DEBUG);
						
				mysql_close();
			}
			else
			{
				$comment_log = "Connexion de $session_username";
				log_action(0, "", $usernameCAS, $comment_log, $mysql_link, $DEBUG);
			}
		}
		else //dans ce cas l'utilisateur n'a pas encore été enregistré dans la base de données db_conges
		{
		   echo "<center>\n";
		   echo "Il n'existe pas de compte correspondant à votre login dans la base de données de PHP_CONGES<br>\n";
		   echo "Contactez l'administrateur de php_conges";
		   echo "</center>\n";
		}
	}
	else
	{
		if (($session_username == "") || ($session_password == "")) // si login et passwd non saisis
		{
			//  SAISIE LOGIN / PASSWORD :
			session_saisie_user_password("", "", ""); // appel du formulaire d'intentification (login/password)
			exit;
		}
		else
		{
			//  AUTHENTIFICATION :

			// le user doit etre authentifié dans la table conges (login + passwd) ou dans le ldap.
			// si on a trouve personne qui correspond au couple user/password

			if ( ($_SESSION['config']['how_to_connect_user'] == "ldap") && ($session_username != "admin") )
			{
				if($DEBUG==TRUE) { echo "autentification LDAP !<br><br>\n"; }

				if(session_id()!="")
					session_destroy();
					
				if (authentification_ldap_conges($session_username,$session_password) != $session_username)
				{
					$session="";
					$session_username="";
					$session_password="";

					$erreur="login_passwd_incorrect";
					// appel du formulaire d'intentification (login/password)
					session_saisie_user_password($erreur, $session_username, $session_password);
					exit;
				}

				if ((authentification_ldap_conges($session_username,$session_password) == $session_username) && ($session_username != ""))
				{
					if (valid_ldap_user($session_username)==TRUE) // LDAP ok, on vérifie ici que le compte existe dans la base de données des congés.
					{
						// on initialise la nouvelle session
                      session_create($session_username);

						// on log la connexion du user 
						if(!isset($mysql_link))
						{
							//connexion mysql
							$mysql_link = connexion_mysql() ;
								
							$comment_log = "Connexion de $session_username";
							log_action(0, "", $session_username, $comment_log, $mysql_link, $DEBUG);
							
							mysql_close();
						}
						else
						{
							$comment_log = "Connexion de $session_username";
							log_action(0, "", $session_username, $comment_log, $mysql_link, $DEBUG);
						}
					}
					else//dans ce cas l'utilisateur n'a pas encore été enregistré dans la base de données db_conges
					{
						$erreur="login_non_connu";
						session_saisie_user_password($erreur, $session_username,$session_password); // appel du formulaire d'intentification (login/password)
						exit;	         	         	
					}
				}
			} // fin du if test avec ldap
			elseif ( ($_SESSION['config']['how_to_connect_user'] == "dbconges") || ($session_username == "admin") )
			{
				if($DEBUG==TRUE) { echo "autentification DBCONGES !<br><br>\n"; }

				if(session_id()!="")
					session_destroy();
					
				if (autentification_passwd_conges($session_username,$session_password) != $session_username)
				{
					$session="";
					$session_username="";
					$session_password="";

					$erreur="login_passwd_incorrect";
					session_saisie_user_password($erreur, $session_username,$session_password); // appel du formulaire d'intentification (login/password)
					exit;
				}

				if ((autentification_passwd_conges($session_username,$session_password) == $session_username) && ($session_username != ""))
				{
					// on initialise la nouvelle session
					session_create($session_username);
					
					// on log la connexion du user 
					if(!isset($mysql_link))
					{
						//connexion mysql
						$mysql_link = connexion_mysql() ;
							
						$comment_log = "Connexion de $session_username";
						log_action(0, "", $session_username, $comment_log, $mysql_link, $DEBUG);
						
						mysql_close();
					}
					else
					{
						$comment_log = "Connexion de $session_username";
						log_action(0, "", $session_username, $comment_log, $mysql_link, $DEBUG);
					}
				}
			}
		}
	}
   
}





?>
