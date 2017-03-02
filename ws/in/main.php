<?php
  // -*-mode: Php; coding: utf-8; -*-
  /* Ce programme est libre, vous pouvez le redistribuer et/ou le
modifier selon les termes de la Licence Publique Générale GNU publiée
par la Free Software Foundation.  Ce programme est distribué car
potentiellement utile, mais SANS AUCUNE GARANTIE, ni explicite ni
implicite, y compris les garanties de commercialisation ou
d'adaptation dans un but spécifique. Reportez-vous à la Licence
Publique Générale GNU pour plus de détails.  Vous devez avoir reçu une
copie de la Licence Publique Générale GNU en même temps que ce
programme ; si ce n'est pas le cas, écrivez à la Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307,
États-Unis.
**************************************************************************
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or any
later version.  This program is distributed in the hope that it will
be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
General Public License for more details.  You should have received a
copy of the GNU General Public License along with this program; if
not, write to the Free Software Foundation, Inc., 59 Temple Place,
Suite 330, Boston, MA 02111-1307 USA
***************************************************************************
*/ 
/* File : main.php
 * Original Author : Didier Pavet / DGAC 
 * Note: this class allows to propose php_conges internal web services 
   mainly used to exchange data asynchronously using Ajax principles between 
   server side and client side ;  typical call are :

   get("ws/in/main.php?rq=call_hello")
   get("ws/in/main.php?rq=print_session&session=phpcongesee8712bf11f8e6a433a11f1122e52ad6")
   get("ws/in/main.php?rq=calc_joursnsolde&session=phpcongesee8712bf11f8e6a433a11f1122e52ad6\
&session= &uid= &type= &sdate= &spart= &edate &epart")
   
   rq like "call*" does not control the session
   all other rq control that the session is valid 
*/
 	
require_once("../Rest.inc.php");
include("../../INCLUDE.PHP/fonction.php"); 
include("../../fonctions_calcul.php");
include("../../fonctions_conges.php");

$session_req = (isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : NULL ));   /* session should be set through GET or POST otherwise value is NULL */ 
error_log("ws/in/main.php session_req ".$session_req);

session_name($session_req);
session_start();

/* behave as a part of php_conges: getting session id */ 
if(phpversion() > "5.1.2") { include("../../controle_ids.php") ;}
	
class API extends REST {
  public $data = "";
		
  private $db = NULL; 
  private $has_session = False ;
  private $rq ; 
  private $mysqllk ; 
	
  public function __construct($session){
    global $session_real, $_SESSION; 
    parent::__construct(); // Init parent contructor
    if (isset($_SESSION["config"]["php_conges_version"])) {
      $this->has_session = True ;
      session_update($session) ; 
      // error_log("session is valid; updating session. ");
    } else {
      error_log("ws/in/main.php session is not valid:");
    };
  }
  public function response($data,$status){
    parent::response($data,$status) ;
    if ($this->db) { 
      // error_log("ldap_close");
      @ldap_close($this->db); 
    };
  }

  /*
   * Public method for access api.
   * This method dynmically call the method based on the query string
   */
  public function processApi(){
    if (isset($_REQUEST['rq'])) { 
      $this->rq = $_REQUEST['rq']; 
    } else {
      $this->response('no method/request has been found',404);  
    }
    if (ereg("^call",$this->rq )) {   // method is technical 
        $func = $this->rq ; 
        if((int)method_exists($this,$func) > 0)
          $this->$func();
        else
          // If the method not exist with in this class, response would be "Page not found".
          $this->response('not found',404);  
    } else {                          // method is functional 
      // a session is required 
      if (!($this->has_session)) {
        $this->_content_type = "text/html" ;
        $this-> response('no valid session found',417); // ,404);
        return ; 
      }; 
      // sleep(4); // _dpa : a retirer 
      // request available are named "do_something" 
      $func = "do_" . strtolower(trim(str_replace("/","",$this->rq)));
      // error_log("func is " . $func); 
      if((int)method_exists($this,$func) > 0)
        $this->$func();
      else
        // If the method not exist with in this class, response would be "Page not found".
        $this->response($func.' method not found',404);  
    }
  }
        
  /* technical for testing purpose are named call_sthing
   */
		
  private function call_hello() {
    if($this->get_request_method() != "GET"){
      $this->response('',406);
    }
    $this->_content_type = "text/plain";
    $this->response("Hello from ws/in/main.php", 200);
  }

  /*
   * Private method of the form do_<name> can be called by 
   * get("ws/in/main.php?rq=<name>") 
   */

  /* main.php ?rq=get_offperiod&uid=pren.nom&datestart="2012/01/01"&dateend="2012/01/01"&as="xml|csv" */

  private function do_print_session() {
    global $session_req; 
    if(($this->get_request_method() != "GET") && 
       ($this->get_request_method() != "POST")){
      $this->response('',406);
    }
    if ($this->has_session) { 
      $lresult = array("session" => $session_req,
                       "_SESSION" => $_SESSION ) ; 
    } else {
      $lresult = array() ; 
    }
    $this->_content_type = "text/html" ; 
    /* comment recuperer la session et les parametres database faire
       une interrog database */ 
    /* $user = $this->_request['login']; */ 
    /* $date_debut, $date_fin, $opt_debut, $opt_fin, $comment */ 
    $sessionprint = print_r($lresult,True); 
    $pagehtml="<html><body><p>print_session</p><pre>$sessionprint</pre></body></html>"; 
    $this->response($pagehtml, 200);
  }


  private function do_get_offperiod() {
    if($this->get_request_method() != "GET"){
      $this->response('',406);
    }
    /* _spec:
     check session ;
     check access do  mysql 
     requete = f ( uid ) ;
     conversion donnees en json ; 

     */ 
    $data = array('status' => 'ok') ;
    $this->response($this->json($data), 200);
  }

  private function do_demojson() {
    if($this->get_request_method() != "GET"){
      $this->response('',406);
    }
    $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
    $this->response($this->json($error), 400);
  }

  private function do_calc_joursnsolde() {
    global $session,$_SESSION ; 
    $DEBUG = False ; 
    $retcomment = "" ; 
    $demencours = 0 ; 
    $soldeuser = 0 ;
    /* "ret" vaut -1: pb tech, 0: calc nb jour impossible , 1: controle de solde ok ,   
       2: controle solde nok 
       "nbj" nombre de jour du titre 
       "soldeini" le solde initial pour type de conge considere
       "demencours" nombre de jours conge du type a etat demande 
       "retcomment" commentaire */ 
    $lresult = array("ret" => -1, 
                     "nbj"=> -1 ,
                     "soldeini" => -1 ,
                     "demencours" => 0 , 
                     "retcomment" => ""); // initial value 
    if(($this->get_request_method() != "GET")){
      $this->response('',406);
    }
    $this->cx_mysql(); 
    $lresult["uid"] = $_REQUEST['uid'] ;

    /* fait appel a fonctions_calcul.php::compter verif_solde_user_engine::user_index */ 
    $nbj = compter($_REQUEST['uid'], $_REQUEST['sdate'],$_REQUEST['edate'], 
                   $_REQUEST['spart'],$_REQUEST['epart'],-1,$retcomment, $this->mysqllk); 
    $lresult["nbj"] = $nbj ;
    $lresult["retcomment"] = $retcomment ; 
    
    if ($nbj > 0) { 
      $bverif = verif_solde_user_engine($_REQUEST['uid'],$_REQUEST['by'],
                                        $_SESSION['config']['solde_toujours_positif'], 
                                        $_REQUEST['type'],$nbj,$retcomment,$demencours,
                                        $soldeuser,$this->mysqllk );
      $lresult["soldeini"] = $soldeuser ; 
      $lresult["demencours"] = $demencours ; 
      if ($bverif) {
        $lresult["ret"] = 1 ; // ok 
      } else {
        $lresult["ret"] = 2 ; // nok
        $lresult["retcomment"] = $retcomment ;
      };

    } else { // 
      $lresult["ret"] = 0 ; // nok
    }
    /* $lresult["retcomment"] = utf8_encode($lresult["retcomment"]); language are latin1 */
    // language are natively utf8 
    $lresult["retcomment"] = $lresult["retcomment"]; 
    $this->response($this->json($lresult), 200);
  }
  
  private function nodo_v1_calc_joursnsolde() {
    global $session,$_SESSION ; 
    $DEBUG = False ; 
    $retcomment = "" ; 
    $lresult = array("ret" => -1,
                     "nbj"=> -1 ,
                     "soldeini" => -1 ); // initial value 
    if(($this->get_request_method() != "GET")){
      $this->response('',406);
    }
    $this->cx_mysql(); 

    $lresult["uid"] = $_REQUEST['uid'] ;
    /* $lresult["type"] = $_REQUEST['type'] ;
    $lresult["sdate"] = $_REQUEST['sdate'] ;
    $lresult["spart"] = $_REQUEST['spart'] ;
    $lresult["edate"] = $_REQUEST['edate'] ;
    $lresult["epart"] = $_REQUEST['epart'] ;
    */
    /* fait appel a fonctions_calcul.php::compter */ 
    $nbj = compter($_REQUEST['uid'], $_REQUEST['sdate'],$_REQUEST['edate'], 
                   $_REQUEST['spart'],$_REQUEST['epart'],$retcomment, $this->mysqllk); 
    $lresult["nbj"] = $nbj ;
    $lresult["retcomment"] = $retcomment ; 

    if ($nbj > 0) { 
      $lresult["ret"] = 1 ; // ok 
      $sel_cta = "select ta_type from conges_type_absence where ta_id='".$_REQUEST['type']."';" ; 
      $rq_cta = requete_mysql($sel_cta, $this->mysqllk, "do_calc_joursnsolde", $DEBUG);
      $res_cta = mysqli_fetch_array($rq_cta); // 1 row seulement
      if (!$res_cta) {
        // error_log("do_v1_calc_joursnsolde error on conges_type_absence");
        $this->response($this->json($lresult), 200);
      };
      if (ereg("^conges",$res_cta["ta_type"])) { // type conges suivi en solde    
        $sel_su = "select su_solde from conges_solde_user where su_login='".$_REQUEST['uid']."' and su_abs_id='".$_REQUEST['type']."' ;" ;
        $rq_su = requete_mysql($sel_su, $this->mysqllk, "do_calc_joursnsolde", $DEBUG);
        $res_su = mysqli_fetch_array($rq_su); // 1 row seulement
        if (!$res_su) {
          // error_log("do_v1_calc_joursnsolde error on conges_solde_user");
          $this->response($this->json($lresult), 200);
        };
        $lresult["soldeini"] = $res_su["su_solde"] ; 
      }; 
    } else {
      $lresult["ret"] = 0 ; // nok
    }
    
    $this->response($this->json($lresult), 200);
  }


  /* private technical functions
   */
  private function json($data){
    if(is_array($data)){
      return json_encode($data);
    } 
    /*    return json_encode(utf8_encode($data)); */
  }

  private function cx_mysql() {
    $this->mysqllk = connexion_mysql(); 
  }
  

}
	
// Initiate Library
$api = new API($session_req);
$api->processApi();
?>