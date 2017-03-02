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
$mversion = "v1.1f" ; 
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

affiche_entete("Conges::toolkit de fusion - outil d'import vers base dest");
echo "<body>\n";
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">$PHP_SELF</legend> \n"; 
echo "assistance a la fusion de base de donnees conges : import base source vers base dest <br> 
version $mversion <br> " ; 
if ($DEBUG) {
  echo "currentcmd is ". $currentcmd . "<br>" ; 
  echo "config_php_conges_version is ". $config_php_conges_version . "<br>" ;
  //   echo "soffset is ". $soffset . "<br>" ;
  print_r( $_SESSION ); 
};

echo "</fieldset> \n";
// echo "<p>$PHP_SELF</p>\n"; 
echo "<br>\n" ; 

// commande courante = 
if ($currentcmd == "src_reinit") {
  $src_param = array() ;
  unset( $_SESSION['src_param']); 
  session_destroy() ; // complementaire 
} elseif  ($currentcmd == "src_valid") {
  $src_param = array ( 'server' => getpost_variable("src_server","") ,
                'dbname' => getpost_variable("src_dbname","") ,
                'user' => getpost_variable("src_user","") ,
                'pw' => getpost_variable("src_pw","") );
  $_SESSION['src_param'] = $src_param ;   
} ; 

// if (array_key_exists('lsqlcmde', $_SESSION)) { 
//   $lsqlcmde = $_SESSION['lsqlcmde'] ;
//  } else {
//   $lsqlcmde = array() ; 
// }; 
if (array_key_exists('lsqlcmdefile', $_SESSION)) { 
  $sqlcmdefile = $_SESSION['lsqlcmdefile'] ;
};
if (array_key_exists('lsqlcmdestatusfile', $_SESSION)) { 
  $sqlcmdestatusfile = $_SESSION['lsqlcmdestatusfile'] ;
};

if ($DEBUG) {
  echo "src_param is [".  join(", ", $_SESSION['src_param'] ) . "] <br>" ;
};
$mysqlsrc_link ; 
$mysqlsrc_link_valid = 0 ; 
if (isset($_SESSION['src_param'])) {
   $mysqlsrc_link = mysql_newconnexion($_SESSION['src_param']['server'],
                                       $_SESSION['src_param']['user'],
                                       $_SESSION['src_param']['pw'], 
                                       $_SESSION['src_param']['dbname'] ); 
   if (is_string($mysqlsrc_link)) { 
     // echo "mysqlsrc_link = ". $mysqlsrc_link . "<br>"; 
     $mysqlsrc_link_valid = 0 ; 
     $goon = 0 ; 
   } else {
     $mysqlsrc_link_valid = 1 ; 
   } 
// localhost, congesadmin, congesadmin, conges_db
 } else {
     $goon = 0 ; 
 };

// produit le formulaire de saisie des paramÃ¨tres ou de validitÃ© de la connexion 

if ($mysqlsrc_link_valid) {
  echo "<form action=\"$PHP_SELF?cmd=src_reinit\" method=\"POST\" />\n";
  echo "<input type=\"submit\" value=\"reinit\">\n";
  echo "</form> \n"; 
 }; 
echo "<form action=\"$PHP_SELF?cmd=src_valid\" method=\"POST\" />\n";

echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">database source</legend> \n"; 

echo "<table border=1>\n" ;
echo "<tr><td  align=center width=\"40%\">info</td><td  width=\"60%\" align=center>value</td></tr>\n" ;  
echo "<tr><td  align=center >src_server</td><td align=center>"; 
if ($mysqlsrc_link_valid) 
  echo $_SESSION['src_param']['server'] ; 
else 
  echo "<input type=\"text\" name=\"src_server\" size=30 >"; 
echo "</td></tr>\n" ;  

echo "<tr><td align=center >src_dbname</td><td align=center>"; 
if ($mysqlsrc_link_valid) 
  echo $_SESSION['src_param']['dbname'] ; 
else 
  echo "<input type=\"text\" name=\"src_dbname\" size=30 >" ; 
echo "</td></tr>\n" ;  

echo "<tr><td  align=center >src_user</td><td align=center> "; 
if ($mysqlsrc_link_valid) 
  echo $_SESSION['src_param']['user'] ; 
else 
  echo "<input type=\"text\" name=\"src_user\" size=30 >"; 
echo " </td></tr>\n" ;  

echo "<tr><td align=center >src_pw</td><td align=center>"; 
if ($mysqlsrc_link_valid) 
  if ($DEBUG)   echo $_SESSION['src_param']['pw'] ; 
  else echo "*** hidden ***" ; 
else 
  echo "<input type=\"password\" name=\"src_pw\" size=30 >"; 
echo "</td></tr>\n" ;  

echo "<tr>" ;
if ($mysqlsrc_link_valid) 
  echo "<td align=center >&nbsp;</td><td align=center>acces base src ok</td>";
else
  echo "<td align=center ><input type=\"submit\" value=\"valider\"></td><td class=\"warning\" align=center>acces base src non ok</td>";
echo "</tr>" ;


echo "</table>";
echo "</fieldset> \n";
echo "</form> \n"; 

echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">database dest </legend> \n"; 

echo "<table border=1>\n" ;
echo "<tr><td width=\"35%\" align=center >info/action </td><td align=center>status</td></tr>\n" ;  

echo "<tr>\n<td>test_dbconnect_file base dest</td> " ; 
if ($goon) {
  if (test_dbconnect_file($DEBUG)==TRUE) {
    echo "<td>ok</td>\n" ; 
  } else {
    $goon = 0 ; 
    echo "<td class=\"warning\" >nok</td>\n";
  } 
} else  { 
    echo "<td class=\"warning\" >non passÃ©</td>\n" ; 
}
echo "</tr>\n" ; 

echo "<tr>\n<td>test_database</td> " ; 
if ($goon) {
  if (test_database($DEBUG)==TRUE) {
    echo "<td>ok</td>\n" ; 
  } else {
    $goon = 0 ; 
    echo "<td class=\"warning\" >nok</td>\n";
  }
} else  { 
    echo "<td class=\"warning\" >non passÃ©</td>\n" ; 
}
echo "</tr>\n" ; 

// on reouvre ici les deux acces database CAR test_dbconnect_file, test_database ecrasent
// le lien 
include("../dbconnect.php") ;

$mysqldest_link = mysql_newconnexion($mysql_serveur, $mysql_user, $mysql_pass, $mysql_database,$mysql_charset);
$mysqlsrc_link = mysql_newconnexion($_SESSION['src_param']['server'],
                                       $_SESSION['src_param']['user'],
                                       $_SESSION['src_param']['pw'], 
                                    $_SESSION['src_param']['dbname'], $mysql_charset);
// WARNING SIMPLIFICATION : les deux database utilise le mÃªme charset type  
// on a alors 2 acces database ouvert $mysqldest_link sur la base "dest"
//                                    $mysqlsrc_link sur la base  "src"
// on verifie ici la coherence interbase 

echo "<tr><td>test coherence structure database </td>\n" ;  
if ($goon) {
  $lcoherence = test_coherence_db($mysqlsrc_link, $mysqldest_link); 

  if ($lcoherence[0]) {
    echo "<td>ok : status [".$lcoherence[1]."] </td>\n" ;
  } else {
    $goon = 0 ; 
    echo "<td class=\"warning\" ><b>nok</b> : status [".$lcoherence[1]."] </td>\n" ;
  };
 } else {
  echo "<td class=\"warning\" >non passÃ©</td>\n" ; 
 };

echo "</tr>\n" ; 
echo "</table>\n" ; 
echo "</fieldset> \n";


if (0 && $goon && $DEBUG) {

echo "<br><br>DEBUG table src conge_user = <br>" ; 
$req_src_user = mysqli_query($mysqlsrc_link,"select u_login, u_nom, u_prenom from conges_users") ;
while ($res1 = mysqli_fetch_row($req_src_user)){ 
  //echo $res1 . "<br>"; // 
  echo "[". join(" ",$res1)."]<br>";
}

echo "<br><br>DEBUG table dest conge_user = <br>" ; 
$req_dest_user = mysqli_query($mysqldest_link,"select u_login, u_nom, u_prenom from conges_users") ;
while ($res1 = mysqli_fetch_row($req_dest_user)){ 
  //echo $res1 . "<br>"; // 
  echo "[". join(" ",$res1)."]<br>";
}
}; 
echo "<br>\n" ; 
echo "<fieldset class=\"roundcorner\"> \n"; 
echo "<legend class=\"rc2\">coherence des databases src et dest </legend> \n"; 

echo "<table border=1>\n" ;
echo "<tr><td  align=center width=\"35%\">controle </td><td align=center>status/diagnostic</td></tr>\n" ;  
// echo "<tr><td>_tbc</td><td>_tbc</td></tr>\n" ;
// _ctrl : les agents de la base src ne doivent pas deja exister ds la base dest 
// forcable 
echo "<tr><td>les agents de la base source n'existent pas en base destination</td>"; 

if ($goon) {
  // on place tous les agents deja en base ds un directory indexe par le login 
  $req_dest_user = mysqli_query($mysqldest_link,"select u_login from conges_users") ;
  $l_dest_user = array(); 
  while ($res1 = mysqli_fetch_row($req_dest_user)) {
    //array_push($l_dest_user, $res1[0]) ; 
    $l_dest_user[$res1[0]] = $res1[0] ; // test plus efficace pour la suite 
  }
  // if ($DEBUG ) echo "<td>". join(" ",$l_dest_user) ."</td>" ; 

  $req_src_user = mysqli_query($mysqlsrc_link,"select u_login from conges_users") ;
  $test_res = 1 ; 
  $lduplicateuser = array() ; 
  $luser2add =  array(); // list des users a ajouter 
  $lreslogin = array ('admin', 'conges') ; // user techniq a ecacrter des traitements
  while ($res2 = mysqli_fetch_row($req_src_user)) {
    $newlogin = $res2[0] ; 

    if  ( !in_array($newlogin,$lreslogin) ) {
      // on ajoute l'utilisateur a la liste $luser2add pour la suite des ctrl 
      $luser2add[$newlogin] = $newlogin ;
      if (array_key_exists($newlogin, $l_dest_user)) {
        $test_res = 0 ;
        // break ; on met en liste les users dupliques (car il faut les traiter) 
        array_push($lduplicateuser, $newlogin) ; 
      };
    }; 
  }
  if ($test_res) {
    echo "<td><b>ok</b></td>\n"; 
  } else {
    $goon = 0 ; 
    echo "<td class=\"warning\" ><b>nok</b><br>utilisateurs dupliques: [".
      join(", ", $lduplicateuser). "] </td>\n"; 
  };
 } else {
  echo "<td class=\"warning\" >non passÃ©</td>\n"; 
 };
echo "</tr>\n"; 

// _ctrl : les groupes importÃ©s ne doivent pas exister en base dest 
echo "<tr><td>les groupes identifiÃ©s par g_newgid en src ne doivent pas exister en base dest </td>"; 
if ($goon) {
  // on place tous les groupes deja en base ds un directory 
  $req_dest_gp = mysqli_query($mysqldest_link,"select g_gid from conges_groupe") ;
  $l_dest_gp = array(); 
  while ($res1 = mysqli_fetch_row($req_dest_gp)) {
    //array_push($l_dest_user, $res1[0]) ; 
    $l_dest_gp[$res1[0]] = $res1[0] ; // test plus efficace pour la suite 
  }
  // if ($DEBUG ) echo "<td>". join(" ",$l_dest_gp) ."</td>" ;
  $req_src_gp = mysqli_query($mysqlsrc_link,"select g_newgid from conges_groupe") ;
  if ($req_src_gp) { // si la requÃ¨te est non vide 
    $test_res = 1 ; 
    $l_src_gpe = array();
    while ($res2 = mysqli_fetch_row($req_src_gp)) {
      $newgp = $res2[0] ;
      array_push($l_src_gpe, $newgp) ; 
      if ( !($newgp >= 1 ) || array_key_exists($newgp, $l_dest_gp)) {
        $test_res = 0 ;
        break ; 
      };
    }
    if ($test_res) {
      echo "<td><b>ok</b> ". join(" ",$l_src_gpe)." </td>\n"; 
    } else {
      $goon = 0 ; 
      echo "<td class=\"warning\" ><b>nok</b> ". join(" ",$l_src_gpe) ." </td>\n"; 
    };
  } else {  // si la requÃ¨te ne retourne rien // colonne n'existe pas 
    echo "<td class=\"warning\" ><b>nok</b> colonne g_newgid n'est pas definie! </td>"; 
    $goon = 0 ; 
  }
 } else {
  echo "<td class=\"warning\" >non passÃ©</td>\n"; 
 };
echo "</tr>\n"; 

// _ctrl : les types absences identifiÃ©s par ta_newid doivent etre defini en base src 
// et exister dans la base dest table conges_type_absence identifie par ta_id 
echo "<tr><td>les types absences identifiÃ©s identifiÃ©s par ta_newid doivent etre defini en base src 
<br> et exister dans la base dest table conges_type_absence identifie par ta_id </td>"; 

if ($goon) {
  $req_src_ta = mysqli_query($mysqlsrc_link,"select ta_newid from conges_type_absence") ;
  if ($req_src_ta) {  // colonne ta_newid est definie 
    // on recueil les ta_id en base dest 
    $l_dest_ta = array() ; 
    $req_dest_ta = mysqli_query($mysqldest_link,"select ta_id from conges_type_absence") ;
    while ($res1 = mysqli_fetch_row($req_dest_ta)) {
    //array_push($l_dest_user, $res1[0]) ; 
      $l_dest_ta[$res1[0]] = $res1[0] ; // test plus efficace pour la suite 
    }
    $test_res = 1 ; 
    $merror = "" ; 
    while($res2 = mysqli_fetch_row($req_src_ta)) {
      $importedta = $res2[0] ;
      if (!array_key_exists($importedta, $l_dest_ta)) {
        $test_res = 0 ;
        $merror = "le type absence " . $importedta . " n existe pas en base dest" ; 
        break ; 
      };
    }
    if ($test_res) {
      echo "<td><b>ok</b> </td>\n"; 
    } else {
      $goon = 0 ; 
      echo "<td class=\"warning\" ><b>nok</b> ". $merror ." </td>\n"; 
    };
     
  } else {
    echo "<td class=\"warning\" ><b>nok</b> colonne ta_newid n est pas definie </td>"; 
  };  
  
 } else {
  echo "<td class=\"warning\" >non passÃ©</td>\n"; 
}; 
echo "</tr>\n"; 

// on capte ds 2 table de correspondance les types absences importÃ©s 
$l_src_ta = array();
$l_src_tainv = array() ; 
$req_src_ta = mysqli_query($mysqlsrc_link,"select ta_id, ta_newid from conges_type_absence") ;
while($res2 = mysqli_fetch_row($req_src_ta)) {
  $l_src_ta[$res2[0]] = $res2[1] ;
  $l_src_tainv[$res2[1]] = $res2[0] ;
};
    
if ($DEBUG) {
  echo "<p>l_src_ta is <pre>";
  print_r($l_src_ta );
  echo "</pre></p>\n"; 
}; 

// _ctrlj : les types absences rÃ©fÃ©rencÃ©s dans les pÃ©riodes doivent trouver 
// une correspondance grace Ã  la table $l_src_ta 
echo "<tr><td>les types absences des Ã©lÃ©ments conges_periode identifiÃ©s par p_type
<br>doivent trouver leur correspondance dans la base dest
<br>ie. la table src conges_type_absence doit les rÃ©fÃ©rencer et leur associer
<br>un type absence en base dest </td>"; 

if ($goon) {
  // conges_periode 
  $req_src_conges_periode =  mysqli_query($mysqlsrc_link,"select * from conges_periode ") ;
  $test_res = 1 ;
  while ($res2 = mysqli_fetch_row($req_src_conges_periode)) {
    $newlogin = $res2[0] ; 
    if  ( array_key_exists($newlogin, $luser2add) ) {  
      // on ne traite que les users Ã  ajouter 
      $oldta = $res2[7] ; // ancien type de conges 
      if ( ! array_key_exists($oldta, $l_src_ta) ) {
        $test_res = 0 ;
        $merror = "la periode suivante reference un type absence non translattable " . \ 
          join("-", $res2) ;
        break ; 
      }
    }
  }

  if ($test_res) {
    echo "<td><b>ok</b> </td>\n"; 
  } else {
    $goon = 0 ; 
    echo "<td class=\"warning\" ><b>nok</b> ". $merror ." </td>\n"; 
  };
     
  
} else {
  echo "<td class=\"warning\" >non passÃ©</td>\n"; 
};
echo "</tr>\n"; 


echo "</table>\n" ; 
echo "</fieldset> \n";

// INSERT INTO `conges_db_20111115`.`conges_groupe` (
// `g_gid` ,
// `g_groupename` ,
// `g_comment` ,
// `g_double_valid` ,
// `g_newgid`
// )
// VALUES (
// '120', 'toto', 'toto', 'N', NULL
// );

if ($goon) { // tous les tests sont passes ; on peut importer 
  // conges_users, conges_echange_rtt, conges_artt, conges_periode , conges_solde_user 
  // conges_groupe, conges_groupe_resp, conges_groupes_grd_resp, conges_groupe_users 
  // conges_logs
  if ($currentcmd == "do_import") {
    // mysqli_query("LOCK TABLES conges_users, conges_echange_rtt, conges_artt, conges_periode , conges_solde_user, conges_groupe, conges_groupe_users, conges_groupe_resp, conges_groupes_grd_resp WRITE" ,  $mysqldest_link) ;
    $lsqlcmde = array() ; // sinon risque d'empilement des requetes 
    // conges_users 
    $req_src_user = mysqli_query($mysqlsrc_link,"select * from conges_users") ;
    while ($res2 = mysqli_fetch_row($req_src_user)) {
      $newlogin = $res2[0] ; 
      if  ( !in_array($newlogin,$lreslogin) ) {
        $req_dest_users = "insert into conges_users values (". 
          my_implode($res2) . ")";
        array_push($lsqlcmde,$req_dest_users) ;
        // on ajoute l'utilisateur a la liste $luser2add;
        // $luser2add[$newlogin] = $newlogin ;
      };
    }; 
    // conges_echange_rtt 
    $req_src_echange_rtt = mysqli_query($mysqlsrc_link,"select * from conges_echange_rtt") ;
    while ($res2 = mysqli_fetch_row($req_src_echange_rtt)) {
      $newlogin = $res2[0] ; 
      if  ( array_key_exists($newlogin, $luser2add) ) {  
        // respect des contraintes integrites refrentielles
        // import uniquement des user existant valide dans la table conges_users
        $req_dest_echange_rtt = "insert into conges_echange_rtt values (". my_implode($res2). ")";
        array_push($lsqlcmde,$req_dest_echange_rtt); 
      };
    };
    // conges_artt 
    $req_src_conges_artt = mysqli_query($mysqlsrc_link,"select * from conges_artt") ;
    while ($res2 = mysqli_fetch_row($req_src_conges_artt)) {
      $newlogin = $res2[0] ; 
      if  ( array_key_exists($newlogin, $luser2add) ) {  
        // respect des contraintes integrites refrentielles
        // import uniquement des user existant valide dans la table conges_users
        $req_dest_conges_artt = "insert into conges_artt values (". my_implode($res2). ")";
        array_push($lsqlcmde,$req_dest_conges_artt);
      };
    };
    // conges_periode 
    $req_src_conges_periode =  mysqli_query($mysqlsrc_link,"select * from conges_periode ") ;
    while ($res2 = mysqli_fetch_row($req_src_conges_periode)) {
      $newlogin = $res2[0] ; 
      if  ( array_key_exists($newlogin, $luser2add) ) {  
        // respect des contraintes integrites refrentielles
        // import uniquement des user existant valide dans la table conges_users

        $oldta = $res2[7] ;
        $res2[7] = $l_src_ta[$oldta] ; // attribution nouveau type conge
        $res2[13] = '' ; // p_num est force a '' ; auto_increment nouvelle base 
        $req_dest_conges_periode = "insert into conges_periode values (". my_implode($res2). ")";
        array_push($lsqlcmde,$req_dest_conges_periode);
      };
    };
    // conges_solde_user  (2nd version pour completer les manques ie. solde non cree)
    // on balaie une matrice 
    // (user src) x (type_absence dest pour les ta_type <> 'absences') 

    $req_dest_ta = mysqli_query($mysqldest_link,"select ta_id from conges_type_absence where ta_type <> 'absences'") ;
    // on se cree une liste des types absences base dest comme suit 
    // id_ta dest ->  id_ta src ou 0 si existe pas ; 
    $lta = array() ;
    while($resta = mysqli_fetch_row($req_dest_ta)) {
      if ( array_key_exists($resta[0], $l_src_tainv) ) {
        $lta[$resta[0]] = $l_src_tainv[$resta[0]]; 
        // ta destination ayant corres ds base importÃ©
      } else {
        $lta[$resta[0]] = 0 ; // ta destination sans correspondance 
      };
    }; 
    if ($DEBUG) {
      echo "<p>lta is <pre>";
      print_r($lta );
      echo "</pre></p>\n"; 
    };
    $req_src_user = mysqli_query($mysqlsrc_link,"select u_login from conges_users") ;
    while ($resuser = mysqli_fetch_row($req_src_user)) {
      $newlogin = $resuser[0] ; 
      if  ( array_key_exists($newlogin, $luser2add) ) {  
        // respect des contraintes integrites refrentielles
        // import uniquement des user existant valide dans la table conges_users
        foreach ($lta as $altak => $altav) {
          // on recherche le solde_user correspondant ; 
          $sql_src_csu = "select * from conges_solde_user where su_login='".
            $newlogin."' and su_abs_id=".$altav." ;" ;
          if ($DEBUG) {
            echo "<p>sql_src_csu is <pre>";
            print_r($sql_src_csu); 
            echo "</pre></p>\n"; 
          };
          $req_src_csu =  mysqli_query($mysqlsrc_link,$sql_src_csu) ;
          $src_csu = mysqli_fetch_row($req_src_csu) ; 
          if ($src_csu) {            // $oldta = $src_csu[1] ; 
            $src_csu[1] = $altak ; // attribution nouveau type conge 
            $req_dest_csu = "insert into conges_solde_user values (". my_implode($src_csu). ")";
            //          } else {
            //echo "<p>warning: req " . $sql_src_csu . " est vide </p>" ; 
          } else { // si solde_user non trouve , insert d'un conges_solde_user vide 
            $lnewcsu = array($newlogin, $altak, 0, 0 ) ;
            if ($DEBUG) {
              echo "<p>lnewcsu is <pre>";
              print_r($lnewcsu );
              echo "</pre></p>\n"; 
            };             
            $req_dest_csu = "insert into conges_solde_user values (". my_implode($lnewcsu). ")";
          }; 
          array_push($lsqlcmde,$req_dest_csu); 
        }; // foreach 
      };
    };
    // conges_groupe 
    $l_src_gpe = array();  // correspondance gpe initial -> gpe future ; 
    $req_src_gp = mysqli_query($mysqlsrc_link,"select * from conges_groupe") ;
    if ($req_src_gp) { // si la requÃ¨te est non vide 
      $test_res = 1 ; 
      while ($res2 = mysqli_fetch_row($req_src_gp)) {
        // on conserve ds un hash association g_id -> g_newgid
        $l_src_gpe[$res2[0]] = $res2[4] ; 
        $res2[0] = $res2[4]  ; // g_id devient g_newgid
        // force g_double_valid a 'N' si NULL ou '' 
        if ( $res2[3] == '') {
          $res2[3] = 'N' ; 
        };
        $newgrp =   array_slice ($res2, 0, 4);

        $req_dest_gp = "insert into conges_groupe values (". my_implode($newgrp) . ")"   ;
        array_push($lsqlcmde,$req_dest_gp); 
      };
    } else {
      // la requÃ¨te est vide 
    };
    // conges_groupe_users
    $req_src_groupe_users = mysqli_query($mysqlsrc_link,"select * from conges_groupe_users") ;
    while ($res2 = mysqli_fetch_row($req_src_groupe_users)) {
      $newlogin = $res2[1] ; 
      if  ( array_key_exists($newlogin, $luser2add) ) {  
        // respect des contraintes integrites refrentielles
        // import uniquement des user existant valide dans la table conges_users
        $oldgpe = $res2[0] ;
        $res2[0] =  $l_src_gpe[$oldgpe] ; 
        $req_dest_groupe_users = "insert into conges_groupe_users values (". my_implode($res2) . ")"   ;
        array_push($lsqlcmde,$req_dest_groupe_users) ;
      };
    };
    // conges_groupe_resp
    $req_src_groupe_resp = mysqli_query($mysqlsrc_link,"select * from conges_groupe_resp") ;
    while ($res2 = mysqli_fetch_row($req_src_groupe_resp)) {
      $newlogin = $res2[1] ; 
      if  ( array_key_exists($newlogin, $luser2add) ) {  
        // respect des contraintes integrites refrentielles
        // import uniquement des user existant valide dans la table conges_users
        $oldgpe = $res2[0] ;
        $res2[0] =  $l_src_gpe[$oldgpe] ; 
        $req_dest_groupe_resp = "insert into conges_groupe_resp values (". my_implode($res2) . ")"   ;
        array_push($lsqlcmde,$req_dest_groupe_resp) ;
      };
    };
    // conges_groupes_grd_resp
    $req_src_groupe_grd_resp = mysqli_query($mysqlsrc_link,"select * from conges_groupe_grd_resp") ;
    while ($res2 = mysqli_fetch_row($req_src_groupe_grd_resp)) {
      $newlogin = $res2[1] ; 
      if  ( array_key_exists($newlogin, $luser2add) ) {  
        // respect des contraintes integrites refrentielles
        // import uniquement des user existant valide dans la table conges_users
        $oldgpe = $res2[0] ;
        $res2[0] =  $l_src_gpe[$oldgpe] ; 
        $req_dest_groupe_grd_resp = "insert into conges_groupe_grd_resp values (". my_implode($res2) . ")"   ;
        array_push($lsqlcmde,$req_dest_groupe_grd_resp);
      };
    };
    // import conges_logs apparait en v1.0d 
    // NON : on ne reprend pas les LOGS 
    // $req_src_conges_logs =  mysqli_query("select * from conges_logs ", $mysqlsrc_link) ;
    // while ($res2 = mysqli_fetch_row($req_src_conges_logs)) {
    //   $newlogin = $res2[2] ; // log_user_login_par
    //   if  ( !in_array($newlogin,$lreslogin) ) {
    //     $res2[0] = '' ; // force a '' ; auto_increment nouvelle base
    //     // force g_double_valid a ' ' si NULL ou '' 
    //     if ( $res2[4] == '') {
    //       $res2[4] = ' ' ; 
    //     };
    //     $req_dest_conges_logs = "insert into conges_logs values (". my_implode($res2). ")";
    //     array_push($lsqlcmde,$req_dest_conges_logs);
    //   };
    // };
    // on insert un enregistrement comme trace de l'import de la base source 
    $snow = date("Y-m-d H:i:s");
    $res2 = array('',0,'admin','admin',' ', 
                  'import base src ['.  $_SESSION['src_param']['dbname'].']',$snow );
    $req_dest_conges_logs = "insert into conges_logs values (". my_implode($res2). ")";
    array_push($lsqlcmde,$req_dest_conges_logs);

    // on ecrit un fichier avec les commandes sql ; 1 ligne par commande ;
    $cmdtag = "/tmp/di_".$mysql_database."-".date("Y-m-d-H-i-s") ;
    $sqlcmdefile = $cmdtag .".sql" ; // fichier de command sql 
    $sqlcmdestatusfile = $cmdtag .".log" ; // fichier de trace des execution 
    $ofsql = fopen($sqlcmdefile, 'w') or die("can't open file to write : " . $sqlcmdefile);
    foreach ($lsqlcmde as $asqlcmde) {
      fwrite($ofsql, $asqlcmde."\n");
    }
    fclose($ofsql); 
    // on conserve la liste de commande 
    // $_SESSION['lsqlcmde'] = $lsqlcmde ; 
    // PLUTOT : on conserve le nom du fichier de la liste de commande 
    $_SESSION['lsqlcmdefile'] = $sqlcmdefile ; 
    $_SESSION['lsqlcmdestatusfile'] = $sqlcmdestatusfile ;

    // on affiche le nom du fichier et les commandes avant action 
    echo "<p>commandes d'importation sauvegardees en <b>". $sqlcmdefile . "</b>.</p>" ; 
    echo "<small>\n" ;
    echo "lsqlcmde <br>" ; 
    echo implode("<br>", $lsqlcmde );
    echo "</small>\n" ; 
    
    echo "<form action=\"$PHP_SELF?cmd=rdo_import\" method=\"POST\" />\n";
    echo "<input type=\"submit\" value=\"really_do_import\">\n";
    echo "</form> \n" ; 
    
  } elseif ($currentcmd == "rdo_import") {
    // on reconsttitue $lsqlcmde Ã  partir du fichier pointe par $sqlcmdefile
    $lsqlcmde = array() ; 
    $ofsql = fopen($sqlcmdefile, 'r') or die("can't open file to read ". $sqlcmdefile ); 
    while ( $aline = fgets($ofsql) ) {  // fgets($ofsql, 4096)
      // on met en liste les lignes , rtrim pour sup le line feed 
      array_push($lsqlcmde, rtrim($aline)) ; 
    };
    fclose($ofsql); 
    // on ouvre en ecriture le fichier cmdestatus 
    $oflog = fopen($sqlcmdestatusfile, 'w') or die("can't open file sqlcmdestatusfile to write ". $sqlcmdestatusfile); 

    mysqli_query($mysqldest_link, 
"LOCK TABLES conges_users WRITE,conges_echange_rtt WRITE,conges_artt WRITE,conges_periode WRITE,conges_solde_user WRITE,conges_groupe WRITE,conges_groupe_users WRITE,conges_groupe_resp WRITE,conges_groupe_grd_resp WRITE,conges_logs WRITE ; " ) or die("can't LOCK TABLES");
    echo "<p>log execution sauvegardes aussi en <b>". $sqlcmdestatusfile . "</b>.</p>" ;
    echo "commmande status <br> \n " ; 
    echo "<small>\n" ; 
    foreach ($lsqlcmde as $asqlcmde) {
      $req3 = mysqli_query($mysqldest_link, $asqlcmde) ;
      if ($req3) {
        $msg = $asqlcmde . ": ok <br>" ;
      } else {
        $msg = $asqlcmde . ": <span style=\"color: #f00;\"> error ". mysqli_error(). "</span><br>" ;
      }
      echo $msg ;
      fwrite($oflog, $msg."\n");
    }
    echo "</small>\n" ; 
    // echo implode("<br>", $lsqlcmde );
    // at the end of import 
    mysqli_query($mysqldest_link, "UNLOCK TABLES ; " ) or die("can't UNLOCK TABLES");
    fclose($oflog);
  }

else {
    echo "<form action=\"$PHP_SELF?cmd=do_import\" method=\"POST\" />\n";
    echo "<input type=\"submit\" value=\"do_import\">\n";
    echo "</form> \n"; 
  }
 };

echo "</body>\n"; 
echo "</html>\n";   

// _tbc ; en utilisant la description de structure SHOW COLUMNS FROM table ; 
// perimetre: conges_users, conges_echange_rtt, conges_artt, conges_periode , conges_solde_user, conges_groupe, conges_groupe_users, conges_groupe_resp, conges_groupes_grd_resp
function test_coherence_db($src_lnk,$dest_lnk) 
{
  // return array(1,"** test coherence non implemente **"); 
  // _tbc 
  $ltable = array('conges_users','conges_echange_rtt','conges_artt','conges_periode','conges_solde_user',
                  'conges_groupe','conges_groupe_users','conges_groupe_resp','conges_groupes_grd_resp', 'conges_logs');
  $ltable1 = array('conges_users');
  $sstatus = "status ok for";
  $sanom = ""; 
  $mresult = 1 ; 
  foreach ($ltable as $atable) {
    $req_showt_src = mysqli_query($src_lnk, "SHOW COLUMNS FROM ".$atable ) ;
    $lre_src = array();
    while ($res1 = mysqli_fetch_row($req_showt_src)){ 
      array_push($lre_src, $res1);
    };
    $req_showt_dest = mysqli_query($dest_lnk, "SHOW COLUMNS FROM ".$atable ) ;
    $lre_dest = array();
    while ($res1 = mysqli_fetch_row($req_showt_dest)){ 
      array_push($lre_dest, $res1);
    };
    // passe en revue la correspondance de table (table src doit etre
    // identique a table dest sur les n champs de dest 
    // table src peut avoir des champs supplÃ©mentaires (cas table augmentee) 
    foreach ($lre_dest as $lredk => $lredv) {
      // cmp de $lredv  et $lre_src[$lredk] doivent etre identique 
      if (!strcmp(join($lre_src[$lredk]) , join($lredv))) {
      } else {
          $mresult = 0 ; 
          $sanom .= "table ". $atable . " diff on column [". join($lre_src[$lredk])."] [".join($lredv). "]<br>"; 
          // break ; 
      }; 
    };
    // if (!$mresult) 
    //   break; 
    $sstatus .= " ".$atable ; 
    //    if ($DEBUG) {
    // $sstatus = "lre_src is <br>" . join(", ", $lre_src ) ;
    // $sstatus .= "<br>lre_dest is <br>" .  join(", ", $lre_dest) ; 
    //    };

  }; 
  if (!$mresult) {
    $sstatus = "status differ <br>" . $sanom  ; 
  };
  return array($mresult,$sstatus); 

}

?>
