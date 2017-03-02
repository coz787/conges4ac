<?php 
  /***********************************************************************************************
DGAC Toolkit pour php_conges 
Copyright (C) 2012 (dgac)

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

// ensemble de fonctions / modules factorises, utilisÃ©s par des scripts utilitaires 
// divers pour faciliter la maintenance evolutive de php_conges 

$actkmversion = "v1.0a" ;

$func = function($value) {
    return $value * 2;
};
print_r(array_map($func, range(1, 5)));
// will be defined by mysql_newconnexion
$mysql_nlink = NULL ;
// required by use of array_map 
$mres = function($mystr) { 
  global $mysql_nlink ; 
  return mysqli_real_escape_string($mysql_nlink,$mystr); 
}

// affiche les entetes html ...
function affiche_entete($title)
{
	echo "<html>\n";
	echo "<head>\n";
    //	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
 	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
    echo "<title>".$title."</title>\n" ; 
    echo "<link href=\"actk.css\" rel=\"stylesheet\" type=\"text/css\">";
	echo "</head>\n";
    //     echo "<link href=\"4conge.css\" rel=\"stylesheet\" type=\"text/css\">";
}

function mysql_newconnexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database,$mysql_charset="")
// retourne un nouveau lien database ou erreur mysql sous forme de texte ; 
{
  //   echo "mysql_newconnexion" . $mysql_serveur. $mysql_user. $mysql_pass. $mysql_database."<br>" ; 
  global $mysql_nlink ; 
  $mysql_nlink = mysqli_connect($mysql_serveur, $mysql_user, $mysql_pass, True) ;
  if (!$mysql_nlink) 
    return "erreur : mysql_newconnexion connect<br>\n".mysql_error() ;
  // _ac3 
  if ( ! mysqli_set_charset($mysql_nlink,$mysql_charset) ) {
    die("mysql_set_charset() : set_charset ".$mysql_charset.
        " en erreur ". mysqli_error($mysql_nlink));
  };

  
  $dbselect   = mysqli_select_db($mysql_nlink,$mysql_database) ;
  if (!$dbselect) 
    return "erreur : mysql_newconnexion select_db <br>\n".mysqli_error($mysql_nlink);

  return $mysql_nlink;
}

function my_implode($larray) 
// retourne les elements du tableau en format:  'item0', NULL, 'item1',    'itemN'
//                                                       si valeur vide 
// utile pour injection sql 
{
  $sret = "" ; 
  $lescarr =  array_map($mres,$larray) ;
  $nbitem = 0 ; 
  foreach($lescarr as $aitem) {
    $nbitem += 1 ; 
    if ($aitem == '') {
      $sret .= "NULL, " ; 
    } else {
      $sret .= "'".$aitem."', "; 
    };
  }
  if ($nbitem > 0) {
    $sret = rtrim($sret); 
    $sret = rtrim($sret,','); // on eleve un <espace> et <virgule> en fin 
    // rtrim($sret); rtrim($sret); // on eleve un <espace> et <virgule> en fin 
  };
  return $sret ; 
}

?>
