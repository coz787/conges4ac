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

$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
include("../INCLUDE.PHP/session.php");
// _nfj_ include("../fonctions_javascript.php") ;

//include("fonctions_install.php") ;
	
$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=FALSE;
//$DEBUG=TRUE;

$session=session_id();

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);

$_SESSION['from_config']=TRUE;  // initialise ce flag pour changer le bouton de retour des popup
propose_config($DEBUG);



/*****************************************************************************/
/*   FONCTIONS   */


function propose_config( $DEBUG=FALSE)
{
	$session=session_id();
	
	echo "<html>\n<head>\n";
	echo "<TITLE> ".$_SESSION['config']['titre_application']." : Installation : </TITLE>\n</head>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";	
	echo "<link href=\"../style_basic.css\" rel=\"stylesheet\" type=\"text/css\">\n";
    echo "<script src=\"../jqcal.min.js\"></script>\n";

	echo "</head>\n";
				
	echo "<body text=\"#000000\" bgcolor=\"#597c98\" link=\"#000080\" vlink=\"#800080\" alink=\"#FF0000\" >\n";
			
	// affichage du titre
	echo "<center>\n";
	echo "<br><H1><img src=\"../img/tux_config_32x32.png\" width=\"32\" height=\"32\" border=\"0\" title=\"".$_SESSION['lang']['install_install_phpconges']."\" alt=\"".$_SESSION['lang']['install_install_phpconges']."\"> ".$_SESSION['lang']['install_index_titre']."</H1>\n";
	echo "<br><br>\n";
	
		echo "<h2>".$_SESSION['lang']['install_configuration']." :</h2>\n";
		echo "<h3>\n";
		echo "<table border=\"0\">\n";
		echo "<tr><td>-> <a href=\"configure.php?session=$session\">".$_SESSION['lang']['install_config_appli']."</a></td></tr>\n";
		echo "<tr><td>-> <a href=\"config_type_absence.php?session=$session\">".$_SESSION['lang']['install_config_types_abs']."</a></td></tr>\n";
		echo "<tr><td>-> <a href=\"config_mail.php?session=$session\">".$_SESSION['lang']['install_config_mail']."</a></td></tr>\n";
		echo "<tr><td>-> <a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('test_mail.php?session=$session','testmail',800,350);\">".$_SESSION['lang']['install_test_mail']."</a></td></tr>\n";
		echo "<tr><td>-> <a href=\"config_logs.php?session=$session\">".$_SESSION['lang']['config_logs']."</a></td></tr>\n";
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td>-> <a href=\"../\">".$_SESSION['lang']['install_acceder_appli']."</a></td></tr>\n";
		echo "</table>\n";
		echo "</h3><br><br>\n";
		
		bouton_deconnexion($DEBUG);

	echo "</center>\n";
				
	echo "</body>\n</html>\n";
}



?>
