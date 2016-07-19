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
// SCRIPT DE MIGRATION DE LA VERSION 1.3.2 vers 1.4.0
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
	// 1 : mise à jour du champ login dans les tables (respect de la casse)

	include("../dbconnect.php") ;

	if($DEBUG==FALSE)
	{
		$mysql_link = mysql_connexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database);
		// on lance les etapes (fonctions) séquentiellement
		e1_insert_into_conges_config($mysql_link, $DEBUG);
		e2_create_table_jours_fermeture($mysql_link, $DEBUG);
		e3_alter_table_conges_periode($mysql_link, $DEBUG);
		//e4_alter_tables_longueur_login($mysql_link, $DEBUG);
		e5_delete_from_conges_config($mysql_link, $DEBUG);
		e6_insert_into_conges_mail($mysql_link, $DEBUG);

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

		if($sub_etape==0) { echo "<a href=\"$PHP_SELF?sub_etape=1&version=$version&lang=$lang\">start upgrade_from_v1.3.0</a><br>\n"; }
		if($sub_etape==1) { e1_insert_into_conges_config($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=2&version=$version&lang=$lang\">sub_etape 1  OK</a><br>\n"; }
		if($sub_etape==2) { e2_create_table_jours_fermeture($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=3&version=$version&lang=$lang\">sub_etape 2  OK</a><br>\n"; }
		if($sub_etape==3) { e3_alter_table_conges_periode($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=4&version=$version&lang=$lang\">sub_etape 3  OK</a><br>\n"; }
		if($sub_etape==4) { e4_alter_tables_longueur_login($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=5&version=$version&lang=$lang\">sub_etape 4  OK</a><br>\n"; }
		if($sub_etape==5) { e5_delete_from_conges_config($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=6&version=$version&lang=$lang\">sub_etape 5  OK</a><br>\n"; }
		if($sub_etape==6) { e6_insert_into_conges_mail($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=7&version=$version&lang=$lang\">sub_etape 6  OK</a><br>\n"; }

		mysql_close($mysql_link);
		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		if($sub_etape==7) { echo "<a href=\"mise_a_jour.php?etape=4&version=$version&lang=$lang\">upgrade_from_v1.3.2  OK</a><br>\n"; }
	}


/********************************************************************************************************/
/********************************************************************************************************/
/***   FONCTIONS   ***/
/********************************************************************************************************/



/*****************************************************************/
/***   ETAPE 1 : Ajout de paramètres dans  conges_config       ***/
/*****************************************************************/
function e1_insert_into_conges_config($mysql_link, $DEBUG=FALSE)
{

	$sql_insert_1="INSERT INTO `conges_config` VALUES ('fermeture_par_groupe', 'FALSE', '10_Gestion par groupes', 'boolean', 'config_comment_fermeture_par_groupe')";
	$result_insert_1 = mysql_query($sql_insert_1, $mysql_link) or die("erreur : e1_insert_into_conges_config<br>\n".mysql_error($mysql_link)) ;

	$sql_insert_2="INSERT INTO `conges_config` VALUES ('affiche_demandes_dans_calendrier', 'FALSE', '13_Divers', 'boolean', 'config_comment_affiche_demandes_dans_calendrier')";
	$result_insert_2 = mysql_query($sql_insert_2, $mysql_link) or die("erreur : e1_insert_into_conges_config<br>\n".mysql_error($mysql_link)) ;

	$sql_insert_3="INSERT INTO `conges_config` VALUES ('calcul_auto_jours_feries_france', 'FALSE', '13_Divers', 'boolean', 'config_comment_calcul_auto_jours_feries_france')";
	$result_insert_3 = mysql_query($sql_insert_3, $mysql_link) or die("erreur : e1_insert_into_conges_config<br>\n".mysql_error($mysql_link)) ;

	$sql_insert_4="INSERT INTO `conges_config` VALUES ('gestion_cas_absence_responsable', 'FALSE', '06_Responsable', 'boolean', 'config_comment_gestion_cas_absence_responsable')";
	$result_insert_4 = mysql_query($sql_insert_4, $mysql_link) or die("erreur : e1_insert_into_conges_config<br>\n".mysql_error($mysql_link)) ;

}


/******************************************************************/
/***   ETAPE 2 : Creation de la table conges_jours_fermeture   ***/
/******************************************************************/
function e2_create_table_jours_fermeture($mysql_link, $DEBUG=FALSE)
{

	$sql_create="CREATE TABLE `conges_jours_fermeture` (
				`jf_id` INT( 5 ) NOT NULL ,
				`jf_gid` INT( 11 ) NOT NULL DEFAULT '0',
				`jf_date` DATE NOT NULL
				) TYPE=MyISAM DEFAULT CHARSET=latin1 ";
	if($DEBUG==FALSE)
		$result_create = mysql_query($sql_create, $mysql_link);
	else
		$result_create = mysql_query($sql_create, $mysql_link) or die("erreur : e2_create_table_jours_fermeture<br>\n".mysql_error($mysql_link)) ;

}


/******************************************************************/
/***   ETAPE 3 : Modif de la table conges_periode   ***/
/******************************************************************/
function e3_alter_table_conges_periode($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	$sql_alter_1=" ALTER TABLE `conges_periode` ADD `p_fermeture_id` INT ";
	$result_alter_1 = mysql_query($sql_alter_1, $mysql_link) or die("erreur : e3_alter_table_conges_periode<br>\n".mysql_error($mysql_link)) ;

	$sql_alter_2=" ALTER TABLE `conges_periode` CHANGE `p_nb_jours` `p_nb_jours` DECIMAL( 5, 2 ) NOT NULL DEFAULT '0.00' ";
	$result_alter_2 = mysql_query($sql_alter_2, $mysql_link) or die("erreur : e3_alter_table_conges_periode<br>\n".mysql_error($mysql_link)) ;

}


/******************************************************************/
/***   ETAPE 4 : Modif de la table conges_users   ***/
/******************************************************************/
// function e4_alter_tables_longueur_login($mysql_link, $DEBUG=FALSE)/
//{les lignes modifiant la longueur des champs ont été supprimées/
//}/



/***********************************************************************/
/***   ETAPE 5 : Suppression de paramètres dans  conges_config       ***/
/***********************************************************************/
function e5_delete_from_conges_config($mysql_link, $DEBUG=FALSE)
{

	$sql_insert_1="DELETE FROM `conges_config` WHERE `conf_nom` = 'resp_vertical_menu' ";
	$result_insert_1 = mysql_query($sql_insert_1, $mysql_link) or die("erreur : e5_delete_from_conges_config<br>\n".mysql_error($mysql_link)) ;

}


/*****************************************************************/
/***   ETAPE 6 : Ajout d'un type de mail dans conges_mail       ***/
/*****************************************************************/
function e6_insert_into_conges_mail($mysql_link, $DEBUG=FALSE)
{

	$sql_insert_1="INSERT INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_new_demande_resp_absent', 'APPLI CONGES - Demande de congés', ' __SENDER_NAME__ a solicité une demande de congés dans l''application de gestion des congés.\r\n\r\nEn votre absence, cette demande a été transférée à votre (vos) propre(s) responsable(s)./\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.')";
	$result_insert_1 = mysql_query($sql_insert_1, $mysql_link) or die("erreur : e1_insert_into_conges_config<br>\n".mysql_error($mysql_link)) ;

}





?>
