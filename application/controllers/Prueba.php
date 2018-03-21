<?php
require_once (APPPATH.'/libraries/Rest_Controller.php');
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');

class Prueba extends REST_Controller {
    
    public function __construct(){
        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");
        parent::__construct();
        $this->load->database();
    }

	public function index(){
		echo "hola mundo";
	}
	public function obtener_arreglo_get($index){

		//echo json_encode($arreglo[$index]);
		if($index>2){
		    $respuesta=array('error'=>TRUE,'Mensaje'=>'no existe ese elemento');
		}else{
		    $arreglo=array("manzana","pera","piña");
		    $respuesta=array('error'=>FALSE,'fruta'=>$arreglo[$index]);
		}
		$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
	}
	public function obtener_producto_get($codigo){
		//$this->load->database();
		$query = $this->db->query("SELECT * FROM productos where codigo='".$codigo."'");
		$this->response($query->result() );
		//echo json_encode($query->result());
	}

}

?>
