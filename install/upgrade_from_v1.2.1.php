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

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.2.1 vers 1.3.0
/*******************************************************************/
include("../fonctions_conges.php") ;
include("../INCLUDE.PHP/fonction.php");
include("fonctions_install.php") ;
	
$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=FALSE;
//$DEBUG=TRUE;

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;

	// résumé des étapes :
	// 1 : mise à jour de la table conges_type_absence
	// 2 : Ajout de paramètres dans  conges_config
	// 3 : creation de la table conges_historique_ajout
	// 4 : Creation de la table conges_logs
	
	include("../dbconnect.php") ;
	
	if($DEBUG==FALSE)
	{
		$mysql_link = mysql_connexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database);
		// on lance les etape (fonctions) séquentiellement 
		e1_maj_table_conges_type_absence($mysql_link, $DEBUG);
		e2_insert_into_conges_config($mysql_link, $DEBUG);
		e3_create_table_conges_historique_ajout($mysql_link, $DEBUG);
		e4_create_table_conges_logs($mysql_link, $DEBUG);
		
		mysql_close($mysql_link);
		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=mise_a_jour.php?etape=4&version=$version&lang=$lang\">";
	}
	else
	{
		$mysql_link = mysql_connexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database);
		
		// on lance les etape (fonctions) séquentiellement :
		// avec un arret à la fin de chaque étape  
		
		$sub_etape=( (isset($_GET['sub_etape'])) ? $_GET['sub_etape'] : ( (isset($_POST['sub_etape'])) ? $_POST['sub_etape'] : 0 ) ) ;

		if($sub_etape==0) { echo "<a href=\"$PHP_SELF?sub_etape=1&version=$version&lang=$lang\">start upgrade_from_v1.2.1</a><br>\n"; }		
		if($sub_etape==1) { e1_maj_table_conges_type_absence($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=2&version=$version&lang=$lang\">sub_etape 1  OK</a><br>\n"; }
		if($sub_etape==2) { e2_insert_into_conges_config($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=3&version=$version&lang=$lang\">sub_etape 2  OK</a><br>\n"; }
		if($sub_etape==3) { e3_create_table_conges_historique_ajout($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=4&version=$version&lang=$lang\">sub_etape 3  OK</a><br>\n"; }
		if($sub_etape==4) { e4_create_table_conges_logs($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=5&version=$version&lang=$lang\">sub_etape 4  OK</a><br>\n"; }
		
		mysql_close($mysql_link);
		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		if($sub_etape==5) { echo "<a href=\"mise_a_jour.php?etape=4&version=$version&lang=$lang\">upgrade_from_v1.2.1  OK</a><br>\n"; }
	}


/********************************************************************************************************/
/********************************************************************************************************/
/***   FONCTIONS   ***/
/********************************************************************************************************/



/*****************************************************************/
/***   ETAPE 1 : mise à jour de la table conges_type_absence   ***/
/*****************************************************************/
function e1_maj_table_conges_type_absence($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	
	# on permet l'ajout d'un type "absences"
	$sql_alter_1=" ALTER TABLE `conges_type_absence` CHANGE `ta_type` `ta_type` enum ('conges', 'conges_exceptionnels', 'absence', 'absences') ";
	if($DEBUG==FALSE)
		$result_alter_1 = mysql_query($sql_alter_1, $mysql_link);
	else
		$result_update = mysql_query($sql_alter_1, $mysql_link) or die("erreur : e1_maj_table_conges_type_absence<br>\n".mysql_error($mysql_link)) ;

	# on modifie le type "absence" en "absences"
	$sql_update=" UPDATE `conges_type_absence` SET `ta_type` = 'absences' where `ta_type` = 'absence'";
	if($DEBUG==FALSE)
		$result_update = mysql_query($sql_update, $mysql_link);
	else
		$result_update = mysql_query($sql_update, $mysql_link) or die("erreur : e1_maj_table_conges_type_absence<br>\n".mysql_error($mysql_link)) ;

	# on supprime la possibilité d'avoir un type "absence"
	$sql_alter_2=" ALTER TABLE `conges_type_absence` CHANGE `ta_type` `ta_type` enum ('conges', 'conges_exceptionnels', 'absences')";
	if($DEBUG==FALSE)
		$result_alter_2 = mysql_query($sql_alter_2, $mysql_link);
	else
		$result_alter_2 = mysql_query($sql_alter_2, $mysql_link) or die("erreur : e1_maj_table_conges_type_absence<br>\n".mysql_error($mysql_link)) ;		

}


/*************************************************************/
/***   ETAPE 2 : Ajout de paramètres dans  conges_config   ***/
/*************************************************************/
function e2_insert_into_conges_config($mysql_link, $DEBUG=FALSE)
{

	$sql_insert="INSERT INTO `conges_config` VALUES ('gestion_conges_exceptionnels', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_gestion_conges_exceptionnels')";
	if($DEBUG==FALSE)
		$result_insert = mysql_query($sql_insert, $mysql_link);
	else
		$result_insert = mysql_query($sql_insert, $mysql_link) or die("erreur : e2_insert_into_conges_config<br>\n".mysql_error($mysql_link)) ;
	
	$sql_insert_2="INSERT INTO `conges_config` VALUES ('grand_resp_ajout_conges', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_grand_resp_ajout_conges')";
	if($DEBUG==FALSE)
		$result_insert_2 = mysql_query($sql_insert_2, $mysql_link);
	else
		$result_insert_2 = mysql_query($sql_insert_2, $mysql_link) or die("erreur : e2_insert_into_conges_config<br>\n".mysql_error($mysql_link)) ;
	
	$sql_insert_3="INSERT INTO `conges_config` VALUES ('interdit_saisie_periode_date_passee', 'FALSE', '13_Divers', 'boolean', 'config_comment_interdit_saisie_periode_date_passee')";
	if($DEBUG==FALSE)
		$result_insert_3 = mysql_query($sql_insert_3, $mysql_link);
	else
		$result_insert_3 = mysql_query($sql_insert_3, $mysql_link) or die("erreur : e2_insert_into_conges_config<br>\n".mysql_error($mysql_link)) ;
	
}


/******************************************************************/
/***   ETAPE 3 : Creation de la table conges_historique_ajout   ***/
/******************************************************************/
function e3_create_table_conges_historique_ajout($mysql_link, $DEBUG=FALSE)
{

	$sql_create="CREATE TABLE `conges_historique_ajout`(
					`ha_login` VARCHAR(16) NOT NULL ,
					`ha_date` DATETIME NOT NULL ,
					`ha_abs_id` INT(2) NOT NULL ,
					`ha_nb_jours` int(4) NOT NULL,
					`ha_commentaire` VARCHAR(200) NOT NULL,
					 PRIMARY KEY (`ha_login`, `ha_date`, `ha_abs_id` )
					) TYPE=MyISAM DEFAULT CHARSET=latin1 ";
	if($DEBUG==FALSE)
		$result_create = mysql_query($sql_create, $mysql_link);
	else
		$result_create = mysql_query($sql_create, $mysql_link) or die("erreur : e3_create_table_conges_historique_ajout<br>\n".mysql_error($mysql_link)) ;
	
}


/******************************************************************/
/***   ETAPE 4 : Creation de la table conges_logs   ***/
/******************************************************************/
function e4_create_table_conges_logs($mysql_link, $DEBUG=FALSE)
{

	$sql_create_logs="CREATE TABLE `conges_logs` (
				   `log_id` integer not null auto_increment, 
				   `log_p_num` int(5) unsigned NOT NULL, 
				   `log_user_login_par` varchar(16) binary NOT NULL default '', 
				   `log_user_login_pour` varchar(16) binary NOT NULL default '', 
				   `log_etat` varchar(16) NOT NULL default '', 
				   `log_comment` TEXT NULL, 
				   `log_date` TIMESTAMP NOT NULL, 
				   PRIMARY KEY  (`log_id`)
					) TYPE=MyISAM DEFAULT CHARSET=latin1 ";
	if($DEBUG==FALSE)
		$result_create_logs = mysql_query($sql_create_logs, $mysql_link);
	else
		$result_create_logs = mysql_query($sql_create_logs, $mysql_link) or die("erreur : e4_create_table_conges_logs<br>\n".mysql_error($mysql_link)) ;
	
}
					





?>
