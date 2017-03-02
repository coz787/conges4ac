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

// src_aug1.php : script d'assistance a la fusion de base conges 
// developpe par dgac/cedre sous meme licence
$mversion = "v1.1c" ; 
// appel de PHP-IDS que si version de php > 5.1.2
if(phpversion() > "5.1.2") { include("../controle_ids.php") ;}

include("fonctions_install.php") ;
include("../fonctions_conges.php") ;
//include("../INCLUDE.PHP/fonction.php");
//include("../fonctions_javascript.php") ;
include("actk.php"); 


$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=TRUE;
//$DEBUG=TRUE;

$session=session_id();
$goon = 1 ; // indique si on peut poursuivre les tests 

// $currentcmd = (isset($_GET['cmd']) ?  $_GET['cmd'] : "" ) ; 
// $noffset = (isset($_POST['noffset']) ?  $_POST['noffset'] : "" ) ;

$currentcmd =  getpost_variable("cmd","") ;
$soffset = getpost_variable("soffset","") ; 
$offsetmin = 0 ; 

affiche_entete("Conges::toolkit de fusion - outil d'augmentation base source");
echo "<body>\n";
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">$PHP_SELF</legend> \n"; 
echo "assistance a la fusion de base de donnees conges : augmentation base source <br> 
version $mversion <br> " ;

if ($DEBUG) {
  echo "currentcmd is ". $currentcmd . "\n" ; 
  echo "soffset is ". $soffset . "\n" ;
};

echo "</fieldset> \n";
// echo "<p>$PHP_SELF</p>\n"; 
echo "<br>\n" ; 

echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">ContrÃ´le des structures de donnÃ©es</legend> \n"; 
echo "<table border=1>\n" ;
echo "<tr><td width=\"65%\" align=center >action ou test</td><td align=center>status</td></tr>\n" ;  

echo "<tr>\n<td>test_dbconnect_file</td> " ; 
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

include("../dbconnect.php") ;
$mysql_link = mysql_newconnexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database,$mysql_charset);

echo "<tr>\n<td>table conges_groupe: ajout champ g_newgid </td> " ; 
if ($goon) {
  $sql_cg = "select g_newgid from conges_groupe" ;
  // $sql_cg = "select g_gid from conges_groupe" ;

  $req_sql_cg = mysqli_query($mysql_link,$sql_cg) or die("ERREUR : sql_cg <br>\n".$sql_cg." --> ".mysqli_error());
 // $req_sql_cg = mysql_query($sql_cg, $mysql_link) ;

  if ($req_sql_cg) { 
  // while ($resultat1 = mysql_fetch_array($req_sql_cg))
  //   { 
  //     printf("resultat1 %s\n" , join("",$resultat1));
  //     $jres = $resultat1[0];
  //     echo "<p>result is  $jres</p>" ; } 
  // elle existe deja 
    echo "<td>ok.<br> table conges_groupe dispose deja de la colonne g_newgid </td>" ; 
  } else {
    if ($currentcmd == "addg_newgid") {
      $sql_cg_alter1_arr = array(
          "LOCK TABLES `conges_groupe` WRITE;",
          "ALTER TABLE `conges_groupe` ADD `g_newgid` INT( 11 ) ;" ,
          "UNLOCK TABLES; " ) ; 
      foreach ($sql_cg_alter1_arr as $sql_un) {
        if ($goon) {
          $req_sql_alter1 = mysqli_query($mysql_link,$sql_un) ;
          if (!$req_sql_alter1) {
            $goon = 0 ; 
            echo "<td>echec : ". mysqli_error(). "</td>" ; 
          }
        }
      }
      if ($goon) {
            echo "<td>succes : colonne g_newgid cree </td>" ; 
      };
    } else {

    echo "<td>" ; 
  		echo "<form action=\"$PHP_SELF?cmd=addg_newgid\" method=\"POST\">\n";
		echo "<input type=\"submit\" value=\"effectuer\">\n";
		echo "</form>\n";
        echo "</td>" ; 
    };
  }
} else  { 
  echo "<td>non passe</td>\n" ; 
}
// NON: inutile 
// on determine le max des id existants ; 
// $sql_cg_f1 = "select  max(g_gid) from `conges_groupe` ; ";
// $req_sql_cg_f1 = mysql_query($sql_cg_f1, $mysql_link) ;
// if ($req_sql_cg_f1) {
//   $res1 = mysql_fetch_array($req_sql_cg_f1) ; 
//   $offsetmin = $res1[0] + 1 ; 
//  }
//  else {
//    $goon = 0 ; 
//  }

echo "<tr><td>offset du nouveau champ g_newgid<br> " ;
// echo "valeur offset mini = <b>". $offsetmin . "</b>" ; 
echo "</td> " ; 
if ($goon) {
  $msg1 = ""; 
  if ($currentcmd == "offsetg_newgid") {
    // echo "<td>will do offset with ". $soffset. "</td>\n" ;
    // la valeur d'offset doit Ãªtre >= 0 ; 
    if ($soffset >= $offsetmin ) {      
      // on fait  echo "<td>will do offset with ". $soffset. "</td>\n" ;
      $sql_cg_u1 = "update `conges_groupe` set g_newgid = g_gid + ".  $soffset ; 
      // where * ; pour tous les rows 
      $req_sql_cg_u1 = mysqli_query($mysql_link, $sql_cg_u1) ;
      if( $req_sql_cg_u1 ) {
        echo "<td>offset de ". $soffset. " effectue avec succes. </td>\n" ;
      } else {
        echo "<td>echec offset : ". mysqli_error(). "</td>" ; 
        $goon = 0 ; 
      }
      
    } else  {
      $msg1 = "<em>corriger la valeur d'offset</em>" ; 
    };

  };
  if ($currentcmd != "offsetg_newgid" || $msg1 != "") { 
    echo "<td>" ; 
    echo $msg1 ; 
    echo "<form action=\"$PHP_SELF?cmd=offsetg_newgid\" method=\"POST\" />\n";
    echo "<label for=\"loffset\">valeur offset groupe</label>&nbsp;";  
    echo "<input type=\"text\" name=\"soffset\" size=6 >" ; 
    echo "<input type=\"submit\" value=\"effectuer\">\n";
    echo "</form>\n";
    // echo "<form action=\"$PHP_SELF?cmd=\" method=\"POST\" />\n";
    // echo "<input type=\"submit\" value=\"continuer\">\n";
    // echo "</form>\n";
    // echo "</td>" ;
  };

} else  { 
  echo "<td>non passe/disponible </td>\n" ; 
}
echo "<tr><td>table: conges_type_absence: ajout du nouveau champ ta_newid </td> " ; 
if ($goon) {
  $sql_ta  = "select ta_newid  from conges_type_absence" ;
  $req_sql_ta = mysqli_query($mysql_link,$sql_ta) ;

  if ($req_sql_ta) { 
    echo "<td>ok.<br> table conges_type_absence dispose deja de la colonne ta_newid </td>";
    
  } else {
    $goon = 0 ; 
    if ($currentcmd == "addta_newid") {
      $sql_cta_arr = array(
          "LOCK TABLES `conges_type_absence` WRITE;",
          "ALTER TABLE `conges_type_absence` ADD `ta_newid` INT(2) ;" ,
          "UNLOCK TABLES; " ) ; 
      $goon = 1 ; 
      foreach ($sql_cta_arr as $sql_un) {
        $req_sql_alter2 = mysqli_query($mysql_link,$sql_un) ;
        if (!$req_sql_alter2) {
          $goon = 0 ; 
          break; 
          echo "<td>echec : ". mysqli_error(). "</td>" ; 
        }
      }

      if ($goon) {
        echo "<td>succes : colonne ta_newi cree </td>"; 
        $req_sql_ta = 1 ; // on force ce resultat de requete a True 
      };
    } else {

      echo "<td>" ; 
      echo "<form action=\"$PHP_SELF?cmd=addta_newid\" method=\"POST\">\n";
      echo "<input type=\"submit\" value=\"effectuer\">\n";
      echo "</form>\n";
      echo "</td>" ; 
    };
  }
} else  { 
  echo "<td>non passe</td>\n" ; 
}
echo "</tr>\n" ; 
echo "</table>\n" ;
echo "</fieldset> \n";
echo "<br>\n" ; 

if ($goon && $req_sql_ta) {
  echo "<fieldset class=\"roundcorner\"> \n"; 
  echo "<legend class=\"rc2\">Mise a jour du champ ta_newid </legend> \n"; 
  if ($currentcmd == "save_tanewid") {
    // on fait la maj d'un item 
    $taid_update = getpost_variable("taid",-1) ;
    if ($taid_update != -1) {
      $sgettanval = getpost_variable("tan_val", "nothing"); // . $taid_update,"nothing")  ; 
      //       echo "<p> cta id ".$taid_update." update with value ". $sgettanval. "</p>" ;
      $sql_updateta = "update conges_type_absence set ta_newid = ".$sgettanval." where ta_id=".$taid_update.";" ;
      $req_maj_ta = mysqli_query($mysql_link,$sql_updateta) ;
      if ($req_maj_ta) {
        echo "<p>ta_id ". $taid_update." mis a jour avec ".$sgettanval. "</p>" ; 
      } else {
        echo "<p> ERREUR maj ". $taid_update." avec ".$sgettanval." error mysql ".mysqli_error()."</p>" ; 
      };
    } 
  };
  echo "<table border=1 width=\"90%\">\n" ;
  //  echo "<tr><td width=\"65%\" align=center >action/test</td><td align=center>status</td></tr>\n" ;
  echo "<tr><td>ta_id</td><td>ta_type</td><td>ta_libelle</td>
<td>ta_short_libelle</td><td>ta_newid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>\n" ; 
  $sql_ta  = "select *  from conges_type_absence order by ta_id" ;
  $req_maj_ta = mysqli_query($mysql_link,$sql_ta) ;

  if ($req_maj_ta) { 
    while ($lata = mysqli_fetch_row($req_maj_ta)) {
      echo "<tr>" ; // on fait un formulaire par rang = BOURRIN 
      echo "<form action=\"$PHP_SELF?cmd=save_tanewid&taid=$lata[0]\" method=\"POST\">\n";
      // ecrit les 4 donnees 
      echo "<td>".$lata[0]."</td><td>".$lata[1]."</td><td>".$lata[2]."</td><td>".$lata[3]."</td>\n";
      $stanval = "tan_val"; // .$lata[0] ; 
      echo "<td>[".$lata[4]."]&nbsp;&nbsp;<input type=\"text\" name=\"". $stanval."\" size=6 value=\"\">". 
        "&nbsp;&nbsp; <input type=\"submit\" value=\"update\">\n";

// <a href=\"$PHP_SELF?cmd=save_tanewid&taid=$lata[0]\">save</a></td>" ;
      echo "</form>\n"; 
      echo "</tr>" ; 
    }
  } else {
    echo "<tr><td>" . mysqli_error(). "</td></tr>\n"; 
  }; 
  echo "</table>\n"; 
  echo "</fieldset>\n" ; 
 };

$req_ta_check = mysqli_query($mysql_link,"select ta_id from conges_type_absence where ta_newid = NULL ") ;

if ($req_ta_check) {
  while ($reqdum = mysqli_fetch_row($req_ta_check)) {
    $goon = 0 ; // si on trouve des Ã©lÃ©ments non rempli 
  };
 } else   {
  $goon = 0 ; // si requete est en erreur ldd , il y a un pb 
};

$req_gp_check = mysqli_query($mysql_link,"select g_gid from conges_groupe where g_newgid = NULL ") ;
if ($req_gp_check) {
  while ($reqdum = mysqli_fetch_row($req_gp_check)) {
    $goon = 0 ; // si on trouve des Ã©lÃ©ments non rempli 
  }; 
} else {
  $goon = 0 ; // si requete est en erreur ldd , il y a un pb 
};

if ($goon) {

  echo "<p><b>OK : si vous avez correctement mis a jour les valeurs de g_newid et ta_newid , vous
pouvez proceder a la routine permettant la fusion</b> </p>"; 
 }else {
  echo "<p>Les donnÃ©es de la base src sont incompletes </p>." ; 
 }; 
echo "</body>\n"; 
echo "</html>\n";   


?>
