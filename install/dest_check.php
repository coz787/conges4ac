<?php
/*************************************************************************************************
PHP_CONGES : Gestion Interactive des CongÃ©s
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique GÃ©nÃ©rale GNU publiÃ©e par la Free Software Foundation.
Ce programme est distribuÃ© car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spÃ©cifique. Reportez-vous Ã  la Licence Publique GÃ©nÃ©rale GNU pour plus de dÃ©tails.
Vous devez avoir reÃ§u une copie de la Licence Publique GÃ©nÃ©rale GNU en mÃªme temps
que ce programme ; si ce n'est pas le cas, Ã©crivez Ã  la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, Ãtats-Unis.
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

// dest_import1.php : script d'assistance a la fusion de base conges 
// developpe par dgac/cedre sous meme licence
// partie du processus qui importe une base "augmentÃ©e" vers une base destination 

// appel de PHP-IDS que si version de php > 5.1.2
if(phpversion() > "5.1.2") { include("../controle_ids.php") ;}

//include("../fonctions_conges.php") ;
//include("../INCLUDE.PHP/fonction.php");
//include("../fonctions_javascript.php") ;

include("actk.php"); 
include("fonctions_install.php") ;
include("../fonctions_conges.php") ;
include("../version.php"); 
// $version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$PHP_SELF=$_SERVER['PHP_SELF'];

//$DEBUG=TRUE;
$DEBUG=FALSE;
session_start();
$session=session_id();
$goon = 1 ; // indique si on peut poursuivre les tests 

// $currentcmd = (isset($_GET['cmd']) ?  $_GET['cmd'] : "" ) ; 
// $noffset = (isset($_POST['noffset']) ?  $_POST['noffset'] : "" ) ;

$currentcmd =  getpost_variable("cmd","") ;
// $soffset = getpost_variable("soffset","") ; 
// $offsetmin = 0 ; 

affiche_entete("dest_check.php");
echo "<body>\n";
echo "<fieldset class=\"roundcorner\"> $PHP_SELF : <br>
assistance a la fusion de base de donnees conges : check base dest <br> 
parcourt la base et verifie que les conges_solde_user sont en accord avec <br>
les conges_periodes " ; 
if ($DEBUG) {
  echo "currentcmd is ". $currentcmd . "<br>" ; 
  echo "config_php_conges_version is ". $config_php_conges_version . "<br>" ;
  //   echo "soffset is ". $soffset . "<br>" ;
};

echo "</fieldset> \n";
// echo "<p>$PHP_SELF</p>\n"; 
echo "<br>\n" ; 

// commande courante = 
echo "<table border=1 width=\"90%\" >\n" ;
echo "<tr><td width=\"65%\" align=center >&nbsp;&nbsp;&nbsp;info/action database dest &nbsp;&nbsp;&nbsp;</td><td align=center>status</td></tr>\n" ;  

echo "<tr>\n<td>test_dbconnect_file base dest</td> " ; 
if ($goon) {
  if (test_dbconnect_file($DEBUG)==TRUE) {
    echo "<td align=center>ok</td>\n" ; 
  } else {
    $goon = 0 ; 
    echo "<td align=center>nok</td>\n";
  } 
} else  { 
    echo "<td align=center>non passe</td>\n" ; 
}
echo "</tr>\n" ; 

echo "<tr>\n<td>test_database</td> " ; 
if ($goon) {
  if (test_database($DEBUG)==TRUE) {
    echo "<td align=center>ok</td>\n" ; 
  } else {
    $goon = 0 ; 
    echo "<td align=center>nok</td>\n";
  }
} else  { 
    echo "<td align=center>non passe</td>\n" ; 
}
echo "</tr>\n" ; 
echo "</table>\n" ; 
// on reouvre ici les deux acces database CAR test_dbconnect_file, test_database ecrasent
// le lien 
include("../dbconnect.php") ;
$mysqldest_link = mysql_newconnexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database);
// on a alors 1 acces database ouvert $mysqldest_link sur la base "dest"

$req_dest_user = mysqli_query($mysqldest_link,"select u_login,u_nom, u_prenom from conges_users") ;
$l_dest_user = array(); 
while ($res1 = mysqli_fetch_row($req_dest_user)) {
  //array_push($l_dest_user, $res1[0]) ; 
  $l_dest_user[$res1[0]] = $res1 ; // permet de recuperer login, nom, prenom sur cle login
}

$req_dest_solde_user =  mysqli_query($mysqldest_link,
     "select conges_type_absence.ta_id, conges_type_absence.ta_short_libelle, conges_solde_user.su_login, conges_solde_user.su_solde \
from conges_solde_user, conges_type_absence where \
conges_solde_user.su_abs_id = conges_type_absence.ta_id" ) ;
$cur_login = "" ; 
// while ($res1 = mysqli_fetch_row($req_dest_solde_user)) {
  
//  }


echo "</body>\n"; 
echo "</html>\n";   


// affiche les entetes html ...
function affiche_entete($title)
{
	echo "<html>\n";
	echo "<head>\n";
    //	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
 	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
   echo "<title>".$title."</title>\n" ; 
//	echo "<link href=\"../".$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
    echo "<style media=\"conge\" type=\"text/css\">\n";
    echo ".roundcorner { 
border: solid 1px #3CF; 
-webkit-border-radius: 8px; 
-moz-border-radius: 8px; 
border-radius: 8px; 
} \n"; 
    echo "</style>\n" ; 
	echo "</head>\n";
    //     echo "<link href=\"4conge.css\" rel=\"stylesheet\" type=\"text/css\">";

}

function mysql_newconnexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database,$mysql_charset=""))
// retourne un nouveau lien database ou erreur mysql sous forme de texte ; 
{
  //   echo "mysql_newconnexion" . $mysql_serveur. $mysql_user. $mysql_pass. $mysql_database."<br>" ; 
  $mysql_nlink = mysqli_connect($mysql_serveur, $mysql_user, $mysql_pass, True) ;
  if (!$mysql_nlink) 
    return "erreur : mysql_newconnexion connect<br>\n".mysqli_error() ;
  // _ac3 
  if ( ! mysqli_set_charset($mysql_nlink,$mysql_charset) ) {
    die("mysql_set_charset() : set_charset ".$mysql_charset.
        " en erreur ". mysqli_error($mysql_nlink));
  };
  $dbselect   = mysqli_select_db($mysql_nlink, $mysql_database) ;
  if (!$dbselect) 
    return "erreur : mysql_newconnexion select_db <br>\n".mysqli_error($mysql_nlink);

  return $mysql_nlink;
}

// function my_implode   importe de actk.php

?>
