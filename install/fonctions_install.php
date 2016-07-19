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

// teste le fichier config.php 
//renvoit TRUE si ok, et FALSE sinon
function test_config_file($DEBUG=FALSE)
{
	// verif si le fichier "config.php" existe et est lisible ....
	$filename = '../config.php';
	if (!is_readable($filename)) 
	{
		return FALSE;
	} 
	else
		return TRUE;
}


// teste le fichier dbconnect.php 
//renvoit TRUE si ok, et FALSE sinon
function test_dbconnect_file($DEBUG=FALSE)
{
	// verif si le fichier "dbconnect.php" existe et est lisible ....
	$filename = '../dbconnect.php';
	if (!is_readable($filename)) 
	{
		return FALSE;
	} 
	else
		return TRUE;
}


// teste l'ancien fichier de conf config_old.php // mis par le user pour upgrade v1.0 to v1.1
//renvoit TRUE si ok, et FALSE sinon
function test_old_config_file($DEBUG=FALSE)
{
	// verif si le fichier "config_old.php" existe et est lisible ....
	$filename = 'config_old.php';
	if (!is_readable($filename)) 
	{
		return FALSE;
	} 
	else
		return TRUE;
}


// teste l'existance et la conexion à la database
//renvoit TRUE si ok, et FALSE sinon
function test_database($DEBUG=FALSE)
{
	
	include("../dbconnect.php") ;

	if( isset($mysql_serveur) && ($mysql_serveur!="") && isset($mysql_user) && ($mysql_user!="") 
			&& isset($mysql_pass) && ($mysql_pass!="") && isset($mysql_database) && ($mysql_database!="") )
	{
		$mysql_link = MYSQL_CONNECT($mysql_serveur,$mysql_user,$mysql_pass);
        // _ac3 
        if ( ! mysql_set_charset($mysql_charset,$mysql_link) ) {
          die("mysql_set_charset() : set_charset ".$mysql_charset.
              " en erreur ". mysql_error($mysql_link));
        };

        
		if (! $mysql_link)
			return FALSE;
		else
		{		
			$dbselect   = mysql_select_db($mysql_database, $mysql_link);
			if (! $dbselect)
				return FALSE;
			else
			{
				mysql_close($mysql_link);
				return TRUE ;
			}
		}
	}
	else
		return FALSE;
}


function mysql_connexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database,$mysql_charset="")
{
	$mysql_link = MYSQL_CONNECT($mysql_serveur, $mysql_user, $mysql_pass) or die("erreur : mysql_connexion<br>\n".mysql_error());
    // _ac3 
    if ( ! mysql_set_charset($mysql_charset,$mysql_link) ) {
      die("mysql_set_charset() : set_charset ".$mysql_charset.
          " en erreur ". mysql_error($mysql_link));
    };


		
	$dbselect   = mysql_select_db($mysql_database, $mysql_link) or die("erreur : mysql_connexion<br>\n".mysql_error());

	return $mysql_link;
}


// renvoit le num de la version installée ou 0 s'il est inaccessible (non renseigné ou table non présente) 
function get_installed_version($mysql_link, $DEBUG=FALSE)
{
	$installed_version=0;
	
	$sql="SELECT conf_valeur FROM conges_config WHERE conf_nom='installed_version' ";
	if($reglog= mysql_query($sql, $mysql_link))
	{
		// la table existe !
		if($result=mysql_fetch_array($reglog))
		{
			if($DEBUG==TRUE) { echo "result = <br>\n"; print_r($result); echo "<br>\n"; }
			$installed_version = $result['conf_valeur'];
		}
	}
	if($DEBUG==TRUE) { echo "installed_version = $installed_version <br>\n"; }
	
	return $installed_version ;
}



// teste la creation de table (verif si le user a les droits suffisants ou pas)
// renvoit TRUE ou FALSE
function test_create_table($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	/*********************************************/
	// creation de la table `conges_test`
	$sql_create="CREATE TABLE `conges_test` (
				`test1` varchar(100) BINARY NOT NULL default '',
				`test2` varchar(100) BINARY NOT NULL default '',
 				 PRIMARY KEY  (`test1`)
				) " ;
	$result_create = mysql_query($sql_create, $mysql_link);
	if(!$result_create)
		return FALSE;
	else
		return TRUE;	
}


// teste "alter table" (verif si le user a les droits suffisants ou pas)
// renvoit TRUE ou FALSE
function test_alter_table($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	/*********************************************/
	// alter de la table `conges_test`
	$sql_alter="ALTER TABLE `conges_test` CHANGE `test2` `test2` varchar(150) ;" ;
	$result_alter = mysql_query($sql_alter, $mysql_link) or die(mysql_error());
	if(!$result_alter)
		return FALSE;
	else
		return TRUE;	
}


// teste la suppression de table (verif si le user a les droits suffisants ou pas)
// renvoit TRUE ou FALSE
function test_drop_table($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	/*********************************************/
	// suppression de la table `conges_test`
	$sql_drop="DROP TABLE `conges_test` ;" ;
	$result_drop = mysql_query($sql_drop, $mysql_link);
	if(!$result_drop)
		return FALSE;
	else
		return TRUE;	
}


// verif si la valeur de "rtt_comme_conges" est TRUE dans la config ou non
/// renvoit TRUE si TRUE et FALSE si FALSE
function is_rtt_comme_conges($mysql_link)
{
	$sql="SELECT conf_valeur FROM conges_config WHERE conf_nom = 'rtt_comme_conges' ";
	$ReqLog = mysql_query($sql, $mysql_link) or die("ERREUR : is_rtt_comme_conges : <br>\n".$sql." --> ".mysql_error());
	if ($resultat = mysql_fetch_array($ReqLog))
		if($resultat['conf_valeur']=="TRUE")
			return TRUE;
	
	return FALSE;
}


?>
