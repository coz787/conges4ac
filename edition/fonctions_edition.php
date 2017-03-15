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

function enregistrement_edition($login, $mysql_link, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	$tab_solde_user=array();
	$sql1 = "SELECT su_abs_id, su_solde FROM conges_solde_user where su_login = '$login' ";
	$ReqLog1 = requete_mysql($sql1, $mysql_link, "enregistrement_edition", $DEBUG);

	while ($resultat1 = mysqli_fetch_array($ReqLog1)) 
	{
		$sql_id=$resultat1["su_abs_id"];
		$tab_solde_user[$sql_id]=$resultat1["su_solde"];
	}
	
	$new_edition_id=get_last_edition_id($mysql_link)+1;
	$aujourdhui = date("Y-m-d");
	$num_for_user=get_num_last_edition_user($login, $mysql_link)+1;

	/*************************************************/
	/* Insertion dans le table conges_edition_papier */
	/*************************************************/
	$sql_insert = "INSERT INTO conges_edition_papier
			SET ep_id=$new_edition_id, ep_login='$login', ep_date='$aujourdhui', ep_num_for_user=$num_for_user ";
	$result_insert = requete_mysql($sql_insert, $mysql_link, "enregistrement_edition", $DEBUG);
	
	
	/*************************************************/
	/* Insertion dans le table conges_solde_edition  */
	/*************************************************/
	// recup du tableau des types de conges (seulement les conges)
	$tab_type_cong=recup_tableau_types_conges($mysql_link, $DEBUG);
	foreach($tab_type_cong as $id_abs => $libelle)
	{
		$sql_insert_2 = "INSERT INTO conges_solde_edition
				SET se_id_edition=$new_edition_id, se_id_absence=$id_abs, se_solde=$tab_solde_user[$id_abs] ";
		$result_insert_2 = requete_mysql($sql_insert_2, $mysql_link, "enregistrement_edition", $DEBUG);
	}
	if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) 
	{
	  $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($mysql_link, $DEBUG);
	} else {
      $tab_type_conges_exceptionnels=array(); 
    }
	foreach($tab_type_conges_exceptionnels as $id_abs => $libelle)
	{
		$sql_insert_3 = "INSERT INTO conges_solde_edition
				SET se_id_edition=$new_edition_id, se_id_absence=$id_abs, se_solde=$tab_solde_user[$id_abs] ";
		$result_insert_3 = requete_mysql($sql_insert_3, $mysql_link, "enregistrement_edition", $DEBUG);
	}	
	
	/********************************************************************************************/
	/* Update du num edition dans la table periode pour les Conges et demandes de cette edition */
	/********************************************************************************************/
	// recup de la liste des id des absence de type conges !
	$sql_list="SELECT ta_id FROM conges_type_absence WHERE ta_type='conges' OR ta_type='conges_exceptionnels'";
	$ReqLog_list = requete_mysql($sql_list, $mysql_link, "enregistrement_edition", $DEBUG);

	$list_abs_id="";
	while($resultat_list = mysqli_fetch_array($ReqLog_list))
	{
		if($list_abs_id=="")
			$list_abs_id=$resultat_list['ta_id'] ;
		else
			$list_abs_id=$list_abs_id.", ".$resultat_list['ta_id'] ;
	}

	$sql_update = "UPDATE conges_periode SET p_edition_id=$new_edition_id 
			WHERE p_login = '$login' 
			AND p_edition_id IS NULL
			AND (p_type IN ($list_abs_id) )
			AND (p_etat!='demande') ";
	$ReqLog_update = requete_mysql($sql_update, $mysql_link, "enregistrement_edition", $DEBUG);
	
	return $new_edition_id;
}


// renvoi le + grand id de la table edition_papier (l'id de la derniere edition)
function get_last_edition_id($mysql_link, $DEBUG=FALSE)
{
	// verif si table edition pas vide
	$sql1 = "SELECT ep_id FROM conges_edition_papier ";
	$ReqLog1 = requete_mysql($sql1, $mysql_link, "get_last_edition_id", $DEBUG);

	if(mysqli_num_rows($ReqLog1)==0) 
		return 0;    // c'est qu'il n'y a pas encore d'edition 
	else
	{
		$sql2 = "SELECT MAX(ep_id) FROM conges_edition_papier ";
		$ReqLog2 = requete_mysql($sql2, $mysql_link, "get_last_edition_id", $DEBUG);
		return mysqli_fetch_array($ReqLog2)[0];
	}	
}

// renvoi le + grand num_par_user de la table edition_papier pour un user donné (le num de la derniere edition du user)
function get_num_last_edition_user($login, $mysql_link, $DEBUG=FALSE)
{
	// verif si le user a une edition
	$sql1 = "SELECT ep_num_for_user FROM conges_edition_papier WHERE ep_login='$login' ";
	$ReqLog1 = requete_mysql($sql1, $mysql_link, "get_num_last_edition_user", $DEBUG);

	if(mysqli_num_rows($ReqLog1)==0) 
		return 0;    // c'est qu'il n'y a pas encore d'edition pour ce user
	else
	{
		$sql2 = "SELECT MAX(ep_num_for_user) FROM conges_edition_papier WHERE ep_login='$login' ";
		$ReqLog2 = requete_mysql($sql2, $mysql_link, "get_num_last_edition_user", $DEBUG);
		return mysqli_fetch_array($ReqLog2)[0];
	}
}


// renvoi le id de la table edition_papier de l'edition précédente pour un user donné et un edition_id donnée.
function get_id_edition_precedente_user($login, $edition_id, $mysql_link, $DEBUG=FALSE)
{
	// verif si le user n'a pas une seule edition
	$sql1 = "SELECT * FROM conges_edition_papier WHERE ep_login='$login' ";
	$ReqLog1 = requete_mysql($sql1, $mysql_link, "get_id_edition_precedente_user", $DEBUG);

	$resultat1 = mysqli_num_rows($ReqLog1) ;
	if($resultat1<=1)    // une seule edition pour ce user
		return 0;
	else
	{
		$sql2 = "SELECT MAX(ep_id) FROM conges_edition_papier WHERE ep_login='$login' AND ep_id<$edition_id ";
		$ReqLog2 = requete_mysql($sql2, $mysql_link, "get_id_edition_precedente_user", $DEBUG);

		return mysqli_fetch_array($ReqLog2)[0];
	}
}



// recup du tab des soldes des conges pour cette edition
function recup_solde_conges_of_edition($edition_id, $mysql_link, $DEBUG=FALSE)
{
	$tab=array();
	$sql_ed = "SELECT se_id_absence, se_solde FROM conges_solde_edition where se_id_edition = $edition_id ";
	$ReqLog_ed = requete_mysql($sql_ed, $mysql_link, "recup_solde_conges_of_edition", $DEBUG);

	$tab=array();
	while ($resultat_ed = mysqli_fetch_array($ReqLog_ed)) 
	{
		$id_absence=$resultat_ed["se_id_absence"];
		$tab[$id_absence]=$resultat_ed["se_solde"];
	}
	return $tab;
}



// recup infos du user
function recup_info_user_pour_edition($login, $mysql_link, $DEBUG=FALSE)
{
	$tab=array();
	$sql_user = "SELECT u_nom, u_prenom, u_quotite FROM conges_users where u_login = '$login' ";
	$ReqLog_user = requete_mysql($sql_user, $mysql_link, "recup_info_user_pour_edition", $DEBUG);

	while ($resultat_user = mysqli_fetch_array($ReqLog_user)) {
		$tab['nom']=$resultat_user["u_nom"];
		$tab['prenom']=$resultat_user["u_prenom"];
		$tab['quotite']=$sql_quotite=$resultat_user["u_quotite"];
	}
	
	// recup dans un tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
	$tab['conges']=recup_tableau_conges_for_user($login, $mysql_link, $DEBUG) ;
	
	return $tab;
}


// recup infos de l'édition
// renvoit un tableau vide si pas de'edition pour le user
function recup_info_edition($edit_id, $mysql_link, $DEBUG=FALSE)
{
	$tab=array();
	
	$sql_edition= "SELECT ep_date, ep_num_for_user FROM conges_edition_papier where ep_id = $edit_id ";
	$ReqLog_edition = requete_mysql($sql_edition, $mysql_link, "recup_info_edition", $DEBUG);

	if($resultat_edition = mysqli_fetch_array($ReqLog_edition)) 
	{
		$tab['date']=$resultat_edition["ep_date"];
		$tab['num_for_user'] = $resultat_edition["ep_num_for_user"];
		// recup du tab des soldes des conges pour cette edition
		$tab['conges']=recup_solde_conges_of_edition($edit_id, $mysql_link, $DEBUG);
	}
	return $tab ;
}	



// Récupération des informations des editions du user
// renvoit un tableau vide si pas de'edition pour le user
function recup_editions_user($login, $mysql_link, $DEBUG=FALSE)
{
	$tab_ed=array();

	$sql2 = "SELECT ep_id, ep_date, ep_num_for_user ";
	$sql2=$sql2."FROM conges_edition_papier WHERE ep_login = '$login' ";
	$sql2=$sql2."ORDER BY ep_num_for_user DESC ";
	$ReqLog2 = requete_mysql($sql2, $mysql_link, "recup_editions_user", $DEBUG);

	if(mysqli_num_rows($ReqLog2) != 0)
	{
		while ($resultat2 = mysqli_fetch_array($ReqLog2)) 
		{
			$tab=array();
			$sql_id = $resultat2["ep_id"];
			$tab['date'] = eng_date_to_fr($resultat2["ep_date"]);
			$tab['num_for_user'] = $resultat2["ep_num_for_user"];
			// recup du tab des soldes des conges pour cette edition
			$tab['conges']=recup_solde_conges_of_edition($sql_id, $mysql_link, $DEBUG);
			
			$tab_ed[$sql_id]=$tab;
		}
	}
	return $tab_ed ;
	
}


?>
