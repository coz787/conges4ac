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
  /* File : Rest.inc.php
   * Original Author : Arun Kumar Sekar
   * Modified by Didier Pavet / DGAC 
   * Note: this class allows to defines basic Rest Api services ; it is
     used in this context to produce an asynchronous web service devoted
     to ldap querying easing the provisionning of an Human Resources 
     Application . 
   */
class REST {
		
  public $_allow = array();
  public $_content_type = "application/json";
  public $_request = array();
		
  private $_method = "";		
  private $_code = 200;
		
  public function __construct(){
    $this->inputs();
  }
		
  public function get_referer(){
    return $_SERVER['HTTP_REFERER'];
  }
		
  public function response($data,$status){
    $this->_code = ($status)?$status:200;
    $this->set_headers();
    echo $data;
  }
		
  private function get_status_message(){
    $status = array(
                    100 => 'Continue',  
                    101 => 'Switching Protocols',  
                    200 => 'OK',
                    201 => 'Created',  
                    202 => 'Accepted',  
                    203 => 'Non-Authoritative Information',  
                    204 => 'No Content',  
                    205 => 'Reset Content',  
                    206 => 'Partial Content',  
                    300 => 'Multiple Choices',  
                    301 => 'Moved Permanently',  
                    302 => 'Found',  
                    303 => 'See Other',  
                    304 => 'Not Modified',  
                    305 => 'Use Proxy',  
                    306 => '(Unused)',  
                    307 => 'Temporary Redirect',  
                    400 => 'Bad Request',  
                    401 => 'Unauthorized',  
                    402 => 'Payment Required',  
                    403 => 'Forbidden',  
                    404 => 'Not Found',  
                    405 => 'Method Not Allowed',  
                    406 => 'Not Acceptable',  
                    407 => 'Proxy Authentication Required',  
                    408 => 'Request Timeout',  
                    409 => 'Conflict',  
                    410 => 'Gone',  
                    411 => 'Length Required',  
                    412 => 'Precondition Failed',  
                    413 => 'Request Entity Too Large',  
                    414 => 'Request-URI Too Long',  
                    415 => 'Unsupported Media Type',  
                    416 => 'Requested Range Not Satisfiable',  
                    417 => 'Expectation Failed',  
                    500 => 'Internal Server Error',  
                    501 => 'Not Implemented',  
                    502 => 'Bad Gateway',  
                    503 => 'Service Unavailable',  
                    504 => 'Gateway Timeout',  
                    505 => 'HTTP Version Not Supported');
    return ($status[$this->_code])?$status[$this->_code]:$status[500];
  }
		
  public function get_request_method(){
    return $_SERVER['REQUEST_METHOD'];
  }
		
  private function inputs(){
    switch($this->get_request_method()){
    case "POST":
      $this->_request = $this->cleanInputs($_POST);
      break;
    case "GET":
    case "DELETE":
      $this->_request = $this->cleanInputs($_GET);
    break;
    case "PUT":
      parse_str(file_get_contents("php://input"),$this->_request);
      $this->_request = $this->cleanInputs($this->_request);
      break;
    default:
      $this->response('',406);
      break;
    }
  }		
		
  private function cleanInputs($data){
    $clean_input = array();
    if(is_array($data)){
      foreach($data as $k => $v){
        $clean_input[$k] = $this->cleanInputs($v);
      }
    }else{
      if(get_magic_quotes_gpc()){
        $data = trim(stripslashes($data));
      }
      $data = strip_tags($data);
      $clean_input = trim($data);
    }
    return $clean_input;
  }		
		
  private function set_headers(){
    header("HTTP/1.1 ".$this->_code." ".$this->get_status_message());
    header("Content-Type:".$this->_content_type);
  }
  }	
?>