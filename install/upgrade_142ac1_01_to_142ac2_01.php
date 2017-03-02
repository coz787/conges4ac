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

// upgrade_ti_142ac1_01.php assistance a la montee de version de php_conges
// developpe par dgac/ssim sous meme licence

$mversion = "v0.1a" ; 
// appel de PHP-IDS que si version de php > 5.1.2
if(phpversion() > "5.1.2") { include("../controle_ids.php") ;}

include("actk.php"); 
include("fonctions_install.php") ;
include("../fonctions_conges.php") ;
include("../version.php"); 
// $version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=TRUE;
//$DEBUG=FALSE;
session_start();
$session=session_id();
$goon = 1 ; // indique si on peut poursuivre les tests 

// $currentcmd = (isset($_GET['cmd']) ?  $_GET['cmd'] : "" ) ; 
// $noffset = (isset($_POST['noffset']) ?  $_POST['noffset'] : "" ) ;

$currentcmd =  getpost_variable("cmd","") ;
// $soffset = getpost_variable("soffset","") ; 
// $offsetmin = 0 ; 

affiche_entete("Conges::toolkit upgrade_to_142ac2_01.php");
echo "<body>\n";
echo "<fieldset class=\"roundcorner\" > <legend class=\"rc2\">$PHP_SELF</legend>
version $mversion - outil d'assistance a la montÃ©e de version conges de vers 142ac2_01"; 
// cela prÃ©voit : mise Ã  jour config::installed_version, creation config::url_conges_assistance, donnees necessaires pour integration conges::agenda , " ; //  class=\"roundcorner\"
if ($DEBUG) {
  echo "<br>currentcmd is ". $currentcmd . "<br>" ; 
  echo "config_php_conges_version is ". $config_php_conges_version . "<br>" ;
  //   echo "soffset is ". $soffset . "<br>" ;
};

echo "</fieldset> \n";
// echo "<p>$PHP_SELF</p>\n"; 
echo "<br>\n" ; 

// commande courante = 
if ($currentcmd == "src_reinit") {
  $src_param = array() ;
  unset( $_SESSION['src_param']); 
} elseif  ($currentcmd == "src_valid") {
  $src_param = array ( 'server' => getpost_variable("src_server","") ,
                'dbname' => getpost_variable("src_dbname","") ,
                'user' => getpost_variable("src_user","") ,
                'pw' => getpost_variable("src_pw","") );
  $_SESSION['src_param'] = $src_param ;   
} ; 

if ($DEBUG) {
  echo "src_param is [".  join(", ", $_SESSION['src_param'] ) . "] <br>" ;
};
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">contrÃ´les sur database </legend> \n"; 

echo "<table border=1>\n" ;
echo "<tr><td width=\"35%\" align=center >info/action</td><td align=center>status</td></tr>\n" ;  

echo "<tr>\n<td>test_dbconnect_file base dest</td> " ; 
if ($goon) {
  if (test_dbconnect_file($DEBUG)==TRUE) {
    echo "<td>ok</td>\n" ; 
  } else {
    $goon = 0 ; 
    echo "<td class=\"warning\">nok</td>\n";
  } 
} else  { 
    echo "<td class=\"warning\">non passe</td>\n" ; 
}
echo "</tr>\n" ; 

echo "<tr>\n<td>test_database</td> " ; 
if ($goon) {
  if (test_database($DEBUG)==TRUE) {
    echo "<td>ok</td>\n" ; 
  } else {
    $goon = 0 ; 
    echo "<td class=\"warning\">nok</td>\n";
  }
} else  { 
    echo "<td class=\"warning\">non passe</td>\n" ; 
}
echo "</tr>\n" ; 

include("../dbconnect.php") ;
$mysql_link = mysql_newconnexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database);

$required_version = "1.4.2ac1_01" ; 
echo "<tr>\n<td>test version application</td> " ; 
if ($goon) {
  $sql_iv = "select conf_valeur from conges_config where conf_nom = 'installed_version' "  ;
  $req_sql_iv = mysqli_query($sql_iv, $mysql_link) ;
  if ($req_sql_iv) { 
    $lrow = mysqli_fetch_row($req_sql_iv); 
    $instal_version = $lrow[0] ; 
    if ($instal_version == $required_version) {
      echo "<td>ok. application version est ". $instal_version . "</td>\n" ;
    } else {
      echo "<td class=\"warning\">nok. application version est <b>". $instal_version . "</b> et diffÃ¨re de la version requise ". $required_version . "</td>\n" ;
      $goon = 0 ;
    }; 
  } else {
    echo "<td class=\"warning\">nok. paramÃ¨tre installed_version non dÃ©clarÃ©. </td>\n" ;
    $goon = 0 ;
  }
}
echo "</tr>\n" ; 
echo "</table>\n</fieldset> \n"; // fin onglet 

// _todo Ã  factoriser pour re-utiliser en fin de programme ; 
$lconfignv1 = array( 
       "int_calendar" => array("TRUE", "16_Int_calendar", "boolean", "config_comment_int_calendar","add"),
       "calendar_tag" => array("sigp-conges-sg", "16_Int_calendar", "texte", "config_comment_calendar_tag","add"),
       "hdeb_periode_am" => array( "090000", "16_Int_calendar", "texte", "heure debut de matinee","add" ),
       "hfin_periode_am" => array( "130000", "16_Int_calendar", "texte", "heure fin de matinee" ,"add"),
       "hdeb_periode_pm" => array( "140000", "16_Int_calendar", "texte", "heure debut d apres midi","add" ),
       "hfin_periode_pm" => array( "180000", "16_Int_calendar", "texte", "heure fin d apres midi","add"),
       "url_conges_assistance" => array("http://url_conges_assistance.sigp.aviation", "00_php_conges", 	"texte", "config_url_conges_assistance","add")
); 
echo "<br>" ; 
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">mise Ã  jour des variables de configuration </legend> \n"; 

echo "<table border=1>\n" ;
echo "<tr><td width=\"35%\" align=center >info/action</td><td align=center>status</td></tr>\n" ;

add_set_config($lconfignv1, $mysql_link) ; 

echo "</table>\n</fieldset> \n"; // fin onglet 

echo "<br>" ; 
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">modification tables de donnÃ©es </legend> \n"; 

echo "<table border=1>\n" ;
echo "<tr><td width=\"35%\" align=center >info/action</td><td align=center>status</td></tr>\n" ;
echo "<tr>\n<td>table conges_users: ajout champ u_has_int_calendar </td> " ;
if ($goon) {
  $sql_cuint  = "select u_has_int_calendar from conges_users " ;
  $req_sql_cuint = mysqli_query($sql_cuint, $mysql_link) ;
  if ($req_sql_cuint) {
    echo "<td>ok.<br> table conges_users dispose deja de la colonne u_has_int_calendar</td>" ;    } else {
    if ($currentcmd == "addg_uhint") {
      $sql_cu_alter1_arr = array(
          "LOCK TABLES `conges_users` WRITE;",
          "ALTER TABLE `conges_users` ADD `u_has_int_calendar` enum('Y','N') DEFAULT 'Y' COMMENT 'integration conges calendar' ;" ,
          "UNLOCK TABLES; " ) ; 
      foreach ($sql_cu_alter1_arr as $sql_un) {
        if ($goon) {
          $req_sql_alter1 = mysqli_query($sql_un, $mysql_link) ;
          if (!$req_sql_alter1) {
            $goon = 0 ; 
            echo "<td class=\"warning\" >echec : ". mysqli_error(). "</td>" ; 
          }
        }
      }
      if ($goon) {
            echo "<td>succes : colonne u_has_int_calendar cree </td>" ; 
      };
    } else {
      echo "<td>" ; 
      echo "<form action=\"$PHP_SELF?cmd=addg_uhint\" method=\"POST\">\n";
      echo "<input type=\"submit\" value=\"effectuer\">\n";
      echo "</form>\n";
      echo "</td>" ; 
    };
  }
 } else { 
  echo "<td>&nbsp;</td>\n" ; 
 };   
echo "<tr>\n<td>table conges_users: ajout champ u_is_gest </td> " ;
if ($goon) {
  $sql_cuisgest  = "select u_is_gest from conges_users " ;
  $req_sql_cuisgest = mysqli_query($sql_cuisgest, $mysql_link) ;
  if ($req_sql_cuisgest) {
    echo "<td>ok.<br> table conges_users dispose deja de la colonne u_is_gest</td>" ;    } else {
    if ($currentcmd == "addg_uistgest") {
      $sql_cu_alter2_arr = array(
          "LOCK TABLES `conges_users` WRITE;",
          "ALTER TABLE `conges_users` ADD `u_is_gest` enum('Y','N') DEFAULT 'N' COMMENT 'status gestionnaire' ;" ,
          "UNLOCK TABLES; " ) ; 
      foreach ($sql_cu_alter2_arr as $sql_un) {
        if ($goon) {
          $req_sql_alter2 = mysqli_query($sql_un, $mysql_link) ;
          if (!$req_sql_alter2) {
            $goon = 0 ; 
            echo "<td class=\"warning\" >echec : ". mysqli_error(). "</td>" ; 
          }
        }
      }
      if ($goon) {
            echo "<td>succes : colonne u_is_gest cree </td>" ; 
      };
    } else {
      echo "<td>" ; 
      echo "<form action=\"$PHP_SELF?cmd=addg_uistgest\" method=\"POST\">\n";
      echo "<input type=\"submit\" value=\"effectuer\">\n";
      echo "</form>\n";
      echo "</td>" ; 
    };
  }
 } else { 
  echo "<td>&nbsp;</td>\n" ; 
 };   
echo "</table>\n</fieldset> \n"; // fin onglet 

echo "<br>" ; 
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">nettoyage de donnÃ©es</legend> \n"; 

echo "<table border=1>\n" ;
echo "<tr valign=top ><td width=\"35%\" align=center >info/action</td><td align=center>status</td></tr>\n" ;
echo "<tr valign=top >\n<td>table conges_users: nettoyage des adresses mails </td> " ;
// $goon = 1 ; 
if ($goon)  {
  // echo "<td><small>" ; 
  $sql_ule  = "select u_login, u_email from conges_users order by u_login " ;
  $req_sql_ule = mysqli_query($sql_ule, $mysql_link) ;
  $lfaultusers = array(); 
  $sfaultemail = "<small>" ; 
  while ( $luser = mysqli_fetch_row($req_sql_ule) ) {
    $email = $luser[1] ;
    if ((substr($email, -1, 1) == ">") || (substr($email, 0, 1) == "<")) {
      $sfaultemail .= $email. ", " ; 
      $neat_email = str_replace("<","",$email);
      $neat_email = str_replace(">","",$neat_email);
      $lfaultusers[$luser[0]] = $neat_email ; 
    }; 
  }
  $sfaultemail .= "</small>" ;
  $neatemailcmd = "neat_email" ; 
  if ( count($lfaultusers) > 0 ) {
    if ($currentcmd == $neatemailcmd ) {
      $lsql_neat = array("lock tables conges_users write ;");
      foreach ($lfaultusers as $login => $neatemail) {
        $instr2 = "update conges_users set u_email = '".$neatemail."' where u_login='".
          $login."' ; " ;
        array_push($lsql_neat,$instr2);
      }
      array_push($lsql_neat,"unlock tables; " ); 
      //echo "<td>_todo : <br><small>"; 
      foreach ($lsql_neat as $sql_un) {
        $req_sql_n = mysqli_query($sql_un, $mysql_link) ;
        if (!$req_sql_n) {
          $goon = 0 ; 
          break; 
          echo "<td class=\"warning\" >echec : ". mysqli_error(). "on". 
            $sql_un. "</td>" ; 
        };
      }
      if ($goon) {
        // redirection 
        // http_redirect("", array(), true, HTTP_REDIRECT_PERM);
        echo "<td ><b>ok</b> commande de nettoyage effectuÃ©. </td>" ;
      } else {
        echo "<td class=\"warning\" >echec global commande nettoyage</td>" ; 
      }

    } else { // on propose la commande et la liste des emails Ã  corriger
      echo "<td>Les adresses email suivantes sont Ã  corriger <br> " ; 
      echo "<form action=\"$PHP_SELF?cmd=neat_email\" method=\"POST\">\n";
      echo "<input type=\"submit\" value=\"effectuer\">\n";
      echo "</form>\n";
      echo $sfaultemail ; 
      echo "</td>" ; 
    }

  } else {
    echo "<td>Les adresses emails sont propres ; pas de correction Ã  effectuer.</td>" ; 
  }
  // echo "</small></td>" ; 

 }; 
echo "</tr>\n" ; 

echo "</table>\n</fieldset> \n"; // fin onglet 
// $goon = 1 ; 
// in fine maj de la variable de config   'installed_version' Ã  "1.4.2ac1.01 
$lconfignv2 = array( 
   "installed_version" => array("1.4.2ac2_01","00_php_conges","texte",
                                "config_comment_installed_version", "update")); 
echo "<br>" ; 
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">mise Ã  jour de la version de l'application </legend> \n"; 

echo "<table border=1>\n" ;
echo "<tr><td width=\"35%\" align=center >info/action</td><td align=center>status</td></tr>\n" ;

add_set_config($lconfignv2, $mysql_link) ; 

echo "</table>\n</fieldset> \n"; // fin onglet 

if ($goon) {
  echo "<p><b>OK : la mise Ã  jour de la base est complÃ¨te et conforme au change envisagÃ© </b> </p>"; 
 }
echo "</body>\n"; 
echo "</html>\n";   


function add_set_config($lconfignv, $mysql_link)  
// manage the addition of a set of conges::config item define by $lconfignv 
{
  global $goon, $currentcmd,$PHP_SELF ; // pas top mais on s'y fait ...
  foreach ($lconfignv as $itemk => $litemval) {
    $sql_confnom = "select conf_nom from conges_config where conf_nom='".$itemk."'"; 
    $req_confnom = mysqli_query($sql_confnom, $mysql_link) ;
    echo "<tr valign=top >" ;
    echo "<td>".$itemk."</td>" ; 
    if ($req_confnom) { 
      // on cree la suite d'instruction 
      $litemv = $litemval ; 
      $mode = array_pop($litemv) ; // 
      $svalue = "'".$itemk."',  ".my_implode($litemv) ;
      $cf_valeur = $litemv[0] ; 
      if ($mode == "add") { 
        $instr1 = "insert into conges_config values (".$svalue.") ; " ;
      } elseif ($mode == "update") {
        $instr1 = "update conges_config set conf_valeur = '".$cf_valeur."' where conf_nom='".
          $itemk."' ; " ;
      } else {
        // error 
      }; 
      $lsql_creat_config = array(
             "lock tables conges_config write ;",
             $instr1 ,  
             "unlock table ; " ) ;
      // $itemcmd est la commande attendue 
      $itemcmd = "add_cf_".$itemk ; 
      // on constuit le texte de commande 
      $sformcmd = $mode. " du champ ".$itemk. " par <br> " ;
      $sformcmd .= "<small>" ;
      foreach ($lsql_creat_config as $sql_un) {
        $sformcmd .= $sql_un."<br>" ; 
      }
      $sformcmd .= "</small>" ; 
      $sformcmd .= "<form action=\"$PHP_SELF?cmd=".$itemcmd."\" method=\"POST\">\n";
      $sformcmd .= "<input type=\"submit\" value=\"effectuer\">\n";
      $sformcmd .= "</form>\n";

      // on recherche le record 
      $conf_n = mysqli_fetch_row($req_confnom) ;
      //      if (($mode=="add") && $conf_n ) {
      if ($mode=="add") {       
        if ($conf_n) {
          echo "<td>paramÃ¨tre deja existant </td>";
        } else {
          if ($currentcmd == $itemcmd ) { // alors on cree la donnees
            foreach ($lsql_creat_config as $sql_un) {
              $req_sql_c = mysqli_query($sql_un, $mysql_link) ;
              if (!$req_sql_c) {
                $goon = 0 ; 
                break; 
                echo "<td class=\"warning\" >echec : ". mysqli_error(). "</td>" ; 
              };
            };
            if ($goon) {
                echo "<td><b>ok</b> ".$mode." effectuÃ©. </td>" ; 
            };
          } else {  // on propose de la creer 
            if ($goon) { 
              echo "<td>".$sformcmd."</td>"; 
            } else {
              echo "<td>&nbsp;</td>\n" ; 
            };
            $goon = 0 ; 
          }
        }
      } elseif ($mode == "update") {
        if ($conf_n) {
          $sql_confval = "select conf_valeur from conges_config where conf_nom='".$itemk."'";
          $req_confval = mysqli_query($sql_confval, $mysql_link) ;
          $conf_val = mysqli_fetch_row($req_confval) ;
          if ($conf_val[0] == $cf_valeur) { 
            echo "<td> <b>vaut deja </b>".$cf_valeur."</td>";
          } else {
            if ($currentcmd == $itemcmd ) { // alors on cree la donnees
              foreach ($lsql_creat_config as $sql_un) {
                $req_sql_c = mysqli_query($sql_un, $mysql_link) ;
                if (!$req_sql_c) {
                  $goon = 0 ; 
                  break; 
                  echo "<td class=\"warning\" >echec : ". mysqli_error(). "</td>" ; 
                };
              };
              if ($goon) {
                echo "<td><b>ok</b> maj effectuÃ©.</td>" ;
              }; 
            } else {  // on propose de la creer 
              if ($goon) { 
                echo "<td>".$sformcmd."</td>"; 
              } else {
                echo "<td>&nbsp;</td>\n" ; 
              };
              $goon = 0 ; 
            }
          }
        } else { 
          echo "<td class=\"warning\" >echec : le paramÃ¨tre n existe pas </td>";
        }
      } else {
        echo "<td class=\"warning\" >erreur de paramÃ¨trage du programme </td>";
        $goon = 0 ;
      };
    }; 

    echo "</tr>" ; 
    // <td>".$itemk."</td><td>". $litemv[3]."<br>".$sql_confnom."</td></tr>\n"; 
  }
}
