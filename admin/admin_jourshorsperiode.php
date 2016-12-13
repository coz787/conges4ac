<?php
/*************************************************************************************************
PHP_CONGES : Gestion Interactive des Congés
Copyright (C) 2005 (cedric chauvineau) & 2012-2015 ( dgac / didier pavet)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à  la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, Etats-Unis.
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
require_once("../fonctions_calcul.php") ;

// etat attribué à objet_conges_periode qui memorise les conges hors periode 
define('HP_ETAT','hp' ); 
$lnow = conges_get_date();
$year_cur = $lnow['year'] ; 

$jhpfinete     = $_SESSION['config']['moisjour-finete'] ;
$refhpdate = new DateTime($year_cur."-".$jhpfinete." 00:00:00"); 
$jhptype = intval($_SESSION['config']['jourshorsperiodetype']) ; 

function display_hperiod_tab($user,$nactualsolde,$mysql_link,$DEBUG)  // $nactualsolde,
// display_hperiod_tab affiche_volet_jourshorsperiode
{  

  /*  $jhptype = intval($_SESSION['config']['jourshorsperiodetype']) ; */ 
  /* $jhpfinete     = $_SESSION['config']['moisjour-finete'] ; */ 
  global $jhptype, $jhpfinete, $refhpdate, $lnow, $year_cur ; 
  $jdebutannee   = $_SESSION['config']['moisjour-debutannee'] ;
  $jfinanneereel = $_SESSION['config']['moisjour-finanneereel'] ;
  $jdebutete     = $_SESSION['config']['moisjour-debutete'] ;

  $oconfig = array(
      "msg_nouveau_solde" => $_SESSION['lang']['hp_msg_nouveau_solde'],
      "msg_hp_controle" => $_SESSION['lang']['hp_msg_hp_controle'] );

  /* $refhpdate = new DateTime($year_cur."-".$jhpfinete." 00:00:00");  */ 
  /* INUTILE $bodytag = str_replace("%body%", "black", "<body text='%body%'>"); 
  $refhpdate_s = str_replace("-","/",$year_cur."-".$jhpfinete) */
  $refhpdate_s = $year_cur."-".$jhpfinete ; 
  $refdebutannee = new DateTime($year_cur."-".$jdebutannee." 00:00:00");
  $refdebutannee_s = $year_cur."-".$jdebutannee ; 
  $reffinanneereel = new DateTime($year_cur."-".$jfinanneereel." 23:59:59"); 
  $reffinanneereel_s = $year_cur."-".$jfinanneereel ; 
  $refdebutete = new DateTime($year_cur."-".$jdebutete." 00:00:00"); 
  $refdebutete_s = $year_cur."-".$jdebutete; 
  
  $cphpvalid_select = "select p_nb_jours,p_num from conges_periode where 
p_login='$user' and p_etat ='".HP_ETAT."' and p_type='$jhptype' and p_date_deb='".$refhpdate->format('Y-m-d H:i:s')."' ; " ;

  // echo "would select [". $cphpvalid_select . "]" ; 
  $cphpvalid_req = requete_mysql($cphpvalid_select,$mysql_link,"display_hperiod_tab", 
                                 $DEBUG);
  $cphpvalid_res  = mysql_fetch_array($cphpvalid_req );
  if ($cphpvalid_res) { 
    $b_creation = False ; 
    // echo "a record hpvalid exist <br>" ; 
  } else { 
    $b_creation = True  ; 
    // echo "a record hpvalid does not exist<br>" ; 
  } 
  if (is_hperiod_mngt_open($DEBUG)) { // 
    $b_mngt_open = True ; 
    $leligperiode = get_eligible_cperiode($user,$jhptype,$refdebutannee,$refdebutannee_s,
                                          $refdebutete,$refdebutete_s,
                                          $refhpdate,$refhpdate_s,
                                          $reffinanneereel,$reffinanneereel_s,$mysql_link,$DEBUG) ;
    // $nactualsolde = 99 ;
    $neligjour = $leligperiode['nbjours'] + $nactualsolde ; 
  } else {
    $b_mngt_open = False ; 
  }

  /* display of parametered data of use by client-side script */ 
  echo "<div id=\"oconfig\" class=\"tech\"> \n"; //  
  echo json_encode($oconfig);
  echo "</div>\n"; 

  echo "<fieldset>\n" ; 
  echo "<legend>". $_SESSION['lang']['hp_titre'] ."</legend> \n"; 
  echo "<table class=\"tablo\" cellpadding=\"2\"  width=\"520\">\n"; //
  // $_SESSION['lang']['hp_octroi']
  /* echo "<tr><td colspan=4 align=center>\n";
  echo $_SESSION['lang']['hp_titre'] ;
  echo "</td></tr>\n"; */ 
  echo "<tr><td colspan=4 align=center>".$_SESSION['lang']['hp_octroi']."</td>";
  echo "<td align=center>"; 
  echo "<input type=\"hidden\" id=\"hpmod\" name=\"hpmod\" readonly />\n" ; 
  
  if ($b_creation) {
    echo "-----" ;
    echo "<div id=\"actualjhp\" style=\"display: none;\" >0</div>";
    echo "<input type=\"hidden\" id=\"p_num\" name=\"p_num\" readonly />\n";
  } else {

    echo "<div id=\"actualjhp\">".$cphpvalid_res['p_nb_jours']."</div>";
    echo "<input type=\"hidden\" id=\"p_num\" name=\"p_num\" value=".$cphpvalid_res['p_num'].
      " readonly />\n";
  }
  echo "</td>";
  echo "<td width=\"60\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n" ; 
  echo "</tr>\n";
  if ($b_mngt_open) { 
    // affiche les periodes eligibles
    foreach($leligperiode['lperiode'] as $aperiod) { 
      echo "<tr>\n";
      echo "<td>".$aperiod['p_date_deb']." ".$aperiod['p_demi_jour_deb']."</td><td>".$aperiod['p_date_fin']." ".$aperiod['p_demi_jour_fin']."</td>"; 
      if ($aperiod['n_elig_day'] == $aperiod['p_nb_jours']) { 
        echo "<td></td>" ; 
      } else {
        // on affiche le nombre de jour du titre inférieur au nombre eligible
        echo "<td><i>(".$aperiod['p_nb_jours'].")</i></td>";
      }
      echo "<td>".$aperiod['n_elig_day']."</td>"; 
      echo "</tr>\n";
    }
    // affiche le nb de jours eligibles
    echo "<tr><td colspan=3>".$_SESSION['lang']['hp_libel_jeligible']."</td>";
    echo "<td align=right>".$leligperiode['nbjours']."</td>";
    /* echo "</tr>\n";
     NON :  affiche le solde encore dispo
    echo "<tr><td colspan=3>".$_SESSION['lang']['hp_libel_solderestant']."</td>";
    echo "<td align=right>".$nactualsolde."</td>";
    echo "</tr>\n";
    // affiche le total
    echo "<tr><td colspan=3>".$_SESSION['lang']['hp_libel_total']."</td>";
    echo "<td align=right>".$neligjour."</td>"; */

    if ($b_creation) {
      $jhpvalid_label = $_SESSION['lang']['form_ajout']; 
      $p_nb_jour = "" ; 
    } else {
      $jhpvalid_label = $_SESSION['lang']['form_modif']; 
      $p_nb_jour = $cphpvalid_res['p_nb_jours']; 
    }
    echo "<td align=center><input id=\"newjhp\" name=\"newjhp\" type=\"text\" size=\"2\" value=\"".$p_nb_jour."\"/></td>";
    echo "<td>\n";
    echo "<input id=\"jhpvalid\" type=\"button\" value=\"".$jhpvalid_label."\" />\n" ;
    echo "</td>\n"; 
    echo "</tr>\n";

    echo "<tr><td colspan=6 id=\"fbm_contain\"><div id=\"fbmessage\">". 
      $_SESSION['lang']['hp_regle']."</td></tr>"; 
  }; 

  echo "</table>\n" ;
  echo "</fieldset>\n" ; 

}
  
function is_hperiod_mngt_open($DEBUG)
{
  $b_hpopen = $_SESSION['config']['jourshorsperiode-ouvert'] ;
  if ($b_hpopen) { 
    return True ; 
  } // else ouverture selon date de fin ete 

  global $lnow ;
  $sminopen = $_SESSION['config']['moisjour-debutete'] ;
  $smaxopen = $_SESSION['config']['moisjour-finanneereel'] ;

  $dnow = new DateTime($lnow['year']."-".$lnow['mon']."-".$lnow['mday']." ".
                       $lnow['hours'].":".$lnow['minutes'].":".$lnow['seconds']); 
  $dmin = new DateTime($lnow['year']."-".$sminopen." 23:59:59");
  $dmax = new DateTime($lnow['year']."-".$smaxopen." 23:59:59");

  // return True ; // for test only 
  if ( $dnow >= $dmin && $dnow <= $dmax ) { 
    return True ;
  } else { 
    return False ;
  }
}
function get_eligible_cperiode_v1($user,$jhptype,$ddebutannee,$ddebutete,$dfinete,$dfinannee,
                               $mysql_link,$DEBUG) 
{
    $cphpok_select = "select p_date_deb,p_demi_jour_deb,p_date_fin,p_demi_jour_fin,p_nb_jours 
from conges_periode where p_login='$user' and p_etat ='ok' and p_type='$jhptype' ;" ;
    $cphpok_req = requete_mysql($cphpok_select,$mysql_link,"get_eligible_cperiode",
                                $DEBUG);
    $leligibledata = array("nbjours" => 0, "lperiode" => array()) ; 
    while ( $cphpok_row  = mysql_fetch_array($cphpok_req) ) {
      $ddeb = new DateTime($cphpok_row['p_date_deb']); 
      $dfin = new DateTime($cphpok_row['p_date_fin']); 
      if (($ddeb >= $ddebutannee && $ddeb < $ddebutete ) || 
          ($ddeb >= $dfinete && $ddeb < $dfinannee )  ) {
        $leligibledata['nbjours'] += $cphpok_row['p_nb_jours'] ; 
        array_push($leligibledata['lperiode'],$cphpok_row);
      };
    }
    return $leligibledata ; 

}
function get_eligible_cperiode_v2($user,$jhptype,$ddebutannee,$ddebutete,$ddebutete_s,
                               $dfinete,$dfinete_s,
                               $dfinannee,$mysql_link,$DEBUG) 
{
    $cphpok_select = "select p_num,p_date_deb,p_demi_jour_deb,p_date_fin,p_demi_jour_fin,p_nb_jours 
from conges_periode where p_login='$user' and p_etat ='ok' and p_type='$jhptype' order by p_date_deb,p_demi_jour_deb ;" ;
    $cphpok_req = requete_mysql($cphpok_select,$mysql_link,"get_eligible_cperiode",
                                $DEBUG);
    $leligibledata = array("nbjours" => 0, "lperiode" => array()) ; 
    while ( $cphpok_row  = mysql_fetch_array($cphpok_req) ) {
      $n_elig_day = -1  ; 
      $comment = "" ; 
      $ddeb = new DateTime($cphpok_row['p_date_deb']); 
      $dfin = new DateTime($cphpok_row['p_date_fin']); 
      if ($ddeb >= $ddebutannee && $dfin <= $dfinannee) { // ds l'année 
        if ($dfin <= $ddebutete || $ddeb >= $dfinete) { // conges complet 
          $n_elig_day = $cphpok_row['p_nb_jours'] ; // compte a 100%
        } elseif ( $ddeb <= $ddebutete && $dfin > $ddebutete) {
          // chevauchement autour du debut ete ; on recompte avec comme fin 
          $n_elig_day = compter($user,$cphpok_row['p_date_deb'], $ddebutete_s,
                                $cphpok_row['p_demi_jour_deb'],"pm", 
                                $cphpok_row['p_num'],$comment, $mysql_link,$DEBUG);
        } elseif ( $ddeb < $dfinete && $dfin >= $dfinete) {
          // chevauchement autour de fin ete ; on recompte avec comme debut
          $n_elig_day = compter($user,$dfinete_s,$cphpok_row['p_date_fin'],
                                "am", $cphpok_row['p_demi_jour_fin'],
                                $cphpok_row['p_num'],$comment, $mysql_link,$DEBUG);
        } // else {
          //error_log("get_eligible_cperiod: conges hors les periodes convenues");
        // }
        if ( $n_elig_day > 0 ) { // les seuls conges eligibles 
          $cphpok_row['n_elig_day'] = $n_elig_day;  // ajout champ ds le rang
          $leligibledata['nbjours'] += $n_elig_day ; // sommage jours 
          array_push($leligibledata['lperiode'],$cphpok_row); 
        };
      };
      // on ne fait rien si conges sur autre année 
    }
    return $leligibledata ; 
}
function get_eligible_cperiode($user,$jhptype,$ddebutannee,$ddebutannee_s,
                               $ddebutete,$ddebutete_s,
                               $dfinete,$dfinete_s,
                               $dfinannee,$dfinannee_s,$mysql_link,$DEBUG) 
{
    $cphpok_select = "select p_num,p_date_deb,p_demi_jour_deb,p_date_fin,p_demi_jour_fin,p_nb_jours 
from conges_periode where p_login='$user' and p_etat ='ok' and p_type='$jhptype' order by p_date_deb,p_demi_jour_deb ;" ;
    $cphpok_req = requete_mysql($cphpok_select,$mysql_link,"get_eligible_cperiode",
                                $DEBUG);
    $leligibledata = array("nbjours" => 0, "lperiode" => array()) ; 
    while ( $cphpok_row  = mysql_fetch_array($cphpok_req) ) {
      $n_elig_day = -1  ; 
      $comment = "" ; 
      $ddeb = new DateTime($cphpok_row['p_date_deb']);
      $ddeb_s = $cphpok_row['p_date_deb'];
      $ddeb_dj = $cphpok_row['p_demi_jour_deb'] ;
      $dfin = new DateTime($cphpok_row['p_date_fin']);
      $dfin_s = $cphpok_row['p_date_fin'];
      $dfin_dj = $cphpok_row['p_demi_jour_fin']; 
      /* si le conge est dans l'annee courante */ 
      if ( ($ddeb >= $ddebutannee && $ddeb <= $dfinannee) ||
           ($dfin >= $ddebutannee && $dfin <= $dfinannee) ) { 
        $is_partial = False ; 
        if ($ddeb < $ddebutannee) { // debut annee est butee basse 
          $is_partial = True ;
          $ddeb = $ddebutannee ; 
          $ddeb_s = $ddebutannee_s ; 
          $ddeb_dj = "am" ;
        }
        if ($dfin > $dfinannee) { // fin annee est butee haute 
          $is_partial = True ;
          $dfin = $dfinannee ; 
          $dfin_s = $dfinannee_s ; 
          $dfin_dj = "pm" ; 
        }
        
        if ($dfin <= $ddebutete || $ddeb >= $dfinete) { 
          if ($is_partial) {
            $n_elig_day = compter($user,$ddeb_s, $dfin_s,
                                  $ddeb_dj,$dfin_dj, 
                                  $cphpok_row['p_num'],$comment, $mysql_link,$DEBUG);
          } else { // conges complet 
            $n_elig_day = $cphpok_row['p_nb_jours'] ; // compte a 100%
          }
        } elseif ( $ddeb <= $ddebutete && $dfin > $ddebutete) {
          // chevauchement autour du debut ete ; on recompte avec comme fin 
          $n_elig_day = compter($user,$ddeb_s, $ddebutete_s,
                                $ddeb_dj,"pm", 
                                $cphpok_row['p_num'],$comment, $mysql_link,$DEBUG);
        } elseif ( $ddeb < $dfinete && $dfin >= $dfinete) {
          // chevauchement autour de fin ete ; on recompte avec comme debut
          $n_elig_day = compter($user,$dfinete_s,$dfin_s,
                                "am", $dfin_dj,
                                $cphpok_row['p_num'],$comment, $mysql_link,$DEBUG);
        } // else {
          //error_log("get_eligible_cperiod: conges hors les periodes convenues");
        // }
        if ( $n_elig_day > 0 ) { // les seuls conges eligibles 
          $cphpok_row['n_elig_day'] = $n_elig_day;  // ajout champ ds le rang
          $leligibledata['nbjours'] += $n_elig_day ; // sommage jours 
          array_push($leligibledata['lperiode'],$cphpok_row); 
        };
      };
      // on ne fait rien si conges sur autre année 
    }
    return $leligibledata ; 
}

function update_hperiod($user,$hpmode,$spnum,$snewjhp,$mysql_link,$DEBUG) 
{ 
  global $lnow ,$year_cur ;

  $jhptype = intval($_SESSION['config']['jourshorsperiodetype']) ; 
  $jhpfinete = $_SESSION['config']['moisjour-finete'] ;

  $srefhpdate = $year_cur."-".$jhpfinete." 00:00:00" ;
  /* $srefhpdatefin = "0000-00-00" ; */
  $newjhp = intval($snewjhp) ; 

  $snow = sprintf("%02d-%02d-%02d %02d:%02d:%02d",$lnow['year'],$lnow['mon'],$lnow['mday'],
                  $lnow['hours'],$lnow['minutes'],$lnow['seconds']); 
  $comment = "jours hors periode ". $year_cur ; 
  if ($hpmode == 'cre') {
    $sqlhp = "INSERT INTO conges_periode 
SET p_login='$user', p_date_deb='$srefhpdate', p_demi_jour_deb='am', p_date_fin='$srefhpdate', p_demi_jour_fin='am', p_nb_jours=$newjhp, p_commentaire='$comment', p_type='$jhptype', p_etat='".HP_ETAT."', p_date_traitement='$snow' ; " ;

  } else if ($hpmode == 'mod'){
    $pnum = intval($spnum); 
    $sqlhp = "UPDATE conges_periode 
SET p_nb_jours='$newjhp', p_commentaire='$comment', p_date_traitement='$snow' WHERE
p_num='$pnum' ; " ;
  } else {
    error_log("**error update_hperiod with wrong mode ".$hpmode); 
    return ; 
    // error management 
  } 
  error_log("update_hperiod with ". $sqlhp); 
  $result = requete_mysql($sqlhp, $mysql_link, "update_hperiod", $DEBUG);
  $comment_log = $hpmode."=".$newjhp." ".$comment ; 
  log_action(0, "", $user, $comment_log, $mysql_link, $DEBUG);
  return $result ; 
} 

function get_hperiod($user,$mysql_link,$DEBUG) 
{  
  global $jhptype; 
  global $refhpdate;

  $cphpvalid_select = "select p_nb_jours from conges_periode where 
p_login='$user' and p_etat ='".HP_ETAT."' and p_type='$jhptype' and p_date_deb='".$refhpdate->format('Y-m-d H:i:s')."' ; " ;

  // echo "would select [". $cphpvalid_select . "]" ; 
  $cphpvalid_req = requete_mysql($cphpvalid_select,$mysql_link,"display_hperiod_tab", 
                                 $DEBUG);
  $cphpvalid_res  = mysql_fetch_array($cphpvalid_req );
  if ($cphpvalid_res) { 
    return $cphpvalid_res['p_nb_jours'] ; 
    // echo "a record hpvalid exist <br>" ; 
  } else { 
    return ""; 
    // echo "a record hpvalid does not exist<br>" ; 
  }
}