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
/* File : aiws.php
 * Original Author : Didier Pavet / DGAC 
 * Note: this class allows to defines basic Rest Api services ; it is
   used in this context to produce an asynchronous web service devoted
   to ldap querying easing the provisionning of an Human Resources 
   Application . 
*/
 	
require_once("../Rest.inc.php");
require_once("../../config_ldap.php"); // to avoid hard writting of ldap id
	
class API extends REST {
  public $data = "";
		
  // const DB_SERVER = "ldaps://ldapds-region.lfpo.aviation:1301" ; 
  // const DB_SERVER = "ldaps://ldapds.lfpo.aviation-civile.gouv.fr:636";
  const DB_SERVER = "ldap://ldapds.lfpo.aviation-civile.gouv.fr:389";
  const DB_protocol = 3 ; 
  const DB_basedn = "dc=aviation-civile,dc=gouv,dc=fr";
  // acbacea complété par sn=ldap-siege,sn=n,sn=internes_si,
  const DB_basednorg = "sn=organigramme,sn=applications,dc=aviation-civile,dc=gouv,dc=fr";

  /*  replacing usage of CONST, direct use of 
      DB_USER = $config_ldap_user ; 
      DB_PASSWORD = $config_ldap_pass ; */
		
  private $db = NULL;
	
  public function __construct(){
    parent::__construct();	 // Init parent contructor

    if (!array_key_exists('noldap',$_REQUEST))  // if noldap, forget 
      $this->ldapConnect();	 // initiating Database connection 
  }
  public function response($data,$status){
    parent::response($data,$status) ;
    if ($this->db) { 
      // error_log("ldap_close");
      @ldap_close($this->db); 
    };
  }
  /*
   *  Ldap db connection 
   */
  private function ldapConnect(){
    $dummy1 = NULL ;
    $bindstatus = NULL ; 
    try {
      $this->db = @ldap_connect(self::DB_SERVER) ;
# error_log("ldap_connect = ". $this->db);
      if (!($this->db)) throw new Exception('ldap_connect') ; 
      @ldap_set_option($this->db, self::DB_protocol, $dummy1); 
      $bindstatus = @ldap_bind($this->db, $config_ldap_user , $config_ldap_pass ); 
      if (!($bindstatus)) throw new Exception('ldap_bind') ;
    } catch (Exception $e) {
      $this->_content_type = "text/plain";
      $this->response($e->getMessage(), 503); 
    }
    // error_log("bind status = " .$bindstatus ); 
  }
		
  /*
   * Public method for access api.
   * This method dynmically call the method based on the query string
   */
  public function processApi(){
    $func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
    if((int)method_exists($this,$func) > 0)
      $this->$func();
    else
      // If the method not exist with in this class, response would be "Page not found".
      $this->response('not found',404);  
  }
        
  /* set of testing functions 
   */
		
  private function hello() {
    if($this->get_request_method() != "GET"){
      $this->response('',406);
    }
    $this->_content_type = "text/plain";
    $this->response("Hello from rest api.php", 200);
  }
  private function demojson() {
    if($this->get_request_method() != "GET"){
      $this->response('',406);
    }
    $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
    $this->response($this->json($error), 400);
  }
  private function fake_finduid() {
    error_log("aiws::warning::fake_finduid: methode = ".$this->get_request_method());
    if(($this->get_request_method() != "GET") && 
       ($this->get_request_method() != "POST")){
      $this->response('',406);   
    }
    $lfdata = array (
                     array ("value"=> "didier.petitpas", "label"=> "didier.petitpas label", "id"=> array("dn" =>"uidNumber=10009,sn=an,sn=internes_si,dc=aviation-civile,dc=gouv,dc=fr", "mail"=> "didier.petitpas@aviation-civile.gouv.fr", "givenname"=> "Didier", "cn"=> "PETITPAS Didier DGAC/SAC-SPM", "uid"=> "didier.petitpas", "sn"=> "PETITPAS")),

                     array ("value"=>'didier.piquot' , "label"=>'didier.piquot label', "id"=> array ("dn" => 'uidNumber=13461,sn=ldap-crna-e,sn=ne,sn=internes_si,dc=aviation-civile,dc=gouv,dc=fr', 'mail'=> 'didier.piquot@aviation-civile.gouv.fr', 'givenname'=> 'Didier', 'cn'=> 'PIQUOT Didier DGAC/CRNA-E', 'uid'=> 'didier.piquot', 'sn'=> 'PIQUOT')),

                     array ("value"=>'didier.prevot' , "label"=>'didier.prevot label', "id"=> array ("dn"=> "uidNumber=13730,sn=ldap-sna-ne,sn=ne,sn=internes_si,dc=aviation-civile,dc=gouv,dc=fr", 'mail'=> 'didier.prevot@aviation-civile.gouv.fr', 'givenname'=> 'Didier', 'cn'=> 'PREVOT Didier DGAC/SNA-NE', 'uid'=> 'didier.prevot', 'sn'=> 'PREVOT')) ); 
    // $lfdata = array() ;
    $this->response($this->json($lfdata), 200);
          
  }

  /* real method which should produce a result 
   */
  private function find_uid() {
    $debug = False ; 
    if (isset($this->_request['debug'])) {
      $debug = True ;
    }; 
    $search_uid =  $this->_request['term']; // $this->_request['uid'];
    if (array_key_exists('number',$this->_request)) 
      $number = $this->_request['number'] ;
    else 
      $number = 12 ; // 12 items will be returned max
    // number of ldap entry we looked for : minimum 100 or 5 x $number
    $nsearch = max(100,5*$number) ; 
    
    error_log("aiws::warning::find_uid methode=".$this->get_request_method()." uid=". $search_uid." number=". $number ) ;
    if(($this->get_request_method() != "GET") && 
       ($this->get_request_method() != "POST")){
      $this->response('',406);
    }
    // $sfilter = "(&(objectClass=angeliqueUser)(uid=".$search_uid."*))" ;
    // $sfilter = "(&(objectClass=angeliqueUser)(uid~=".$search_uid."))" ;
    $sfilter = '(&(objectClass=angeliqueUser)(uid='.$search_uid.'*))' ;
    // $sfilter = '(uid=*'.$search_uid.'*)' ;
    // $sfilter = "(uid=*".$search_uid."*)" ;
    // $sfilter = "(uid=%".$search_uid.")" ;

    $lattributs = array("uid", "sn", "givenname","cn", "mail");
    // search of $number entries 
    $osr = @ldap_search($this->db, self::DB_basedn,$sfilter,$lattributs,0,$nsearch,0);
    $lresult = @ldap_get_entries($this->db,$osr);
          
    if (sizeof($lresult) > 0 ) {
      // presente un array de type 
      // array( array("id"=>(<all>),"label"=>$uid - $cn,"value"=>$uid))
      if ($debug) {
        $rawresult = print_r($lresult,True); 
        $predata = print_r($this->luser2autoc($lresult,$number),True); 
        $pagehtml="<html><body><p>raw_result from request</p><pre>$rawresult</pre>
<p>preparation by luser2autoc</p><pre>$predata</pre</body></html>";
        $this->_content_type = "text/html" ; 
        $this->response($pagehtml, 200);
      } else { 
        $this->response($this->json($this->luser2autoc($lresult,$number)), 200);
      }

    } else {  // empty response 
      $this->response('',204);
    };
 
  }
  private function find_uo() {
    $search_uo =  $this->_request['term']; // $this->_request['uid'];
    if (array_key_exists('number',$this->_request)) 
      $number = $this->_request['number'] ;
    else 
      $number = 12 ; // 12 items will be search max  
    error_log("aiws::warning::find_uo methode=".$this->get_request_method()." uid=". $search_uid." number=". $number ) ;
    if(($this->get_request_method() != "GET") && 
       ($this->get_request_method() != "POST")){
      $this->response('',406);
    }
    // $sfilter = "(&(|((objectClass=angeliqueOrganizationalUnit)(objectClass=angeliqueOrganization)))(sn=".$search_uo."*))" ;
    $sfilter = "(&(|(objectClass=angeliqueOrganizationalUnit)(objectClass=angeliqueOrganization))(sn=".$search_uo."*))" ; 
    // $sfilter = "(uid=*".$search_uid."*)" ;
    // $sfilter = "(uid=%".$search_uid.")" ;

    $lattributs = array("sn","cn","seealso");
    // search of $number entries 
    $osr = @ldap_search($this->db, self::DB_basednorg,$sfilter,$lattributs,0,$number,0);
    $lresult = @ldap_get_entries($this->db,$osr);
          
    if ($lresult) {
      // presente un array de type 
      // array( array("id"=>(<all>),"label"=>$uid - $cn,"value"=>$uid))
      //$this->response($this->json($this->lentry2autoc($lresult)), 200);
      $this->response($this->json($lresult), 200);
    } else {  // empty response 
      $this->response('',204);
    };
  }
  private function luser2autoc_v1_old($lentry) { 
    // format a set of ldap entry in autocomplete menu entry 
    // array( array("id"=>(<all>),"label"=>$uid - $cn,"value"=>$uid))
    // sorted on uid 
    $lautoc = array() ; 
    foreach($lentry as $k => $v){
      if ($k == "count") continue ; 
      $laset = array("id"=>Null,"label"=>"","value"=>"") ;
      $lid = array("sn"=>"", "mail"=>"", "givenname"=>"") ; 
      $laset["value"] = $v["uid"][0] ; 
      $laset["label"] = $laset["value"]." - ".$v["cn"][0] ; 
      $lid["sn"] = $v["sn"][0] ;
      $lid["mail"] = $v["mail"][0] ;
      $lid["givenname"] = $v["givenname"][0] ;
      $laset["id"] = $lid ;
      // form a 
      $lautoc[$laset["value"]] = $laset;
    }
    sort($lautoc); 
    return $lautoc ; 
  }
/* v2 */ 
  private function luser2autoc($lentry,$nbmax) { 
    // format a set of ldap entry in autocomplete menu entry 
    // array( array("id"=>(<all>),"label"=>$uid - $cn,"value"=>$uid))
    // sorted on uid 
    $lautoc = array() ; 
    foreach($lentry as $k => $v){
      // _care: operateur == provoque un bug comparaison chaine / nombre
      if ($k === "count") { 
        continue ;
      }; 
      $laset = array("id"=>Null,"label"=>"","value"=>"") ;
      $lid = array("sn"=>"", "mail"=>"", "givenname"=>"") ; 

      $laset["value"] = $v["uid"][0] ; 
      $laset["label"] = $laset["value"]." - ".$v["cn"][0] ; 
      $lid["sn"] = $v["sn"][0] ;
      $lid["mail"] = $v["mail"][0] ;
      $lid["givenname"] = $v["givenname"][0] ;
      $laset["id"] = $lid ;
      // echo $laset["value"]."\n" ; 
      $lautoc[$laset["value"]] = $laset;
    }
    ksort($lautoc) ;                          /* tri sur la cle */
    $lautoc = array_slice($lautoc,0,$nbmax);  /* retourne que $nbmax val */ 
    return array_values($lautoc) ;   /* on retourne les valeurs du tableau */ 
  }

  /*
   *	Encode array into JSON
   */
  private function json($data){
    if(is_array($data)){
      return json_encode($data);
    }
  }
}
	
// Initiate Library
$api = new API;
$api->processApi();
?>