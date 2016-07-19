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

// dest_backup1.php : script d'assistance a la maintenance de base php_conges
// developpe par dgac/cedre sous meme licence
// partie du processus qui fait un backup/export sql zippÃ© vers un fichier 
$mversion = "v1.1c" ; 

// appel de PHP-IDS que si version de php > 5.1.2
if(phpversion() > "5.1.2") { include("../controle_ids.php") ;}

//include("../fonctions_conges.php") ;
//include("../INCLUDE.PHP/fonction.php");
//include("../fonctions_javascript.php") ;

include("actk.php"); 
include("fonctions_install.php") ;
include("../fonctions_conges.php") ;

$PHP_SELF=$_SERVER['PHP_SELF'];

// $DEBUG=TRUE;
$DEBUG=FALSE;
session_start();
$session=session_id();
$goon = 1 ; // indique si on peut poursuivre les tests 

// $currentcmd = (isset($_GET['cmd']) ?  $_GET['cmd'] : "" ) ; 
// $noffset = (isset($_POST['noffset']) ?  $_POST['noffset'] : "" ) ;

$currentcmd =  getpost_variable("cmd","") ;
$valid_cmdbackup = $_SESSION['valid_cmdbackup'] ; 
// $soffset = getpost_variable("soffset","") ; 
// $offsetmin = 0 ; 

affiche_entete("Conges::toolkit de fusion - backup ");
echo "<body>\n";
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">$PHP_SELF</legend> \n"; 
echo "assistance  a la maintenance base conges: backup <br> 
version $mversion <br> " ;
if ($DEBUG) {
  echo "currentcmd is ". $currentcmd . "<br>" ; 
  echo "valid_cmdbackup is ". $valid_cmdbackup . "<br>" ; 
  //   echo "soffset is ". $soffset . "<br>" ;
};

echo "</fieldset> \n";
// echo "<p>$PHP_SELF</p>\n"; 
echo "<br>\n" ; 

// commande courante = 
if ($currentcmd == "do_backup") {
  system( $valid_cmdbackup , $ret ) ; 
  // exec( $valid_cmdbackup , $output , $ret ) ; 
  // exec ("als -l /tmp" , $output , $ret ) ;
  $output= array(); 
  $output_str= "<small>" . join("<br>", $output) ."</small>"; 
  echo "ouput is " . $output . "<br> ";
  echo "ret is " . $ret  . "<br> ";
  
 } else {
  $valid_cmdbackup = "" ;
 }; 

if ($DEBUG) {
  echo "" ; // echo "src_param is [".  join(", ", $_SESSION['src_param'] ) . "] <br>" ;
};
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">actions sur database a sauvegarder </legend> \n"; 

echo "<table border=1>\n" ;
echo "<tr><td width=\"35%\" align=center >info/action</td><td align=center>status</td></tr>\n" ;  

echo "<tr>\n<td>test_dbconnect_file base dest</td> " ; 
if ($goon) {
  if (test_dbconnect_file($DEBUG)==TRUE) {
    echo "<td>ok</td>\n" ; 
  } else {
    $goon = 0 ; 
    echo "<td>nok</td>\n";
  } 
} else  { 
    echo "<td>non passe</td>\n" ; 
}
echo "</tr>\n" ; 

echo "<tr>\n<td>test_database</td> " ; 
if ($goon) {
  if (test_database($DEBUG)==TRUE) {
    echo "<td>ok</td>\n" ; 
  } else {
    $goon = 0 ; 
    echo "<td>nok</td>\n";
  }
} else  { 
    echo "<td>non passe</td>\n" ; 
}
echo "</tr>\n" ; 
echo "<tr><td>dispo command mysqldump</td> " ; 
if ($goon) {
  //  $backupFile = $dbname . date("Y-m-d-H-i-s") . '.gz';
  // $command = "mysqldump --opt -h $dbhost -u $dbuser -p $dbpass $dbname | gzip > $backupFile";
  // system($command);
  $cmd_dump = exec("which mysqldump") ; 
  if (is_executable($cmd_dump )) { // "/usr/bin/mysqldump")) {
    echo "<td>".$cmd_dump. " ok</td>\n" ; 
  } else {
    $goon = 0 ; 
    echo "<td>".$cmd_dump. " nok</td>\n";
  };
 };
echo "</tr>\n" ; 
echo "<tr><td>dispo command gzip</td> " ; 
$gzip = 0 ; 

if ($goon) {
  $cmd_gzip = exec("which gzip") ;
  if (is_executable($cmd_gzip)) {
    echo "<td>". $cmd_gzip . " dispo</td>\n" ; 
    $gzip = 1 ; 
  } else {
    $gzip = 0  ; 
    echo "<td>". $cmd_gzip . " non dispo</td>\n";
  };
 };
echo "</tr>\n" ; 

echo "<tr><td>backup</td> " ;
include("../dbconnect.php") ;
if ($goon) {
  if ($valid_cmdbackup=="") {

    $cmd_backup = "mysqldump --opt -h ". $mysql_serveur. " -u ". $mysql_user. " --password=". 
      $mysql_pass. " ". $mysql_database ;
    $backupfile = $mysql_database . "-". date("Y-m-d-H-i-s") ; 

    if ($gzip) {
      $backupfile .= ".gz" ; 
      $cmd_backup .= " |gzip ";
    };
    $cmd_backup .= " > /tmp/" . $backupfile ; 
    $_SESSION['valid_cmdbackup'] = $cmd_backup ; 
  
    echo "<td>execute backup commande <br><small>". $cmd_backup. "</small><br> " ;
    echo "<form action=\"$PHP_SELF?cmd=do_backup\" method=\"POST\" />\n";
    echo "<input type=\"submit\" value=\"do_backup\">\n";
    echo "</form> \n"; 

  } else {
    
    if ($ret) 
    echo "<td class=\"warning\">commande <small>". $valid_cmdbackup. "</small> en erreur </td>" ; 
    else 
      echo "<td>commande <small>". $valid_cmdbackup. "</small>  <b>ok</b></td>" ;
    echo "<br>" ;
    echo $output_str . "</td>" ; 
  }  
 };
echo "</tr>\n" ; 
 
echo "</table>\n" ; 
echo "</fieldset> \n";  

echo "</body>\n"; 
echo "</html>\n";   


?>
