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
// SCRIPT DE MIGRATION DE LA VERSION 1.0 vers 1.1
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
	// 1 : Mise à jour de la table conges_users
	// 2 : Creation de la table conges_config 
	// 3 : Initialisation de la table `conges_config`
	// 4 : Mise a jour de la table `conges_config` avec les param de l'ancien fichier de config
	// 5 : Création de la table `conges_type_absence`
	// 6 : Insertion du contenu de la table `conges_type_absence`
	// 7 : Mise à jour de la table `conges_periode`
	// 8 : Création de la table `conges_solde_user`
	// 9 : Migration de données de la table conges_users vers `conges_solde_user`
	// 10 : Mise à jour de la table `conges_users`
	// 11 : Création de la table `conges_solde_edition`
	// 12 : Migration de données de la table conges_edition_papier vers `conges_solde_edition`
	// 13 : Mise à jour de la table `conges_edition_papier`
	
	include("../config.php") ;
	
	if($DEBUG==FALSE)
	{
		$mysql_link = mysql_connexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database);
		// on lance les etape (fonctions) séquentiellement 
		e1_maj_1_table_conges_users($mysql_link, $DEBUG);
		e2_create_table_conges_config($mysql_link, $DEBUG);
		e3_insert_into_conges_config($mysql_link, $DEBUG);
		e4_maj_table_conges_config($mysql_link, $DEBUG);
		e5_create_table_conges_type_absence($mysql_link, $DEBUG);
		e6_insert_into_conges_type_absence($mysql_link, $DEBUG);
		e7_maj_table_conges_periode($mysql_link, $DEBUG);
		e8_create_table_conges_solde_user($mysql_link, $DEBUG);
		e9_insert_into_conges_solde_user($mysql_link, $DEBUG);
		e10_maj_2_table_conges_users($mysql_link, $DEBUG);
		e11_create_table_conges_solde_edition($mysql_link, $DEBUG);
		e12_insert_into_conges_solde_edition($mysql_link, $DEBUG);
		e13_maj_table_conges_edition_papier($mysql_link, $DEBUG);
		
		mysql_close($mysql_link);
		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=mise_a_jour.php?etape=4&version=$version&lang=$lang\">";
	}
	else
	{
		$mysql_link = mysql_connexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database);

		// on lance les etape (fonctions) séquentiellement :
		// avec un arret à la fin de chaque étape  
		
		$sub_etape=( (isset($_GET['sub_etape'])) ? $_GET['sub_etape'] : ( (isset($_POST['sub_etape'])) ? $_POST['sub_etape'] : 1 ) ) ;

		if($sub_etape==1) { e1_maj_1_table_conges_users($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=2&lang=$lang\">sub_etape 1  OK</a><br>\n"; }
		if($sub_etape==2) { e2_create_table_conges_config($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=3&lang=$lang\">sub_etape 2  OK</a><br>\n"; }
		if($sub_etape==3) { e3_insert_into_conges_config($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=4&lang=$lang\">sub_etape 3  OK</a><br>\n"; }
		if($sub_etape==4) { e4_maj_table_conges_config($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=5&lang=$lang\">sub_etape 4  OK</a><br>\n"; }
		if($sub_etape==5) { e5_create_table_conges_type_absence($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=6&lang=$lang\">sub_etape 5  OK</a><br>\n"; }
		if($sub_etape==6) { e6_insert_into_conges_type_absence($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=7&lang=$lang\">sub_etape 6  OK</a><br>\n"; }
		if($sub_etape==7) { e7_maj_table_conges_periode($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=8&lang=$lang\">sub_etape 7  OK</a><br>\n"; }
		if($sub_etape==8) { e8_create_table_conges_solde_user($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=9&lang=$lang\">sub_etape 8  OK</a><br>\n"; }
		if($sub_etape==9) { e9_insert_into_conges_solde_user($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=10&lang=$lang\">sub_etape 9  OK</a><br>\n"; }
		if($sub_etape==10) { e10_maj_2_table_conges_users($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=11&lang=$lang\">sub_etape 10  OK</a><br>\n"; }
		if($sub_etape==11) { e11_create_table_conges_solde_edition($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=12&lang=$lang\">sub_etape 11  OK</a><br>\n"; }
		if($sub_etape==12) { e12_insert_into_conges_solde_edition($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=13&lang=$lang\">sub_etape 12  OK</a><br>\n"; }
		if($sub_etape==13) { e13_maj_table_conges_edition_papier($mysql_link, $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=14&lang=$lang\">sub_etape 13  OK</a><br>\n"; }

		mysql_close($mysql_link);
		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		if($sub_etape==14) { echo "<a href=\"mise_a_jour.php?etape=4&version=$version\">upgrade_from_v1.0  OK</a><br>\n"; }
	}


/********************************************************************************************************/
/********************************************************************************************************/
/***   FONCTIONS   ***/
/********************************************************************************************************/



/**********************************************************/
/***   ETAPE 1 : mise à jour de la table conges_users   ***/
/**********************************************************/
// update de la table conges_users
function e1_maj_1_table_conges_users($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	
	// verif si champ u_see_all existe déja
	$result = mysql_query("SHOW COLUMNS FROM conges_users", $mysql_link);
	while ($row = mysql_fetch_assoc($result)) 
	{
		//print_r($row);
		if($row['Field']=="u_see_all")
			return ; // on sort de la fonction
	}

	// ajout du champ dans le table
	$sql_alter="ALTER TABLE `conges_users` ADD `u_see_all` ENUM( 'Y', 'N' ) DEFAULT 'N' NOT NULL AFTER `u_is_admin` " ;
	if($DEBUG==FALSE)
		$result_alter = mysql_query($sql_alter, $mysql_link);
	else
		$result_alter = mysql_query($sql_alter, $mysql_link) or die("erreur : e1_maj_1_table_conges_users<br>\n".mysql_error()) ;

	// mise à jour des users
	$sql_update=" UPDATE `conges_users` SET `u_see_all` = 'Y' WHERE `u_login` = 'conges' " ;
	if($DEBUG==FALSE)
		$result_update = mysql_query($sql_update, $mysql_link);
	else
		$result_update = mysql_query($sql_update, $mysql_link) or die("erreur : e1_maj_1_table_conges_users<br>\n".mysql_error()) ;
		

}


/***********************************************************/
/***   ETAPE 2 :  Création de la table `conges_config`   ***/
/***********************************************************/
function e2_create_table_conges_config($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	/*********************************************/
	// creation de la table `conges_config`
	$sql_create="CREATE TABLE `conges_config` (
				`conf_nom` varchar(100) BINARY NOT NULL default '',
  				`conf_valeur` varchar(200) BINARY NOT NULL default '',
  				`conf_groupe` VARCHAR(200) NOT NULL default '',
  				`conf_type` VARCHAR(200) NOT NULL default 'texte' ,
  				`conf_commentaire` text NOT NULL default '',
 				 PRIMARY KEY  (`conf_nom`)
				) TYPE=MyISAM;" ;
	if($DEBUG==FALSE)
		$result_create = mysql_query($sql_create, $mysql_link);
	else
		$result_create = mysql_query($sql_create, $mysql_link) or die("erreur : e2_create_table_conges_config<br>\n".mysql_error()) ;
}
		

/*****************************************************************/
/***   ETAPE 3 :  initialisation de la table `conges_config`   ***/
/*****************************************************************/
// initialisation de la table `conges_config`
function e3_insert_into_conges_config($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
		
	// verif si le fichier "data_conges_config_v1.1.sql" (qui contient les requetes INSERT)existe et est lisible ....
	// la verif a du etre faite en debut de la procedure d'install ...
	$result = execute_sql_file("sql/data_conges_config_v1.1.sql", $mysql_link, $DEBUG);
	
	if($DEBUG==TRUE)
		if($result==FALSE)
		{
			echo "erreur : e3_insert_into_conges_config<br>execute_sql_file(\"sql/data_conges_config_v1.1.sql\")...\n";
			exit;
		} 
}


/**********************************************************************************************************/
/***   ETAPE 4 : Mise a jour de la table `conges_config` avec les param de l'ancien fichier de config   ***/
/**********************************************************************************************************/
function e4_maj_table_conges_config($mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

		/*********************************************/
		// Mise a jour de la table `conges_config` avec les param de l'ancien fichier de config
		
		// verif si le fichier "config_old.php" existe et est lisible ....
		// la verif a du etre faite en debut de la procedure d'install ...
		// on lit l'ancien fichier de config
		include("config_old.php") ;
				
		// on update la table conges_config 
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$URL_ACCUEIL_CONGES' WHERE `conf_nom` = 'URL_ACCUEIL_CONGES' ;";
		if($DEBUG==FALSE)
			$result = mysql_query($sql, $mysql_link);
		else
			$result = mysql_query($sql, $mysql_link) or die("erreur : e4_maj_table_conges_config<br>\n".mysql_error()) ;
		// si debug on ne teste pas tous les result mais seulement le premier ....
		
		
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_img_login' WHERE `conf_nom` = 'img_login' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_texte_img_login' WHERE `conf_nom` = 'texte_img_login' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_lien_img_login' WHERE `conf_nom` = 'lien_img_login' ;";
		$result = mysql_query($sql, $mysql_link);
		
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_titre_calendrier' WHERE `conf_nom` = 'titre_calendrier' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_titre_user_index' WHERE `conf_nom` = 'titre_user_index' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_titre_resp_index' WHERE `conf_nom` = 'titre_resp_index' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_titre_admin_index' WHERE `conf_nom` = 'titre_admin_index' ;";
		$result = mysql_query($sql, $mysql_link);
		
		if($config_auth==TRUE) {$config_auth="TRUE";} else {$config_auth="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_auth' WHERE `conf_nom` = 'auth' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_how_to_connect_user' WHERE `conf_nom` = 'how_to_connect_user' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_export_users_from_ldap==TRUE) {$config_export_users_from_ldap="TRUE";} else {$config_export_users_from_ldap="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_export_users_from_ldap' WHERE `conf_nom` = 'export_users_from_ldap' ;";
		$result = mysql_query($sql, $mysql_link);
		
		if($config_user_saisie_demande==TRUE) {$config_user_saisie_demande="TRUE";} else {$config_user_saisie_demande="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_user_saisie_demande' WHERE `conf_nom` = 'user_saisie_demande' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_user_affiche_calendrier==TRUE) {$config_user_affiche_calendrier="TRUE";} else {$config_user_affiche_calendrier="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_user_affiche_calendrier' WHERE `conf_nom` = 'user_affiche_calendrier' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_user_saisie_mission==TRUE) {$config_user_saisie_mission="TRUE";} else {$config_user_saisie_mission="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_user_saisie_mission' WHERE `conf_nom` = 'user_saisie_mission' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_user_ch_passwd==TRUE) {$config_user_ch_passwd="TRUE";} else {$config_user_ch_passwd="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_user_ch_passwd' WHERE `conf_nom` = 'user_ch_passwd' ;";
		$result = mysql_query($sql, $mysql_link);
		
		if($config_responsable_virtuel==TRUE) {$config_responsable_virtuel="TRUE";} else {$config_responsable_virtuel="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_responsable_virtuel' WHERE `conf_nom` = 'responsable_virtuel' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_resp_affiche_calendrier==TRUE) {$config_resp_affiche_calendrier="TRUE";} else {$config_resp_affiche_calendrier="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_resp_affiche_calendrier' WHERE `conf_nom` = 'resp_affiche_calendrier' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_resp_saisie_mission==TRUE) {$config_resp_saisie_mission="TRUE";} else {$config_resp_saisie_mission="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_resp_saisie_mission' WHERE `conf_nom` = 'resp_saisie_mission' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_resp_vertical_menu==TRUE) {$config_resp_vertical_menu="TRUE";} else {$config_resp_vertical_menu="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_resp_vertical_menu' WHERE `conf_nom` = 'resp_vertical_menu' ;";
		$result = mysql_query($sql, $mysql_link);
		
		if($config_admin_see_all==TRUE) {$config_admin_see_all="TRUE";} else {$config_admin_see_all="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_admin_see_all' WHERE `conf_nom` = 'admin_see_all' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_admin_change_passwd==TRUE) {$config_admin_change_passwd="TRUE";} else {$config_admin_change_passwd="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_admin_change_passwd' WHERE `conf_nom` = 'admin_change_passwd' ;";
		$result = mysql_query($sql, $mysql_link);
		
		if($config_mail_new_demande_alerte_resp==TRUE) {$config_mail_new_demande_alerte_resp="TRUE";} else {$config_mail_new_demande_alerte_resp="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_mail_new_demande_alerte_resp' WHERE `conf_nom` = 'mail_new_demande_alerte_resp' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_mail_valid_conges_alerte_user==TRUE) {$config_mail_valid_conges_alerte_user="TRUE";} else {$config_mail_valid_conges_alerte_user="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_mail_valid_conges_alerte_user' WHERE `conf_nom` = 'mail_valid_conges_alerte_user' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_mail_refus_conges_alerte_user==TRUE) {$config_mail_refus_conges_alerte_user="TRUE";} else {$config_mail_refus_conges_alerte_user="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_mail_refus_conges_alerte_user' WHERE `conf_nom` = 'mail_refus_conges_alerte_user' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_mail_annul_conges_alerte_user==TRUE) {$config_mail_annul_conges_alerte_user="TRUE";} else {$config_mail_annul_conges_alerte_user="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_mail_annul_conges_alerte_user' WHERE `conf_nom` = 'mail_annul_conges_alerte_user' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_serveur_smtp' WHERE `conf_nom` = 'serveur_smtp' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_where_to_find_user_email' WHERE `conf_nom` = 'where_to_find_user_email' ;";
		$result = mysql_query($sql, $mysql_link);
		
		if($config_samedi_travail==TRUE) {$config_samedi_travail="TRUE";} else {$config_samedi_travail="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_samedi_travail' WHERE `conf_nom` = 'samedi_travail' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_dimanche_travail==TRUE) {$config_dimanche_travail="TRUE";} else {$config_dimanche_travail="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_dimanche_travail' WHERE `conf_nom` = 'dimanche_travail' ;";
		$result = mysql_query($sql, $mysql_link);
		
		if($config_gestion_groupes==TRUE) {$config_gestion_groupes="TRUE";} else {$config_gestion_groupes="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_gestion_groupes' WHERE `conf_nom` = 'gestion_groupes' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_affiche_groupe_in_calendrier==TRUE) {$config_affiche_groupe_in_calendrier="TRUE";} else {$config_affiche_groupe_in_calendrier="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_affiche_groupe_in_calendrier' WHERE `conf_nom` = 'affiche_groupe_in_calendrier' ;";
		$result = mysql_query($sql, $mysql_link);
		
		if($config_editions_papier==TRUE) {$config_editions_papier="TRUE";} else {$config_editions_papier="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_editions_papier' WHERE `conf_nom` = 'editions_papier' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_texte_haut_edition_papier' WHERE `conf_nom` = 'texte_haut_edition_papier' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_texte_bas_edition_papier' WHERE `conf_nom` = 'texte_bas_edition_papier' ;";
		$result = mysql_query($sql, $mysql_link);
		
		//ATTENTION 
		// cette info ne sert plus dans php conges, mais set dans la suite de la migration : on la stoque donc ici !
		if($config_rtt_comme_conges==TRUE) {$config_rtt_comme_conges="TRUE";} else {$config_rtt_comme_conges="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_rtt_comme_conges' WHERE `conf_nom` = 'rtt_comme_conges' ;";
		$result = mysql_query($sql, $mysql_link);

		if($config_user_echange_rtt==TRUE) {$config_user_echange_rtt="TRUE";} else {$config_user_echange_rtt="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_user_echange_rtt' WHERE `conf_nom` = 'user_echange_rtt' ;";
		$result = mysql_query($sql, $mysql_link);

		if($config_affiche_bouton_calcul_nb_jours_pris==TRUE) {$config_affiche_bouton_calcul_nb_jours_pris="TRUE";} else {$config_affiche_bouton_calcul_nb_jours_pris="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_affiche_bouton_calcul_nb_jours_pris' WHERE `conf_nom` = 'affiche_bouton_calcul_nb_jours_pris' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_rempli_auto_champ_nb_jours_pris==TRUE) {$config_rempli_auto_champ_nb_jours_pris="TRUE";} else {$config_rempli_auto_champ_nb_jours_pris="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_rempli_auto_champ_nb_jours_pris' WHERE `conf_nom` = 'rempli_auto_champ_nb_jours_pris' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$duree_session' WHERE `conf_nom` = 'duree_session' ;";
		$result = mysql_query($sql, $mysql_link);
		if($config_verif_droits==TRUE) {$config_verif_droits="TRUE";} else {$config_verif_droits="FALSE";} ;
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_verif_droits' WHERE `conf_nom` = 'verif_droits' ;";
		$result = mysql_query($sql, $mysql_link);
		
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_stylesheet_file' WHERE `conf_nom` = 'stylesheet_file' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_bgcolor' WHERE `conf_nom` = 'bgcolor' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_bgimage' WHERE `conf_nom` = 'bgimage' ;";
		$result = mysql_query($sql, $mysql_link);
		$sql = "UPDATE `conges_config` SET `conf_valeur` = '$config_light_grey_bgcolor' WHERE `conf_nom` = 'light_grey_bgcolor' ;";
		$result = mysql_query($sql, $mysql_link);
		
}


/******************************************************************/
/***   ETAPE 5 : Création de la table `conges_type_absence`     ***/
/******************************************************************/
function e5_create_table_conges_type_absence($mysql_link, $DEBUG=FALSE)
{
	// creation de la table `conges_type_absence`
	$sql_create="CREATE TABLE `conges_type_absence` (
				  `ta_id` int(2) unsigned NOT NULL AUTO_INCREMENT,
				  `ta_type` enum('conges','absence') NOT NULL default 'conges',
				  `ta_libelle` varchar(20) NOT NULL default '',
				  `ta_short_libelle` char(3) NOT NULL default '',
				  PRIMARY KEY  (`ta_id`)
				) TYPE=MyISAM;" ;
	$result_create = mysql_query($sql_create, $mysql_link);
}

/****************************************************************************/
/***   ETAPE 6 : Insertion du contenu de la table `conges_type_absence`   ***/
/****************************************************************************/
function e6_insert_into_conges_type_absence($mysql_link, $DEBUG=FALSE)
{
	// ajout des types d'absence de base dans la table `conges_type_absence`
	$sql_insert="INSERT INTO `conges_type_absence` VALUES (1, 'conges', 'congés payés', 'cp');" ;
	if($DEBUG==FALSE)
		$result_insert = mysql_query($sql_insert, $mysql_link);
	else
		$result_insert = mysql_query($sql_insert, $mysql_link) or die("erreur : e6_insert_into_conges_type_absence<br>\n".mysql_error()) ;

	if(is_rtt_comme_conges($mysql_link)==TRUE)
	{
		$sql_insert="INSERT INTO `conges_type_absence` VALUES (2, 'conges', 'rtt', 'rtt');";
		if($DEBUG==FALSE)
			$result_insert = mysql_query($sql_insert, $mysql_link);
		else
			$result_insert = mysql_query($sql_insert, $mysql_link) or die("erreur : e6_insert_into_conges_type_absence<br>\n".mysql_error()) ;
	}

	$sql_insert="INSERT INTO `conges_type_absence` VALUES (3, 'absence', 'formation', 'fo');";
	if($DEBUG==FALSE)
		$result_insert = mysql_query($sql_insert, $mysql_link);
	else
		$result_insert = mysql_query($sql_insert, $mysql_link) or die("erreur : e6_insert_into_conges_type_absence<br>\n".mysql_error()) ;

	$sql_insert="INSERT INTO `conges_type_absence` VALUES (4, 'absence', 'misson', 'mi');" ;
	if($DEBUG==FALSE)
		$result_insert = mysql_query($sql_insert, $mysql_link);
	else
		$result_insert = mysql_query($sql_insert, $mysql_link) or die("erreur : e6_insert_into_conges_type_absence<br>\n".mysql_error()) ;

	$sql_insert="INSERT INTO `conges_type_absence` VALUES (5, 'absence', 'autre', 'ab');";
	if($DEBUG==FALSE)
		$result_insert = mysql_query($sql_insert, $mysql_link);
	else
		$result_insert = mysql_query($sql_insert, $mysql_link) or die("erreur : e6_insert_into_conges_type_absence<br>\n".mysql_error()) ;

}

/******************************************************************/
/***   ETAPE 7 : Mise à jour de la table `conges_periode`       ***/
/******************************************************************/
function e7_maj_table_conges_periode($mysql_link, $DEBUG=FALSE)
{
	// modif de la table conges_periode existante avec les types d'absence !

	$sql_alter1="ALTER TABLE `conges_periode` ADD `new_type` INT( 2 ) UNSIGNED NOT NULL " ;
	if($DEBUG==FALSE)
		$result_alter1 = mysql_query($sql_alter1, $mysql_link);
	else
		$result_alter1 = mysql_query($sql_alter1, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

	$sql_update1="UPDATE `conges_periode` SET `new_type` = '1' WHERE `p_type` = 'conges' " ;
	if($DEBUG==FALSE)
		$result_update1 = mysql_query($sql_update1, $mysql_link);
	else
		$result_update1 = mysql_query($sql_update1, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;
	
	$sql_update1="UPDATE `conges_periode` SET `new_type` = '2' WHERE `p_type` = 'rtt' " ;
	if($DEBUG==FALSE)
		$result_update1 = mysql_query($sql_update1, $mysql_link);
	else
		$result_update1 = mysql_query($sql_update1, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;
	
	$sql_update1="UPDATE `conges_periode` SET `new_type` = '3' WHERE `p_type` = 'formation' " ;
	if($DEBUG==FALSE)
		$result_update1 = mysql_query($sql_update1, $mysql_link);
	else
		$result_update1 = mysql_query($sql_update1, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

	$sql_update1="UPDATE `conges_periode` SET `new_type` = '4' WHERE `p_type` = 'mission' " ;
	if($DEBUG==FALSE)
		$result_update1 = mysql_query($sql_update1, $mysql_link);
	else
		$result_update1 = mysql_query($sql_update1, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

	$sql_update1="UPDATE `conges_periode` SET `new_type` = '5' WHERE `p_type` = 'autre' ";
	if($DEBUG==FALSE)
		$result_update1 = mysql_query($sql_update1, $mysql_link);
	else
		$result_update1 = mysql_query($sql_update1, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

	$sql_alter2="ALTER TABLE `conges_periode` CHANGE `p_type` `p_type` INT( 2 ) UNSIGNED DEFAULT '1' NOT NULL  ";
	if($DEBUG==FALSE)
		$result_alter2 = mysql_query($sql_alter2, $mysql_link);
	else
		$result_alter2 = mysql_query($sql_alter2, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

	$sql_update2="UPDATE `conges_periode` SET `p_type` = 1 WHERE `new_type` = 1 " ;
	if($DEBUG==FALSE)
		$result_update2 = mysql_query($sql_update2, $mysql_link);
	else
		$result_update2 = mysql_query($sql_update2, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;
	
	$sql_update2="UPDATE `conges_periode` SET `p_type` = 2 WHERE `new_type` = 2 " ;
	if($DEBUG==FALSE)
		$result_update2 = mysql_query($sql_update2, $mysql_link);
	else
		$result_update2 = mysql_query($sql_update2, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

	$sql_update2="UPDATE `conges_periode` SET `p_type` = 3 WHERE `new_type` = 3 " ;
	if($DEBUG==FALSE)
		$result_update2 = mysql_query($sql_update2, $mysql_link);
	else
		$result_update2 = mysql_query($sql_update2, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

	$sql_update2="UPDATE `conges_periode` SET `p_type` = 4 WHERE `new_type` = 4 " ;
	if($DEBUG==FALSE)
		$result_update2 = mysql_query($sql_update2, $mysql_link);
	else
		$result_update2 = mysql_query($sql_update2, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

	$sql_update2="UPDATE `conges_periode` SET `p_type` = 5 WHERE `new_type` = 5 ";
	if($DEBUG==FALSE)
		$result_update2 = mysql_query($sql_update2, $mysql_link);
	else
		$result_update2 = mysql_query($sql_update2, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

	$sql_alter3="ALTER TABLE `conges_periode` DROP `new_type`  ";
	if($DEBUG==FALSE)
		$result_alter3 = mysql_query($sql_alter3, $mysql_link);
	else
		$result_alter3 = mysql_query($sql_alter3, $mysql_link) or die("erreur : e7_maj_table_conges_periode<br>\n".mysql_error()) ;

}


/****************************************************************/
/***   ETAPE 8 :  Création de la table `conges_solde_user`    ***/
/****************************************************************/
function e8_create_table_conges_solde_user($mysql_link, $DEBUG=FALSE)
{
	// creation de la table `conges_solde_user`
	$sql_create="CREATE TABLE `conges_solde_user` (
				  `su_login` varchar(16) NOT NULL default '',
				  `su_abs_id` int(2) unsigned NOT NULL default '0',
				  `su_nb_an` decimal(4,2) NOT NULL default '0.00',
				  `su_solde` decimal(4,2) NOT NULL default '0.00'
				) TYPE=MyISAM;" ;
	if($DEBUG==FALSE)
		$result_create = mysql_query($sql_create, $mysql_link);
	else
		$result_create = mysql_query($sql_create, $mysql_link) or die("erreur : e8_create_table_conges_solde_user<br>\n".mysql_error()) ;

}	

/*********************************************************************************************/
/***   ETAPE 9 :  Migration de données de la table conges_users vers `conges_solde_user`   ***/
/*********************************************************************************************/
function e9_insert_into_conges_solde_user($mysql_link, $DEBUG=FALSE)
{
	// id des type d'absence
	$id_conges=1 ;   // id du type d'asb dont le libelle est "conges"
	$id_rtt=2 ;      // id du type d'asb dont le libelle est "rtt"
	
	// recup des infos de conges_users
	$sql="SELECT u_login, u_nb_jours_an, u_solde_jours, u_nb_rtt_an, u_solde_rtt FROM conges_users " .
			" WHERE u_login!='admin' AND u_login!='conges' ";
	$ReqLog = mysql_query($sql, $mysql_link) or die("ERREUR : e9_insert_into_conges_solde_user : <br>\n".$sql." --> ".mysql_error());
	while ($resultat = mysql_fetch_array($ReqLog))
	{
		$sql_login=$resultat['u_login'];
		$sql_nb_jours_an=$resultat['u_nb_jours_an'];
		$sql_solde_jours=$resultat['u_solde_jours'];
		$sql_nb_rtt_an=$resultat['u_nb_rtt_an'];
		$sql_solde_rtt=$resultat['u_solde_rtt'];
		
		// pour chaque user : insert du type conges (nb_an et solde) et insert du type rtt (nb_an et solde) (si rtt_comme conges)
		$sql_insert_cong="INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde) VALUES ('$sql_login', $id_conges, $sql_nb_jours_an, $sql_solde_jours) ";
		//echo "$sql_insert_cong<br>\n";
		if($DEBUG==FALSE)
			$result_insert_cong = mysql_query($sql_insert_cong, $mysql_link);
		else
			$result_insert_cong = mysql_query($sql_insert_cong, $mysql_link) or die("erreur : e9_insert_into_conges_solde_user<br>\n".mysql_error()) ;
		
		if(is_rtt_comme_conges($mysql_link)==TRUE)
		{
			$sql_insert_rtt="INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde) VALUES ('$sql_login', $id_rtt, $sql_nb_rtt_an, $sql_solde_rtt) ";
			if($DEBUG==FALSE)
				$result_insert_rtt = mysql_query($sql_insert_rtt, $mysql_link);
			else
				$result_insert_rtt = mysql_query($sql_insert_rtt, $mysql_link) or die("erreur : e9_insert_into_conges_solde_user<br>\n".mysql_error()) ;
		}
	}

}


/*************************************************************/
/***   ETAPE 10 : Mise à jour de la table `conges_users`   ***/
/*************************************************************/
function e10_maj_2_table_conges_users($mysql_link, $DEBUG=FALSE)
{
	// suppr des champs nb_an et solde pour conges et rtt de conges_users 
	$sql_alter1="ALTER TABLE conges_users DROP u_nb_jours_an  ";
	if($DEBUG==FALSE)
		$result_alter1 = mysql_query($sql_alter1, $mysql_link);
	else
		$result_alter1 = mysql_query($sql_alter1, $mysql_link) or die("erreur : e10_maj_2_table_conges_users<br>\n".mysql_error()) ;

	$sql_alter2="ALTER TABLE conges_users DROP u_solde_jours  ";
	if($DEBUG==FALSE)
		$result_alter2 = mysql_query($sql_alter2, $mysql_link);
	else
		$result_alter2 = mysql_query($sql_alter2, $mysql_link) or die("erreur : e10_maj_2_table_conges_users<br>\n".mysql_error()) ;

	$sql_alter3="ALTER TABLE conges_users DROP u_nb_rtt_an  ";
	if($DEBUG==FALSE)
		$result_alter3 = mysql_query($sql_alter3, $mysql_link);
	else
		$result_alter3 = mysql_query($sql_alter3, $mysql_link) or die("erreur : e10_maj_2_table_conges_users<br>\n".mysql_error()) ;

	$sql_alter4="ALTER TABLE conges_users DROP u_solde_rtt  ";
	if($DEBUG==FALSE)
		$result_alter4 = mysql_query($sql_alter4, $mysql_link);
	else
		$result_alter4 = mysql_query($sql_alter4, $mysql_link) or die("erreur : e10_maj_2_table_conges_users<br>\n".mysql_error()) ;

}


/*******************************************************************/
/***   ETAPE 11 : Création de la table `conges_solde_edition`    ***/
/*******************************************************************/
function e11_create_table_conges_solde_edition($mysql_link, $DEBUG=FALSE)
{
	// creation de la table `conges_solde_user`
	$sql_create="CREATE TABLE `conges_solde_edition` (
		`se_id_edition` INT( 11 ) NOT NULL ,
		`se_id_absence` INT( 2 ) NOT NULL ,
		`se_solde` DECIMAL( 4, 2 ) NOT NULL
		) TYPE=MyISAM;" ;
	if($DEBUG==FALSE)
		$result_create = mysql_query($sql_create, $mysql_link);
	else
		$result_create = mysql_query($sql_create, $mysql_link) or die("erreur : e11_create_table_conges_solde_edition<br>\n".mysql_error()) ;

}	


/**********************************************************************************************************/
/***   ETAPE 12 : Migration de données de la table conges_edition_papier vers `conges_solde_edition`    ***/
/**********************************************************************************************************/
function e12_insert_into_conges_solde_edition($mysql_link, $DEBUG=FALSE)
{
 	// id des type d'absence
	$id_conges=1 ;   // id du type d'asb dont le libelle est "conges"
	$id_rtt=2 ;      // id du type d'asb dont le libelle est "rtt"
	
	// recup des infos de conges_edition_papier
	$sql="SELECT  ep_id, ep_solde_jours, ep_solde_rtt FROM conges_edition_papier " ;
	$ReqLog = mysql_query($sql, $mysql_link) or die("ERREUR : e12_insert_into_conges_solde_edition : <br>\n".$sql." --> ".mysql_error());
	while ($resultat = mysql_fetch_array($ReqLog))
	{
		$sql_ep_id=$resultat['ep_id'];
		$sql_ep_solde_jours=$resultat['ep_solde_jours'];
		$sql_ep_solde_rtt=$resultat['ep_solde_rtt'];
		
		// pour chaque edition : insert du solde conges et insert du solde rtt (si rtt_comme conges)
		$sql_insert_cong="INSERT INTO conges_solde_edition (se_id_edition, se_id_absence, se_solde) VALUES ($sql_ep_id, $id_conges, $sql_ep_solde_jours) ";
		//echo "$sql_insert_cong<br>\n";
		if($DEBUG==FALSE)
			$result_insert_cong = mysql_query($sql_insert_cong, $mysql_link);
		else
			$result_insert_cong = mysql_query($sql_insert_cong, $mysql_link) or die("erreur : e12_insert_into_conges_solde_edition<br>\n".mysql_error()) ;

		if(is_rtt_comme_conges($mysql_link)==TRUE)
		{
			$sql_insert_rtt="INSERT INTO conges_solde_edition (se_id_edition, se_id_absence, se_solde) VALUES ($sql_ep_id, $id_rtt, $sql_ep_solde_rtt) ";
			if($DEBUG==FALSE)
				$result_insert_rtt = mysql_query($sql_insert_rtt, $mysql_link);
			else
				$result_insert_rtt = mysql_query($sql_insert_rtt, $mysql_link) or die("erreur : e12_insert_into_conges_solde_edition<br>\n".mysql_error()) ;
		}
	}

}

/***********************************************************************/
/***   ETAPE 13 : Mise à jour de la table `conges_edition_papier`    ***/
/***********************************************************************/
function e13_maj_table_conges_edition_papier($mysql_link, $DEBUG=FALSE)
{
	// suppr des champs solde pour conges et rtt de conges_edition_papier 
	$sql_alter1="ALTER TABLE conges_edition_papier DROP ep_solde_jours  ";
	if($DEBUG==FALSE)
		$result_alter1 = mysql_query($sql_alter1, $mysql_link);
	else
		$result_alter1 = mysql_query($sql_alter1, $mysql_link) or die("erreur : e13_maj_table_conges_edition_papier<br>\n".mysql_error()) ;

	$sql_alter2="ALTER TABLE conges_edition_papier DROP ep_solde_rtt  ";
	if($DEBUG==FALSE)
		$result_alter2 = mysql_query($sql_alter2, $mysql_link);
	else
		$result_alter2 = mysql_query($sql_alter2, $mysql_link) or die("erreur : e13_maj_table_conges_edition_papier<br>\n".mysql_error()) ;

}





?>
